<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Two major 1971 political-prisoner cases The Black Panther covered heavily,
 * whose members were missing from the database:
 *
 *  - The San Quentin Six — prisoners charged over the August 21, 1971 events at
 *    San Quentin in which George Jackson was killed. Fleeta Drumgo and David
 *    Johnson are already recorded; added here are Hugo Pinell, Johnny Spain,
 *    Willie Tate and Luis Talamantez (tried 1975-76 before Judge Henry J.
 *    Broderick, prosecutor D.A. Jerry Herman; verdicts Aug 12, 1976).
 *  - The Wilmington Ten — nine Black men and one white woman convicted Oct 17,
 *    1972 of firebombing a Wilmington, N.C. grocery and conspiracy during the
 *    Feb 1971 unrest; imprisoned Feb 2, 1976 after ~5 years on appeal bond;
 *    convictions overturned Dec 4, 1980; pardoned of innocence by Gov. Perdue
 *    Dec 31, 2012.
 *
 * Idempotent: skips any name already present.
 */
final class AddSanQuentinSixWilmingtonTen extends Command
{
    protected $signature = 'prisoners:add-sq6-wilmington10';

    protected $description = 'Add the San Quentin Six (missing members) and the Wilmington Ten';

    public function handle(): int
    {
        $added = 0;
        $skipped = 0;

        foreach ($this->records() as $r) {
            if (Prisoner::withUnderReview()->where('name', $r['name'])->exists()) {
                $this->warn("Exists, skipping: {$r['name']}");
                $skipped++;

                continue;
            }

            DB::transaction(function () use ($r) {
                $cases = $r['cases'] ?? [];
                unset($r['cases']);
                $prisoner = Prisoner::create($r);
                foreach ($cases as $c) {
                    if (! empty($c['institution_name'])) {
                        $inst = Institution::firstOrCreate(
                            ['name' => $c['institution_name']],
                            ['city' => $c['institution_city'] ?? null, 'state' => $c['institution_state'] ?? null],
                        );
                        $c['institution_id'] = $inst->id;
                    }
                    unset($c['institution_name'], $c['institution_city'], $c['institution_state']);
                    $c['prisoner_id'] = $prisoner->id;
                    PrisonerCase::create($c);
                }
            });

            $this->info("Added: {$r['name']}");
            $added++;
        }

        $this->info("\nDone. Added={$added} Skipped={$skipped}");

        return self::SUCCESS;
    }

