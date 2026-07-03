<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds the three Japanese Americans who committed civil disobedience against the
 * Second World War curfew and exclusion orders (Executive Order 9066) and were
 * prosecuted for it — Gordon Hirabayashi, Minoru Yasui, and Fred Korematsu,
 * whose test cases reached the Supreme Court in 1943–44. Complements the Heart
 * Mountain Fair Play Committee draft resisters already in the database (a
 * different, camp-based cohort).
 *
 * All three convictions were later vacated on writs of coram nobis in the 1980s
 * after the discovery that the government had suppressed and altered evidence.
 *
 * Dates are set with honest precision. Idempotent: prisoner:add refuses
 * duplicates by name and the variant-name guard skips anyone already recorded
 * (these three currently appear only in a history-topic body, not as prisoner
 * records).
 */
final class AddJapaneseAmericanResisters extends Command
{
    protected $signature = 'prisoners:add-japanese-american-resisters';

    protected $description = 'Add the three WWII Japanese American curfew/exclusion resisters (Hirabayashi, Yasui, Korematsu)';

    public function handle(): int
    {
        $people = [
            [
                'payload' => [
                    'name' => 'Gordon Hirabayashi',
                    'first_name' => 'Gordon',
                    'last_name' => 'Hirabayashi',
                    'description' => "Gordon Hirabayashi, a University of Washington senior and Quaker, deliberately defied the Second World War curfew and removal orders imposed on Japanese Americans under Executive Order 9066, walking into an FBI office in May 1942 with a written statement explaining that to obey would be to accept the denial of his rights as an American. Convicted of curfew violation and of failing to report for removal, he served his sentence at a federal road camp near Tucson, Arizona — famously hitchhiking there himself. His appeal produced Hirabayashi v. United States (1943), in which the Supreme Court upheld the curfew. He was jailed again in 1944 for refusing to answer the loyalty questionnaire and submit to the draft. His convictions were vacated on a writ of coram nobis in 1987, and he received the Presidential Medal of Freedom in 2012.",
                    'state' => 'Washington',
                    'gender' => 'Male',
                    'race' => 'Asian',
                    'ideologies' => ['Pacifism', 'Quakerism', 'Civil rights'],
                    'era' => '1940s',
                    'cases' => [
                        [
                            'charges' => 'Deliberately violated the WWII curfew on Japanese Americans and refused to report for forced removal under Executive Order 9066.',
                            'convicted' => 'Convicted, 1942; Hirabayashi v. United States (1943); vacated by coram nobis, 1987',
                            'sentence' => 'Served at a federal road camp near Tucson, Arizona.',
                            'institution_name' => 'Catalina Federal Honor Camp',
                            'institution_city' => 'Tucson',
                            'institution_state' => 'Arizona',
                        ],
                        [
                            'charges' => 'Refused to answer the government loyalty questionnaire and to submit to the draft while incarcerated.',
                            'convicted' => 'Convicted, 1944',
                            'sentence' => 'About one year in federal prison.',
                        ],
                    ],
                ],
                'dates' => ['incarceration_date' => [1942, null, null], 'release_date' => [1943, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Minoru Yasui',
                    'first_name' => 'Minoru',
                    'last_name' => 'Yasui',
                    'description' => "Minoru Yasui, a Portland lawyer and the first Japanese American member of the Oregon bar, set out on the night of 28 March 1942 to get himself arrested: he walked the streets of Portland in deliberate violation of the military curfew imposed on Japanese Americans, so that its constitutionality could be tested in court. Convicted, he was held for about nine months — much of it in solitary confinement — in the Multnomah County Jail. His appeal became Yasui v. United States (1943). His conviction was vacated on a writ of coram nobis in 1984, and he received the Presidential Medal of Freedom posthumously in 2015.",
                    'state' => 'Oregon',
                    'gender' => 'Male',
                    'race' => 'Asian',
                    'ideologies' => ['Civil rights'],
                    'era' => '1940s',
                    'cases' => [[
                        'charges' => 'Deliberately violated the WWII military curfew on Japanese Americans in Portland to test its constitutionality.',
                        'convicted' => 'Convicted of curfew violation, 1942; Yasui v. United States (1943); vacated by coram nobis, 1984',
                        'sentence' => 'Held about nine months, largely in solitary confinement, at the Multnomah County Jail.',
                        'institution_name' => 'Multnomah County Jail',
                        'institution_city' => 'Portland',
                        'institution_state' => 'Oregon',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1942, 3, null], 'release_date' => [1943, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Fred Korematsu',
                    'first_name' => 'Fred',
                    'last_name' => 'Korematsu',
                    'description' => "Fred Korematsu, an Oakland shipyard welder, refused to comply with the forced removal of Japanese Americans in the spring of 1942 and stayed in the Bay Area rather than report to an assembly center. Arrested on 30 May 1942 and convicted of violating the exclusion order, he was sentenced to five years' probation and sent to the Topaz incarceration camp in Utah. His case became Korematsu v. United States (1944), the Supreme Court decision — now widely repudiated — that upheld the mass exclusion. His conviction was vacated on a writ of coram nobis in 1983, and he received the Presidential Medal of Freedom in 1998.",
                    'state' => 'California',
                    'gender' => 'Male',
                    'race' => 'Asian',
                    'ideologies' => ['Civil rights'],
                    'era' => '1940s',
                    'cases' => [[
                        'charges' => "Refused to comply with the exclusion order under Executive Order 9066 and remained in California; arrested 30 May 1942.",
                        'convicted' => 'Convicted of violating the exclusion order, 1942; Korematsu v. United States (1944); vacated by coram nobis, 1983',
                        'sentence' => "Five years' probation; detained and sent to the Topaz incarceration camp, Utah.",
                    ]],
                ],
                'dates' => ['arrest_date' => [1942, 5, 30], 'incarceration_date' => [1942, 5, 30]],
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

        $this->info("\nDone. Processed {$added} Japanese American resister(s).");

        return self::SUCCESS;
    }
}
