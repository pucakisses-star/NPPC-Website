<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds eight imprisoned Second World War draft resisters and absolutist
 * conscientious objectors (1940–1946) — civil disobedience against war and
 * conscription in the years before 1950. Complements the WWI-era CO records
 * already in the database (the Hofer brothers, Ben Salmon, Philip Grosser,
 * Ammon Hennacy, Roger Baldwin, Evan Thomas, Julius Eichel, Carl Haessler,
 * Maurice Hess and others) and the Civil War Quaker Cyrus Pringle.
 *
 * Covers the 1940 "Union Eight" seminary draft refusers (the first organized
 * WWII draft resistance), the Danbury dining-hall desegregation strikers, and
 * absolutist noncooperators. David Dellinger — another of the Union Eight — is
 * already in the database and is skipped by the variant-name guard.
 *
 * Dates are set with honest precision: exact where documented, year/month
 * otherwise, and omitted where the record is unclear (the no-release-date guard
 * then shows the duration as unknown rather than inventing one).
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard below skips anyone already recorded, so the backfill is safe to re-run.
 */
final class AddWwiiWarResisters extends Command
{
    protected $signature = 'prisoners:add-wwii-resisters';

    protected $description = 'Add eight imprisoned WWII draft resisters and absolutist conscientious objectors (1940–1946)';

