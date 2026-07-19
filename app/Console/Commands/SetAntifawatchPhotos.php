<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches profile photos to 53 prisoners who had none, sourced from
 * antifawatch.net's database entries for each person (mugshots, arrest photos,
 * and incident images), committed under database/data/photos/legacy/<slug>.jpg.
 * Each image was visually verified; composites were cropped to the person
 * (Carlos Matchett, Quinn McCormic, William Pierce).
 *
 * Six of the requested names had no usable person-photo on antifawatch (their
 * entries show court documents, article text, or scene shots with no
 * identifiable subject) and are NOT included: Ayoub Tabri, Davon DeAndre
 * Turner, Javon Hardy, Kevin Chianella, Samantha Brooks, Steven M. Fitch.
 *
 * Only fills empty photo fields — never overwrites an existing photo.
 * Idempotent.
 */
final class SetAntifawatchPhotos extends Command
{
    protected $signature = 'prisoners:set-antifawatch-photos';

    protected $description = 'Attach committed antifawatch-sourced photos to 53 prisoners lacking one';

    private const SLUGS = [
        'alissa-azar',
        'brandon-pack',
        'bryan-kelley',
        'bryce-michael-williams',
        'carlos-matchett',
        'christian-rea',
        'christopher-rojas',
        'christopher-tindal',
        'corey-smith',
        'cyan-bass',
        'dakotah-horton',
        'desmond-david-pitts',
        'devin-montgomery',
        'dwight-parker',
        'edward-schinzing',
        'elaine-carberry',
        'ellen-reiche',
        'gavaughn-streeter-hillerich',
        'gilberto-castillo',
        'isael-ortiz',
        'isaiah-willoughby',
        'jacob-gaines',
        'jacob-greenburg',
        'jacqueline-quimby',
        'jamal-newman-jr',
        'jerritt-pace',
        'jessica-lopez',
        'jonathan-montanez',
        'jordan-coyne',
        'joseph-ybarra',
        'kellen-sorber',
        'kelly-thomas-jackson',
        'lateesha-richards',
        'leroy-lemonte-perry-williams',
        'mackenzie-drechsler',
        'marc-castillo',
        'marquis-frasier',
        'marquon-clark',
        'martino-andrews',
        'matthew-banta',
        'micah-tillmon',
        'nicholas-scaglione',
        'quinn-mccormic',
        'richard-morano',
        'sam-resto',
        'sergey-turzhanskiy',
        'shador-jackson',
        'shakell-sanks',
        'tyre-wayne-means-jr',
        'tyvarh-nicholson',
        'victor-devon-edwards',
        'vida-jones',
        'william-pierce',
    ];

    public function handle(): int
    {
        $set = 0;

        foreach (self::SLUGS as $slug) {
            $source = database_path("data/photos/legacy/{$slug}.jpg");
            if (! is_file($source)) {
                $this->warn("  source image missing: database/data/photos/legacy/{$slug}.jpg");

                continue;
            }

            $prisoner = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first();
            if (! $prisoner) {
                $this->warn("  no prisoner '{$slug}'");

                continue;
            }

            if ($prisoner->photo) {
                $this->line("  {$prisoner->name} already has a photo — skipped.");

                continue;
            }

            $dest = "prisoners/{$slug}.jpg";
            Storage::disk('public')->put($dest, file_get_contents($source));
            $prisoner->photo = $dest;
            $prisoner->save();
            $set++;
        }

        $this->info("\nDone. Set {$set} photo(s).");
        $this->line('No usable antifawatch photo (not attached): Ayoub Tabri, Davon DeAndre Turner, Javon Hardy, Kevin Chianella, Samantha Brooks, Steven M. Fitch.');

        return self::SUCCESS;
    }
}
