<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Named conscientious-objector prisoners drawn from Wanda Mason's study of the
 * Historic Peace Churches (and the standard sources), spanning World War I to
 * the Vietnam era. Fills existing stubs for Philip Grosser, Maurice Hess, and
 * Bayard Rustin; adds the others.
 *
 * Create-or-update by name; rebuilds each single case. Idempotent.
 */
class AddNamedCoPrisoners extends Command
{
    protected $signature = 'prisoners:add-named-co-prisoners';

    protected $description = 'Add named CO prisoners (Grosser, Hess, Rustin, Gerdes, Champney, Snider, Swift)';

    public function handle(): int
    {
        $people = [
            [
                'name' => 'Philip Grosser', 'first' => 'Philip', 'middle' => 'B.', 'last' => 'Grosser',
                'state' => 'Massachusetts', 'era' => '1910s', 'birth' => [1890], 'death' => [1933],
                'ideologies' => ['Pacifism', 'Anti-War', 'Anarchism', 'Conscientious objection'],
                'bio' => 'Philip B. Grosser (1890–1933), of Boston, was an absolutist conscientious objector arrested in December 1917 for failing to report for military duty. Refusing all cooperation with the military, he was subjected to some of the war\'s worst treatment of objectors — hung by his wrists from ropes at Fort Riley and confined in "the hole" at Alcatraz — and was held at Fort Leavenworth as well. He recorded the ordeal in his memoir "Uncle Sam\'s Devil\'s Island."',
                'charges' => 'Refusing military service as an absolutist conscientious objector (arrested in Boston, December 1917).',
                'convicted' => 'Yes — court-martialed.',
                'sentence' => 'Imprisoned at Fort Leavenworth and Alcatraz — hung by the wrists and held in solitary confinement — until his release after the war.',
                'incarceration' => [1917, 12], 'release' => [1920],
            ],
            [
                'name' => 'Maurice Hess', 'first' => 'Maurice', 'middle' => 'Abram', 'last' => 'Hess',
                'state' => 'Pennsylvania', 'era' => '1910s', 'birth' => [1888, 2, 7],
                'ideologies' => ['Pacifism', 'Conscientious objection'],
                'affiliation' => ['Church of the Brethren'],
                'bio' => 'Maurice Abram Hess (born 1888), of Mont Alto, Pennsylvania, was an Old German Baptist Brethren conscientious objector who declared his stand at Camp Meade in November 1917. Court-martialed for refusing to bear arms, he delivered a widely reproduced statement of conscience to the court on October 1, 1918, and was sentenced to twenty-five years at Fort Leavenworth. Released after the war, he became a professor at McPherson College in Kansas.',
                'charges' => 'Refusing to bear arms as a Church of the Brethren conscientious objector during World War I.',
                'convicted' => 'Yes — court-martialed (October 1918).',
                'sentence' => 'Twenty-five years at Fort Leavenworth; released after the war.',
                'incarceration' => [1918], 'release' => [1919],
            ],
            [
                'name' => 'Albert Duane Swift', 'first' => 'Albert', 'middle' => 'Duane', 'last' => 'Swift', 'aka' => 'Duane Swift',
                'state' => 'Arkansas', 'era' => '1910s',
                'ideologies' => ['Pacifism', 'Conscientious objection'],
                'bio' => 'Albert Duane Swift, of Batesville, Arkansas, registered as a conscientious objector in 1917 and refused military service on religious grounds. Imprisoned at Fort Leavenworth, he was among the objectors brutally punished — shackled in half-inch irons and forced to move rocks from one place to another at Fort Riley.',
                'charges' => 'Refusing military service as a conscientious objector during World War I.',
                'convicted' => 'Yes — court-martialed.',
                'sentence' => 'Imprisoned at Fort Leavenworth; shackled in irons at hard labor.',
                'incarceration' => [1918],
            ],
            [
                'name' => 'David Gerdes', 'first' => 'David', 'last' => 'Gerdes',
                'state' => 'Illinois', 'era' => '1910s',
                'ideologies' => ['Pacifism', 'Anti-War', 'Free speech'],
                'affiliation' => ['Church of the Brethren'],
                'bio' => 'David Gerdes of Illinois was prosecuted under the Espionage Act during World War I for discouraging the purchase of Liberty (war) bonds. He served ten months at Fort Leavenworth.',
                'charges' => 'Violating the Espionage Act — for discouraging the purchase of Liberty (war) bonds.',
                'convicted' => 'Yes — convicted under the Espionage Act.',
                'sentence' => 'Ten months at Fort Leavenworth.',
                'incarceration' => [1918],
            ],
            [
                'name' => 'Bayard Rustin', 'first' => 'Bayard', 'last' => 'Rustin',
                'state' => 'Pennsylvania', 'era' => '1940s', 'birth' => [1912, 3, 17], 'death' => [1987, 8, 24],
                'ideologies' => ['Pacifism', 'Civil rights', 'Anti-War'],
                'affiliation' => ['Fellowship of Reconciliation', 'War Resisters League'],
                'bio' => 'Bayard Rustin (1912–1987) was a Quaker pacifist and civil-rights strategist who refused to register for or cooperate with the World War II draft. Convicted in 1944, he spent about three years in federal prison at Ashland, Kentucky and Lewisburg, Pennsylvania, where he organized protests against the racial segregation of the prison dining hall. Released in 1947, he was jailed again that year on a North Carolina chain gang for the Journey of Reconciliation, an early freedom ride. He went on to become a trusted adviser to Dr. Martin Luther King Jr. and the chief organizer of the 1963 March on Washington.',
                'charges' => 'Refusing to register for or cooperate with the draft (World War II), as a pacifist.',
                'convicted' => 'Yes — convicted in 1944.',
                'sentence' => 'About three years in federal prison (Ashland, Kentucky and Lewisburg, Pennsylvania), 1944–1947.',
                'incarceration' => [1944], 'release' => [1947],
            ],
            [
                'name' => 'Kenneth Champney', 'first' => 'Kenneth', 'last' => 'Champney',
                'state' => 'Ohio', 'era' => '1950s',
                'ideologies' => ['Pacifism', 'Conscientious objection'],
                'affiliation' => ['Quaker'],
                'bio' => 'Kenneth Champney was a 19-year-old Quaker from Yellow Springs, Ohio, and publisher of the local newspaper when he was arrested during the Korean War and convicted of conscientious non-cooperation with the draft. He was sentenced to two years in a West Virginia federal prison; his wife and friends kept the newspaper running until his return.',
                'charges' => 'Conscientious non-cooperation with the draft (Korean War).',
                'convicted' => 'Yes — convicted of non-cooperation.',
                'sentence' => 'Two years in a West Virginia federal prison.',
                'incarceration' => [1951],
            ],
            [
                'name' => 'Lyle Snider', 'first' => 'Lyle', 'middle' => 'B.', 'last' => 'Snider',
                'era' => '1970s',
                'ideologies' => ['Pacifism', 'Tax resistance', 'Anti-War'],
                'affiliation' => ['Quaker'],
                'bio' => 'Lyle B. Snider was a Quaker war-tax resister who, convinced of a shared responsibility for all humanity and opposed to military spending, claimed billions of dependents on his tax form in 1972. Arrested that December and convicted of tax evasion, he was sentenced to eight months in prison — plus an additional thirty days for contempt of court, imposed because, on grounds of conscience, he would not rise when the judge entered the courtroom.',
                'charges' => 'War-tax resistance (charged as tax evasion) — for claiming the world\'s population as dependents to protest military spending.',
                'convicted' => 'Yes — convicted of tax evasion (and contempt of court).',
                'sentence' => 'Eight months in prison, plus thirty days for contempt of court.',
                'incarceration' => [1973],
            ],
        ];

        DB::transaction(function () use ($people) {
            foreach ($people as $p) {
                $prisoner = Prisoner::withUnderReview()->where('name', $p['name'])->first()
                    ?? new Prisoner(['name' => $p['name']]);

                $prisoner->fill([
                    'name' => $p['name'],
                    'first_name' => $p['first'],
                    'middle_name' => $p['middle'] ?? null,
                    'last_name' => $p['last'],
                    'aka' => $p['aka'] ?? null,
                    'gender' => 'Male',
                    'race' => $p['race'] ?? 'White',
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
