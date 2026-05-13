<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Fetches photos for the 9 Philly ABC prisoners that had direct
 * headshot URLs on phillyabc.org and links them on the Prisoner
 * records. Saves to storage/app/public/prisoners/{slug}.jpg and sets
 * Prisoner.photo accordingly.
 *
 * Malik Muhammad and Calla Walsh had no individual photo on Philly
 * ABC (event graphic / obscured-face graphic), so they're skipped.
 */
final class FetchPhillyAbcPhotos extends Command {
    protected $signature = 'archive:fetch-philly-abc-photos {--force : Re-download and re-link even if a photo already exists}';
    protected $description = 'Download Philly ABC headshots for the 11 imported prisoners and link them on the records';

    public function handle(): int {
        $force = (bool) $this->option('force');
        $disk = Storage::disk('public');
        $disk->makeDirectory('prisoners');

        $photos = [
            'jamil-abdullah-al-amin'    => 'https://phillyabc.org/images/imam-jamil-al-amin.jpg',
            'jessica-reznicek'          => 'https://phillyabc.org/images/jessica-reznicek-letter-writing.jpg',
            'daniel-alan-baker'         => 'https://phillyabc.org/images/dan-baker-letter-writing.jpg',
            'urooj-rahman'              => 'https://media.phillyabc.org/images/urooj-letter-writing.jpg',
            'lore-elisabeth-blumenthal' => 'https://phillyabc.org/images/lore.jpg',
            'anthony-smith'             => 'https://phillyabc.org/images/ant-smith-letter-writing.jpg',
            'brian-dipippa'             => 'https://media.phillyabc.org/images/peppy-dipippa-letter-writing.jpg',
            'fran-thompson'             => 'https://phillyabc.org/images/fran-thompson-letter-writing.jpg',
            'jesse-cannon'              => 'https://media.phillyabc.org/images/jesse-cannon-released.jpg',
        ];

        $downloaded = 0;
        $linked = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($photos as $slug => $url) {
            $prisoner = Prisoner::where('slug', $slug)->first();
            if (! $prisoner) {
                $this->warn("Prisoner not found: {$slug}");
                $failed++;

                continue;
            }
            if (! empty($prisoner->photo) && ! $force) {
                $existingPath = ltrim($prisoner->photo, '/');
                if ($disk->exists($existingPath)) {
                    $this->line("skip (already has photo): {$slug} -> {$prisoner->photo}");
                    $skipped++;

                    continue;
                }
            }

            $this->line("fetch {$url}");
            try {
                $resp = Http::withHeaders([
                    'User-Agent' => 'NPPC-Archive/1.0 (https://nationalpoliticalprisonercoalition.org)',
                ])->timeout(30)->get($url);
                if (! $resp->successful()) {
                    $this->error("  HTTP {$resp->status()} for {$slug}");
                    $failed++;

                    continue;
                }
                $bytes = $resp->body();
                if (strlen($bytes) < 1000) {
                    $this->error('  suspiciously small response ('.strlen($bytes).' bytes) for '.$slug);
                    $failed++;

                    continue;
                }
                $relative = 'prisoners/'.$slug.'.jpg';
                $disk->put($relative, $bytes);
                $this->info('  saved '.number_format(strlen($bytes) / 1024, 1).' KB to '.$relative);
                $downloaded++;

                $prisoner->photo = $relative;
                $prisoner->save();
                $linked++;
            } catch (\Throwable $e) {
                $this->error('  '.$e->getMessage());
                $failed++;
            }
        }

        $this->info("\nDone. Downloaded={$downloaded} Linked={$linked} Skipped={$skipped} Failed={$failed}");

        return self::SUCCESS;
    }
}
