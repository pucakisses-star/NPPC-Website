<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Kyle Wagner — the Minneapolis anti-ICE / self-identified antifa activist
 * arrested Feb. 5, 2026 in an early-morning federal raid and charged in the
 * Eastern District of Michigan with cyberstalking and threatening
 * communications targeting ICE officers. Bond was denied at his initial
 * appearance and he is held without bond pending trial at the Milan Detention
 * Center in Michigan, so awaiting_trial/in_custody are set and released is
 * false. He is already on the dashboard as a prosecution marker; this adds the
 * full prisoner profile. Dedupes by name — safe to re-run.
 */
class AddKyleWagnerPrisoner extends Command {
    protected $signature = 'prisoners:add-kyle-wagner';
    protected $description = 'Add Kyle Wagner (Minneapolis anti-ICE/antifa activist, awaiting trial)';

    public function handle(): int {
        $name = 'Kyle Wagner';
        if (Prisoner::withoutGlobalScopes()->where('name', $name)->exists()) {
            $this->warn("{$name} already exists — skipping.");

            return self::SUCCESS;
        }

        $milan = Institution::firstOrCreate(
            ['name' => 'Milan Detention Center (FCI Milan)'],
            ['city' => 'Milan', 'state' => 'Michigan']
        );

        DB::transaction(function () use ($name, $milan) {
            $prisoner = Prisoner::create([
                'name'         => $name,
                'first_name'   => 'Kyle',
                'last_name'    => 'Wagner',
                'age'          => 37,
                'gender'       => 'Male',
                'state'        => 'Minnesota',
                'era'          => '2020s',
                'ideologies'   => ['Anti-fascist', 'Anti-ICE'],
                'affiliation'  => ['Antifa'],
                'in_custody'   => true,
                'released'     => false,
                'awaiting_trial' => true,
                'description'  => "Kyle Wagner is a 37-year-old Minneapolis activist who self-identifies as antifascist (\"antifa\"). On February 5, 2026, Homeland Security Investigations agents arrested him in an early-morning raid on his apartment building in the Whittier neighborhood of Minneapolis. He was charged in the U.S. District Court for the Eastern District of Michigan with cyberstalking and transmitting threatening communications in interstate commerce.\n\nProsecutors allege that in January 2026 Wagner repeatedly posted on Facebook and Instagram urging his followers to \"forcibly confront, assault, impede, oppose, and resist\" federal immigration officers — whom he called the \"Gestapo\" and \"murderers\" — with posts including \"hunt ICE,\" \"cripple them,\" and \"this is kill or be killed,\" and that he doxxed a Michigan-based ICE supporter by publishing their phone number, partial birth date, and home address. Attorney General Pam Bondi and the White House publicly labeled him a domestic terrorist, casting the prosecution as part of the administration's post-NSPM-7 crackdown on \"antifa\" networks.\n\nAt his February 5, 2026 hearing, U.S. Magistrate Judge David Schultz denied bond and ordered Wagner detained; his defense appealed the bond denial. He is being held without bond pending trial at the Milan Detention Center (FCI Milan) in Milan, Michigan. The case has drawn First Amendment scrutiny over whether his online posts amount to true threats or protected political speech.",
            ]);

            PrisonerCase::create([
                'prisoner_id'        => $prisoner->id,
                'institution_id'     => $milan->id,
                'charges'            => 'Cyberstalking (18 U.S.C. § 2261A); transmitting threatening communications in interstate commerce (18 U.S.C. § 875(c))',
                'arrest_date'        => '2026-02-05',
                'incarceration_date' => '2026-02-05',
                'convicted'          => 'No — awaiting trial',
                'plead'              => 'Ordered detained without bond at his initial appearance (bail denied February 5, 2026 by U.S. Magistrate Judge David Schultz, District of Minnesota, on a Rule 40 removal); defense appealed the bond denial. Held pending trial at the Milan Detention Center in Milan, Michigan; charged in the Eastern District of Michigan.',
            ]);

            $this->info("Added {$prisoner->name} (slug: {$prisoner->slug}).");
        });

        return self::SUCCESS;
    }
}
