<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FetchCreativeCommonsPhotos extends Command
{
    protected $signature = 'prisoners:fetch-cc-photos
                            {--limit= : Cap the number of prisoners processed in this run}
                            {--overwrite : Re-fetch even if photo already exists}
                            {--dry-run : Show what would be downloaded, do not write}
                            {--names= : Only run for specific names, comma-separated}';

    protected $description = 'Fetch missing prisoner photos from Wikimedia Commons via the Wikipedia pageimages API. All Commons content is Creative Commons or public domain; attribution and license are saved alongside the file.';

    private const USER_AGENT = 'NPPC-Website/1.0 (+https://nppc.org; political-prisoner-database)';

    public function handle(): int
    {
        $dryRun    = (bool) $this->option('dry-run');
        $overwrite = (bool) $this->option('overwrite');
        $limit     = $this->option('limit') ? (int) $this->option('limit') : null;
        $namesOpt  = $this->option('names');

        $query = Prisoner::query();
        if (! $overwrite) {
            $query->where(function ($q) {
                $q->whereNull('photo')->orWhere('photo', '');
            });
        }
        if ($namesOpt) {
            $names = array_map('trim', explode(',', $namesOpt));
            $query->whereIn('name', $names);
        }
        $prisoners = $query->orderBy('name')->get();

        if ($limit) {
            $prisoners = $prisoners->take($limit);
        }

        if ($prisoners->isEmpty()) {
            $this->info('No prisoners need photos.');

            return self::SUCCESS;
        }

        $this->info($prisoners->count() . ' prisoners to process.');
        $this->newLine();

        if (! $dryRun) {
            Storage::disk('public')->makeDirectory('prisoners');
        }

        $downloaded = 0;
        $missingPage = 0;
        $missingImage = 0;
        $failed = 0;

        foreach ($prisoners as $prisoner) {
            $title = $this->findWikipediaTitle($prisoner);
            if (! $title) {
                $missingPage++;
                $this->line("  [no Wikipedia page] {$prisoner->name}");
                continue;
            }

            $imageData = $this->fetchPageImage($title);
            if (! $imageData) {
                $missingImage++;
                $this->line("  [no image on page]  {$prisoner->name}  →  {$title}");
                continue;
            }

            $attribution = $this->fetchAttribution($imageData['filename']);

            if ($dryRun) {
                $this->line(sprintf(
                    "  [DRY] %s  →  %s  (%s)",
                    $prisoner->name,
                    $imageData['url'],
                    $attribution['license'] ?? 'unknown license'
                ));
                continue;
            }

            try {
                $resp = Http::withHeaders(['User-Agent' => self::USER_AGENT])
                    ->timeout(30)
                    ->get($imageData['url']);
                if (! $resp->successful()) {
                    $failed++;
                    $this->line("  [http {$resp->status()}]   {$prisoner->name}");
                    continue;
                }

                $ext = $this->extensionFromMime($resp->header('Content-Type'));
                $filename = 'prisoners/' . Str::slug($prisoner->name) . '-' . $prisoner->id . '.' . $ext;
                Storage::disk('public')->put($filename, $resp->body());

                $prisoner->photo = $filename;
                $prisoner->photo_source_url = 'https://commons.wikimedia.org/wiki/File:' . str_replace(' ', '_', $imageData['filename']);
                $prisoner->photo_attribution = $attribution['attribution'] ?? null;
                $prisoner->photo_license = $attribution['license'] ?? null;
                $prisoner->saveQuietly();

                $downloaded++;
                $this->line(sprintf(
                    "  [downloaded] %s  ←  %s  (%s)",
                    $prisoner->name,
                    $imageData['filename'],
                    $attribution['license'] ?? 'unknown license'
                ));
            } catch (\Throwable $e) {
                $failed++;
                $this->line("  [error] {$prisoner->name}: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Downloaded:  {$downloaded}");
        $this->info("No Wiki page: {$missingPage}");
        $this->info("Page no img:  {$missingImage}");
        if ($failed > 0) {
            $this->warn("Failed:       {$failed}");
        }

        return self::SUCCESS;
    }

    /**
     * Resolve a prisoner to an English Wikipedia article title via the
     * MediaWiki search API. Tries the canonical name first, then aka.
     */
    private function findWikipediaTitle(Prisoner $p): ?string
    {
        foreach (array_filter([$p->name, $p->aka]) as $candidate) {
            $title = $this->searchWikipedia($candidate);
            if ($title) {
                return $title;
            }
        }

        return null;
    }

    private function searchWikipedia(string $query): ?string
    {
        try {
            $resp = Http::withHeaders(['User-Agent' => self::USER_AGENT])
                ->timeout(15)
                ->get('https://en.wikipedia.org/w/api.php', [
                    'action'   => 'query',
                    'list'     => 'search',
                    'srsearch' => $query,
                    'srlimit'  => 1,
                    'format'   => 'json',
                ]);
            if (! $resp->successful()) {
                return null;
            }
            $hits = data_get($resp->json(), 'query.search', []);
            return $hits[0]['title'] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get the page's main image via the pageimages extension.
     * Returns ['url' => ..., 'filename' => ...] or null.
     */
    private function fetchPageImage(string $title): ?array
    {
        try {
            $resp = Http::withHeaders(['User-Agent' => self::USER_AGENT])
                ->timeout(15)
                ->get('https://en.wikipedia.org/w/api.php', [
                    'action'   => 'query',
                    'titles'   => $title,
                    'prop'     => 'pageimages',
                    'piprop'   => 'original|name',
                    'format'   => 'json',
                ]);
            if (! $resp->successful()) {
                return null;
            }
            $pages = data_get($resp->json(), 'query.pages', []);
            $page = is_array($pages) ? reset($pages) : null;
            $url = data_get($page, 'original.source');
            $filename = data_get($page, 'pageimage');
            if (! $url || ! $filename) {
                return null;
            }
            // Only accept upload.wikimedia.org URLs (Commons-served).
            if (! str_contains($url, 'upload.wikimedia.org')) {
                return null;
            }
            return ['url' => $url, 'filename' => $filename];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Pull attribution and license from Commons via imageinfo extmetadata.
     */
    private function fetchAttribution(string $filename): array
    {
        try {
            $resp = Http::withHeaders(['User-Agent' => self::USER_AGENT])
                ->timeout(15)
                ->get('https://commons.wikimedia.org/w/api.php', [
                    'action'   => 'query',
                    'titles'   => 'File:' . $filename,
                    'prop'     => 'imageinfo',
                    'iiprop'   => 'extmetadata',
                    'format'   => 'json',
                ]);
            if (! $resp->successful()) {
                return [];
            }
            $pages = data_get($resp->json(), 'query.pages', []);
            $page = is_array($pages) ? reset($pages) : null;
            $meta = data_get($page, 'imageinfo.0.extmetadata', []);
            $artist = strip_tags(data_get($meta, 'Artist.value', ''));
            $license = data_get($meta, 'LicenseShortName.value', '');
            $credit = strip_tags(data_get($meta, 'Credit.value', ''));
            $attribution = trim($artist) ?: trim($credit) ?: 'Wikimedia Commons';
            return [
                'attribution' => mb_substr(html_entity_decode($attribution), 0, 250),
                'license'     => $license ?: null,
            ];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function extensionFromMime(?string $mime): string
    {
        return match (true) {
            str_contains($mime ?? '', 'png')  => 'png',
            str_contains($mime ?? '', 'gif')  => 'gif',
            str_contains($mime ?? '', 'webp') => 'webp',
            str_contains($mime ?? '', 'svg')  => 'svg',
            default                           => 'jpg',
        };
    }
}
