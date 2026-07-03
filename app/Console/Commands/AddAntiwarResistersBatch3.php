<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * A third batch of imprisoned pre-1950 anti-war resisters, all Second World War
 * draft refusers and absolutist conscientious objectors:
 *
 *  - Robert Lowell, the poet, who refused induction in 1943 in protest against
 *    the Allied bombing of civilians (his "Memories of West Street and Lepke").
 *  - War Resisters League prison veterans Ralph DiGia and Bill Sutherland and
 *    the anarchist David Wieck, who helped lead the Danbury/Lewisburg strikes
 *    against segregation and censorship.
 *  - Joseph Bevilacqua, Richard Wichlein and Howard Spragg — the last three of
 *    the 1940 "Union Theological Seminary Eight," completing that cohort in the
 *    database (David Dellinger, Donald Benedict, George Houser, Meredith Dallas
 *    and William Lovell are already recorded).
 *
 * Complements prisoners:add-early-cos, prisoners:add-wwii-resisters and
 * prisoners:add-antiwar-resisters-2.
 *
 * Dates are set with honest precision: exact where documented, year/month
 * otherwise, and omitted where unclear. Idempotent: prisoner:add refuses
 * duplicates by name and the variant-name guard skips anyone already recorded.
 */
final class AddAntiwarResistersBatch3 extends Command
{
    protected $signature = 'prisoners:add-antiwar-resisters-3';

    protected $description = 'Add seven more pre-1950 anti-war resisters (WWII draft refusers; completes the Union Eight)';

