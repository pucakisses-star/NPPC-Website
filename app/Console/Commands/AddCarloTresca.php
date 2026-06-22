<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Carlo Tresca (1879–1943) — Italian-American anarchist, IWW strike
 * leader, and anti-fascist editor of Il Martello ("The Hammer"). He was jailed
 * in two documented cases:
 *   - 1916 Mesabi Range (Minnesota) iron miners' strike: arrested with other
 *     organizers as an accessory to a murder arising from the strike and held
 *     for months awaiting trial; the case collapsed and he was freed without
 *     trial after three miners accepted manslaughter pleas (December 1916).
 *   - 1925 Atlanta federal penitentiary: convicted under the Comstock Act for a
 *     two-line birth-control advertisement in Il Martello — a transparently
 *     political "obscenity" prosecution sought by the Italian government to
 *     silence his anti-fascist paper. Sentenced to a year and a day; entered
 *     prison January 5, 1925 and served about four months before President
 *     Coolidge commuted it to time served.
 * Assassinated on a Manhattan street corner on January 11, 1943.
 *
 * Dates: the Atlanta entry date (Jan 5, 1925) is well documented; release is
 * stored ~four months later (commuted to time served). The 1916 Mesabi dates
 * are approximate (summer arrest, charges dropped December 1916).
 *
 * Idempotent: re-running updates the record and its two cases in place.
 */
final class AddCarloTresca extends Command
{
    protected $signature = 'prisoners:add-carlo-tresca';

    protected $description = 'Add Carlo Tresca (anarchist/IWW organizer and anti-fascist editor)';

    public function handle(): int
    {
        $fields = [
            'name' => 'Carlo Tresca',
            'first_name' => 'Carlo',
            'last_name' => 'Tresca',
            'gender' => 'Male',
            'race' => 'White',
            'state' => 'New York',
            'era' => '1910s',
            'birthdate' => '1879-03-09',
            'death_date' => '1943-01-11',
            'ideologies' => ['Anarchism', 'Syndicalism', 'Anti-fascism', 'Anti-Stalinism'],
            'affiliation' => ['Industrial Workers of the World (IWW)'],
            'in_custody' => false,
            'released' => true,
            'awaiting_trial' => false,
            'description' => "Carlo Tresca (1879–1943) was an Italian-American anarchist, syndicalist, orator, and newspaper editor who became one of the most prominent radical labor agitators in the United States. A leader of the Industrial Workers of the World during the 1910s, he helped lead the Lawrence (1912) and Paterson (1913) textile strikes and the 1916 Mesabi Range iron miners' strike in Minnesota, where he and other organizers were jailed for months on an accessory-to-murder charge arising from the strike that ultimately collapsed without trial. As editor of the anti-fascist weekly Il Martello ('The Hammer'), he became a leading voice against Mussolini's regime in America; at the behest of the Italian government, federal authorities prosecuted him for a two-line birth-control advertisement and he served about four months in the Atlanta federal penitentiary in 1925 before public outcry forced President Calvin Coolidge to commute the sentence to time served. A fierce opponent of both fascism and Stalinism, Tresca was assassinated on a Manhattan street corner on January 11, 1943, in a killing widely attributed to the Mafia and never officially solved.",
        ];

        $caseSpecs = [
            [
                'institution' => ['name' => 'St. Louis County Jail', 'city' => 'Duluth', 'state' => 'Minnesota'],
                'charges' => "First-degree murder (as an accessory) — 1916 Mesabi Range iron miners' strike",
                'arrest_date' => '1916-07-01',
                'incarceration_date' => '1916-07-01',
                'release_date' => '1916-12-31',
                'convicted' => 'No — charges dropped; released without trial (December 1916)',
                'sentence' => 'None — held for months awaiting trial; the case against the organizers was dropped after three miners accepted manslaughter pleas',
            ],
            [
                'institution' => ['name' => 'United States Penitentiary, Atlanta', 'city' => 'Atlanta', 'state' => 'Georgia'],
                'charges' => "Sending 'obscene' matter through the mails — a birth-control book advertisement in Il Martello (Comstock Act)",
                'arrest_date' => '1923-08-01',
                'incarceration_date' => '1925-01-05',
                'release_date' => '1925-05-05',
                'convicted' => 'Yes — convicted October 1923; sentence affirmed November 1924',
                'sentence' => 'One year and one day; entered the Atlanta federal penitentiary January 5, 1925 and served about four months before President Coolidge commuted it to time served',
            ],
        ];

        DB::transaction(function () use ($fields, $caseSpecs) {
            $prisoner = Prisoner::withUnderReview()->where('name', 'Carlo Tresca')->first();

            if ($prisoner) {
                $prisoner->fill($fields)->save();
                $prisoner->cases()->delete();
            } else {
                $prisoner = Prisoner::create($fields);
            }

            foreach ($caseSpecs as $spec) {
                $inst = $spec['institution'];
                unset($spec['institution']);

                $institution = Institution::firstOrCreate(
                    ['name' => $inst['name']],
                    ['city' => $inst['city'], 'state' => $inst['state']],
                );

                $spec['prisoner_id'] = $prisoner->id;
                $spec['institution_id'] = $institution->id;
                PrisonerCase::create($spec);
            }
        });

        $this->info('Added/updated Carlo Tresca with 2 cases (1916 Mesabi Range frame-up; 1925 Atlanta penitentiary).');

        return self::SUCCESS;
    }
}
