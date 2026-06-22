<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Fills in Timothy Leary's record — the Harvard psychologist turned
 * psychedelic-movement icon whom President Nixon called "the most dangerous man
 * in America." Adds (idempotently, keyed by arrest date) two cases: (1) his
 * 1968 marijuana conviction and 1970 imprisonment at the California Men's
 * Colony, his September 12, 1970 escape with Weather Underground help, and his
 * 1970–1973 exile (Algeria, Switzerland, Austria, Afghanistan); and (2) his
 * recapture at Kabul airport on January 14, 1973, his re-imprisonment, and his
 * April 21, 1976 parole. Sets his death date (May 31, 1996).
 *
 * Upsert: finds the existing Leary record (he is already in the database) and
 * fills only blank prose/array fields, while setting the death date and status
 * flags authoritatively; creates the record if absent. The in_exile flag is set
 * AFTER the cases are saved so the model's saving hook does not stamp a spurious
 * exile period onto the post-recapture (Folsom) case.
 */
final class AddTimothyLeary extends Command
{
    protected $signature = 'prisoners:add-timothy-leary';

    protected $description = 'Add/fill Timothy Leary: arrest, imprisonment, exile, recapture, parole, and death dates';

    public function handle(): int
    {
        $bio = 'Timothy Leary (1920–1996) was a Harvard psychologist turned countercultural icon of the psychedelic '
            .'movement, famous for the phrase "turn on, tune in, drop out"; President Nixon called him "the most '
            .'dangerous man in America." After a 1965 marijuana arrest at the Laredo, Texas border (a conviction the '
            .'U.S. Supreme Court overturned in Leary v. United States, 1969) and a December 1968 arrest in Laguna Beach, '
            .'California, he was convicted in 1970 and imprisoned at the California Men\'s Colony. On September 12, 1970 '
            .'he escaped with help from the Weather Underground and fled into exile — first to Algeria, where Eldridge '
            .'Cleaver\'s exiled Black Panthers briefly held him, then to Switzerland, Austria, and Afghanistan. He was '
            .'captured at Kabul airport on January 14, 1973, returned to the United States, and re-imprisoned (including '
            .'at Folsom) until his parole on April 21, 1976. He died on May 31, 1996.';

        DB::transaction(function () use ($bio) {
            $menscolony = Institution::firstOrCreate(
                ['name' => 'California Men\'s Colony'],
                ['city' => 'San Luis Obispo', 'state' => 'California']
            );
            $folsom = Institution::firstOrCreate(
                ['name' => 'Folsom State Prison'],
                ['city' => 'Represa', 'state' => 'California']
            );

            $prisoner = Prisoner::withUnderReview()->where('name', 'like', '%Timothy Leary%')->first()
                ?? new Prisoner(['name' => 'Timothy Leary']);

            // Fill only blank descriptive fields (never clobber existing prose).
            $fill = [
                'first_name' => 'Timothy',
                'last_name' => 'Leary',
                'aka' => 'Timothy Francis Leary',
                'gender' => 'Male',
                'race' => 'White',
                'state' => 'California',
                'era' => '1970s',
                'birthdate' => '1920-10-22',
                'ideologies' => ['Counterculture', 'Civil liberties'],
                'affiliation' => ['League for Spiritual Discovery'],
                'description' => $bio,
            ];
            foreach ($fill as $key => $value) {
                $current = $prisoner->getAttribute($key);
                $blank = $current === null || $current === '' || (is_array($current) && count($current) === 0);
                if ($blank) {
                    $prisoner->setAttribute($key, $value);
                }
            }

            // Authoritative facts. in_exile is set LAST (see below).
            $prisoner->death_date = '1996-05-31';
            $prisoner->in_custody = false;
            $prisoner->released = true;
            $prisoner->currently_in_exile = false;
            $prisoner->awaiting_trial = false;
            $prisoner->in_exile = false;
            $prisoner->save();

            // Case 1: 1968 conviction, 1970 imprisonment + escape, 1970–73 exile.
            $caseA = $prisoner->cases()->whereDate('arrest_date', '1968-12-26')->first()
                ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);
            $caseA->institution_id = $menscolony->id;
            $caseA->charges = 'Marijuana possession (arrested December 26, 1968 in Laguna Beach, California). An earlier 1965 marijuana conviction at the Laredo, Texas border was overturned by the U.S. Supreme Court in Leary v. United States (1969).';
            $caseA->convicted = 'Yes — convicted in 1970 and sentenced to up to ten years.';
            $caseA->arrest_date = '1968-12-26';
            $caseA->in_exile_since = '1970-09-13';
            $caseA->end_of_exile = '1973-01-14';
            $caseA->sentence = 'Imprisoned at the California Men\'s Colony in early 1970; on September 12, 1970 he escaped with help from the Weather Underground and fled into exile — Algeria (where Eldridge Cleaver\'s exiled Black Panthers briefly held him), then Switzerland, Austria, and Afghanistan — until his capture at Kabul airport on January 14, 1973.';
            $caseA->save();

            // Case 2: recapture (Jan 14, 1973) → re-imprisonment → parole (Apr 21, 1976).
            $caseB = $prisoner->cases()->whereDate('arrest_date', '1973-01-14')->first()
                ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);
            $caseB->institution_id = $folsom->id;
            $caseB->charges = 'Returned to U.S. custody after his January 14, 1973 capture at Kabul airport, Afghanistan, to serve the remainder of his sentence and answer for the 1970 escape.';
            $caseB->convicted = 'Yes — returned to prison after recapture.';
            $caseB->arrest_date = '1973-01-14';
            $caseB->incarceration_date = '1973-01-14';
            $caseB->release_date = '1976-04-21';
            // Explicitly no exile on this case (clear any prior auto-stamped value).
            $caseB->in_exile_since = null;
            $caseB->end_of_exile = null;
            $caseB->sentence = 'Held at Folsom and other California prisons after extradition; paroled on April 21, 1976.';
            $caseB->save();

            // Now flag the prisoner as formerly in exile (saving the prisoner does
            // not re-run the case hook, so case 2 stays exile-free).
            $prisoner->in_exile = true;
            $prisoner->save();
        });

        $this->info('Upserted Timothy Leary with 2 cases (conviction/escape/exile and recapture/parole) and death date.');

        return self::SUCCESS;
    }
}
