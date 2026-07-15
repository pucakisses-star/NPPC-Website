<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches portraits for peace-movement, Native-rights, and related political
 * prisoners. Theodore Kaczynski's arrest photo is public domain; the rest are
 * low-resolution fair-use press/obituary images (see CREDITS-nonfree.md).
 *
 * Each record is matched by slug (several candidates), then by exact name /
 * alias. The photo is set ONLY when the prisoner currently has none — existing
 * photos are never overwritten. Idempotent and safe to re-run.
 */
final class AttachPeaceNativePhotos extends Command
{
    protected $signature = 'prisoners:attach-peace-native-photos';

    protected $description = 'Attach peace-movement / Native-rights prisoner portraits (fill-if-empty)';

    /** @var array<int,array{file:string,slugs:string[],names:string[]}> */
    private const ENTRIES = [
        ['file' => 'theodore-kaczynski.jpg',
         'slugs' => ['theodore-kaczynski', 'ted-kaczynski'],
         'names' => ['Theodore Kaczynski', 'Ted Kaczynski']],
        ['file' => 'frank-lamere.jpg',
         'slugs' => ['frank-lamere'],
         'names' => ['Frank LaMere']],
        ['file' => 'helen-john.jpg',
         'slugs' => ['helen-john'],
         'names' => ['Helen John']],
        ['file' => 'sharon-day.jpg',
         'slugs' => ['sharon-day'],
         'names' => ['Sharon Day']],
        ['file' => 'mary-dann.jpg',
         'slugs' => ['mary-dann'],
         'names' => ['Mary Dann']],
        ['file' => 'carrie-dann.jpg',
         'slugs' => ['carrie-dann'],
         'names' => ['Carrie Dann']],
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
