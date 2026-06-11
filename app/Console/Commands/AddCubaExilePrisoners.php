<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Adds the Republic of New Afrika hijacker trio — Charlie Hill, Michael
 * Finney, and Ralph Goodwin — who fled to Cuba in 1971 and were granted
 * political asylum by Fidel Castro, never returning to the United States.
 * They are the clear gap in the "Americans exiled to Cuba" category; the
 * database already carries the other major Cuba exiles (Assata Shakur,
 * Nehanda Abiodun, William Lee Brent, Robert F. Williams, William Morales,
 * Victor Manuel Gerena, Huey P. Newton, Eldridge Cleaver).
 *
 * On November 8, 1971, during a traffic stop outside Albuquerque, New Mexico
 * State Police Officer Robert Rosenbloom was shot and killed. The three hid
 * for nearly three weeks, then on November 27, 1971 hijacked TWA Flight 106
 * to Havana. Goodwin drowned off the Cuban coast in 1973; Finney died of
 * throat cancer in Havana in 2005; Hill lived in Cuba for more than fifty
 * years and never returned.
 *
 * Exact death dates are not reliably documented, so only death years are
 * stated (in the bios) rather than invented as precise death_date values.
 *
 * Idempotent: prisoner:add refuses duplicate names.
 */
final class AddCubaExilePrisoners extends Command {
    protected $signature = 'prisoners:add-cuba-exiles';
    protected $description = 'Add the Republic of New Afrika hijacker trio exiled to Cuba (Hill, Finney, Goodwin)';

    public function handle(): int {
        $base = [
            'race' => 'Black',
            'gender' => 'Male',
            'state' => 'Cuba',
            'ideologies' => ['Black nationalism', 'New Afrikan independence', 'Black liberation'],
            'affiliation' => ['Republic of New Afrika'],
            'era' => '1970s',
            'in_custody' => false,
            'released' => false,
            'in_exile' => true,
            'imprisoned_or_exiled' => true,
        ];

        $prisoners = [
            array_merge($base, [
                'name' => 'Charlie Hill',
                'first_name' => 'Charlie',
                'last_name' => 'Hill',
                'aka' => 'Charles Hill',
                'currently_in_exile' => true,
                'description' => 'Charlie Hill was a member of the Republic of New Afrika, the Black-nationalist movement that sought an independent Black republic in the Deep South. On November 8, 1971, during a traffic stop on Interstate 40 outside Albuquerque, New Mexico, New Mexico State Police Officer Robert Rosenbloom was shot and killed; Hill and fellow members Michael Finney and Ralph Goodwin were charged in connection with the killing. After hiding in the mountains for nearly three weeks, the three hijacked TWA Flight 106 from the Albuquerque airport on November 27, 1971 and forced it to fly to Havana, where Fidel Castro granted them political asylum. Of the three, Hill lived the longest, remaining in Cuba for more than fifty years and becoming one of the best-known American political exiles on the island. He never returned to the United States.',
                'cases' => [[
                    'charges' => 'Murder of New Mexico State Police Officer Robert Rosenbloom and air piracy (hijacking of TWA Flight 106), 1971',
                    'convicted' => 'Never tried; fled to Cuba and granted political asylum',
                    'in_exile_since' => '1971-11-27',
                ]],
            ]),
            array_merge($base, [
                'name' => 'Michael Finney',
                'first_name' => 'Michael',
                'last_name' => 'Finney',
                'currently_in_exile' => false,
                'description' => 'Michael Finney was a member of the Republic of New Afrika, the Black-nationalist movement seeking an independent Black nation in the Deep South. After New Mexico State Police Officer Robert Rosenbloom was shot and killed during a November 8, 1971 traffic stop outside Albuquerque, Finney and fellow members Charlie Hill and Ralph Goodwin evaded capture and, on November 27, 1971, hijacked TWA Flight 106 to Havana, where they were granted political asylum by Fidel Castro. Finney remained in Cuba for the rest of his life and died of throat cancer in Havana in 2005, never having returned to the United States.',
                'cases' => [[
                    'charges' => 'Murder of New Mexico State Police Officer Robert Rosenbloom and air piracy (hijacking of TWA Flight 106), 1971',
                    'convicted' => 'Never tried; fled to Cuba and granted political asylum',
                    'in_exile_since' => '1971-11-27',
                ]],
            ]),
            array_merge($base, [
                'name' => 'Ralph Goodwin',
                'first_name' => 'Ralph',
                'last_name' => 'Goodwin',
                'currently_in_exile' => false,
                'description' => 'Ralph Goodwin was a member of the Republic of New Afrika who, with Charlie Hill and Michael Finney, fled to Cuba after the November 8, 1971 shooting death of New Mexico State Police Officer Robert Rosenbloom during a traffic stop outside Albuquerque. On November 27, 1971 the three hijacked TWA Flight 106 to Havana and were granted political asylum by Fidel Castro. Goodwin, the youngest of the trio, drowned in the sea off the Cuban coast in 1973 — reportedly while trying to save another swimmer — less than two years after arriving, the only one of the three who did not live out a long exile.',
                'cases' => [[
                    'charges' => 'Murder of New Mexico State Police Officer Robert Rosenbloom and air piracy (hijacking of TWA Flight 106), 1971',
                    'convicted' => 'Never tried; fled to Cuba and granted political asylum',
                    'in_exile_since' => '1971-11-27',
                ]],
            ]),
        ];

        $added = 0;
        $skipped = 0;
        foreach ($prisoners as $p) {
            $this->line("\n— {$p['name']} —");
            $code = Artisan::call('prisoner:add', ['json' => json_encode($p, JSON_UNESCAPED_UNICODE)]);
            $this->line(trim(Artisan::output()));
            if ($code === self::SUCCESS) {
                $added++;
            } else {
                $skipped++;
            }
        }

        $this->info("\nDone — added {$added}, skipped {$skipped} (already present).");

        return self::SUCCESS;
    }
}
