<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds political prisoners documented in THE INSURGENT (newsletter of the
 * Committee to Fight Repression, 1986–89) who were missing from the database,
 * found by reading the six issues just added to the archive. The vast majority
 * of names in those issues are already in the DB; these are the genuine gaps:
 *
 *  - Six co-defendants of the Puerto Rican "Hartford" / Wells Fargo (Los
 *    Macheteros) case — arrested Aug. 30, 1985, held in long preventive
 *    detention; their co-defendants (Ojeda Ríos, Segarra Palmer, Camacho
 *    Negrón, Maldonado, Ayes Suárez, Luz María Berríos) are already in the DB.
 *  - Two grand-jury resisters jailed for civil contempt (Vernon Bellecourt,
 *    AIM, 1988; Samuel Sánchez, Puerto Rican-independence grand jury, 1989).
 *
 * Idempotent: skips any person whose name already exists. Unknown birth/death
 * dates and uncertain dispositions are intentionally left blank/general.
 */
final class AddInsurgentPrisoners extends Command
{
    protected $signature = 'prisoners:add-insurgent-prisoners';

    protected $description = 'Add political prisoners documented in The Insurgent that were missing (Hartford/Macheteros, grand-jury resisters)';

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
        $machetero = function (string $name, string $first, string $last, string $detail, ?string $inmate = null) {
            return [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => 'Male',
                'race' => 'Hispanic',
                'state' => 'Puerto Rico',
                'era' => '1980s',
                'ideologies' => ['Puerto Rican Independence'],
                'affiliation' => ['Los Macheteros (Ejército Popular Boricua)'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'inmate_number' => $inmate,
                'description' => "{$name} was a Puerto Rican independence activist arrested on August 30, 1985 in the mass FBI raids in Puerto Rico that targeted Los Macheteros (the Ejército Popular Boricua) in connection with the 1983 \$7.2 million Wells Fargo expropriation in West Hartford, Connecticut. Held in extended pre-trial preventive detention without bail and prosecuted in Hartford federal court, the defendants asserted that U.S. courts lacked jurisdiction over Puerto Rican patriots. {$detail}",
                'cases' => [
                    [
                        'charges' => 'Charged in the 1983 Wells Fargo expropriation (West Hartford, CT) attributed to Los Macheteros',
                        'arrest_date' => '1985-08-30',
                        'convicted' => 'Held in years-long pre-trial preventive detention; most of the principal charges in the Hartford case were ultimately dropped, pleaded down, or ended in directed acquittals',
                    ],
                ],
            ];
        };

        return [
            $machetero(
                'Norman Ramírez Talavera', 'Norman', 'Ramírez Talavera',
                'In 1989 Judge T. Emmett Clarie directed not-guilty verdicts for him and three co-defendants on the principal charges.'
            ),
            $machetero(
                'Luis Alfredo Colón Osorio', 'Luis Alfredo', 'Colón Osorio',
                'In October 1986 he began a hunger fast at MCC New York protesting his 15-month preventive detention, the conditions of his confinement, and the sexual assault of fellow prisoners Alejandrina Torres and Susan Rosenberg, and demanding the case be moved to Puerto Rico.',
                '03172-069'
            ),
            $machetero(
                'Orlando Claudio González', 'Orlando', 'Claudio González',
                'His was one of the appeals that led the Second Circuit, in late 1986, to order bail reconsidered for the defendants still held under the Bail Reform Act.',
                '03173-069'
            ),
            $machetero(
                'Yvonne Meléndez Carrión', 'Yvonne', 'Meléndez Carrión',
                'A mother, student, and school aide, she was among the women held in preventive detention and gave interviews describing the case as colonial political repression.',
                '03170-069'
            ),
            $machetero(
                'Hilton Fernández Diamante', 'Hilton', 'Fernández Diamante',
                'A longtime independence and human-rights figure, he was among the group held in preventive detention at MCC New York.',
                '03168-069'
            ),
            $machetero(
                'Elías Samuel Castro Ramos', 'Elías Samuel', 'Castro Ramos',
                'A public-school teacher and labor unionist, he was among the defendants held in preventive detention and gave interviews on the case.',
                '03169-069'
            ),
            [
                'name' => 'Vernon Bellecourt',
                'first_name' => 'Vernon',
                'last_name' => 'Bellecourt',
                'aka' => 'WaBun-Inini',
                'gender' => 'Male',
                'race' => 'Native American',
                'birthdate' => '1931-10-17',
                'death_date' => '2007-10-13',
                'state' => 'Minnesota',
                'era' => '1980s',
                'ideologies' => ['Indigenous Sovereignty', 'Native American'],
                'affiliation' => ['American Indian Movement'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Vernon Bellecourt (WaBun-Inini, 1931–2007), an Anishinaabe (White Earth Ojibwe) leader of the American Indian Movement, was jailed in September 1988 for civil contempt after refusing to cooperate with a federal grand jury (the "Operation Friendly Skies" investigation), held for 18 months or until the grand jury expired. A longtime AIM organizer and international spokesman, he refused to inform on the movement.',
                'cases' => [
                    [
                        'charges' => 'Civil contempt for refusing to testify before a federal grand jury (the "Operation Friendly Skies" investigation)',
                        'arrest_date' => '1988-09-14',
                        'convicted' => 'Held in civil contempt — 18 months or until the grand jury expired',
                    ],
                ],
            ],
            [
                'name' => 'Samuel Sánchez',
                'first_name' => 'Samuel',
                'last_name' => 'Sánchez',
                'gender' => 'Male',
                'race' => 'Hispanic',
                'state' => 'New York',
                'era' => '1980s',
                'ideologies' => ['Puerto Rican Independence'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Samuel Sánchez, a 28-year-old mental-health worker, was held in civil contempt on January 24, 1989 by Judge Eugene Nickerson for refusing to cooperate with a Brooklyn federal grand jury investigating the FALN / Puerto Rican independence movement. He had been subpoenaed days after undergoing skull-reconstruction surgery; FBI agents reportedly tried to interrogate him at his hospital bedside. He refused to inform, telling the court, "I say no to the grand jury."',
                'cases' => [
                    [
                        'charges' => 'Civil contempt for refusing to cooperate with a federal grand jury investigating the Puerto Rican independence movement',
                        'arrest_date' => '1989-01-24',
                        'convicted' => 'Held in civil contempt for grand-jury resistance',
                    ],
                ],
            ],
        ];
    }
}
