<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds the four "Turtle Island Liberation Front" defendants arrested Dec. 12,
 * 2025 in the Mojave Desert and charged in the Central District of California
 * over an alleged New Year's Eve pipe-bomb plot ("Operation Midnight Sun").
 * All four are held without bond and have pleaded not guilty — they are
 * awaiting trial, so awaiting_trial/in_custody are set and released is false.
 * Carroll and Page also face a conspiracy-to-use-a-WMD count carrying a
 * statutory maximum of life; Gaffield and Lai face up to 25 years. The
 * Intercept reported that a longtime paid FBI informant was instrumental in
 * the case. Dedupes by name, so the command is safe to re-run.
 */
class AddTurtleIslandPrisoners extends Command {
    protected $signature = 'prisoners:add-turtle-island';
    protected $description = 'Add the four Turtle Island Liberation Front bombing-plot defendants (awaiting trial)';

    public function handle(): int {
        $created = 0;
        $skipped = 0;

        $mdcLa = Institution::firstOrCreate(
            ['name' => 'Metropolitan Detention Center, Los Angeles'],
            ['city' => 'Los Angeles', 'state' => 'California']
        );

        $plotContext = "The Turtle Island Liberation Front (TILF) was described by federal prosecutors as an anti-capitalist and anti-government group. According to the December 2025 charges, members planned 'Operation Midnight Sun' — a coordinated attack to simultaneously detonate backpack pipe bombs at five or more locations targeting two U.S. companies in the greater Los Angeles area at midnight on New Year's Eve, and also discussed targeting ICE agents and vehicles. FBI agents arrested the four in the Mojave Desert on December 12, 2025, where the group had allegedly acquired materials and attempted test detonations. The Intercept subsequently reported that a longtime paid FBI informant had been instrumental in building the case, raising entrapment questions of the kind that have recurred in post-9/11 domestic terrorism prosecutions. All four pleaded not guilty and are being held without bond pending trial in the Central District of California.";

        $defendants = [];

        // Audrey Carroll — alleged author of the plan; faces up to life
        $defendants[] = [
            'data' => [
                'name' => 'Audrey Carroll', 'first_name' => 'Audrey', 'middle_name' => 'Illeene', 'last_name' => 'Carroll',
                'aka' => 'Asiginaak',
                'age' => 30, 'state' => 'California', 'era' => '2020s',
                'ideologies' => ['Anti-capitalist', 'Anti-government', 'Pro-Palestine'],
                'affiliation' => ['Turtle Island Liberation Front'],
                'in_custody' => true, 'released' => false, 'awaiting_trial' => true,
                'description' => "Audrey Illeene Carroll, of South Los Angeles, is one of four people charged in the alleged Turtle Island Liberation Front New Year's Eve bombing plot. Prosecutors allege she authored a handwritten eight-page document titled 'Operation Midnight Sun' in November 2025 laying out the plan. She is charged with conspiracy to use a weapon of mass destruction, providing and attempting to provide material support to terrorists, and possession of an unregistered destructive device, and faces a statutory maximum of life in federal prison.\n\n{$plotContext}",
            ],
            'case' => [
                'institution_id' => $mdcLa->id,
                'charges'        => 'Conspiracy to use a weapon of mass destruction (18 U.S.C. § 2332a); providing and attempting to provide material support to terrorists (18 U.S.C. § 2339A); possession of an unregistered destructive device (26 U.S.C. § 5861(d))',
                'arrest_date'        => '2025-12-12',
                'incarceration_date' => '2025-12-12',
                'indicted'           => 'Yes — federal grand jury, Central District of California (December 2025)',
                'convicted'          => 'No — awaiting trial (pleaded not guilty)',
                'plead'              => 'Pleaded not guilty; arraigned January 2026, U.S. District Court, Central District of California. Statutory maximum if convicted: life in federal prison.',
            ],
        ];

        // Zachary Page — faces up to life
        $defendants[] = [
            'data' => [
                'name' => 'Zachary Page', 'first_name' => 'Zachary', 'middle_name' => 'Aaron', 'last_name' => 'Page',
                'aka' => 'AK',
                'age' => 32, 'state' => 'California', 'era' => '2020s',
                'ideologies' => ['Anti-capitalist', 'Anti-government', 'Pro-Palestine'],
                'affiliation' => ['Turtle Island Liberation Front'],
                'in_custody' => true, 'released' => false, 'awaiting_trial' => true,
                'description' => "Zachary Aaron Page, of Torrance, California, is one of four people charged in the alleged Turtle Island Liberation Front New Year's Eve bombing plot. He is charged with conspiracy to use a weapon of mass destruction, providing and attempting to provide material support to terrorists, and possession of an unregistered destructive device, and — like co-defendant Audrey Carroll — faces a statutory maximum of life in federal prison.\n\n{$plotContext}",
            ],
            'case' => [
                'institution_id' => $mdcLa->id,
                'charges'        => 'Conspiracy to use a weapon of mass destruction (18 U.S.C. § 2332a); providing and attempting to provide material support to terrorists (18 U.S.C. § 2339A); possession of an unregistered destructive device (26 U.S.C. § 5861(d))',
                'arrest_date'        => '2025-12-12',
                'incarceration_date' => '2025-12-12',
                'indicted'           => 'Yes — federal grand jury, Central District of California (December 2025)',
                'convicted'          => 'No — awaiting trial (pleaded not guilty)',
                'plead'              => 'Pleaded not guilty; arraigned January 2026, U.S. District Court, Central District of California. Statutory maximum if convicted: life in federal prison.',
            ],
        ];

        // Dante Gaffield — faces up to 25 years
        $defendants[] = [
            'data' => [
                'name' => 'Dante Gaffield', 'first_name' => 'Dante', 'last_name' => 'Gaffield',
                'aka' => 'Nomad',
                'age' => 24, 'state' => 'California', 'era' => '2020s',
                'ideologies' => ['Anti-capitalist', 'Anti-government', 'Pro-Palestine'],
                'affiliation' => ['Turtle Island Liberation Front'],
                'in_custody' => true, 'released' => false, 'awaiting_trial' => true,
                'description' => "Dante Gaffield, of South Los Angeles, is one of four people charged in the alleged Turtle Island Liberation Front New Year's Eve bombing plot. He is charged with providing and attempting to provide material support to terrorists and possession of an unregistered destructive device, and faces a statutory maximum of 25 years in federal prison.\n\n{$plotContext}",
            ],
            'case' => [
                'institution_id' => $mdcLa->id,
                'charges'        => 'Providing and attempting to provide material support to terrorists (18 U.S.C. § 2339A); possession of an unregistered destructive device (26 U.S.C. § 5861(d))',
                'arrest_date'        => '2025-12-12',
                'incarceration_date' => '2025-12-12',
                'indicted'           => 'Yes — federal grand jury, Central District of California (December 2025)',
                'convicted'          => 'No — awaiting trial (pleaded not guilty)',
                'plead'              => 'Pleaded not guilty; arraigned January 2026, U.S. District Court, Central District of California. Statutory maximum if convicted: 25 years in federal prison.',
            ],
        ];

        // Tina Lai — faces up to 25 years
        $defendants[] = [
            'data' => [
                'name' => 'Tina Lai', 'first_name' => 'Tina', 'last_name' => 'Lai',
                'aka' => 'Kickwhere',
                'age' => 41, 'state' => 'California', 'era' => '2020s',
                'ideologies' => ['Anti-capitalist', 'Anti-government', 'Pro-Palestine'],
                'affiliation' => ['Turtle Island Liberation Front'],
                'in_custody' => true, 'released' => false, 'awaiting_trial' => true,
                'description' => "Tina Lai, of Glendale, California, is one of four people charged in the alleged Turtle Island Liberation Front New Year's Eve bombing plot. She is charged with providing and attempting to provide material support to terrorists and possession of an unregistered destructive device, and faces a statutory maximum of 25 years in federal prison.\n\n{$plotContext}",
            ],
            'case' => [
                'institution_id' => $mdcLa->id,
                'charges'        => 'Providing and attempting to provide material support to terrorists (18 U.S.C. § 2339A); possession of an unregistered destructive device (26 U.S.C. § 5861(d))',
                'arrest_date'        => '2025-12-12',
                'incarceration_date' => '2025-12-12',
                'indicted'           => 'Yes — federal grand jury, Central District of California (December 2025)',
                'convicted'          => 'No — awaiting trial (pleaded not guilty)',
                'plead'              => 'Pleaded not guilty; arraigned January 2026, U.S. District Court, Central District of California. Statutory maximum if convicted: 25 years in federal prison.',
            ],
        ];

        foreach ($defendants as $entry) {
            DB::transaction(function () use ($entry, &$created, &$skipped) {
                $name = $entry['data']['name'];
                if (Prisoner::withoutGlobalScopes()->where('name', $name)->exists()) {
                    $this->warn("Skipping {$name} — already exists.");
                    $skipped++;

                    return;
                }

                $prisoner = Prisoner::create($entry['data']);
                PrisonerCase::create(array_merge(['prisoner_id' => $prisoner->id], $entry['case']));
                $this->info("Added {$prisoner->name} (slug: {$prisoner->slug})");
                $created++;
            });
        }

        $this->info("\nDone. Created {$created}, skipped {$skipped}.");

        return self::SUCCESS;
    }
}
