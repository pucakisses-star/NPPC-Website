<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Portland prison-sentence roster extras surfaced from the deeper
 * pass through DOJ press releases, Seattle Times / KGW / OPB
 * reporting, and NLG Portland post-conviction summaries.
 *
 *   - Jennifer Kolar    — ELF "The Family" cell, 2001 UW Center for
 *                          Urban Horticulture arson; 60mo federal
 *   - Lacey Phillabaum  — ELF "The Family", same UW arson; 36mo
 *   - Gabriel Agard-Berryhill — threw an incendiary at the Mark O.
 *                          Hatfield U.S. Courthouse during the 2020
 *                          Portland uprising; federal time served
 *                          plus halfway-house placement
 *
 * Marquise Love (BLM-era motorist beating, 20mo) was reviewed and
 * excluded: outside the movement-defense scope of this archive.
 */
final class AddPortlandRosterExtras extends Command {
    protected $signature = 'archive:add-portland-roster-extras';
    protected $description = 'Add 3 Portland-tied PPs (2 ELF UW arson + 1 Hatfield Courthouse 2020)';

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
        $elfDesc = 'Member of the Earth Liberation Front "The Family" cell — the Pacific Northwest ELF/ALF affiliation responsible for a series of arsons across Oregon, Washington, California, Wyoming, and Colorado from 1996 to 2001 totaling roughly $40 million in damage. Convicted in the federal Operation Backfire prosecution that broke open in 2005 after cooperator Jacob Ferguson wore a wire. Cooperated with the government after arrest, receiving a substantially reduced sentence; participated specifically in the May 21, 2001 arson at the University of Washington Center for Urban Horticulture, which had been incorrectly targeted as a hub of GMO poplar research.';
        $elfBase = [
            'race' => 'White',
            'gender' => 'Female',
            'ideologies' => ['Eco-defense', 'Earth Liberation Front'],
            'affiliation' => ['Earth Liberation Front', 'The Family'],
            'era' => '2000s',
            'in_custody' => false,
            'released' => true,
        ];

        return [
            [
                'name' => 'Jennifer Kolar',
                'first_name' => 'Jennifer',
                'last_name' => 'Kolar',
                'description' => $elfDesc.' Kolar — a Seattle-based computer-vision researcher at the time — was sentenced in May 2008 to 60 months in federal prison and ordered to pay $6 million in restitution. The UW Horticulture fire she helped set was the largest single arson loss in the Operation Backfire series.',
                'state' => 'Washington',
                'cases' => [[
                    'institution_state' => 'Washington',
                    'charges' => 'Federal arson and conspiracy (UW Center for Urban Horticulture, May 21, 2001; Operation Backfire prosecution).',
                    'arrest_date' => '2006-03-01',
                    'sentenced_date' => '2008-05-22',
                    'convicted' => 'Pled guilty (cooperator).',
                    'sentence' => '60 months federal prison; $6 million restitution.',
                ]],
            ] + $elfBase,
            [
                'name' => 'Lacey Phillabaum',
                'first_name' => 'Lacey',
                'last_name' => 'Phillabaum',
                'description' => $elfDesc.' Phillabaum — a former Earth First! Journal editor — was sentenced in May 2008 to 36 months in federal prison and ordered to pay $6 million in restitution. The UW Center for Urban Horticulture fire she helped set destroyed Toby Bradshaw\'s office; subsequent reporting confirmed the lab held no GMO poplars, making the action one of the most-criticized targeting choices in ELF history.',
                'state' => 'Washington',
                'cases' => [[
                    'institution_state' => 'Washington',
                    'charges' => 'Federal arson and conspiracy (UW Center for Urban Horticulture, May 21, 2001; Operation Backfire prosecution).',
                    'arrest_date' => '2006-05-01',
                    'sentenced_date' => '2008-05-22',
                    'convicted' => 'Pled guilty (cooperator).',
                    'sentence' => '36 months federal prison; $6 million restitution.',
                ]],
            ] + $elfBase,
            [
                'name' => 'Gabriel Agard-Berryhill',
                'first_name' => 'Gabriel',
                'last_name' => 'Agard-Berryhill',
                'description' => 'Portland 2020 uprising defendant — 19 years old at the time — federally charged with civil disorder and arson for throwing a homemade incendiary device toward the front doors of the Mark O. Hatfield United States Courthouse in downtown Portland on the night of July 28, 2020, during the multi-week federal-officer-deployment confrontation. Held in pretrial federal detention for roughly five months before being sentenced in 2021 to time served plus a 90-day halfway-house placement and supervised release. One of the small handful of Portland 2020 defendants to receive a federal arson conviction.',
                'state' => 'Oregon',
                'race' => 'White',
                'gender' => 'Male',
                'ideologies' => ['Anti-fascist', 'Black Lives Matter'],
                'affiliation' => ['2020 Portland uprising'],
                'era' => '2020s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Oregon',
                    'charges' => 'Federal arson (18 U.S.C. §844(f)); civil disorder — incendiary device thrown at Hatfield U.S. Courthouse, July 28, 2020.',
                    'arrest_date' => '2020-07-29',
                    'sentenced_date' => '2021-04-15',
                    'convicted' => 'Pled guilty.',
                    'sentence' => 'Time served (~5 months federal pretrial) + 90 days halfway house + supervised release.',
                ]],
            ],
        ];
    }
}
