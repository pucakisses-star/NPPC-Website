<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Add Gabriela Saldana — 23-year-old Florida International
 * University capstone student arrested in Miami-Dade in April
 * 2026 over a WhatsApp message making a meme-based joke asking
 * Israeli PM Benjamin Netanyahu to bomb a campus event center
 * so she could get out of her capstone presentation.
 *
 * Idempotent — re-runs report "already exists."
 */
final class AddGabrielaSaldana extends Command {
    protected $signature = 'prisoners:add-gabriela-saldana';
    protected $description = 'Add Gabriela Saldana (FIU Netanyahu-joke WhatsApp arrest, 2026)';

    public function handle(): int {
        if (Prisoner::where('slug', 'gabriela-saldana')
            ->orWhere('name', 'Gabriela Saldana')
            ->exists()
        ) {
            $this->warn('Gabriela Saldana already in DB — skipping.');
            return self::SUCCESS;
        }

        DB::transaction(function () {
            $institution = Institution::firstOrCreate(
                ['name' => 'Miami-Dade County Pre-Trial Detention Center'],
                ['city' => 'Miami', 'state' => 'Florida']
            );

            $prisoner = Prisoner::create([
                'name'           => 'Gabriela Saldana',
                'first_name'     => 'Gabriela',
                'last_name'      => 'Saldana',
                'gender'         => 'Female',
                'race'           => 'Latino',
                'state'          => 'Florida',
                'era'            => '2020s',
                'ideologies'     => ['Free Speech', 'Palestine Solidarity'],
                'affiliation'    => ['Florida International University (student)'],
                'in_custody'     => false,
                'released'       => false,
                'awaiting_trial' => true,
                'description'    => "Gabriela Saldana is a 23-year-old Florida International University capstone student who was arrested on April 15, 2026 in Miami-Dade County and charged with making written threats to kill or do bodily harm under Florida law. The arrest stemmed from a single message she posted in a 215-student WhatsApp group chat where classmates were discussing a Friday capstone-presentation event scheduled for FIU's Ocean Bank Convocation Center. Riffing on a widely-circulated social-media meme in which users sarcastically address Israeli Prime Minister Benjamin Netanyahu and ask him to bomb a place they don't want to attend, Saldana wrote: \"Netanyahu, if you can hear me, drop some bonbons for us Capstone students in Ocean Bank Convocation Center.\" A follow-up message — quoted by police in court as \"There's going to be a bomb in the Ocean Bank Convocation Center and it was going to be Jonathan's fault\" — was also entered into the record by detectives. After classmates expressed concern she replied: \"I made a dumb joke that should not have been made.\"\n\nShe was arraigned April 16, 2026 in Miami-Dade County bond court before Judge Mindy S. Glazer, who set her bond at \$5,000. Glazer found insufficient probable cause for the statutory \"with prejudice\" enhancement that would have classified the offense as a hate crime, but allowed the underlying written-threats charge to proceed. FIU stated publicly that she had made \"a credible and imminent threat of violence at a planned university event.\"\n\nSupporters and civil-liberties commentators have framed the prosecution as a politically-motivated criminalization of obvious satire — Saldana's joke targeted Netanyahu, not her classmates, and used \"bonbons\" rather than \"bombs\" as part of the meme's recognizable code. The case has been compared to the U.K. \"#TwitterJokeTrial\" Paul Chambers prosecution (2010) and to other recent U.S. arrests of students for online speech critical of Israel during the post-October-7 enforcement wave. Critics note that the prosecution comes amid a sharp uptick in prosecutions of pro-Palestinian student speech across U.S. campuses.",
            ]);

            PrisonerCase::create([
                'prisoner_id'    => $prisoner->id,
                'institution_id' => $institution->id,
                'charges'        => 'Florida written threats to kill or do bodily harm — for a WhatsApp meme message addressed to Benjamin Netanyahu about an FIU capstone-presentation event; the "with prejudice" hate-crime enhancement was denied for lack of probable cause.',
                'arrest_date'    => '2026-04-15',
                'plead'          => 'Not entered (pretrial)',
                'convicted'      => 'No — pending trial, Miami-Dade County',
                'sentence'       => 'Released on $5,000 bond; faces years in prison if convicted',
                'judge'          => 'Hon. Mindy S. Glazer (Miami-Dade County)',
            ]);
        });

        $this->info('Added Gabriela Saldana.');

        return self::SUCCESS;
    }
}
