<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches public-domain portraits for Philippine Revolution leaders
 * imprisoned or exiled under U.S. rule, plus Progressive-era U.S. free-speech
 * and labor prisoners (Ida Craddock; the Western Federation of Miners'
 * Haywood–Moyer–Pettibone defendants). All images are early-1900s works in the
 * public domain; provenance is recorded in CREDITS-nonfree.md.
 *
 * Each record is matched by slug (several candidates), then by exact name /
 * alias. The photo is set ONLY when the prisoner currently has none — existing
 * photos are never overwritten. Idempotent and safe to re-run.
 */
final class AttachPhilippineAndLaborPhotos extends Command
{
    protected $signature = 'prisoners:attach-philippine-labor-photos';

    protected $description = 'Attach public-domain Philippine-Revolution and Progressive-era prisoner portraits (fill-if-empty)';

    /** @var array<int,array{file:string,slugs:string[],names:string[]}> */
    private const ENTRIES = [
        ['file' => 'emilio-aguinaldo.jpg',
         'slugs' => ['emilio-aguinaldo'],
         'names' => ['Emilio Aguinaldo']],
        ['file' => 'apolinario-mabini.jpg',
         'slugs' => ['apolinario-mabini'],
         'names' => ['Apolinario Mabini']],
        ['file' => 'artemio-ricarte.jpg',
         'slugs' => ['artemio-ricarte'],
         'names' => ['Artemio Ricarte']],
        ['file' => 'mariano-llanera.jpg',
         'slugs' => ['mariano-llanera'],
         'names' => ['Mariano Llanera']],
        ['file' => 'mariano-trias.jpg',
         'slugs' => ['mariano-trias'],
         'names' => ['Mariano Trías', 'Mariano Trias']],
        ['file' => 'pablo-ocampo.jpg',
         'slugs' => ['pablo-ocampo'],
         'names' => ['Pablo Ocampo']],
        ['file' => 'pio-del-pilar.jpg',
         'slugs' => ['pio-del-pilar'],
         'names' => ['Pío del Pilar', 'Pio del Pilar']],
        ['file' => 'ida-craddock.jpg',
         'slugs' => ['ida-craddock'],
         'names' => ['Ida Craddock']],
        ['file' => 'steve-adams.jpg',
         'slugs' => ['steve-adams'],
         'names' => ['Steve Adams', 'Stephen Adams']],
        ['file' => 'charles-moyer.jpg',
         'slugs' => ['charles-h-moyer', 'charles-moyer'],
         'names' => ['Charles H. Moyer', 'Charles Moyer']],
        ['file' => 'george-pettibone.jpg',
         'slugs' => ['george-pettibone', 'george-a-pettibone'],
         'names' => ['George Pettibone', 'George A. Pettibone']],
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
