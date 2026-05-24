<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds 7 PPs surfaced from the deep crawl of Wikipedia's "List of
 * incidents of civil unrest in the US" article. 20 of 27 candidates
 * were already in the DB; these are the missing ones:
 *
 *   - Charles Pernasilice — Attica Uprising co-defendant (1971/75)
 *   - Namir Abdul Mateen (James Were) — Lucasville death row
 *   - Bill Blizzard — UMWA leader, Battle of Blair Mountain 1921
 *   - Allen Bullock, Donta Betts, Darius Stewart — 2015 Baltimore
 *     Uprising defendants
 *   - Tia Pugh — Mobile AL 2020 protester, federally prosecuted for
 *     breaking police-car windows
 *
 * Era values per project decade-string convention.
 */
final class AddCivilUnrestPps extends Command {
    protected $signature = 'archive:add-civil-unrest-pps';
    protected $description = 'Add 7 PPs from civil unrest crawl (Attica, Lucasville, Blair Mountain, Baltimore 2015, Mobile 2020)';

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
            [
                'name' => 'Charles Pernasilice',
                'first_name' => 'Charles',
                'last_name' => 'Pernasilice',
                'description' => 'Catawba prisoner at Attica Correctional Facility at the time of the September 1971 uprising. Indicted with John Hill (Dacajeweiah) for the death of officer William Quinn during the prisoner takeover. Convicted of attempted second-degree assault in the 1975 Attica murder trial. Sentenced to up to 2 years on top of his prior sentence. Both his and Hill\'s convictions remain among the only Attica-uprising prosecutions to result in jail time, despite the state\'s killing of 39 people during its retaking of the prison.',
                'state' => 'New York',
                'race' => 'Indigenous',
                'gender' => 'Male',
                'ideologies' => ['Prison rebellion', 'Indigenous resistance'],
                'affiliation' => ['Attica Uprising'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Attica Correctional Facility',
                    'institution_state' => 'New York',
                    'charges' => 'Attempted second-degree assault — for role in the September 1971 Attica uprising killing of officer William Quinn.',
                    'arrest_date' => '1971-09-13',
                    'sentenced_date' => '1975-04-05',
                    'convicted' => 'Yes.',
                    'sentence' => 'Up to 2 years additional time.',
                ]],
            ],
            [
                'name' => 'Namir Abdul Mateen',
                'aka' => 'James Were',
                'first_name' => 'Namir',
                'last_name' => 'Mateen',
                'description' => 'Black Muslim prisoner at Southern Ohio Correctional Facility, sentenced to death for the killing of correctional officer Robert Vallandingham during the April 1993 Lucasville prison uprising — the longest prison riot in U.S. history. Sentenced alongside Keith LaMar (Bomani Shakur), Siddique Abdullah Hasan (Carlos Sanders), and others. Movement supporters have long argued the prosecutions were political — the state needed to convict leaders to shore up the legitimacy of the violent retaking — and the trials were marred by withheld exculpatory evidence and snitch testimony.',
                'state' => 'Ohio',
                'race' => 'Black',
                'gender' => 'Male',
                'ideologies' => ['Black Muslim', 'Prison rebellion'],
                'affiliation' => ['Lucasville Uprising'],
                'era' => '1990s',
                'in_custody' => true,
                'released' => false,
                'cases' => [[
                    'institution_name' => 'Ohio State Penitentiary (Youngstown)',
                    'institution_state' => 'Ohio',
                    'charges' => 'Aggravated murder of CO Robert Vallandingham (Lucasville Uprising, April 11–21, 1993).',
                    'arrest_date' => '1993-04-21',
                    'sentenced_date' => '1995-09-21',
                    'convicted' => 'Yes.',
                    'sentence' => 'Death (currently held in custody at Ohio State Penitentiary, Youngstown).',
                ]],
            ],
            [
                'name' => 'Bill Blizzard',
                'first_name' => 'William',
                'aka' => 'Bill Blizzard',
                'last_name' => 'Blizzard',
                'description' => 'United Mine Workers of America organizer who led approximately 10,000 armed coal miners up Blair Mountain in West Virginia in August–September 1921 — the largest armed labor uprising in U.S. history. Indicted for treason against the State of West Virginia after the battle (the largest mass treason prosecution since the Whiskey Rebellion). His acquittal in 1922 caused the prosecution of approximately 985 other indicted miners to collapse; the last paroled in 1925. Continued UMWA organizing for decades and became district president.',
                'state' => 'West Virginia',
                'race' => 'White',
                'gender' => 'Male',
                'birthdate' => '1892-08-19',
                'death_date' => '1958-07-30',
                'ideologies' => ['Labor', 'UMWA'],
                'affiliation' => ['United Mine Workers of America'],
                'era' => '1920s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'West Virginia',
                    'charges' => 'Treason against the State of West Virginia (Battle of Blair Mountain, August–September 1921).',
                    'arrest_date' => '1921-09-01',
                    'sentenced_date' => '1922-05-25',
                    'convicted' => 'No — acquitted at trial; the verdict caused the ~985 other miner indictments to collapse.',
                    'sentence' => 'Acquitted.',
                ]],
            ],
            [
                'name' => 'Allen Bullock',
                'first_name' => 'Allen',
                'last_name' => 'Bullock',
                'description' => 'Black teenage Baltimore protester whose photograph smashing a Baltimore Police Department patrol car windshield with a traffic cone during the April 27, 2015 Freddie Gray uprising became one of the iconic images of the rebellion. Charged with eight counts; the state sought a $500,000 bail. Pled guilty to malicious destruction of property and rioting; sentenced to 12 years in prison, all but 6 months suspended, plus community service and probation.',
                'state' => 'Maryland',
                'race' => 'Black',
                'gender' => 'Male',
                'ideologies' => ['Anti-police violence', 'Black uprising'],
                'affiliation' => ['Freddie Gray protests / Baltimore Uprising'],
                'era' => '2010s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Maryland',
                    'charges' => 'Malicious destruction of property, rioting (Baltimore Uprising, April 27, 2015).',
                    'arrest_date' => '2015-04-29',
                    'sentenced_date' => '2015-09-25',
                    'convicted' => 'Pled guilty.',
                    'sentence' => '12 years; all but 6 months suspended; community service and probation.',
                ]],
            ],
            [
                'name' => 'Donta Betts',
                'first_name' => 'Donta',
                'last_name' => 'Betts',
                'description' => 'Black Baltimore 20-year-old federally convicted for his role in the April 2015 Freddie Gray uprising — specifically for making a destructive device (lighter fluid on propane tanks) intended for use during the unrest. Sentenced to 15 years in federal prison, the longest sentence from the Baltimore Uprising.',
                'state' => 'Maryland',
                'race' => 'Black',
                'gender' => 'Male',
                'ideologies' => ['Anti-police violence', 'Black uprising'],
                'affiliation' => ['Freddie Gray protests / Baltimore Uprising'],
                'era' => '2010s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Maryland',
                    'charges' => 'Making a destructive device; related federal counts (Baltimore Uprising, April 2015).',
                    'arrest_date' => '2015-05-01',
                    'sentenced_date' => '2016-08-01',
                    'convicted' => 'Yes — federal conviction.',
                    'sentence' => '15 years federal prison.',
                ]],
            ],
            [
                'name' => 'Darius Stewart',
                'first_name' => 'Darius',
                'last_name' => 'Stewart',
                'description' => 'Black Baltimore defendant convicted of arson on West North Avenue during the April 2015 Freddie Gray uprising. Sentenced to 5 years in prison.',
                'state' => 'Maryland',
                'race' => 'Black',
                'gender' => 'Male',
                'ideologies' => ['Anti-police violence', 'Black uprising'],
                'affiliation' => ['Freddie Gray protests / Baltimore Uprising'],
                'era' => '2010s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Maryland',
                    'charges' => 'Arson (Baltimore Uprising, April 2015 — West North Avenue).',
                    'arrest_date' => '2015-05-01',
                    'convicted' => 'Yes.',
                    'sentence' => '5 years.',
                ]],
            ],
            [
                'name' => 'Tia Pugh',
                'first_name' => 'Tia',
                'last_name' => 'Pugh',
                'description' => 'Black Mobile, Alabama woman who became one of the first federal prosecutions of the 2020 George Floyd uprising — charged in federal court under the federal civil disorder statute (18 U.S.C. §231) for breaking police-cruiser windows with a baseball bat during a May 31, 2020 protest. Convicted by jury in 2021; sentenced to a financial penalty and supervised release (no prison time). Her trial became a key federal-court test of the Reconstruction-era civil disorder statute\'s constitutionality.',
                'state' => 'Alabama',
                'race' => 'Black',
                'gender' => 'Female',
                'ideologies' => ['Black Lives Matter'],
                'affiliation' => ['2020 George Floyd Uprising'],
                'era' => '2020s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Alabama',
                    'charges' => 'Civil disorder (18 U.S.C. §231) — breaking police-cruiser windows, May 31, 2020 Mobile protest.',
                    'arrest_date' => '2020-06-01',
                    'sentenced_date' => '2021-10-26',
                    'convicted' => 'Yes — by federal jury.',
                    'sentence' => 'Financial penalty + supervised release (no prison).',
                ]],
            ],
        ];
    }
}
