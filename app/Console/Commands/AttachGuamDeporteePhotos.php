<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches the Ortigas Foundation Library photograph "Insurgent prisoners
 * deported to the island of Guam" (No. 29, published 1901 — public domain,
 * CREDITS-wikipedia.md) as a shared group image across the January 1901
 * Guam-deportee profiles. The print names only a few figures (M. Llanera,
 * M. Hison, P. Ocampo, L. Almeida), so NO individual crops are made — the
 * archive gives no positional key for the rest. The later "eleven from
 * Ilocos Norte aboard the U.S.S. Solace" batch is deliberately excluded:
 * those men cannot be in this picture.
 *
 * Also attaches the Chaim Leib Weinberg newsprint portrait (photos/nonfree/,
 * CREDITS-nonfree.md), the lead image of libcom.org's posting of his memoir
 * "Forty Years in the Struggle", titled with his name.
 *
 * Fill-if-empty: the photo is set ONLY when the prisoner currently has none
 * (Mabini, Ricarte, Llanera, Trías, Ocampo and del Pilar keep their existing
 * individual portraits). Idempotent and safe to re-run.
 */
final class AttachGuamDeporteePhotos extends Command
{
    protected $signature = 'prisoners:attach-guam-deportee-photos';

    protected $description = 'Attach the 1901 Guam deportee group photo + Weinberg portrait (fill-if-empty)';

    private const GROUP_FILE = 'guam-deportees.jpg';

    /** January 1901 deportation (the group the photograph documents). */
    private const GROUP_SLUGS = [
        'alipio-tecson', 'anastacio-carmona', 'antonio-reyes', 'apolinario-mabini',
        'artemio-ricarte', 'bartolome-de-la-rosa', 'cornelio-riquiestas',
        'doroteo-espino', 'esteban-consortes', 'fabian-villaruel',
        'francisco-de-los-santos', 'hermogenes-plata', 'igmidio-de-jesus',
        'jose-buenaventura', 'jose-florante', 'jose-mata', 'juan-mauricio',
        'juan-villarino', 'julian-gerona', 'lucas-camerino', 'lucino-almeida',
        'macario-de-ocampo', 'mariano-barruga', 'mariano-llanera', 'mariano-trias',
        'maximino-hizon', 'maximino-trias', 'norberto-dimayuga', 'pablo-ocampo',
        'pedro-cubarrubias', 'pio-del-pilar', 'pio-varican', 'silvestre-legaspi',
        'simon-tecson',
    ];

    /** @var array<int,array{file:string,slugs:string[],names:string[]}> */
    private const PORTRAITS = [
        ['file' => 'nonfree/chaim-weinberg.jpg', 'slugs' => ['chaim-weinberg'], 'names' => ['Chaim Weinberg', 'Chaim Leib Weinberg']],
    ];

    public function handle(): int
    {
        Storage::disk('public')->makeDirectory('prisoners');

        $linked = 0;
        $skipped = 0;
        $missing = [];

        // --- Shared group photograph -------------------------------------
        $src = database_path('data/photos/'.self::GROUP_FILE);
        if (! is_file($src)) {
            $this->error("Source image not found: {$src}");

            return self::FAILURE;
        }
        $relative = 'prisoners/'.self::GROUP_FILE;
        Storage::disk('public')->put($relative, (string) file_get_contents($src));

        foreach (self::GROUP_SLUGS as $slug) {
            $prisoner = Prisoner::withUnderReview()->where('slug', $slug)->first();
            if (! $prisoner) {
                $missing[] = $slug;

                continue;
            }
            if (! empty($prisoner->photo)) {
                $skipped++;

                continue;
            }
            $prisoner->photo = $relative;
            $prisoner->save();
            $this->info("Linked group photo for {$prisoner->name}.");
            $linked++;
        }

        // --- Individual portraits ----------------------------------------
        foreach (self::PORTRAITS as $e) {
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
