<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches portraits from the July 2026 labor-history batch (Centralia 1919,
 * Matewan, Sweet trials, Gastonia 1929, New Bedford 1928, Greco-Carrillo).
 *
 * Every identification is caption-certified: the Centralia crops follow the
 * UW archival caption's explicit row-by-row left-to-right order; Ed Chambers
 * is the left figure per the NPS caption; Henry Sweet is far left in the
 * Burton Historical Collection photo whose caption lists the four men in
 * image order (Darrow, unmistakable, anchors the direction at far right);
 * John Porter is the portrait inside the "Fight to Free Comrade Porter!"
 * clipping; Greco and Carrillo each have individually captioned Italian-army
 * portraits in the January 1928 Labor Defender; Amy Schechter is named by
 * the press caption on her Gastonia booking photograph.
 *
 * Free images are credited in CREDITS-wikipedia.md, the rest (photos/nonfree/)
 * in CREDITS-nonfree.md.
 *
 * Fill-if-empty: the photo is set ONLY when the prisoner currently has none.
 * Idempotent and safe to re-run.
 */
final class AttachLaborHistoryPhotos extends Command
{
    protected $signature = 'prisoners:attach-labor-history-photos';

    protected $description = 'Attach labor-history batch portraits (fill-if-empty)';

    /** @var array<int,array{file:string,slugs:string[],names:string[]}> */
    private const ENTRIES = [
        // Centralia 1919 defendants, cropped from the 1921 A. C. Girard group
        // photograph (run prisoners:merge-duplicates for the two Bland
        // full-name duplicates before attaching).
        ['file' => 'bert-bland.jpg', 'slugs' => ['bert-bland'], 'names' => ['Bert Bland']],
        ['file' => 'john-lamb.jpg', 'slugs' => ['john-lamb'], 'names' => ['John Lamb']],
        ['file' => 'britt-smith.jpg', 'slugs' => ['britt-smith'], 'names' => ['Britt Smith']],
        ['file' => 'oc-bland.jpg', 'slugs' => ['oc-bland'], 'names' => ['O.C. Bland']],
        ['file' => 'ray-becker.jpg', 'slugs' => ['ray-becker'], 'names' => ['Ray Becker']],
        ['file' => 'eugene-barnett.jpg', 'slugs' => ['eugene-barnett'], 'names' => ['Eugene Barnett']],
        ['file' => 'ed-chambers.jpg', 'slugs' => ['ed-chambers'], 'names' => ['Ed Chambers']],
        ['file' => 'john-porter.jpg', 'slugs' => ['john-porter'], 'names' => ['John Porter']],
        ['file' => 'sophie-melvin.jpg', 'slugs' => ['sophie-melvin'], 'names' => ['Sophie Melvin']],
        ['file' => 'vera-buch.jpg', 'slugs' => ['vera-buch'], 'names' => ['Vera Buch']],
        ['file' => 'calogero-greco.jpg', 'slugs' => ['calogero-greco'], 'names' => ['Calogero Greco']],
        ['file' => 'donato-carrillo.jpg', 'slugs' => ['donato-carrillo'], 'names' => ['Donato Carrillo']],
        ['file' => 'nonfree/henry-sweet.jpg', 'slugs' => ['henry-sweet'], 'names' => ['Henry Sweet']],
        ['file' => 'nonfree/mary-donovan-hapgood.jpg', 'slugs' => ['mary-donovan-hapgood'], 'names' => ['Mary Donovan Hapgood']],
        ['file' => 'nonfree/eulalia-mendes.jpg', 'slugs' => ['eulalia-mendes'], 'names' => ['Eulalia Mendes']],
        ['file' => 'nonfree/augusto-gonzales-pinto.jpg', 'slugs' => ['augusto-gonzales-pinto'], 'names' => ['Augusto Gonzales Pinto']],
        ['file' => 'nonfree/amy-schechter.jpg', 'slugs' => ['amy-schechter'], 'names' => ['Amy Schechter']],
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
