<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Makes James McInerney's record complete and authoritative. Sources confirm
 * the Centralia James McInerney was "an Irish immigrant who was a veteran of the
 * Everett Massacre" — the same man booked "Jim Mack" (IWW prisoner no. 4888) in
 * 1916. This command sets his identity/dates and rebuilds his cases as his two
 * imprisonments:
 *   1. Everett Massacre (1916): held ~6 months in the Snohomish County jail on
 *      murder charges that were dropped after Thomas Tracy's acquittal.
 *   2. Centralia (1919–1930): arrested Nov 11, 1919; tried at Montesano
 *      (Jan 26, 1920); convicted of second-degree murder Mar 13, 1920; sentenced
 *      Apr 5, 1920 to 25–40 years; conviction upheld Apr 14, 1921; never
 *      released — died at the Washington State Penitentiary on Aug 13, 1930.
 *
 * Because his record is also touched by other loaders (add-labor-defender-
 * famous-cases and the rebel-girl roster), run this LAST; it rebuilds both cases
 * to the authoritative state. Idempotent.
 */
final class FillJamesMcInerneyEverett extends Command
{
    protected $signature = 'prisoners:fill-james-mcinerney-everett';

    protected $description = 'Rebuild James McInerney with his Everett (1916) and Centralia (1919–1930) cases';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()
            ->where('name', 'James McInerney')
            ->get()
            ->first(fn ($p) => str_contains((string) $p->description, 'Centralia')
                || str_contains((string) $p->description, 'Everett')
                || in_array('Industrial Workers of the World (IWW)', (array) $p->affiliation, true))
            ?? new Prisoner(['name' => 'James McInerney']);

        $snohomish = Institution::firstOrCreate(['name' => 'Snohomish County Jail'], ['city' => 'Everett', 'state' => 'Washington'])->id;
        $walla = Institution::firstOrCreate(['name' => 'Washington State Penitentiary'], ['city' => 'Walla Walla', 'state' => 'Washington'])->id;

        $bio = 'James McInerney (1886–1930) was an Irish-born Wobbly — a native of Scariff, County Clare — who was a defendant in two major Industrial Workers of the World cases. Booked as "Jim Mack" (IWW prisoner no. 4888), he was among the 74 IWW members jailed on murder charges after the November 5, 1916 Everett Massacre, before those charges were dropped. Three years later he was in the IWW hall in Centralia, Washington on Armistice Day, November 11, 1919, when the hall was attacked; with Mike Sheehan, Ray Becker, and Bert Faulkner he hid in a cold-storage locker and surrendered. Convicted of second-degree murder on March 13, 1920 at Montesano and sentenced to 25 to 40 years, he was imprisoned at the Washington State Penitentiary at Walla Walla (inmate no. 9410), where he died on August 13, 1930 — of meningitis and tuberculosis that the IWW attributed to prison neglect.';

        DB::transaction(function () use ($prisoner, $bio, $snohomish, $walla) {
            $prisoner->fill([
                'name' => 'James McInerney',
                'first_name' => 'James',
                'last_name' => 'McInerney',
                'aka' => null,
                'gender' => 'Male',
                'race' => 'White',
                'state' => 'Washington',
                'era' => '1910s',
                'inmate_number' => '9410',
                'ideologies' => ['Labor Activism'],
                'affiliation' => ['Industrial Workers of the World (IWW)'],
                'description' => $bio,
                'in_custody' => false,
                'released' => false,
                'in_exile' => false,
                'awaiting_trial' => false,
            ]);
            $prisoner->setPartialDate('birthdate', 1886, 8, 15);
            $prisoner->setPartialDate('death_date', 1930, 8, 13);
            $prisoner->save();

            $prisoner->cases()->delete();

            // Case 1 — Everett Massacre, 1916.
            $everett = new PrisonerCase(['prisoner_id' => $prisoner->id]);
            $everett->fill([
                'prisoner_id' => $prisoner->id,
                'institution_id' => $snohomish,
                'charges' => 'First-degree murder — for the deaths of deputies Jefferson Beard and Charles Curtis in the November 5, 1916 Everett Massacre. Held as one of 74 IWW members charged after the shooting (booked as "Jim Mack," IWW prisoner no. 4888).',
                'convicted' => 'No — charged and held for trial; after Thomas H. Tracy\'s acquittal on May 5, 1917, the charges against the remaining Everett defendants were dropped.',
                'sentence' => 'Held in the Snohomish County jail from November 1916 until release in 1917 after the charges were dropped — about six months of pretrial detention.',
            ]);
            $everett->setPartialDate('arrest_date', 1916, 11, 5);
            $everett->setPartialDate('incarceration_date', 1916, 11, 5);
            $everett->setPartialDate('release_date', 1917, 5);
            $everett->save();

            // Case 2 — Centralia, 1919–1930 (died in custody).
            $centralia = new PrisonerCase(['prisoner_id' => $prisoner->id]);
            $centralia->fill([
                'prisoner_id' => $prisoner->id,
                'institution_id' => $walla,
                'charges' => 'Second-degree murder — for the deaths of American Legionnaires during the November 11, 1919 Armistice Day attack on the Centralia, Washington IWW hall. Arrested at the hall (he and three others hid in a cold-storage locker and surrendered) and tried at Montesano with Britt Smith, Ray Becker, Bert Bland, Eugene Barnett, John Lamb, and O. C. Bland.',
                'convicted' => 'Yes — convicted of second-degree murder on March 13, 1920 (trial opened January 26, 1920 at Montesano); sentenced April 5, 1920. The Washington Supreme Court upheld the conviction on April 14, 1921.',
                'sentence' => '25 to 40 years in the state penitentiary. Held from his November 11, 1919 arrest and transferred to the Washington State Penitentiary at Walla Walla (inmate no. 9410) in 1921. Never released — he died in prison on August 13, 1930 (meningitis/tuberculosis, attributed by the IWW to prison neglect).',
            ]);
            $centralia->setPartialDate('arrest_date', 1919, 11, 11);
            $centralia->setPartialDate('incarceration_date', 1919, 11, 11);
            $centralia->setPartialDate('sentenced_date', 1920, 4, 5);
            $centralia->setPartialDate('death_in_custody_date', 1930, 8, 13);
            $centralia->save();

            // Keep/attach his WSP portrait if present and not already set.
            $src = database_path('data/photos/james-mcinerney.jpg');
            if (is_file($src) && empty($prisoner->photo)) {
                Storage::disk('public')->makeDirectory('prisoners');
                Storage::disk('public')->put('prisoners/james-mcinerney.jpg', (string) file_get_contents($src));
                $prisoner->photo = 'prisoners/james-mcinerney.jpg';
                $prisoner->save();
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Rebuilt James McInerney with Everett (1916) and Centralia (1919–1930) cases.');

        return self::SUCCESS;
    }
}
