<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Fills the empty stubs for political prisoners mentioned in the Roger Baldwin /
 * ACLU biography: the WWI Espionage Act and conscientious-objector cases (Scott
 * Nearing, Agnes Smedley, Ben Salmon) and the WWII Japanese-American exclusion
 * resister Gordon Hirabayashi.
 *
 * Create-or-update by name; rebuilds each single case. Idempotent.
 */
class FillBaldwinBookStubs extends Command
{
    protected $signature = 'prisoners:fill-baldwin-book-stubs';

    protected $description = 'Fill empty stubs for political prisoners named in the Baldwin/ACLU book (Nearing, Smedley, Salmon, Hirabayashi)';

    public function handle(): int
    {
        $people = [
            [
                'name' => 'Scott Nearing', 'first' => 'Scott', 'last' => 'Nearing',
                'race' => 'White', 'state' => 'Pennsylvania', 'era' => '1910s',
                'birth' => [1883], 'death' => [1983],
                'ideologies' => ['Pacifism', 'Anti-War', 'Socialism', 'Free speech'],
                'affiliation' => ['People\'s Council of America'],
                'bio' => 'Scott Nearing (1883–1983) was a radical economist, pacifist, and later a founder of the American back-to-the-land movement. Dismissed from two universities for his politics, he wrote the antiwar pamphlet "The Great Madness" (1917) for the People\'s Council of America. In 1918 he was indicted under the Espionage Act for obstructing recruitment; at his 1919 trial the jury acquitted Nearing personally even as it convicted his publisher, the American Socialist Society — a notable free-speech verdict.',
                'charges' => 'Violating the Espionage Act — for the antiwar pamphlet "The Great Madness," charged with obstructing military recruitment.',
                'convicted' => 'No — indicted in 1918; at trial in 1919 the jury acquitted Nearing (while convicting his publisher).',
                'sentence' => 'None — acquitted.',
            ],
            [
                'name' => 'Agnes Smedley', 'first' => 'Agnes', 'last' => 'Smedley',
                'gender' => 'Female', 'race' => 'White', 'state' => 'New York', 'era' => '1910s',
                'birth' => [1892], 'death' => [1950],
                'ideologies' => ['Anti-imperialism', 'Feminism', 'Socialism'],
                'affiliation' => ['Friends of Freedom for India'],
                'bio' => 'Agnes Smedley (1892–1950) was an American journalist, feminist, and anticolonial activist best known for her later reporting on the Chinese Revolution. In March 1918 she was arrested in New York and indicted under the Espionage Act for her work with Indian nationalists seeking independence from Britain. She was held about two months in the Tombs — where Roger Baldwin, also jailed, met her — before being bailed out; the New York indictment was dismissed later in 1918 and the related San Francisco charges were dropped in 1919.',
                'charges' => 'Violating the Espionage Act and neutrality laws — for aiding Indian nationalists working for independence from Britain.',
                'convicted' => 'No — indicted in 1918; the charges were dismissed in 1918–1919.',
                'sentence' => 'Held about two months in the Tombs in New York before release on bail.',
                'incarceration' => [1918, 3], 'release' => [1918, 5],
            ],
            [
                'name' => 'Ben Salmon', 'first' => 'Benjamin', 'middle' => 'Joseph', 'last' => 'Salmon',
                'race' => 'White', 'state' => 'Colorado', 'era' => '1910s',
                'birth' => [1888, 10, 15], 'death' => [1932, 2, 15],
                'ideologies' => ['Pacifism', 'Anti-War', 'Conscientious objection'],
                'affiliation' => ['Catholic Church'],
                'bio' => 'Ben Salmon was one of the most visible U.S. conscientious objectors of World War I, arguing from Catholic pacifist convictions that participation in war was morally impossible. On June 5, 1917, he wrote directly to President Wilson refusing to register for the draft on the grounds that all war was incompatible with the teachings of Christ; he was sentenced to death, which was reduced to 25 years hard labor for desertion and "spreading propaganda," and imprisoned at Fort Leavenworth. In 1920 he launched a hunger strike over his treatment and was transferred to St. Elizabeths Hospital; public pressure for the release of wartime objectors grew, and he was pardoned and freed in November 1920. His ordeal permanently broke his health; he died of pneumonia in 1932 at age 43.',
                'charges' => 'Refusing to register for or perform military service (desertion / disobedience) as a Catholic conscientious objector during World War I.',
                'convicted' => 'Yes — court-martialed and convicted.',
                'sentence' => 'Sentenced to death, commuted to twenty-five years at hard labor; released by War Department pardon on November 26, 1920.',
                'incarceration' => [1918], 'release' => [1920, 11, 26],
            ],
            [
                'name' => 'Gordon Hirabayashi', 'first' => 'Gordon', 'last' => 'Hirabayashi',
                'race' => 'Asian', 'state' => 'Washington', 'era' => '1940s',
                'birth' => [1918, 4, 23], 'death' => [2012, 1, 2],
                'ideologies' => ['Civil rights', 'Pacifism', 'Anti-racism'],
                'affiliation' => ['Quaker'],
                'bio' => 'Gordon Kiyoshi Hirabayashi (1918–2012) was a Japanese American student at the University of Washington and a Quaker who, in 1942, openly defied the wartime curfew and exclusion orders imposed on people of Japanese ancestry, turning himself in to the FBI. Convicted of curfew violation and of refusing to report for removal, he was sentenced to ninety days, which he asked to serve at an outdoor road camp — hitchhiking to Arizona to do so. The Supreme Court upheld his conviction in Hirabayashi v. United States (1943); he was later imprisoned again for refusing the loyalty questionnaire and the draft. His convictions were finally vacated in 1987 through a writ of coram nobis.',
                'charges' => 'Violating the WWII military curfew and exclusion orders targeting Japanese Americans (1942), and later refusing the loyalty questionnaire and the draft.',
                'convicted' => 'Yes — convicted in 1942; upheld in Hirabayashi v. United States (1943). The convictions were vacated in 1987 (coram nobis).',
                'sentence' => 'Ninety days at a road camp for the 1942 convictions, and a further prison term for his later draft resistance.',
                'incarceration' => [1942],
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
                    'gender' => $p['gender'] ?? 'Male',
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

                $this->info('Filled: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
            }

            // Ben Salmon was duplicated as a second record, "Benjamin Joseph
            // Salmon" (his full name, kept here as the canonical record's aka).
            // Fold that duplicate into the filled ben-salmon record and delete
            // it. Idempotent — no-op once the duplicate is gone.
            $canon = Prisoner::withUnderReview()->where('slug', 'ben-salmon')->first();
            $dup = Prisoner::withUnderReview()->where('slug', 'benjamin-joseph-salmon')->first();
            if ($canon && $dup && $dup->id !== $canon->id) {
                $dup->cases()->delete();
                $dup->delete();
                $this->info('Deleted duplicate "Benjamin Joseph Salmon" (merged into ben-salmon).');
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
