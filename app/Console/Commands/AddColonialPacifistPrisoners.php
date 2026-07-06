<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Two named early-American Quaker conscientious objectors recorded in Peter
 * Brock's "Pacifism in the United States" (via Joseph Besse's collection of
 * Quaker sufferings and the Philadelphia Friend):
 *   - William Piersehouse — jailed four months for refusing to supply labor
 *     for military fortifications.
 *   - Moses Sleeper — a Maine Quaker court-martialed during the Revolution for
 *     refusing the militia and sentenced to forty-five lashes.
 *
 * Create-or-update by name; rebuilds each single case. Idempotent.
 */
class AddColonialPacifistPrisoners extends Command
{
    protected $signature = 'prisoners:add-colonial-pacifist-prisoners';

    protected $description = 'Add early-American Quaker conscientious objectors (Piersehouse, Sleeper)';

    public function handle(): int
    {
        $people = [
            [
                'name' => 'William Piersehouse', 'first' => 'William', 'last' => 'Piersehouse',
                'race' => 'White', 'era' => '1600s',
                'ideologies' => ['Pacifism', 'Conscientious objection'], 'affiliation' => ['Quaker'],
                'bio' => 'William Piersehouse was an early member of the Religious Society of Friends whose case is recorded in Joseph Besse\'s collection of the sufferings of Quakers. He was sentenced to four months\' "hard imprisonment, to the great impairing of his health," for conscientiously refusing to send his servants to work on military fortifications — one of the earliest documented American conscientious objections to supporting war.',
                'charges' => 'Refusing to supply labor for military fortifications, as a Quaker conscientious objector.',
                'convicted' => 'Yes.',
                'sentence' => 'Four months of hard imprisonment.',
            ],
            [
                'name' => 'Moses Sleeper', 'first' => 'Moses', 'last' => 'Sleeper',
                'race' => 'White', 'state' => 'Maine', 'era' => '1770s',
                'ideologies' => ['Pacifism', 'Conscientious objection'], 'affiliation' => ['Quaker'],
                'bio' => 'Moses Sleeper was a Quaker attender in Maine (then part of Massachusetts) during the American Revolution who refused to join the militia. He was arrested, imprisoned in a fort, and court-martialed, and was sentenced "to receive forty-five lashes on the naked back." The sentence was never carried out, and he was soon released. His experience was later printed in the Philadelphia Friend (1879).',
                'charges' => 'Refusing militia service as a Quaker conscientious objector during the American Revolution.',
                'convicted' => 'Yes — court-martialed by the military.',
                'sentence' => 'Sentenced to forty-five lashes on the bare back (never carried out); imprisoned in a fort and soon released.',
                'incarceration' => [1777],
            ],
        ];

        DB::transaction(function () use ($people) {
            foreach ($people as $p) {
                $prisoner = Prisoner::withUnderReview()->where('name', $p['name'])->first()
                    ?? new Prisoner(['name' => $p['name']]);

                $prisoner->fill([
                    'name' => $p['name'],
                    'first_name' => $p['first'],
                    'last_name' => $p['last'],
                    'gender' => 'Male',
                    'race' => $p['race'] ?? null,
                    'state' => $p['state'] ?? null,
                    'era' => $p['era'],
                    'ideologies' => $p['ideologies'],
                    'affiliation' => $p['affiliation'],
                    'description' => $p['bio'],
                    'in_custody' => false,
                    'released' => true,
                    'in_exile' => false,
                    'awaiting_trial' => false,
                ]);
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
                $case->save();

                $this->info(($prisoner->wasRecentlyCreated ? 'Added: ' : 'Set: ').$prisoner->name.' (slug: '.$prisoner->slug.')');
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
