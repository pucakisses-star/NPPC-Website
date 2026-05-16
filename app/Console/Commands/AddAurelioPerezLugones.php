<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Add Aurelio Luis Perez-Lugones — 61-year-old former Pentagon
 * contractor from Laurel, Maryland, arrested January 8, 2026 and
 * indicted January 22, 2026 in the Eastern District of Virginia
 * (Alexandria) for leaking classified national defense information
 * to Washington Post reporter Hannah Natanson. He has remained
 * jailed since arrest. Sits in the Reality Winner / Daniel Hale /
 * Thomas Drake / John Kiriakou lineage of Espionage Act
 * prosecutions of federal-government workers who released
 * information to journalists in the public interest.
 *
 * Idempotent — re-runs report "already exists."
 */
final class AddAurelioPerezLugones extends Command {
    protected $signature = 'prisoners:add-perez-lugones';
    protected $description = 'Add Aurelio Luis Perez-Lugones (WaPo / Natanson Espionage Act leak case, 2026)';

    public function handle(): int {
        if (Prisoner::where('slug', 'aurelio-luis-perez-lugones')
            ->orWhere('slug', 'aurelio-perez-lugones')
            ->orWhere('name', 'Aurelio Luis Perez-Lugones')
            ->exists()
        ) {
            $this->warn('Aurelio Luis Perez-Lugones already in DB — skipping.');
            return self::SUCCESS;
        }

        DB::transaction(function () {
            $institution = Institution::firstOrCreate(
                ['name' => 'Alexandria Detention Center'],
                ['city' => 'Alexandria', 'state' => 'Virginia']
            );

            $prisoner = Prisoner::create([
                'name'         => 'Aurelio Luis Perez-Lugones',
                'first_name'   => 'Aurelio',
                'middle_name'  => 'Luis',
                'last_name'    => 'Perez-Lugones',
                'gender'       => 'Male',
                'race'         => 'Latino',
                'state'        => 'Maryland',
                'era'          => '2020s',
                'ideologies'   => ['Press Freedom', 'Whistleblower'],
                'affiliation'  => ['Pentagon contractor (former)'],
                'in_custody'   => true,
                'released'     => false,
                'awaiting_trial' => true,
                'description'  => "Aurelio Luis Perez-Lugones is a 61-year-old systems engineer and information technology specialist from Laurel, Maryland who held a Top Secret / Sensitive Compartmented Information security clearance while working as a Pentagon contractor. Beginning in October 2025 he is alleged to have taken screenshots of classified intelligence reports — some related to an unspecified foreign country — pasted them into Microsoft Word documents and other applications to obscure his unauthorized access, printed them, and brought them home. Documents marked SECRET were later recovered from a lunchbox in his car and from his basement.\n\nFederal prosecutors allege he passed the materials to Washington Post reporter Hannah Natanson, who co-wrote and contributed to at least five articles based on the information on October 31, November 11, and December 8, 2025, and January 6 and January 9, 2026. The leaked material informed Natanson's coverage of the Trump administration's reshaping of the federal government. Phone messages between Perez-Lugones and the reporter included him writing, \"I'm going quiet for a bit … just to see if anyone starts asking questions,\" after sending one document.\n\nHe was arrested on January 8, 2026 and has remained jailed since. On January 22, 2026 a grand jury in the Eastern District of Virginia indicted him on five counts of unlawfully transmitting and one count of unlawfully retaining classified national defense information under the Espionage Act (18 U.S.C. §793). The indictment was announced by Attorney General Pamela Bondi and FBI Director Kash Patel. Six days after his arrest the FBI executed a pre-dawn search warrant at Natanson's home in Virginia and seized her phone, two laptops, a recorder, a portable hard drive, and a Garmin smartwatch — a seizure two federal judges (a magistrate and U.S. District Judge Anthony J. Trenga, ruling May 4, 2026) have since blocked DOJ from searching under the Privacy Protection Act.\n\nThe prosecution sits in the Reality Winner / Daniel Hale / Thomas Drake / John Kiriakou lineage of Espionage Act cases brought against federal workers who released information to journalists in the public interest. Each transmission count carries up to ten years in federal prison.",
            ]);

            PrisonerCase::create([
                'prisoner_id'        => $prisoner->id,
                'institution_id'     => $institution->id,
                'charges'            => 'Five counts of unlawful transmission and one count of unlawful retention of classified national defense information — 18 U.S.C. §793 (Espionage Act).',
                'arrest_date'        => '2026-01-08',
                'indicted'           => '2026-01-22',
                'incarceration_date' => '2026-01-08',
                'plead'              => 'Not entered (pretrial)',
                'convicted'          => 'No — pending trial in E.D. Va.',
                'prosecutor'         => 'U.S. Attorney\'s Office for the Eastern District of Virginia; announced by AG Pamela Bondi and FBI Director Kash Patel',
                'sentence'           => 'Pretrial detention since January 8, 2026; faces up to 10 years per transmission count if convicted (50 + years cumulative possible)',
            ]);
        });

        $this->info('Added Aurelio Luis Perez-Lugones.');

        return self::SUCCESS;
    }
}
