<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches portraits for 1930s Depression-era labor, cultural, and radical
 * political prisoners — CAWIU criminal-syndicalism defendants, Harlan County
 * writers, Workers Alliance / Hunger March organizers, and others. Most are
 * public-domain images; Tillie Olsen and Sam Darcy are CC BY-SA 4.0, Elaine
 * Black Yoneda is CC BY 2.0, and the Herbert Benjamin crop is low-resolution
 * fair use (see CREDITS-nonfree.md).
 *
 * Each record is matched by slug (several candidates), then by exact name /
 * alias. The photo is set ONLY when the prisoner currently has none — existing
 * photos are never overwritten. Idempotent and safe to re-run.
 */
final class AttachThirtiesLeftPhotos extends Command
{
    protected $signature = 'prisoners:attach-thirties-left-photos';

    protected $description = 'Attach 1930s labor/cultural/radical prisoner portraits (fill-if-empty)';

    /** @var array<int,array{file:string,slugs:string[],names:string[]}> */
    private const ENTRIES = [
        ['file' => 'caroline-decker.jpg',
         'slugs' => ['caroline-decker'],
         'names' => ['Caroline Decker']],
        ['file' => 'pat-chambers.jpg',
         'slugs' => ['pat-chambers', 'patrick-chambers'],
         'names' => ['Pat Chambers', 'Patrick Chambers']],
        ['file' => 'crisanto-evangelista.jpg',
         'slugs' => ['crisanto-evangelista'],
         'names' => ['Crisanto Evangelista']],
        ['file' => 'waldo-frank.jpg',
         'slugs' => ['waldo-frank'],
         'names' => ['Waldo Frank']],
        ['file' => 'aunt-molly-jackson.jpg',
         'slugs' => ['aunt-molly-jackson', 'molly-jackson', 'mary-magdalene-garland'],
         'names' => ['Aunt Molly Jackson', 'Molly Jackson', 'Mary Magdalene Garland']],
        ['file' => 'tillie-olsen.jpg',
         'slugs' => ['tillie-olsen'],
         'names' => ['Tillie Olsen']],
        ['file' => 'theodore-dreiser.jpg',
         'slugs' => ['theodore-dreiser'],
         'names' => ['Theodore Dreiser']],
        ['file' => 'david-lasser.jpg',
         'slugs' => ['david-lasser'],
         'names' => ['David Lasser']],
        ['file' => 'elaine-black-yoneda.jpg',
         'slugs' => ['elaine-black-yoneda', 'elaine-yoneda', 'elaine-black'],
         'names' => ['Elaine Black Yoneda', 'Elaine Black', 'Elaine Yoneda']],
        ['file' => 'louis-budenz.jpg',
         'slugs' => ['louis-f-budenz', 'louis-budenz'],
         'names' => ['Louis F. Budenz', 'Louis Budenz']],
        ['file' => 'sam-darcy.jpg',
         'slugs' => ['sam-darcy', 'samuel-adams-darcy', 'samuel-darcy'],
         'names' => ['Sam Darcy', 'Samuel Adams Darcy', 'Samuel Darcy']],
        ['file' => 'herbert-benjamin.jpg',
         'slugs' => ['herbert-benjamin'],
         'names' => ['Herbert Benjamin']],
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
