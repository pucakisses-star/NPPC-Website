<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Attach prisoner portraits from the National Jericho Movement's "Ancestors"
 * pages (committed in database/data/photos/jericho/) to matching prisoner
 * entries that currently HAVE NO PHOTO. Existing photos are never overwritten.
 *
 * Matching is variant-aware: each entry lists several name fragments and the
 * first prisoner matched (case-insensitively) gets the photo. Prisoners not
 * present (e.g. on a stale local snapshot) are skipped with a warning, so the
 * command fills gaps wherever it is run (locally or on production).
 *
 * Source/attribution: database/data/photos/CREDITS-jericho.md
 */
final class AttachJerichoPhotos extends Command
{
    protected $signature = 'prisoners:attach-jericho-photos {--overwrite : Replace existing photos too}';

    protected $description = 'Attach Jericho Movement ancestor photos to prisoners missing a photo';

    /** @var array<array{0:string[],1:string}> [name-match fragments, photo file] */
    private array $map = [
        [['Al-Amin', 'Jamil Al-Amin'], 'al-amin-jamil-abdullah.png'],
        [['Sekou Odinga'], 'odinga-sekou.jpg'],
        [['Poindexter'], 'poindexter-ed.png'],
        [['Mutulu Shakur', 'Mutulu'], 'shakur-mutulu-1950-2023.png'],
        [['Shoatz'], 'russell-maroon-shoatz-harun-abdur-rauf-1943-2021.png'],
        [['Sims Africa', 'Charles Sims'], 'charles-chuck-sims-africa-1959-2021.jpg'],
        [['Romaine Fitzgerald', 'Romaine'], 'romaine-chip-fitzgerald-1949-2021.png'],
        [['Delbert Africa'], 'africa-delbert-orr.jpg'],
        [['Seth Hayes'], 'robert-seth-hayes.jpg'],
        [['Mafundi'], 'lake-richard-mafundi.jpg'],
        [['Lynne Stewart'], 'lynne-stewart-1939-2017.jpg'],
        [['Fulani'], 'iya-fulani-sunni-ali-1948-2016.jpg'],
        [['Oscar Washington'], 'oscar-washington-1950-2016.jpg'],
        [['Mohamman', 'Koti'], 'mohamman-koti-aka-james-johnson-1926-2016.png'],
        [['Afeni'], 'afeni-shakur-1947-2016.jpeg'],
        [['Luis V. Rodriguez'], 'luis-v-rodriguez-1956-2016.jpg'],
        [['Abdullah Majid', 'Abdul Majid', 'Anthony Laborde'], 'abdullah-majid-aka-anthony-laborde-1949-2016.png'],
        [['Mondo we Langa', 'Mondo We Langa', 'We Langa'], 'mondo-we-langa-aka-david-rice-1947-2016.png'],
        [['Pinell'], 'hugo-pinell-assassinated-1954-2015.png'],
        [['Phil Africa'], 'phil-africa-19562015.jpg'],
        [['Herman Ferguson'], 'herman-ferguson-1921-2014.jpeg'],
        [['Herman Wallace'], 'herman-wallace-1942-2013.jpg'],
        [['Gilday'], 'william-lefty-gilday-1918-2011.jpg'],
        [['Geronimo'], 'geronimo-ji-jaga-1947-2010.jpg'],
        [['Cetewayo', 'Michael Tabor'], 'michael-cetewayo-tabor-1946-2010.jpg'],
        [['Marilyn Buck'], 'marilyn-buck-1947-2010.png'],
        [['Alan Berkman'], 'dr-alan-berkman-1945-2009.jpg'],
        [['Eddie Hatcher'], 'eddie-hatcher-1948-2009.jpg'],
        [['Bashir Hameed'], 'bashir-hameed-1940-2008.jpg'],
        [['Richard Williams'], 'richard-williams-1947-2005.jpg'],
        [['Bukhari'], 'safiya-asya-bukhari-1950-2003.jpg'],
        [['Jah Heath', 'Teddy Heath', 'Teddy Jah'], 'teddy-jah-heath-1946-2001.jpg'],
        [['Nuh Washington'], 'albert-nuh-washington-1941-2000.jpg'],
        [['Merle Africa'], 'merle-africa-1951-1998.jpg'],
        [['Balagoon'], 'kuwasi-balagoon-1946-1986.jpg'],
        [['Robert Webb'], 'robert-webb-1949-1971.jpeg'],
        [['Fred Hampton'], 'fred-hampton-1948-1969.jpeg'],
        [['Mark Clark'], 'mark-clark-1947-1969.jpeg'],
        [['Ehehosi'], 'masai-ehehosi.jpeg'],
        [['Thomas Manning'], 'thomas-manning-1946-2019.png'],
    ];

    public function handle(): int
    {
        $overwrite = (bool) $this->option('overwrite');
        $set = 0;
        $hadPhoto = 0;
        $missing = 0;

        foreach ($this->map as [$fragments, $file]) {
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

            $src = database_path('data/photos/jericho/'.$file);
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
            $this->info("  {$prisoner->name} ← jericho/{$file}");
            $set++;
        }

        $this->info("\nDone. Photos set={$set}  Already had={$hadPhoto}  Not found/missing={$missing}");

        return self::SUCCESS;
    }
}
