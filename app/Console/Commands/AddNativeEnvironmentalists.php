<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds the Native American environmentalists found by reading the Wikipedia
 * "Native American environmentalists" category who were arrested/jailed/charged
 * for their land-, water-, and anti-nuclear protest activity and were missing
 * from the database. Civil-disobedience arrests count even where charges were
 * dropped or the disposition is unrecorded; several of these arrests are
 * documented only in news/archival sources, not the subjects' Wikipedia bios.
 *
 * Included: Jasilyn Charger, Klee Benally, Regina Brave, Dallas Goldtooth,
 * Grace Thorpe, Sharon Day.
 *
 * Excluded after vetting (advocacy/elders with no documented personal arrest):
 * Corbin Harney (he convened the Nevada Test Site gatherings; the line-crossers
 * were arrested, not him — confirmed across his UNLV oral history and the Rozsa
 * scholarship), JoAnn Tall, Faith Spotted Eagle, Betty Osceola, Sarah James,
 * Annie Alowa, Margaret Behan, Jewell James, Beatrice Long Visitor Holy Dance.
 * Borderline-excluded: Roberta Blackgoat (only an uncited "jailed numerous
 * times" claim; the dated 2001 Big Mountain arrests were of her co-resisters).
 * Idempotent.
 */
final class AddNativeEnvironmentalists extends Command
{
    protected $signature = 'prisoners:add-native-environmentalists';

    protected $description = 'Add jailed/charged Native American environmentalists from the Wikipedia category that were missing';

    public function handle(): int
    {
        $added = 0;
        $skipped = 0;

        foreach ($this->records() as $r) {
            if (Prisoner::withUnderReview()->where('name', $r['name'])->exists()) {
                $this->warn("Exists, skipping: {$r['name']}");
                $skipped++;

                continue;
            }

            DB::transaction(function () use ($r) {
                $cases = $r['cases'] ?? [];
                unset($r['cases']);
                $prisoner = Prisoner::create($r);
                foreach ($cases as $c) {
                    $c['prisoner_id'] = $prisoner->id;
                    PrisonerCase::create($c);
                }
            });

            $this->info("Added: {$r['name']}");
            $added++;
        }

        $this->info("\nDone. Added={$added} Skipped={$skipped}");

        return self::SUCCESS;
    }

    private function records(): array
    {
        return [
            [
                'name' => 'Jasilyn Charger',
                'first_name' => 'Jasilyn',
                'last_name' => 'Charger',
                'gender' => 'Female',
                'race' => 'Native American',
                'birthdate' => '1996-05-20',
                'state' => 'South Dakota',
                'era' => '2020s',
                'ideologies' => ['Indigenous Sovereignty', 'Environmental'],
                'affiliation' => ['Sacred Stone Camp', 'One Mind Youth Movement'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Jasilyn Charger (born 1996) is a Cheyenne River Sioux water protector and land defender who, as a teenager, co-founded the One Mind Youth Movement and the Sacred Stone Camp in April 2016 — helping ignite the Standing Rock resistance to the Dakota Access Pipeline. She later organized against the Keystone XL pipeline, and on November 21, 2020 she locked herself to a KXL pump station near the Cheyenne River Reservation in an act of nonviolent civil disobedience. She was arrested and charged with Class 1 misdemeanor trespassing, which carried up to a year in jail; in June 2021 she pleaded no contest and received probation and fines rather than jail time, the case resolving as the KXL project was officially canceled.',
                'cases' => [[
                    'charges' => 'Class 1 misdemeanor trespassing — locking herself to a Keystone XL pipeline pump station in nonviolent civil disobedience near the Cheyenne River Reservation, South Dakota',
                    'arrest_date' => '2020-11-21',
                    'convicted' => 'Pleaded no contest (June 24, 2021)',
                    'sentence' => 'Six months\' probation and about $518 in fines; no jail time (charge carried up to one year)',
                ]],
            ],
            [
                'name' => 'Klee Benally',
                'first_name' => 'Klee',
                'last_name' => 'Benally',
                'gender' => 'Male',
                'race' => 'Native American',
                'birthdate' => '1975-10-06',
                'death_date' => '2023-12-30',
                'state' => 'Arizona',
                'era' => '2010s',
                'ideologies' => ['Indigenous Sovereignty', 'Environmental'],
                'affiliation' => ['Indigenous Action'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Klee Benally (1975–2023) was a Diné (Navajo) environmental and Indigenous-rights activist, musician (guitarist in the family band Blackfire), and filmmaker from Black Mesa, Arizona, based in Flagstaff. He spent decades in frontline civil disobedience to protect the sacred San Francisco Peaks (Dook\'o\'oosííd) from Arizona Snowbowl\'s wastewater-snowmaking, and to oppose uranium and pumice mining on Native land. He was arrested and prosecuted for these actions — including chaining himself to a Snowbowl pipeline excavator in 2011 and facing federal charges in 2012 — and authored a book on Indigenous anarchism, "No Spiritual Surrender." He died December 30, 2023, at age 48.',
                'cases' => [
                    [
                        'charges' => 'Trespassing and disorderly conduct — chaining himself to an excavator digging the Snowbowl wastewater-snowmaking pipeline trench on the sacred San Francisco Peaks, Coconino County, Arizona',
                        'arrest_date' => '2011-08-13',
                        'convicted' => 'Tried in Coconino Justice Court (trial June 12, 2012); the judge found his motivations "genuine and heartfelt"',
                        'sentence' => 'Community service and $99.24 restitution to Arizona Snowbowl; no jail time',
                    ],
                    [
                        'charges' => 'Two federal counts of threatening, resisting, intimidating, or interfering with U.S. Forest Service officers — a protest at the Flagstaff Forest Service office over sewage-effluent snowmaking on the San Francisco Peaks (September 21, 2012; charges filed December 6, 2012)',
                        'convicted' => 'Charged in federal District Court (Flagstaff); he turned himself in and was released pending trial. Each count carried up to six months\' imprisonment',
                        'sentence' => 'Disposition not documented in available sources',
                    ],
                ],
            ],
            [
                'name' => 'Regina Brave',
                'first_name' => 'Regina',
                'last_name' => 'Brave',
                'gender' => 'Female',
                'race' => 'Native American',
                'state' => 'South Dakota',
                'era' => '2010s',
                'ideologies' => ['Indigenous Sovereignty', 'Environmental'],
                'affiliation' => ['American Indian Movement'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Regina Brave (born 1941) is an Oglala Lakota elder and activist from the Pine Ridge Reservation, South Dakota. In 1973, at age 32, she was an armed front-line participant in the American Indian Movement\'s 71-day occupation of Wounded Knee, where an AP photograph of her holding a rifle was published nationally. Decades later she became a prominent elder ("Grandma Regina") of the 2016–2017 Standing Rock / Dakota Access Pipeline resistance, and on February 23, 2017 she was among the last holdouts arrested when militarized law enforcement cleared the Oceti Sakowin camp — reported as the last person to leave. She was removed and detained but not charged, as authorities chose not to prosecute a respected elder.',
                'cases' => [[
                    'charges' => 'Arrested during the eviction of the Oceti Sakowin camp at the Standing Rock / Dakota Access Pipeline protests (Cannon Ball, North Dakota) — one of the last holdouts to refuse to vacate',
                    'arrest_date' => '2017-02-23',
                    'convicted' => 'Detained and removed; Morton County declined to file charges, citing her status as a respected elder',
                    'sentence' => 'None (no charges filed; released)',
                ]],
            ],
            [
                'name' => 'Dallas Goldtooth',
                'first_name' => 'Dallas',
                'last_name' => 'Goldtooth',
                'gender' => 'Male',
                'race' => 'Native American',
                'birthdate' => '1983-05-03',
                'state' => 'Minnesota',
                'era' => '2020s',
                'ideologies' => ['Indigenous Sovereignty', 'Environmental'],
                'affiliation' => ['Indigenous Environmental Network'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Dallas Goldtooth (born 1983) is a Mdewakanton Dakota and Diné environmental activist, comedian, and actor enrolled in the Lower Sioux Indian Community of Minnesota. For years he served as the "Keep It in the Ground" campaign organizer for the Indigenous Environmental Network, helping lead Indigenous resistance to the Keystone XL, Dakota Access, and Line 3 pipelines, and he is a founding member of the "1491s" comedy group and a cast member on FX\'s "Reservation Dogs." On September 12, 2023 he was among roughly 35 people arrested for civil disobedience outside the White House while demanding clemency for imprisoned activist Leonard Peltier.',
                'cases' => [[
                    'charges' => 'Blocking a sidewalk / Pennsylvania Avenue in an act of civil disobedience at the White House, demanding clemency for Leonard Peltier (Washington, D.C.)',
                    'arrest_date' => '2023-09-12',
                    'convicted' => 'Arrested by U.S. Park Police; issued a citation',
                    'sentence' => '$50 citation; released, no jail time',
                ]],
            ],
            [
                'name' => 'Grace Thorpe',
                'first_name' => 'Grace',
                'middle_name' => 'Frances',
                'last_name' => 'Thorpe',
                'aka' => 'No Ten O Quah ("Wind Woman")',
                'gender' => 'Female',
                'race' => 'Native American',
                'birthdate' => '1921-12-10',
                'death_date' => '2008-04-01',
                'state' => 'California',
                'era' => '1970s',
                'ideologies' => ['Indigenous Sovereignty', 'Environmental'],
                'affiliation' => ['Indians of All Tribes', 'National Environmental Coalition of Native Americans'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Grace Thorpe (1921–2008) was a Sac and Fox Nation activist, World War II veteran (a WAC corporal who earned a Bronze Star in the New Guinea campaign), and the youngest daughter of the Olympic athlete Jim Thorpe. A prominent figure in the Red Power movement, she served as publicist and negotiator during the 1969–71 occupation of Alcatraz Island and joined subsequent land-reclamation occupations. She was arrested and charged with trespassing on June 6, 1970 during the Pit River Tribe\'s takeover of Pacific Gas & Electric land at Big Bend, California (one of 34 arrested). In her later decades she became a nationally known anti-nuclear activist, founding the National Environmental Coalition of Native Americans (NECONA) and pressuring tribes to reject federal nuclear-waste storage on Native lands.',
                'cases' => [[
                    'charges' => 'Trespassing — a sit-in during the Pit River Tribe\'s occupation of Pacific Gas & Electric land at Big Bend, Shasta County, California, to reclaim ancestral land taken illegally during the 1850s Gold Rush (34 people arrested)',
                    'arrest_date' => '1970-06-06',
                    'convicted' => 'Arrested and charged; case litigated in Sacramento Municipal Court (a February 1971 Superior Court petition contested the defendants\' access to a public defender)',
                    'sentence' => 'Not documented in available sources',
                ]],
            ],
            [
                'name' => 'Sharon Day',
                'first_name' => 'Sharon',
                'middle_name' => 'M.',
                'last_name' => 'Day',
                'gender' => 'Female',
                'race' => 'Native American',
                'state' => 'Minnesota',
                'era' => '1990s',
                'ideologies' => ['Indigenous Sovereignty', 'Environmental'],
                'affiliation' => ['Indigenous Peoples Task Force'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Sharon M. Day (born 1951) is an Ojibwe activist, artist, and writer of the Bois Forte Band and the founder and longtime executive director of the Indigenous Peoples Task Force in Minnesota. A nationally recognized water protector, she co-launched the Mother Earth Water Walks with elder Josephine Mandamin in 2003 and has since led the Nibi (Water) Walks — long ceremonial walks along major rivers. In the late 1990s, before the walks, she was arrested several times and jailed for days for acts of civil disobedience defending the sacred Coldwater Spring in Minneapolis from the Highway 55 reroute and development.',
                'cases' => [[
                    'charges' => 'Multiple civil-disobedience arrests protecting the sacred Coldwater Spring from the Highway 55 reroute and development, south Minneapolis, Minnesota (late 1990s)',
                    'convicted' => 'Arrested several times for civil disobedience',
                    'sentence' => 'Served days in jail; specific charges and disposition not documented in available sources',
                ]],
            ],
        ];
    }
}
