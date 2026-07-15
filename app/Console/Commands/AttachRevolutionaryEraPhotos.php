<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches public-domain portraits and silhouettes for Revolutionary-era
 * political prisoners — banished Loyalists and the Philadelphia Quaker
 * "Prisoners of Congress" exiled to Virginia in 1777. All images are
 * 18th/19th-century works in the public domain; provenance is recorded in
 * CREDITS-nonfree.md.
 *
 * Each record is matched by slug (several candidates), then by exact name /
 * alias. The photo is set ONLY when the prisoner currently has none — existing
 * photos are never overwritten. Idempotent and safe to re-run.
 */
final class AttachRevolutionaryEraPhotos extends Command
{
    protected $signature = 'prisoners:attach-revolutionary-era-photos';

    protected $description = 'Attach public-domain Revolutionary-era prisoner portraits to records with no photo (fill-if-empty)';

    /** @var array<int,array{file:string,slugs:string[],names:string[]}> */
    private const ENTRIES = [
        ['file' => 'samuel-seabury.jpg',
         'slugs' => ['samuel-seabury'],
         'names' => ['Samuel Seabury']],
        ['file' => 'william-franklin.jpg',
         'slugs' => ['william-franklin'],
         'names' => ['William Franklin']],
        ['file' => 'peter-van-schaack.jpg',
         'slugs' => ['peter-van-schaack', 'peter-van-schaak'],
         'names' => ['Peter Van Schaack', 'Peter Van Schaak']],
        ['file' => 'james-pemberton.jpg',
         'slugs' => ['james-pemberton'],
         'names' => ['James Pemberton']],
        ['file' => 'john-pemberton.jpg',
         'slugs' => ['john-pemberton'],
         'names' => ['John Pemberton']],
        ['file' => 'henry-drinker.jpg',
         'slugs' => ['henry-drinker-jr', 'henry-drinker', 'henry-drinker-junior'],
         'names' => ['Henry Drinker Jr.', 'Henry Drinker', 'Henry Drinker Junior']],
        ['file' => 'samuel-pleasants.jpg',
         'slugs' => ['samuel-pleasants'],
         'names' => ['Samuel Pleasants']],
    ];

    public function handle(): int
    {
        Storage::disk('public')->makeDirectory('prisoners');

        $linked = 0;
        $skipped = 0;
        $missing = [];

        foreach (self::ENTRIES as $e) {
            $src = database_path("data/photos/nonfree/{$e['file']}");
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

            $relative = 'prisoners/'.$e['file'];
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
