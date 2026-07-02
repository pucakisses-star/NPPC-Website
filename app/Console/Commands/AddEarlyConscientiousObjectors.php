<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds six early imprisoned conscientious objectors — five WWI-era absolutists
 * court-martialed and sent to Fort Leavenworth / Fort Douglas / Alcatraz, plus
 * Civil War Quaker draftee Cyrus Pringle. Complements the CO records already
 * in the database (the Hofer brothers, Jacob Wipf, Ben Salmon, Philip Grosser,
 * Ammon Hennacy, Roger Baldwin, Wally Nelson, Evan Welling Thomas, Howard
 * Wilbur Moore, Harold Studley Gray).
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

    protected $description = 'Add six early imprisoned conscientious objectors (WWI absolutists + Civil War Quaker Cyrus Pringle)';

    public function handle(): int
    {
        $people = [
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

            // Guard against variant-name duplicates (e.g. an existing "Evan
            // Welling Thomas" when the payload says "Evan Thomas"): skip anyone
            // whose first AND last name both already appear in a record.
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

        $this->info("\nDone. Processed {$added} early conscientious objector(s).");

        return self::SUCCESS;
    }
}
