<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches portraits for early-20th-century labor / IWW / Socialist-era
 * political prisoners recovered from public archives. Almost all are
 * pre-1929 photographs and thus public domain (Wikimedia Commons, Library
 * of Congress, Leavenworth/Everett booking photographs); Taraknath Das's
 * Clara Sipprell portrait and Tom Mooney's Getty Museum portrait are the
 * exceptions (see CREDITS-nonfree.md).
 *
 * Several are cropped from labeled group plates (Irwin Tucker and Adolph
 * Germer from the 1919 Socialist defendants plate; Arturo Giovannitti from
 * the Ettor–Giovannitti–Caruso portrait; the Leavenworth mugshots to their
 * front view).
 *
 * Matched by slug then name; the photo is set ONLY when the prisoner has
 * none. Idempotent and safe to re-run.
 */
final class AttachHistoricalLaborPhotos extends Command
{
    protected $signature = 'prisoners:attach-historical-labor-photos';

    protected $description = 'Attach early-labor / IWW-era prisoner portraits (fill-if-empty)';

    /** @var array<int,array{file:string,slugs:string[],names:string[]}> */
    private const ENTRIES = [
        ['file' => 'adolph-frank-germer.jpg',
         'slugs' => ['adolph-frank-germer'],
         'names' => ['Adolph Frank Germer']],
        ['file' => 'agnes-smedley.jpg',
         'slugs' => ['agnes-smedley'],
         'names' => ['Agnes Smedley']],
        ['file' => 'arturo-giovannitti.jpg',
         'slugs' => ['arturo-giovannitti'],
         'names' => ['Arturo Giovannitti']],
        ['file' => 'ben-reitman.jpg',
         'slugs' => ['ben-reitman'],
         'names' => ['Ben Reitman']],
        ['file' => 'benjamin-gitlow.jpg',
         'slugs' => ['benjamin-gitlow'],
         'names' => ['Benjamin Gitlow']],
        ['file' => 'clayton-j-woodworth.jpg',
         'slugs' => ['clayton-j-woodworth'],
         'names' => ['Clayton J. Woodworth']],
        ['file' => 'dora-kelly-lewis.jpg',
         'slugs' => ['dora-kelly-lewis'],
         'names' => ['Dora Kelly Lewis']],
        ['file' => 'f-j-gallagher.jpg',
         'slugs' => ['f-j-gallagher'],
         'names' => ['F. J. Gallagher']],
        ['file' => 'harrison-george.jpg',
         'slugs' => ['harrison-george'],
         'names' => ['Harrison George']],
        ['file' => 'harry-wicks.jpg',
         'slugs' => ['harry-wicks'],
         'names' => ['Harry Wicks']],
        ['file' => 'irwin-st-john-tucker.jpg',
         'slugs' => ['irwin-st-john-tucker'],
         'names' => ['Irwin St. John Tucker']],
        ['file' => 'isaac-e-ferguson.jpg',
         'slugs' => ['isaac-e-ferguson'],
         'names' => ['Isaac E. Ferguson']],
        ['file' => 'jack-johnson.jpg',
         'slugs' => ['jack-johnson'],
         'names' => ['Jack Johnson']],
        ['file' => 'jacob-abrams.jpg',
         'slugs' => ['jacob-abrams'],
         'names' => ['Jacob Abrams']],
        ['file' => 'katherine-morey.jpg',
         'slugs' => ['katherine-morey'],
         'names' => ['Katherine Morey']],
        ['file' => 'marcus-mosiah-garvey-jr.jpg',
         'slugs' => ['marcus-mosiah-garvey-jr'],
         'names' => ['Marcus Mosiah Garvey Jr.']],
        ['file' => 'matthew-schmidt.jpg',
         'slugs' => ['matthew-schmidt'],
         'names' => ['Matthew Schmidt']],
        ['file' => 'michael-sapper.jpg',
         'slugs' => ['michael-sapper'],
         'names' => ['Michael Sapper']],
        ['file' => 'phineas-eastman.jpg',
         'slugs' => ['phineas-eastman'],
         'names' => ['Phineas Eastman']],
        ['file' => 'ralph-hosea-chaplin.jpg',
         'slugs' => ['ralph-hosea-chaplin'],
         'names' => ['Ralph Hosea Chaplin']],
        ['file' => 'taraknath-das.jpg',
         'slugs' => ['taraknath-das'],
         'names' => ['Taraknath Das']],
        ['file' => 'thomas-mooney.jpg',
         'slugs' => ['thomas-mooney'],
         'names' => ['Thomas Mooney']],
        ['file' => 'tom-tracy.jpg',
         'slugs' => ['tom-tracy'],
         'names' => ['Tom Tracy']],
        ['file' => 'wenzel-francik.jpg',
         'slugs' => ['wenzel-francik'],
         'names' => ['Wenzel Francik']],
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
            $this->warn('Not found ('.count($missing).'): '.implode(', ', $missing));
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
