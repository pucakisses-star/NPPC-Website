<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds soldiers of the all-Black 24th Infantry Regiment court-martialed after
 * the Houston riot of 1917 (the Camp Logan mutiny) — the largest court-martial
 * in U.S. history, in which 118 men were tried, 19 executed, and 63 sentenced
 * to life. In 2023 the U.S. Army set aside all 110 convictions and honorably
 * discharged the soldiers, recognizing that the trials were unjust.
 *
 * This adds the thirteen soldiers hanged at dawn on 11 December 1917 after the
 * first ("Nesbit") court-martial — carried out in secret within a day of
 * sentencing and without the War Department review that would have allowed an
 * appeal — plus Private William D. Boone, the last of the nineteen executed
 * (24 September 1918), and two of the life-sentenced men whose imprisonment is
 * individually documented, LeRoy Pinkett and Roy Tyler. The five other 1918
 * executions and the remaining life-sentenced men are not added individually
 * here, pending reliable per-name sourcing.
 *
 * Executed men are recorded as died in custody (released = false). Idempotent:
 * prisoner:add refuses duplicates by name and the variant-name guard skips
 * anyone already recorded.
 */
final class AddHoustonRiotSoldiers extends Command
{
    protected $signature = 'prisoners:add-houston-riot-soldiers';

    protected $description = 'Add 24th Infantry soldiers executed or imprisoned after the 1917 Houston riot (Camp Logan mutiny)';

    private const CONTEXT = "The Houston riot of 1917 (the Camp Logan mutiny) erupted on 23 August 1917 after Houston police, enforcing Jim Crow, beat and arrested soldiers of the all-Black 24th Infantry Regiment. About 150 soldiers marched on the city; the night left some twenty people dead. In the largest court-martial in U.S. history, 118 men were tried, 19 executed, and 63 sentenced to life imprisonment. In 2023 the U.S. Army set aside all 110 convictions and honorably discharged the soldiers.";

