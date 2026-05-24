<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds 20 named political prisoners surfaced from the Wikipedia
 * "Mass racial violence in the United States" article, grouped into
 * three foundational cases of state prosecution of marginalized
 * defendants following mass-violence events:
 *
 *   - Elaine 12 (Arkansas, 1919) — Black sharecroppers sentenced to
 *     death after the white-mob massacre of 100–800 of their
 *     neighbors. Moore v. Dempsey (1923) was a landmark SCOTUS
 *     decision overturning their death sentences for lack of due
 *     process — the first time the Court intervened in a state
 *     criminal proceeding on those grounds.
 *
 *   - Modoc War defendants (1873) — the only Indigenous combatants
 *     in U.S. history convicted as "war criminals" by a military
 *     commission. Four hanged at Fort Klamath, two given life at
 *     Alcatraz.
 *
 *   - Plenty Horses (1891) — Sicangu Lakota young man who shot
 *     Lt. Edward Casey nine days after the Wounded Knee massacre;
 *     acquitted because the judge ruled a state of war existed.
 *
 *   - Harlem Six (1964) — six Black teenagers convicted of murder
 *     after the April 1964 Harlem police-beating disturbance;
 *     widely seen as a frame-up by NYPD.
 *
 * Idempotent — prisoner:add refuses duplicates by name.
 */
final class AddElaineModocHarlem extends Command {
    protected $signature = 'archive:add-elaine-modoc-harlem';
    protected $description = 'Add 20 PPs: Elaine 12 (7), Modoc War (6), Plenty Horses, Harlem Six (6)';

