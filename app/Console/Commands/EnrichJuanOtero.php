<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Enriches Juan Otero's thin record from the "Free Juan Otero" defense flyer
 * (1973) now catalogued in the archive: a fuller sourced bio, the robbery
 * frame-up charges and five-year sentence, and the portrait cropped from the
 * flyer (non-free/fair-use; see CREDITS-nonfree.md). Updates the existing record
 * in place (the movement-archive importer skips names that already exist).
 * Idempotent.
 */
final class EnrichJuanOtero extends Command
{
    protected $signature = 'prisoners:enrich-juan-otero';

    protected $description = 'Enrich Juan Otero from the 1973 defense flyer and attach his portrait';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'Juan Otero')->first();
        if (! $prisoner) {
            $this->warn('Juan Otero not found — run prisoners:add-movement-archive-prisoners first.');

            return self::SUCCESS;
        }

        $prisoner->fill([
            'first_name' => 'Juan',
            'last_name' => 'Otero',
            'gender' => 'Male',
            'race' => 'Hispanic',
            'state' => 'New York',
            'era' => '1970s',
            'ideologies' => ['Community organizing'],
            'affiliation' => ['Committee to Defend Juan Otero'],
            'description' => 'Juan Otero was a Bronx family man, educational specialist, and community activist — a '
                .'young father with no police record — working for equality in the building trades when he became the '
                .'subject of a 1973 Puerto Rican movement defense campaign, "Free Juan Otero." The Committee to Defend '
                .'Juan Otero (c/o Rev. Juan Otero, 535 Jackson Avenue, the Bronx) held that he had been framed on two '
                .'robbery charges — no evidence was ever found in his home or car, and the robberies took place while '
                .'he was visiting friends — through a personal vendetta by South Bronx police who knew him from his '
                .'community work, in collaboration with corrupt construction contractors. Convicted by a jury after an '
                .'attorney urged him not to "make a political case out of this," he was sentenced to five years in '
                .'prison. The campaign linked his case to those of Angela Davis, the Berrigans, and Carlos Feliciano. '
                .'(Sourced from the "Free Juan Otero" defense flyer now catalogued in the archive.)',
            'in_custody' => false,
            'released' => true,
            'in_exile' => false,
            'awaiting_trial' => false,
        ]);
        $prisoner->save();

        $case = $prisoner->cases()->first() ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);
        $case->prisoner_id = $prisoner->id;
        $case->charges = 'Two robbery charges — which the "Free Juan Otero" defense campaign (1973) called a frame-up '
            .'by South Bronx police, in collaboration with corrupt building-trades contractors, in retaliation for his '
            .'community organizing; no evidence was found in his home or car.';
        $case->convicted = 'Yes — convicted by a jury (per the 1973 defense campaign).';
        $case->sentence = 'Five years in prison.';
        $case->save();

        $src = database_path('data/photos/nonfree/juan-otero.jpg');
        if (is_file($src)) {
            Storage::disk('public')->makeDirectory('prisoners');
            $rel = 'prisoners/juan-otero.jpg';
            Storage::disk('public')->put($rel, (string) file_get_contents($src));
            $prisoner->photo = $rel;
            $prisoner->save();
            $this->info('  attached portrait from the flyer.');
        } else {
            $this->warn('  photo source not found: '.$src);
        }

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Enriched Juan Otero (slug: '.$prisoner->slug.').');

        return self::SUCCESS;
    }
}
