<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Re-crops / re-sources a batch of previously attached prisoner portraits that
 * a crop audit flagged as badly framed:
 *
 *   - Tight zoom crops that cut off the head (edin-enamorado's problem).
 *   - Driver's-licence / ID photos left with white letterbox bars.
 *   - Scene, group, screenshot, or photo-of-a-framed-print images where the
 *     subject's face was tiny or off-centre.
 *
 * Five entries were re-sourced from Wikimedia Commons (Dreiser, Lolita Lebrón,
 * Yuri Kochiyama, Elaine Black Yoneda, Marissa Alexander); four tight crops
 * were re-fetched from their original source articles and re-cropped with the
 * full head visible (Edin Enamorado, Yorie von Kahl, Linwood Kaine, Thomas
 * Jurgens); the rest were re-cropped from the existing source. See
 * CREDITS-nonfree.md for provenance.
 *
 * Unlike the fill-if-empty attach commands, this command DELIBERATELY
 * OVERWRITES the existing photo — that is the whole point of a re-crop. It only
 * touches the slugs listed below, so the other ~194 portraits are untouched.
 * Idempotent: re-running simply re-writes the same corrected files.
 */
final class RecropPrisonerPhotos extends Command
{
    protected $signature = 'prisoners:recrop-photos {--dry-run : List what would change without writing}';

    protected $description = 'Overwrite badly-cropped prisoner portraits with corrected versions';

    /** @var array<int,array{file:string,slugs:string[],names:string[]}> */
    private const ENTRIES = [
        ['file' => 'edin-enamorado.jpg', 'slugs' => ['edin-alex-enamorado', 'edin-enamorado'], 'names' => ['Edin Alex Enamorado', 'Edin Enamorado']],
        ['file' => 'yorie-von-kahl.jpg', 'slugs' => ['yorie-von-kahl'], 'names' => ['Yorie von Kahl', 'Yorie Von Kahl', 'Yorie Kahl']],
        ['file' => 'linwood-kaine.jpg', 'slugs' => ['linwood-kaine'], 'names' => ['Linwood Kaine', 'Linwood "Woody" Kaine']],
        ['file' => 'thomas-jurgens.jpg', 'slugs' => ['thomas-jurgens'], 'names' => ['Thomas Jurgens', 'Thomas Webb Jurgens']],
        ['file' => 'edwin-pena.jpg', 'slugs' => ['edwin-pena'], 'names' => ['Edwin Pena', 'Edwin Peña']],
        ['file' => 'elaine-black-yoneda.jpg', 'slugs' => ['elaine-black-yoneda'], 'names' => ['Elaine Black Yoneda']],
        ['file' => 'fernando-lopez.jpg', 'slugs' => ['fernando-lopez'], 'names' => ['Fernando Lopez', 'Fernando López']],
        ['file' => 'francisco-torres.jpg', 'slugs' => ['francisco-torres'], 'names' => ['Francisco Torres', 'Francisco "Cisco" Torres']],
        ['file' => 'helen-john.jpg', 'slugs' => ['helen-john'], 'names' => ['Helen John']],
        ['file' => 'holger-isabelle-janicke.jpg', 'slugs' => ['holger-isabelle-janicke'], 'names' => ['Holger Isabelle Janicke', 'Isabelle Janicke']],
        ['file' => 'lolita-lebron.jpg', 'slugs' => ['lolita-lebron'], 'names' => ['Lolita Lebrón', 'Lolita Lebron']],
        ['file' => 'marissa-alexander.jpg', 'slugs' => ['marissa-alexander'], 'names' => ['Marissa Alexander']],
        ['file' => 'stephanie-amesquita.jpg', 'slugs' => ['stephanie-amesquita'], 'names' => ['Stephanie Amesquita', 'Stephanie Amésquita']],
        ['file' => 'theodore-dreiser.jpg', 'slugs' => ['theodore-dreiser'], 'names' => ['Theodore Dreiser']],
        ['file' => 'vanessa-carrasco.jpg', 'slugs' => ['vanessa-carrasco'], 'names' => ['Vanessa Carrasco']],
        ['file' => 'wendy-lujan.jpg', 'slugs' => ['wendy-lujan'], 'names' => ['Wendy Lujan', 'Wendy Luján']],
        ['file' => 'yuri-kochiyama.jpg', 'slugs' => ['yuri-kochiyama'], 'names' => ['Yuri Kochiyama']],
    ];

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        Storage::disk('public')->makeDirectory('prisoners');

        $updated = 0;
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

            $relative = 'prisoners/'.$e['file'];

            if ($dry) {
                $this->line("would recrop {$prisoner->name}: {$prisoner->photo} → {$relative}");

                continue;
            }

            Storage::disk('public')->put($relative, (string) file_get_contents($src));
            $prisoner->photo = $relative;
            $prisoner->save();
            $this->info("Recropped photo for {$prisoner->name}.");
            $updated++;
        }

        if ($updated > 0) {
            Cache::forget(PrisonerApiController::cacheKey());
        }

        $this->info("\nDone. Recropped={$updated}.");
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
