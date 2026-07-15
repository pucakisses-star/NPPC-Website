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
 * The "Presidio 27" — stockade prisoners at the Presidio of San Francisco who
 * staged a sit-down protest on October 14, 1968 (three days after a guard shot
 * and killed the prisoner Richard Bunch) and were charged with mutiny, a
 * capital offense. Adds the four named protest leaders plus Richard Bunch, the
 * prisoner whose killing triggered the mutiny (a death in custody).
 *
 * Create-or-update by name; rebuilds each single case. Idempotent.
 */
final class AddPresidio27 extends Command
{
    protected $signature = 'prisoners:add-presidio-27';

    protected $description = 'Add the Presidio 27 mutiny leaders (Mather, Pawlowski, Rowland, Blake) and Richard Bunch';

    public function handle(): int
    {
        $presidio = Institution::firstOrCreate(['name' => 'Presidio Stockade'], ['city' => 'San Francisco', 'state' => 'California'])->id;
        $fortOrd = Institution::firstOrCreate(['name' => 'Fort Ord Stockade'], ['city' => 'Fort Ord', 'state' => 'California'])->id;

        $people = [
            [
                'name' => 'Keith Mather', 'first' => 'Keith', 'last' => 'Mather',
                'ideologies' => ['Anti-War', 'GI resistance'], 'affiliation' => ['Presidio 27'],
                'bio' => 'Keith Mather was a U.S. Army private and one of the leaders of the "Presidio 27," the stockade prisoners at the Presidio of San Francisco who staged a sit-down protest on October 14, 1968. The protest erupted after a guard shot and killed a fellow prisoner, Richard Bunch; the men sang "We Shall Overcome," read a list of grievances, and were charged with mutiny, which carried a potential death penalty. Mather, who had earlier taken antiwar sanctuary in a San Francisco church with the "Nine for Peace," escaped from the stockade on Christmas Eve 1968 with Walter Pawlowski and fled to Canada. He lived there in exile for some fifteen years; after returning to the United States he was arrested in 1984, court-martialed, and imprisoned at Fort Ord before his release in 1985 — his attorney called him the last American prisoner of conscience of the Vietnam War.',
                'charges' => 'Mutiny — for leading the October 1968 sit-down protest by prisoners at the Presidio stockade.',
                'convicted' => 'Charged with mutiny; a fugitive in Canada 1968–1984, then court-martialed after his return.',
                'sentence' => 'Escaped the Presidio stockade on December 24, 1968 and lived in exile in Canada for about fifteen years; arrested on his 1984 return, court-martialed, and released from Fort Ord in 1985.',
                'incarceration' => [1984], 'release' => [1985], 'inst' => $fortOrd,
            ],
            [
                'name' => 'Walter Pawlowski', 'first' => 'Walter', 'last' => 'Pawlowski',
                'ideologies' => ['Anti-War', 'GI resistance'], 'affiliation' => ['Presidio 27'],
                'bio' => 'Walter Pawlowski was a U.S. Army soldier and one of the leaders of the "Presidio 27" stockade protest of October 14, 1968, at the Presidio of San Francisco. During the sit-down — sparked by a guard\'s killing of the prisoner Richard Bunch — Pawlowski read aloud the protesters\' list of grievances. Charged with mutiny, a potential capital offense, he escaped from the stockade on Christmas Eve 1968 together with Keith Mather and fled to Canada.',
                'charges' => 'Mutiny — for the October 1968 sit-down protest at the Presidio stockade.',
                'convicted' => 'Charged with mutiny; escaped custody before trial.',
                'sentence' => 'Escaped the Presidio stockade on December 24, 1968 and fled into exile in Canada.',
                'incarceration' => [1968, 10], 'release' => [1968, 12], 'inst' => $presidio,
            ],
            [
                'name' => 'Randy Rowland', 'first' => 'Randy', 'last' => 'Rowland',
                'ideologies' => ['Anti-War', 'GI resistance'], 'affiliation' => ['Presidio 27'],
                'bio' => 'Randy Rowland was a U.S. Army soldier and one of the "Presidio 27" — the stockade prisoners who staged a sit-down protest on October 14, 1968, at the Presidio of San Francisco after a guard shot and killed the prisoner Richard Bunch. Charged with mutiny, a capital offense, Rowland was court-martialed and imprisoned; on appeal in June 1970 the mutiny convictions were reduced to willful disobedience, and he was released after about a year and a half.',
                'charges' => 'Mutiny — for the October 1968 sit-down protest at the Presidio stockade (reduced to willful disobedience on appeal, June 1970).',
                'convicted' => 'Yes — court-martialed; mutiny reduced to willful disobedience on appeal.',
                'sentence' => 'Imprisoned about a year and a half; released in 1970 after the mutiny conviction was reduced on appeal.',
                'incarceration' => [1968, 10], 'release' => [1970], 'inst' => $presidio,
            ],
            [
                'name' => 'Linden Blake', 'first' => 'Linden', 'last' => 'Blake',
                'ideologies' => ['Anti-War', 'GI resistance'], 'affiliation' => ['Presidio 27'],
                'bio' => 'Linden Blake was a U.S. Army soldier and one of the four AWOL men who initiated the "Presidio 27" stockade sit-down of October 1968 at the Presidio of San Francisco, staged after a guard killed the prisoner Richard Bunch. Charged with mutiny, Blake escaped from the prison hospital in February 1969 — sawing through the window bars over two weeks with a smuggled hacksaw blade and squeezing out — and fled to Canada, escaping conviction on appeal.',
                'charges' => 'Mutiny — for helping initiate the October 1968 sit-down protest at the Presidio stockade.',
                'convicted' => 'Charged with mutiny; escaped custody.',
                'sentence' => 'Escaped the Presidio prison hospital in February 1969 and fled into exile in Canada.',
                'incarceration' => [1968, 10], 'release' => [1969, 2], 'inst' => $presidio,
            ],
            [
                'name' => 'Richard Bunch', 'first' => 'Richard', 'last' => 'Bunch',
                'ideologies' => ['GI resistance'], 'affiliation' => [],
                'bio' => 'Richard Bunch was a young stockade prisoner at the Presidio of San Francisco whose killing triggered the "Presidio 27" mutiny protest. On October 11, 1968, a guard shot him in the back with a shotgun as he walked away from a work detail. His death outraged the other prisoners, twenty-seven of whom staged a sit-down protest three days later and were charged with mutiny.',
                'charges' => 'Held in the Presidio stockade as an AWOL soldier.',
                'convicted' => 'Died in custody — shot in the back by a stockade guard on October 11, 1968.',
                'sentence' => 'Shot and killed by a Presidio stockade guard on October 11, 1968.',
                'incarceration' => [1968], 'died' => [1968, 10, 11], 'inst' => $presidio,
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
                    'last_name' => $p['last'],
                    'gender' => 'Male',
                    'race' => 'White',
                    'state' => 'California',
                    'era' => '1960s',
                    'ideologies' => $p['ideologies'],
                    'affiliation' => $p['affiliation'],
                    'description' => $p['bio'],
                    'in_custody' => false,
                    'released' => ! $died,
                    'in_exile' => false,
                    'awaiting_trial' => false,
                ]);
                if ($died) {
                    $prisoner->setPartialDate('death_date', ...$p['died']);
                }
                $prisoner->save();

                $prisoner->cases()->delete();
                $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
                $case->fill([
                    'prisoner_id' => $prisoner->id,
                    'institution_id' => $p['inst'],
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

                $this->info(($prisoner->wasRecentlyCreated ? 'Added: ' : 'Filled: ').$prisoner->name.' (slug: '.$prisoner->slug.')');
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
