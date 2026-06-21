<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * The New Orleans "Desire" Black Panther case (1970-71) — one of the most famous
 * chapters in Panther history (the basis of Orissa Arend's "Showdown in Desire").
 *
 * The New Orleans National Committee to Combat Fascism, organized by Steve Green,
 * Ronald Ailsworth and Harold Holmes in May 1970, set up near the Desire housing
 * project. After police assaulted the party's Piety Street office on Sept. 14-15,
 * 1970 and raided the Desire-project office on Nov. 26, 1970, dozens were
 * arrested. In a landmark August 1971 trial — the first Panther trial before a
 * Black judge and a majority-Black jury — twelve defendants were acquitted of
 * attempting to murder police.
 *
 * Recorded here are figures missing from the database: defense minister Malik
 * Rahim (born Donald Guyton), co-founders Steve Green and Ronald Ailsworth, and
 * the additional defendants The Black Panther named (Alfred McCoy, George Russell
 * and Betty Powell were already added in an earlier batch). Where individual
 * names rest on The Black Panther's contemporaneous coverage, spellings are as
 * rendered there. Idempotent: skips any name already present.
 */
final class AddNewOrleansDesirePanthers extends Command
{
    protected $signature = 'prisoners:add-neworleans-desire';

    protected $description = 'Add the New Orleans Desire-project Black Panther defendants (1970-71)';

    public function handle(): int
    {
        $added = 0;
        $skipped = 0;

        foreach ($this->records() as $r) {
            if (Prisoner::withUnderReview()->where('name', $r['name'])->exists()) {
                $this->warn("Exists, skipping: {$r['name']}");
                $skipped++;

                continue;
            }

            DB::transaction(function () use ($r) {
                $cases = $r['cases'] ?? [];
                unset($r['cases']);
                $prisoner = Prisoner::create($r);
                foreach ($cases as $c) {
                    if (! empty($c['institution_name'])) {
                        $inst = Institution::firstOrCreate(
                            ['name' => $c['institution_name']],
                            ['city' => $c['institution_city'] ?? null, 'state' => $c['institution_state'] ?? null],
                        );
                        $c['institution_id'] = $inst->id;
                    }
                    unset($c['institution_name'], $c['institution_city'], $c['institution_state']);
                    $c['prisoner_id'] = $prisoner->id;
                    PrisonerCase::create($c);
                }
            });

            $this->info("Added: {$r['name']}");
            $added++;
        }

        $this->info("\nDone. Added={$added} Skipped={$skipped}");

        return self::SUCCESS;
    }

    private function records(): array
    {
        // Shared-case scaffold for the additional Desire defendants. $group is
        // 'sept' (the Sept. 14-15, 1970 Piety Street shootout / the twelve tried
        // and acquitted) or 'nov' (the Nov. 26, 1970 Desire-project raid).
        $desire = function (string $name, string $first, string $last, string $group, bool $female = false): array {
            $rec = [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => $female ? 'Female' : 'Male',
                'race' => 'Black',
                'state' => 'Louisiana',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['Black Panther Party', 'National Committee to Combat Fascism'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
            ];
            if ($group === 'sept') {
                $rec['description'] = "{$name} was a member of the New Orleans chapter of the Black Panther Party (organized as the National Committee to Combat Fascism) and one of the defendants arrested after the September 14-15, 1970 police assault on the party's Piety Street office near the Desire housing project. In a landmark trial in August 1971 — the first Black Panther trial held before a Black judge and a majority-Black jury — twelve of the New Orleans defendants were acquitted of attempting to murder police. {$name} is named among the defendants in The Black Panther's contemporaneous coverage.";
                $rec['cases'] = [[
                    'institution_name' => 'Orleans Parish Prison',
                    'institution_city' => 'New Orleans',
                    'institution_state' => 'Louisiana',
                    'charges' => 'Attempted murder of police (the September 1970 Piety Street standoff, New Orleans)',
                    'convicted' => 'No — twelve of the New Orleans Panther defendants were acquitted in August 1971',
                ]];
            } else {
                $rec['description'] = "{$name} was a member of the New Orleans chapter of the Black Panther Party. During the pre-dawn raid of November 26, 1970 — in which about fifty New Orleans police disguised as priests and postal workers stormed the party's office in the Desire housing project — {$name} was among the six people arrested and charged. They were charged with attempted murder and violating the federal firearms act and held at the Orleans Parish Prison. {$name} is named among the defendants in The Black Panther's contemporaneous coverage.";
                $rec['cases'] = [[
                    'institution_name' => 'Orleans Parish Prison',
                    'institution_city' => 'New Orleans',
                    'institution_state' => 'Louisiana',
                    'charges' => 'Attempted murder and violation of the federal firearms act (the Nov. 26, 1970 raid on the Desire-project Black Panther Party office)',
                    'arrest_date' => '1970-11-26',
                ]];
            }

            return $rec;
        };

        return [
            // ---- Leaders / verified figures ----
            [
                'name' => 'Malik Rahim',
                'first_name' => 'Malik',
                'last_name' => 'Rahim',
                'aka' => 'Donald Guyton',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Louisiana',
                'era' => '1970s',
                'birthdate' => '1947-12-17',
                'ideologies' => ['Black liberation', 'Black Power'],
                'affiliation' => ['Black Panther Party', 'National Committee to Combat Fascism'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Malik Rahim, born Donald Guyton on December 17, 1947 in Algiers, Louisiana, was the defense minister of the New Orleans chapter of the Black Panther Party (the National Committee to Combat Fascism) during the 1970 standoffs around the Desire housing project. He was among the twelve New Orleans Panthers charged with attempted murder over the confrontations with police, and all twelve were acquitted by a jury in a landmark August 1971 trial — the first Black Panther trial in the country before a Black judge and a majority-Black jury. Decades later he became a nationally known housing- and prisoner-rights activist, co-founding the Common Ground Collective after Hurricane Katrina in 2005 and running for office as a Green Party candidate.',
                'cases' => [[
                    'institution_name' => 'Orleans Parish Prison',
                    'institution_city' => 'New Orleans',
                    'institution_state' => 'Louisiana',
                    'charges' => 'Attempted murder of police (the September 1970 Piety Street / Desire standoffs, New Orleans)',
                    'convicted' => 'No — acquitted with the other eleven defendants, August 1971',
                ]],
            ],
            [
                'name' => 'Steve Green',
                'first_name' => 'Steve',
                'last_name' => 'Green',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Louisiana',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['Black Panther Party', 'National Committee to Combat Fascism'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Steve Green, a Vietnam veteran recruited into the Black Panther Party by Geronimo Pratt, was one of the organizers (with Ronald Ailsworth and Harold Holmes) who established the National Committee to Combat Fascism in New Orleans in May 1970. He was among the New Orleans Panthers arrested and charged over the 1970 confrontations with police around the Desire housing project; twelve of the defendants were acquitted of attempted murder in the landmark August 1971 trial.',
                'cases' => [[
                    'institution_name' => 'Orleans Parish Prison',
                    'institution_city' => 'New Orleans',
                    'institution_state' => 'Louisiana',
                    'charges' => 'Attempted murder of police (the 1970 Piety Street / Desire standoffs, New Orleans)',
                    'convicted' => 'No — twelve of the New Orleans defendants were acquitted in August 1971',
                ]],
            ],
            [
                'name' => 'Ronald Ailsworth',
                'first_name' => 'Ronald',
                'last_name' => 'Ailsworth',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Louisiana',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['Black Panther Party', 'National Committee to Combat Fascism'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Ronald Ailsworth was one of the three organizers (with Steve Green and Harold Holmes) who established the National Committee to Combat Fascism — the New Orleans chapter of the Black Panther Party — in May 1970. He was among the New Orleans Panthers arrested and charged over the 1970 standoffs with police around the Desire housing project. (His name appears in some of The Black Panther\'s coverage rendered as "Donald Elsworth.")',
                'cases' => [[
                    'institution_name' => 'Orleans Parish Prison',
                    'institution_city' => 'New Orleans',
                    'institution_state' => 'Louisiana',
                    'charges' => 'Attempted murder of police (the 1970 Piety Street / Desire standoffs, New Orleans)',
                ]],
            ],

            // ---- Sept. 1970 Piety Street defendants (the twelve, acquitted Aug. 1971) ----
            $desire('Charles Scott', 'Charles', 'Scott', 'sept'),
            $desire('Tyrone Edwards', 'Tyrone', 'Edwards', 'sept'),
            $desire('Alton Edwards', 'Alton', 'Edwards', 'sept'),
            $desire('William Cloud', 'William', 'Cloud', 'sept'),
            $desire('Isaac Edwards', 'Isaac', 'Edwards', 'sept'),
            $desire('Milton Martin', 'Milton', 'Martin', 'sept'),
            $desire('Leroy Jones', 'Leroy', 'Jones', 'sept'),
            $desire('Elaine Young', 'Elaine', 'Young', 'sept', true),
            $desire('Leah Hodges', 'Leah', 'Hodges', 'sept', true),

            // ---- Nov. 26, 1970 Desire-project raid defendants ----
            $desire('Leon Lewis', 'Leon', 'Lewis', 'nov'),
            $desire('Odell Brown', 'Odell', 'Brown', 'nov'),
            $desire('Larry Jackson', 'Larry', 'Jackson', 'nov'),
        ];
    }
}
