<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds six Civil War conscientious objectors — pre-WWI war resisters who
 * refused to bear arms and were imprisoned, tortured, or condemned for it.
 *
 * Four are Southern Quakers conscripted into the Confederate army who refused to
 * serve, pay the exemption tax, or hire a substitute (Seth Laughlin, the Hockett
 * brothers, Tilghman Vestal — their stories preserved in Fernando Cartland's
 * 1895 "Southern Heroes"). Two are the Vermont Quakers drafted with Cyrus
 * Pringle in 1863 and paroled with him by President Lincoln (Lindley Macomber
 * and Peter Dakin). Complements Cyrus Pringle, already in the database.
 *
 * Dates are set with honest precision: exact where documented, year otherwise,
 * omitted where unclear. Idempotent: prisoner:add refuses duplicates by name and
 * the variant-name guard skips anyone already recorded.
 */
final class AddCivilWarConscientiousObjectors extends Command
{
    protected $signature = 'prisoners:add-civil-war-cos';

    protected $description = 'Add six Civil War conscientious objectors (Confederate-conscripted Quakers + Pringle\'s Vermont co-draftees)';

    public function handle(): int
    {
        $people = [
            [
                'payload' => [
                    'name' => 'Seth Laughlin',
                    'first_name' => 'Seth',
                    'last_name' => 'Laughlin',
                    'description' => "Seth W. Laughlin, a North Carolina Quaker, was conscripted into the Confederate army during the Civil War and refused, as a member of the Society of Friends, to bear arms or drill. According to Quaker accounts he was tortured to force his submission and finally court-martialed and sentenced to be shot; granted time before his execution, he prayed aloud for the forgiveness of his persecutors, and the sentence was not carried out. Worn down by the ordeal, he died soon afterward. His story became one of the best-known martyr accounts of the Southern Friends in wartime.",
                    'state' => 'North Carolina',
                    'gender' => 'Male',
                    'ideologies' => ['Quakerism', 'Pacifism'],
                    'era' => '1860s',
                    'released' => false,
                    'cases' => [[
                        'charges' => 'Conscripted into the Confederate army and refused, as a Quaker, to bear arms.',
                        'convicted' => 'Court-martialed and sentenced to be shot for refusing to serve',
                        'sentence' => 'Tortured and condemned to death; the execution was not carried out, but he died soon after his ordeal.',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1862, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Himelius Hockett',
                    'first_name' => 'Himelius',
                    'last_name' => 'Hockett',
                    'description' => "Himelius M. Hockett, a North Carolina Quaker, refused to serve after being conscripted into the Confederate army, and with his brother William endured repeated imprisonment, beatings, and forced marches toward the front for declining to bear arms. He would neither fight, pay the exemption tax the Confederacy levied on Friends, nor hire a substitute. He survived the war, and his ordeal was recorded in the Quaker history Southern Heroes.",
                    'state' => 'North Carolina',
                    'gender' => 'Male',
                    'ideologies' => ['Quakerism', 'Pacifism'],
                    'era' => '1860s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Conscripted into the Confederate army and refused, as a Quaker, to bear arms, pay the exemption tax, or hire a substitute.',
                        'convicted' => 'Held under military discipline, Civil War',
                        'sentence' => 'Repeatedly imprisoned, beaten, and marched toward the front for refusing to serve; survived the war.',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1863, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'William Hockett',
                    'first_name' => 'William',
                    'last_name' => 'Hockett',
                    'description' => "William B. Hockett, a North Carolina Quaker conscripted into the Confederate army with his brother Himelius, refused to bear arms and kept a journal of the imprisonment, abuse, and marches to the front that followed. His account is among the fullest first-person records of the Southern Friends who resisted conscription in the Civil War.",
                    'state' => 'North Carolina',
                    'gender' => 'Male',
                    'ideologies' => ['Quakerism', 'Pacifism'],
                    'era' => '1860s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Conscripted into the Confederate army and refused, as a Quaker, to bear arms.',
                        'convicted' => 'Held under military discipline, Civil War',
                        'sentence' => 'Imprisoned, abused, and marched toward the front for refusing to serve; kept a journal of the ordeal and survived the war.',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1863, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Tilghman Vestal',
                    'first_name' => 'Tilghman',
                    'last_name' => 'Vestal',
                    'description' => "Tilghman Ross Vestal, a young Tennessee Quaker, was conscripted into the Confederate army and refused to serve, and was held in military prisons — much of it in harsh conditions — for well over a year rather than take up arms. His long imprisonment made him one of the emblematic conscientious objectors among the Southern Friends.",
                    'state' => 'Tennessee',
                    'gender' => 'Male',
                    'ideologies' => ['Quakerism', 'Pacifism'],
                    'era' => '1860s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Conscripted into the Confederate army and refused, as a Quaker, to bear arms.',
                        'convicted' => 'Held under military discipline, Civil War',
                        'sentence' => 'Imprisoned in Confederate military prisons for well over a year for refusing to serve.',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1862, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Lindley Macomber',
                    'first_name' => 'Lindley',
                    'last_name' => 'Macomber',
                    'description' => "Lindley M. Macomber, a Vermont Quaker, was drafted in 1863 together with Cyrus Pringle and Peter Dakin and, like them, refused to serve, to pay the commutation fee, or to hire a substitute. Held under military discipline in camps in Vermont and Virginia, the three men were finally paroled by the personal order of President Lincoln in November 1863.",
                    'state' => 'Vermont',
                    'gender' => 'Male',
                    'ideologies' => ['Quakerism', 'Pacifism'],
                    'era' => '1860s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Drafted in 1863 and held under military discipline for refusing, as a Quaker, to serve, pay commutation, or furnish a substitute.',
                        'convicted' => 'Never tried — paroled by order of President Lincoln, November 1863',
                        'sentence' => 'Held under military discipline in Vermont and Virginia from mid-1863; paroled home with Cyrus Pringle by Lincoln\'s order.',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1863, 7, null], 'release_date' => [1863, 11, null]],
            ],
            [
                'payload' => [
                    'name' => 'Peter Dakin',
                    'first_name' => 'Peter',
                    'last_name' => 'Dakin',
                    'description' => "Peter Dakin, a Vermont Quaker, was drafted in 1863 alongside Cyrus Pringle and Lindley Macomber and joined them in refusing all military service, commutation, or substitution. After being held under military discipline in Vermont and Virginia, he was paroled with the others by order of President Lincoln in November 1863.",
                    'state' => 'Vermont',
                    'gender' => 'Male',
                    'ideologies' => ['Quakerism', 'Pacifism'],
                    'era' => '1860s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Drafted in 1863 and held under military discipline for refusing, as a Quaker, to serve, pay commutation, or furnish a substitute.',
                        'convicted' => 'Never tried — paroled by order of President Lincoln, November 1863',
                        'sentence' => 'Held under military discipline in Vermont and Virginia from mid-1863; paroled home with Cyrus Pringle by Lincoln\'s order.',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1863, 7, null], 'release_date' => [1863, 11, null]],
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

        $this->info("\nDone. Processed {$added} Civil War conscientious objector(s).");

        return self::SUCCESS;
    }
}
