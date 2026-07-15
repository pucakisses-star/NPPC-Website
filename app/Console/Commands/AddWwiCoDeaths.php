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
 * Conscientious objectors who DIED in U.S. military prisons during World War I —
 * the roughly seventeen CO prison deaths (beyond the Hutterite Hofer brothers
 * and Henry Franz, added separately). Names from the list in Wanda Mason's study
 * of the Historic Peace Churches (citing Shubin); biographical detail from Anne
 * Yoder's Swarthmore WWI C.O. database. Each is recorded as a death in custody.
 *
 * Create-or-update by name; rebuilds each single case. Idempotent.
 */
class AddWwiCoDeaths extends Command
{
    protected $signature = 'prisoners:add-wwi-co-deaths';

    protected $description = 'Add WWI conscientious objectors who died in U.S. military prisons';

    public function handle(): int
    {
        $lw = Institution::firstOrCreate(['name' => 'United States Disciplinary Barracks, Fort Leavenworth'], ['city' => 'Fort Leavenworth', 'state' => 'Kansas'])->id;

        $tag = ' He was one of the roughly seventeen conscientious objectors who died in United States military prisons during World War I.';

        $people = [
            [
                'name' => 'Charles W. Bolly', 'first' => 'Charles', 'middle' => 'W.', 'last' => 'Bolly', 'aka' => 'Charles Bolley',
                'state' => 'Indiana', 'denom' => 'Church of the Brethren',
                'bio' => 'Charles W. Bolly was a Progressive Brethren religious objector from Mongo, Indiana. Sent to Camp Zachary Taylor and then imprisoned at Fort Leavenworth on October 4, 1918, he died there in custody.',
                'incarceration' => [1918, 10, 4], 'died' => [1919], 'inst' => $lw,
            ],
            [
                'name' => 'Frank Burke', 'first' => 'Frank', 'middle' => 'J.', 'last' => 'Burke',
                'state' => 'Illinois',
                'bio' => 'Frank J. Burke of Chicago registered as a conscientious objector in June 1917 and was imprisoned at Fort Leavenworth and the Fort Douglas War Prison Barracks. He died in custody on July 30, 1919.',
                'incarceration' => [1918], 'died' => [1919, 7, 30], 'inst' => $lw,
            ],
            [
                'name' => 'Reuben Eash', 'first' => 'Reuben', 'middle' => 'J.', 'last' => 'Eash',
                'state' => 'Oklahoma', 'denom' => 'Mennonite',
                'bio' => 'Reuben J. Eash was a Mennonite religious objector from Thomas, Oklahoma, sent to Camp Travis and placed in the conscientious-objector detachment before being imprisoned at Fort Leavenworth, where he died in October 1918.',
                'incarceration' => [1918], 'died' => [1918, 10], 'inst' => $lw,
            ],
            [
                'name' => 'Julius Firestone', 'first' => 'Julius', 'last' => 'Firestone',
                'state' => 'New York',
                'bio' => 'Julius Firestone, of New York, was inducted in 1917. For his outspoken stance against the war he was tarred and feathered by soldiers. He died in custody on November 25, 1918.',
                'incarceration' => [1918], 'died' => [1918, 11, 25],
            ],
            [
                'name' => 'Daniel B. Flory', 'first' => 'Daniel', 'middle' => 'B.', 'last' => 'Flory',
                'state' => 'Pennsylvania',
                'bio' => 'Daniel B. Flory, of Lancaster, Pennsylvania, objected to military service on religious grounds. Imprisoned at Fort Leavenworth, he died in custody in March 1919.',
                'incarceration' => [1918], 'died' => [1919, 3], 'inst' => $lw,
            ],
            [
                'name' => 'Ernest Gellert', 'first' => 'Ernest', 'last' => 'Gellert',
                'state' => 'New York',
                'bio' => 'Ernest Gellert was a 22-year-old conscientious objector court-martialed at Camp Upton, New York, and subjected to humiliating abuse — stripped of his clothing and forced to stand exposed. He died on April 8, 1918, a death recorded as a suicide amid his mistreatment.',
                'incarceration' => [1918], 'died' => [1918, 4, 8],
            ],
            [
                'name' => 'Mark R. Thomas', 'first' => 'Mark', 'middle' => 'R.', 'last' => 'Thomas',
                'state' => 'Pennsylvania', 'denom' => "Jehovah's Witnesses",
                'bio' => 'Mark R. Thomas, of Vandergrift, Pennsylvania, was a member of the International Bible Students Association (Jehovah\'s Witnesses) who refused military service. Named on the Judge Advocate General\'s conscientious-objector card file, he was imprisoned at Fort Leavenworth and died there on October 15, 1918.',
                'incarceration' => [1918], 'died' => [1918, 10, 15], 'inst' => $lw,
            ],
            [
                'name' => 'Ernest D. Wells', 'first' => 'Ernest', 'middle' => 'D.', 'last' => 'Wells',
                'state' => 'Virginia', 'denom' => 'Christadelphian',
                'bio' => 'Ernest D. Wells, a Christadelphian religious objector from Virginia, was imprisoned at Fort Leavenworth in the spring of 1918 and had died in custody there by March 1919.',
                'incarceration' => [1918], 'died' => [1919, 3], 'inst' => $lw,
            ],
            [
                'name' => 'John M. Wolfe', 'first' => 'John', 'middle' => 'M.', 'last' => 'Wolfe',
                'state' => 'Maryland', 'denom' => 'Church of the Brethren',
                'bio' => 'John M. Wolfe was an Old German Baptist Brethren objector from Smithsburg, Maryland, sent to Camp Funston at Fort Riley. He was among the 41 objectors confined in a "damp, dark basement cell" in September 1918, and he died in custody on December 6, 1918.',
                'incarceration' => [1918], 'died' => [1918, 12, 6],
            ],
            [
                'name' => 'Daniel S. Yoder', 'first' => 'Daniel', 'middle' => 'S.', 'last' => 'Yoder',
                'state' => 'Ohio', 'denom' => 'Amish',
                'bio' => 'Daniel S. Yoder was an Amish Mennonite religious objector from Apple Creek, Ohio, who was mistreated at Camp Sherman — scrubbed with a heavy brush and dragged by the hair — before being imprisoned at Fort Leavenworth, where he died in custody on January 26, 1919.',
                'incarceration' => [1918], 'died' => [1919, 1, 26], 'inst' => $lw,
            ],
            [
                'name' => 'Walter Sprunger', 'first' => 'Walter', 'last' => 'Sprunger',
                'state' => 'Indiana', 'denom' => 'Mennonite',
                'bio' => 'Walter Sprunger was a General Conference Mennonite religious objector from Berne, Indiana. Court-martialed and imprisoned at Fort Leavenworth, he died in custody in October 1918.',
                'incarceration' => [1918], 'died' => [1918, 10], 'inst' => $lw,
            ],
            [
                'name' => 'Daniel B. Teuscher', 'first' => 'Daniel', 'middle' => 'B.', 'last' => 'Teuscher',
                'state' => 'Illinois', 'denom' => 'Mennonite',
                'bio' => 'Daniel B. Teuscher was a Mennonite objector from Fisher, Illinois, sent to Camp Travis and among the group of 41 objectors court-martialed for refusing to wear the uniform. Imprisoned at Fort Leavenworth, he died in custody in November 1918.',
                'incarceration' => [1918], 'died' => [1918, 11], 'inst' => $lw,
            ],
            [
                'name' => 'Van Skedine', 'first' => 'Van', 'last' => 'Skedine',
                'bio' => 'Van Skedine was a conscientious objector who died in a United States military prison during World War I; his case is recorded among the war\'s CO prison deaths, though little further detail survives.',
                'died' => [1918],
            ],
            [
                'name' => 'Johannes Klassen', 'first' => 'Johannes', 'last' => 'Klassen', 'denom' => 'Mennonite',
                'bio' => 'Johannes Klassen was a Mennonite conscientious objector who died in a United States military prison during World War I, listed among the objectors who did not survive their imprisonment.',
                'died' => [1918],
            ],
        ];

        DB::transaction(function () use ($people, $tag) {
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
                    'race' => 'White',
                    'state' => $p['state'] ?? null,
                    'era' => '1910s',
                    'ideologies' => ['Pacifism', 'Conscientious objection'],
                    'affiliation' => ! empty($p['denom']) ? [$p['denom']] : [],
                    'description' => $p['bio'].$tag,
                    'in_custody' => false,
                    'released' => false,   // died in custody
                    'in_exile' => false,
                    'awaiting_trial' => false,
                ]);
                if (! empty($p['died'])) {
                    $prisoner->setPartialDate('death_date', ...$p['died']);
                }
                $prisoner->save();

                $prisoner->cases()->delete();
                $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
                $case->fill([
                    'prisoner_id' => $prisoner->id,
                    'institution_id' => $p['inst'] ?? null,
                    'charges' => 'Refusing military service as a conscientious objector during World War I — court-martialed for disobeying orders / refusing to wear the uniform.',
                    'convicted' => 'Yes — court-martialed.',
                    'sentence' => 'Imprisoned as a conscientious objector; he died in custody.',
                ]);
                if (! empty($p['incarceration'])) {
                    $case->setPartialDate('incarceration_date', ...$p['incarceration']);
                }
                if (! empty($p['died'])) {
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
