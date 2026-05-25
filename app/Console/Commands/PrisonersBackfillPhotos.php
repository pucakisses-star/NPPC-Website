<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Backfills photos for Prisoner rows that don't have one set, using
 * Wikipedia's REST summary endpoint (Wikimedia Commons photos).
 *
 * For each photoless prisoner:
 *   1. Try /api/rest_v1/page/summary/<Full Name> first (most precise)
 *   2. If no result, try the slug variant
 *   3. If page_image exists, download to prisoners/<slug>.jpg and
 *      set prisoner.photo
 *
 * Honors --dry-run, --limit, --since-id (for incremental runs).
 */
final class PrisonersBackfillPhotos extends Command {
    protected $signature = 'prisoners:backfill-photos
        {--dry-run : Show what would be persisted without writing}
        {--limit=50 : Maximum prisoners to process this run}
        {--since-id= : Only process prisoners with id > this value (lexicographic; useful for resuming)}
        {--newest : Process newest-created prisoners first instead of oldest}';
    protected $description = 'Pull Wikipedia photos for prisoners that don\'t have one set';

    public function handle(): int {
        $dryRun = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');
        $sinceId = $this->option('since-id');

        $query = Prisoner::query()
            ->whereNull('photo')
            ->orWhere('photo', '');

        if ($sinceId) {
            $query->where('id', '>', $sinceId);
        }

        $query->orderBy('created_at', $this->option('newest') ? 'desc' : 'asc')
              ->limit($limit);

        $prisoners = $query->get(['id', 'name', 'slug', 'created_at']);

        $this->info('Photoless prisoners selected this run: '.$prisoners->count());

        $set = 0; $missing = []; $errors = [];

        foreach ($prisoners as $p) {
            $name = trim($p->name ?? '');
            if ($name === '') {
                continue;
            }

            $imageUrl = null; $articleTitle = null;
            // Try full name first.
            [$imageUrl, $articleTitle] = $this->wikipediaImage($name);
            // Slug form as a fallback (e.g. "Big Bill Haywood" → "Big_Bill_Haywood")
            if (! $imageUrl) {
                [$imageUrl, $articleTitle] = $this->wikipediaImage(str_replace(' ', '_', $name));
            }

            if (! $imageUrl) {
                $missing[] = $name;
                continue;
            }

            $slug = $p->slug ?: Str::slug($name);
            $ext = $this->guessExt($imageUrl);
            $path = 'prisoners/'.$slug.'.'.$ext;

            if ($dryRun) {
                $this->line(sprintf('  [DRY] %s  ←  %s', $name, $articleTitle));
                $set++;
                continue;
            }

            try {
                $resp = Http::withHeaders(['User-Agent' => 'NPPC-Archive/1.0 (https://nationalpoliticalprisonercoalition.org)'])
                    ->timeout(60)
                    ->get($imageUrl);
                if (! $resp->successful() || strlen($resp->body()) < 2000) {
                    $errors[] = "{$name} (download HTTP {$resp->status()})";
                    continue;
                }
                Storage::disk('public')->put($path, $resp->body());
            } catch (\Throwable $e) {
                $errors[] = "{$name} (".$e->getMessage().')';
                continue;
            }

            $p->photo = $path;
            $p->save();
            $this->line(sprintf('  PHOTO  %s  ←  %s', $name, $articleTitle));
            $set++;
        }

        $this->newLine();
        $this->info(($dryRun ? '[DRY RUN] ' : '')."Set {$set} photos.");
        if ($missing) {
            $this->warn('No Wikipedia image found ('.count($missing).'):');
            foreach (array_slice($missing, 0, 30) as $n) {
                $this->line('  - '.$n);
            }
            if (count($missing) > 30) {
                $this->line('  ... ('.(count($missing) - 30).' more)');
            }
        }
        if ($errors) {
            $this->warn('Errors ('.count($errors).'):');
            foreach (array_slice($errors, 0, 30) as $e) {
                $this->line('  - '.$e);
            }
        }
        return self::SUCCESS;
    }

    /**
     * @return array{0: ?string, 1: ?string}  [imageUrl, articleTitle]
     */
    private function wikipediaImage(string $articleTitle): array {
        try {
            $resp = Http::withHeaders(['User-Agent' => 'NPPC-Archive/1.0 (https://nationalpoliticalprisonercoalition.org)'])
                ->timeout(20)
                ->get('https://en.wikipedia.org/api/rest_v1/page/summary/'.rawurlencode($articleTitle));
        } catch (\Throwable $e) {
            return [null, null];
        }
        if (! $resp->successful()) {
            return [null, null];
        }
        $data = $resp->json();
        $imageUrl = $data['originalimage']['source'] ?? $data['thumbnail']['source'] ?? null;
        if (! $imageUrl
            || str_contains($imageUrl, 'wikipedia-logo')
            || str_contains($imageUrl, 'Question_book')
            || str_contains($imageUrl, 'Bracketsorter')
            || str_contains($imageUrl, 'No_image')) {
            return [null, null];
        }
        return [$imageUrl, $data['title'] ?? $articleTitle];
    }

    private function guessExt(string $url): string {
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $ext = preg_replace('/[^a-z0-9]/', '', $ext);
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            return $ext === 'jpeg' ? 'jpg' : $ext;
        }
        return 'jpg';
    }
}
