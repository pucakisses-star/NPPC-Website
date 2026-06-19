<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds four figures adjacent to the Sam Melville/Jonathan Jackson Unit (SMJJU)
 * / United Freedom Front milieu, surfaced while auditing UFF membership:
 *
 *   - Everett Carlson  — Fred Hampton Unit; convicted in the Aug 7, 1976 bombings.
 *   - Edward Gullion   — Fred Hampton Unit; FBI Ten Most Wanted #344; convicted.
 *   - Joseph Aceto     — early SMJJU/Fred Hampton Unit participant who turned
 *                        state's evidence (cooperating witness) — included with
 *                        that caveat stated plainly in the bio.
 *   - James Barrett    — convicted of a 1975 SMJJU bank robbery but, per the
 *                        court record, declined to join the cell — noted as a
 *                        one-time accomplice rather than a member.
 *
 * Idempotent: skips any person whose name already exists. Unknown birth/death
 * dates and case details are intentionally left blank rather than guessed.
 */
final class AddUffAssociates extends Command
{
    protected $signature = 'prisoners:add-uff-associates';

    protected $description = 'Add Fred Hampton Unit members (Carlson, Gullion) and SMJJU associates (Aceto, Barrett)';

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
                'name' => 'Everett Carlson',
                'first_name' => 'Everett',
                'last_name' => 'Carlson',
                'aka' => 'Picky',
                'gender' => 'Male',
                'race' => 'White',
                'era' => '1970s',
                'ideologies' => ['Anti-imperialism'],
                'affiliation' => ["Fred Hampton Unit of the People's Forces"],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "Everett \"Picky\" Carlson was a member of the Fred Hampton Unit of the People's Forces, a small clandestine leftist cell that carried out a series of bombings in Massachusetts and New Hampshire on August 7, 1976 — a National Guard armory in Dorchester, an Eastern Airlines plane at Logan Airport, the Essex County Courthouse in Newburyport, and a post office in Seabrook, New Hampshire — framed as protests against U.S. militarism and state repression. He was arrested at his home and convicted of interstate transportation of explosives with intent to injure or intimidate. (In later years, unconnected to his 1970s radical activity, Carlson accumulated a record of violent crimes, including a 1997 imprisonment for assault and kidnapping.)",
                'cases' => [
                    [
                        'charges' => "Interstate transportation of explosives with intent to injure/intimidate — the Fred Hampton Unit's August 7, 1976 bombing campaign in Massachusetts and New Hampshire",
                        'convicted' => 'Yes — convicted of federal explosives charges',
                    ],
                ],
            ],
            [
                'name' => 'Edward Gullion',
                'first_name' => 'Edward',
                'middle_name' => 'Patrick',
                'last_name' => 'Gullion',
                'aka' => 'Edward Patrick Gullion Jr.',
                'gender' => 'Male',
                'race' => 'White',
                'era' => '1970s',
                'ideologies' => ['Anti-imperialism'],
                'affiliation' => ["Fred Hampton Unit of the People's Forces"],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "Edward Patrick Gullion Jr. was a member of the Fred Hampton Unit of the People's Forces, the clandestine leftist cell behind the August 7, 1976 bombings of a National Guard armory in Dorchester, an Eastern Airlines plane at Logan Airport, the Essex County Courthouse in Newburyport, and a post office in Seabrook, New Hampshire. Placed on the FBI's Ten Most Wanted Fugitives list (#344) in August 1976, he was arrested on October 22, 1976 in Providence, Rhode Island, where he was working at a jewelry store, and was convicted of federal explosives charges. Little is documented about his life after release.",
                'cases' => [
                    [
                        'charges' => "Interstate transportation of explosives — the Fred Hampton Unit's August 7, 1976 bombing campaign in Massachusetts and New Hampshire",
                        'arrest_date' => '1976-10-22',
                        'convicted' => 'Yes — convicted of federal explosives charges',
                    ],
                ],
            ],
            [
                'name' => 'Joseph Aceto',
                'first_name' => 'Joseph',
                'last_name' => 'Aceto',
                'aka' => 'Joey; Joseph Balino (witness-protection name)',
                'gender' => 'Male',
                'race' => 'White',
                'death_date' => '2014-05-20',
                'state' => 'Maine',
                'era' => '1970s',
                'ideologies' => ['Anti-imperialism'],
                'affiliation' => ['Sam Melville/Jonathan Jackson Unit', "Fred Hampton Unit of the People's Forces"],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "Joseph \"Joey\" Aceto (died 2014) was an early participant in the Maine prison–rooted militant left of the mid-1970s — documented by a federal court as having taken part in activities of the Sam Melville/Jonathan Jackson Unit and as a member of the allied Fred Hampton Unit of the People's Forces. Arrested on July 4, 1976 near Boston with dynamite and guns in his car, he pleaded guilty to interstate transportation of explosives, served less than three years, and then turned state's evidence — becoming the government's chief informant against Raymond Levasseur, Thomas Manning, and the others — entering the federal Witness Protection Program under the name \"Balino.\" He is recorded here for his role in the group, with the explicit caveat that he was a cooperating witness, not a co-defendant who stood with the others. In later years, unconnected to any political cause, he committed serious violent crimes — a prison murder in Arkansas and a 2000 kidnapping and shooting in Montana — and died in a Montana prison in 2014 while serving a 220-year sentence.",
                'cases' => [
                    [
                        'charges' => 'Interstate transportation of explosives (the 1975–76 SMJJU / Fred Hampton Unit bombings and bank robberies)',
                        'arrest_date' => '1976-07-04',
                        'convicted' => "Pleaded guilty; served under three years, then turned state's evidence against the group and entered the Witness Protection Program",
                        'sentence' => 'Under three years (as a cooperating witness)',
                    ],
                ],
            ],
            [
                'name' => 'James Barrett',
                'first_name' => 'James',
                'last_name' => 'Barrett',
                'gender' => 'Male',
                'race' => 'White',
                'state' => 'Maine',
                'era' => '1970s',
                'ideologies' => [],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "James W. Barrett was convicted of the October 4, 1975 armed robbery of the Northeast Bank's Lunts Corner branch in Portland, Maine — a holdup carried out with Raymond Levasseur, Thomas Manning, and Joseph Aceto of the Sam Melville/Jonathan Jackson Unit, who framed such robberies as political \"expropriations.\" Barrett, who knew Manning from prison, was recruited for the robbery but, by his own court testimony, was then asked to join the group's wider campaign of bombings, kidnappings, and robberies and refused — so he is best understood as a one-time accomplice rather than a committed member of the clandestine cell. He was convicted of armed bank robbery (United States v. Barrett, 766 F.2d 609) and sentenced to a long federal prison term.",
                'cases' => [
                    [
                        'charges' => 'Armed robbery of the Northeast Bank (Lunts Corner branch), Portland, Maine, October 4, 1975 — committed with Sam Melville/Jonathan Jackson Unit members',
                        'convicted' => 'Yes — convicted of armed bank robbery (United States v. Barrett, 766 F.2d 609, 1st Cir. 1985)',
                        'sentence' => '20 years',
                    ],
                ],
            ],
        ];
    }
}
