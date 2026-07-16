<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches portraits recovered from Wikipedia article infobox images for
 * people whose originally-supplied sources were JavaScript-only. Rosa Lee
 * Ingram's drawing is public domain; the others are low-resolution non-free
 * images used under the site's fair-use rationale (see CREDITS-nonfree.md).
 *
 * Notably includes the correct Little Rock Daisy Bates (an earlier batch had
 * mistakenly matched the unrelated Australian Daisy Bates).
 *
 * Each record is matched by slug (several candidates), then by exact name /
 * alias. The photo is set ONLY when the prisoner currently has none — existing
 * photos are never overwritten. Idempotent and safe to re-run.
 */
final class AttachRecoveredPhotos2 extends Command
{
    protected $signature = 'prisoners:attach-recovered-photos-2';

    protected $description = 'Attach portraits recovered from Wikipedia infobox images (fill-if-empty)';

    /** @var array<int,array{file:string,slugs:string[],names:string[]}> */
    private const ENTRIES = [
        ['file' => 'daisy-bates.jpg',
         'slugs' => ['daisy-bates'],
         'names' => ['Daisy Bates']],
        ['file' => 'septima-clark.jpg',
         'slugs' => ['septima-poinsette-clark', 'septima-clark'],
         'names' => ['Septima Poinsette Clark', 'Septima Clark']],
        ['file' => 'anna-mae-aquash.jpg',
         'slugs' => ['anna-mae-aquash', 'anna-mae-pictou-aquash'],
         'names' => ['Anna Mae Aquash', 'Anna Mae Pictou-Aquash']],
        ['file' => 'anne-braden.jpg',
         'slugs' => ['anne-braden'],
         'names' => ['Anne Braden']],
        ['file' => 'gene-sharp.jpg',
         'slugs' => ['gene-sharp'],
         'names' => ['Gene Sharp']],
        ['file' => 'rosa-lee-ingram.jpg',
         'slugs' => ['rosa-lee-ingram'],
         'names' => ['Rosa Lee Ingram']],
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