    private function records(): array
    {
        $herman = 'Marin County District Attorney Jerry Herman';
        $broderick = 'Henry J. Broderick';
        $sq6 = function (string $charges, string $convicted, array $extra = []) use ($herman, $broderick): array {
            return array_merge([
                'institution_name' => 'San Quentin State Prison',
                'institution_city' => 'San Quentin',
                'institution_state' => 'California',
                'charges' => $charges,
                'convicted' => $convicted,
                'prosecutor' => $herman,
                'judge' => $broderick,
            ], $extra);
        };

        // Wilmington Ten scaffold (shared case; per-person sentence + opts).
        // Trial prosecutor James "Jay" Stroud; presiding Judge Robert Martin.
        $stroud = 'James "Jay" Stroud';
        $martin = 'Robert Martin';
        $wilm = function (string $name, string $first, string $last, string $sentence, array $opts = []) use ($stroud, $martin): array {
            $rec = [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => ($opts['female'] ?? false) ? 'Female' : 'Male',
                'race' => ($opts['white'] ?? false) ? 'White' : 'Black',
                'state' => 'North Carolina',
                'era' => '1970s',
                'ideologies' => ['Civil rights', 'Black liberation'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "{$name} was one of the Wilmington Ten, convicted on October 17, 1972 of firebombing Mike's Grocery and conspiracy to assault emergency personnel during the racial unrest in Wilmington, North Carolina in February 1971.".($opts['descExtra'] ?? '').' The ten were sentenced to a combined 282 years; after roughly five years free on appeal bond they began serving their terms on February 2, 1976. Their case became an international cause célèbre (Amnesty International named them prisoners of conscience); a federal appeals court overturned the convictions on December 4, 1980, and North Carolina Governor Beverly Perdue granted the ten pardons of innocence on December 31, 2012.',
                'cases' => [[
                    'institution_state' => 'North Carolina',
                    'charges' => "Arson (firebombing of Mike's Grocery, Wilmington, N.C.) and conspiracy to assault emergency personnel",
                    'incarceration_date' => '1976-02-02',
                    'convicted' => 'Yes — convicted October 17, 1972; conviction overturned December 4, 1980; pardoned of innocence December 31, 2012',
                    'sentence' => $sentence,
                    'prosecutor' => $stroud,
                    'judge' => $martin,
                ]],
            ];
            if (! empty($opts['birthdate'])) {
                $rec['birthdate'] = $opts['birthdate'];
            }
            if (! empty($opts['death_date'])) {
                $rec['death_date'] = $opts['death_date'];
            }

            return $rec;
        };

        return [
            // ---- San Quentin Six (the four not already recorded) ----
            [
                'name' => 'Hugo Pinell',
                'first_name' => 'Hugo',
                'last_name' => 'Pinell',
                'aka' => 'Yogi; Dahariki Kambon',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1970s',
                'birthdate' => '1945-03-10',
                'death_date' => '2015-08-12',
                'ideologies' => ['Black liberation', 'New Afrikan', 'Revolutionary socialism'],
                'affiliation' => ['Black Panther Party'],
                'in_custody' => false,
                'released' => false,
                'awaiting_trial' => false,
                'description' => 'Hugo "Yogi" Pinell, a Nicaraguan-born revolutionary and comrade of George Jackson, was one of the San Quentin Six, charged over the August 21, 1971 events in the prison\'s Adjustment Center in which George Jackson and five others were killed. Imprisoned since a 1965 rape conviction (and separately convicted of the 1971 killing of a Soledad guard), he was convicted in the 1975-76 San Quentin Six trial of two counts of felony assault on guards while being acquitted of murder and conspiracy. He became one of the longest-held prisoners in solitary confinement in U.S. history — roughly 45 years, including about 24 at the Pelican Bay SHU — and authorities labeled him a Black Guerrilla Family leader, a claim he denied. He was stabbed to death by other inmates at New Folsom prison on August 12, 2015.',
                'cases' => [$sq6(
                    'Two counts of felony assault on correctional officers, arising from the August 21, 1971 events at San Quentin (acquitted of murder and conspiracy)',
                    'Yes — convicted of two counts of felony assault (August 12, 1976); acquitted of murder and conspiracy',
                    ['sentence' => 'An additional life sentence (he was already serving life); held in solitary confinement for roughly 45 years until his death in 2015'],
                )],
            ],
            [
                'name' => 'Johnny Spain',
                'first_name' => 'Johnny',
                'last_name' => 'Spain',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1970s',
                'birthdate' => '1949-07-30',
                'ideologies' => ['Black Power', 'Black revolutionary nationalism', 'Revolutionary socialism'],
                'affiliation' => ['Black Panther Party'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Johnny Larry Spain (born Larry Michael Armstrong) was a Black Panther and the closest comrade of George Jackson at San Quentin — the only one of the San Quentin Six convicted of murder over the August 21, 1971 events. Already serving a life term for a 1966 Los Angeles robbery-homicide committed at age 17, he was convicted in 1976 of two counts of murder and conspiracy. He was released on bail in 1988, and in 1989 the federal courts overturned the conviction on the ground that his being shackled and chained throughout the long trial had violated due process; he was never retried. His life was chronicled in the book "Black Power, White Blood."',
                'cases' => [$sq6(
                    'Two counts of murder and conspiracy, arising from the August 21, 1971 events at San Quentin (the only one of the six convicted of murder)',
                    'Yes — convicted of two counts of murder and conspiracy (August 12, 1976); released on bail in 1988, conviction overturned in 1989 (he had been shackled throughout the trial), and never retried',
                    ['sentence' => 'Consecutive life terms; conviction vacated 1989'],
                )],
            ],
            [
                'name' => 'Willie Tate',
                'first_name' => 'Willie',
                'last_name' => 'Tate',
                'aka' => 'Sundiata',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1970s',
                'ideologies' => ['Black liberation', 'Prison movement'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Willie "Sundiata" Tate was one of the San Quentin Six, charged over the August 21, 1971 events at San Quentin. Already paroled on his underlying sentence in January 1975, he was the one defendant not shackled during the 1975-76 trial and was acquitted of all charges — murder, conspiracy, and assault — on August 12, 1976. In 1977 he was shot and seriously wounded in San Francisco, and he went on to become a prisoners\'-rights activist.',
                'cases' => [$sq6(
                    'Murder, conspiracy, and assault, arising from the August 21, 1971 events at San Quentin',
                    'No — acquitted on all charges (August 12, 1976)',
                )],
            ],
            [
                'name' => 'Luis Talamantez',
                'first_name' => 'Luis',
                'last_name' => 'Talamantez',
                'aka' => 'Bato',
                'gender' => 'Male',
                'race' => 'Hispanic',
                'state' => 'California',
                'era' => '1970s',
                'birthdate' => '1943-04-10',
                'ideologies' => ['Chicano Movement', 'Prison movement', 'Revolutionary socialism'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Luis "Bato" Talamantez was one of the San Quentin Six, a Chicano prisoner-organizer charged over the August 21, 1971 events at San Quentin. Imprisoned on a 1966 Los Angeles armed-robbery conviction, he took an active role educating and organizing Chicano prisoners and was acquitted of all charges in the San Quentin Six case on August 12, 1976, then paroled days later. He became a poet and a co-founder of California Prison Focus, documenting abuses in solitary confinement and the Pelican Bay SHU for decades afterward.',
                'cases' => [$sq6(
                    'Murder, conspiracy, and assault, arising from the August 21, 1971 events at San Quentin',
                    'No — acquitted on all charges (August 12, 1976)',
                    ['release_date' => '1976-08-20'],
                )],
            ],

            // ---- The Wilmington Ten ----
            $wilm('Benjamin Chavis', 'Benjamin', 'Chavis', 'The longest of the ten sentences — 34 years', [
                'birthdate' => '1948-01-22',
                'descExtra' => ' A young civil-rights organizer for the United Church of Christ Commission for Racial Justice, Benjamin Chavis (later Benjamin Chavis Muhammad) drew the longest sentence and was the last of the ten paroled, in December 1979; he went on to serve as executive director of the NAACP (1993-94) and to help organize the 1995 Million Man March.',
            ]),
            $wilm('Connie Tindall', 'Connie', 'Tindall', '31 years', ['death_date' => '2012-08-03']),
            $wilm('Marvin Patrick', 'Marvin', 'Patrick', '29 years', ['death_date' => '2020-12-19', 'descExtra' => ' Known as "Chilly."']),
            $wilm('Wayne Moore', 'Wayne', 'Moore', '29 years'),
            $wilm('Reginald Epps', 'Reginald', 'Epps', '28 years'),
            $wilm('Jerry Jacobs', 'Jerry', 'Jacobs', '29 years', ['descExtra' => ' He died in 1989.']),
            $wilm('James McKoy', 'James', 'McKoy', '29 years', ['death_date' => '2023-11-10', 'descExtra' => ' Known as "Bun."']),
            $wilm('Willie Earl Vereen', 'Willie', 'Vereen', '29 years', ['death_date' => '2024-05-25']),
            $wilm('William Joe Wright', 'William', 'Wright', '29 years', ['descExtra' => ' He died around 1990, before the 2012 pardons.']),
            $wilm('Ann Shepard', 'Ann', 'Shepard', '15 years — the lightest of the ten', [
                'descExtra' => ' A white anti-poverty worker (also recorded as Anne Sheppard Turner), the only woman and only white member of the ten; she reportedly died in 2011. Her exact sentence is reported inconsistently.',
                'female' => true,
                'white' => true,
            ]),
        ];
    }
}
