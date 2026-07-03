<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * A second batch of people held by the Confederacy at Richmond for their loyalty
 * to the Union — complementing the Confederate-held Unionists already recorded
 * (John Minor Botts, Parson Brownlow, Franklin Stearns, and the East Tennessee
 * bridge burners).
 *
 * Two are Richmond political prisoners: Burnham Wardwell, a Union-sympathizing
 * ice dealer held at Castle Thunder and then banished, and Thomas A. R. Nelson,
 * the East Tennessee Unionist congressman-elect arrested on his way to take his
 * seat. Three are Union agents the Confederacy imprisoned at Richmond — Timothy
 * Webster and Spencer Kellogg Brown, both hanged, and Pryce Lewis, condemned but
 * reprieved and eventually released.
 *
 * Dates are set with honest precision; the two executed men are recorded as
 * died in custody (released = false). Idempotent: prisoner:add refuses
 * duplicates by name and the variant-name guard skips anyone already recorded.
 */
final class AddConfederateHeldUnionists2 extends Command
{
    protected $signature = 'prisoners:add-confederate-held-unionists-2';

    protected $description = 'Add five more people held by the Confederacy at Richmond for Union loyalty (Richmond prisoners + executed agents)';

    public function handle(): int
    {
        $people = [
            [
                'payload' => [
                    'name' => 'Burnham Wardwell',
                    'first_name' => 'Burnham',
                    'last_name' => 'Wardwell',
                    'description' => "Burnham Wardwell, a Maine-born ice dealer living in Richmond, was an outspoken Union sympathizer who quietly aided Union prisoners of war in the Confederate capital. Arrested for his loyalties, he was held in the Castle Thunder military prison and was ultimately banished through the lines to the North. He returned to Richmond after the war and remained active in Reconstruction-era Unionist politics.",
                    'state' => 'Virginia',
                    'gender' => 'Male',
                    'ideologies' => ['Unionism'],
                    'era' => '1860s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Arrested as a Union sympathizer in Richmond for aiding Union prisoners of war.',
                        'convicted' => 'Held by Confederate authority, 1862–1863',
                        'sentence' => 'Imprisoned at Castle Thunder and then banished through the lines to the North.',
                        'institution_name' => 'Castle Thunder',
                        'institution_city' => 'Richmond',
                        'institution_state' => 'Virginia',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1862, null, null], 'release_date' => [1863, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Thomas A. R. Nelson',
                    'first_name' => 'Thomas',
                    'last_name' => 'Nelson',
                    'description' => "Thomas Amis Rogers Nelson, a leading East Tennessee Unionist elected to the United States Congress in 1859, was arrested by Confederate cavalry in August 1861 as he tried to make his way north to take his seat. Taken to Richmond, he was released only after publishing an address agreeing to submit to the Confederate government — a pledge he gave under duress to protect himself and the Unionists of East Tennessee. He later served on the defense team at Andrew Johnson's impeachment trial.",
                    'state' => 'Tennessee',
                    'gender' => 'Male',
                    'ideologies' => ['Unionism'],
                    'era' => '1860s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Arrested by Confederate forces as a Unionist while traveling to take his seat in the U.S. Congress.',
                        'convicted' => 'Detained by Confederate authority, August 1861',
                        'sentence' => 'Held at Richmond and released after pledging, under duress, to submit to the Confederate government.',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1861, 8, null], 'release_date' => [1861, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Timothy Webster',
                    'first_name' => 'Timothy',
                    'last_name' => 'Webster',
                    'description' => "Timothy Webster, an English-born operative of Pinkerton's detective agency, was one of the Union's most effective secret agents, moving through the South disguised as a secessionist courier. Betrayed after two fellow agents sent to check on him were recognized in Richmond, he was tried by the Confederacy as a spy and hanged there on 29 April 1862 — the first execution of a spy by either side in the Civil War.",
                    'gender' => 'Male',
                    'death_date' => '1862-04-29',
                    'ideologies' => ['Unionism'],
                    'affiliation' => ['Pinkerton National Detective Agency'],
                    'era' => '1860s',
                    'released' => false,
                    'cases' => [[
                        'charges' => 'Tried by the Confederacy as a Union spy operating in Richmond.',
                        'convicted' => 'Convicted of espionage by Confederate authority, 1862',
                        'sentence' => 'Hanged at Richmond on 29 April 1862.',
                        'institution_name' => 'Castle Godwin',
                        'institution_city' => 'Richmond',
                        'institution_state' => 'Virginia',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1862, null, null], 'death_in_custody_date' => [1862, 4, 29]],
            ],
            [
                'payload' => [
                    'name' => 'Spencer Kellogg Brown',
                    'first_name' => 'Spencer',
                    'last_name' => 'Brown',
                    'description' => "Spencer Kellogg Brown, raised in the free-state struggle in Kansas, served the Union as a scout and agent before being captured and charged with spying. Held at Richmond, he was condemned by the Confederacy and hanged there on 25 September 1863. A memoir drawn from his prison letters was published after his death.",
                    'state' => 'Kansas',
                    'gender' => 'Male',
                    'death_date' => '1863-09-25',
                    'ideologies' => ['Unionism', 'Abolitionism'],
                    'era' => '1860s',
                    'released' => false,
                    'cases' => [[
                        'charges' => 'Charged by the Confederacy with spying for the Union.',
                        'convicted' => 'Convicted of espionage by Confederate authority, 1863',
                        'sentence' => 'Hanged at Richmond on 25 September 1863.',
                        'institution_name' => 'Castle Thunder',
                        'institution_city' => 'Richmond',
                        'institution_state' => 'Virginia',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1863, null, null], 'death_in_custody_date' => [1863, 9, 25]],
            ],
            [
                'payload' => [
                    'name' => 'Pryce Lewis',
                    'first_name' => 'Pryce',
                    'last_name' => 'Lewis',
                    'description' => "Pryce Lewis, a Welsh-born Pinkerton operative, was sent into Richmond in 1862 to check on the ailing agent Timothy Webster and was recognized and arrested. Tried by the Confederacy and condemned to death, he was reprieved and held in Richmond's prisons for many months before being released through the lines. He survived the war and later wrote an account of his captivity.",
                    'gender' => 'Male',
                    'ideologies' => ['Unionism'],
                    'affiliation' => ['Pinkerton National Detective Agency'],
                    'era' => '1860s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Arrested and tried by the Confederacy as a Union agent while on a mission in Richmond.',
                        'convicted' => 'Condemned to death by Confederate authority, then reprieved, 1862',
                        'sentence' => "Held in Richmond's prisons for many months before being released through the lines.",
                        'institution_name' => 'Castle Godwin',
                        'institution_city' => 'Richmond',
                        'institution_state' => 'Virginia',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1862, 3, null], 'release_date' => [1863, null, null]],
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

        $this->info("\nDone. Processed {$added} Confederate-held Unionist(s)/agent(s).");

        return self::SUCCESS;
    }
}