    public function handle(): int
    {
        $people = [
            [
                'payload' => [
                    'name' => 'Bayard Rustin',
                    'first_name' => 'Bayard',
                    'last_name' => 'Rustin',
                    'description' => "Bayard Rustin, the Quaker organizer who would later architect the 1963 March on Washington, refused during the Second World War both to be inducted and to accept conscientious-objector alternative service, holding that any cooperation with the Selective Service system sustained the machinery of war. Convicted of draft violation in 1944, he served roughly twenty-eight months in the federal penitentiaries at Ashland, Kentucky, and Lewisburg, Pennsylvania, where he openly organized against the segregation of the prison dining hall and was disciplined for it. He was released in 1946.",
                    'state' => 'New York',
                    'gender' => 'Male',
                    'race' => 'Black',
                    'ideologies' => ['Pacifism', 'Socialism', 'Quakerism'],
                    'affiliation' => ['Fellowship of Reconciliation', 'War Resisters League'],
                    'era' => '1940s',
                    'cases' => [[
                        'charges' => 'Convicted in 1944 of refusing induction and rejecting conscientious-objector alternative service under the Selective Training and Service Act.',
                        'convicted' => 'Convicted of draft-law violation, 1944',
                        'sentence' => 'Three-year sentence; served ~28 months at the federal penitentiaries in Ashland, Kentucky and Lewisburg, Pennsylvania; released in 1946.',
                        'institution_name' => 'Federal Correctional Institution, Ashland',
                        'institution_city' => 'Ashland',
                        'institution_state' => 'Kentucky',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1944, null, null], 'release_date' => [1946, 6, null]],
            ],
            [
                'payload' => [
                    'name' => 'Corbett Bishop',
                    'first_name' => 'Corbett',
                    'last_name' => 'Bishop',
                    'description' => "Corbett Bishop, a bookseller and absolutist pacifist, walked out of a Civilian Public Service camp during the Second World War to protest conscription and, once arrested, carried noncooperation to its limit: he refused to walk, dress, or feed himself, forcing the authorities to carry and force-feed him, and he would sign nothing and accept no conditions of release. After roughly 426 days in custody — including a final unbroken 193-day stretch of total noncooperation — the government released him unconditionally in 1946 rather than make him a martyr.",
                    'gender' => 'Male',
                    'ideologies' => ['Pacifism'],
                    'era' => '1940s',
                    'cases' => [[
                        'charges' => 'Arrested after leaving a Civilian Public Service camp in protest against Second World War conscription.',
                        'convicted' => 'Held for refusing military conscription, 1944–1946',
                        'sentence' => 'Practiced total noncooperation — refusing to walk, dress, or eat — through roughly 426 days in custody, including a final 193-day unbroken stretch; released unconditionally in 1946.',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1944, null, null], 'release_date' => [1946, 3, null]],
            ],
            [
                'payload' => [
                    'name' => 'Lowell Naeve',
                    'first_name' => 'Lowell',
                    'last_name' => 'Naeve',
                    'description' => "Lowell Naeve, a young painter, refused to register and later to submit to induction for the Second World War, and was imprisoned more than once — at the West Street jail in New York and the federal correctional institution at Danbury, Connecticut — where he kept resisting from inside, joining work strikes and destroying his own draft paperwork. His illustrated memoir A Field of Broken Stones (1950) became a classic first-person account of the wartime resisters. He was released after the war.",
                    'gender' => 'Male',
                    'ideologies' => ['Pacifism', 'Anarchism'],
                    'era' => '1940s',
                    'cases' => [[
                        'charges' => 'Imprisoned for refusing to register and to submit to induction under Second World War conscription.',
                        'convicted' => 'Convicted of draft-law violation, WWII',
                        'sentence' => 'Held at the West Street jail (New York) and FCI Danbury; took part in prison strikes against the war; released after the war.',
                        'institution_name' => 'Federal Correctional Institution, Danbury',
                        'institution_city' => 'Danbury',
                        'institution_state' => 'Connecticut',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1941, null, null], 'release_date' => [1946, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'James Peck',
                    'first_name' => 'James',
                    'last_name' => 'Peck',
                    'description' => "James Peck, heir to a clothing fortune who gave it up for a life of radical pacifism, refused military service in the Second World War and served roughly three years as a conscientious objector at the federal correctional institution in Danbury, Connecticut. There in 1943 he helped lead a 135-day strike that forced the desegregation of the prison dining hall — one of the first successful nonviolent actions against Jim Crow in a federal institution. He later became a Freedom Rider, beaten nearly to death in Birmingham in 1961.",
                    'state' => 'New York',
                    'gender' => 'Male',
                    'ideologies' => ['Pacifism', 'Socialism'],
                    'affiliation' => ['War Resisters League', 'Congress of Racial Equality'],
                    'era' => '1940s',
                    'cases' => [[
                        'charges' => 'Refused military service in the Second World War as a conscientious objector.',
                        'convicted' => 'Convicted of draft-law violation, WWII',
                        'sentence' => 'About three years at FCI Danbury; helped lead the 1943 strike that desegregated the prison dining hall; released around 1945.',
                        'institution_name' => 'Federal Correctional Institution, Danbury',
                        'institution_city' => 'Danbury',
                        'institution_state' => 'Connecticut',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1942, null, null], 'release_date' => [1945, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Caleb Foote',
                    'first_name' => 'Caleb',
                    'last_name' => 'Foote',
                    'description' => "Caleb Foote, a Fellowship of Reconciliation organizer and pacifist writer, refused to cooperate with Second World War conscription and was imprisoned twice for it, using the war years to agitate against both militarism and the internment of Japanese Americans. After the war he became one of the country's leading scholars of criminal justice and bail reform, teaching law at the University of Pennsylvania and the University of California, Berkeley.",
                    'gender' => 'Male',
                    'ideologies' => ['Pacifism'],
                    'affiliation' => ['Fellowship of Reconciliation'],
                    'era' => '1940s',
                    'cases' => [[
                        'charges' => 'Refused to register for and cooperate with Second World War conscription.',
                        'convicted' => 'Convicted of draft-law violation (twice), WWII',
                        'sentence' => 'Served two federal prison terms as a wartime draft resister.',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1943, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'George Houser',
                    'first_name' => 'George',
                    'last_name' => 'Houser',
                    'description' => "George Houser, a divinity student, was one of the eight Union Theological Seminary students — the \"Union Eight\" — who in October 1940 publicly refused to register for the newly enacted peacetime draft, staging the first organized draft resistance of the Second World War era. Convicted in November 1940, he served a year and a day at the federal correctional institution in Danbury, Connecticut. In 1942 he helped found the Congress of Racial Equality (CORE) and went on to a lifetime of civil-rights and anti-colonial organizing.",
                    'gender' => 'Male',
                    'ideologies' => ['Pacifism', 'Christian pacifism'],
                    'affiliation' => ['Fellowship of Reconciliation', 'Congress of Racial Equality'],
                    'era' => '1940s',
                    'cases' => [[
                        'charges' => 'Refused to register for the draft under the Selective Training and Service Act in October 1940 as one of the "Union Eight."',
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
                    'name' => 'Donald Benedict',
                    'first_name' => 'Donald',
                    'last_name' => 'Benedict',
                    'description' => "Donald Benedict was one of the eight Union Theological Seminary students who refused to register for the draft in October 1940, the opening act of organized Second World War draft resistance in America. Sentenced to a year and a day at Danbury, he later refused to cooperate a second time and served an additional term. He went on to a long career as a socially engaged urban minister, leading the Community Renewal Society in Chicago.",
                    'gender' => 'Male',
                    'ideologies' => ['Christian pacifism', 'Pacifism'],
                    'era' => '1940s',
                    'cases' => [[
                        'charges' => 'Refused to register for the draft in October 1940 as one of the "Union Eight"; later refused to cooperate a second time.',
                        'convicted' => 'Convicted, 1940 (and again mid-war)',
                        'sentence' => 'A year and a day at FCI Danbury, followed by a further term for renewed noncooperation.',
                        'institution_name' => 'Federal Correctional Institution, Danbury',
                        'institution_city' => 'Danbury',
                        'institution_state' => 'Connecticut',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1940, 11, null]],
            ],
            [
                'payload' => [
                    'name' => 'Igal Roodenko',
                    'first_name' => 'Igal',
                    'last_name' => 'Roodenko',
                    'description' => "Igal Roodenko, a printer and lifelong pacifist, was imprisoned during the Second World War for refusing to cooperate with conscription, having concluded that even alternative Civilian Public Service made him part of the war machine. In 1947 he joined the Journey of Reconciliation — the first Freedom Ride testing bus desegregation in the South — and was sentenced to a North Carolina chain gang for it. He later chaired the War Resisters League for decades.",
                    'gender' => 'Male',
                    'ideologies' => ['Pacifism', 'Anarchism'],
                    'affiliation' => ['War Resisters League'],
                    'era' => '1940s',
                    'cases' => [[
                        'charges' => 'Imprisoned during the Second World War for refusing to cooperate with military conscription.',
                        'convicted' => 'Convicted of draft-law violation, WWII',
                        'sentence' => 'Served a federal prison term as a wartime conscientious objector (exact dates not documented here).',
                    ]],
                ],
                'dates' => [],
            ],
        ];

        $added = 0;
        foreach ($people as $person) {
            $payload = $person['payload'];
            $payload['in_custody'] = false;
            $payload['released'] = true;

            // Guard against variant-name duplicates (e.g. an existing "David
            // Dellinger" among the Union Eight): skip anyone whose first AND last
            // name both already appear in a record.
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

        $this->info("\nDone. Processed {$added} WWII war resister(s).");

        return self::SUCCESS;
    }
}
