<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds seven Southern Unionists imprisoned — and in most cases executed — by the
 * Confederacy for their loyalty to the Union, complementing the Confederate-held
 * Unionists already recorded (John Minor Botts, William G. "Parson" Brownlow).
 *
 * Five are East Tennessee bridge burners: Unionists who, in the coordinated
 * uprising of 8–9 November 1861, burned railroad bridges to aid an expected
 * Union advance. Under a War Department order that bridge burners be hanged,
 * Henry Fry and Jacob Harmon were hanged at Greeneville and Christopher Haun,
 * Henry Harmon and Jacob Hinshaw at Knoxville in late 1861. Two are Unionist
 * organizers who were imprisoned but survived: Captain David Fry, who led the
 * Lick Creek bridge burning, and Alexander H. Jones of North Carolina, founder
 * of the secret Unionist order the Heroes of America.
 *
 * Dates are set with honest precision. The executed men are recorded as having
 * died in custody (released = false). Idempotent: prisoner:add refuses
 * duplicates by name and the variant-name guard skips anyone already recorded.
 */
final class AddConfederateHeldUnionists extends Command
{
    protected $signature = 'prisoners:add-confederate-held-unionists';

    protected $description = 'Add seven Southern Unionists imprisoned or executed by the Confederacy (East Tennessee bridge burners + organizers)';

