<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Second batch of political prisoners surfaced by reading The Black Panther
 * newspaper (1967-1970) page by page — people the paper organized around who
 * were missing from the database:
 *
 *  - The other April 6, 1968 Oakland-shootout co-defendants. An Alameda County
 *    grand jury indicted eight Panthers (two counts of attempted murder + two of
 *    assault with a deadly weapon on a police officer). Already recorded:
 *    Eldridge Cleaver, David Hilliard, Warren Wells (and Bobby Hutton, killed).
 *    Added here: Charles Bursey, Donnell Lankford, Terry Cotton, Wendell Wade.
 *  - Mark Comfort — the Oakland organizer (Oakland Direct Action Committee) who
 *    helped bring the Party to the May 2, 1967 armed protest at the California
 *    State Capitol and was jailed for it, after an earlier jailing for the 1964
 *    Oakland Tribune blockade.
 *
 * Sourced primarily from the Party's own "Partial List of Political Prisoners"
 * (Feb 21, 1970) and the legal record (In re Wells, People v. Bursey). Honest
 * gaps: Wade's individual disposition and the four shootout co-defendants'
 * birth/death dates and sentence lengths are undocumented and omitted rather
 * than guessed; Comfort's 1964 arrest is dated only to the year. Idempotent:
 * skips any name already present.
 */