    public function handle(): int
    {
        $people = [
            [
                'payload' => [
                    'name' => 'Robert Lowell',
                    'first_name' => 'Robert',
                    'last_name' => 'Lowell',
                    'description' => "Robert Lowell, later one of the most celebrated American poets of the century, refused induction into the armed forces in 1943, sending President Roosevelt a \"Declaration of Personal Responsibility\" that condemned the Allied bombing of civilian cities and the demand for unconditional surrender. Convicted of draft evasion and sentenced to a year and a day, he was held at the West Street jail in New York and the federal correctional institution at Danbury before being paroled. He recorded the experience in his poem \"Memories of West Street and Lepke.\"",
                    'state' => 'Massachusetts',
                    'gender' => 'Male',
                    'ideologies' => ['Pacifism', 'Catholicism'],
                    'era' => '1940s',
                    'cases' => [[
                        'charges' => 'Convicted of draft evasion in 1943 after refusing induction in protest against the Allied bombing of civilians and the policy of unconditional surrender.',
                        'convicted' => 'Convicted of draft-law violation, 1943',
                        'sentence' => 'A year and a day; held at the West Street jail (New York) and FCI Danbury; paroled after several months.',
                        'institution_name' => 'Federal Correctional Institution, Danbury',
                        'institution_city' => 'Danbury',
                        'institution_state' => 'Connecticut',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1943, 10, null], 'release_date' => [1944, 3, null]],
            ],
            [
                'payload' => [
                    'name' => 'Ralph DiGia',
                    'first_name' => 'Ralph',
                    'last_name' => 'DiGia',
                    'description' => "Ralph DiGia refused to report for induction in 1943 as a resister to the Second World War and served about three years in the federal prisons at Danbury, Connecticut, and Lewisburg, Pennsylvania, where he helped lead work strikes against racial segregation and mail censorship. He devoted the rest of his life to the cause, working for decades as the administrator of the War Resisters League in New York and taking part in pacifist actions into his eighties.",
                    'state' => 'New York',
                    'gender' => 'Male',
                    'ideologies' => ['Pacifism', 'Socialism'],
                    'affiliation' => ['War Resisters League'],
                    'era' => '1940s',
                    'cases' => [[
                        'charges' => 'Refused to report for induction in 1943 as a conscientious resister to the Second World War.',
                        'convicted' => 'Convicted of draft-law violation, 1943',
                        'sentence' => 'About three years at FCI Danbury and USP Lewisburg; helped lead prison strikes against segregation and censorship; released after the war.',
                        'institution_name' => 'Federal Correctional Institution, Danbury',
                        'institution_city' => 'Danbury',
                        'institution_state' => 'Connecticut',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1943, null, null], 'release_date' => [1946, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Bill Sutherland',
                    'first_name' => 'Bill',
                    'last_name' => 'Sutherland',
                    'description' => "Bill Sutherland refused to cooperate with Second World War conscription — objecting both to war and to serving in a segregated army — and was imprisoned at the Lewisburg penitentiary in Pennsylvania alongside other pacifist resisters. After the war he moved to Africa and became a lifelong bridge between African liberation movements and the American peace and civil-rights movements, working in Ghana and Tanzania and hosting a generation of activists.",
                    'state' => 'New Jersey',
                    'gender' => 'Male',
                    'race' => 'Black',
                    'ideologies' => ['Pacifism', 'Pan-Africanism'],
                    'affiliation' => ['War Resisters League'],
                    'era' => '1940s',
                    'cases' => [[
                        'charges' => 'Imprisoned during the Second World War for refusing to cooperate with conscription and to serve in a segregated army.',
                        'convicted' => 'Convicted of draft-law violation, WWII',
                        'sentence' => 'Held at USP Lewisburg, Pennsylvania; released after the war.',
                        'institution_name' => 'United States Penitentiary, Lewisburg',
                        'institution_city' => 'Lewisburg',
                        'institution_state' => 'Pennsylvania',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1943, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'David Wieck',
                    'first_name' => 'David',
                    'last_name' => 'Wieck',
                    'description' => "David Wieck, an anarchist writer and later a philosophy professor, refused to cooperate with Second World War conscription and was imprisoned as a draft resister. Inside, he was among the organizers of the prison strikes against racial segregation and censorship. After the war he edited the anarchist journal Resistance and wrote widely on ethics and nonviolence.",
                    'gender' => 'Male',
                    'ideologies' => ['Anarchism', 'Pacifism'],
                    'era' => '1940s',
                    'cases' => [[
                        'charges' => 'Imprisoned during the Second World War for refusing to cooperate with conscription.',
                        'convicted' => 'Convicted of draft-law violation, WWII',
                        'sentence' => 'Held in federal prison, where he helped organize strikes against segregation and censorship; released after the war.',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1943, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Joseph Bevilacqua',
                    'first_name' => 'Joseph',
                    'last_name' => 'Bevilacqua',
                    'description' => "Joseph Bevilacqua was one of the eight Union Theological Seminary students — the \"Union Eight\" — who in October 1940 refused to register for the first peacetime draft, the opening act of organized Second World War draft resistance. Convicted in November 1940, he served a year and a day at the federal correctional institution in Danbury, Connecticut.",
                    'gender' => 'Male',
                    'ideologies' => ['Christian pacifism', 'Pacifism'],
                    'era' => '1940s',
                    'cases' => [[
                        'charges' => 'Refused to register for the draft in October 1940 as one of the "Union Eight."',
                        'convicted' => 'Convicted, November 1940',
                        'sentence' => 'A year and a day at FCI Danbury.',
                        'institution_name' => 'Federal Correctional Institution, Danbury',
                        'institution_city' => 'Danbury',
                        'institution_state' => 'Connecticut',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1940, 11, null], 'release_date' => [1941, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Richard Wichlein',
                    'first_name' => 'Richard',
                    'last_name' => 'Wichlein',
                    'description' => "Richard Wichlein was one of the eight Union Theological Seminary students who refused to register for the draft in October 1940, staging the first organized draft resistance of the Second World War era. Convicted in November 1940, he served a year and a day at the federal correctional institution in Danbury, Connecticut.",
                    'gender' => 'Male',
                    'ideologies' => ['Christian pacifism', 'Pacifism'],
                    'era' => '1940s',
                    'cases' => [[
                        'charges' => 'Refused to register for the draft in October 1940 as one of the "Union Eight."',
                        'convicted' => 'Convicted, November 1940',
                        'sentence' => 'A year and a day at FCI Danbury.',
                        'institution_name' => 'Federal Correctional Institution, Danbury',
                        'institution_city' => 'Danbury',
                        'institution_state' => 'Connecticut',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1940, 11, null], 'release_date' => [1941, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Howard Spragg',
                    'first_name' => 'Howard',
                    'last_name' => 'Spragg',
                    'description' => "Howard Spragg was one of the eight Union Theological Seminary students who refused to register for the draft in October 1940 as an act of conscience against war and conscription. Convicted in November 1940, he served a year and a day at the federal correctional institution in Danbury, Connecticut, and went on to a long career in the ministry of the United Church of Christ.",
                    'gender' => 'Male',
                    'ideologies' => ['Christian pacifism', 'Pacifism'],
                    'era' => '1940s',
                    'cases' => [[
                        'charges' => 'Refused to register for the draft in October 1940 as one of the "Union Eight."',
                        'convicted' => 'Convicted, November 1940',
                        'sentence' => 'A year and a day at FCI Danbury.',
                        'institution_name' => 'Federal Correctional Institution, Danbury',
                        'institution_city' => 'Danbury',
                        'institution_state' => 'Connecticut',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1940, 11, null], 'release_date' => [1941, null, null]],
            ],
        ];

        $added = 0;
        foreach ($people as $person) {
            $payload = $person['payload'];
            $payload['in_custody'] = false;
            $payload['released'] = true;

            // Guard against variant-name duplicates: skip anyone whose first AND
            // last name both already appear in a record.
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
            $prisoner->released = true;
            $prisoner->save();

            // Backfill dates on the first case with honest precision.
            $case = $prisoner->cases()->first();
            if ($case && ! empty($person['dates'])) {
                foreach ($person['dates'] as $field => [$y, $m, $d]) {
                    $case->setPartialDate($field, $y, $m, $d);
                }
                $case->save();
            }
            $added++;
        }

        $this->info("\nDone. Processed {$added} anti-war resister(s).");

        return self::SUCCESS;
    }
}
