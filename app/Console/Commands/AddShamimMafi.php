<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Add Shamim Mafi — 44-year-old Iranian national, U.S. lawful
 * permanent resident since 2016, arrested April 18, 2026 at LAX
 * and charged in the Central District of California with a single
 * count of violating the International Emergency Economic Powers
 * Act (IEEPA), 50 U.S.C. §1705, for allegedly brokering the sale
 * of Iranian-made drones, bombs, bomb fuses, and ammunition to
 * Sudan. Faces up to 20 years federal if convicted.
 *
 * Idempotent — re-runs report "already exists."
 */
final class AddShamimMafi extends Command {
    protected $signature = 'prisoners:add-shamim-mafi';
    protected $description = 'Add Shamim Mafi (IEEPA Iran-Sudan arms-brokering case, 2026)';

    public function handle(): int {
        if (Prisoner::where('slug', 'shamim-mafi')
            ->orWhere('name', 'Shamim Mafi')
            ->exists()
        ) {
            $this->warn('Shamim Mafi already in DB — skipping.');
            return self::SUCCESS;
        }

        DB::transaction(function () {
            $institution = Institution::firstOrCreate(
                ['name' => 'Metropolitan Detention Center, Los Angeles'],
                ['city' => 'Los Angeles', 'state' => 'California']
            );

            $prisoner = Prisoner::create([
                'name'           => 'Shamim Mafi',
                'first_name'     => 'Shamim',
                'last_name'      => 'Mafi',
                'gender'         => 'Female',
                'race'           => 'Middle Eastern',
                'state'          => 'California',
                'era'            => '2020s',
                'ideologies'     => [],
                'affiliation'    => [],
                'in_custody'     => true,
                'released'       => false,
                'awaiting_trial' => true,
                'description'    => "Shamim Mafi is a 44-year-old Iranian national, a lawful permanent resident of the United States since 2016, who lived in Woodland Hills, California prior to her arrest. On the evening of April 18, 2026 she was arrested at Los Angeles International Airport by federal agents and charged in the U.S. District Court for the Central District of California with a single count of violating the International Emergency Economic Powers Act (IEEPA), 50 U.S.C. §1705. The prosecution alleges she brokered the sale of Iranian-manufactured drones, bombs, bomb fuses, and millions of rounds of ammunition for transfer to Sudan during that country's ongoing civil war.\n\nIf convicted she faces a statutory maximum of 20 years in federal prison. Her initial appearance was scheduled for the afternoon of April 20, 2026 in downtown Los Angeles. She is presumed innocent. The indictment was announced by U.S. Attorney for the Central District of California Bilal Essayli, a Trump-administration appointee with a politically prominent profile in Iranian-American policy circles.\n\nThe case is included in the NPPC database because IEEPA — the federal sanctions statute under which Mafi is charged — has been criticized by civil-liberties groups and Iranian-American legal-defense organizations for its disproportionate use against Iranian nationals and dual citizens in cases that often blend genuine export-control concerns with politically-coded prosecution. The defense bar has long argued that the statute's broad reach allows the government to criminalize conduct, including ordinary commercial transactions touching Iran, that would not be charged against defendants from other sanctioned jurisdictions. Mafi's prosecution will be watched as a test case for how aggressively the current administration pursues IEEPA enforcement against permanent residents of Iranian origin.",
            ]);

            PrisonerCase::create([
                'prisoner_id'        => $prisoner->id,
                'institution_id'     => $institution->id,
                'charges'            => 'Violation of the International Emergency Economic Powers Act (IEEPA), 50 U.S.C. §1705 — allegedly brokering the sale of Iranian-made drones, bombs, bomb fuses, and millions of rounds of ammunition to Sudan during the Sudanese civil war.',
                'arrest_date'        => '2026-04-18',
                'incarceration_date' => '2026-04-18',
                'plead'              => 'Not entered (pretrial)',
                'convicted'          => 'No — pending trial in C.D. Cal.',
                'sentence'           => 'Faces statutory maximum of 20 years federal if convicted',
                'prosecutor'         => 'U.S. Attorney Bilal Essayli (Central District of California)',
                'judge'              => 'U.S. District Court, Central District of California (Los Angeles)',
            ]);
        });

        $this->info('Added Shamim Mafi.');

        return self::SUCCESS;
    }
}
