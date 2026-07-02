<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds nine early imprisoned conscientious objectors — eight WWI-era
 * absolutists court-martialed and sent to Fort Leavenworth / Fort Douglas /
 * Alcatraz, plus Civil War Quaker draftee Cyrus Pringle. Complements the CO
 * records already in the database (the Hofer brothers, Jacob Wipf, Ben Salmon,
 * Philip Grosser, Ammon Hennacy, Roger Baldwin, Wally Nelson).
 *
 * Dates are set with honest precision: exact where documented, year/month
 * precision otherwise, and omitted entirely where the record is unclear (the
 * no-release-date guard then shows the duration as unknown rather than
 * inventing one).
 *
 * Idempotent: prisoner:add refuses duplicates by name; the backfill is safe to
 * re-run.
 */
final class AddEarlyConscientiousObjectors extends Command
{
    protected $signature = 'prisoners:add-early-cos';

    protected $description = 'Add nine early imprisoned conscientious objectors (WWI absolutists + Civil War Quaker Cyrus Pringle)';

    public function handle(): int
    {
        $people = [
            [
                'payload' => [
                    'name' => 'Evan Thomas',
                    'first_name' => 'Evan',
                    'last_name' => 'Thomas',
                    'description' => 'Evan Thomas — younger brother of Socialist Party leader Norman Thomas — was one of the best-known absolutist conscientious objectors of the First World War. A divinity student who refused all military service, he led hunger strikes at Fort Riley against the brutal treatment of fellow objectors, was court-martialed in 1918 and sentenced to life imprisonment (reduced to 25 years), and was held at Fort Leavenworth, where he was among the objectors manacled to their cell bars nine hours a day. The manacling scandal helped force the War Department to abolish the practice, and he was released in January 1919. He later became a physician and chaired the War Resisters League through the Second World War.',
                    'state' => 'New York',
                    'gender' => 'Male',
                    'ideologies' => ['Pacifism', 'Christian pacifism'],
                    'affiliation' => ['War Resisters League'],
                    'era' => '1910s',
                    'cases' => [[
                        'charges' => 'Court-martialed in 1918 for refusing military orders as an absolutist conscientious objector; sentenced to life imprisonment, reduced to 25 years.',
                        'convicted' => 'Convicted by court-martial, 1918',
                        'sentence' => 'Life imprisonment, reduced to 25 years at Fort Leavenworth; released in January 1919 after the manacling scandal and postwar clemency.',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1918, null, null], 'release_date' => [1919, 1, null]],
            ],
            [
                'payload' => [
                    'name' => 'Howard W. Moore',
                    'first_name' => 'Howard',
                    'last_name' => 'Moore',
                    'description' => 'Howard W. Moore, of Cherry Valley, New York, was an absolutist conscientious objector of the First World War who refused both combatant and noncombatant service. Court-martialed and sentenced to 25 years, he was held at Fort Leavenworth and Fort Douglas, Utah, enduring solitary confinement and manacling, and was among the very last World War I objectors freed, in November 1920 — two years after the Armistice. His memoir, Plowing My Own Furrow (1985), is one of the classic accounts of the WWI objectors. He lived to 103.',
                    'state' => 'New York',
                    'gender' => 'Male',
                    'ideologies' => ['Pacifism'],
                    'era' => '1910s',
                    'cases' => [[
                        'charges' => 'Court-martialed for refusing all military service as an absolutist conscientious objector.',
                        'convicted' => 'Convicted by court-martial, 1918',
                        'sentence' => '25 years; held at Fort Leavenworth and Fort Douglas, among the last WWI objectors released, November 1920.',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1918, null, null], 'release_date' => [1920, 11, null]],
            ],
            [
                'payload' => [
                    'name' => 'Carl Haessler',
                    'first_name' => 'Carl',
                    'last_name' => 'Haessler',
                    'description' => "Carl Haessler, a Rhodes Scholar and University of Illinois philosophy instructor, refused to serve in the First World War as a socialist \"political objector,\" telling his court-martial the war was \"a rich man's war.\" Court-martialed in 1918 and sentenced to 12 years at hard labor, he was held at Fort Leavenworth — where he helped lead the 1919 prisoners' general strike — and at Alcatraz, and was released in 1920. He went on to a long career as a labor journalist, running the Federated Press news service for three decades.",
                    'state' => 'Illinois',
                    'gender' => 'Male',
                    'ideologies' => ['Socialism', 'Pacifism'],
                    'era' => '1910s',
                    'cases' => [[
                        'charges' => 'Court-martialed in 1918 for refusing military service as a socialist political objector.',
                        'convicted' => 'Convicted by court-martial, 1918',
                        'sentence' => '12 years at hard labor; held at Fort Leavenworth and Alcatraz; released in 1920.',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1918, null, null], 'release_date' => [1920, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Harold Studley Gray',
                    'first_name' => 'Harold',
                    'last_name' => 'Gray',
                    'description' => "Harold Studley Gray, of Detroit, was serving with the YMCA in wartime England when he became convinced that all war was incompatible with Christianity. Returning home, he refused military service as an absolutist conscientious objector, was court-martialed at Camp Custer in 1918 and sentenced to 25 years, and was imprisoned at Fort Leavenworth, where he joined the objectors' work strike. Released in 1919, he told his story in Character \"Bad\": The Story of a Conscientious Objector (1934) and later founded the Saline Valley Farms cooperative in Michigan.",
                    'state' => 'Michigan',
                    'gender' => 'Male',
                    'ideologies' => ['Christian pacifism'],
                    'era' => '1910s',
                    'cases' => [[
                        'charges' => 'Court-martialed at Camp Custer in 1918 for refusing military service as an absolutist Christian objector.',
                        'convicted' => 'Convicted by court-martial, 1918',
                        'sentence' => '25 years; imprisoned at Fort Leavenworth; released in 1919.',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1918, null, null], 'release_date' => [1919, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Julius Eichel',
                    'first_name' => 'Julius',
                    'last_name' => 'Eichel',
                    'description' => 'Julius Eichel, a Jewish absolutist conscientious objector from Brooklyn, refused all military service in the First World War, was court-martialed and sentenced to life (later reduced), and was imprisoned at Fort Leavenworth and Fort Douglas until 1920 — refusing even to accept the conditions of early release offered to objectors. A generation later he took the same stand against Second World War conscription and was jailed again, making him one of the rare Americans imprisoned for conscience in both world wars. He edited The Absolutist, a newsletter of the World War II objector community.',
                    'state' => 'New York',
                    'gender' => 'Male',
                    'ideologies' => ['Pacifism', 'Socialism'],
                    'era' => '1910s',
                    'cases' => [
                        [
                            'charges' => 'Court-martialed in the First World War for refusing all military service as an absolutist conscientious objector.',
                            'convicted' => 'Convicted by court-martial, 1918',
                            'sentence' => 'Life, later reduced; imprisoned at Fort Leavenworth and Fort Douglas; released in 1920.',
                        ],
                        [
                            'charges' => 'Refused to comply with Second World War conscription and was imprisoned again for his resistance.',
                            'convicted' => 'Convicted, World War II era',
                            'sentence' => 'Jailed again during the Second World War; term details not well documented.',
                        ],
                    ],
                ],
                'dates' => ['incarceration_date' => [1918, null, null], 'release_date' => [1920, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Maurice Hess',
                    'first_name' => 'Maurice',
                    'last_name' => 'Hess',
                    'description' => 'Maurice Hess, a young Dunkard (Old Order German Baptist Brethren) schoolteacher from Pennsylvania, refused military service in the First World War on religious grounds. His statement to his 1918 court-martial — "I do not believe that I am seeking martyrdom... but I cannot forsake my Lord" — became one of the most quoted testimonies of the WWI objectors. Sentenced to 25 years, he was imprisoned at Fort Leavenworth and released after the war. He later taught classics for decades at McPherson College in Kansas.',
                    'state' => 'Pennsylvania',
                    'gender' => 'Male',
                    'ideologies' => ['Christian pacifism', 'Anabaptism'],
                    'era' => '1910s',
                    'cases' => [[
                        'charges' => 'Court-martialed in 1918 for refusing military service as a religious conscientious objector.',
                        'convicted' => 'Convicted by court-martial, 1918',
                        'sentence' => '25 years; imprisoned at Fort Leavenworth; released after the war (exact date not documented).',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1918, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Erling Lunde',
                    'first_name' => 'Erling',
                    'last_name' => 'Lunde',
                    'description' => "Erling Lunde, a young Chicago engineer, refused military service in the First World War as an absolutist conscientious objector and was court-martialed in 1918 and imprisoned at Fort Leavenworth. His father, reformer Theodore Lunde, publicized his son's case and the treatment of the wartime objectors in the pamphlet The Case of Erling Lunde, part of the public pressure that eventually forced improvements in the objectors' treatment.",
                    'state' => 'Illinois',
                    'gender' => 'Male',
                    'ideologies' => ['Pacifism'],
                    'era' => '1910s',
                    'cases' => [[
                        'charges' => 'Court-martialed in 1918 for refusing military service as an absolutist conscientious objector.',
                        'convicted' => 'Convicted by court-martial, 1918',
                        'sentence' => 'Imprisoned at Fort Leavenworth; released after the war (term details not well documented).',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1918, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Roderick Seidenberg',
                    'first_name' => 'Roderick',
                    'last_name' => 'Seidenberg',
                    'description' => 'Roderick Seidenberg, a New York architect, refused military service in the First World War as an absolutist conscientious objector. Court-martialed and imprisoned at Fort Leavenworth and Fort Douglas, Utah, he served roughly two and a half years — among the objectors held long after the Armistice — and was released in 1920. His prison essay "I Refuse to Serve" (American Mercury, 1932) remains a classic account; he later gained note as an architect and as the author of Posthistoric Man.',
                    'state' => 'New York',
                    'gender' => 'Male',
                    'ideologies' => ['Pacifism'],
                    'era' => '1910s',
                    'cases' => [[
                        'charges' => 'Court-martialed for refusing all military service as an absolutist conscientious objector.',
                        'convicted' => 'Convicted by court-martial, 1918',
                        'sentence' => 'Imprisoned at Fort Leavenworth and Fort Douglas for roughly two and a half years; released in 1920.',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1918, null, null], 'release_date' => [1920, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Cyrus Pringle',
                    'first_name' => 'Cyrus',
                    'last_name' => 'Pringle',
                    'description' => "Cyrus Pringle, a young Quaker farmer from Charlotte, Vermont, was drafted into the Union Army in July 1863 and refused on religious grounds to serve, pay the commutation fee, or accept a substitute. Held in camps in Vermont and Virginia, he refused all military duty and at one point was staked spread-eagled to the ground for refusing to carry a rifle. President Lincoln personally ordered the Quaker objectors paroled, and Pringle was released in November 1863. His diary, published as The Record of a Quaker Conscience, is the classic American account of Civil War conscientious objection; he later became one of America's most celebrated botanists.",
                    'state' => 'Vermont',
                    'gender' => 'Male',
                    'ideologies' => ['Pacifism', 'Quakerism'],
                    'era' => '1800s',
                    'cases' => [[
                        'charges' => 'Drafted into the Union Army in July 1863 and held under military discipline for refusing, as a Quaker, to serve, pay commutation, or furnish a substitute.',
                        'convicted' => 'Never tried — paroled by order of President Lincoln, November 1863',
                        'sentence' => 'Held under military discipline from July to November 1863, including being staked to the ground for refusing to bear arms; paroled home by Lincoln\'s order.',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1863, 7, null], 'release_date' => [1863, 11, null]],
            ],
        ];

        $added = 0;
        foreach ($people as $person) {
            $payload = $person['payload'];
            $payload['in_custody'] = false;
            $payload['released'] = true;

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

        $this->info("\nDone. Processed {$added} early conscientious objector(s).");

        return self::SUCCESS;
    }
}
