<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Add Duncan Andrew Small — convicted in 2022 of two North Carolina
 * misdemeanors (possession of pyrotechnics; going armed to the
 * terror of the public) for throwing improvised explosive devices at
 * the remains of the Confederate Vance Monument in Asheville during
 * a July 4, 2022 protest. Federally indicted March 31, 2026 on a
 * single count of possession of a firearm by a convicted felon,
 * after sending threatening communications to the Asheville Police
 * bomb technician from his earlier case and to Immigration and
 * Customs Enforcement agents in Florida.
 *
 * Idempotent — re-runs report "already exists."
 */
final class AddDuncanSmall extends Command {
    protected $signature = 'prisoners:add-duncan-small';
    protected $description = 'Add Duncan Andrew Small (Vance Monument / anti-ICE threats federal case, 2026)';

    public function handle(): int {
        if (Prisoner::where('slug', 'duncan-small')
            ->orWhere('slug', 'duncan-andrew-small')
            ->orWhere('name', 'Duncan Andrew Small')
            ->orWhere('name', 'Duncan Small')
            ->exists()
        ) {
            $this->warn('Duncan Andrew Small already in DB — skipping.');
            return self::SUCCESS;
        }

        DB::transaction(function () {
            $institution = Institution::firstOrCreate(
                ['name' => 'Federal Bureau of Prisons (location varied)']
            );

            $prisoner = Prisoner::create([
                'name'           => 'Duncan Andrew Small',
                'first_name'     => 'Duncan',
                'middle_name'    => 'Andrew',
                'last_name'      => 'Small',
                'gender'         => 'Male',
                'state'          => 'Florida',
                'era'            => '2020s',
                'ideologies'     => ['Anti-Confederate', 'Anti-ICE'],
                'affiliation'    => [],
                'in_custody'     => true,
                'released'       => false,
                'awaiting_trial' => true,
                'description'    => "Duncan Andrew Small is a Florida resident with a 2022 North Carolina misdemeanor record for throwing improvised pyrotechnic devices — described by Asheville police at the time as \"similar to pipe bombs\" — at the remains of the Vance Monument, an obelisk Confederate memorial in downtown Asheville, North Carolina, during a Fourth of July 2022 protest. He was arrested at the scene and convicted of two state misdemeanors: possession of pyrotechnics and going armed to the terror of the public. He received twelve months of probation.\n\nIn early 2024, according to an FBI agent's criminal complaint, Small sent a text message to the Asheville Police Department bomb technician who had handled his earlier case, telling the officer he had \"f***ed up\" and including an image of a police officer being shot captioned \"SPEAK TO COPS IN A LANGUAGE THEY UNDERSTAND.\" Federal agents subsequently developed evidence of additional threatening communications targeting Immigration and Customs Enforcement (ICE) agents in Florida amid the Trump administration's stepped-up immigration-enforcement operations. On March 31, 2026 he was federally indicted on a single count of **possession of a firearm by a convicted felon** (18 U.S.C. §922(g)(1)) — a charge that turns on his earlier North Carolina misdemeanor record.\n\nThe case sits at the intersection of two distinct strands of political-defendant prosecution. On one hand: his July 4 2022 action against a Confederate monument fits a tradition of direct action against Lost-Cause iconography that the Stop Cop City, anti-statue, and broader racial-justice movements have prosecuted in court. On the other: the federal charge is structured around threats of violence against named officers, which prosecutors and many critics agree falls outside the protected-speech protections of pure protest activity. The record is included here for completeness; the description does not endorse the threats or minimize the prosecution's allegations about them. His case was profiled in journalist Seamus Hughes's Court Watch newsletter on April 5, 2026.",
            ]);

            PrisonerCase::create([
                'prisoner_id'    => $prisoner->id,
                'institution_id' => $institution->id,
                'charges'        => 'Federal: possession of a firearm by a convicted felon (18 U.S.C. §922(g)(1)) — predicated on 2022 North Carolina misdemeanor convictions for the Vance Monument protest. Underlying investigation: threatening communications to an Asheville Police bomb technician and to ICE agents in Florida.',
                'arrest_date'    => '2026-03-31',
                'plead'          => 'Not entered (pretrial)',
                'convicted'      => 'No — pending in federal court',
                'sentence'       => 'Faces up to 10 years federal if convicted under §922(g)(1)',
            ]);
        });

        $this->info('Added Duncan Andrew Small.');

        return self::SUCCESS;
    }
}
