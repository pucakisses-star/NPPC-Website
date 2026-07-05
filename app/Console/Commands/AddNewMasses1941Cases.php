<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * New Masses mining, batch 9 — 1941.
 *
 * 1941 was dominated by the Browder clemency campaign, the Harry Bridges
 * deportation fight, and — after June — the pro-war turn of the Communist-
 * aligned New Masses following the Nazi invasion of the USSR. The magazine was
 * hostile to the Trotskyist defendants of the December 1941 Minneapolis Smith
 * Act trial and named none of them. The standing cases (Browder, William
 * Weiner, Sam Darcy, Morris Schappes, the Oklahoma City core defendants Robert
 * & Ina Wood / Alan Shaw / Eli Jaffe, Joseph Gelders, the King-Ramsay-Conner
 * defendants, Scottsboro) are all already in the database and skipped.
 *
 * This adds the genuinely-new US class-war prisoners of 1941: the Philadelphia
 * Workers School "bomb plot" frame-up, the Reading PA ballot-petition
 * prosecution, a Chicago International Harvester strike-HQ raid, two additional
 * Oklahoma City criminal-syndicalism co-defendants, the San Francisco "California
 * Scottsboro" case, and a framed ex-ILD organizer at Sing Sing.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddNewMasses1941Cases extends Command
{
    protected $signature = 'prisoners:add-new-masses-1941';

    protected $description = 'Add the genuinely-new US class-war prisoners surfaced mining New Masses 1941 (the Philadelphia bomb-plot frame-up, the Reading PA ballot case, additional Oklahoma City defendants, the Festus Coleman case, and more)';

    public function handle(): int
    {
        $people = [];
        $mk = function (array $p, array $dates = []) use (&$people) {
            $people[] = ['payload' => $p, 'dates' => $dates];
        };

        // ── PENNSYLVANIA — PHILADELPHIA WORKERS SCHOOL "BOMB PLOT" ──────
        $mk([
            'name' => 'Adolph Heller', 'first_name' => 'Adolph', 'last_name' => 'Heller',
            'description' => "Adolph Heller was director of the Workers School in Philadelphia who, in April 1941, was convicted in a 'bomb plot' frame-up after police claimed to have found a time bomb following an anonymous phone tip. The case rested on circumstantial evidence and the trial judge harangued the jury about Communism; the conviction was later set aside by the judge for insufficient evidence.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Convicted in a 'bomb plot' frame-up built on an anonymous tip.",
                'convicted' => 'Convicted, 1941',
                'sentence' => 'Conviction later set aside for insufficient evidence.',
                'institution_city' => 'Philadelphia', 'institution_state' => 'Pennsylvania',
            ]],
        ], ['arrest_date' => [1940, null, null]]);

        $mk([
            'name' => 'Bernard Rush', 'first_name' => 'Bernard', 'last_name' => 'Rush',
            'description' => "Bernard Rush was a student at the Workers School in Philadelphia arrested and convicted alongside school director Adolph Heller in the 1940–41 'bomb plot' frame-up — a case built on a time bomb police claimed to have found after an anonymous tip and presented as an anti-Communist prosecution. His conviction, like Heller's, was subsequently set aside by the trial judge for weak evidence.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Convicted in the Philadelphia Workers School 'bomb plot' frame-up.",
                'convicted' => 'Convicted, 1941',
                'sentence' => 'Conviction later set aside for weak evidence.',
                'institution_city' => 'Philadelphia', 'institution_state' => 'Pennsylvania',
            ]],
        ], ['arrest_date' => [1940, null, null]]);

        // ── PENNSYLVANIA — READING BALLOT-PETITION PROSECUTION ──────────
        $mk([
            'name' => 'Ben Rubin', 'first_name' => 'Ben', 'last_name' => 'Rubin',
            'description' => "Ben Rubin was a young Communist Party organizer in Reading, Berks County, Pennsylvania — a wounded, decorated veteran of the Spanish Civil War — who ran as the CP candidate for Congress. From August 1940 he was repeatedly arrested and prosecuted on 'perjury' and election-code charges for gathering signatures on his party's nominating petitions, a campaign used to keep the Communist ticket off the ballot. Facing dozens of indictments, he accumulated roughly seventeen years in cumulative sentences and awaited a third trial in early 1941.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Communism'], 'affiliation' => ['Communist Party'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Prosecuted on 'perjury'/election-code charges for gathering Communist nominating-petition signatures.",
                'convicted' => 'Convicted, 1940-41',
                'sentence' => 'Roughly seventeen years in cumulative sentences across multiple trials.',
                'institution_city' => 'Reading', 'institution_state' => 'Pennsylvania',
            ]],
        ], ['arrest_date' => [1940, 8, null]]);

        // ── ILLINOIS — INTERNATIONAL HARVESTER STRIKE (Chicago) ────────
        $mk([
            'name' => 'Jack Ryan', 'first_name' => 'Jack', 'last_name' => 'Ryan',
            'description' => "Jack Ryan was a CIO organizer during the 1941 International Harvester strike in the Chicago area. When police raided the CIO strike headquarters without warrants, breaking down the door, Ryan was beaten and arrested with seventeen other strikers; the group was held incommunicado for two days before being booked on disorderly-conduct charges.",
            'state' => 'Illinois', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'], 'affiliation' => ['Congress of Industrial Organizations'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Beaten and arrested in a warrantless police raid on CIO strike headquarters.',
                'convicted' => 'Arrested, 1941',
                'sentence' => 'Held incommunicado two days; booked on disorderly conduct.',
                'institution_city' => 'Chicago', 'institution_state' => 'Illinois',
            ]],
        ], ['arrest_date' => [1941, null, null]]);

        // ── OKLAHOMA — ADDITIONAL OKLAHOMA CITY DEFENDANTS ──────────────
        $mk([
            'name' => 'Herbert Brausch', 'first_name' => 'Herbert', 'last_name' => 'Brausch',
            'description' => "Herbert 'Herb' Brausch was an Oklahoma City labor organizer who founded Local 602 of the Hod Carriers' Union. In the 1940–41 Oklahoma County criminal-syndicalism roundup he was arrested — reportedly after buying a copy of the Daily Worker — and jailed roughly eight months in the county jail awaiting trial, charged under the same Red Scare statute used against Robert and Ina Wood, Alan Shaw and Eli Jaffe.",
            'state' => 'Oklahoma', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'], 'affiliation' => ["Hod Carriers' Union"],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Charged under the Oklahoma criminal-syndicalism law in the Oklahoma City roundup.',
                'convicted' => 'Jailed, 1940-41',
                'sentence' => 'Jailed about eight months awaiting trial.',
                'institution_name' => 'Oklahoma County Jail',
                'institution_city' => 'Oklahoma City', 'institution_state' => 'Oklahoma',
            ]],
        ], ['arrest_date' => [1940, null, null]]);

        $mk([
            'name' => 'Orval Lewis', 'first_name' => 'Orval', 'last_name' => 'Lewis',
            'description' => "Orval Lewis was the eighteen-year-old son of Oklahoma homesteaders swept up in Oklahoma County's 1940–41 criminal-syndicalism prosecutions, arrested and jailed alongside his parents in the roundup of about a dozen alleged Communists and radicals built largely on the possession of 'radical' books. The convictions from the Oklahoma City episode were overturned by the state Court of Criminal Appeals in 1943.",
            'state' => 'Oklahoma', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Jailed under the Oklahoma criminal-syndicalism law for possession of 'radical' literature.",
                'convicted' => 'Jailed, 1940-41',
                'sentence' => 'Jailed; convictions overturned on appeal in 1943.',
                'institution_city' => 'Oklahoma City', 'institution_state' => 'Oklahoma',
            ]],
        ], ['arrest_date' => [1940, null, null]]);

        // ── CALIFORNIA — "CALIFORNIA SCOTTSBORO" ────────────────────────
        $mk([
            'name' => 'Festus Coleman', 'first_name' => 'Festus', 'last_name' => 'Coleman',
            'description' => "Festus Lewis Coleman was a young Black San Franciscan arrested in April 1941 and convicted on robbery and assault charges that the International Labor Defense denounced as a political frame-up, promoting his cause as a 'California Scottsboro case.' The ILD named him among its 'new labor and political prisoners' in its December 1941 drive; he was imprisoned in California.",
            'state' => 'California', 'gender' => 'Male', 'race' => 'Black',
            'ideologies' => ['Civil rights'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Convicted on robbery and assault charges the ILD called a 'California Scottsboro' frame-up.",
                'convicted' => 'Convicted, 1941',
                'sentence' => 'Imprisoned in California.',
                'institution_city' => 'San Francisco', 'institution_state' => 'California',
            ]],
        ], ['arrest_date' => [1941, 4, null]]);

        // ── NEW YORK — FRAMED EX-ILD ORGANIZER ──────────────────────────
        $mk([
            'name' => 'Reginald Thomas', 'first_name' => 'Reginald', 'last_name' => 'Thomas',
            'description' => "Reginald Thomas was a former International Labor Defense organizer sent to Sing Sing prison on a two-to-four-year sentence for a stabbing that New Masses (1941) reported he did not commit — eyewitnesses described a slight 17-year-old, while Thomas was 34 and heavyset. New Masses charged that he was prosecuted in retaliation for his earlier labor-defense organizing.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'], 'affiliation' => ['International Labor Defense'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Convicted of a stabbing the left press said he did not commit, in retaliation for ILD organizing.',
                'convicted' => 'Convicted, 1941',
                'sentence' => 'Two to four years at Sing Sing.',
                'institution_name' => 'Sing Sing', 'institution_state' => 'New York',
            ]],
        ], ['incarceration_date' => [1941, null, null]]);

        // ── INSERT ───────────────────────────────────────────────────────
        $added = 0;
        foreach ($people as $person) {
            $payload = $person['payload'];
            $payload['in_custody'] = false;
            if (! array_key_exists('released', $payload)) {
                $payload['released'] = true;
            }

            $existing = Prisoner::withoutGlobalScopes()
                ->where('name', 'like', '%'.$payload['first_name'].'%')
                ->where('name', 'like', '%'.$payload['last_name'].'%')
                ->first();
            if ($existing) {
                $this->line("  already in database as \"{$existing->name}\" — skipping {$payload['name']}.");

                continue;
            }

            $this->call('prisoner:add', ['json' => json_encode($payload)]);

            $prisoner = Prisoner::withoutGlobalScopes()->where('name', $payload['name'])->first();
            if (! $prisoner) {
                continue;
            }
            $prisoner->in_custody = false;
            $prisoner->released = $payload['released'];
            $prisoner->save();

            $case = $prisoner->cases()->first();
            if ($case && ! empty($person['dates'])) {
                foreach ($person['dates'] as $field => [$y, $m, $d]) {
                    $case->setPartialDate($field, $y, $m, $d);
                }
                $case->save();
            }
            $added++;
        }

        $this->info("\nDone. Processed {$added} of ".count($people)." New Masses 1941 prisoner(s).");

        return self::SUCCESS;
    }
}
