<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * A second batch of imprisoned pre-1950 anti-war resisters — civil disobedience
 * against conscription and war, spanning the First World War Espionage Act
 * prosecutions and the Second World War draft-resistance movement.
 *
 * WWI: Charles Schenck (anti-draft leaflets; the Schenck v. United States
 * "clear and present danger" case) and the Mexican anarchists Ricardo Flores
 * Magón and Librado Rivera, convicted for a 1918 anti-war manifesto — Magón
 * died in Fort Leavenworth in 1922.
 *
 * WWII: the Danbury/Springfield hunger strikers Stanley Murphy and Louis
 * Taylor, the remaining "Union Eight" seminary draft refusers Meredith Dallas
 * and William Lovell, and Larry Gara — jailed as a wartime draft resister and
 * again in 1949 for counseling a student to refuse to register.
 *
 * Complements the WWI CO records (prisoners:add-early-cos) and the WWII
 * resisters batch (prisoners:add-wwii-resisters) already in the database.
 *
 * Dates are set with honest precision: exact where documented, year/month
 * otherwise, and omitted where unclear. Idempotent: prisoner:add refuses
 * duplicates by name and the variant-name guard skips anyone already recorded.
 */
final class AddAntiwarResistersBatch2 extends Command
{
    protected $signature = 'prisoners:add-antiwar-resisters-2';

    protected $description = 'Add eight more pre-1950 anti-war resisters (WWI Espionage Act + WWII draft resisters)';

