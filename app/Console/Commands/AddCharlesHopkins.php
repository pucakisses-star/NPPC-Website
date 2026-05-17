<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Add Charles Hopkins (Mansa Musa) — former Black Panther,
 * co-founder of the Maryland Penitentiary Intercommunal Survival
 * Committee, served 48 years 9 months 5 days in the Maryland
 * prison system on a life sentence stemming from a 1971 robbery
 * arrest at age 19, released December 5, 2019. Now co-hosts
 * "Rattling The Bars" on The Real News Network alongside the
 * late Marshall "Eddie" Conway.
 *
 * Idempotent — re-runs report "already exists."
 */
final class AddCharlesHopkins extends Command {
    protected $signature = 'prisoners:add-charles-hopkins';
    protected $description = 'Add Charles Hopkins / Mansa Musa (BPP-era Maryland political prisoner, released 2019)';

    public function handle(): int {
        if (Prisoner::where('slug', 'charles-hopkins')
            ->orWhere('slug', 'mansa-musa')
            ->orWhere('name', 'Charles Hopkins')
            ->orWhere('aka', 'like', '%Mansa Musa%')
            ->exists()
        ) {
            $this->warn('Charles Hopkins / Mansa Musa already in DB — skipping.');
            return self::SUCCESS;
        }

        DB::transaction(function () {
            $bopMd = Institution::firstOrCreate(
                ['name' => 'Maryland Division of Correction (multiple facilities)'],
                ['state' => 'Maryland']
            );

            $prisoner = Prisoner::create([
                'name'         => 'Charles Hopkins',
                'first_name'   => 'Charles',
                'last_name'    => 'Hopkins',
                'aka'          => 'Mansa Musa',
                'gender'       => 'Male',
                'race'         => 'Black',
                'state'        => 'Maryland',
                'era'          => '1970s',
                'ideologies'   => ['Black Liberation', 'Prison Abolition'],
                'affiliation'  => ['Black Panther Party', 'Maryland Penitentiary Intercommunal Survival Committee (PISC)', 'Rattling The Bars / The Real News Network'],
                'in_custody'   => false,
                'released'     => true,
                'website'      => 'https://therealnews.com/rattling-the-bars',
                'description'  => "Charles Hopkins — who adopted the name Mansa Musa after reading Malcolm X's writings on African history, rejecting the surname inherited from his enslavers — was arrested in 1971 at the age of 19 after a 7-Eleven robbery in Prince George's County, Maryland in which a customer, who turned out to be an off-duty park police officer, was shot. He was given a life sentence. He would later discover that the police officers handling the case were members of a unit subsequently exposed as a \"death squad\" that fabricated evidence and suppressed exculpatory information in multiple cases.\n\nHopkins served **48 years, 9 months, and 5 days** in the Maryland prison system, cycling through six or seven institutions including Hagerstown Correctional Institution, Jessup Correctional Institution, the Maryland Correctional Training Center, and four and a half years in a supermax facility. Inside, he became a central organizer of the **Maryland Penitentiary Intercommunal Survival Committee (PISC)**, a Black Panther–aligned prisoner formation that ran educational programs, literacy instruction, and prisoner-support work. He led organizing among prisoners across the country to engage the United Nations on U.S. prison conditions, corresponding with political prisoners including Anthony Bottom (Jalil Muntaqim) and Sundiata Acoli.\n\nHe was released on **December 5, 2019** on conditional release. Since then he has co-hosted **\"Rattling The Bars\"** on The Real News Network — first alongside fellow former Black Panther political prisoner Marshall \"Eddie\" Conway until Conway's death in 2023, and continuing the show since — and works with reentry programs including Voices for a Second Chance. He remains a prominent public voice on prison abolition, prisoner solidarity, and the connection between the carceral system and the labor struggle.",
            ]);

            PrisonerCase::create([
                'prisoner_id'        => $prisoner->id,
                'institution_id'     => $bopMd->id,
                'charges'            => 'Armed robbery and related felony charges arising from a 7-Eleven robbery in Prince George\'s County, Maryland in which a customer (off-duty park police officer) was shot. Defense alleges fabricated evidence and suppressed exculpatory material by a Prince George\'s County police unit later exposed as a "death squad."',
                'arrest_date'        => '1971-03-01',
                'incarceration_date' => '1971-03-01',
                'release_date'       => '2019-12-05',
                'convicted'          => 'Yes — Prince George\'s County, Maryland',
                'sentence'           => 'Life; served 48 years, 9 months, 5 days before conditional release December 5, 2019',
                'imprisoned_for_days' => 17813,
            ]);
        });

        $this->info('Added Charles Hopkins / Mansa Musa.');

        return self::SUCCESS;
    }
}