    public function handle(): int {
        $added = 0; $skipped = 0;

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
        $elaineSharedDescription = 'One of the "Elaine Twelve" — Black sharecroppers in Phillips County, Arkansas, sentenced to death by all-white juries in trials lasting less than an hour, after the September-October 1919 Elaine massacre. White mobs and federal troops killed an estimated 100 to 800 Black residents, who had been organizing the Progressive Farmers and Household Union of America to demand fair settlement for their cotton crops. The state prosecuted 122 Black survivors; not a single white attacker was charged. The death sentences of the "Moore" defendants were vacated by the U.S. Supreme Court in Moore v. Dempsey (1923), a landmark ruling that for the first time held that mob-dominated trials violate federal due process.';
        $elaineCase = [
            'institution_name' => 'Arkansas State Penitentiary',
            'institution_state' => 'Arkansas',
            'charges' => 'First-degree murder of Clinton Lee (a white deputy killed during the white-mob assault); related accessory charges.',
            'arrest_date' => '1919-10-01',
            'sentenced_date' => '1919-11-03',
            'release_date' => '1925-01-14',
            'convicted' => 'Yes — death sentences vacated by Moore v. Dempsey (1923).',
            'sentence' => 'Death by electrocution; vacated 1923; released 1925.',
        ];

        $modocSharedDescription = 'A combatant in the Modoc War (1872–1873) — the only Indigenous fighters in U.S. history convicted as "war criminals" by a U.S. military commission for their role in the killing of Brigadier General Edward Canby during peace negotiations at the Lava Beds on April 11, 1873. The Modoc had resisted forced removal from their California ancestral lands to the Klamath Reservation in Oregon for years before the war.';

        $harlemSharedDescription = 'One of the "Harlem Six" — Black teenagers arrested in May 1964 and charged with the murder of clothing-store owner Margit Sugar, 12 days after the April 17, 1964 "Little Fruit Stand Riot" police-beating disturbance in Harlem. The case became a landmark frame-up exposé: independent journalists documented that the confessions were coerced by NYPD detectives. James Baldwin published "A Report from Occupied Territory" defending the six in 1966.';
        $harlemCase = [
            'institution_state' => 'New York',
            'charges' => 'First-degree murder of Margit Sugar (April 29, 1964 Harlem clothing-store killing).',
            'arrest_date' => '1964-05-01',
            'sentenced_date' => '1965-04-08',
            'sentence' => 'Life imprisonment.',
        ];

        $elaine = ['Frank Moore', 'Ed Hicks', 'Frank Hicks', 'J. E. Knox', 'Paul Hall', 'Ed Coleman', 'Ed Ware'];
        $modocHanged = ['Schonchin John', 'Black Jim', 'Boston Charley'];
        $modocLife = ['Barncho', 'Slolux'];
        $harlem = ['Wallace Baker', 'Daniel Hamm', 'William Craig', 'Ronald Felder', 'Walter Thomas', 'Robert Rice'];

        $out = [];

        foreach ($elaine as $name) {
            $parts = preg_split('/\s+/', $name);
            $out[] = [
                'name' => $name,
                'first_name' => $parts[0],
                'last_name' => end($parts),
                'description' => $elaineSharedDescription,
                'state' => 'Arkansas',
                'race' => 'Black',
                'gender' => 'Male',
                'ideologies' => ['Black Southern labor organizing'],
                'affiliation' => ['Progressive Farmers and Household Union of America', 'Elaine Twelve'],
                'era' => '1910s',
                'in_custody' => false,
                'released' => true,
                'cases' => [$elaineCase],
            ];
        }

        // Kintpuash gets a custom (more detailed) entry as the named leader.
        $out[] = [
            'name' => 'Kintpuash',
            'aka' => 'Captain Jack',
            'first_name' => 'Kintpuash',
            'description' => 'Modoc tribal leader who led his band\'s armed resistance to forced removal from their California ancestral lands to the Klamath Reservation in Oregon. During peace negotiations at the Lava Beds on April 11, 1873, Kintpuash shot and killed Brigadier General Edward Canby — the only U.S. general killed by Native combatants. After a six-month war in which 53 Modoc fighters held off ~1,000 U.S. troops, Kintpuash and three others were convicted by military commission and hanged at Fort Klamath on October 3, 1873. His head was severed after death and sent to the Army Medical Museum in Washington, D.C.',
            'state' => 'Oregon',
            'race' => 'Indigenous',
            'gender' => 'Male',
            'death_date' => '1873-10-03',
            'ideologies' => ['Modoc sovereignty', 'Anti-colonial resistance'],
            'affiliation' => ['Modoc Nation'],
            'era' => '1870s',
            'in_custody' => false,
            'released' => false,
            'cases' => [
                [
                    'institution_name' => 'Fort Klamath',
                    'institution_state' => 'Oregon',
                    'charges' => 'Murder — for killing Brigadier General Edward Canby during peace negotiations, April 11, 1873.',
                    'arrest_date' => '1873-06-01',
                    'sentenced_date' => '1873-07-09',
                    'death_in_custody_date' => '1873-10-03',
                    'convicted' => 'Yes — by U.S. military commission.',
                    'sentence' => 'Death by hanging; executed October 3, 1873 at Fort Klamath.',
                ],
            ],
        ];

        foreach ($modocHanged as $name) {
            $out[] = [
                'name' => $name,
                'first_name' => preg_split('/\s+/', $name)[0],
                'description' => $modocSharedDescription.' Convicted alongside Kintpuash and hanged at Fort Klamath on October 3, 1873.',
                'state' => 'Oregon',
                'race' => 'Indigenous',
                'gender' => 'Male',
                'death_date' => '1873-10-03',
                'ideologies' => ['Modoc sovereignty', 'Anti-colonial resistance'],
                'affiliation' => ['Modoc Nation'],
                'era' => '1870s',
                'in_custody' => false,
                'released' => false,
                'cases' => [[
                    'institution_name' => 'Fort Klamath',
                    'institution_state' => 'Oregon',
                    'charges' => 'Murder — role in the killing of Gen. Edward Canby and Rev. Eleazar Thomas during peace negotiations, April 11, 1873.',
                    'arrest_date' => '1873-06-01',
                    'sentenced_date' => '1873-07-09',
                    'death_in_custody_date' => '1873-10-03',
                    'convicted' => 'Yes — by U.S. military commission.',
                    'sentence' => 'Death by hanging; executed at Fort Klamath.',
                ]],
            ];
        }

        foreach ($modocLife as $name) {
            $out[] = [
                'name' => $name,
                'first_name' => $name,
                'description' => $modocSharedDescription.' Sentenced to life imprisonment and held at Alcatraz federal military prison.',
                'state' => 'California',
                'race' => 'Indigenous',
                'gender' => 'Male',
                'ideologies' => ['Modoc sovereignty', 'Anti-colonial resistance'],
                'affiliation' => ['Modoc Nation'],
                'era' => '1870s',
                'in_custody' => false,
                'released' => false,
                'cases' => [[
                    'institution_name' => 'Alcatraz Federal Military Prison',
                    'institution_state' => 'California',
                    'charges' => 'Murder — role in the Lava Beds peace-talks killings, April 11, 1873.',
                    'sentenced_date' => '1873-07-09',
                    'convicted' => 'Yes — by U.S. military commission.',
                    'sentence' => 'Life imprisonment at Alcatraz.',
                ]],
            ];
        }

        $out[] = [
            'name' => 'Plenty Horses',
            'first_name' => 'Plenty',
            'last_name' => 'Horses',
            'description' => 'Sicangu Lakota young man who, nine days after the December 29, 1890 Wounded Knee massacre, shot and killed Lieutenant Edward Casey of the 22nd Infantry. Held for trial at Fort Meade and then in federal court in Sioux Falls. The court ultimately acquitted him after the judge ruled that a state of war existed between the Sioux Nation and the United States — meaning the killing was a combatant act, not murder.',
            'state' => 'South Dakota',
            'race' => 'Indigenous',
            'gender' => 'Male',
            'birthdate' => '1869-01-01',
            'death_date' => '1933-06-15',
            'ideologies' => ['Lakota sovereignty', 'Anti-colonial resistance'],
            'affiliation' => ['Sicangu Lakota Oyate'],
            'era' => '1890s',
            'in_custody' => false,
            'released' => true,
            'cases' => [
                [
                    'institution_name' => 'Fort Meade / Federal Court (Sioux Falls)',
                    'institution_state' => 'South Dakota',
                    'charges' => 'Murder of Lt. Edward Casey, January 7, 1891.',
                    'arrest_date' => '1891-01-08',
                    'convicted' => 'No — acquitted on grounds that a state of war existed.',
                    'sentence' => 'Acquitted; held approximately four months pretrial.',
                ],
            ],
        ];

        foreach ($harlem as $name) {
            $parts = preg_split('/\s+/', $name);
            $out[] = [
                'name' => $name,
                'first_name' => $parts[0],
                'last_name' => end($parts),
                'description' => $harlemSharedDescription,
                'state' => 'New York',
                'race' => 'Black',
                'gender' => 'Male',
                'ideologies' => ['Civil rights', 'Anti-police repression'],
                'affiliation' => ['Harlem Six'],
                'era' => '1960s',
                'in_custody' => false,
                'released' => $name !== 'Robert Rice',
                'cases' => [$harlemCase],
            ];
        }

        return $out;
    }
}
