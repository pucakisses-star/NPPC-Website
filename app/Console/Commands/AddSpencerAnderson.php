<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Add Spencer Anderson — 24-year-old man from Clarkston, Michigan
 * charged in February 2026 with three felony counts of malicious
 * destruction of police property after Waterford Township police
 * recovered Automatic License Plate Reader (ALPR / Flock) cameras
 * that had been forcibly broken from their mounts. Camera footage
 * from one of the still-functioning ALPRs captured his vehicle and
 * plate at the scene; a neighboring business's security camera
 * recorded a person striking two cameras with a blunt instrument.
 * He was arraigned February 27, 2026 in the 51st District Court
 * and released on a $500 cash bond. Each felony count carries up
 * to four years in prison and a $5,000 fine.
 *
 * Surfaced by a 2026-03-06 @JasonBassler1 tweet framing him as an
 * anti-surveillance protester.
 *
 * Idempotent — re-runs report "already exists."
 */
final class AddSpencerAnderson extends Command {
    protected $signature = 'prisoners:add-spencer-anderson';
    protected $description = 'Add Spencer Anderson (Waterford MI Flock ALPR camera vandalism, 2026)';

    public function handle(): int {
        if (Prisoner::where('slug', 'spencer-anderson')
            ->orWhere('name', 'Spencer Anderson')
            ->exists()
        ) {
            $this->warn('Spencer Anderson already in DB — skipping.');
            return self::SUCCESS;
        }

        DB::transaction(function () {
            $institution = Institution::firstOrCreate(
                ['name' => '51st District Court (Waterford)'],
                ['city' => 'Waterford', 'state' => 'Michigan']
            );

            $prisoner = Prisoner::create([
                'name'           => 'Spencer Anderson',
                'first_name'     => 'Spencer',
                'last_name'      => 'Anderson',
                'gender'         => 'Male',
                'state'          => 'Michigan',
                'era'            => '2020s',
                'ideologies'     => ['Anti-surveillance'],
                'affiliation'    => [],
                'in_custody'     => false,
                'released'       => false,
                'awaiting_trial' => true,
                'description'    => "Spencer Anderson is a 24-year-old man from Clarkston, Michigan charged in February 2026 with three felony counts of malicious destruction of police property. Waterford Township police announced on February 23, 2026 that several Automatic License Plate Reader (ALPR / Flock Safety) cameras in the township had been forcibly broken from their mounts and smashed. One of the still-functioning ALPRs captured the suspect's vehicle and plate at the scene; a nearby business's security camera recorded a person exiting the vehicle and striking two of the cameras with a blunt instrument. Anderson was arraigned February 27, 2026 in the 51st District Court in Waterford and released on a \$500 cash bond. Each of the three counts carries up to four years in prison and a \$5,000 fine, plus restitution for the roughly \$10,000 in damaged equipment.\n\nA probable-cause conference was set for March 11, 2026 with a preliminary examination scheduled for March 18, 2026. Supporters frame the case as anti-surveillance civil disobedience targeting one of the most widely deployed private ALPR systems in U.S. policing; prosecutors and Waterford police characterize it as malicious property destruction. The entry is neutral and notes both framings.",
            ]);

            PrisonerCase::create([
                'prisoner_id'    => $prisoner->id,
                'institution_id' => $institution->id,
                'charges'        => 'Three felony counts of malicious destruction of police property (Michigan) — Flock / ALPR cameras smashed in Waterford Township, February 2026. Up to four years prison + $5,000 fine per count.',
                'arrest_date'    => '2026-02-27',
                'plead'          => 'Not entered (probable-cause stage)',
                'convicted'      => 'No — pending preliminary examination (Mar 18, 2026)',
                'sentence'       => 'Released on $500 cash bond pending trial; faces up to 12 years prison if convicted on all three counts plus restitution',
                'judge'          => '51st District Court (Waterford)',
            ]);
        });

        $this->info('Added Spencer Anderson.');

        return self::SUCCESS;
    }
}
