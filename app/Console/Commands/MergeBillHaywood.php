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
 * Merges the two duplicate "Big Bill" Haywood records ("Bill Haywood" and
 * "William Dudley Haywood") into one entry displayed as "Bill Haywood", with the
 * legal name on the first/middle/last fields (William / Dudley / Haywood) and no
 * aka. Keeps the fuller bio and affiliation list, and rebuilds his cases as the
 * de-duplicated union — the 1906–07 Steunenberg pretrial detention (acquitted;
 * with its buggy dates corrected) and the 1918 Espionage Act conviction served
 * at Leavenworth — dropping a thin duplicate stub. The unrelated Scottsboro
 * defendant "Haywood Patterson" is untouched. Idempotent.
 */
final class MergeBillHaywood extends Command
{
    protected $signature = 'prisoners:merge-bill-haywood';

    protected $description = 'Merge the two Bill Haywood duplicate records into one';

    public function handle(): int
    {
        $candidates = Prisoner::withUnderReview()
            ->whereIn('name', ['Bill Haywood', 'William Dudley Haywood'])
            ->get();
        if ($candidates->isEmpty()) {
            $this->error('No Bill/William Dudley Haywood records found.');

            return self::FAILURE;
        }
        $primary = $candidates->firstWhere('name', 'Bill Haywood') ?? $candidates->first();

        $ada = Institution::firstOrCreate(['name' => 'Ada County Jail'], ['city' => 'Boise', 'state' => 'Idaho'])->id;
        $leav = Institution::firstOrCreate(['name' => 'United States Penitentiary, Leavenworth'], ['city' => 'Leavenworth', 'state' => 'Kansas'])->id;

        $bio = 'William Dudley "Big Bill" Haywood was the founding general secretary-treasurer of the Industrial Workers of the World (IWW, the Wobblies) and one of the most important figures in U.S. radical labor history. A one-eyed former Western miner, he led the Western Federation of Miners and was acquitted in the 1907 Steunenberg murder trial, defended by Clarence Darrow. In September 1917 federal agents raided IWW offices across the country and arrested its leaders; the resulting mass federal trial in Chicago — United States v. Haywood et al., before Judge Kenesaw Mountain Landis — was the largest political prosecution in U.S. history at that point. After a four-month trial, on August 17, 1918, the jury convicted 101 of the 113 IWW defendants, and Haywood was sentenced on August 30, 1918 to twenty years in federal prison and a $20,000 fine. While free on $30,000 bail pending appeal in March 1921, Haywood jumped bail and fled to Soviet Russia, where he died on May 18, 1928; his ashes were divided between the Kremlin Wall Necropolis in Moscow and the Haymarket Martyrs Monument in Forest Park, Illinois.';

        DB::transaction(function () use ($candidates, $primary, $bio, $ada, $leav) {
            foreach ($candidates as $dup) {
                if ($dup->id !== $primary->id) {
                    $dup->cases()->delete();
                    $dup->delete();
                    $this->info('Merged duplicate: '.$dup->name.' (deleted).');
                }
            }

            $primary->fill([
                'name' => 'Bill Haywood',
                'first_name' => 'William',
                'middle_name' => 'Dudley',
                'last_name' => 'Haywood',
                'aka' => null,
                'gender' => 'Male',
                'race' => 'White',
                'state' => 'Idaho',
                'era' => '1910s',
                'affiliation' => ['Industrial Workers of the World (IWW)', 'Western Federation of Miners', 'Socialist Party of America'],
                'description' => $bio,
                'in_custody' => false,
                'released' => false,
                'in_exile' => true,
            ]);
            $primary->setPartialDate('birthdate', 1869, 2, 4);
            $primary->setPartialDate('death_date', 1928, 5, 18);
            $primary->save();

            $primary->cases()->delete();

            // 1906–07 Steunenberg trial — pretrial detention, then acquitted.
            $c1 = new PrisonerCase(['prisoner_id' => $primary->id]);
            $c1->fill([
                'prisoner_id' => $primary->id,
                'institution_id' => $ada,
                'charges' => 'Conspiracy to murder former Idaho Governor Frank Steunenberg, killed by a bomb at his Caldwell home on December 30, 1905 — the prosecution resting on Harry Orchard\'s Pinkerton-obtained confession naming Haywood, Charles Moyer, and George Pettibone of the Western Federation of Miners. Pinkerton agents seized the three in Denver on February 17, 1906 and rendered them to Idaho without normal extradition.',
                'convicted' => 'No — acquitted by the jury at the Ada County District Court, Boise, on July 29, 1907 (defended by Clarence Darrow).',
                'sentence' => 'Held without bail in the Ada County Jail, Boise, from February 17, 1906 to July 29, 1907 — about 528 days of pretrial detention — then acquitted.',
            ]);
            $c1->setPartialDate('arrest_date', 1906, 2, 17);
            $c1->setPartialDate('incarceration_date', 1906, 2, 17);
            $c1->setPartialDate('release_date', 1907, 7, 29);
            $c1->save();

            // 1918 Espionage Act conviction — served at Leavenworth, then jumped bail.
            $c2 = new PrisonerCase(['prisoner_id' => $primary->id]);
            $c2->fill([
                'prisoner_id' => $primary->id,
                'institution_id' => $leav,
                'charges' => 'Conspiracy to violate the Espionage Act of 1917 and the Selective Service Act — as lead defendant in United States v. Haywood et al. (N.D. Ill.), the 1918 Chicago IWW mass trial before Judge Kenesaw Mountain Landis (101 of 113 IWW defendants convicted), for conspiring to obstruct the draft and the war effort.',
                'convicted' => 'Yes — convicted by the federal jury on August 17, 1918; sentenced August 30, 1918.',
                'sentence' => 'Twenty years in federal prison and a $20,000 fine. Imprisoned at the U.S. Penitentiary at Leavenworth, then released on $30,000 bail pending appeal; when the appeal failed he jumped bail in March 1921 and fled to Soviet Russia, where he died in 1928.',
            ]);
            $c2->setPartialDate('arrest_date', 1917, 9, 4);
            $c2->setPartialDate('sentenced_date', 1918, 8, 30);
            $c2->setPartialDate('incarceration_date', 1918, 8, 29);
            $c2->setPartialDate('release_date', 1921, 3, 31);
            $c2->save();

            // Keep the portrait attached.
            $src = database_path('data/photos/bill-haywood.jpg');
            if (is_file($src)) {
                Storage::disk('public')->makeDirectory('prisoners');
                Storage::disk('public')->put('prisoners/bill-haywood.jpg', (string) file_get_contents($src));
                $primary->photo = 'prisoners/bill-haywood.jpg';
                $primary->save();
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Merged into one "Bill Haywood" record (slug: '.$primary->slug.').');

        return self::SUCCESS;
    }
}
