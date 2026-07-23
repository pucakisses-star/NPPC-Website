<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Attaches copyrighted, non-free portraits (committed in
 * database/data/photos/nonfree/) that are retained at the site owner's
 * direction under a non-commercial fair-use / political-prisoner memorial
 * rationale. These are deliberately kept OUT of the freely-licensed Wikimedia
 * workflow (prisoners:attach-wikipedia-photos) — see CREDITS-nonfree.md for the
 * source and rights of each image.
 *
 * Matching is variant-aware and the command only fills entries that currently
 * HAVE NO PHOTO (it never overwrites). Prisoners not present (e.g. on a stale
 * local snapshot) are skipped with a warning, so it fills gaps wherever it runs.
 */
final class AttachNonfreePhotos extends Command
{
    protected $signature = 'prisoners:attach-nonfree-photos {--overwrite : Replace existing photos too} {--only= : Only process map entries whose name fragments contain this string}';

    protected $description = 'Attach non-free (fair-use) prisoner portraits to prisoners missing a photo';

    /** @var array<array{0:string[],1:string}> [name-match fragments, photo file in photos/nonfree/] */
    private array $map = [
        [['Juan Antonio Corretjer', 'Corretjer'], 'corretjer-juan-antonio.jpg'],
        [['Donald Cox', 'Don Cox'], 'cox-donald.jpg'],
        [['Pete O\'Neal'], 'oneal-pete.jpg'],
        [['Kenny "Zulu" Whitmore', 'Zulu Whitmore', 'Whitmore'], 'whitmore-kenny-zulu.jpg'],
        [['Ángel Rodríguez Cristóbal', 'Rodríguez Cristóbal', 'Cristóbal'], 'rodriguez-cristobal-angel.jpg'],
        [['Robert Robideau', 'Robideau'], 'robideau-robert.jpg'],
        [['Yorie von Kahl', 'Yorie Von Kahl'], 'yorie-von-kahl.jpg'],
        [['Bethany Terrill', 'Bethany Abigail Terrill'], 'bethany-terrill.jpg'],
        [['Saul Wellman'], 'saul-wellman.jpg'],
        [['David Gordon'], 'david-gordon.jpg'],
    ];

    public function handle(): int
    {
        $overwrite = (bool) $this->option('overwrite');
        $only = $this->option('only');
        $set = 0;
        $hadPhoto = 0;
        $missing = 0;

        foreach ($this->map as [$fragments, $file]) {
            // --only restricts the run to entries whose name fragments contain
            // the given string (case-insensitive), e.g. --only=Robideau.
            if ($only !== null && $only !== '') {
                $matchesFilter = false;
                foreach ($fragments as $frag) {
                    if (stripos($frag, $only) !== false) {
                        $matchesFilter = true;
                        break;
                    }
                }
                if (! $matchesFilter) {
                    continue;
                }
            }

            $prisoner = null;
            foreach ($fragments as $frag) {
                $prisoner = Prisoner::withUnderReview()->where('name', 'like', '%'.$frag.'%')->first();
                if ($prisoner) {
                    break;
                }
            }

            if (! $prisoner) {
                $this->warn('Not found, skipping: '.$fragments[0]);
                $missing++;

                continue;
            }

            if ($prisoner->photo && ! $overwrite) {
                $this->line("  Has photo, skipping: {$prisoner->name}");
                $hadPhoto++;

                continue;
            }

            $src = database_path('data/photos/nonfree/'.$file);
            if (! is_file($src)) {
                $this->warn("  Photo file missing: {$file}");
                $missing++;

                continue;
            }

            $ext = strtolower(pathinfo($src, PATHINFO_EXTENSION) ?: 'jpg');
            $path = 'prisoners/'.Str::slug($prisoner->name).'.'.$ext;
            Storage::disk('public')->put($path, (string) file_get_contents($src));
            $prisoner->photo = $path;
            $prisoner->save();
            $this->info("  {$prisoner->name} ← nonfree/{$file}");
            $set++;
        }

        $this->info("\nDone. Photos set={$set}  Already had={$hadPhoto}  Not found/missing={$missing}");

        return self::SUCCESS;
    }
}
