<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches public-domain portraits for 19th-century political prisoners —
 * abolitionists, Anti-Rent War and Coxey's Army figures, fugitive-slave-case
 * defendants, and Civil-War-era Unionists and Confederates. All images are
 * pre-1900 works in the public domain; provenance is recorded in
 * CREDITS-nonfree.md.
 *
 * Each record is matched by slug (several candidates), then by exact name /
 * alias. The photo is set ONLY when the prisoner currently has none — existing
 * photos are never overwritten. Idempotent and safe to re-run.
 */
final class AttachNineteenthCenturyPhotos extends Command
{
    protected $signature = 'prisoners:attach-19th-century-photos';

    protected $description = 'Attach public-domain 19th-century prisoner portraits to records with no photo (fill-if-empty)';

    /** @var array<int,array{file:string,slugs:string[],names:string[]}> */
    private const ENTRIES = [
        ['file' => 'parker-pillsbury.jpg',
         'slugs' => ['parker-pillsbury'],
         'names' => ['Parker Pillsbury']],
        ['file' => 'stephen-foster.jpg',
         'slugs' => ['stephen-symonds-foster', 'stephen-foster', 's-s-foster'],
         'names' => ['Stephen Symonds Foster', 'Stephen S. Foster', 'Stephen Foster']],
        ['file' => 'big-thunder.jpg',
         'slugs' => ['smith-a-boughton', 'smith-boughton', 'big-thunder'],
         'names' => ['Smith A. Boughton', 'Smith Boughton', 'Big Thunder', 'Smith A. Boughton (Big Thunder)']],
        ['file' => 'robert-morris.jpg',
         'slugs' => ['robert-morris'],
         'names' => ['Robert Morris']],
        ['file' => 'castner-hanway.jpg',
         'slugs' => ['castner-hanway'],
         'names' => ['Castner Hanway']],
        ['file' => 'francis-lubbock.jpg',
         'slugs' => ['francis-richard-lubbock', 'francis-lubbock', 'francis-r-lubbock'],
         'names' => ['Francis Richard Lubbock', 'Francis R. Lubbock', 'Francis Lubbock']],
        ['file' => 'burton-harrison.jpg',
         'slugs' => ['burton-norvell-harrison', 'burton-harrison'],
         'names' => ['Burton Norvell Harrison', 'Burton N. Harrison', 'Burton Harrison']],
        ['file' => 'alexander-stephens.jpg',
         'slugs' => ['alexander-h-stephens', 'alexander-stephens', 'alexander-hamilton-stephens'],
         'names' => ['Alexander H. Stephens', 'Alexander Hamilton Stephens', 'Alexander Stephens']],
        ['file' => 'james-mason.jpg',
         'slugs' => ['james-murray-mason', 'james-m-mason', 'james-mason'],
         'names' => ['James Murray Mason', 'James M. Mason', 'James Mason']],
        ['file' => 'john-minor-botts.jpg',
         'slugs' => ['john-minor-botts', 'john-m-botts'],
         'names' => ['John Minor Botts', 'John M. Botts']],
        ['file' => 'carl-browne.jpg',
         'slugs' => ['carl-browne'],
         'names' => ['Carl Browne']],
        ['file' => 'jacob-coxey.jpg',
         'slugs' => ['jacob-s-coxey', 'jacob-coxey', 'jacob-sechler-coxey'],
         'names' => ['Jacob S. Coxey', 'Jacob Sechler Coxey', 'Jacob Coxey']],
        ['file' => 'anthony-burns.jpg',
         'slugs' => ['anthony-burns'],
         'names' => ['Anthony Burns']],
        ['file' => 'parson-brownlow.jpg',
         'slugs' => ['william-g-brownlow', 'william-gannaway-brownlow', 'william-brownlow', 'parson-brownlow'],
         'names' => ['William G. Brownlow', 'William Gannaway Brownlow', 'William G. "Parson" Brownlow', 'Parson Brownlow']],
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
