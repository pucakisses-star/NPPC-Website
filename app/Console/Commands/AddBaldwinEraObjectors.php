<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * World War I conscientious objectors and free-speech prisoners drawn from
 * Peggy Lamson's / Robert Cottrell's biography of Roger Nash Baldwin and the
 * ACLU:
 *   - Roger Nash Baldwin (fills an existing stub) — the ACLU's founder, jailed
 *     in 1918–19 for refusing to register for the draft.
 *   - Evan W. Thomas — the absolutist objector (brother of Norman Thomas)
 *     imprisoned at Fort Leavenworth.
 *   - Oral James — a St. Louis objector Baldwin had mentored, jailed at Fort
 *     Leavenworth, where he led a protest strike.
 *
 * Create-or-update by name; rebuilds each single case. Idempotent.
 */
class AddBaldwinEraObjectors extends Command
{
    protected $signature = 'prisoners:add-baldwin-era-objectors';

    protected $description = 'Add/fill WWI conscientious objectors from the Roger Baldwin/ACLU biography (Baldwin, Evan Thomas, Oral James)';

    public function handle(): int
    {
        $people = [
            [
                'name' => 'Roger Baldwin', 'first' => 'Roger', 'middle' => 'Nash', 'last' => 'Baldwin',
                'aka' => 'Roger Nash Baldwin',
                'race' => 'White', 'state' => 'New York', 'era' => '1910s',
                'birth' => [1884, 1, 21], 'death' => [1981, 8, 26],
                'ideologies' => ['Pacifism', 'Anti-War', 'Free speech', 'Civil liberties'],
                'affiliation' => ['American Civil Liberties Union', 'National Civil Liberties Bureau'],
                'bio' => 'Roger Nash Baldwin (1884–1981) was the founder and, for thirty years, director of the American Civil Liberties Union. During World War I he ran the National Civil Liberties Bureau — the ACLU\'s predecessor — defending conscientious objectors and antiwar dissenters against the Espionage Act and military repression. Refusing to register for the draft, he was indicted under the Selective Service Act; at his sentencing on October 30, 1918 he delivered a celebrated statement of conscience, later published as "The Individual and the State," declaring his "uncompromising opposition to the principle of conscription of life by the state." Sentenced to one year, he served about nine to ten months — first in the Tombs and then the Essex County Jail in Newark, where he organized a prisoners\' welfare league — and was released in the summer of 1919. He founded the ACLU the next year.',
                'charges' => 'Refusing to register for the draft (violating the Selective Service Act), in opposition to conscription during World War I.',
                'convicted' => 'Yes — convicted after delivering his famous courtroom statement "The Individual and the State" (October 30, 1918).',
                'sentence' => 'One year; he served about nine to ten months (the Tombs, then the Essex County Jail in Newark) and was released in the summer of 1919.',
                'incarceration' => [1918, 10, 30], 'release' => [1919, 7],
            ],
            [
                'name' => 'Evan Thomas', 'first' => 'Evan', 'last' => 'Thomas', 'aka' => 'Evan W. Thomas',
                'race' => 'White', 'state' => 'New York', 'era' => '1910s',
                'birth' => [1890], 'death' => [1974],
                'ideologies' => ['Pacifism', 'Anti-War', 'Conscientious objection'],
                'affiliation' => ['Fellowship of Reconciliation'],
                'bio' => 'Evan W. Thomas (1890–1974), a divinity student and younger brother of the socialist leader Norman Thomas, was among the most uncompromising "absolutist" conscientious objectors of World War I — refusing not only to bear arms but to perform any service under military control. Court-martialed by the Army, he was imprisoned at Fort Riley and then the military prison at Fort Leavenworth, where he was manacled to the bars of his cell and joined hunger strikes protesting the brutal treatment of objectors. Released after the war, he became a physician and remained a lifelong pacifist.',
                'charges' => 'Refusing all military service as an absolutist conscientious objector during World War I.',
                'convicted' => 'Yes — court-martialed by the U.S. Army.',
                'sentence' => 'Imprisoned at Fort Riley and Fort Leavenworth — manacled and subjected to harsh treatment — until released after the war.',
                'incarceration' => [1918], 'release' => [1919],
            ],
            [
                'name' => 'Oral James', 'first' => 'Oral', 'last' => 'James',
                'state' => 'Missouri', 'era' => '1910s',
                'ideologies' => ['Pacifism', 'Anti-War', 'Conscientious objection'],
                'bio' => 'Oral James was a young conscientious objector from St. Louis — one of two youths the future ACLU founder Roger Baldwin had looked after there — who refused military service of any kind during World War I. Court-martialed, he was imprisoned at the military prison at Fort Leavenworth, where he led a strike protesting the treatment of objectors.',
                'charges' => 'Refusing military service as a conscientious objector during World War I.',
                'convicted' => 'Yes — court-martialed.',
                'sentence' => 'Imprisoned at the military prison at Fort Leavenworth, where he led a protest strike.',
                'incarceration' => [1918],
            ],
        ];

        DB::transaction(function () use ($people) {
            foreach ($people as $p) {
                $prisoner = Prisoner::withUnderReview()->where('name', $p['name'])->first()
                    ?? new Prisoner(['name' => $p['name']]);

                $prisoner->fill([
                    'name' => $p['name'],
                    'first_name' => $p['first'] ?? null,
                    'middle_name' => $p['middle'] ?? null,
                    'last_name' => $p['last'] ?? null,
                    'aka' => $p['aka'] ?? null,
                    'gender' => 'Male',
                    'race' => $p['race'] ?? null,
                    'state' => $p['state'] ?? null,
                    'era' => $p['era'],
                    'ideologies' => $p['ideologies'],
                    'affiliation' => $p['affiliation'] ?? [],
                    'description' => $p['bio'],
                    'in_custody' => false,
                    'released' => true,
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
                $case->save();

                $this->info(($prisoner->wasRecentlyCreated ? 'Added: ' : 'Filled: ').$prisoner->name.' (slug: '.$prisoner->slug.')');
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
