<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches portraits of Maurice Braverman and Philip Frankfeld, cropped from
 * the April 4, 1952 press photograph of the sentenced Baltimore Smith Act
 * defendants being led from federal court. The Washington Area Spark caption
 * places them precisely ("Between two agents at the left are Maurice
 * Braverman (left), an attorney; and Philip Frankfeld"); the other two
 * defendants in the frame (Wood, Meyers) face away from the camera and are
 * deliberately NOT cropped. Non-free (photos/nonfree/, CREDITS-nonfree.md).
 *
 * Run prisoners:merge-duplicates for the phil-frankfeld duplicate first.
 *
 * Fill-if-empty: the photo is set ONLY when the prisoner currently has none.
 * Idempotent and safe to re-run.
 */
final class AttachBaltimoreSmithActPhotos extends Command
{
    protected $signature = 'prisoners:attach-baltimore-smith-act-photos';

    protected $description = 'Attach 1952 Baltimore Smith Act portraits (fill-if-empty)';

    /** @var array<int,array{file:string,slugs:string[],names:string[]}> */
    private const ENTRIES = [
        ['file' => 'nonfree/maurice-braverman.jpg', 'slugs' => ['maurice-braverman'], 'names' => ['Maurice Braverman', 'Maurice L. Braverman']],
        ['file' => 'nonfree/phil-frankfeld.jpg', 'slugs' => ['phil-frankfeld', 'philip-frankfeld'], 'names' => ['Phil Frankfeld', 'Philip Frankfeld']],
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
