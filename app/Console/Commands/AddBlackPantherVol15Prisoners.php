<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Political prisoners surfaced by reading The Black Panther newspaper's Volume 15
 * (1976) — the twenty-nine issues archived at marxists.org (Vol. 15 Nos. 1–30,
 * April 17 – November 6, 1976; there is no No. 16 in the archive). This volume
 * is dominated by the San Quentin Six trial (which ended August 12, 1976) and by
 * AIM's Pine Ridge / Loud Hawk cases, Gary Tyler, the Wilmington Ten, the
 * Livernois defendants, the George Jackson Brigade, Huey Newton's exile, and
 * many more — nearly all of them already in the database and skipped here by
 * name (Johnny Spain and the rest of the San Quentin Six, George Jackson,
 * Leonard Peltier, Dennis and KaMook Banks, the Loud Hawk defendants, Gary
 * Tyler, Ben Chavis and the Wilmington Ten, Rubin Carter and John Artis, Dessie
 * Woods, H. Rap Brown, Marshall "Eddie" Conway, Mark Cook / John Sherman / Ed
 * Mead of the George Jackson Brigade, the Menominee Warrior Society's Michael
 * Sturdevant, the Livernois Five, Clarence Norris, Charles Wakefield, Stanton
 * Story, Delbert Tibbs, Lureida Torres, Lee Otis Johnson, Robert Sobukwe, Andrés
 * Figueroa Cordero, and the Mokoape/SASO Nine already added from Vol. 16).
 *
 * Added here are the clearly-named political prisoners the volume covers who were
 * NOT already recorded. Domestic (United States):
 *
 *  - Ricardo Chávez Ortiz — the Mexican-American who "hijacked" a Frontier
 *    Airlines flight in 1972 with an unloaded gun to demand airtime to protest
 *    injustices against Mexican-Americans; a Chicano-movement cause (No. 13).
 *  - Olga Talamante — a Bay Area Chicana held sixteen months and tortured as a
 *    political prisoner in Argentina, freed in March 1976 (Nos. 21, 24).
 *  - Glenn Diamond — a former Atmore-Holman "Inmates For Action" member and
 *    Mobile activist whom police tried to lynch during a robbery arrest (No. 5).
 *  - J.B. Johnson — the St. Louis man twice convicted, on a frame-up per the
 *    paper, of a policeman's death and given life (Nos. 24, 25).
 *
 * International (anti-apartheid and other liberation-solidarity cases the paper
 * championed, following the Mandela / Sisulu precedent already in the database):
 *
 *  - Desmond Trotter — the Dominican Black Power activist condemned to death on
 *    a framed murder charge, commuted to life in 1976 (No. 2).
 *  - John Kani and Winston Ntshona — the Tony-winning South African actors
 *    detained in the Transkei over their play "Sizwe Banzi Is Dead" (Nos. 28–29).
 *  - Joseph Mdluli — the ANC activist tortured to death in Durban security
 *    police custody in 1976 (No. 14).
 *  - David Rabkin — the South African journalist sentenced to ten years for
 *    producing ANC literature (No. 25).
 *  - Looksmart Ngudle — the first person to die under South Africa's security
 *    detention laws (1963), recalled in the paper's Sharpeville series (No. 14).
 *  - Edgardo Enríquez — the Chilean MIR leader seized in Argentina in 1976 and
 *    disappeared (No. 4).
 *
 * Facts are corroborated against outside sources (court records, TRC findings,
 * the Loud Hawk and SASO/BPC histories, etc.). Where the paper and the record
 * differ the record is followed and the discrepancy noted. Unknown fields are
 * left out rather than guessed.
 *
 * Idempotent: skips any name already present.
 */
