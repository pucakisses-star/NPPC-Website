<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches portraits recovered from the confirmed-identity photo-research pass —
 * contemporary movement / protest / immigration defendants. Six are cropped
 * from the San Bernardino County Sheriff "Operation Accountability" booking
 * release (public record); Holger Jaenicke's is public domain (Flickr); the
 * rest are low-resolution non-free press images used under the site's fair-use
 * rationale (see CREDITS-nonfree.md).
 *
 * Each record is matched by slug then exact name / alias. The photo is set ONLY
 * when the prisoner currently has none — existing photos are never overwritten.
 * Idempotent and safe to re-run.
 */
final class AttachIdentityResearchPhotos extends Command
{
    protected $signature = 'prisoners:attach-identity-research-photos';

    protected $description = 'Attach confirmed-identity research portraits (fill-if-empty)';

    /** @var array<int,array{file:string,slugs:string[],names:string[]}> */
    private const ENTRIES = [
        ['file' => 'aditya-wahyu-harsono.jpg',
         'slugs' => ['aditya-wahyu-harsono'],
         'names' => ['Aditya Wahyu Harsono']],
        ['file' => 'alejandro-orellana.jpg',
         'slugs' => ['alejandro-orellana'],
         'names' => ['Alejandro Orellana']],
        ['file' => 'alireza-doroudi.jpg',
         'slugs' => ['alireza-doroudi'],
         'names' => ['Alireza Doroudi']],
        ['file' => 'arden-wells.jpg',
         'slugs' => ['arden-wells'],
         'names' => ['Arden Wells']],
        ['file' => 'ashton-l-howard.jpg',
         'slugs' => ['ashton-l-howard'],
         'names' => ['Ashton L. Howard']],
        ['file' => 'brian-jordan-bartels.jpg',
         'slugs' => ['brian-jordan-bartels'],
         'names' => ['Brian Jordan Bartels']],
        ['file' => 'christopher-alan-west.jpg',
         'slugs' => ['christopher-alan-west'],
         'names' => ['Christopher Alan West']],
        ['file' => 'cortez-lamont-edwards.jpg',
         'slugs' => ['cortez-lamont-edwards'],
         'names' => ['Cortez Lamont Edwards']],
        ['file' => 'dajon-lengyel.jpg',
         'slugs' => ['dajon-lengyel'],
         'names' => ['Da\'Jon Lengyel']],
        ['file' => 'daniel-jongyon-park.jpg',
         'slugs' => ['daniel-jongyon-park'],
         'names' => ['Daniel Jongyon Park']],
        ['file' => 'david-chavez.jpg',
         'slugs' => ['david-chavez'],
         'names' => ['David Chavez']],
        ['file' => 'edwin-pena.jpg',
         'slugs' => ['edwin-pena'],
         'names' => ['Edwin Pena']],
        ['file' => 'fernando-lopez.jpg',
         'slugs' => ['fernando-lopez'],
         'names' => ['Fernando Lopez']],
        ['file' => 'gabriel-agard-berryhill.jpg',
         'slugs' => ['gabriel-agard-berryhill'],
         'names' => ['Gabriel Agard-Berryhill']],
        ['file' => 'holger-isabelle-janicke.jpg',
         'slugs' => ['holger-isabelle-janicke'],
         'names' => ['Holger Isabelle Jänicke']],
        ['file' => 'jack-mazurek.jpg',
         'slugs' => ['jack-mazurek'],
         'names' => ['Jack Mazurek']],
        ['file' => 'jonathan-zou.jpg',
         'slugs' => ['jonathan-zou'],
         'names' => ['Jonathan Zou']],
        ['file' => 'keonne-rodriguez.jpg',
         'slugs' => ['keonne-rodriguez'],
         'names' => ['Keonne Rodriguez']],
        ['file' => 'mike-forcia.jpg',
         'slugs' => ['mike-forcia'],
         'names' => ['Mike Forcia']],
        ['file' => 'stephanie-amesquita.jpg',
         'slugs' => ['stephanie-amesquita'],
         'names' => ['Stephanie Amesquita']],
        ['file' => 'vanessa-carrasco.jpg',
         'slugs' => ['vanessa-carrasco'],
         'names' => ['Vanessa Carrasco']],
        ['file' => 'wendy-lujan.jpg',
         'slugs' => ['wendy-lujan'],
         'names' => ['Wendy Lujan']],
        ['file' => 'tia-pugh.jpg',
         'slugs' => ['tia-pugh'],
         'names' => ['Tia Pugh']],
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
