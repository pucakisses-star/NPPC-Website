<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds Carl Braden (1914–1975) — white anti-racist organizer in
 * Louisville, KY — to the prisoners table.
 *
 * Braden and his wife Anne are best known for the 1954 Wade case:
 * they bought a Louisville home in their own names and transferred
 * it to a Black family (Andrew and Charlotte Wade) at a time when
 * real estate brokers refused to sell to Black buyers in white
 * neighborhoods. The home was firebombed; Kentucky prosecutors
 * charged Carl Braden with sedition for "inciting violence and
 * civil unrest" instead of pursuing the bombers. Sentenced to 15
 * years, served ~8 months before the conviction was overturned
 * by Pennsylvania v. Nelson (1956).
 *
 * Carl was incarcerated a second time in 1961–62 (~10 months) for
 * contempt of Congress, after refusing to answer HUAC questions
 * about his political associations.
 *
 * Anne Braden remained close to Dr. Martin Luther King Jr. and is
 * one of the few white organizers named in his "Letter from a
 * Birmingham Jail" (1963).
 */
final class AddCarlBradenPrisoner extends Command {
    protected $signature = 'archive:add-carl-braden';
    protected $description = 'Add Carl Braden (1914–1975) to the prisoners table';

    public function handle(): int {
        $payload = [
            'name' => 'Carl Braden',
            'first_name' => 'Carl',
            'middle_name' => 'James',
            'last_name' => 'Braden',
            'description' => 'White anti-racist organizer, journalist, and labor activist in Louisville, Kentucky. In 1954, Carl and his wife Anne Braden purchased a home in their own names and transferred it to their friends Andrew and Charlotte Wade — a Black family that local real estate brokers had refused to sell to in any white neighborhood. The Wades\' new home was firebombed weeks after they moved in. Instead of pursuing the bombers, Kentucky prosecutors charged Carl Braden with sedition for "inciting violence and civil unrest" by helping a Black family integrate a white neighborhood. He was convicted in December 1954 and sentenced to 15 years; he served approximately eight months before the conviction was overturned by the U.S. Supreme Court\'s 1956 ruling in Pennsylvania v. Nelson (federal sedition law preempted state sedition prosecutions). Carl was incarcerated a second time in 1961–1962 (~10 months) for contempt of Congress after refusing to answer HUAC questions about his political associations. He continued civil-rights and anti-segregation organizing in the South until his death in 1975. His wife Anne Braden was one of the few white organizers named by name in Dr. Martin Luther King Jr.\'s "Letter from a Birmingham Jail" (1963).',
            'state' => 'Kentucky',
            'race' => 'White',
            'gender' => 'Male',
            'birthdate' => '1914-07-04',
            'death_date' => '1975-02-18',
            'ideologies' => ['Civil rights', 'Anti-segregation', 'Labor organizing', 'Anti-racist'],
            'affiliation' => ['Southern Conference Educational Fund (SCEF)'],
            'era' => '1950s',
            'in_custody' => false,
            'released' => true,
            'cases' => [
                [
                    'institution_name' => 'Kentucky State Reformatory',
                    'institution_state' => 'Kentucky',
                    'charges' => 'Sedition (KY) — for purchasing a home in his name and transferring it to a Black family in Louisville. Convicted by a Jefferson County jury, Dec 13, 1954.',
                    'arrest_date' => '1954-10-01',
                    'sentenced_date' => '1954-12-13',
                    'release_date' => '1955-08-01',
                    'convicted' => 'Yes — overturned in 1956 by Pennsylvania v. Nelson (state sedition laws preempted by federal law).',
                    'sentence' => '15 years; served ~8 months before conviction overturned.',
                ],
                [
                    'institution_name' => 'Federal correctional facility',
                    'charges' => 'Contempt of Congress — refusal to answer HUAC questions about political associations.',
                    'arrest_date' => '1961-05-01',
                    'release_date' => '1962-03-01',
                    'sentence' => 'Approximately 10 months federal imprisonment.',
                ],
            ],
        ];

        $exit = $this->call('prisoner:add', ['json' => json_encode($payload)]);
        if ($exit === self::SUCCESS) {
            $this->info('Added Carl Braden.');
        } else {
            $this->warn('prisoner:add reported a failure (likely duplicate).');
        }
        return self::SUCCESS;
    }
}
