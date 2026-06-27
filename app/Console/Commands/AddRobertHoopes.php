<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds (or updates) Robert Jacob Hoopes, a Portland, Oregon anti-ICE protester
 * sentenced in June 2026 to 30 months in federal prison for throwing a rock
 * that struck a U.S. Immigration and Customs Enforcement officer during a
 * protest outside the ICE facility in South Portland on June 14, 2025.
 *
 * Source: AP / OPB, "Anti-ICE protester in Portland sentenced to 30 months in
 * prison for assaulting a federal officer" (June 12, 2026), plus KGW/KATU/Fox
 * coverage of the June 11, 2026 sentencing. The BOP facility was not reported,
 * so the case carries no institution. Idempotent; matches an existing record by
 * slug/name so it won't duplicate.
 */
final class AddRobertHoopes extends Command
{
    protected $signature = 'prisoners:add-robert-hoopes';

    protected $description = 'Add Robert Jacob Hoopes (Portland anti-ICE protester, 30-month federal sentence)';

    public function handle(): int
    {
        $bio = 'Robert Jacob Hoopes is a Portland, Oregon protester and Reed College alumnus who was '
            .'sentenced on June 11, 2026 to 30 months in federal prison for throwing a large rock that '
            .'struck a U.S. Immigration and Customs Enforcement (ERO) officer in the head during an '
            .'anti-ICE protest outside the ICE facility in South Portland on June 14, 2025, opening a '
            .'gash over the officer\'s eye. The FBI identified him using facial recognition technology '
            .'applied to news and college photographs. He pleaded guilty in February 2026 to aggravated '
            .'assault of a federal employee with a dangerous weapon, and U.S. District Judge Adrienne '
            .'Nelson sentenced him to 30 months in prison, three years of supervised release, and more '
            .'than $8,000 in restitution.';

        $attributes = [
            'name' => 'Robert Jacob Hoopes',
            'first_name' => 'Robert',
            'middle_name' => 'Jacob',
            'last_name' => 'Hoopes',
            'gender' => 'Male',
            'age' => 25,
            'state' => 'Oregon',
            'era' => '2020s',
            'ideologies' => ['Anti-ICE', 'Migrant Solidarity'],
            'description' => $bio,
            'in_custody' => true,
            'released' => false,
            'awaiting_trial' => false,
            'under_review' => false,
        ];

        $prisoner = Prisoner::withoutGlobalScopes()
            ->where('slug', 'robert-jacob-hoopes')
            ->orWhere('slug', 'robert-hoopes')
            ->orWhere('name', 'like', '%Robert%Hoopes%')
            ->first();

        if ($prisoner) {
            $prisoner->fill($attributes)->save();
            $this->info("Updated existing prisoner: {$prisoner->name} (ID: {$prisoner->id})");
        } else {
            $prisoner = Prisoner::create($attributes);
            $this->info("Created prisoner: {$prisoner->name} (ID: {$prisoner->id})");
        }

        // Federal case. The reported BOP facility is unknown, so no institution
        // is attached. Update the existing case in place if one already exists.
        $case = $prisoner->cases()->first() ?? $prisoner->cases()->make([]);
        $case->charges = 'Aggravated assault of a federal employee with a dangerous weapon — threw a large '
            .'rock that struck a U.S. Immigration and Customs Enforcement (ERO) officer in the head during '
            .'an anti-ICE protest outside the ICE facility in South Portland, Oregon on June 14, 2025.';
        $case->plead = 'Guilty';
        $case->convicted = 'Yes — pleaded guilty (February 2026)';
        $case->sentence = '30 months in federal prison; 3 years supervised release; more than $8,000 in restitution.';
        $case->judge = 'Adrienne Nelson';
        $case->sentenced_date = '2026-06-11';
        $case->setPartialDate('incarceration_date', 2026, 6, 11);
        $case->save();
        $this->info('Set federal case: aggravated assault of a federal officer, 30-month sentence.');

        $this->info("View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
