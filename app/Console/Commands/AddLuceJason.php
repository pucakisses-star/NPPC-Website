<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds the two named defendants from the mass-arrest protest-case
 * sweep that weren't already in the DB:
 *   - Luce Guillén-Givins (RNC 8, 2008)
 *   - Jason Sutherlin (Tinley Park 5, 2012)
 *
 * The 30 other candidates from that sweep are all already in.
 *
 * Idempotent — prisoner:add refuses duplicates by name.
 */
final class AddLuceJason extends Command {
    protected $signature = 'archive:add-luce-jason';
    protected $description = 'Add Luce Guillén-Givins (RNC 8) and Jason Sutherlin (Tinley Park 5)';

    public function handle(): int {
        $added = 0;
        $skipped = 0;

        foreach ($this->prisoners() as $payload) {
            $exit = $this->call('prisoner:add', ['json' => json_encode($payload)]);
            if ($exit === self::SUCCESS) {
                $this->info('ADD: '.$payload['name']);
                $added++;
            } else {
                $skipped++;
            }
        }

        $this->info("Done — added {$added}, skipped {$skipped}.");
        return self::SUCCESS;
    }

    /** @return array<int, array<string, mixed>> */
    private function prisoners(): array {
        return [
            [
                'name' => 'Luce Guillén-Givins',
                'first_name' => 'Luce',
                'last_name' => 'Guillén-Givins',
                'description' => 'Minneapolis anarchist and one of the "RNC 8" — eight organizers with the RNC Welcoming Committee preemptively arrested in raids leading up to the 2008 Republican National Convention in St. Paul, MN. The eight were the first defendants charged under Minnesota\'s 2002 state anti-terrorism statute ("conspiracy to riot in furtherance of terrorism"). After a 4-year prosecution and sustained movement defense, the terrorism enhancements were dropped; most defendants pled to reduced misdemeanor charges with no prison time.',
                'state' => 'Minnesota',
                'gender' => 'Female',
                'ideologies' => ['Anarchist', 'Anti-imperialist'],
                'affiliation' => ['RNC Welcoming Committee', 'RNC 8'],
                'era' => '2000s',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'institution_state' => 'Minnesota',
                        'charges' => 'Conspiracy to riot in furtherance of terrorism (Minnesota Patriot Act); conspiracy to commit criminal damage to property. Terrorism charges dropped.',
                        'arrest_date' => '2008-08-30',
                        'sentence' => 'Pled to reduced charges in 2010; no prison time.',
                    ],
                ],
            ],
            [
                'name' => 'Jason Sutherlin',
                'first_name' => 'Jason',
                'last_name' => 'Sutherlin',
                'description' => 'Anti-Racist Action organizer and one of the "Tinley Park Five" — five antifascists who, on May 19, 2012, entered an organizing meeting of white nationalists (the Illinois European Heritage Association) at the Ashford House restaurant in Tinley Park, IL, and assaulted those present. The five turned themselves in and accepted plea deals; Sutherlin received the longest sentence at six years in the Illinois Department of Corrections. The case became a defining 2010s antifascist political-prisoner cause.',
                'state' => 'Indiana',
                'gender' => 'Male',
                'ideologies' => ['Anti-fascist', 'Anti-racist'],
                'affiliation' => ['Anti-Racist Action', 'Tinley Park Five'],
                'era' => '2010s',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'institution_state' => 'Illinois',
                        'charges' => 'Armed violence, aggravated battery, mob action.',
                        'arrest_date' => '2012-05-19',
                        'sentence' => '6 years Illinois Department of Corrections.',
                    ],
                ],
            ],
        ];
    }
}