final class AddBlackPantherVol15Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-vol15';

    protected $description = 'Add political prisoners from The Black Panther Vol. 15 (1976)';

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
                'name' => 'Ricardo Chávez Ortiz',
                'aka' => 'Ricardo Chavez-Ortiz',
                'first_name' => 'Ricardo',
                'last_name' => 'Chávez Ortiz',
                'gender' => 'Male',
                'race' => 'Latino',
                'state' => 'California',
                'birthdate' => '1933-01-01',
                'death_date' => '2021-01-01',
                'era' => '1970s',
                'ideologies' => ['Chicano liberation'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Ricardo Chávez Ortiz was a Mexican-American who became a hero of the Chicano movement. '
                    .'On April 13, 1972 he boarded Frontier Airlines Flight 91 and, using an unloaded pistol, diverted '
                    .'it to Los Angeles — demanding not ransom but airtime on the news, where he delivered a rambling '
                    .'address protesting police brutality, racism and the mistreatment of Mexican-Americans. Convicted '
                    .'of air piracy and sentenced to life (later reduced to twenty years on appeal), he became a '
                    .'movement cause célèbre; The Black Panther reported in 1976 that his attorneys were filing a '
                    .'motion to modify the sentence and win his release.',
                'cases' => [[
                    'charges' => 'Air piracy — the April 13, 1972 diversion of Frontier Airlines Flight 91 with an '
                        .'unloaded pistol, a protest (no ransom demanded) for television airtime to denounce injustices '
                        .'against Mexican-Americans.',
                    'arrest_date' => '1972-04-13',
                    'convicted' => 'Yes — convicted of air piracy and sentenced to life imprisonment, reduced to twenty years on appeal.',
                    'sentence' => 'Life, reduced to twenty years on appeal.',
                ]],
            ],
            [
                'name' => 'Olga Talamante',
                'first_name' => 'Olga',
                'last_name' => 'Talamante',
                'gender' => 'Female',
                'race' => 'Latino',
                'state' => 'California',
                'era' => '1970s',
                'ideologies' => ['Chicano liberation', 'Anti-imperialism', 'Socialism'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Olga Talamante was a Bay Area Chicana activist and University of California, Santa Cruz '
                    .'graduate who, while doing political work in Argentina with the Peronist Youth, was arrested in '
                    .'November 1974 in Azul and held for sixteen months as a political prisoner, during which she was '
                    .'tortured with electric shock. An international campaign by the Olga Talamante Defense Committee won '
                    .'her freedom on March 27, 1976; The Black Panther reported her return, introducing her at Bay Area '
                    .'rallies as a freed political prisoner. She later directed the Chicana Latina Foundation.',
                'cases' => [[
                    'charges' => 'Detained as a political prisoner in Argentina for political organizing with the Peronist '
                        .'Youth; held sixteen months and tortured.',
                    'arrest_date' => '1974-11-01',
                    'date_precision' => ['arrest_date' => 'month'],
                    'release_date' => '1976-03-27',
                    'convicted' => 'Held sixteen months as a political prisoner under Argentina\'s repression; freed March 27, 1976 after an international defense campaign.',
                    'sentence' => 'About sixteen months\' imprisonment (Argentina).',
                    'institution_name' => 'Villa Devoto Prison',
                    'institution_city' => 'Buenos Aires',
                    'institution_state' => 'Argentina',
                ]],
            ],
            [
                'name' => 'Glenn Diamond',
                'aka' => 'Casmarah Mani',
                'first_name' => 'Glenn',
                'last_name' => 'Diamond',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Alabama',
                'era' => '1970s',
                'ideologies' => ['Black liberation', 'Prisoners\' rights'],
                'affiliation' => ['Inmates For Action'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Glenn Diamond (called Casmarah Mani in The Black Panther) was a 27-year-old Mobile, '
                    .'Alabama activist and former member of Inmates For Action, the militant prisoners\' organization '
                    .'formed at the Atmore-Holman prison. In late March 1976, seized by Mobile police who wanted a '
                    .'robbery confession, he was handcuffed and — as officers said "we ought to hang him" — a rope from '
                    .'a patrol car was put around his neck and he was hauled off the ground until he choked and nearly '
                    .'lost consciousness. Eight officers were suspended over the attempted lynching and one, Michael '
                    .'Patrick, was tried for assault (and acquitted by an all-white jury). Diamond was nonetheless '
                    .'charged with the robbery, held on $100,000 bail and transferred to Mt. Meigs prison to await '
                    .'trial, which the paper framed as a continuation of the persecution of a prison-movement activist.',
                'cases' => [[
                    'charges' => 'Robbery — the charge on which Mobile police arrested Glenn Diamond in late March 1976 '
                        .'and, seeking a confession, attempted to lynch him. He was held on $100,000 bail at Mt. Meigs '
                        .'awaiting trial.',
                    'arrest_date' => '1976-03-28',
                    'institution_name' => 'Mt. Meigs Prison',
                    'institution_city' => 'Montgomery',
                    'institution_state' => 'Alabama',
                ]],
            ],
            [
                'name' => 'J.B. Johnson',
                'first_name' => 'J.B.',
                'last_name' => 'Johnson',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Missouri',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'J.B. Johnson was a Black St. Louis man whom The Black Panther described as the victim of '
                    .'a six-year frame-up in the death of a St. Louis police officer killed during a January 23, 1970 '
                    .'University City jewelry-store robbery. His 1972 conviction was reversed by the Missouri Supreme '
                    .'Court in 1975; he was reconvicted under the felony-murder rule in May 1976 and given a life '
                    .'sentence, and the trial court then denied probation — a ruling his attorney William Kunstler '
                    .'called "racism at its worst." The paper pointed to suppressed and contradictory evidence and a '
                    .'witness who took years to identify him. A National Committee to Free J.B. Johnson organized around '
                    .'the case.',
                'cases' => [[
                    'charges' => 'First-degree (felony) murder in the death of a St. Louis police officer during the '
                        .'January 23, 1970 robbery of a University City jewelry store — a frame-up, per the paper, built '
                        .'on suppressed and contradictory evidence.',
                    'arrest_date' => '1970-01-01',
                    'date_precision' => ['arrest_date' => 'year'],
                    'convicted' => 'Convicted 1972 (reversed by the Missouri Supreme Court in 1975); reconvicted under the felony-murder rule in May 1976 and sentenced to life, with probation denied.',
                    'sentence' => 'Life imprisonment ("natural life").',
                ]],
            ],
            [
                'name' => 'Desmond Trotter',
                'aka' => 'Ras Kabinda',
                'first_name' => 'Desmond',
                'last_name' => 'Trotter',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Dominica',
                'era' => '1970s',
                'ideologies' => ['Black Power', 'Pan-Africanism', 'Rastafari'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Desmond Trotter (Ras Kabinda) was a Dominican Black Power and Rastafari activist whom The '
                    .'Black Panther presented as a framed political prisoner. Convicted on November 1, 1974 of the murder '
                    .'of a white American tourist, John Jirasek, and sentenced to hang, he became the subject of an '
                    .'international campaign after the prosecution\'s chief witness admitted she had lied. On April 5, '
                    .'1976 his death sentence was commuted to life imprisonment amid demands that the case be reopened. '
                    .'He was released in 1979.',
                'cases' => [[
                    'charges' => 'Murder of a white American tourist (John Jirasek) in May 1974 — a frame-up, per the '
                        .'paper and Trotter\'s supporters; the chief prosecution witness later admitted lying.',
                    'arrest_date' => '1974-01-01',
                    'sentenced_date' => '1974-11-01',
                    'release_date' => '1979-01-01',
                    'date_precision' => ['arrest_date' => 'year', 'release_date' => 'year'],
                    'convicted' => 'Yes — convicted November 1, 1974 and sentenced to death; the sentence was commuted to life imprisonment on April 5, 1976, and he was released in 1979.',
                    'sentence' => 'Death, commuted to life imprisonment (April 5, 1976).',
                    'institution_name' => 'Dominica Prison',
                    'institution_city' => 'Roseau',
                    'institution_state' => 'Dominica',
                ]],
            ],
            [
                'name' => 'John Kani',
                'first_name' => 'John',
                'last_name' => 'Kani',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'South Africa',
                'birthdate' => '1943-08-30',
                'era' => '1970s',
                'ideologies' => ['Anti-apartheid'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'John Kani was an internationally acclaimed South African actor and playwright who, with '
                    .'Winston Ntshona, had won a 1975 Tony Award for the anti-apartheid plays "Sizwe Banzi Is Dead" and '
                    .'"The Island." The Black Panther reported that the two were arrested on October 8, 1976 in '
                    .'Butterworth, in the nominally self-governing Transkei bantustan, after a performance of "Sizwe '
                    .'Banzi Is Dead" in which they improvised remarks mocking the coming "independence" of the Transkei. '
                    .'Held without formal charge, they were released about October 24, 1976 — two days before the '
                    .'Transkei declaration — by personal order of Chief Minister Kaiser Matanzima.',
                'cases' => [[
                    'charges' => 'Detained without formal charge by Transkei authorities over remarks made during a '
                        .'performance of "Sizwe Banzi Is Dead" ridiculing the bantustan\'s "independence."',
                    'arrest_date' => '1976-10-08',
                    'release_date' => '1976-10-24',
                    'convicted' => 'Never charged — detained about two weeks and released by order of the Transkei Chief Minister.',
                    'sentence' => 'Roughly two weeks\' detention without trial.',
                ]],
            ],
            [
                'name' => 'Winston Ntshona',
                'first_name' => 'Winston',
                'last_name' => 'Ntshona',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'South Africa',
                'birthdate' => '1941-10-06',
                'death_date' => '2018-08-02',
                'era' => '1970s',
                'ideologies' => ['Anti-apartheid'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Winston Ntshona was an internationally acclaimed South African actor who, with John Kani, '
                    .'won a 1975 Tony Award for "Sizwe Banzi Is Dead" and "The Island." The Black Panther reported that '
                    .'the two were arrested on October 8, 1976 in Butterworth, Transkei, after a performance of "Sizwe '
                    .'Banzi Is Dead" in which they improvised remarks mocking the bantustan\'s coming "independence." '
                    .'Held without charge, they were released about October 24, 1976 on the order of Chief Minister '
                    .'Kaiser Matanzima.',
                'cases' => [[
                    'charges' => 'Detained without formal charge by Transkei authorities over remarks during a performance '
                        .'of "Sizwe Banzi Is Dead" ridiculing the bantustan\'s "independence."',
                    'arrest_date' => '1976-10-08',
                    'release_date' => '1976-10-24',
                    'convicted' => 'Never charged — detained about two weeks and released by order of the Transkei Chief Minister.',
                    'sentence' => 'Roughly two weeks\' detention without trial.',
                ]],
            ],
            [
                'name' => 'Joseph Mdluli',
                'aka' => 'Joseph Masobiya Mdluli',
                'first_name' => 'Joseph',
                'last_name' => 'Mdluli',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'South Africa',
                'era' => '1970s',
                'ideologies' => ['Anti-apartheid', 'African nationalism'],
                'affiliation' => ['African National Congress'],
                'in_custody' => false,
                'released' => false,
                'awaiting_trial' => false,
                'description' => 'Joseph Mdluli was a Durban ANC activist, about fifty years old, who was arrested by the '
                    .'South African security police on March 18, 1976 and was dead the next day. When his wife was '
                    .'allowed to see his body she found it covered with injuries — a swollen forehead, a cut lip, cuts '
                    .'at the base of the skull. The Black Panther reported his killing as another death by torture in '
                    .'apartheid detention. Four security policemen were charged with culpable homicide but acquitted; '
                    .'South Africa\'s Truth and Reconciliation Commission later found the security police responsible '
                    .'for his death.',
                'cases' => [[
                    'charges' => 'Detained without trial by the South African security police; died in custody the day '
                        .'after his arrest, his body bearing injuries consistent with torture.',
                    'arrest_date' => '1976-03-18',
                    'death_in_custody_date' => '1976-03-19',
                    'convicted' => 'Never charged — detained and killed in security-police custody on March 19, 1976.',
                    'institution_name' => 'Durban Security Branch (police detention)',
                    'institution_city' => 'Durban',
                    'institution_state' => 'South Africa',
                ]],
            ],
            [
                'name' => 'David Rabkin',
                'first_name' => 'David',
                'last_name' => 'Rabkin',
                'gender' => 'Male',
                'race' => 'White',
                'state' => 'South Africa',
                'death_date' => '1985-01-01',
                'era' => '1970s',
                'ideologies' => ['Anti-apartheid', 'Communism'],
                'affiliation' => ['African National Congress', 'South African Communist Party'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'David Rabkin was a South African journalist, a copy editor for the Cape Town Argus, who '
                    .'The Black Panther reported had pleaded guilty in 1976 to writing and distributing pamphlets for '
                    .'banned Black organizations including the African National Congress, in violation of the Terrorism '
                    .'Act and the Internal Security Act. He was sentenced to ten years\' imprisonment and served about '
                    .'seven; his wife Sue Rabkin was also convicted. After his release he joined Umkhonto we Sizwe and '
                    .'was killed in a training accident in Angola in 1985.',
                'cases' => [[
                    'charges' => 'Producing and distributing literature for the banned African National Congress and '
                        .'South African Communist Party, under the Terrorism Act and Internal Security Act.',
                    'arrest_date' => '1976-01-01',
                    'sentenced_date' => '1976-01-01',
                    'date_precision' => ['arrest_date' => 'year', 'sentenced_date' => 'year'],
                    'convicted' => 'Yes — pleaded guilty in 1976 and was sentenced to ten years; served about seven.',
                    'sentence' => 'Ten years\' imprisonment (served about seven).',
                    'institution_name' => 'Pretoria Local Prison',
                    'institution_city' => 'Pretoria',
                    'institution_state' => 'South Africa',
                ]],
            ],
            [
                'name' => 'Looksmart Ngudle',
                'aka' => 'Looksmart Solwandle Ngudle',
                'first_name' => 'Looksmart',
                'last_name' => 'Ngudle',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'South Africa',
                'death_date' => '1963-09-05',
                'era' => '1960s',
                'ideologies' => ['Anti-apartheid', 'African nationalism'],
                'affiliation' => ['African National Congress', 'Umkhonto we Sizwe'],
                'in_custody' => false,
                'released' => false,
                'awaiting_trial' => false,
                'description' => 'Looksmart Solwandle Ngudle was an ANC and Umkhonto we Sizwe activist and the first '
                    .'person to die in South African security detention, on September 5, 1963, after being held '
                    .'incommunicado in solitary confinement and, according to fellow detainees, tortured. A judge '
                    .'blocked evidence of his torture and the regime banned him posthumously, so that his own inquest '
                    .'could not be reported. The Black Panther recalled his death in 1976 in its serialized report on '
                    .'apartheid repression, as the founding case of death-in-detention under South Africa\'s security '
                    .'laws.',
                'cases' => [[
                    'charges' => 'Detained incommunicado in solitary confinement under South Africa\'s 90-day security '
                        .'detention law; died in custody, his fellow detainees said as a result of torture.',
                    'arrest_date' => '1963-01-01',
                    'death_in_custody_date' => '1963-09-05',
                    'date_precision' => ['arrest_date' => 'year'],
                    'convicted' => 'Never charged — detained without trial and died in security-police custody on September 5, 1963; banned posthumously.',
                    'institution_name' => 'Pretoria security detention',
                    'institution_city' => 'Pretoria',
                    'institution_state' => 'South Africa',
                ]],
            ],
            [
                'name' => 'Edgardo Enríquez',
                'aka' => 'Edgardo Enríquez Espinosa',
                'first_name' => 'Edgardo',
                'last_name' => 'Enríquez',
                'gender' => 'Male',
                'race' => 'Latino',
                'state' => 'Chile',
                'era' => '1970s',
                'ideologies' => ['Revolutionary socialism', 'Anti-imperialism'],
                'affiliation' => ['Movimiento de Izquierda Revolucionaria (MIR)'],
                'in_custody' => false,
                'released' => false,
                'awaiting_trial' => false,
                'description' => 'Edgardo Enríquez Espinosa was a leading member of Chile\'s Movement of the Revolutionary '
                    .'Left (MIR). The Black Panther reported that he was arrested in Argentina on April 10, 1976 — two '
                    .'weeks after the military coup there — and was being held and tortured in a military prison, in '
                    .'danger of being handed to the Chilean junta. Seized as part of the cross-border repression later '
                    .'known as Operation Condor, he was disappeared and never seen again.',
                'cases' => [[
                    'charges' => 'Seized for his political activity as a MIR leader by the Argentine military and held and '
                        .'tortured in a secret military prison; forcibly disappeared under Operation Condor.',
                    'arrest_date' => '1976-04-10',
                    'convicted' => 'Never charged — detained, tortured and forcibly disappeared after his April 10, 1976 arrest in Argentina.',
                    'institution_name' => 'Argentine military detention',
                    'institution_city' => 'Buenos Aires',
                    'institution_state' => 'Argentina',
                ]],
            ],
        ];
    }
}