final class AddBlackPantherPapers2Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-2';

    protected $description = 'Add a second batch of Black Panther newspaper-era political prisoners (1967-1970)';

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
                    if (! empty($c['institution_name'])) {
                        $inst = Institution::firstOrCreate(
                            ['name' => $c['institution_name']],
                            ['city' => $c['institution_city'] ?? null, 'state' => $c['institution_state'] ?? null],
                        );
                        $c['institution_id'] = $inst->id;
                    }
                    unset($c['institution_name'], $c['institution_city'], $c['institution_state']);
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
        // Shared scaffold for the April 6, 1968 Oakland-shootout co-defendants.
        $shootout = function (string $name, string $first, string $last, string $desc, string $convicted = ''): array {
            $case = [
                'institution_name' => 'Alameda County Jail',
                'institution_city' => 'Oakland',
                'institution_state' => 'California',
                'charges' => 'Two counts of attempted murder and two counts of assault with a deadly weapon upon a police officer (Alameda County grand jury indictment arising from the April 6, 1968 West Oakland gun battle)',
                'arrest_date' => '1968-04-06',
            ];
            if ($convicted !== '') {
                $case['convicted'] = $convicted;
            }

            return [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1960s',
                'ideologies' => ['Black Power', 'Black nationalism', 'Revolutionary socialism'],
                'affiliation' => ['Black Panther Party'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => $desc,
                'cases' => [$case],
            ];
        };

        return [
            [
                'name' => 'Mark Comfort',
                'first_name' => 'Mark',
                'middle_name' => 'Everett',
                'last_name' => 'Comfort',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1960s',
                'birthdate' => '1934-02-06',
                'death_date' => '1976-11-06',
                'ideologies' => ['Black Power', 'Black nationalism'],
                'affiliation' => ['Oakland Direct Action Committee', 'Black Panther Party', 'Student Nonviolent Coordinating Committee'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Mark Everett Comfort was one of Oakland\'s most prominent grassroots Black organizers of the early-to-mid 1960s and a direct precursor to the Black Panther Party. He founded the Oakland Direct Action Committee in 1965 and ran a "Black Panther Project" under it. After learning of the police killing of Denzil Dowell, he helped bring the Black Panther Party for Self-Defense to Sacramento and, at Bobby Seale\'s request, was among those arrested with Huey Newton and Bobby Seale at the armed protest at the California State Capitol on May 2, 1967, for which he was sentenced to six months and served 44 days at the Santa Rita Prison Farm before Supreme Court Justice William O. Douglas granted a stay. He had earlier been jailed about 30 days for blockading the Oakland Tribune building in a 1964 anti-discrimination protest, which prompted a "Free Mark Comfort" campaign. He later did SNCC security work in Lowndes County, Alabama, and died in 1976.',
                'cases' => [
                    [
                        'institution_name' => 'Santa Rita Prison Farm',
                        'institution_city' => 'Dublin',
                        'institution_state' => 'California',
                        'charges' => 'Misdemeanor stemming from the May 2, 1967 armed Black Panther protest at the California State Capitol in Sacramento (disrupting the legislative session); arrested alongside Huey Newton and Bobby Seale',
                        'arrest_date' => '1967-05-02',
                        'convicted' => 'Yes',
                        'sentence' => 'Six months; served 44 days at the Santa Rita Prison Farm before Supreme Court Justice William O. Douglas granted a stay of sentence',
                    ],
                    [
                        'institution_name' => 'Alameda County Jail',
                        'institution_city' => 'Oakland',
                        'institution_state' => 'California',
                        'charges' => 'Blockading the Oakland Tribune building during an Ad Hoc Committee anti-discrimination protest (1964)',
                        'convicted' => 'Yes',
                        'sentence' => 'About 30 days in jail (1964)',
                    ],
                ],
            ],
            $shootout('Charles Bursey', 'Charles', 'Bursey',
                'Charles Bursey was a member of the Black Panther Party in Oakland, later well known for serving meals in the Party\'s Free Breakfast for Children Program. He was one of the Panthers present during the April 6, 1968 confrontation with Oakland police — two days after Dr. King\'s assassination — in which police killed 17-year-old Bobby Hutton and wounded Eldridge Cleaver and Warren Wells. Indicted on two counts of attempted murder and two counts of assault with a deadly weapon, he was held in the Alameda County Jail and then convicted on all four counts on August 7, 1969. The Black Panther newspaper called him "a black political prisoner in the Alameda County Jail."',
                'Yes — found guilty by a jury on all four counts on August 7, 1969'),
            $shootout('Donnell Lankford', 'Donnell', 'Lankford',
                'Donnell Lankford was a member of the Black Panther Party arrested in the April 6, 1968 gun battle between Panthers and Oakland police in which 17-year-old Bobby Hutton was killed. Indicted on two counts of attempted murder and two counts of assault with a deadly weapon, he was tried jointly with Terry Cotton (People v. Lankford and Cotton, Alameda County Superior Court No. 42287); both were convicted of the assault charges and acquitted of attempted murder. His case was later cited in In re Wells as part of the prosecution\'s pattern of striking Black jurors — no Black person served on any of the three related Panther juries.',
                'Yes — convicted of assault with a deadly weapon; acquitted of attempted murder'),
            $shootout('Terry Cotton', 'Terry', 'Cotton',
                'Terry Cotton was one of the earliest members of the Black Panther Party, in the same founding-era cohort as Huey P. Newton and Bobby Seale. By his own account he "was with Lil\' Bobby Hutton when he was murdered by the Oakland Police Department on April 6, 1968." Indicted on two counts of attempted murder and two counts of assault with a deadly weapon, he was tried jointly with Donnell Lankford (People v. Lankford and Cotton, No. 42287); both were convicted of assault and acquitted of attempted murder. He remained active in Bay Area revolutionary circles for decades after his release.',
                'Yes — convicted of assault with a deadly weapon; acquitted of attempted murder'),
            $shootout('Wendell Wade', 'Wendell', 'Wade',
                'Wendell Wade (also spelled Wendel Wade in Party publications) was a member of the Black Panther Party arrested in connection with the April 6, 1968 confrontation with Oakland police in which 17-year-old Bobby Hutton was killed and Eldridge Cleaver and Warren Wells were wounded. He was among the group indicted by the Alameda County grand jury on two counts of attempted murder and two counts of assault with a deadly weapon, and was listed by name among the April 6, 1968 defendants in the Black Panther newspaper\'s February 1970 roster of political prisoners. The disposition of his individual case is not documented in available sources.'),
        ];
    }
}
