<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches 9 portraits from the July 2026 photo-findability audit — the
 * "Tier 2" batch, sourced from press, obituary, and campaign coverage where
 * Wikipedia carried no portrait. All are non-free images (photos/nonfree/,
 * credited in CREDITS-nonfree.md) used at low resolution under the site's
 * fair-use rationale.
 *
 * Fill-if-empty: the photo is set ONLY when the prisoner currently has none.
 * Idempotent and safe to re-run.
 */
final class AttachAuditNewsPhotos extends Command
{
    protected $signature = 'prisoners:attach-audit-news-photos';

    protected $description = 'Attach Tier-2 audit portraits from press/campaign coverage (fill-if-empty)';

    /** @var array<int,array{file:string,slugs:string[],names:string[]}> */
    private const ENTRIES = [
        ['file' => 'frank-big-black-smith.jpg', 'slugs' => ['frank-big-black-smith'], 'names' => ['Frank Big Black Smith', 'Frank "Big Black" Smith', 'Frank Smith']],
        ['file' => 'georges-ibrahim-abdallah.jpg', 'slugs' => ['georges-ibrahim-abdallah'], 'names' => ['Georges Ibrahim Abdallah']],
        ['file' => 'john-fife.jpg', 'slugs' => ['john-fife'], 'names' => ['John Fife', 'Rev. John Fife']],
        ['file' => 'john-froines.jpg', 'slugs' => ['john-froines'], 'names' => ['John Froines']],
        ['file' => 'keith-lamar.jpg', 'slugs' => ['keith-lamar'], 'names' => ['Keith LaMar', 'Bomani Shakur']],
        ['file' => 'marshall-conway.jpg', 'slugs' => ['marshall-conway'], 'names' => ['Marshall Conway', 'Marshall "Eddie" Conway', 'Eddie Conway']],
        ['file' => 'scott-warren.jpg', 'slugs' => ['scott-warren'], 'names' => ['Scott Warren', 'Scott Daniel Warren']],
        ['file' => 'syed-fahad-hashmi.jpg', 'slugs' => ['syed-fahad-hashmi'], 'names' => ['Syed Fahad Hashmi', 'Fahad Hashmi']],
        ['file' => 'tre-arrow.jpg', 'slugs' => ['tre-arrow'], 'names' => ['Tre Arrow', 'Michael James Scarpitti']],
        ['file' => 'marlon-kautz.jpg', 'slugs' => ['marlon-kautz'], 'names' => ['Marlon Kautz', 'Marlon Scott Kautz']],
        ['file' => 'adele-maclean.jpg', 'slugs' => ['adele-maclean'], 'names' => ['Adele MacLean', 'Adele Maclean']],
        ['file' => 'savannah-patterson.jpg', 'slugs' => ['savannah-patterson'], 'names' => ['Savannah Patterson', 'Savannah D. Patterson']],
        ['file' => 'stanley-grant-phanor.jpg', 'slugs' => ['stanley-grant-phanor'], 'names' => ['Stanley Grant Phanor', 'Stanley Phanor']],
        ['file' => 'nelson-johnson.jpg', 'slugs' => ['nelson-johnson'], 'names' => ['Nelson Johnson', 'Rev. Nelson Johnson']],
        ['file' => 'ardeth-platte.jpg', 'slugs' => ['ardeth-platte'], 'names' => ['Ardeth Platte', 'Sister Ardeth Platte']],
        ['file' => 'bill-bichsel.jpg', 'slugs' => ['bill-bichsel'], 'names' => ['Bill Bichsel', 'William J. Bichsel', 'William Bichsel']],
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
