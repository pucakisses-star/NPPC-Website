<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Adds the Native American / AIM-era political prisoners surfaced as genuine
 * gaps by a roster sweep. The database was already deep on this category —
 * Leonard Peltier, Russell Means, Dennis Banks, Carter Camp, Leonard Crow
 * Dog, Bob Robideau, Dino Butler, the Standing Rock DAPL convicts (Red Fawn
 * Fallis, Little Feather, Rattler, Dion Ortiz), Oso Blanco, Eddie Hatcher,
 * and Norma Jean Croy were all present. These four are the real gaps:
 *
 *  - David Sohappy — Yakama/Wanapum treaty fishing-rights elder jailed in the
 *    1980s "Salmonscam" Lacey Act sting; died 1991.
 *  - Richard Marshall — AIM "Oglala political prisoner," 24 years for the
 *    Montileaux killing his defenders called an FBI frame-up (Myrtle Poor
 *    Bear, the discredited Peltier-case witness); paroled 2000.
 *  - Patrick "Hooty" Croy — Shasta; death row, conviction overturned, then
 *    acquitted at his 1990 retrial after ~12 years (7 on death row).
 *  - Bear Lincoln — Wailacki; held without bail two years, acquitted in 1997
 *    of a Mendocino County deputy's killing.
 *
 * EXCLUDED after research: Clyde Bellecourt (imprisonments were for robbery
 * and a 1985 LSD-distribution conviction — not political), Stan Holder (no
 * substantial prison time), and DAPL defendants James White / Brennan
 * Nastacio (could not confirm they served prison time).
 *
 * Idempotent: prisoner:add refuses duplicate names.
 */
final class AddNativePrisoners extends Command {
    protected $signature = 'prisoners:add-native-prisoners';
    protected $description = 'Add the Native American / AIM-era political prisoners missing from the roster sweep';

