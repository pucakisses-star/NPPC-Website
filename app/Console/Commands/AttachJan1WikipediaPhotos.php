<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Attaches freely-licensed (public-domain / Creative Commons) Wikimedia Commons
 * portraits to the "January-1 birthday" batch of prisoners, whose Wikipedia
 * pages were located by cross-checking name + date of birth. Only free images
 * are included; each person's identity was verified against the Wikipedia
 * article's birth year, and pure group photos were excluded. Attribution and
 * licenses are recorded in database/data/photos/CREDITS-jan1-wikipedia.md.
 *
 * Images are downloaded from Commons at runtime (Special:FilePath, 600px) so no
 * binaries are committed. Only fills prisoners that currently have NO photo
 * (pass --overwrite to replace). Missing prisoners are skipped with a warning.
 * Idempotent.
 */
final class AttachJan1WikipediaPhotos extends Command
{
    protected $signature = 'prisoners:attach-jan1-wikipedia-photos {--overwrite : Replace existing photos too}';

    protected $description = 'Download and attach freely-licensed Wikimedia portraits for the Jan-1 birthday batch';

    /** Prisoner name => Wikimedia Commons file name. */
    private array $map = array (
  'Albert Lannon' => 'Pictures_of_I._Wallace._(Al_Lannon)_1943_Slide_1_Crop.png',
  'Alison Turnbull Hopkins' => 'Mrs._J._H._H._Hopkins_152008v.jpg',
  'Ben Gold' => 'Ben_Gold_circa_1948_Edit.jpg',
  'Camille Marino' => 'Camille_Marino.jpg',
  'David Hartsough' => 'David_Hartsough_(1940–2025)_in_the_video_\'David_Hartsough,_Remembering_Past_Wars,_at_the_San_Francisco_Public_Library\'.png',
  'Dorothy Parker' => 'Young_Dorothy_Parker.jpg',
  'Eqbal Ahmad' => 'Eqbal_Ahmad_by_David_L._Marton.jpg',
  'Frederick Krafft' => 'Frederick_Krafft_passport_photograph.jpg',
  'Harvey Franklin Wasserman' => 'Harvey_Wasserman_-_The_Fukushima_Threat_01.jpg',
  'Haywood Patterson' => 'Haywood_Patterson.png',
  'Jack Stachel' => 'Jack_Stachel_Edit.jpg',
  'Laura Whitehorn' => 'NLN_Laura_Whitehorn.jpg',
  'Lauren Handy' => 'Lauren_Handy_protesting_capital_punishment_in_2022_(cropped).jpg',
  'Lewis Hayden' => 'Lewis_Hayden_Portrait.png',
  'Louis Weinstock' => 'Louis_Weinstock_1972.png',
  'Louise Olivereau' => 'Louise_Olivereau.jpg',
  'Manuelito' => 'Manuelito.jpg',
  'Martha Hennessy' => 'Martha_Hennessy_(51017840612)_(cropped).jpg',
  'Mary A. Nolan' => 'Mary_A._Nolan_(c._1910-1920).jpg',
  'Matilda Hall Gardner' => 'Matilda_Gilson_Gardner.jpg',
  'Mohsen Mahdawi' => 'Mohsen_Mahdawi_with_magnolia_2025-05-03.jpg',
  'Paul Jacob' => 'Paul_Jacob_in_2006.gif',
  'Pete O\'Neal' => 'Pete_O\'Neal.JPG',
  'Plenty Horses' => 'Grabill_-_Tasunka,_Ota_(alias_Plenty_Horses)-2.jpg',
  'Ralph Chaplin' => 'Ralph_Chaplin.png',
  'Rose Chernin' => 'Rose_Chernin_1951.jpg',
  'Shadrach Minkins' => 'Shadrach_Minkins_for_sale.jpg',
  'Sojourner Truth' => 'Sojourner_Truth,_1870_(cropped,_restored).jpg',
  'Thomas Sims' => 'Thomas_Sims,_The_Slave.png',
  'Wilhelm von Brincken' => 'Wilhelm_von_Brincken_in_International_Crime_(1938).jpg',
  'Yū Kikumura' => 'Yu_Kikumura.jpg',
  'Zoe Rosenberg' => 'Zoe_Rosenberg_2018_(cropped).jpg',
  'John Slidell' => 'John_Slidell_LA_1859.jpg',
);

    public function handle(): int
    {
        $overwrite = (bool) $this->option('overwrite');
        Storage::disk('public')->makeDirectory('prisoners');
        $set = 0; $skip = 0; $fail = 0;

        foreach ($this->map as $name => $file) {
            $prisoner = Prisoner::withUnderReview()->where('name', $name)->first();
            if (! $prisoner) {
                $this->warn("Not found, skipping: {$name}");
                $skip++;
                continue;
            }
            if ($prisoner->photo && ! $overwrite) {
                $this->line("Has photo, leaving: {$name}");
                $skip++;
                continue;
            }

            $url = 'https://commons.wikimedia.org/wiki/Special:FilePath/'.rawurlencode($file).'?width=600';
            [$data, $type] = $this->fetch($url);
            if ($data === null) {
                $this->warn("Download failed: {$name}");
                $fail++;
                continue;
            }

            $ext = match (true) {
                str_contains($type, 'png') => 'png',
                str_contains($type, 'gif') => 'gif',
                str_contains($type, 'webp') => 'webp',
                default => 'jpg',
            };
            $path = 'prisoners/'.Str::slug($prisoner->name).'.'.$ext;
            Storage::disk('public')->put($path, $data);
            $prisoner->photo = $path;
            $prisoner->save();
            $this->info("Set photo: {$prisoner->name} -> {$path}");
            $set++;
        }

        $this->info("\nDone. Set={$set}  Skipped={$skip}  Failed={$fail}");

        return self::SUCCESS;
    }

    /** @return array{0:?string,1:string} [binary data or null, content-type] */
    private function fetch(string $url): array
    {
        $ctx = stream_context_create(['http' => [
            'timeout' => 45,
            'follow_location' => 1,
            'header' => "User-Agent: NPPC-website/1.0 (prisoner portrait import; +https://nppc)\r\n",
        ]]);
        for ($i = 0; $i < 4; $i++) {
            $data = @file_get_contents($url, false, $ctx);
            if ($data !== false && strlen($data) > 1500) {
                $type = '';
                foreach (($http_response_header ?? []) as $h) {
                    if (stripos($h, 'content-type:') === 0) { $type = strtolower($h); }
                }
                return [$data, $type];
            }
            sleep(1 + $i);
        }
        return [null, ''];
    }
}