    public function handle(): int
    {
        // The thirteen hanged at Camp Travis at dawn on 11 December 1917 (the
        // "Nesbit case"): one sergeant, four corporals, and eight privates.
        $hangedDec1917 = [
            ['Sergeant', 'William C. Nesbit', 'William', 'Nesbit'],
            ['Corporal', 'Larnon J. Brown', 'Larnon', 'Brown'],
            ['Corporal', 'James Wheatley', 'James', 'Wheatley'],
            ['Corporal', 'Jesse Moore', 'Jesse', 'Moore'],
            ['Corporal', 'Charles W. Baltimore', 'Charles', 'Baltimore'],
            ['Private', 'William Breckenridge', 'William', 'Breckenridge'],
            ['Private', 'Thomas C. Hawkins', 'Thomas', 'Hawkins'],
            ['Private', 'Carlos Snodgrass', 'Carlos', 'Snodgrass'],
            ['Private', 'Ira B. Davis', 'Ira', 'Davis'],
            ['Private', 'James Divins', 'James', 'Divins'],
            ['Private', 'Frank Johnson', 'Frank', 'Johnson'],
            ['Private', 'Rosley W. Young', 'Rosley', 'Young'],
            ['Private', 'Pat McWhorter', 'Pat', 'McWhorter'],
        ];

        $people = [];

        foreach ($hangedDec1917 as [$rank, $name, $first, $last]) {
            $people[] = [
                'payload' => [
                    'name' => $name,
                    'first_name' => $first,
                    'last_name' => $last,
                    'aka' => $rank,
                    'description' => "{$rank} {$name} was one of the thirteen soldiers of the all-Black 24th Infantry Regiment hanged at Camp Travis, near San Antonio, at dawn on 11 December 1917, after the first Houston-riot court-martial (the \"Nesbit case\"). The executions were carried out in secret, within a day of sentencing and without the War Department review that would normally have allowed an appeal. ".self::CONTEXT,
                    'state' => 'Texas',
                    'gender' => 'Male',
                    'race' => 'Black',
                    'death_date' => '1917-12-11',
                    'affiliation' => ['24th Infantry Regiment'],
                    'era' => '1910s',
                    'released' => false,
                    'cases' => [[
                        'charges' => 'Court-martialed for the 23 August 1917 Houston riot (Camp Logan mutiny) of the 24th Infantry Regiment.',
                        'convicted' => 'Convicted by court-martial, 1917; conviction set aside by the U.S. Army in 2023',
                        'sentence' => 'Hanged at Camp Travis, near Fort Sam Houston, on 11 December 1917.',
                        'institution_name' => 'Fort Sam Houston',
                        'institution_city' => 'San Antonio',
                        'institution_state' => 'Texas',
                    ]],
                ],
                'dates' => ['death_in_custody_date' => [1917, 12, 11]],
            ];
        }

        // The last of the nineteen executed.
        $people[] = [
            'payload' => [
                'name' => 'William D. Boone',
                'first_name' => 'William',
                'last_name' => 'Boone',
                'aka' => 'Private',
                'description' => "Private William D. Boone of the 24th Infantry Regiment was the last of the nineteen soldiers executed for the Houston riot, hanged at Fort Sam Houston on 24 September 1918 after the later courts-martial. ".self::CONTEXT,
                'state' => 'Texas',
                'gender' => 'Male',
                'race' => 'Black',
                'death_date' => '1918-09-24',
                'affiliation' => ['24th Infantry Regiment'],
                'era' => '1910s',
                'released' => false,
                'cases' => [[
                    'charges' => 'Court-martialed for the 23 August 1917 Houston riot (Camp Logan mutiny) of the 24th Infantry Regiment.',
                    'convicted' => 'Convicted by court-martial, 1918; conviction set aside by the U.S. Army in 2023',
                    'sentence' => 'Hanged at Fort Sam Houston on 24 September 1918.',
                    'institution_name' => 'Fort Sam Houston',
                    'institution_city' => 'San Antonio',
                    'institution_state' => 'Texas',
                ]],
            ],
            'dates' => ['death_in_custody_date' => [1918, 9, 24]],
        ];

        // Two life-sentenced soldiers whose imprisonment is individually documented.
        $people[] = [
            'payload' => [
                'name' => 'LeRoy Pinkett',
                'first_name' => 'LeRoy',
                'last_name' => 'Pinkett',
                'description' => "Private LeRoy Pinkett of the 24th Infantry Regiment was convicted of murder, mutiny, and assault in the first Houston-riot court-martial and sentenced to life imprisonment. Held at the Leavenworth federal penitentiary, he was released on parole in 1927. ".self::CONTEXT,
                'state' => 'Texas',
                'gender' => 'Male',
                'race' => 'Black',
                'affiliation' => ['24th Infantry Regiment'],
                'era' => '1910s',
                'released' => true,
                'cases' => [[
                    'charges' => 'Convicted of murder, mutiny, and assault in the first Houston-riot court-martial (the "Nesbit case").',
                    'convicted' => 'Convicted by court-martial, 1917; conviction set aside by the U.S. Army in 2023',
                    'sentence' => 'Life imprisonment; held at Leavenworth and released on parole in 1927.',
                    'institution_name' => 'United States Penitentiary, Leavenworth',
                    'institution_city' => 'Leavenworth',
                    'institution_state' => 'Kansas',
                ]],
            ],
            'dates' => ['incarceration_date' => [1917, null, null], 'release_date' => [1927, null, null]],
        ];

        $people[] = [
            'payload' => [
                'name' => 'Roy Tyler',
                'first_name' => 'Roy',
                'last_name' => 'Tyler',
                'description' => "Private Roy Tyler of the 24th Infantry Regiment was sentenced to life imprisonment for his alleged role in the 1917 Houston riot. Paroled on 14 December 1924, he went on to play in the Negro leagues. ".self::CONTEXT,
                'state' => 'Texas',
                'gender' => 'Male',
                'race' => 'Black',
                'affiliation' => ['24th Infantry Regiment'],
                'era' => '1910s',
                'released' => true,
                'cases' => [[
                    'charges' => 'Court-martialed for the 23 August 1917 Houston riot (Camp Logan mutiny) of the 24th Infantry Regiment.',
                    'convicted' => 'Convicted by court-martial; conviction set aside by the U.S. Army in 2023',
                    'sentence' => 'Life imprisonment; released on parole on 14 December 1924.',
                    'institution_name' => 'United States Penitentiary, Leavenworth',
                    'institution_city' => 'Leavenworth',
                    'institution_state' => 'Kansas',
                ]],
            ],
            'dates' => ['incarceration_date' => [1917, null, null], 'release_date' => [1924, 12, 14]],
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

            $case = $prisoner->cases()->first();
            if ($case && ! empty($person['dates'])) {
                foreach ($person['dates'] as $field => [$y, $m, $d]) {
                    $case->setPartialDate($field, $y, $m, $d);
                }
                $case->save();
            }
            $added++;
        }

        $this->info("\nDone. Processed {$added} Houston-riot soldier(s).");

        return self::SUCCESS;
    }
}
