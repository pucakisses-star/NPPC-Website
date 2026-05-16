<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Add Aurelio Luis Perez-Lugones — 61-year-old Pentagon contractor
 * from Laurel, MD, arrested Jan 8, 2026 and indicted Jan 22, 2026
 * in the Eastern District of Virginia on five counts of unlawfully
 * transmitting and one count of unlawfully retaining classified
 * national defense information (18 U.S.C. §793). He is alleged to
 * have leaked classified intelligence to Washington Post reporter
 * Hannah Natanson over Oct 2025 – Jan 2026; the FBI's Jan 14, 2026
 * pre-dawn search of Natanson's home was executed under a warrant
 * tied to this investigation.
 *
 * Idempotent — re-runs report "already exists."
 */
final class AddPerezLugones extends Command {
    protected $signature = 'prisoners:add-perez-lugones';
    protected $description = 'Add Aurelio Luis Perez-Lugones (Pentagon contractor Espionage Act leak prosecution, 2026)';

    public function handle(): int {
        if (Prisoner::where('slug', 'aurelio-perez-lugones')
            ->orWhere('name', 'Aurelio Luis Perez-Lugones')
            ->orWhere('name', 'Aurelio Perez-Lugones')
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
                'ideologies'   => ['Press Freedom', 'Whistleblowing'],
                'affiliation'  => ['Pentagon contractor (former)'],
                'in_custody'   => true,
                'released'     => false,
                'description'  => "Aurelio Luis Perez-Lugones is a 61-year-old resident of Laurel, Maryland who worked as a systems engineer and information technology specialist for a Pentagon contracting firm, holding a Top Secret / SCI clearance. He was arrested on January 8, 2026 and indicted on January 22, 2026 in the U.S. District Court for the Eastern District of Virginia on **five counts of unlawfully transmitting and one count of unlawfully retaining classified national defense information** under 18 U.S.C. §793 (the Espionage Act). Each count carries up to ten years in federal prison.\n\nBeginning in October 2025, according to an FBI agent's affidavit, Perez-Lugones took screenshots of classified intelligence reports — some concerning an unspecified foreign country — and pasted the images into Microsoft Word documents and other applications to obscure his unauthorized access, then printed them and brought them home. FBI agents searching his home and car in January 2026 recovered \"SECRET\"-marked documents, including one in a lunchbox in his vehicle.\n\nProsecutors allege he passed the printouts to a journalist identified in the indictment only as \"Reporter 1.\" Subsequent media reporting and court filings established that Reporter 1 is Washington Post national-security reporter Hannah Natanson, whose coverage focused on the Trump administration's transformation of the federal government. The Post published at least five articles co-authored by Natanson (Oct 31 2025; Nov 11 2025; Dec 8 2025; Jan 6 2026; Jan 9 2026) drawing on the leaked material. Investigators recovered phone messages between Perez-Lugones and Natanson; in one, after sending a document, Perez-Lugones wrote: \"I'm going quiet for a bit … just to see if anyone starts asking questions.\"\n\nThe FBI's pre-dawn January 14, 2026 search of Natanson's home in Virginia — in which agents seized her phone, two laptops, a Garmin watch, a recorder, and a portable hard drive — was executed under a warrant tied to this investigation. A federal magistrate judge in Alexandria, later affirmed on May 4, 2026 by U.S. District Judge Anthony J. Trenga, blocked DOJ from searching the contents of Natanson's devices under the federal Privacy Protection Act (42 U.S.C. §2000aa), citing the \"harassing and chilling effects such a seizure could have on a reporter.\"\n\nThe Perez-Lugones prosecution sits in the Reality Winner / Daniel Hale / John Kiriakou / Thomas Drake lineage of Espionage Act prosecutions targeting low-level government employees who shared classified information with the press in the public interest. Attorney General Pamela Bondi and FBI Director Kash Patel both publicly commented on the indictment. He has remained jailed since his January 8, 2026 arrest.",
            ]);

            PrisonerCase::create([
                'prisoner_id'        => $prisoner->id,
                'institution_id'     => $institution->id,
                'charges'            => 'Five counts of unlawful transmission of classified national defense information and one count of unlawful retention — 18 U.S.C. §793 (Espionage Act). Allegedly leaked classified intelligence reports to Washington Post reporter Hannah Natanson, Oct 2025 – Jan 2026.',
                'arrest_date'        => '2026-01-08',
                'incarceration_date' => '2026-01-08',
                'plead'              => 'Not entered (pretrial)',
                'convicted'          => 'No — pending trial in E.D. Va.',
                'sentence'           => 'Faces up to ten years per count if convicted; jailed since January 8, 2026 arrest',
                'prosecutor'         => 'U.S. Department of Justice (AG Pamela Bondi)',
                'judge'              => 'U.S. District Court, Eastern District of Virginia (Alexandria)',
            ]);
        });

        $this->info('Added Aurelio Luis Perez-Lugones.');

        return self::SUCCESS;
    }
}
