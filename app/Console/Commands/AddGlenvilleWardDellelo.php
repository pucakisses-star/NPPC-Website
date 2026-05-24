<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds 6 PPs surfaced from the third-wave deep crawl of "List of
 * incidents of civil unrest in the US":
 *
 *   - 4 Glenville Shootout co-defendants of Fred Ahmed Evans
 *     (Cleveland, July 23, 1968 — Black Nationalists of New Libya
 *     vs. police):
 *       Lathan L. Donald — 7 consecutive life terms
 *       Alfred Thomas — ruled insane, Lima State Hospital
 *       Leslie Jackson (Osu Bey) — juvenile delinquency, Mansfield
 *       John Hardrick — juvenile, Mansfield
 *
 *   - Earl Ward — intellectual leader of the 1952 Jackson Michigan
 *     prison riot (2,500+ prisoners; five-day hostage standoff);
 *     received additional 20-30 years for kidnapping
 *
 *   - Bobby Dellelo — NPRA chapter president at Walpole MA; led the
 *     three-month 1973 prisoner-run prison; movement-recognized PP
 *     organizer
 *
 * Eras per project decade-string convention.
 */
final class AddGlenvilleWardDellelo extends Command {
    protected $signature = 'archive:add-glenville-ward-dellelo';
    protected $description = 'Add 4 Glenville Shootout co-defendants + Earl Ward + Bobby Dellelo';

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
        $glenvilleBase = [
            'state' => 'Ohio',
            'race' => 'Black',
            'gender' => 'Male',
            'ideologies' => ['Black nationalism', 'Black armed self-defense'],
            'affiliation' => ['Black Nationalists of New Libya', 'Republic of New Libya'],
            'era' => '1960s',
            'in_custody' => false,
            'released' => true,
        ];
        $glenvilleSharedDesc = 'Co-defendant of Fred Ahmed Evans in the Glenville Shootout — the July 23, 1968 armed confrontation in the Glenville neighborhood of Cleveland, Ohio between the Black Nationalists of New Libya (a militant Black-nationalist group led by Evans) and Cleveland police. Three police officers, three Black militants, and one Black civilian were killed in the gunfight, which sparked five days of uprising in the city. Federal and state authorities prosecuted the surviving militants in what became one of the harshest Black-nationalist prosecutions of the era.';

