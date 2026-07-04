<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Political prisoners surfaced by reading The Black Panther newspaper's final
 * volume — Vol. 20 (1980), the nine issues archived at marxists.org (Feb 11 –
 * Sept 1980). The bulk of the volume's prisoner coverage is people already in
 * the database (skipped by name here), among them: Leonard Peltier, Huey P.
 * Newton, David Hilliard, George Jackson, Johnny Spain and the rest of the San
 * Quentin Six (Hugo Pinell, David Johnson, Fleeta Drumgo, Willie Tate, Luis
 * Talamantez), Elmer "Geronimo" Pratt, Nelson & Winnie Mandela, Marcus Garvey,
 * Gary Tyler, Dessie Woods, Ernest "Shujaa" Graham and Eugene Allen, Imari
 * Abubakari Obadele, Tommy Lee Hines, the 1954 Puerto Rican Nationalists
 * (Lolita Lebrón, Rafael Cancel Miranda, Irvin Flores) and Oscar Collazo, and
 * the Loud Hawk / Oregon-dynamite AIM defendants (Dennis Banks, KaMook Banks,
 * Russ Redner, Kenneth Loud Hawk, Anna Mae Aquash).
 *
 * Added here are the clearly-named political prisoners the volume covers who
 * were NOT already recorded:
 *
 *  - Carol Crooks — the Bedford Hills prisoner whose 1974 beating sparked the
 *    "August Rebellion"; the 1980 paper reports her being re-sentenced and
 *    denied a long-overdue parole (Vol. 20 No. 3).
 *  - Ervin Edwards — a Black man in Mobile, Alabama sentenced to death in Feb.
 *    1980 for killing a policeman with the officer's own gun during a struggle
 *    over an unlawful, warrantless arrest; the defense was self-defense
 *    (Vol. 20 No. 2). Conviction later reversed by the Alabama Supreme Court.
 *  - Leonard Alexander — a Black Soledad inmate tried with a dozen others on
 *    murder/assault/conspiracy charges from the Dec. 6, 1979 Whitney Hall riot,
 *    who wrote appealing for defense help (Vol. 20 No. 9).
 *  - Jack Johnson — the first Black world heavyweight champion, imprisoned
 *    under the Mann Act; the paper's C.R. Gibbs "FBI Harassment of Black
 *    Americans" series opens with him as the Bureau's first Black target
 *    (Vol. 20 No. 1). Posthumously pardoned in 2018.
 *  - Walter Sisulu — the ANC leader serving a Rivonia life term on Robben
 *    Island alongside Mandela; guerrillas demanded his release in the April
 *    1980 Booysens attack coverage (Vol. 20 No. 5).
 *  - Joseph Mavi — chairman of the unofficial Black Municipal Workers' Union,
 *    detained under South Africa's Sabotage Act after the August 1980
 *    Johannesburg municipal strike (Vol. 20 No. 8).
 *  - Kim Dae Jung — the South Korean opposition leader a military court
 *    sentenced to death in Sept. 1980 for "sedition" over the Kwangju uprising
 *    (Vol. 20 Nos. 8–9). Sentence commuted; later President of South Korea and
 *    a Nobel Peace laureate.
 *
 * Facts are corroborated against outside sources (court records, the August
 * Rebellion and Rivonia histories, the Loud Hawk litigation, etc.); where the
 * paper and the record differ, the record is followed and the discrepancy
 * noted (e.g. the Edwards killing was Aug. 14, 1979, which the paper misprints
 * as 1978, and court records spell him "Ervin," which the paper spells
 * "Erwin"). Unknown fields are left out rather than guessed.
 *
 * Idempotent: skips any name already present.
 */
