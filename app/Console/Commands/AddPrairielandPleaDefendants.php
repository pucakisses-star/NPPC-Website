<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Add the five Prairieland defendants who took federal guilty pleas but are NOT
 * carried on the prairielanddefendants.com support roster (which lists only the
 * convicted-at-trial defendants and the two non-cooperating pleaders, Joy Gibson
 * and Rebecca Morgan, who are already in the database).
 *
 * All five pleaded guilty to one count of providing material support to
 * terrorists (max 15 years); the Nov 19, 2025 batch covered Sikes, Baumann,
 * Sharp and Thomas. Per court reporting, Baumann, Sharp, Thomas and Kent
 * testified for the government as cooperating co-defendants. Sentencing for the
 * plea defendants is scheduled for July 1, 2026 (N.D. Tex., Judge Pittman).
 *
 * Because the support committee does not represent them, these records are NOT
 * given the prairielanddefendants.com site/socials. Gender is left unset (not
 * reliably sourced). Idempotent — skips anyone already present with a case.
 */
final class AddPrairielandPleaDefendants extends Command
{
    protected $signature = 'prisoners:add-prairieland-plea-defendants';

    protected $description = 'Add the five cooperating/plea Prairieland defendants (material-support guilty pleas)';

    private const CONTEXT = 'in connection with the July 4, 2025 demonstration at the Prairieland Detention Center, an ICE jail in Alvarado, Texas, which federal prosecutors charged as a "North Texas Antifa Cell" terrorism case. Sentencing is scheduled for July 1, 2026 in the U.S. District Court for the Northern District of Texas.';

    public function handle(): int
    {
        $shared = [
            'state' => 'Texas',
            'era' => '2020s',
            'ideologies' => ['Antifascist'],
        ];

        $cooperated = ' Testified for the government as a cooperating co-defendant during the March 2026 trial.';

        $defendants = [
            [
                'name' => 'Seth Sikes', 'first_name' => 'Seth', 'last_name' => 'Sikes',
                'plea' => 'Pleaded guilty on November 19, 2025 to one count of providing material support to terrorists',
                'convicted' => 'Yes — guilty plea, November 19, 2025 (Northern District of Texas)',
                'coop' => '',
            ],
            [
                'name' => 'Nathan Baumann', 'first_name' => 'Nathan', 'last_name' => 'Baumann',
                'plea' => 'Pleaded guilty on November 19, 2025 to one count of providing material support to terrorists',
                'convicted' => 'Yes — guilty plea, November 19, 2025 (Northern District of Texas)',
                'coop' => $cooperated,
            ],
            [
                'name' => 'Lynette Sharp', 'first_name' => 'Lynette', 'last_name' => 'Sharp',
                'plea' => 'Pleaded guilty on November 19, 2025 to one count of providing material support to terrorists',
                'convicted' => 'Yes — guilty plea, November 19, 2025 (Northern District of Texas)',
                'coop' => $cooperated,
            ],
            [
                'name' => 'John Thomas', 'first_name' => 'John', 'last_name' => 'Thomas',
                'plea' => 'Pleaded guilty on November 19, 2025 to one count of providing material support to terrorists',
                'convicted' => 'Yes — guilty plea, November 19, 2025 (Northern District of Texas)',
                'coop' => $cooperated,
            ],
            [
                'name' => 'Susan Kent', 'first_name' => 'Susan', 'last_name' => 'Kent',
                'plea' => 'Pleaded guilty to one count of providing material support to terrorists',
                'convicted' => 'Yes — guilty plea (Northern District of Texas)',
                'coop' => $cooperated,
            ],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($defendants as $d) {
            DB::transaction(function () use ($d, $shared, &$created, &$skipped) {
                $prisoner = Prisoner::withoutGlobalScopes()->where('name', $d['name'])->first();
                if ($prisoner && PrisonerCase::where('prisoner_id', $prisoner->id)->exists()) {
                    $this->warn("Skipping {$d['name']} — already exists with a case.");
                    $skipped++;

                    return;
                }

                $description = "{$d['name']} is one of the defendants charged "
                    .self::CONTEXT." {$d['plea']}.{$d['coop']} They face up to 15 years in federal prison.";

                if (! $prisoner) {
                    $prisoner = Prisoner::create(array_merge($shared, [
                        'name' => $d['name'],
                        'first_name' => $d['first_name'],
                        'last_name' => $d['last_name'],
                        'description' => $description,
                    ]));
                    $this->info("Added {$prisoner->name} (slug: {$prisoner->slug})");
                }

                PrisonerCase::create([
                    'prisoner_id' => $prisoner->id,
                    'charges' => 'Providing material support to terrorists (18 U.S.C. § 2339A)',
                    'convicted' => $d['convicted'],
                    'sentence' => 'Awaiting sentencing (scheduled July 1, 2026); faces up to 15 years in federal prison.',
                ]);
                $created++;
            });
        }

        $this->info("\nDone. Created {$created}, skipped {$skipped}.");

        return self::SUCCESS;
    }
}
