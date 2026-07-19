<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches profile photos to the supplemental antifawatch Browse cohort (the
 * prisoners surfaced in PRs #1578/#1579) who had no photo. Each image was pulled
 * from that person's antifawatch.net database entry and committed under
 * database/data/photos/legacy/<file>. Every image was visually verified to be an
 * identifiable photo of the person; Tyler Maple's composite was cropped to his
 * mugshot.
 *
 * One searched name is NOT included: Steven Lopez -- his only antifawatch entry
 * with a real image is a shirtless street-arrest scene (face not clearly shown)
 * whose description matches a different, lesser charge, so identity could not be
 * confirmed.
 *
 * Matches prisoners by exact name. Only fills empty photo fields -- never
 * overwrites an existing photo. Idempotent.
 */
final class SetAntifawatchBrowsePhotos extends Command
{
    protected $signature = 'prisoners:set-antifawatch-browse-photos {--dry-run : Report what would change without writing}';

    protected $description = 'Attach committed antifawatch-sourced photos to the supplemental Browse-cohort prisoners lacking one';

    /** @var array<int, array{0:string,1:string}> [name, committed image filename] */
    private array $rows = [
            ['Corey Long', 'corey-long.jpg'],
            ['Cyril Lartigue', 'cyril-lartigue.jpg'],
            ['Garrett Ziegler', 'garrett-ziegler.jpg'],
            ['Jabari Davis', 'jabari-davis.jpg'],
            ['James Marshall', 'james-marshall.png'],
            ['John Dupree', 'john-dupree.png'],
            ['Kenyatta Huggins', 'kenyatta-huggins.png'],
            ['Mena Yousif', 'mena-yousif.jpg'],
            ['Oliva Hull', 'oliva-hull.png'],
            ['Ronald Raymond', 'ronald-raymond.png'],
            ['Samuel Frey', 'samuel-frey.png'],
            ['Semaj Pigram', 'semaj-pigram.png'],
            ['Shante Sutton', 'shante-sutton.jpg'],
            ['Talib Crump', 'talib-crump.jpg'],
            ['Tyler Maple', 'tyler-maple.jpg'],
            ['Walter Stewart', 'walter-stewart.png'],
            ['Zachary Karas', 'zachary-karas.png'],
        ];

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $set = $skipped = $notFound = $missingFile = 0;

        foreach ($this->rows as [$name, $file]) {
            $source = database_path("data/photos/legacy/{$file}");
            if (! is_file($source)) {
                $this->warn("  source image missing: database/data/photos/legacy/{$file}");
                $missingFile++;

                continue;
            }

            $prisoner = Prisoner::withoutGlobalScopes()->where('name', $name)->first();
            if (! $prisoner) {
                $this->warn("  NOT FOUND: {$name}");
                $notFound++;

                continue;
            }

            if ($prisoner->photo) {
                $this->line("  {$name} already has a photo — skipped.");
                $skipped++;

                continue;
            }

            $dest = "prisoners/{$file}";
            if ($dry) {
                $this->line("[dry-run] would set {$name} -> {$dest}");
                $set++;

                continue;
            }

            Storage::disk('public')->put($dest, file_get_contents($source));
            $prisoner->photo = $dest;
            $prisoner->save();
            $this->info("set photo -> {$name} ({$dest})");
            $set++;
        }

        $verb = $dry ? 'would set' : 'set';
        $this->info("\nDone. {$verb} {$set} photo(s); skipped {$skipped} that already had one; {$notFound} not found; {$missingFile} missing source file(s).");

        return self::SUCCESS;
    }
}