    public function handle(): int
    {
        $people = [
            [
                'payload' => [
                    'name' => 'Henry Fry',
                    'first_name' => 'Henry',
                    'last_name' => 'Fry',
                    'description' => "Henry Fry was an East Tennessee Unionist who took part in the coordinated bridge-burning uprising of November 1861, when loyalists across the region set fire to Confederate railroad bridges to aid an expected Union advance. Captured by Confederate authorities, he was tried under the War Department's order that bridge burners be hanged, and was executed at Greeneville, Tennessee, on 30 November 1861 — his body left hanging beside the railroad as a warning.",
                    'state' => 'Tennessee',
                    'gender' => 'Male',
                    'death_date' => '1861-11-30',
                    'ideologies' => ['Unionism'],
                    'era' => '1860s',
                    'released' => false,
                    'cases' => [[
                        'charges' => 'Took part in the East Tennessee bridge-burning uprising of November 1861 against the Confederacy.',
                        'convicted' => 'Tried by Confederate military authority and sentenced to hang, 1861',
                        'sentence' => 'Hanged at Greeneville, Tennessee, on 30 November 1861.',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1861, 11, null], 'death_in_custody_date' => [1861, 11, 30]],
            ],
            [
                'payload' => [
                    'name' => 'Jacob Harmon',
                    'first_name' => 'Jacob',
                    'last_name' => 'Harmon',
                    'description' => "Jacob M. Harmon was an East Tennessee Unionist who, with his sons, joined the November 1861 bridge-burning uprising against the Confederacy. Captured and tried under the order to hang bridge burners, he was executed at Greeneville, Tennessee, on 30 November 1861. His son Henry was hanged soon afterward at Knoxville.",
                    'state' => 'Tennessee',
                    'gender' => 'Male',
                    'death_date' => '1861-11-30',
                    'ideologies' => ['Unionism'],
                    'era' => '1860s',
                    'released' => false,
                    'cases' => [[
                        'charges' => 'Took part in the East Tennessee bridge-burning uprising of November 1861 against the Confederacy.',
                        'convicted' => 'Tried by Confederate military authority and sentenced to hang, 1861',
                        'sentence' => 'Hanged at Greeneville, Tennessee, on 30 November 1861.',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1861, 11, null], 'death_in_custody_date' => [1861, 11, 30]],
            ],
            [
                'payload' => [
                    'name' => 'Christopher Haun',
                    'first_name' => 'Christopher',
                    'last_name' => 'Haun',
                    'description' => "Christopher Alexander Haun, a well-known Greene County potter, was among the East Tennessee Unionists who burned Confederate railroad bridges in the November 1861 uprising. Captured and condemned under the order to hang bridge burners, he was executed at Knoxville, Tennessee, on 11 December 1861.",
                    'state' => 'Tennessee',
                    'gender' => 'Male',
                    'death_date' => '1861-12-11',
                    'ideologies' => ['Unionism'],
                    'era' => '1860s',
                    'released' => false,
                    'cases' => [[
                        'charges' => 'Took part in the East Tennessee bridge-burning uprising of November 1861 against the Confederacy.',
                        'convicted' => 'Tried by Confederate military authority and sentenced to hang, 1861',
                        'sentence' => 'Hanged at Knoxville, Tennessee, on 11 December 1861.',
                        'institution_name' => 'Knox County Jail',
                        'institution_city' => 'Knoxville',
                        'institution_state' => 'Tennessee',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1861, 11, null], 'death_in_custody_date' => [1861, 12, 11]],
            ],
            [
                'payload' => [
                    'name' => 'Henry Harmon',
                    'first_name' => 'Henry',
                    'last_name' => 'Harmon',
                    'description' => "Henry Harmon, a son of Jacob M. Harmon, joined his father and other East Tennessee Unionists in the November 1861 bridge-burning uprising against the Confederacy. Captured and condemned under the order to hang bridge burners, he was executed at Knoxville, Tennessee, in December 1861, shortly after his father was hanged at Greeneville.",
                    'state' => 'Tennessee',
                    'gender' => 'Male',
                    'ideologies' => ['Unionism'],
                    'era' => '1860s',
                    'released' => false,
                    'cases' => [[
                        'charges' => 'Took part in the East Tennessee bridge-burning uprising of November 1861 against the Confederacy.',
                        'convicted' => 'Tried by Confederate military authority and sentenced to hang, 1861',
                        'sentence' => 'Hanged at Knoxville, Tennessee, in December 1861.',
                        'institution_name' => 'Knox County Jail',
                        'institution_city' => 'Knoxville',
                        'institution_state' => 'Tennessee',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1861, 11, null], 'death_in_custody_date' => [1861, 12, null]],
            ],
            [
                'payload' => [
                    'name' => 'Jacob Hinshaw',
                    'first_name' => 'Jacob',
                    'last_name' => 'Hinshaw',
                    'description' => "Jacob Hinshaw was an East Tennessee Unionist who took part in the November 1861 uprising that burned Confederate railroad bridges across the region. Captured and condemned under the War Department's order to hang bridge burners, he was executed at Knoxville, Tennessee, in December 1861.",
                    'state' => 'Tennessee',
                    'gender' => 'Male',
                    'ideologies' => ['Unionism'],
                    'era' => '1860s',
                    'released' => false,
                    'cases' => [[
                        'charges' => 'Took part in the East Tennessee bridge-burning uprising of November 1861 against the Confederacy.',
                        'convicted' => 'Tried by Confederate military authority and sentenced to hang, 1861',
                        'sentence' => 'Hanged at Knoxville, Tennessee, in December 1861.',
                        'institution_name' => 'Knox County Jail',
                        'institution_city' => 'Knoxville',
                        'institution_state' => 'Tennessee',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1861, 11, null], 'death_in_custody_date' => [1861, 12, null]],
            ],
            [
                'payload' => [
                    'name' => 'David Fry',
                    'first_name' => 'David',
                    'last_name' => 'Fry',
                    'description' => "Captain David Fry, an East Tennessee Unionist, led the party that burned the Lick Creek railroad bridge in the November 1861 uprising and then took to the mountains to organize loyalists for the Union army. Captured by the Confederacy in 1862, he was held in a series of Confederate prisons under threat of execution before escaping. He survived the war.",
                    'state' => 'Tennessee',
                    'gender' => 'Male',
                    'ideologies' => ['Unionism'],
                    'era' => '1860s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Led the Lick Creek bridge burning of November 1861 and organized East Tennessee Unionists against the Confederacy.',
                        'convicted' => 'Held by Confederate military authority under threat of execution, 1862',
                        'sentence' => 'Imprisoned in several Confederate prisons; escaped and survived the war.',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1862, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Alexander H. Jones',
                    'first_name' => 'Alexander',
                    'last_name' => 'Jones',
                    'description' => "Alexander H. Jones, a western North Carolina newspaperman and outspoken Unionist, founded and led the Heroes of America, a secret order that helped loyalists resist the Confederacy and reach Union lines. Captured by Confederate authorities in 1863 while trying to make his way north, he was imprisoned and held until he escaped in 1864. After the war he was elected to Congress.",
                    'state' => 'North Carolina',
                    'gender' => 'Male',
                    'ideologies' => ['Unionism'],
                    'affiliation' => ['Heroes of America'],
                    'era' => '1860s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Organized the Unionist Heroes of America and aided loyalists against the Confederacy.',
                        'convicted' => 'Held as a Unionist by Confederate authority, 1863–1864',
                        'sentence' => 'Imprisoned by the Confederacy from 1863 until his escape in 1864.',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1863, null, null], 'release_date' => [1864, null, null]],
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

        $this->info("\nDone. Processed {$added} Confederate-held Unionist(s).");

        return self::SUCCESS;
    }
}
