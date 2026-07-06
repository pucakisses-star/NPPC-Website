<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Named Civil War conscientious objectors — Quakers conscripted into the Union
 * and Confederate armies who refused to bear arms and were tortured, imprisoned,
 * or (in two cases) died. Sourced to Cyrus Pringle's "The Record of a Quaker
 * Conscience," Fernando Cartland's "Southern Heroes" (1895), and the standard
 * accounts of Quaker suffering in the Confederacy.
 *
 * Create-or-update by name; rebuilds each single case. Idempotent.
 */
class AddCivilWarObjectors extends Command
{
    protected $signature = 'prisoners:add-civil-war-objectors';

    protected $description = 'Add named Civil War conscientious objectors (Pringle, Laughlin, the Hocketts, Vestal, etc.)';

    public function handle(): int
    {
        $vt = 'was one of three Vermont Quakers drafted into the Union Army on July 13, 1863, who refused both to bear arms and — as a compromise of principle — to pay the $300 commutation fee. Sent to the Army of the Potomac in Virginia, the men were pressured, threatened, and abused; at Culpeper on October 3, 1863, they were staked spread-eagle to the ground for hours. When Secretary of War Edwin Stanton learned of it he had "the three incorrigibles" brought to Washington, and on November 7, 1863 they were released at the personal wish of President Lincoln.';

        $people = [
            [
                'name' => 'Cyrus Pringle', 'first' => 'Cyrus', 'middle' => 'Guernsey', 'last' => 'Pringle',
                'race' => 'White', 'state' => 'Vermont', 'era' => '1860s',
                'birth' => [1838, 5, 6], 'death' => [1911, 5, 25],
                'bio' => 'Cyrus Guernsey Pringle (1838–1911), a Quaker from Charlotte, Vermont — later one of the great American botanists — '.$vt.' His diary, "The Record of a Quaker Conscience," became a classic account of conscientious objection.',
                'charges' => 'Refusing to bear arms as a Quaker conscientious objector after being drafted into the Union Army (1863).',
                'convicted' => 'Not convicted — coerced and tortured in the field to force his submission.',
                'sentence' => 'Staked spread-eagle to the ground at Culpeper, Virginia (October 3, 1863); released November 7, 1863 by order of President Lincoln.',
                'incarceration' => [1863, 7, 13], 'release' => [1863, 11, 7],
            ],
            [
                'name' => 'Peter Dakin', 'first' => 'Peter', 'last' => 'Dakin',
                'race' => 'White', 'state' => 'Vermont', 'era' => '1860s',
                'bio' => 'Peter Dakin, a Quaker from Ferrisburgh, Vermont, '.$vt,
                'charges' => 'Refusing to bear arms as a Quaker conscientious objector after being drafted into the Union Army (1863).',
                'convicted' => 'Not convicted — coerced and abused in the field.',
                'sentence' => 'Held with Cyrus Pringle and Lindley Macomber; released November 7, 1863 by order of President Lincoln.',
                'incarceration' => [1863, 7, 13], 'release' => [1863, 11, 7],
            ],
            [
                'name' => 'Lindley M. Macomber', 'first' => 'Lindley', 'middle' => 'M.', 'last' => 'Macomber',
                'race' => 'White', 'state' => 'Vermont', 'era' => '1860s',
                'bio' => 'Lindley M. Macomber, a Quaker from Grand Isle, Vermont, '.$vt,
                'charges' => 'Refusing to bear arms as a Quaker conscientious objector after being drafted into the Union Army (1863).',
                'convicted' => 'Not convicted — coerced and abused in the field.',
                'sentence' => 'Held with Cyrus Pringle and Peter Dakin; released November 7, 1863 by order of President Lincoln.',
                'incarceration' => [1863, 7, 13], 'release' => [1863, 11, 7],
            ],
            [
                'name' => 'Seth Laughlin', 'first' => 'Seth', 'middle' => 'W.', 'last' => 'Laughlin',
                'race' => 'White', 'state' => 'North Carolina', 'era' => '1860s',
                'death' => [1864],
                'bio' => 'Seth W. Laughlin was a North Carolina man who, having become convinced of Friends\' peace principles, joined the Marlboro Monthly Meeting in November 1863 and was conscripted into the Confederate army. Reporting to camp near Petersburg, Virginia, he refused to wear the uniform, bear arms, or follow orders. He was tortured — deprived of sleep, "bucked and gagged," and hung by his thumbs — then court-martialed for insubordination and sentenced to be shot. The firing squad refused to shoot him; he was imprisoned instead, fell ill from his treatment, and died. He is remembered for praying for his persecutors, and his story was recorded in Fernando Cartland\'s "Southern Heroes."',
                'charges' => 'Refusing to bear arms or follow orders as a Quaker conscientious objector conscripted into the Confederate army.',
                'convicted' => 'Yes — court-martialed for insubordination and sentenced to be shot; the firing squad refused, and he was imprisoned.',
                'sentence' => 'Tortured (bucked and gagged, hung by the thumbs); imprisoned after the firing squad refused to shoot him, he fell ill and died in custody.',
                'incarceration' => [1863], 'died' => [1864],
            ],
            [
                'name' => 'Tilghman Vestal', 'first' => 'Tilghman', 'last' => 'Vestal',
                'race' => 'White', 'state' => 'Tennessee', 'era' => '1860s',
                'bio' => 'Tilghman Vestal was a young Quaker who refused to pay the Confederate exemption tax and was drafted. When he would not serve, fellow soldiers pierced him with their bayonets; in November 1863 he was sent to Castle Thunder — the Richmond, Virginia tobacco warehouse converted into a prison for civilians, spies, and political prisoners.',
                'charges' => 'Refusing military service as a Quaker conscientious objector (Confederate conscription).',
                'convicted' => 'Imprisoned as a political prisoner.',
                'sentence' => 'Bayonet-tortured, then imprisoned at Castle Thunder in Richmond, Virginia (from November 1863).',
                'incarceration' => [1863, 11],
            ],
            [
                'name' => 'Himelius Hockett', 'first' => 'Himelius', 'middle' => 'Mendenhall', 'last' => 'Hockett',
                'race' => 'White', 'state' => 'North Carolina', 'era' => '1860s',
                'bio' => 'Himelius Mendenhall Hockett, a North Carolina Quaker, was conscripted into the Confederate army with his brother Jesse on April 4, 1862. Because they would not bear arms, they were tortured and imprisoned — at one point held in a jail at Kinston, North Carolina for four days and five nights without bread or water, while onlookers came to stare at them through the bars.',
                'charges' => 'Refusing to bear arms as a Quaker conscientious objector conscripted into the Confederate army (1862).',
                'convicted' => 'Imprisoned for his refusal.',
                'sentence' => 'Tortured and imprisoned at Kinston, North Carolina — four days and five nights without food or water.',
                'incarceration' => [1862, 4, 4],
            ],
            [
                'name' => 'Jesse Hockett', 'first' => 'Jesse', 'middle' => 'Davis', 'last' => 'Hockett',
                'race' => 'White', 'state' => 'North Carolina', 'era' => '1860s',
                'bio' => 'Jesse Davis Hockett, a North Carolina Quaker, was conscripted into the Confederate army with his brother Himelius on April 4, 1862. Refusing to bear arms, the brothers were tortured and imprisoned, including four days and five nights without bread or water in a jail at Kinston, North Carolina.',
                'charges' => 'Refusing to bear arms as a Quaker conscientious objector conscripted into the Confederate army (1862).',
                'convicted' => 'Imprisoned for his refusal.',
                'sentence' => 'Tortured and imprisoned at Kinston, North Carolina with his brother.',
                'incarceration' => [1862, 4, 4],
            ],
        ];

        DB::transaction(function () use ($people) {
            foreach ($people as $p) {
                $prisoner = Prisoner::withUnderReview()->where('name', $p['name'])->first()
                    ?? new Prisoner(['name' => $p['name']]);
                $died = ! empty($p['died']);

                $prisoner->fill([
                    'name' => $p['name'],
                    'first_name' => $p['first'],
                    'middle_name' => $p['middle'] ?? null,
                    'last_name' => $p['last'],
                    'gender' => 'Male',
                    'race' => $p['race'] ?? null,
                    'state' => $p['state'] ?? null,
                    'era' => $p['era'],
                    'ideologies' => ['Pacifism', 'Conscientious objection'],
                    'affiliation' => ['Quaker'],
                    'description' => $p['bio'],
                    'in_custody' => false,
                    'released' => ! $died,
                    'in_exile' => false,
                    'awaiting_trial' => false,
                ]);
                if (! empty($p['birth'])) {
                    $prisoner->setPartialDate('birthdate', ...$p['birth']);
                }
                if (! empty($p['death'])) {
                    $prisoner->setPartialDate('death_date', ...$p['death']);
                }
                $prisoner->save();

                $prisoner->cases()->delete();
                $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
                $case->fill([
                    'prisoner_id' => $prisoner->id,
                    'charges' => $p['charges'],
                    'convicted' => $p['convicted'],
                    'sentence' => $p['sentence'],
                ]);
                if (! empty($p['incarceration'])) {
                    $case->setPartialDate('incarceration_date', ...$p['incarceration']);
                }
                if (! empty($p['release'])) {
                    $case->setPartialDate('release_date', ...$p['release']);
                }
                if ($died) {
                    $case->setPartialDate('death_in_custody_date', ...$p['died']);
                }
                $case->save();

                $this->info(($prisoner->wasRecentlyCreated ? 'Added: ' : 'Set: ').$prisoner->name.' (slug: '.$prisoner->slug.')');
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
