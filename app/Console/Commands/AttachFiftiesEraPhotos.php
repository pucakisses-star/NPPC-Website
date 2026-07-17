<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches portraits found during the July 2026 deep dive on photoless
 * 1950s-era prisoners. Six are freely licensed from Wikimedia Commons
 * (credited in CREDITS-wikipedia.md). The Kissing Case newspaper clipping is
 * a non-free English Wikipedia image (photos/nonfree/, CREDITS-nonfree.md)
 * whose caption identifies both boys — it is deliberately attached to both
 * James Hanover Thompson and David Simpson, the same intentional-shared
 * pattern as the Soto couple photo.
 *
 * Fill-if-empty: the photo is set ONLY when the prisoner currently has none.
 * Idempotent and safe to re-run.
 */
final class AttachFiftiesEraPhotos extends Command
{
    protected $signature = 'prisoners:attach-fifties-era-photos';

    protected $description = 'Attach 1950s-era audit portraits (fill-if-empty)';

    /** @var array<int,array{file:string,slugs:string[],names:string[]}> */
    private const ENTRIES = [
        ['file' => 'harry-gold.jpg', 'slugs' => ['harry-gold'], 'names' => ['Harry Gold']],
        ['file' => 'james-e-jackson.jpg', 'slugs' => ['james-e-jackson'], 'names' => ['James E. Jackson', 'James E. Jackson Jr.']],
        ['file' => 'morton-sobell.jpg', 'slugs' => ['morton-sobell'], 'names' => ['Morton Sobell']],
        ['file' => 'oscar-collazo.jpg', 'slugs' => ['oscar-collazo'], 'names' => ['Oscar Collazo']],
        ['file' => 'rosa-cortez-collazo.jpg', 'slugs' => ['rosa-cortez-collazo'], 'names' => ['Rosa Cortez Collazo', 'Rosa Collazo']],
        ['file' => 'steve-nelson.jpg', 'slugs' => ['steve-nelson'], 'names' => ['Steve Nelson']],
        ['file' => 'nonfree/hanover-thompson.jpg', 'slugs' => ['hanover-thompson'], 'names' => ['Hanover Thompson', 'James Hanover Thompson']],
        ['file' => 'nonfree/david-simpson.jpg', 'slugs' => ['david-simpson'], 'names' => ['David Simpson', 'David "Fuzzy" Simpson']],
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
