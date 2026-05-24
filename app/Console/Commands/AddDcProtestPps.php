<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds 6 named PPs surfaced from the Wikipedia "List of rallies and
 * protest marches in Washington, D.C." sweep that weren't already in
 * the prisoner DB:
 *
 *   - 3 additional 1917 NWP Silent Sentinels (rounding out the set
 *     started in PR #558): Matilda Hall Gardner, Alison Turnbull
 *     Hopkins, Katherine Morey
 *   - Norman Mailer — author crossed MP line at the 1967 Pentagon
 *     March; chronicled in "Armies of the Night"
 *   - Ralph Abernathy — SCLC president arrested clearing
 *     Resurrection City, 1968
 *   - Daniel Ellsberg — Pentagon Papers whistleblower; indicted
 *     1971; mistrial declared 1973 for government misconduct
 *
 * Eras per project decade-string convention (1910s, 1960s, 1970s).
 */
final class AddDcProtestPps extends Command {
    protected $signature = 'archive:add-dc-protest-pps';
    protected $description = 'Add 6 DC-protest PPs (3 Silent Sentinels, Mailer, Abernathy, Ellsberg)';

    public function handle(): int {
        $added = 0; $skipped = 0;

        foreach ($this->prisoners() as $payload) {
            $exit = $this->call('prisoner:add', ['json' => json_encode($payload)]);
            if ($exit === self::SUCCESS) {
                $this->info('ADD: '.$payload['name']);
                $added++;
            } else {
                $skipped++;
            }
        }

        $this->info("Done — added {$added}, skipped {$skipped}.");
        return self::SUCCESS;
    }

    /** @return array<int, array<string, mixed>> */
    private function prisoners(): array {
        return [
            // === Silent Sentinels (extending PR #558) ===
            [
                'name' => 'Matilda Hall Gardner',
                'first_name' => 'Matilda',
                'middle_name' => 'Hall',
                'last_name' => 'Gardner',
                'description' => 'Washington, DC suffragist and Silent Sentinel arrested for picketing the Wilson White House for women\'s right to vote in 1917. Sentenced to 60 days at the Occoquan Workhouse in Virginia. Wife of journalist Gilson Gardner.',
                'state' => 'District of Columbia',
                'race' => 'White',
                'gender' => 'Female',
                'birthdate' => '1878-01-01',
                'death_date' => '1948-08-19',
                'ideologies' => ['Women\'s suffrage'],
                'affiliation' => ['National Woman\'s Party', 'Silent Sentinels'],
                'era' => '1910s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Occoquan Workhouse',
                    'institution_state' => 'Virginia',
                    'charges' => 'Obstructing traffic (picketing the White House for women\'s suffrage).',
                    'arrest_date' => '1917-07-14',
                    'sentence' => '60 days at Occoquan Workhouse.',
                ]],
            ],
            [
                'name' => 'Alison Turnbull Hopkins',
                'first_name' => 'Alison',
                'middle_name' => 'Turnbull',
                'last_name' => 'Hopkins',
                'description' => 'New Jersey NWP organizer and Silent Sentinel. Arrested in 1917 for picketing the Wilson White House for women\'s suffrage and sentenced to 60 days at the Occoquan Workhouse. Her husband John A. H. Hopkins was a Bull Moose Party leader, which made her arrest a national scandal among progressive Democrats.',
                'state' => 'New Jersey',
                'race' => 'White',
                'gender' => 'Female',
                'birthdate' => '1880-01-01',
                'death_date' => '1951-01-01',
                'ideologies' => ['Women\'s suffrage'],
                'affiliation' => ['National Woman\'s Party', 'Silent Sentinels'],
                'era' => '1910s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Occoquan Workhouse',
                    'institution_state' => 'Virginia',
                    'charges' => 'Obstructing traffic (picketing the White House).',
                    'arrest_date' => '1917-07-14',
                    'sentence' => '60 days at Occoquan Workhouse.',
                ]],
            ],
            [
                'name' => 'Katherine Morey',
                'first_name' => 'Katherine',
                'last_name' => 'Morey',
                'description' => 'Massachusetts suffragist and Silent Sentinel. Among the first NWP picketers arrested at the Wilson White House in 1917, carrying a banner from a Wilson speech that read "Mr. President, what will you do for woman suffrage?". Jailed at the District Jail.',
                'state' => 'Massachusetts',
                'race' => 'White',
                'gender' => 'Female',
                'ideologies' => ['Women\'s suffrage'],
                'affiliation' => ['National Woman\'s Party', 'Silent Sentinels'],
                'era' => '1910s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'District Jail',
                    'institution_state' => 'District of Columbia',
                    'charges' => 'Obstructing traffic (picketing the White House with Wilson-quote banner).',
                    'arrest_date' => '1917-06-22',
                    'sentence' => 'Jailed in the District Jail (term unspecified).',
                ]],
            ],

            // === Vietnam era ===
            [
                'name' => 'Norman Mailer',
                'first_name' => 'Norman',
                'last_name' => 'Mailer',
                'description' => 'Pulitzer Prize-winning novelist arrested at the October 21, 1967 March on the Pentagon for crossing an MP line and refusing orders to disperse. He pled nolo contendere, was sentenced to 30 days (most suspended; served 5), and wrote his National Book Award and Pulitzer Prize-winning "Armies of the Night" (1968) as a first-person account of the march, his arrest, and the night he spent in custody at the Occoquan Workhouse with other antiwar protesters.',
                'state' => 'New York',
                'race' => 'White',
                'gender' => 'Male',
                'birthdate' => '1923-01-31',
                'death_date' => '2007-11-10',
                'ideologies' => ['Anti-Vietnam War'],
                'era' => '1960s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Occoquan Workhouse',
                    'institution_state' => 'Virginia',
                    'charges' => 'Crossing a military police line at the Pentagon, refusing to disperse.',
                    'arrest_date' => '1967-10-21',
                    'release_date' => '1967-11-15',
                    'convicted' => 'Pled nolo contendere.',
                    'sentence' => '30 days; most suspended; served approximately 5 days.',
                ]],
            ],
            [
                'name' => 'Ralph Abernathy',
                'first_name' => 'Ralph',
                'last_name' => 'Abernathy',
                'description' => 'Baptist minister, civil rights leader, and Dr. Martin Luther King Jr.\'s closest associate. Co-founder and longtime president of the Southern Christian Leadership Conference (SCLC). Arrested an estimated 40+ times during the civil rights movement. After taking over SCLC following King\'s assassination, Abernathy led the Poor People\'s Campaign and was arrested on June 24, 1968 leading a "Day of Solidarity" march from Resurrection City on the National Mall to the U.S. Capitol; he served 20 days in DC Jail.',
                'state' => 'Alabama',
                'race' => 'Black',
                'gender' => 'Male',
                'birthdate' => '1926-03-11',
                'death_date' => '1990-04-17',
                'ideologies' => ['Civil rights', 'Anti-poverty', 'Christian nonviolence'],
                'affiliation' => ['Southern Christian Leadership Conference', 'Poor People\'s Campaign'],
                'era' => '1960s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'District of Columbia Jail',
                    'institution_state' => 'District of Columbia',
                    'charges' => 'Unlawful assembly on the U.S. Capitol grounds (Poor People\'s Campaign / Resurrection City "Day of Solidarity" march, June 24, 1968).',
                    'arrest_date' => '1968-06-24',
                    'release_date' => '1968-07-13',
                    'sentence' => '20 days in DC Jail.',
                ]],
            ],

            // === Pentagon Papers ===
            [
                'name' => 'Daniel Ellsberg',
                'first_name' => 'Daniel',
                'last_name' => 'Ellsberg',
                'description' => 'Former RAND Corporation military analyst and State Department official who in 1971 leaked the Pentagon Papers — a top-secret 7,000-page Department of Defense study of U.S. decision-making in Vietnam from 1945 to 1968 — to the New York Times and 16 other newspapers. The leak revealed that successive administrations had systematically deceived Congress and the public about the war. Indicted on June 28, 1971 under the Espionage Act and related statutes; Ellsberg faced 115 years in prison. His May 1973 trial ended in a mistrial after the court learned of Nixon-administration misconduct (the "Plumbers" had burglarized his psychiatrist\'s office, illegally wiretapped him, and offered the trial judge the FBI directorship). Ellsberg spent the rest of his life as a leading anti-nuclear, antiwar, and whistleblower advocate, defending Chelsea Manning, Edward Snowden, and Julian Assange. Died June 16, 2023.',
                'state' => 'California',
                'race' => 'White',
                'gender' => 'Male',
                'birthdate' => '1931-04-07',
                'death_date' => '2023-06-16',
                'ideologies' => ['Anti-Vietnam War', 'Whistleblower', 'Anti-nuclear'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'California',
                    'charges' => 'Conspiracy, espionage (Espionage Act of 1917), and theft of government property — for leaking the Pentagon Papers to the New York Times et al.',
                    'arrest_date' => '1971-06-28',
                    'sentenced_date' => '1973-05-11',
                    'convicted' => 'No — mistrial declared May 11, 1973 for government misconduct; all charges dismissed.',
                    'sentence' => 'Faced 115 years; charges dismissed after mistrial.',
                ]],
            ],
        ];
    }
}
