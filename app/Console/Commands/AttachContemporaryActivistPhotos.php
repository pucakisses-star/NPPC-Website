<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches portraits for a batch of contemporary activists — climate
 * "Valve Turners", Native-rights and environmental defenders, BLM-era
 * defendants, and whistleblowers. Casey Camp-Horinek and Jordan Halliday are
 * free (Creative Commons via Wikimedia Commons); the rest are low-resolution
 * non-free press/obituary images used under the site's fair-use rationale
 * (see CREDITS-nonfree.md).
 *
 * Each record is matched by slug (several candidates), then by exact name /
 * alias. The photo is set ONLY when the prisoner currently has none — existing
 * photos are never overwritten. Idempotent and safe to re-run.
 */
final class AttachContemporaryActivistPhotos extends Command
{
    protected $signature = 'prisoners:attach-contemporary-activist-photos';

    protected $description = 'Attach contemporary activist / climate / BLM-era prisoner portraits (fill-if-empty)';

    /** @var array<int,array{file:string,slugs:string[],names:string[]}> */
    private const ENTRIES = [
        ['file' => 'regina-brave.jpg',
         'slugs' => ['regina-brave'],
         'names' => ['Regina Brave']],
        ['file' => 'klee-benally.jpg',
         'slugs' => ['klee-benally'],
         'names' => ['Klee Benally']],
        ['file' => 'casey-camp-horinek.jpg',
         'slugs' => ['casey-camp-horinek'],
         'names' => ['Casey Camp-Horinek', 'Casey Camp Horinek']],
        ['file' => 'tom-goldtooth.jpg',
         'slugs' => ['tom-b-k-goldtooth', 'tom-goldtooth'],
         'names' => ['Tom B. K. Goldtooth', 'Tom B.K. Goldtooth', 'Tom Goldtooth']],
        ['file' => 'jordan-halliday.jpg',
         'slugs' => ['jordan-halliday'],
         'names' => ['Jordan Halliday']],
        ['file' => 'annette-klapstein.jpg',
         'slugs' => ['annette-klapstein'],
         'names' => ['Annette Klapstein']],
        ['file' => 'emily-johnston.jpg',
         'slugs' => ['emily-johnston'],
         'names' => ['Emily Johnston']],
        ['file' => 'ken-ward.jpg',
         'slugs' => ['ken-ward'],
         'names' => ['Ken Ward']],
        ['file' => 'leonard-higgins.jpg',
         'slugs' => ['leonard-higgins'],
         'names' => ['Leonard Higgins']],
        ['file' => 'lynne-greenwald.jpg',
         'slugs' => ['lynne-greenwald', 'lynne-greenewald'],
         'names' => ['Lynne Greenwald', 'Lynne Greenewald']],
        ['file' => 'darius-stewart.jpg',
         'slugs' => ['darius-stewart'],
         'names' => ['Darius Stewart']],
        ['file' => 'cece-mcdonald.jpg',
         'slugs' => ['cece-mcdonald'],
         'names' => ['CeCe McDonald', 'Cece McDonald', 'Chrishaun Reed McDonald']],
        ['file' => 'marissa-alexander.jpg',
         'slugs' => ['marissa-alexander'],
         'names' => ['Marissa Alexander']],
        ['file' => 'rakem-balogun.jpg',
         'slugs' => ['rakem-balogun'],
         'names' => ['Rakem Balogun', 'Christopher Daniels']],
        ['file' => 'thomas-drake.jpg',
         'slugs' => ['thomas-a-drake', 'thomas-drake'],
         'names' => ['Thomas A. Drake', 'Thomas Drake']],
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
