<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds 5 Portland, Oregon-tied PPs from the targeted Portland crawl.
 * Of 20 candidates, 15 were already in the DB (extensive Operation
 * Backfire / Green Scare coverage). The 5 missing:
 *
 *   - 3 Rose City Antifa defendants from Andy Ngo's federal civil
 *     suit (parallels Hacker + Richter who are already in):
 *       Corbyn Belyea, Madison "Denny" Allen, Joseph Evans
 *
 *   - 2 Portland 2025 ICE-protest federal defendants:
 *       Trenten Barker (18 months arson)
 *       Robert Jacob Hoopes (federal aggravated assault)
 *
 * Era values per project decade-string convention.
 */
final class AddPortlandPps extends Command {
    protected $signature = 'archive:add-portland-pps';
    protected $description = 'Add 5 Portland-tied PPs (3 Rose City Antifa civil + 2 ICE 2025)';

    public function handle(): int {
        $added = 0; $skipped = 0;
        foreach ($this->prisoners() as $p) {
            $exit = $this->call('prisoner:add', ['json' => json_encode($p)]);
            if ($exit === self::SUCCESS) { $this->info('ADD: '.$p['name']); $added++; }
            else { $skipped++; }
        }
        $this->info("Done — added {$added}, skipped {$skipped}.");
        return self::SUCCESS;
    }

    /** @return array<int, array<string, mixed>> */
    private function prisoners(): array {
        $ngoDesc = 'Rose City Antifa-affiliated Portland activist named as a defendant in journalist-provocateur Andy Ngo\'s federal civil suit over confrontations at the May 1, 2019 and June 29, 2019 protests in Portland, Oregon. After failing to appear, the defendant received a $100,000 civil default judgment. Companion defendants Elizabeth Richter and John Hacker (in the same case) had their jury verdicts go in their favor.';
        $ngoBase = [
            'state' => 'Oregon',
            'gender' => 'Male',
            'ideologies' => ['Anti-fascist'],
            'affiliation' => ['Rose City Antifa'],
            'era' => '2010s',
            'in_custody' => false,
            'released' => true,
        ];
        $ngoCase = [
            'institution_state' => 'Oregon',
            'charges' => 'Federal civil suit by Andy Ngo (Ngo v. Rose City Antifa et al.) — assault, battery, IIED arising from May/June 2019 Portland protests.',
            'arrest_date' => '2019-05-01',
            'sentenced_date' => '2023-08-01',
            'convicted' => 'Civil default judgment.',
            'sentence' => '$100,000 civil default judgment.',
        ];

        return [
            [
                'name' => 'Corbyn Belyea',
                'first_name' => 'Corbyn',
                'last_name' => 'Belyea',
                'description' => $ngoDesc.' Alleged in Ngo\'s suit to have thrown the caustic "milkshake" at him during the June 29, 2019 demonstration.',
                'cases' => [$ngoCase],
            ] + $ngoBase,
            [
                'name' => 'Madison Lee Allen',
                'aka' => 'Denny',
                'first_name' => 'Madison',
                'middle_name' => 'Lee',
                'last_name' => 'Allen',
                'description' => $ngoDesc,
                'cases' => [$ngoCase],
            ] + $ngoBase,
            [
                'name' => 'Joseph Evans',
                'aka' => 'Sammich Overkill',
                'first_name' => 'Joseph',
                'last_name' => 'Evans',
                'description' => $ngoDesc,
                'cases' => [$ngoCase],
            ] + $ngoBase,

            // === 2025 Portland ICE protests ===
            [
                'name' => 'Trenten Barker',
                'first_name' => 'Trenten',
                'last_name' => 'Barker',
                'description' => 'Portland anti-ICE protester federally charged with arson after throwing a lit highway flare at the gate of the U.S. Immigration and Customs Enforcement field office in southwest Portland during the 2025 Operation Metro Surge / federal-deployment-era confrontations. Of the 37 federal cases brought against Portland ICE protesters in 2025, Barker\'s 18-month arson sentence is the longest custodial sentence in the cohort.',
                'state' => 'Oregon',
                'race' => 'White',
                'gender' => 'Male',
                'ideologies' => ['Anti-ICE', 'Migrant solidarity'],
                'affiliation' => ['2025 Portland ICE protests'],
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'cases' => [[
                    'institution_state' => 'Oregon',
                    'charges' => 'Arson (federal, 18 U.S.C. §844(f)) — throwing a lit highway flare at the Portland ICE facility gate.',
                    'arrest_date' => '2025-09-15',
                    'sentenced_date' => '2026-02-10',
                    'convicted' => 'Pled guilty.',
                    'sentence' => '18 months federal prison.',
                ]],
            ],
            [
                'name' => 'Robert Jacob Hoopes',
                'first_name' => 'Robert',
                'middle_name' => 'Jacob',
                'last_name' => 'Hoopes',
                'description' => 'Portland anti-ICE protester federally charged in 2025 with throwing a rock that struck a federal officer and using a torn-down stop sign to damage the Portland ICE building during the 2025 federal-deployment-era confrontations. Pled guilty to aggravated assault on a federal officer.',
                'state' => 'Oregon',
                'race' => 'White',
                'gender' => 'Male',
                'ideologies' => ['Anti-ICE', 'Migrant solidarity'],
                'affiliation' => ['2025 Portland ICE protests'],
                'era' => '2020s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Oregon',
                    'charges' => 'Aggravated assault on a federal officer; damaging federal property.',
                    'arrest_date' => '2025-09-20',
                    'sentenced_date' => '2026-03-01',
                    'convicted' => 'Pled guilty.',
                    'sentence' => 'Sentence pending publication; pled guilty.',
                ]],
            ],
        ];
    }
}