    public function handle(): int
    {
        $people = [
            [
                'payload' => [
                    'name' => 'Charles Schenck',
                    'first_name' => 'Charles',
                    'last_name' => 'Schenck',
                    'description' => "Charles Schenck, general secretary of the Socialist Party in Philadelphia, organized the printing and mailing of roughly 15,000 leaflets in 1917 urging drafted men to resist conscription, which the leaflets condemned as unconstitutional involuntary servitude. He was convicted under the Espionage Act of 1917, and his appeal produced Schenck v. United States (1919), in which Justice Holmes announced the \"clear and present danger\" test and the \"shouting fire in a crowded theatre\" analogy while upholding his conviction. He served six months.",
                    'state' => 'Pennsylvania',
                    'gender' => 'Male',
                    'ideologies' => ['Socialism', 'Pacifism'],
                    'affiliation' => ['Socialist Party of America'],
                    'era' => '1910s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Convicted under the Espionage Act of 1917 for mailing ~15,000 leaflets urging draftees to resist conscription as unconstitutional involuntary servitude.',
                        'convicted' => 'Convicted under the Espionage Act, 1918; upheld in Schenck v. United States (1919)',
                        'sentence' => 'Six months\' imprisonment.',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1919, null, null], 'release_date' => [1919, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Ricardo Flores Magón',
                    'first_name' => 'Ricardo',
                    'last_name' => 'Magón',
                    'description' => "Ricardo Flores Magón, the Mexican anarchist and intellectual force behind the Partido Liberal Mexicano and the newspaper Regeneración, spent years in and out of U.S. prisons for his revolutionary activity while exiled in the United States. In 1918 he and Librado Rivera issued a manifesto to the anarchists of the world; the government prosecuted it under the Espionage Act as obstruction of the war effort, and Magón was sentenced to twenty years. He died in his cell at Fort Leavenworth on 21 November 1922 — officially of heart failure, though comrades charged medical neglect.",
                    'state' => 'California',
                    'gender' => 'Male',
                    'death_date' => '1922-11-21',
                    'ideologies' => ['Anarchism'],
                    'affiliation' => ['Partido Liberal Mexicano'],
                    'era' => '1910s',
                    'released' => false,
                    'cases' => [[
                        'charges' => 'Convicted under the Espionage Act of 1917 for a 1918 anarchist manifesto the government held to obstruct the U.S. war effort.',
                        'convicted' => 'Convicted under the Espionage Act, 1918',
                        'sentence' => '20 years; held at McNeil Island and Fort Leavenworth, where he died in custody on 21 November 1922.',
                        'institution_name' => 'United States Penitentiary, Leavenworth',
                        'institution_city' => 'Leavenworth',
                        'institution_state' => 'Kansas',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1918, null, null], 'death_in_custody_date' => [1922, 11, 21]],
            ],
            [
                'payload' => [
                    'name' => 'Librado Rivera',
                    'first_name' => 'Librado',
                    'last_name' => 'Rivera',
                    'description' => "Librado Rivera, a Mexican anarchist and lifelong collaborator of Ricardo Flores Magón, co-edited Regeneración and helped lead the Partido Liberal Mexicano from exile in the United States. Convicted with Magón under the Espionage Act for their 1918 anti-war manifesto, he was sentenced to fifteen years and imprisoned at Fort Leavenworth. Released in 1923 and deported to Mexico, he kept publishing anarchist papers and was jailed repeatedly there until his death.",
                    'state' => 'California',
                    'gender' => 'Male',
                    'ideologies' => ['Anarchism'],
                    'affiliation' => ['Partido Liberal Mexicano'],
                    'era' => '1910s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Convicted with Ricardo Flores Magón under the Espionage Act for the 1918 anarchist anti-war manifesto.',
                        'convicted' => 'Convicted under the Espionage Act, 1918',
                        'sentence' => '15 years; imprisoned at Fort Leavenworth; released in 1923 and deported to Mexico.',
                        'institution_name' => 'United States Penitentiary, Leavenworth',
                        'institution_city' => 'Leavenworth',
                        'institution_state' => 'Kansas',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1918, null, null], 'release_date' => [1923, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Stanley Murphy',
                    'first_name' => 'Stanley',
                    'last_name' => 'Murphy',
                    'description' => "Stanley Murphy, a Second World War absolutist conscientious objector, walked out of a Civilian Public Service camp to protest conscription and, imprisoned, mounted one of the era's longest hunger strikes against the war and the objector system. He and fellow resister Louis Taylor were force-fed and confined in the federal medical center at Springfield, Missouri — their ordeal became a rallying point for the wartime pacifist movement.",
                    'gender' => 'Male',
                    'ideologies' => ['Pacifism'],
                    'era' => '1940s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Imprisoned after walking out of a Civilian Public Service camp in protest against Second World War conscription.',
                        'convicted' => 'Held as a wartime conscientious objector, 1943',
                        'sentence' => 'Undertook a prolonged hunger strike; force-fed and confined at the federal medical center in Springfield, Missouri.',
                        'institution_name' => 'United States Medical Center for Federal Prisoners, Springfield',
                        'institution_city' => 'Springfield',
                        'institution_state' => 'Missouri',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1943, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Louis Taylor',
                    'first_name' => 'Louis',
                    'last_name' => 'Taylor',
                    'description' => "Louis Taylor, a Second World War conscientious objector, joined Stanley Murphy in walking out of a Civilian Public Service camp to protest conscription and in the long hunger strike that followed. Force-fed and held at the federal medical center in Springfield, Missouri, the two men's resistance drew national attention to the treatment of the wartime objectors.",
                    'gender' => 'Male',
                    'ideologies' => ['Pacifism'],
                    'era' => '1940s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Imprisoned after walking out of a Civilian Public Service camp in protest against Second World War conscription.',
                        'convicted' => 'Held as a wartime conscientious objector, 1943',
                        'sentence' => 'Joined a prolonged hunger strike; force-fed and confined at the federal medical center in Springfield, Missouri.',
                        'institution_name' => 'United States Medical Center for Federal Prisoners, Springfield',
                        'institution_city' => 'Springfield',
                        'institution_state' => 'Missouri',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1943, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Meredith Dallas',
                    'first_name' => 'Meredith',
                    'last_name' => 'Dallas',
                    'description' => "Meredith Dallas was one of the eight Union Theological Seminary students — the \"Union Eight\" — who in October 1940 refused to register for the first peacetime draft, the opening act of organized Second World War draft resistance. Convicted in November 1940, he served a year and a day at the federal correctional institution in Danbury, Connecticut. He later became a noted professor of theater at Antioch College.",
                    'gender' => 'Male',
                    'ideologies' => ['Christian pacifism', 'Pacifism'],
                    'era' => '1940s',
                    'released' => true,
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
                    'name' => 'William Lovell',
                    'first_name' => 'William',
                    'last_name' => 'Lovell',
                    'description' => "William Lovell was one of the eight Union Theological Seminary students who refused to register for the draft in October 1940, staging the first organized draft resistance of the Second World War era. Sentenced to a year and a day, he served his term at the federal correctional institution in Danbury, Connecticut, and went on to a career in the ministry.",
                    'gender' => 'Male',
                    'ideologies' => ['Christian pacifism', 'Pacifism'],
                    'era' => '1940s',
                    'released' => true,
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
                    'name' => 'Larry Gara',
                    'first_name' => 'Larry',
                    'last_name' => 'Gara',
                    'description' => "Larry Gara, later a distinguished pacifist historian, refused to register for the draft in the Second World War and served a federal prison term as a young resister. In 1949 he was prosecuted again — this time for counseling a Bluffton College student to refuse to register — and convicted under the Selective Service Act, one of the most prominent draft-counseling cases of the postwar years. He spent decades afterward teaching history and writing on nonviolence at Wilmington College.",
                    'state' => 'Ohio',
                    'gender' => 'Male',
                    'ideologies' => ['Pacifism'],
                    'era' => '1940s',
                    'released' => true,
                    'cases' => [
                        [
                            'charges' => 'Refused to register for the draft during the Second World War.',
                            'convicted' => 'Convicted of draft-law violation, WWII',
                            'sentence' => 'Served a federal prison term as a wartime draft resister.',
                        ],
                        [
                            'charges' => 'Convicted in 1949 under the Selective Service Act for counseling a Bluffton College student to refuse to register for the draft.',
                            'convicted' => 'Convicted, 1949',
                            'sentence' => 'Sentenced to prison for draft counseling; served about fifteen months.',
                        ],
                    ],
                ],
                'dates' => ['incarceration_date' => [1943, null, null]],
            ],
        ];

        $added = 0;
        foreach ($people as $person) {
            $payload = $person['payload'];
            $payload['in_custody'] = false;
            if (! array_key_exists('released', $payload)) {
                $payload['released'] = true;
            }

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
            $prisoner->released = $payload['released'];
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
