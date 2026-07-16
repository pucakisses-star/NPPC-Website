<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches public-domain portraits recovered from Wikipedia article images for
 * people whose originally-supplied sources were JavaScript-only or dead — in
 * particular Alexander Howat and three of the 1941 Minneapolis Smith Act
 * defendants (Farrell Dobbs, Oscar Coover, Grace Carlson), plus Harry Van
 * Arsdale Jr. All are public domain (see CREDITS-nonfree.md).
 *
 * Each record is matched by slug (several candidates), then by exact name /
 * alias. The photo is set ONLY when the prisoner currently has none — existing
 * photos are never overwritten. Idempotent and safe to re-run.
 */
final class AttachRecoveredPhotos1 extends Command
{
    protected $signature = 'prisoners:attach-recovered-photos-1';

    protected $description = 'Attach public-domain portraits recovered from Wikipedia articles (fill-if-empty)';

    /** @var array<int,array{file:string,slugs:string[],names:string[]}> */
    private const ENTRIES = [
        ['file' => 'alexander-howat.jpg',
         'slugs' => ['alexander-howat'],
         'names' => ['Alexander Howat']],
        ['file' => 'harry-van-arsdale.jpg',
         'slugs' => ['harry-van-arsdale-jr', 'harry-van-arsdale'],
         'names' => ['Harry Van Arsdale Jr.', 'Harry Van Arsdale']],
        ['file' => 'farrell-dobbs.jpg',
         'slugs' => ['farrell-dobbs'],
         'names' => ['Farrell Dobbs']],
        ['file' => 'oscar-coover.jpg',
         'slugs' => ['oscar-coover'],
         'names' => ['Oscar Coover']],
        ['file' => 'grace-carlson.jpg',
         'slugs' => ['grace-carlson'],
         'names' => ['Grace Carlson']],
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
