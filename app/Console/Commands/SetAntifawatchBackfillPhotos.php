<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches profile photos to the AntifaWatch 600-663 backfill prisoners (the
 * same cohort handled by prisoner:backfill-antifawatch-cases) who had no photo.
 * Each image was pulled from that person's antifawatch.net database entry and
 * committed under database/data/photos/legacy/<file>. Every image was visually
 * verified to be an identifiable photo of the person.
 *
 * Nine of the searched names had no usable person-photo on antifawatch (their
 * entries show only a shared incident/scene image or a masked black-bloc
 * frame with no identifiable subject) and are NOT included: Devarian Haynes,
 * Ricardo Densmore, Tyree Walker (all share one burning-police-SUV photo),
 * Miguel Ramos (overturned burning car), Wesley Somers (fire silhouette),
 * Tandre Buchanan (obscured action shot), Lore-Elisabeth Blumenthal (street
 * scene), Rakem Balogun (armed group, ambiguous subject), Linwood Kaine
 * (masked scene).
 *
 * Matches prisoners by exact name (same lookup as BackfillAntifawatchCases).
 * Only fills empty photo fields -- never overwrites an existing photo.
 * Idempotent.
 */
final class SetAntifawatchBackfillPhotos extends Command
{
    protected $signature = 'prisoners:set-antifawatch-backfill-photos {--dry-run : Report what would change without writing}';

    protected $description = 'Attach committed antifawatch-sourced photos to the 600-663 backfill prisoners lacking one';

    /** @var array<int, array{0:string,1:string}> [name, committed image filename] */
    private array $rows = [
            ['Anthony Hayne', 'anthony-hayne.png'],
            ['Anthony Krohn', 'anthony-krohn.png'],
            ['Branden Wolfe', 'branden-wolfe.jpg'],
            ['Brandon Baxter', 'brandon-baxter.png'],
            ['Bruce Thompson', 'bruce-thompson.jpg'],
            ['Bryce Williams', 'bryce-williams.jpg'],
            ['Channel Lewis', 'channel-lewis.png'],
            ['Charles Pittman', 'charles-pittman.jpg'],
            ['Colinford Mattis', 'colinford-mattis.png'],
            ['Connor Stevens', 'connor-stevens.png'],
            ['Courtland Renford', 'courtland-renford.jpg'],
            ['Damion Zachary Feller', 'damion-zachary-feller.jpg'],
            ['Dashun Martin', 'dashun-martin.jpg'],
            ['David Elmakayes', 'david-elmakayes.jpg'],
            ['Delveccho Waller', 'delveccho-waller.jpg'],
            ['Deyanna Davis', 'deyanna-davis.jpg'],
            ['Douglas Wright', 'douglas-wright.png'],
            ['Dylan Robinson', 'dylan-robinson.jpg'],
            ['Earlja Dudley', 'earlja-dudley.png'],
            ['Edgar Samaniego', 'edgar-samaniego.jpg'],
            ['Fornandous Henderson', 'fornandous-henderson.jpg'],
            ['Gage Halupowski', 'gage-halupowski.png'],
            ['Jackson Patton', 'jackson-patton.png'],
            ['Jesse Clark', 'jesse-clark.jpg'],
            ['Jesse Smallwood', 'jesse-smallwood.jpg'],
            ['Jose Felan', 'jose-felan.jpg'],
            ['Joshua Stafford', 'joshua-stafford.png'],
            ['Judah Bailey', 'judah-bailey.jpg'],
            ['Kyle Olson', 'kyle-olson.png'],
            ['Loren Reed', 'loren-reed.png'],
            ['Margaret Channon', 'margaret-channon.png'],
            ['Matthew Rupert', 'matthew-rupert.jpg'],
            ['Melquan Barnett', 'melquan-barnett.jpg'],
            ['Montez Lee', 'montez-lee.jpg'],
            ['Nicholas Lucia', 'nicholas-lucia.jpg'],
            ['Richard Rubalcava', 'richard-rubalcava.png'],
            ['Robert Majure', 'robert-majure.jpg'],
            ['Samantha Shader', 'samantha-shader.png'],
            ['Shamar Betts', 'shamar-betts.jpg'],
            ['Timothy O\'Donnell', 'timothy-odonnell.png'],
            ['Urooj Rahman', 'urooj-rahman.jpg'],
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
