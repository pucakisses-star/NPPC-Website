<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Current political prisoners from the Jericho Movement's main prisoners list
 * that were not already in the database. Almost the entire list is already
 * recorded (Mumia, the Virgin Islands / Fountain Valley Five — Malik Bey/Meral
 * Smith, Abdul Aziz/Warren Ballantine, Hanif Shabazz Bey/Beaumont Gereau —
 * Oso Blanco, Bill Dunne, Kamau Sadiki, Larry Hoover, Jeff Fort, Marius Mason,
 * Joy Powell, Jessica Reznicek, Casey Goonan, Alexander Contompasis, Kojo Bomani
 * Sababu, Fred Burton, Alvaro Luna Hernandez, etc.). Added here are the two New
 * Afrikan political prisoners that were missing:
 *
 *  - Abdul Olugbala Shakur (#C48884) and Shaka Shakur (#1996207).
 *
 * Idempotent and variant-aware. These are currently-held prisoners.
 */
final class AddJerichoCurrentPrisoners extends Command
{
    protected $signature = 'prisoners:add-jericho-current';

    protected $description = 'Add missing current political prisoners from the Jericho Movement list';

    public function handle(): int
    {
        $added = 0;
        $skipped = 0;

        foreach ($this->records() as $r) {
            $terms = $r['match'] ?? [$r['name']];
            unset($r['match']);

            $exists = false;
            foreach ($terms as $t) {
                if (Prisoner::withUnderReview()->where('name', 'like', '%'.$t.'%')->exists()) {
                    $exists = true;
                    break;
                }
            }
            if ($exists) {
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
        return [
            [
                'name' => 'Abdul Olugbala Shakur',
                'first_name' => 'Abdul',
                'last_name' => 'Shakur',
                'aka' => 'Joka; CDCR #C48884',
                'match' => ['Abdul Olugbala', 'Olugbala Shakur'],
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1980s',
                'ideologies' => ['New Afrikan', 'Black liberation', 'Prison movement'],
                'affiliation' => ['New Afrikan Independence Movement'],
                'in_custody' => true,
                'released' => false,
                'awaiting_trial' => false,
                'description' => 'Abdul Olugbala Shakur is a New Afrikan political prisoner held in the California prison system since he was captured at the age of 16 — more than four decades. He founded the George Jackson University, a prisoner-education project the state repeatedly shut down and he repeatedly rebuilt, while enduring years of torture in security housing units (SHU) and solitary confinement intended to break New Afrikan revolutionaries. He helped lead the historic California prison hunger strikes against indefinite solitary confinement, was a plaintiff in the class action Ashker v. Brown, and was a principal author of the prisoners\' "Agreement to End Hostilities." Told he could only leave the SHU by choosing to "parole, snitch or die," he refused all three.',
                'cases' => [[
                    'institution_name' => 'Kern Valley State Prison',
                    'institution_city' => 'Delano',
                    'institution_state' => 'California',
                    'charges' => 'Imprisoned in California since the age of 16; held for decades in SHU/solitary confinement for his New Afrikan organizing',
                ]],
            ],
            [
                'name' => 'Shaka Shakur',
                'first_name' => 'Shaka',
                'last_name' => 'Shakur',
                'aka' => 'IDOC #1996207',
                'match' => ['Shaka Shakur'],
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Indiana',
                'era' => '2000s',
                'ideologies' => ['New Afrikan', 'Black liberation', 'Prison movement'],
                'affiliation' => ['New Afrikan Independence Movement', 'New Afrikan Liberation Collective'],
                'in_custody' => true,
                'released' => false,
                'awaiting_trial' => false,
                'description' => 'Shaka Shakur is a New Afrikan political prisoner held since 2002 on what supporters call a trumped-up charge of attempted murder of a police officer, after politically motivated harassment by the Gary, Indiana police. First incarcerated at 16, he embraced New Afrikan revolutionary politics — mentored by Zolo Azania and James "Yaki" Sayles — and became an organizer at the Indiana State Prison; he and five others ("the Indiana 6") were transferred to the Westville supermax, and his work to expose its abuses helped produce the Human Rights Watch report "Cold Storage." A co-founder of the New Afrikan Liberation Collective and contributor to Prison Lives Matter, he was made a "domestic exile" in 2019, transferred to Virginia in a swap with that state for Kevin "Rashid" Johnson to disrupt their organizing.',
                'cases' => [[
                    'institution_name' => 'Lunenburg Correctional Facility',
                    'institution_city' => 'Victoria',
                    'institution_state' => 'Virginia',
                    'charges' => 'Attempted murder of a police officer (Gary, Indiana — described by supporters as a politically motivated frame-up); held by the Indiana DOC since 2002, later exiled to Virginia',
                    'convicted' => 'Yes — tried as a habitual offender; held since 2002',
                ]],
            ],
        ];
    }
}
