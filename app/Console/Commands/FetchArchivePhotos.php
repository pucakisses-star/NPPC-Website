<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Downloads freely-licensed portrait photos for the National Guardian /
 * archive figures from Wikimedia Commons, using the verified manifest in
 * database/data/archive-photos.json. Each entry was hand-checked so the
 * Wikipedia subject (domain + lifespan) matches our prisoner record —
 * same-name impostors were excluded during manifest generation.
 *
 * Images are stored on the public disk at prisoners/{slug}.{ext} and the
 * path is written to prisoners.photo. Idempotent: skips a prisoner that
 * already has a photo (unless --force) and reuses an already-downloaded
 * file. License is recorded per entry for attribution.
 */
final class FetchArchivePhotos extends Command
{
    protected $signature = 'prisoners:fetch-archive-photos {--force : Overwrite photos that are already set}';

    protected $description = 'Fetch freely-licensed Wikimedia photos for archive figures (from archive-photos.json)';

    private const UA = 'NPPC-Archive-PhotoBot/1.0 (https://nppc.org; advocacy nonprofit) Laravel-Http';

    public function handle(): int
    {
        $file = database_path('data/archive-photos.json');
        if (! file_exists($file)) {
            $this->error('archive-photos.json not found.');

            return self::FAILURE;
        }

        $rows = json_decode(file_get_contents($file), true);
        if (! is_array($rows)) {
            $this->error("Could not parse {$file}");

            return self::FAILURE;
        }

        $disk = Storage::disk('public');
        $added = 0;
        $skipped = 0;
        $missing = 0;
        $failed = 0;

        foreach ($rows as $r) {
            $name = $r['name'] ?? null;
            $url = $r['source_url'] ?? null;
            if (! $name || ! $url) {
                continue;
            }

            $prisoner = Prisoner::withUnderReview()->where('name', $name)->first();
            if (! $prisoner) {
                $this->warn("Not in DB: {$name}");
                $missing++;

                continue;
            }

            if ($prisoner->photo && ! $this->option('force')) {
                $this->line("Has photo (skip): {$name}");
                $skipped++;

                continue;
            }

            $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg');
            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                $ext = 'jpg';
            }
            $path = 'prisoners/'.Str::slug($name).'.'.$ext;

            if (! $disk->exists($path)) {
                // Throttle to stay under Wikimedia's anonymous rate limit.
                sleep(1);
                // Manifest URLs are already width-bounded Commons thumbnails.
                $body = $this->fetch($url, $name);
                if ($body === null) {
                    $failed++;

                    continue;
                }
                $disk->put($path, $body);
            }

            $prisoner->photo = $path;
            $prisoner->save();
            $this->info("Set photo: {$name}  [{$r['license']}]");
            $added++;
        }

        $this->info("\nDone. Set={$added} Skipped={$skipped} NotInDB={$missing} Failed={$failed}");

        return self::SUCCESS;
    }

    /**
     * Fetch image bytes, retrying on HTTP 429 with exponential backoff.
     * Returns null on a non-retryable failure (so the caller can fall back).
     */
    private function fetch(string $url, string $name): ?string
    {
        foreach ([0, 5, 12, 25] as $wait) {
            if ($wait) {
                sleep($wait);
            }
            try {
                $resp = Http::withHeaders(['User-Agent' => self::UA])->timeout(90)->get($url);
                if ($resp->status() === 429) {
                    $this->warn("429 rate-limited, backing off ({$name})");

                    continue;
                }
                if (! $resp->successful() || strlen($resp->body()) < 2000) {
                    return null;
                }

                return $resp->body();
            } catch (\Throwable $e) {
                $this->error("Fetch error for {$name}: ".$e->getMessage());

                return null;
            }
        }

        $this->error("Still 429 after retries: {$name}");

        return null;
    }
}
