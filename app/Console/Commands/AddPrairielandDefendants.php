<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddPrairielandDefendants extends Command
{
    protected $signature = 'prisoners:add-prairieland';
    protected $description = 'Add the 9 Prairieland Detention Center defendants convicted by federal jury on March 13, 2026.';

    private const INCIDENT = <<<'TXT'
On July 4, 2025, a group of demonstrators gathered outside the Prairieland Detention Center, an ICE detention facility in Alvarado, Texas. During the protest, fireworks were set off and an Alvarado police officer was shot and wounded. Federal prosecutors charged the participants as members of a "North Texas Antifa Cell" — the first time the Department of Justice has brought terrorism-related charges in connection with alleged antifa activity. After a 12-day federal trial in Fort Worth, a jury returned guilty verdicts on March 13, 2026. Sentencing is scheduled for June 18, 2026.
TXT;

    public function handle(): int
    {
        $shared = [
            'state'           => 'Texas',
            'era'             => 'Modern',
            'ideologies'      => ['Antifascist'],
            'in_custody'      => true,
            'released'        => false,
            'awaiting_trial'  => false,
        ];

        $sharedCharges = 'Riot; providing material support to terrorists; conspiracy to use and carry an explosive; use and carry of an explosive (fireworks)';
        $sharedConv    = 'Yes — federal jury verdict March 13, 2026 (Northern District of Texas, Fort Worth)';
        $sharedSent    = 'Awaiting sentencing June 18, 2026 — minimum 10 years, maximum 60 years';
        $sharedInst    = ['name' => 'Tarrant County Jail', 'city' => 'Fort Worth', 'state' => 'Texas'];

        $defendants = [
            [
                'name'        => 'Cameron Arnold',
                'first_name'  => 'Cameron',
                'last_name'   => 'Arnold',
                'aka'         => 'Autumn Hill',
                'age'         => 30,
                'gender'      => 'Female',
                'description' => 'Cameron Arnold, who goes by Autumn Hill, is one of nine defendants convicted by a federal jury on March 13, 2026 in the Prairieland Detention Center case. She faces a minimum of 10 years and a maximum of 60 years in federal prison.',
            ],
            [
                'name'        => 'Zachary Evetts',
                'first_name'  => 'Zachary',
                'last_name'   => 'Evetts',
                'age'         => 36,
                'gender'      => 'Male',
                'description' => 'Zachary Evetts is one of nine defendants convicted by a federal jury on March 13, 2026 in the Prairieland Detention Center case. He faces a minimum of 10 years and a maximum of 60 years in federal prison.',
            ],
            [
                'name'        => 'Benjamin Song',
                'first_name'  => 'Benjamin',
                'last_name'   => 'Song',
                'aka'         => 'Champagne',
                'age'         => 32,
                'gender'      => 'Male',
                'description' => 'Benjamin Song, also known as Champagne, was the only defendant convicted of attempted murder in the Prairieland Detention Center case. The federal jury found him guilty on March 13, 2026 of attempted murder of officers and employees of the United States and three counts of discharging a firearm during a crime of violence. He faces a minimum of 20 years and up to life in federal prison.',
                'case'        => [
                    'charges'   => 'Attempted murder of officers and employees of the United States; three counts of discharging a firearm during a crime of violence; riot; providing material support to terrorists; conspiracy to use and carry an explosive; use and carry of an explosive (fireworks)',
                    'sentence'  => 'Awaiting sentencing June 18, 2026 — minimum 20 years, maximum life',
                ],
            ],
            [
                'name'        => 'Savanna Batten',
                'first_name'  => 'Savanna',
                'last_name'   => 'Batten',
                'age'         => 32,
                'gender'      => 'Female',
                'description' => 'Savanna Batten is one of nine defendants convicted by a federal jury on March 13, 2026 in the Prairieland Detention Center case. She faces a minimum of 10 years and a maximum of 60 years in federal prison.',
            ],
            [
                'name'        => 'Bradford Morris',
                'first_name'  => 'Bradford',
                'last_name'   => 'Morris',
                'aka'         => 'Meagan Morris',
                'age'         => 41,
                'gender'      => 'Female',
                'description' => 'Bradford Morris, who goes by Meagan Morris, is one of nine defendants convicted by a federal jury on March 13, 2026 in the Prairieland Detention Center case. She faces a minimum of 10 years and a maximum of 60 years in federal prison.',
            ],
            [
                'name'        => 'Maricela Rueda',
                'first_name'  => 'Maricela',
                'last_name'   => 'Rueda',
                'age'         => 33,
                'gender'      => 'Female',
                'description' => 'Maricela Rueda is one of nine defendants convicted by a federal jury on March 13, 2026 in the Prairieland Detention Center case. In addition to the charges shared with the other defendants, she and her husband Daniel Sanchez Estrada were convicted of conspiracy to conceal documents. She faces a minimum of 10 years and a maximum of 60 years in federal prison.',
                'case'        => [
                    'charges' => $sharedCharges.'; conspiracy to conceal documents',
                ],
            ],
            [
                'name'        => 'Elizabeth Soto',
                'first_name'  => 'Elizabeth',
                'last_name'   => 'Soto',
                'age'         => 40,
                'gender'      => 'Female',
                'description' => 'Elizabeth Soto is one of nine defendants convicted by a federal jury on March 13, 2026 in the Prairieland Detention Center case. She faces a minimum of 10 years and a maximum of 60 years in federal prison.',
            ],
            [
                'name'        => 'Ines Soto',
                'first_name'  => 'Ines',
                'last_name'   => 'Soto',
                'age'         => 41,
                'gender'      => 'Female',
                'description' => 'Ines Soto is one of nine defendants convicted by a federal jury on March 13, 2026 in the Prairieland Detention Center case. She faces a minimum of 10 years and a maximum of 60 years in federal prison.',
            ],
            [
                'name'        => 'Daniel Sanchez Estrada',
                'first_name'  => 'Daniel',
                'middle_name' => 'Rolando',
                'last_name'   => 'Sanchez Estrada',
                'aka'         => 'Des',
                'age'         => 39,
                'gender'      => 'Male',
                'description' => 'Daniel Rolando Sanchez Estrada, known as Des, is the husband of co-defendant Maricela Rueda. He was not present at the July 4, 2025 protest but was charged after Rueda allegedly called him from jail and asked him to conceal evidence. He was convicted by a federal jury on March 13, 2026 of corruptly concealing a document or record and conspiracy to conceal documents. He faces up to 40 years in federal prison.',
                'case'        => [
                    'charges'  => 'Corruptly concealing a document or record; conspiracy to conceal documents',
                    'sentence' => 'Awaiting sentencing June 18, 2026 — maximum 40 years',
                ],
            ],
        ];

        $institution = Institution::firstOrCreate(
            ['name' => $sharedInst['name']],
            ['city' => $sharedInst['city'], 'state' => $sharedInst['state']]
        );

        $created = 0;
        $skipped = 0;

        foreach ($defendants as $d) {
            $caseOverrides = $d['case'] ?? [];
            unset($d['case']);

            $caseAttrs = [
                'institution_id' => $institution->id,
                'charges'        => $caseOverrides['charges']  ?? $sharedCharges,
                'convicted'      => $caseOverrides['convicted'] ?? $sharedConv,
                'sentence'       => $caseOverrides['sentence']  ?? $sharedSent,
            ];

            DB::transaction(function () use ($d, $shared, $caseAttrs, &$created, &$skipped) {
                $prisoner = Prisoner::where('name', $d['name'])->first();

                if ($prisoner && $prisoner->cases()->exists()) {
                    $this->warn("Skipping {$d['name']} — already exists with a case.");
                    $skipped++;
                    return;
                }

                if (! $prisoner) {
                    $prisoner = Prisoner::create(array_merge($shared, $d, [
                        'description' => $d['description']."\n\n".self::INCIDENT,
                    ]));
                    $this->info("Added {$prisoner->name} (slug: {$prisoner->slug})");
                } else {
                    $this->info("Found existing {$prisoner->name} without a case — adding case.");
                }

                PrisonerCase::create(array_merge($caseAttrs, ['prisoner_id' => $prisoner->id]));
                $created++;
            });
        }

        $this->info("\nDone. Created {$created}, skipped {$skipped}.");

        return self::SUCCESS;
    }
}
