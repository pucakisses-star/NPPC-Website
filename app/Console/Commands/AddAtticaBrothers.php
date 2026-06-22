<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Attica Brothers — three leaders/defendants of the September 1971 Attica
 * uprising and its prosecutions, surfaced in The Black Panther's coverage of the
 * Attica murder trials (Feb. 1975) and missing from the database (John Hill /
 * Dacajeweiah, Charles Joe Pernasilice, Jomo Joka Omowale and Roger Champen are
 * already recorded):
 *
 *  - Frank "Big Black" Smith — head of security in the yard during the uprising,
 *    notoriously tortured by guards after the retaking; later lead plaintiff in
 *    the inmates' civil-rights suit.
 *  - Shango Bahati Kakawana (Bernard Stroble) — indicted for murder and
 *    kidnapping over the deaths of two inmates; acquitted of all charges.
 *  - Herbert X. Blyden — a chief negotiator and spokesman for the rebelling
 *    prisoners.
 *
 * Idempotent: skips any name already present.
 */
final class AddAtticaBrothers extends Command
{
    protected $signature = 'prisoners:add-attica-brothers';

    protected $description = 'Add Attica Brothers Frank "Big Black" Smith, Shango Bahati Kakawana, and Herbert X. Blyden';

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
        $ctx = ' During the September 1971 uprising at New York\'s Attica prison — in which inmates seized the facility and held hostages for four days before Governor Nelson Rockefeller ordered an armed assault that killed 39 men — the state, in December 1972, indicted 62 surviving inmates on some 1,289 counts while charging no trooper or guard; the Attica Brothers Legal Defense organized their cases, and Governor Hugh Carey closed the inmate prosecutions in 1976.';

        return [
            [
                'name' => 'Frank Smith',
                'first_name' => 'Frank',
                'last_name' => 'Smith',
                'aka' => 'Big Black',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'New York',
                'era' => '1970s',
                'ideologies' => ['Prison movement', 'Black liberation'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Frank "Big Black" Smith was an inmate at New York\'s Attica prison who served as head of security in the exercise yard during the September 1971 uprising, protecting the hostages. After the state retook the prison, he was singled out for notorious torture — made to lie naked for hours on a table with a football under his chin and told he would be killed if it fell, while guards beat and burned him.'.$ctx.' He was among the indicted Attica Brothers, spent years organizing the Attica Brothers Legal Defense, and became the lead plaintiff in the prisoners\' civil-rights lawsuit, which won a settlement in 2000. He died in 2004.',
                'cases' => [[
                    'institution_name' => 'Attica Correctional Facility',
                    'institution_city' => 'Attica',
                    'institution_state' => 'New York',
                    'charges' => 'Among the Attica Brothers indicted after the September 1971 uprising; tortured by guards during the retaking of the prison',
                ]],
            ],
            [
                'name' => 'Shango Bahati Kakawana',
                'first_name' => 'Shango',
                'last_name' => 'Kakawana',
                'aka' => 'Bernard Stroble',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'New York',
                'era' => '1970s',
                'ideologies' => ['Prison movement', 'Black liberation'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Shango Bahati Kakawana (Bernard Stroble) was one of the Attica Brothers, indicted on murder and kidnapping charges over the stabbing deaths of two inmates in the prison yard during the September 1971 uprising — among the most serious of the Attica prosecutions.'.$ctx.' After a closely watched trial he was acquitted of all charges.',
                'cases' => [[
                    'institution_name' => 'Attica Correctional Facility',
                    'institution_city' => 'Attica',
                    'institution_state' => 'New York',
                    'charges' => 'Murder and kidnapping (the stabbing deaths of two inmates in the Attica yard during the September 1971 uprising)',
                    'convicted' => 'No — acquitted of all charges',
                ]],
            ],
            [
                'name' => 'Herbert X. Blyden',
                'first_name' => 'Herbert',
                'last_name' => 'Blyden',
                'aka' => 'Herbert Blyden',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'New York',
                'era' => '1970s',
                'ideologies' => ['Prison movement', 'Black liberation'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Herbert X. Blyden was one of the chief negotiators and spokesmen for the rebelling prisoners during the September 1971 Attica uprising, a veteran prison organizer who had also helped lead the 1970 rebellion at the Tombs (the Manhattan House of Detention).'.$ctx.' He was among the indicted Attica Brothers and a named plaintiff in the prisoners\' federal litigation.',
                'cases' => [[
                    'institution_name' => 'Attica Correctional Facility',
                    'institution_city' => 'Attica',
                    'institution_state' => 'New York',
                    'charges' => 'Among the Attica Brothers indicted after the September 1971 uprising, in which he was a chief negotiator for the prisoners',
                ]],
            ],
        ];
    }
}