    public function handle(): int {
        $prisoners = [
            [
                'name' => 'David Sohappy',
                'first_name' => 'David',
                'last_name' => 'Sohappy',
                'aka' => 'David Sohappy Sr.; Tucknashut',
                'description' => 'David Sohappy Sr. (1925-1991), known as Tucknashut, was a Wanapum and Yakama fisherman and religious leader who became a central figure in the Native treaty fishing-rights struggle on the Columbia River. Living at Cooks Landing, he fished according to treaty rights and refused to submit to state regulation, and was the named plaintiff in the landmark case Sohappy v. Smith (1969). In 1981 and 1982 federal agents ran an undercover sting known as Salmonscam, luring Columbia River Indians into selling salmon caught out of season. Sohappy, his son, and three other Yakama tribal members were convicted in 1983 under the federal Lacey Act, and he was sentenced to five years in prison — a term widely condemned as grossly disproportionate. He suffered a debilitating stroke while incarcerated and was released only on May 18, 1988, after U.S. Senators Daniel Inouye and Dan Evans intervened. He died three years later. Sohappy became an international symbol of the fight to defend Native treaty and human rights.',
                'race' => 'Native American',
                'gender' => 'Male',
                'state' => 'Washington',
                'ideologies' => ['Indigenous treaty rights', 'Native sovereignty'],
                'era' => '1980s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'charges' => 'Selling salmon caught out of season in violation of the federal Lacey Act (treaty fishing-rights prosecution via the Salmonscam sting)',
                    'convicted' => 'Convicted 1983',
                    'sentence' => 'Five years in federal prison; released May 18, 1988 after Senate intervention',
                    'release_date' => '1988-05-18',
                ]],
            ],
            [
                'name' => 'Richard Marshall',
                'first_name' => 'Richard',
                'last_name' => 'Marshall',
                'description' => 'Richard Marshall was an Oglala Lakota member of the American Indian Movement known to supporters as the Oglala political prisoner. In 1976 he was convicted of the 1975 shooting death of Martin Montileaux in a bar in Scenic, South Dakota. The prosecution relied in part on Myrtle Poor Bear — the same witness whose fabricated statements were later discredited in the case of Leonard Peltier — and Marshall and AIM maintained the prosecution was a frame-up meant to neutralize an AIM leader. He served twenty-four years in prison before being paroled in 2000. In 2008 federal prosecutors indicted Marshall in the killing of AIM activist Anna Mae Aquash, but he was acquitted at trial in 2010.',
                'race' => 'Native American',
                'gender' => 'Male',
                'state' => 'South Dakota',
                'ideologies' => ['Native sovereignty', 'Indigenous resistance'],
                'affiliation' => ['American Indian Movement'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'charges' => 'Murder of Martin Montileaux (1975)',
                    'convicted' => 'Convicted 1976; Marshall and AIM maintained the case was a frame-up built on the discredited witness Myrtle Poor Bear',
                    'sentence' => 'Life sentence; served approximately 24 years before parole in 2000',
                ]],
            ],
            [
                'name' => 'Patrick Croy',
                'first_name' => 'Patrick',
                'last_name' => 'Croy',
                'aka' => 'Patrick Hooty Croy',
                'description' => 'Patrick Hooty Croy was a Shasta Indian sentenced to death for the 1978 shooting death of a Yreka, California police officer following a confrontation that escalated into a hillside shootout. Croy, his sister Norma Jean Croy, and several cousins were prosecuted; Patrick was convicted of first-degree murder in 1979 and sent to death row. In 1985 the California Supreme Court overturned his conviction. At his 1990 retrial, defended by attorney Tony Serra with a landmark defense rooted in the history of violence against Native people, Croy testified that he had acted in self-defense and was acquitted — after roughly twelve years in prison, seven of them on death row. His sister Norma Jean, also convicted in the case, was not released until 1997.',
                'race' => 'Native American',
                'gender' => 'Male',
                'state' => 'California',
                'ideologies' => ['Native sovereignty', 'Indigenous resistance'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'charges' => 'First-degree murder of a Yreka, California police officer (1978)',
                    'convicted' => 'Convicted 1979 and sentenced to death; conviction overturned 1985; acquitted at 1990 retrial on self-defense grounds',
                    'sentence' => 'Approximately twelve years imprisoned (seven on death row) before acquittal',
                ]],
            ],
            [
                'name' => 'Bear Lincoln',
                'first_name' => 'Eugene',
                'last_name' => 'Lincoln',
                'aka' => 'Eugene Bear Lincoln',
                'description' => 'Eugene Bear Lincoln, a member of the Wailacki of the Round Valley Indian Tribes, was charged with murder after an April 1995 shootout on the Round Valley Reservation near Covelo, California, in which Mendocino County Sheriff Deputy Bob Davis and Lincoln\'s friend Leonard Acorn Peters were killed. Maintaining that he fired in self-defense after deputies ambushed the two men, Lincoln turned himself in at the San Francisco law office of attorney Tony Serra. He was held without bail in the Mendocino County jail for two years. In 1997, after a trial that drew national attention to law-enforcement treatment of Native people in Mendocino County, a jury acquitted Lincoln of murder. He was later, in 2000, convicted of an unrelated charge of firing into reservation homes.',
                'race' => 'Native American',
                'gender' => 'Male',
                'state' => 'California',
                'ideologies' => ['Native sovereignty', 'Indigenous resistance'],
                'era' => '1990s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'charges' => 'Murder of a Mendocino County sheriff deputy (1995)',
                    'convicted' => 'Acquitted of murder at his 1997 trial',
                    'sentence' => 'Held without bail approximately two years pending trial; acquitted 1997',
                    'imprisoned_for_days' => 730,
                ]],
            ],
        ];

        $added = 0;
        $skipped = 0;
        foreach ($prisoners as $p) {
            $this->line("\n— {$p['name']} —");
            $code = Artisan::call('prisoner:add', ['json' => json_encode($p, JSON_UNESCAPED_UNICODE)]);
            $this->line(trim(Artisan::output()));
            if ($code === self::SUCCESS) {
                $added++;
            } else {
                $skipped++;
            }
        }

        $this->info("\nDone — added {$added}, skipped {$skipped} (already present).");

        return self::SUCCESS;
    }
}
