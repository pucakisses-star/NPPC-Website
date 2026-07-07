<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The rest of the named "Presidio 27" defendants — the stockade prisoners
 * charged with mutiny for the October 14, 1968 sit-down at the Presidio of San
 * Francisco. The four leaders (Mather, Pawlowski, Rowland, Blake) and Richard
 * Bunch are added by prisoners:add-presidio-27; this adds the other 18 who can
 * be identified by name from contemporary press and the case histories. (About
 * five of the 27 are not named in accessible sources.)
 *
 * Create-or-update by name; rebuilds each single case. Idempotent.
 */
final class AddPresidio27Roster extends Command
{
    protected $signature = 'prisoners:add-presidio-27-roster';

    protected $description = 'Add the remaining named Presidio 27 mutiny defendants (Sood, Reidel, Osczepinski, and 15 more)';

    public function handle(): int
    {
        $presidio = Institution::firstOrCreate(['name' => 'Presidio Stockade'], ['city' => 'San Francisco', 'state' => 'California'])->id;

        $ctx = 'was a U.S. Army soldier and one of the "Presidio 27" — the stockade prisoners at the Presidio of San Francisco who, on October 14, 1968, staged a sit-down protest against brutal conditions and the killing of a fellow prisoner, Richard Bunch, who had been shot in the back by a guard three days earlier. Singing "We Shall Overcome" and reading out a list of grievances, the men were charged with mutiny, a capital offense. On appeal in June 1970 the mutiny convictions were reduced to willful disobedience, and the defendants were released after about eighteen months.';

        // name parts + optional origin (age/hometown) + optional sentence clause
        $people = [
            ['first' => 'Nesrey', 'middle' => 'Dean', 'last' => 'Sood', 'origin' => 'then 26 and from Oakland, California', 'sent' => 'The first to be tried, he was sentenced to fifteen years at hard labor — later cut to seven years and then to two.'],
            ['first' => 'Lawrence', 'middle' => 'W.', 'last' => 'Reidel', 'origin' => 'then 20', 'sent' => 'He was sentenced to fourteen years at hard labor.'],
            ['first' => 'Louis', 'middle' => 'S.', 'last' => 'Osczepinski', 'origin' => 'then 21', 'sent' => 'He received the harshest sentence, sixteen years at hard labor.'],
            ['first' => 'Richard', 'middle' => 'Lee', 'last' => 'Gentile', 'origin' => 'then 20 and from Hampton, Virginia', 'sent' => 'He was sentenced to six months at hard labor.'],
            ['first' => 'Ricky', 'middle' => 'Lee', 'last' => 'Dodd', 'origin' => null, 'sent' => ''],
            ['first' => 'Roy', 'last' => 'Pulley', 'origin' => 'then 19 and from Lakeport, California', 'sent' => ''],
            ['first' => 'William', 'last' => 'Hayes', 'aka' => 'Billy Hayes', 'origin' => null, 'sent' => ''],
            ['first' => 'Danny', 'last' => 'Seals', 'origin' => 'then 21 and from Auburn, California', 'sent' => ''],
            ['first' => 'Larry', 'last' => 'Sales', 'origin' => 'then 21 and from Modesto, California', 'sent' => ''],
            ['first' => 'Danny', 'last' => 'Wilkins', 'origin' => 'then 19 and from Safford, Arizona', 'sent' => ''],
            ['first' => 'Stephen', 'last' => 'Rowland', 'origin' => 'then 21 and from St. Louis', 'sent' => ''],
            ['first' => 'Buddy', 'last' => 'Shaw', 'origin' => null, 'sent' => ''],
            ['first' => 'Harold', 'last' => 'Swanson', 'origin' => null, 'sent' => ''],
            ['first' => 'Michael', 'last' => 'Murphy', 'origin' => null, 'sent' => ''],
            ['first' => 'Ed', 'last' => 'Yost', 'origin' => null, 'sent' => ''],
            ['first' => 'Larry', 'last' => 'Zaino', 'origin' => null, 'sent' => ''],
            ['first' => 'John', 'last' => 'Colip', 'origin' => null, 'sent' => ''],
            ['first' => 'Richard', 'last' => 'Duncan', 'origin' => null, 'sent' => ''],
        ];

        DB::transaction(function () use ($people, $ctx, $presidio) {
            foreach ($people as $p) {
                $name = trim($p['first'].' '.$p['last']);
                $lead = $name.(! empty($p['origin']) ? ', '.$p['origin'].',' : '');
                $bio = $lead.' '.$ctx.(! empty($p['sent']) ? ' '.$p['sent'] : '');

                $prisoner = Prisoner::withUnderReview()->where('name', $name)->first()
                    ?? new Prisoner(['name' => $name]);

                $prisoner->fill([
                    'name' => $name,
                    'first_name' => $p['first'],
                    'middle_name' => $p['middle'] ?? null,
                    'last_name' => $p['last'],
                    'aka' => $p['aka'] ?? null,
                    'gender' => 'Male',
                    'state' => 'California',
                    'era' => '1960s',
                    'ideologies' => ['Anti-War', 'GI resistance'],
                    'affiliation' => ['Presidio 27'],
                    'description' => $bio,
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
                    'institution_id' => $presidio,
                    'charges' => 'Mutiny — for the October 14, 1968 sit-down protest by prisoners at the Presidio stockade (reduced to willful disobedience on appeal, June 1970).',
                    'convicted' => 'Yes — court-martialed; mutiny reduced to willful disobedience on appeal.',
                    'sentence' => ! empty($p['sent'])
                        ? $p['sent'].' Released after about eighteen months when the sentences were reduced on appeal.'
                        : 'Sentenced (the group\'s sentences ranged from nine months to sixteen years); released after about eighteen months when the mutiny convictions were reduced on appeal.',
                ]);
                $case->setPartialDate('incarceration_date', 1968, 10);
                $case->setPartialDate('release_date', 1970);
                $case->save();

                $this->info(($prisoner->wasRecentlyCreated ? 'Added: ' : 'Filled: ').$name.' (slug: '.$prisoner->slug.')');
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
