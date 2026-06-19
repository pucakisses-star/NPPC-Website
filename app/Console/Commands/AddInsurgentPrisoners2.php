<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * A second batch of figures documented in THE INSURGENT (1986–89) that were
 * held back as judgment calls on the first pass and added at the user's
 * request:
 *
 *  - John Perotti     — IWW jailhouse organizer at Lucasville (SOCF), Ohio.
 *  - Mujahid Farid    — New Afrikan prisoner-educator (PEPA), Auburn, NY.
 *  - Bob Brown        — AAPRP organizer jailed for the same 1988 grand-jury
 *                       resistance as Vernon Bellecourt.
 *  - John Hyde        — anti-nuclear activist convicted of assault after the
 *                       1988 Lexington control-unit court hearings.
 *
 * Idempotent: skips any person whose name already exists. Uncertain dates are
 * left blank rather than guessed.
 */
final class AddInsurgentPrisoners2 extends Command
{
    protected $signature = 'prisoners:add-insurgent-prisoners-2';

    protected $description = 'Add the four held-back Insurgent figures (Perotti, Farid, Bob Brown, Hyde)';

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
        return [
            [
                'name' => 'John Perotti',
                'first_name' => 'John',
                'last_name' => 'Perotti',
                'gender' => 'Male',
                'race' => 'White',
                'state' => 'Ohio',
                'era' => '1980s',
                'ideologies' => ["Prisoners' Rights"],
                'affiliation' => ['Industrial Workers of the World (IWW)'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'John Perotti was a militant jailhouse organizer and member of the Industrial Workers of the World (IWW) who spent much of his life in the Ohio prison system, largely at the Southern Ohio Correctional Facility (Lucasville). A relentless prisoners\'-rights activist and jailhouse lawyer, he was repeatedly beaten and thrown into isolation in retaliation for organizing against prison conditions and guard brutality, and filed numerous brutality lawsuits; in one incident he stabbed a guard who attacked him. The prisoner-support movement regarded him as a political prisoner for the retaliation he endured for his activism, though his original imprisonment was not for a political offense. He kept organizing from behind the walls for decades.',
                'cases' => [
                    [
                        'charges' => 'Held at the Southern Ohio Correctional Facility (Lucasville); subjected to repeated beatings, isolation, and disciplinary/criminal charges in retaliation for IWW jailhouse organizing and prisoners\'-rights litigation',
                    ],
                ],
            ],
            [
                'name' => 'Mujahid Farid',
                'first_name' => 'Mujahid',
                'last_name' => 'Farid',
                'gender' => 'Male',
                'race' => 'Black',
                'death_date' => '2018-11-20',
                'state' => 'New York',
                'era' => '1970s',
                'ideologies' => ['Black Liberation'],
                'affiliation' => ['New Afrikan Independence Movement'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Mujahid Farid was a New Afrikan activist who spent 33 years in New York State prisons following a 1978 conviction arising from a shootout with New York City police. While incarcerated he earned multiple degrees and became a leading prisoner-educator and organizer, co-founding the Prisoners Education Project on AIDS (PEPA) at Auburn to confront the prison AIDS crisis. Released in 2011, he founded the Release Aging People in Prison (RAPP) campaign and became a nationally known advocate for elderly prisoners until his death in 2018.',
                'cases' => [
                    [
                        'charges' => 'Convicted in 1978 in connection with a shootout with New York City police officers',
                        'convicted' => 'Convicted 1978',
                        'sentence' => 'Served 33 years (1978–2011)',
                    ],
                ],
            ],
            [
                'name' => 'Bob Brown',
                'first_name' => 'Bob',
                'last_name' => 'Brown',
                'gender' => 'Male',
                'race' => 'Black',
                'era' => '1980s',
                'ideologies' => ['Pan-Africanism'],
                'affiliation' => ["All-African People's Revolutionary Party"],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Bob Brown was an organizer with the All-African People\'s Revolutionary Party (AAPRP) who, on September 14, 1988, was jailed for civil contempt after refusing to cooperate with the federal "Operation Friendly Skies" grand jury — the same grand-jury resistance for which American Indian Movement leader Vernon Bellecourt was jailed the same day. He faced up to 18 months\' confinement or until the grand jury expired.',
                'cases' => [
                    [
                        'charges' => 'Civil contempt for refusing to cooperate with the federal "Operation Friendly Skies" grand jury',
                        'arrest_date' => '1988-09-14',
                        'convicted' => 'Held in civil contempt — up to 18 months or until the grand jury expired',
                    ],
                ],
            ],
            [
                'name' => 'John Hyde',
                'first_name' => 'John',
                'last_name' => 'Hyde',
                'gender' => 'Male',
                'era' => '1980s',
                'ideologies' => ['Anti-Nuclear', 'Anti-Militarism'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'John Hyde was an anti-nuclear activist arrested in May 1988 at the federal court hearings in Washington, D.C. over the Lexington women\'s control unit, when he and a co-defendant were dragged out and beaten by U.S. marshals for refusing to rise for the judge. His co-arrestee was acquitted, but Hyde was convicted of assault; the government cited his prior nonviolent anti-nuclear arrests in seeking a one-year term. No sentencing date had been set as of late 1988.',
                'cases' => [
                    [
                        'charges' => 'Assault (on a U.S. marshal) arising from his removal from the May 1988 federal court hearings on the Lexington women\'s control unit',
                        'convicted' => 'Convicted of assault (his co-arrestee was acquitted); no sentencing date set as of late 1988',
                    ],
                ],
            ],
        ];
    }
}
