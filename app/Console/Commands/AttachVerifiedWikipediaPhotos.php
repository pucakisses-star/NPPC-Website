<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches 18 portraits found during the July 2026 photo-findability audit —
 * the "Tier 1" batch, where an English Wikipedia page for the prisoner was
 * verified to carry a usable portrait. Sixteen are freely licensed
 * (public domain / CC / KOGL, from Wikimedia Commons — see
 * CREDITS-wikipedia.md); Blanca Canales and George Jackson are non-free
 * images carried on English Wikipedia under a fair-use rationale and live in
 * photos/nonfree/ (see CREDITS-nonfree.md).
 *
 * Fill-if-empty: the photo is set ONLY when the prisoner currently has none.
 * Idempotent and safe to re-run.
 */
final class AttachVerifiedWikipediaPhotos extends Command
{
    protected $signature = 'prisoners:attach-verified-wikipedia-photos';

    protected $description = 'Attach Tier-1 audit portraits verified on Wikipedia (fill-if-empty)';

    /** @var array<int,array{file:string,slugs:string[],names:string[]}> */
    private const ENTRIES = [
        ['file' => 'akua-njeri.jpg', 'slugs' => ['akua-njeri'], 'names' => ['Akua Njeri', 'Deborah Johnson']],
        ['file' => 'andrew-mickel.jpg', 'slugs' => ['andrew-mickel'], 'names' => ['Andrew Mickel', 'Andrew Hampton McCrae Mickel']],
        ['file' => 'anne-timpson.jpg', 'slugs' => ['anne-timpson'], 'names' => ['Anne Timpson', 'Anne Burlak Timpson', 'Anne Burlak']],
        ['file' => 'calla-walsh.jpg', 'slugs' => ['calla-walsh'], 'names' => ['Calla Walsh', 'Calla Mairead Walsh']],
        ['file' => 'cleveland-sellers.jpg', 'slugs' => ['cleveland-sellers'], 'names' => ['Cleveland Sellers', 'Cleveland Sellers Jr.']],
        ['file' => 'ellen-moves-camp.jpg', 'slugs' => ['ellen-moves-camp'], 'names' => ['Ellen Moves Camp']],
        ['file' => 'filiberto-ojeda-rios.jpg', 'slugs' => ['filiberto-ojeda-rios'], 'names' => ['Filiberto Ojeda Rios', 'Filiberto Ojeda Ríos']],
        ['file' => 'joan-little.jpg', 'slugs' => ['joan-little'], 'names' => ['Joan Little', 'Joanne Little']],
        ['file' => 'john-boncore-hill.jpg', 'slugs' => ['john-boncore-hill'], 'names' => ['John Boncore Hill', 'Splitting the Sky', 'Dacajeweiah']],
        ['file' => 'kim-dae-jung.jpg', 'slugs' => ['kim-dae-jung'], 'names' => ['Kim Dae Jung', 'Kim Dae-jung']],
        ['file' => 'lamonica-mciver.jpg', 'slugs' => ['lamonica-mciver'], 'names' => ['LaMonica McIver']],
        ['file' => 'max-stanford.jpg', 'slugs' => ['max-stanford'], 'names' => ['Max Stanford', 'Muhammad Ahmad']],
        ['file' => 'rodolfo-gonzales.jpg', 'slugs' => ['rodolfo-gonzales'], 'names' => ['Rodolfo Gonzales', 'Rodolfo "Corky" Gonzales', 'Corky Gonzales']],
        ['file' => 'thomas-james-reddy.jpg', 'slugs' => ['thomas-james-reddy'], 'names' => ['Thomas James Reddy', 'T.J. Reddy', 'T. J. Reddy']],
        ['file' => 'walter-edward-fauntroy.jpg', 'slugs' => ['walter-edward-fauntroy'], 'names' => ['Walter Edward Fauntroy', 'Walter Fauntroy']],
        ['file' => 'wendy-yoshimura.jpg', 'slugs' => ['wendy-yoshimura'], 'names' => ['Wendy Yoshimura']],
        ['file' => 'nonfree/blanca-canales.jpg', 'slugs' => ['blanca-canales'], 'names' => ['Blanca Canales', 'Blanca Canales Torresola']],
        ['file' => 'nonfree/george-jackson.jpg', 'slugs' => ['george-jackson'], 'names' => ['George Jackson', 'George Lester Jackson']],
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