        return [
            // === Glenville Shootout co-defendants ===
            [
                'name' => 'Lathan L. Donald',
                'first_name' => 'Lathan',
                'middle_name' => 'L.',
                'last_name' => 'Donald',
                'description' => $glenvilleSharedDesc.' Donald, age 19 at the time of the shootout, was convicted in August 1969 of three counts of first-degree murder and four counts of second-degree murder. Sentenced to seven consecutive life terms with a minimum of 110 years.',
                'cases' => [[
                    'institution_state' => 'Ohio',
                    'charges' => 'Three counts of first-degree murder; four counts of second-degree murder (Glenville Shootout, July 23, 1968).',
                    'arrest_date' => '1968-07-23',
                    'sentenced_date' => '1969-08-01',
                    'convicted' => 'Yes.',
                    'sentence' => 'Seven consecutive life terms; minimum 110 years.',
                ]],
            ] + $glenvilleBase,
            [
                'name' => 'Alfred Thomas',
                'first_name' => 'Alfred',
                'last_name' => 'Thomas',
                'description' => $glenvilleSharedDesc.' Thomas was charged with seven counts of first-degree murder. Ruled legally insane and committed to Lima State Hospital for the Criminally Insane.',
                'cases' => [[
                    'institution_name' => 'Lima State Hospital for the Criminally Insane',
                    'institution_state' => 'Ohio',
                    'charges' => 'Seven counts of first-degree murder (Glenville Shootout).',
                    'arrest_date' => '1968-07-23',
                    'convicted' => 'No — ruled legally insane; committed.',
                    'sentence' => 'Indefinite commitment, Lima State Hospital.',
                ]],
            ] + $glenvilleBase,
            [
                'name' => 'Leslie Jackson',
                'aka' => 'Osu Bey',
                'first_name' => 'Leslie',
                'last_name' => 'Jackson',
                'description' => $glenvilleSharedDesc.' Jackson was 16 at the time of the shootout. Adjudicated delinquent in juvenile court and held at the Mansfield Youth Center until age 21.',
                'cases' => [[
                    'institution_name' => 'Mansfield Youth Center',
                    'institution_state' => 'Ohio',
                    'charges' => 'Juvenile delinquency proceeding arising from Glenville Shootout.',
                    'arrest_date' => '1968-07-23',
                    'convicted' => 'Adjudicated delinquent in juvenile court.',
                    'sentence' => 'Held until age 21 (~5 years) at Mansfield Youth Center.',
                ]],
            ] + $glenvilleBase,
            [
                'name' => 'John Hardrick',
                'first_name' => 'John',
                'last_name' => 'Hardrick',
                'description' => $glenvilleSharedDesc.' Hardrick was 17 at the time of the shootout. Adjudicated delinquent in juvenile court and held at Mansfield Youth Center until age 21.',
                'cases' => [[
                    'institution_name' => 'Mansfield Youth Center',
                    'institution_state' => 'Ohio',
                    'charges' => 'Juvenile delinquency proceeding arising from Glenville Shootout.',
                    'arrest_date' => '1968-07-23',
                    'convicted' => 'Adjudicated delinquent in juvenile court.',
                    'sentence' => 'Held until age 21 at Mansfield Youth Center.',
                ]],
            ] + $glenvilleBase,

            // === 1952 Jackson Michigan prison riot ===
            [
                'name' => 'Earl Ward',
                'first_name' => 'Earl',
                'last_name' => 'Ward',
                'description' => 'Intellectual leader of the 1952 Michigan State Prison riot at Jackson — at the time the largest prison rebellion in U.S. history, involving over 2,500 prisoners and a five-day hostage standoff during which the rebels held nine guards. Ward, a young Detroit prisoner serving 25–30 years for armed robbery, emerged as the rebels\' chief negotiator with prison officials and the press, articulating demands for better food, an end to brutality in solitary, the firing of a sadistic deputy warden, and an end to "the silent system" that forbade prisoner conversation. After the rebellion ended without state violence (a rare outcome for U.S. prison uprisings), Ward was given an additional 20–30 years on kidnapping charges for holding the guards.',
                'state' => 'Michigan',
                'race' => 'Black',
                'gender' => 'Male',
                'ideologies' => ['Prison rebellion'],
                'affiliation' => ['1952 Jackson Michigan Prison Riot'],
                'era' => '1950s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Michigan State Prison (Jackson)',
                    'institution_state' => 'Michigan',
                    'charges' => 'Kidnapping (of nine guards held hostage during 1952 prison riot, April 20–25, 1952).',
                    'arrest_date' => '1952-04-25',
                    'sentenced_date' => '1952-09-01',
                    'convicted' => 'Yes.',
                    'sentence' => 'Additional 20–30 years on top of prior 25–30 year armed robbery sentence.',
                ]],
            ],

            // === Walpole MA 1973 ===
            [
                'name' => 'Bobby Dellelo',
                'aka' => 'Robert Dellelo',
                'first_name' => 'Bobby',
                'last_name' => 'Dellelo',
                'description' => 'Massachusetts prisoner-organizer and chapter president of the National Prisoners Reform Association (NPRA) at MCI-Walpole. In March 1973, after a prolonged hunger strike and growing prisoner self-organization, the state withdrew uniformed correctional officers from inside Walpole; for the next three months — until the state forcibly retook the prison that summer — Walpole was effectively self-governed by NPRA prisoners. Dellelo helped maintain order, organize work crews, mediate disputes, and run inmate-led programs in what is widely considered the most sustained experiment in prisoner self-government in U.S. history. When the state retook the prison, Dellelo was beaten and held in solitary confinement; he subsequently spent decades in maximum-security and supermax isolation. Co-founded the Coalition for Effective Public Safety and led prisoner-rights litigation in Massachusetts for the rest of his life.',
                'state' => 'Massachusetts',
                'race' => 'White',
                'gender' => 'Male',
                'ideologies' => ['Prisoner self-governance', 'Anti-solitary'],
                'affiliation' => ['National Prisoners Reform Association'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'MCI-Walpole',
                    'institution_state' => 'Massachusetts',
                    'charges' => 'Held throughout the prisoner-run period of MCI-Walpole, March-June 1973; subjected to extended solitary confinement after state takeover.',
                    'arrest_date' => '1973-03-15',
                    'sentence' => 'Decades in maximum-security and supermax solitary after the Walpole takeover; specific NPRA-era charges not formally indicted.',
                ]],
            ],
        ];
    }
}