final class AddBlackPantherVol20Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-vol20';

    protected $description = 'Add political prisoners from The Black Panther Vol. 20 (1980), the newspaper\'s final volume';

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
        return [
            [
                'name' => 'Carol Crooks',
                'first_name' => 'Carol',
                'last_name' => 'Crooks',
                'gender' => 'Female',
                'race' => 'Black',
                'state' => 'New York',
                'era' => '1970s',
                'ideologies' => ['Prisoners\' rights', 'Black liberation'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Carol Crooks was a Black woman imprisoned at the Bedford Hills Correctional '
                    .'Facility for Women in New York who became a central figure in the 1970s women\'s prison '
                    .'movement. Jailed as a teenager on a first-degree manslaughter conviction and sentenced to '
                    .'up to 15 years in 1972, she sued over being thrown into solitary confinement without notice '
                    .'or a hearing and won a July 1974 ruling establishing incarcerated women\'s right to due '
                    .'process before segregation (Crooks v. Warne). On August 29, 1974, after she was involved in '
                    .'a brief fight, a squad of male guards in riot gear from a nearby prison forced into her cell, '
                    .'beat her, and dragged her out — provoking the "August Rebellion," an uprising by some 200 '
                    .'women who took over parts of the prison in protest of her treatment; the women later won the '
                    .'class-action suit Powell v. Ward. The Black Panther reported in early 1980 that Crooks, after '
                    .'years of fighting conditions at Bedford Hills and serving as a lead plaintiff in suits over '
                    .'illegal segregation transfers and inadequate medical care, had just been sentenced in '
                    .'Westchester to a further three-and-a-half to fifteen years on an in-prison assault charge, '
                    .'and — instead of a long-overdue parole she had been told she had won — was told she must serve '
                    .'three more years before becoming parole-eligible.',
                'cases' => [[
                    'charges' => 'First-degree manslaughter (convicted 1972, sentenced to up to 15 years, incarcerated at '
                        .'Bedford Hills as a teenager). While imprisoned she was, as The Black Panther reported in early '
                        .'1980, sentenced in Westchester County to an additional three-and-a-half to fifteen years on an '
                        .'in-prison assault charge and denied a parole she had been told she had been granted.',
                    'convicted' => 'Yes — first-degree manslaughter (1972); later an additional 3½-to-15-year term for an in-prison assault (1980).',
                    'incarceration_date' => '1972-01-01',
                    'date_precision' => ['incarceration_date' => 'year'],
                    'sentence' => 'Up to 15 years for manslaughter; an additional 3½-to-15-year term imposed in 1980 for an in-prison assault.',
                    'institution_name' => 'Bedford Hills Correctional Facility for Women',
                    'institution_city' => 'Bedford Hills',
                    'institution_state' => 'New York',
                ]],
            ],
            [
                'name' => 'Ervin Edwards',
                'aka' => 'Erwin Edwards',
                'first_name' => 'Ervin',
                'last_name' => 'Edwards',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Alabama',
                'era' => '1970s',
                'ideologies' => ['Self-defense against racist violence'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Ervin Edwards (spelled "Erwin" in The Black Panther) was a Black man in Mobile, '
                    .'Alabama, prosecuted for the death of city patrolman Henry Booth. On August 14, 1979 (the paper '
                    .'misprints the year as 1978), Booth tried to arrest Edwards on an outstanding misdemeanor warrant '
                    .'the officer did not have with him and, when Edwards asked to see it, tried to force him into the '
                    .'patrol car; a struggle followed in which Booth struck Edwards with a flashlight and the two '
                    .'fought over the officer\'s service revolver, which discharged and killed Booth. Trial testimony — '
                    .'including that of witness Michael Burrell — supported Edwards\'s claim of self-defense against an '
                    .'unlawful arrest, but an all-white jury in what the paper called a "mockery of a jury trial" '
                    .'convicted him of capital murder in February 1980 and he was sentenced to death, with a sentencing '
                    .'hearing set for March 6, 1980. He was represented by Alabama state senator Michael Figures. The '
                    .'Alabama Supreme Court later reversed the conviction (Ex parte Edwards, 452 So. 2d 503, 1983), '
                    .'holding the trial court should have instructed the jury on a person\'s limited right to resist '
                    .'an unlawful arrest.',
                'cases' => [[
                    'charges' => 'Capital murder of a police officer — the August 14, 1979 death of Mobile patrolman Henry '
                        .'Booth, who was killed with his own service revolver during a struggle after he tried to arrest '
                        .'Edwards without the warrant in hand. Edwards\'s defense was self-defense against an unlawful arrest.',
                    'arrest_date' => '1979-08-14',
                    'convicted' => 'Yes — convicted of capital murder in February 1980 and sentenced to death by an all-white '
                        .'jury; the Alabama Supreme Court reversed the conviction in 1983 (Ex parte Edwards, 452 So. 2d 503) '
                        .'for the trial court\'s failure to instruct on the right to resist an unlawful arrest.',
                    'sentence' => 'Death (February 1980), later reversed on appeal.',
                ]],
            ],
            [
                'name' => 'Leonard Alexander',
                'first_name' => 'Leonard',
                'last_name' => 'Alexander',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1970s',
                'ideologies' => ['Black liberation', 'Prisoners\' rights'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Leonard Alexander was a Black inmate at the Correctional Training Facility at Soledad '
                    .'who, in a letter published by The Black Panther in 1980, appealed to the Prisoners\' Rights Task '
                    .'Force for help defending himself and twelve other Black prisoners being tried on murder, assault '
                    .'and conspiracy charges arising from racial disturbances at Soledad in 1979. He alleged the '
                    .'district attorney and the Department of Corrections used perjured informant testimony, rewarded '
                    .'prosecution witnesses with sentence commutations, and forced Black defendants before all-white '
                    .'juries. The charges stemmed from the December 6, 1979 riot in Whitney Hall, a Soledad housing '
                    .'unit, in which one inmate was stabbed to death in his cell and others were wounded. Alexander — '
                    .'who worked as the prison visiting-room photographer and served as the Black representative for '
                    .'B wing on the Men\'s Advisory Council — was, after an earlier trial ended in a hung jury, '
                    .'convicted of conspiracy to commit murder and conspiracy to commit assault upon an inmate '
                    .'(People v. Alexander, 140 Cal. App. 3d 647, 1983).',
                'cases' => [[
                    'charges' => 'Conspiracy to commit murder and conspiracy to commit assault upon an inmate, arising from '
                        .'the December 6, 1979 racial riot in Whitney Hall at the Correctional Training Facility (Soledad). '
                        .'Alexander and a dozen Black co-defendants said they were framed with perjured informant testimony.',
                    'convicted' => 'Yes — conspiracy to commit murder and conspiracy to commit assault upon an inmate, after an earlier trial ended in a hung jury (People v. Alexander, 1983).',
                    'institution_name' => 'Correctional Training Facility (Soledad State Prison)',
                    'institution_city' => 'Soledad',
                    'institution_state' => 'California',
                ]],
            ],
            [
                'name' => 'Jack Johnson',
                'aka' => 'John Arthur Johnson',
                'first_name' => 'Jack',
                'last_name' => 'Johnson',
                'gender' => 'Male',
                'race' => 'Black',
                'birthdate' => '1878-03-31',
                'death_date' => '1946-06-10',
                'era' => '1910s',
                'ideologies' => ['Racial justice'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Jack Johnson (John Arthur Johnson) became the first Black world heavyweight boxing '
                    .'champion when he defeated Tommy Burns in 1908. The Black Panther\'s series "FBI Harassment of '
                    .'Black Americans, 1910–1980," by historian C.R. Gibbs, opens with Johnson as the Bureau\'s first '
                    .'Black target: two years after winning the title he became the object of a federal pursuit under '
                    .'the newly passed Mann Act, a prosecution the paper frames as racial persecution of a Black man '
                    .'who consorted openly with white women. Convicted in 1913 by an all-white jury of transporting a '
                    .'woman across state lines for "immoral purposes," Johnson fled the country, then returned and '
                    .'served roughly a year in the federal penitentiary at Leavenworth beginning in 1920. He was '
                    .'posthumously pardoned by President Trump in 2018.',
                'cases' => [[
                    'charges' => 'Violation of the Mann Act — transporting a woman across state lines for "immoral purposes." '
                        .'The Black Panther presents the case as the racially motivated federal persecution of the first '
                        .'Black world heavyweight champion, and as the FBI/Bureau of Investigation\'s first major action '
                        .'against a Black American.',
                    'convicted' => 'Yes — convicted under the Mann Act in 1913 by an all-white jury; posthumously pardoned in 2018.',
                    'incarceration_date' => '1920-01-01',
                    'release_date' => '1921-01-01',
                    'date_precision' => ['incarceration_date' => 'year', 'release_date' => 'year'],
                    'sentence' => 'One year and one day; served at Leavenworth beginning 1920 after returning from years abroad as a fugitive.',
                    'institution_name' => 'United States Penitentiary, Leavenworth',
                    'institution_city' => 'Leavenworth',
                    'institution_state' => 'Kansas',
                ]],
            ],
            [
                'name' => 'Walter Sisulu',
                'aka' => 'Walter Max Ulyate Sisulu',
                'first_name' => 'Walter',
                'last_name' => 'Sisulu',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'South Africa',
                'birthdate' => '1912-05-18',
                'death_date' => '2003-05-05',
                'era' => '1960s',
                'ideologies' => ['Anti-apartheid', 'African nationalism'],
                'affiliation' => ['African National Congress'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Walter Sisulu was a leader of the African National Congress (ANC) and one of the '
                    .'defendants sentenced to life imprisonment at the 1964 Rivonia Trial alongside Nelson Mandela. '
                    .'By 1980 he had been held for some sixteen years, most of them on Robben Island, the penal colony '
                    .'off Cape Town used for South Africa\'s political prisoners. The Black Panther reported that Black '
                    .'guerrillas who attacked a police station near Soweto in April 1980 left leaflets demanding the '
                    .'release of Sisulu, "leader of the banned African National Congress," as the armed struggle '
                    .'against the apartheid regime escalated. He was released in October 1989 and went on to serve as '
                    .'ANC deputy president during the transition from apartheid.',
                'cases' => [[
                    'charges' => 'Sabotage and conspiracy to overthrow the state — convicted with Nelson Mandela and other '
                        .'ANC leaders at the 1964 Rivonia Trial.',
                    'arrest_date' => '1963-01-01',
                    'sentenced_date' => '1964-06-12',
                    'release_date' => '1989-10-15',
                    'date_precision' => ['arrest_date' => 'year'],
                    'convicted' => 'Yes — sentenced to life imprisonment at the Rivonia Trial, June 1964.',
                    'sentence' => 'Life imprisonment; served about 25 years, most on Robben Island, before his release in October 1989.',
                    'institution_name' => 'Robben Island Prison',
                    'institution_city' => 'Cape Town',
                    'institution_state' => 'South Africa',
                ]],
            ],
            [
                'name' => 'Joseph Mavi',
                'first_name' => 'Joseph',
                'last_name' => 'Mavi',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'South Africa',
                'era' => '1980s',
                'ideologies' => ['Anti-apartheid', 'Labor rights'],
                'affiliation' => ['Black Municipal Workers\' Union'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Joseph Mavi was the chairman of the unofficial Black Municipal Workers\' Union in '
                    .'Johannesburg. The Black Panther reported that after a strike by black municipal workers in '
                    .'July–August 1980 — a walkout of more than 10,000 African workers over wages and the migrant-labor '
                    .'system, which the apartheid authorities broke by dismissing and deporting over a thousand strikers '
                    .'to the tribal homelands and forcing thousands more back to work — Mavi was arrested and, together '
                    .'with the union\'s secretary, charged under South Africa\'s Sabotage Act and Riotous Assembly Act, '
                    .'a far harsher response than ordinary labor law, for actions connected to the strike.',
                'cases' => [[
                    'charges' => 'Detained and charged under South Africa\'s Sabotage Act and Riotous Assembly Act, together '
                        .'with the union\'s secretary, over the July–August 1980 Johannesburg black municipal workers\' '
                        .'strike, in which more than 10,000 African workers struck and over a thousand dismissed strikers '
                        .'were removed by police to the tribal homelands.',
                    'arrest_date' => '1980-08-01',
                    'date_precision' => ['arrest_date' => 'month'],
                    'convicted' => 'Charged under the Sabotage and Riotous Assembly Acts (1980); outcome not recorded in the source.',
                ]],
            ],
            [
                'name' => 'Kim Dae Jung',
                'aka' => 'Kim Dae-jung',
                'first_name' => 'Dae Jung',
                'last_name' => 'Kim',
                'gender' => 'Male',
                'race' => 'Korean',
                'state' => 'South Korea',
                'birthdate' => '1924-01-06',
                'death_date' => '2009-08-18',
                'era' => '1980s',
                'ideologies' => ['Democracy', 'Human rights'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Kim Dae Jung was South Korea\'s leading opposition dissident. The Black Panther '
                    .'reported that in 1980, following the military\'s seizure of power and the crushing of the Kwangju '
                    .'(Gwangju) uprising, military prosecutors indicted him for violating national-security laws and '
                    .'"plotting insurrection," and that in September 1980 a military court sentenced him to death for '
                    .'sedition — accused of instigating the uprising — while 23 co-defendants received terms of two to '
                    .'twenty years. Kim said his interrogation had verged "on the very point short of torture" and that '
                    .'co-defendants had been beaten into false confessions. Under intense international pressure his '
                    .'death sentence was commuted in January 1981; he was later released and exiled, and eventually '
                    .'became President of South Korea (1998–2003) and the 2000 Nobel Peace laureate.',
                'cases' => [[
                    'charges' => 'Sedition and conspiracy to overthrow the government / violating national-security laws — '
                        .'accused by a military court of instigating the May 1980 Kwangju (Gwangju) uprising.',
                    'arrest_date' => '1980-01-01',
                    'sentenced_date' => '1980-09-17',
                    'date_precision' => ['arrest_date' => 'year'],
                    'convicted' => 'Yes — sentenced to death by a military court in September 1980; the sentence was commuted (to life, then a term of years) in January 1981 under international pressure.',
                    'sentence' => 'Death, commuted in January 1981; later released and exiled.',
                ]],
            ],
        ];
    }
}
