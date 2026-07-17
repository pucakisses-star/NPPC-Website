<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches portraits of the 1958 Golden Rule crew (July 2026 deep dive).
 *
 * Sherwood, Huntington and Willoughby are cropped from the 1958 color
 * photograph of the crew aboard the boat, whose published caption gives the
 * left-to-right order (Bigelow, Sherwood, Huntington, Willoughby) — the order
 * is corroborated by their ages and known later portraits. Peck is cropped
 * from the 1961 Library of Congress picketing photograph in which his own
 * placard names him. All four are non-free (photos/nonfree/,
 * CREDITS-nonfree.md). Bigelow already has a portrait.
 *
 * Fill-if-empty: the photo is set ONLY when the prisoner currently has none.
 * Idempotent and safe to re-run.
 */
final class AttachGoldenRulePhotos extends Command
{
    protected $signature = 'prisoners:attach-golden-rule-photos';

    protected $description = 'Attach 1958 Golden Rule crew portraits (fill-if-empty)';

    /** @var array<int,array{file:string,slugs:string[],names:string[]}> */
    private const ENTRIES = [
        ['file' => 'nonfree/orion-sherwood.jpg', 'slugs' => ['orion-sherwood'], 'names' => ['Orion Sherwood']],
        ['file' => 'nonfree/william-huntington.jpg', 'slugs' => ['william-huntington'], 'names' => ['William Huntington', 'William R. Huntington']],
        ['file' => 'nonfree/george-willoughby.jpg', 'slugs' => ['george-willoughby'], 'names' => ['George Willoughby']],
        ['file' => 'nonfree/james-peck.jpg', 'slugs' => ['james-peck'], 'names' => ['James Peck', 'Jim Peck']],
    ];

    public function handle(): int
    {
        Storage::disk('public')->makeDirectory('prisoners');

        $linked = 0;
        $skipped = 0;
        $missing = [];

        foreach (self::ENTRIES as $e) {
            $src = database_path("data/photos/{$e['file']}");
            if (! is_file($src)) {
                $this->warn("Source image not found: {$src}");

                continue;
            }

            $prisoner = $this->resolve($e['slugs'], $e['names']);
            if (! $prisoner) {
                $missing[] = $e['names'][0];

                continue;
            }

            if (! empty($prisoner->photo)) {
                $this->line("{$prisoner->name} already has a photo — leaving alone.");
                $skipped++;

                continue;
            }

            $relative = 'prisoners/'.basename($e['file']);
            Storage::disk('public')->put($relative, (string) file_get_contents($src));
            $prisoner->photo = $relative;
            $prisoner->save();
            $this->info("Linked photo for {$prisoner->name}.");
            $linked++;
        }

        if ($linked > 0) {
            Cache::forget(PrisonerApiController::cacheKey());
        }

        $this->info("\nDone. Linked={$linked}, already-had-photo={$skipped}.");
        if ($missing) {
            $this->warn('Not found ('.count($missing).'): '.implode(', ', $missing)
                .' — pass me the exact site name/slug and I will map it.');
        }

        return self::SUCCESS;
    }

    /**
     * @param  string[]  $slugs
     * @param  string[]  $names
     */
    private function resolve(array $slugs, array $names): ?Prisoner
    {
        foreach ($slugs as $slug) {
            $p = Prisoner::withUnderReview()->where('slug', $slug)->first();
            if ($p) {
                return $p;
            }
        }
        foreach ($names as $name) {
            $p = Prisoner::withUnderReview()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();
            if ($p) {
                return $p;
            }
        }

        return null;
    }
}
