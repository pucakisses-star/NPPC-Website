<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Political prisoners surfaced by reading The Black Panther newspaper's Volume 16
 * (1977) — the twenty issues archived at marxists.org (Vol. 16 Nos. 8–16 and
 * 20–30, Jan 1 – Jun 11, 1977; Nos. 17–19 are absent from the archive). The
 * volume is dense with prisoner coverage, most of it people already in the
 * database and skipped by name here: Gary Tyler, Leonard Peltier, Assata Shakur,
 * Sundiata Acoli, Dennis Banks, Rubin "Hurricane" Carter and John Artis, Inez
 * García, Yvonne Wanrow, Wendy Yoshimura, Paul Skyhorse and Richard Mohawk, the
 * Wilmington Ten (Ben Chavis, Willie Vereen, Marvin Patrick, James McCoy, Wayne
 * Moore, Ann Shepard), the Charlotte Three (James Earl Grant, T.J. Reddy),
 * Johnny Spain, Huey Newton, Ericka Huggins and Bobby Seale, Marshall "Eddie"
 * Conway, John Hill / Dacajeweiah, Lorenzo Kom'boa Ervin, Dino Butler and Bob
 * Robideau, Carter Camp, Leonard Crow Dog, Nelson and Winnie Mandela, Dennis
 * Brutus, Bishop Donal Lamont, Phil Shinnick, and Elizabeth McAlister.
 *
 * Added here are the clearly-named political prisoners the volume covers who were
 * NOT already recorded. Domestic (United States):
 *
 *  - Ray Lee Patterson — a Black Marine sergeant given two life terms for the
 *    self-defense killing of two Georgia troopers (Vol. 16 No. 12).
 *  - Juan Haro and Antonio Quintana — Denver Crusade for Justice (Chicano
 *    movement) activists framed on police-bombing conspiracy charges via an
 *    agent provocateur (Vol. 16 No. 9).
 *  - Margo Cowan — the Manzo Area Council director indicted on 25 felony counts
 *    for aiding undocumented immigrants (Vol. 16 No. 9).
 *  - José Jacques Medina — a Mexican political exile and CASA organizer fighting
 *    deportation (Vol. 16 Nos. 13, 20).
 *  - Curtis Jones Jr. — a Black Marine court-martialed as one of the "Camp
 *    Pendleton 14" who confronted an on-base Klan presence (Vol. 16 Nos. 24,
 *    27, 29).
 *  - The Dawson Five — five young Black Georgians framed for murder on coerced
 *    confessions; all charges dropped Dec 19, 1977 (Vol. 16 Nos. 12, 22, 23).
 *
 * International (anti-colonial / anti-apartheid solidarity cases the paper
 * championed, following the Nelson Mandela / Walter Sisulu precedent already in
 * the database):
 *
 *  - The SASO Nine — Black Consciousness (SASO/BPC) leaders convicted under
 *    South Africa's Terrorism Act and sent to Robben Island (Vol. 16 No. 10).
 *  - Andimba Toivo ya Toivo — the SWAPO co-founder serving 20 years on Robben
 *    Island for Namibian independence (Vol. 16 No. 21).
 *  - Peter Magubane, Thenjiwe Mtintso, Nat Serache and Eric Abraham — South
 *    African journalists/activists detained, banned or tortured in the crackdown
 *    after the 1976 Soweto uprising (Vol. 16 Nos. 8, 9, 14, 25).
 *  - Matthews Mabelane — a young detainee who died in South African police
 *    custody (Vol. 16 No. 16).
 *
 * Facts are corroborated against outside sources (court records, the SASO/BPC
 * trial and Dawson Five histories, Robben Island prisoner records, etc.). Where
 * the paper and the record differ, the record is followed and the discrepancy
 * noted. Unknown fields are left out rather than guessed.
 *
 * Idempotent: skips any name already present.
 */
final class AddBlackPantherVol16Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-vol16';

    protected $description = 'Add political prisoners from The Black Panther Vol. 16 (1977)';

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
        return array_merge(
            $this->domesticRecords(),
            $this->dawsonFive(),
            $this->sasoNine(),
            $this->southernAfrica(),
        );
    }

    private function domesticRecords(): array
    {
        return [
            [
                'name' => 'Ray Lee Patterson',
                'aka' => 'Ray Patterson',
                'first_name' => 'Ray',
                'middle_name' => 'Lee',
                'last_name' => 'Patterson',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Georgia',
                'era' => '1970s',
                'ideologies' => ['Self-defense against racist violence', 'Black liberation'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Ray Lee Patterson was a Black U.S. Marine sergeant convicted of the self-defense '
                    .'killings of two white Georgia law-enforcement officers who stopped his car on May 4, 1975. The '
                    .'Black Panther reported in 1977 that he was seeking a new trial, arguing that the troopers had '
                    .'drawn a service revolver on him during a struggle and that his trial was tainted by judicial '
                    .'bias and missing evidence (the removed carpet of the patrol car). An all-white jury convicted '
                    .'him and he was given two consecutive life sentences. He was defended by John Carroll and Millard '
                    .'Farmer of the Team Defense project.',
                'cases' => [[
                    'charges' => 'Murder of two Georgia state law-enforcement officers, whom Patterson killed during a '
                        .'struggle after a car stop on May 4, 1975. His defense was self-defense.',
                    'arrest_date' => '1975-05-04',
                    'sentenced_date' => '1975-01-01',
                    'date_precision' => ['sentenced_date' => 'year'],
                    'convicted' => 'Yes — convicted by an all-white jury and given two consecutive life sentences (1975); sought a new trial (Patterson v. State, Ga. 1977).',
                    'sentence' => 'Two consecutive life sentences.',
                    'institution_name' => 'Crisp County Jail',
                    'institution_city' => 'Cordele',
                    'institution_state' => 'Georgia',
                ]],
            ],
            [
                'name' => 'Juan Haro',
                'first_name' => 'Juan',
                'last_name' => 'Haro',
                'gender' => 'Male',
                'race' => 'Latino',
                'state' => 'Colorado',
                'era' => '1970s',
                'ideologies' => ['Chicano liberation'],
                'affiliation' => ['Crusade for Justice'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Juan Haro was the head of security of the Denver Chicano-movement organization Crusade '
                    .'for Justice. The Black Panther reported that he and Antonio Quintana were arrested on September 17, '
                    .'1975 — timed to a national police-chiefs convention in Denver — and falsely charged with conspiring '
                    .'to bomb Denver police stations on the testimony of an agent provocateur, José Cordova Jr. Haro had '
                    .'already been convicted on four counts of unlawful possession of explosives and sentenced to four '
                    .'concurrent six-year terms; the conspiracy trial was set to begin January 12, 1977. He later wrote '
                    .'a memoir of the case, "The Ultimate Betrayal."',
                'cases' => [[
                    'charges' => 'Conspiracy to bomb Denver police stations, and unlawful possession of explosives / hand '
                        .'grenades — charges the paper and the Crusade for Justice said were manufactured by an agent '
                        .'provocateur, José Cordova Jr.',
                    'arrest_date' => '1975-09-17',
                    'convicted' => 'Convicted on four counts of unlawful possession of explosives (four concurrent six-year terms), which he appealed; a separate bombing-conspiracy trial with Antonio Quintana was set for January 1977.',
                    'sentence' => 'Four concurrent six-year terms on the explosives counts.',
                ]],
            ],
            [
                'name' => 'Antonio Quintana',
                'first_name' => 'Antonio',
                'last_name' => 'Quintana',
                'gender' => 'Male',
                'race' => 'Latino',
                'state' => 'Colorado',
                'era' => '1970s',
                'ideologies' => ['Chicano liberation'],
                'affiliation' => ['Crusade for Justice'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Antonio Quintana was a 22-year-old member of the Denver Chicano-movement organization '
                    .'Crusade for Justice. The Black Panther reported that he was arrested with Juan Haro on September 17, '
                    .'1975 and falsely charged with conspiring to bomb several Denver police stations, in what the paper '
                    .'called a sensationalized set-up built on the testimony of the agent provocateur José Cordova Jr., '
                    .'timed to coincide with a national police-chiefs meeting in Denver. Their conspiracy trial was set '
                    .'to begin January 12, 1977.',
                'cases' => [[
                    'charges' => 'Conspiracy to bomb Denver police stations — a frame-up, per the paper and the Crusade '
                        .'for Justice, engineered through the agent provocateur José Cordova Jr.',
                    'arrest_date' => '1975-09-17',
                    'convicted' => 'Awaiting a conspiracy trial set for January 12, 1977 as a co-defendant of Juan Haro.',
                ]],
            ],
            [
                'name' => 'Margo Cowan',
                'first_name' => 'Margo',
                'last_name' => 'Cowan',
                'gender' => 'Female',
                'race' => 'White',
                'state' => 'Arizona',
                'era' => '1970s',
                'ideologies' => ['Immigrant rights'],
                'affiliation' => ['Manzo Area Council'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Margo Cowan was the director of the Manzo Area Council, a Tucson social-service agency '
                    .'that helped undocumented immigrants. The Black Panther reported that after an April 1976 raid she '
                    .'was indicted on all 25 felony counts of aiding, abetting and transporting "aliens" — a federal test '
                    .'case challenging the right of community agencies to counsel undocumented workers — and faced a '
                    .'maximum of 77 years in prison and a $95,000 fine. Co-defendants indicted with her included Catalina '
                    .'Montaño, Marguerita Jáuregui Ramírez and a nun, Sister Ann Gabriel Marcaig. Cowan went on to a long '
                    .'career as an immigrant-rights and public-defense attorney in Arizona.',
                'cases' => [[
                    'charges' => 'Twenty-five felony counts of aiding, abetting and transporting undocumented immigrants — '
                        .'a federal prosecution the paper framed as an attack on immigrant-rights organizing, carrying up '
                        .'to 77 years and a $95,000 fine.',
                    'convicted' => 'Indicted on all 25 counts (1976) in a federal test case; faced up to 77 years.',
                ]],
            ],
            [
                'name' => 'José Jacques Medina',
                'aka' => 'Jose Medina',
                'first_name' => 'José',
                'last_name' => 'Jacques Medina',
                'gender' => 'Male',
                'race' => 'Latino',
                'state' => 'California',
                'era' => '1970s',
                'ideologies' => ['Immigrant rights', 'Socialism'],
                'affiliation' => ['CASA (Centro de Acción Social Autónomo)'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'José Jacques Medina was a Mexican political exile, attorney and labor organizer in Los '
                    .'Angeles and a leader of CASA (Centro de Acción Social Autónomo), which organized undocumented '
                    .'workers. A veteran of Mexico\'s 1968 student-worker movement who had defended its political '
                    .'prisoners, he fled Mexico and was arrested by federal agents in March 1976 and turned over to the '
                    .'INS on an "illegal entry" charge. The Black Panther reported his fight against deportation and his '
                    .'denied bid for political asylum, framing it as U.S. collaboration with Mexican repression. He was '
                    .'later a Mexican federal deputy.',
                'cases' => [[
                    'charges' => 'Immigration "illegal entry" / deportation proceedings, which the paper presented as the '
                        .'political persecution of an exiled Mexican movement organizer who had been denied asylum.',
                    'arrest_date' => '1976-03-01',
                    'date_precision' => ['arrest_date' => 'month'],
                    'convicted' => 'Held for deportation; asylum denied (March 1977). He contested removal (later Medina v. Castillo, 9th Cir. 1980).',
                ]],
            ],
            [
                'name' => 'Curtis Jones Jr.',
                'first_name' => 'Curtis',
                'last_name' => 'Jones',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1970s',
                'ideologies' => ['Black liberation', 'Anti-racism', 'Self-defense against racist violence'],
                'affiliation' => ['Camp Pendleton 14'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Corporal Curtis Jones Jr. was one of the "Camp Pendleton 14," a group of Black Marines '
                    .'court-martialed after they confronted an active-duty Ku Klux Klan presence at Marine Corps Base '
                    .'Camp Pendleton on November 13, 1976 — raiding a barracks they believed was hosting a Klan meeting '
                    .'amid a wave of on-base cross-burnings and Klan literature. The Black Panther reported that Jones, '
                    .'charged with one count of conspiracy and six counts of aggravated assault, was the first of about '
                    .'ten Black Marines ordered before a court-martial board and was held in the brig in solitary for '
                    .'over eight months, while the white Klansmen were quietly transferred and never punished.',
                'cases' => [[
                    'charges' => 'One count of conspiracy and six counts of aggravated assault, arising from the November '
                        .'13, 1976 confrontation between Black Marines and an on-base Ku Klux Klan gathering at Camp '
                        .'Pendleton. The defense was self-defense against organized racist violence.',
                    'arrest_date' => '1976-11-13',
                    'convicted' => 'Court-martialed (proceedings 1977) as the first of about ten of the Camp Pendleton 14 to face a board; the Klansmen were transferred without charge.',
                    'institution_name' => 'Marine Corps Base Camp Pendleton (brig)',
                    'institution_city' => 'Oceanside',
                    'institution_state' => 'California',
                ]],
            ],
        ];
    }

    private function dawsonFive(): array
    {
        $context = 'The "Dawson Five" were five young Black men framed for the January 22, 1976 shooting of a white '
            .'customer, Gordon B. Howell Jr., during a robbery of Tiny\'s Grocery at Bridges Crossroads near Dawson, in '
            .'"Terrible Terrell" County, Georgia. Charged on February 1, 1976, they were held for nearly two years; the '
            .'case rested on a confession beaten out of Roosevelt Watson by police who, he said, threatened to shoot, '
            .'electrocute and castrate him. Defended by Millard Farmer of Team Defense, who attacked the systematic '
            .'exclusion of Black jurors, they became a national cause célèbre (the paper reported bake sales for them '
            .'across the country). On December 19, 1977, District Attorney John Irwin dropped all charges after Judge '
            .'Walter Geer voided the coerced confession.';

        $member = function (string $name, string $first, string $last) use ($context): array {
            return [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Georgia',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['Dawson Five'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "{$name} was one of the Dawson Five. {$context}",
                'cases' => [[
                    'charges' => 'Murder and armed robbery in the January 22, 1976 killing of Gordon B. Howell Jr. near '
                        .'Dawson, Georgia — charges built on a coerced confession, for which the prosecutor sought the '
                        .'death penalty.',
                    'arrest_date' => '1976-02-01',
                    'release_date' => '1977-12-19',
                    'convicted' => 'No — all charges dropped on December 19, 1977 after the coerced confession was voided.',
                    'institution_name' => 'Terrell County Jail',
                    'institution_city' => 'Dawson',
                    'institution_state' => 'Georgia',
                ]],
            ];
        };

        return [
            $member('Roosevelt Watson', 'Roosevelt', 'Watson'),
            $member('Henderson Watson', 'Henderson', 'Watson'),
            $member('J.D. Davenport', 'J.D.', 'Davenport'),
            $member('Johnnie B. Jackson', 'Johnnie', 'Jackson'),
            $member('George Poor', 'George', 'Poor'),
        ];
    }

    private function sasoNine(): array
    {
        $context = 'The SASO Nine were leaders of the South African Students\' Organisation (SASO) and the Black '
            .'People\'s Convention — the Black Consciousness movement — tried in Pretoria in one of the longest political '
            .'trials of the apartheid era (it ended December 21, 1976). Arrested in September–October 1974 after they '
            .'organised rallies in Natal celebrating the pro-FRELIMO independence of Mozambique, in defiance of a police '
            .'ban, they were convicted under the Terrorism Act of conspiring to bring about revolutionary change — the '
            .'first ruling equating opposition to apartheid with "terrorism." Steve Biko testified for the defense. All '
            .'nine were sent to Robben Island; three received six-year sentences and six received five-year sentences.';

        $member = function (string $name, string $first, string $last, string $detail = '') use ($context): array {
            return [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'South Africa',
                'era' => '1970s',
                'ideologies' => ['Anti-apartheid', 'Black Consciousness'],
                'affiliation' => ['South African Students\' Organisation', 'Black People\'s Convention'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => trim("{$name} was one of the SASO Nine. {$detail} {$context}"),
                'cases' => [[
                    'charges' => 'Convicted under South Africa\'s Terrorism Act for Black Consciousness organising — '
                        .'specifically the September 1974 pro-FRELIMO rallies held in defiance of a police ban.',
                    'arrest_date' => '1974-09-01',
                    'sentenced_date' => '1976-12-21',
                    'date_precision' => ['arrest_date' => 'month'],
                    'convicted' => 'Yes — convicted under the Terrorism Act (December 1976) and sentenced to five or six years on Robben Island.',
                    'sentence' => 'Five or six years\' imprisonment on Robben Island (three of the nine received six years, six received five).',
                    'institution_name' => 'Robben Island Prison',
                    'institution_city' => 'Cape Town',
                    'institution_state' => 'South Africa',
                ]],
            ];
        };

        return [
            $member('Saths Cooper', 'Saths', 'Cooper',
                'A psychologist and Black People\'s Convention leader, he later became a prominent figure in South African and international psychology.'),
            $member('Mosiuoa Lekota', 'Mosiuoa', 'Lekota',
                'Nicknamed "Terror," he later became an ANC leader, national chairperson, Premier of the Free State and South Africa\'s Minister of Defence, and founded the Congress of the People (COPE).'),
            $member('Strini Moodley', 'Strini', 'Moodley',
                'A journalist, playwright and founding SASO member, he remained a Black Consciousness activist until his death in 2006.'),
            $member('Muntu Myeza', 'Muntu', 'Myeza',
                'He was SASO\'s secretary-general and a principal organiser of the banned September 1974 pro-FRELIMO rallies.'),
            $member('Pandelani Nefolovhodwe', 'Pandelani', 'Nefolovhodwe',
                'A SASO president, he later led the Azanian People\'s Organisation (AZAPO) and served in Parliament.'),
            $member('Zithulele Cindi', 'Zithulele', 'Cindi',
                'He was a leader of the Black People\'s Convention and, later, of AZAPO.'),
            $member('Aubrey Mokoape', 'Aubrey', 'Mokoape',
                'A medical doctor and veteran activist who had been involved in Black politics since the Pan Africanist Congress, he continued organising after his release.'),
            $member('Nkwenke Nkomo', 'Nkwenke', 'Nkomo',
                'He was among the SASO/BPC leaders convicted and imprisoned on Robben Island.'),
            $member('Gilbert Sedibe', 'Gilbert', 'Sedibe',
                'He was among the SASO/BPC leaders convicted and imprisoned on Robben Island.'),
        ];
    }

    private function southernAfrica(): array
    {
        return [
            [
                'name' => 'Andimba Toivo ya Toivo',
                'aka' => 'Herman Toivo ja Toivo',
                'first_name' => 'Andimba',
                'last_name' => 'Toivo ya Toivo',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Namibia',
                'era' => '1960s',
                'ideologies' => ['Namibian independence', 'Anti-colonialism'],
                'affiliation' => ['South West Africa People\'s Organisation (SWAPO)'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Andimba Toivo ya Toivo was a co-founder of the South West Africa People\'s Organisation '
                    .'(SWAPO) and a leader of the Namibian independence struggle against South African rule. Tried in '
                    .'Pretoria under a Terrorism Act made retroactive to reach him, he was sentenced in 1968 to twenty '
                    .'years and imprisoned on Robben Island. The Black Panther named him in 1977 in Amnesty '
                    .'International\'s report on Namibian political prisoners. He was released in 1984 and later served in '
                    .'independent Namibia\'s government.',
                'cases' => [[
                    'charges' => 'Convicted of "terrorism" under South Africa\'s Terrorism Act (applied retroactively) for '
                        .'the SWAPO-led armed struggle for Namibian independence.',
                    'arrest_date' => '1966-01-01',
                    'sentenced_date' => '1968-01-01',
                    'release_date' => '1984-03-01',
                    'date_precision' => ['arrest_date' => 'year', 'sentenced_date' => 'year', 'release_date' => 'month'],
                    'convicted' => 'Yes — sentenced to 20 years in 1968; imprisoned on Robben Island until his release in March 1984.',
                    'sentence' => 'Twenty years\' imprisonment on Robben Island.',
                    'institution_name' => 'Robben Island Prison',
                    'institution_city' => 'Cape Town',
                    'institution_state' => 'South Africa',
                ]],
            ],
            [
                'name' => 'Peter Magubane',
                'aka' => 'Peter Sexford Magubane',
                'first_name' => 'Peter',
                'last_name' => 'Magubane',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'South Africa',
                'era' => '1970s',
                'ideologies' => ['Anti-apartheid'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Peter Magubane was an internationally recognized South African photojournalist for the '
                    .'Rand Daily Mail whose images of the 1976 Soweto uprising drew worldwide attention. The Black '
                    .'Panther reported that he was among the last of 102 people freed at the end of 1976 after months of '
                    .'detention without trial under the Internal Security Act, one of many arrests and bannings he '
                    .'endured; he had been beaten by riot police at least twice while covering the uprising. He continued '
                    .'documenting the anti-apartheid struggle for decades until his death in 2024.',
                'cases' => [[
                    'charges' => 'Detained without trial under South Africa\'s Internal Security Act during the crackdown '
                        .'following the 1976 Soweto uprising, which he had photographed.',
                    'arrest_date' => '1976-08-01',
                    'release_date' => '1976-12-01',
                    'date_precision' => ['arrest_date' => 'month', 'release_date' => 'month'],
                    'convicted' => 'Held in security detention without trial (about 123 days); released at the end of 1976.',
                    'sentence' => 'Detention without trial (roughly 123 days), one of numerous detentions and bannings.',
                ]],
            ],
            [
                'name' => 'Thenjiwe Mtintso',
                'aka' => 'Thenjiwe Mthintso',
                'first_name' => 'Thenjiwe',
                'last_name' => 'Mtintso',
                'gender' => 'Female',
                'race' => 'Black',
                'state' => 'South Africa',
                'era' => '1970s',
                'ideologies' => ['Anti-apartheid', 'Black Consciousness'],
                'affiliation' => ['Southern African News Agency'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Thenjiwe Mtintso was a South African journalist (the Southern African News Agency\'s '
                    .'Eastern Cape representative and a Daily Dispatch reporter) and Black Consciousness activist. The '
                    .'Black Panther reported that she was detained incommunicado under the Terrorism Act and then interned '
                    .'under the Internal Security Act, held 129 days before being released in December 1976 and '
                    .'immediately served with banning and house-arrest orders. Tortured in detention, she went into exile '
                    .'in 1978 and joined the ANC and its armed wing; she later became ANC deputy secretary-general and a '
                    .'South African ambassador.',
                'cases' => [[
                    'charges' => 'Detained incommunicado under the Terrorism Act and interned under the Internal Security '
                        .'Act, then banned and placed under house arrest, for anti-apartheid journalism and activism.',
                    'arrest_date' => '1976-08-01',
                    'release_date' => '1976-12-28',
                    'date_precision' => ['arrest_date' => 'month'],
                    'convicted' => 'Held about 129 days in security detention (released December 28, 1976), then banned and placed under house arrest.',
                    'sentence' => '129 days\' detention without trial, followed by banning and house-arrest orders.',
                ]],
            ],
            [
                'name' => 'Nat Serache',
                'aka' => 'Nathaniel Serache',
                'first_name' => 'Nat',
                'last_name' => 'Serache',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'South Africa',
                'era' => '1970s',
                'ideologies' => ['Anti-apartheid'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Nat Serache was a South African journalist for the Rand Daily Mail and a Soweto '
                    .'correspondent for the BBC whose reporting of the 1976 Soweto uprising drew wide notice. The Black '
                    .'Panther reported that he was interned under the Internal Security Act and, after being tortured for '
                    .'eleven successive days by the security police (who tried to force him to implicate Steve Biko), '
                    .'jumped bail and escaped to Botswana. South African forces bombed his Botswana home in 1985.',
                'cases' => [[
                    'charges' => 'Interned under the Internal Security Act and charged (after his reporting of the Soweto '
                        .'uprising) with "incitement to racial hostility"; tortured in detention.',
                    'arrest_date' => '1976-01-01',
                    'date_precision' => ['arrest_date' => 'year'],
                    'convicted' => 'Held and tortured in security detention; jumped bail and fled to Botswana in 1977 rather than face trial.',
                ]],
            ],
            [
                'name' => 'Eric Abraham',
                'aka' => 'Eric Anthony Abraham',
                'first_name' => 'Eric',
                'last_name' => 'Abraham',
                'gender' => 'Male',
                'race' => 'White',
                'state' => 'South Africa',
                'era' => '1970s',
                'ideologies' => ['Anti-apartheid'],
                'affiliation' => ['Southern African News Agency'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Eric Abraham was a South African journalist who founded the Southern African News Agency '
                    .'(SANA), a correspondent for the BBC and The Guardian. The Black Panther reported that in November '
                    .'1976 the Justice and Police Minister placed him under five-year banning and house-arrest orders '
                    .'that confined him and barred him from journalism, and that in early January 1977, amid death '
                    .'threats, he escaped over a border fence into Botswana. He was granted asylum in Britain and later '
                    .'became an Academy Award–winning film producer.',
                'cases' => [[
                    'charges' => 'Placed under a five-year banning order and house arrest under South Africa\'s security '
                        .'laws, silencing his anti-apartheid journalism.',
                    'arrest_date' => '1976-11-24',
                    'convicted' => 'Banned and placed under house arrest (November 24, 1976); escaped to Botswana in January 1977.',
                    'sentence' => 'Five-year banning order and house arrest.',
                ]],
            ],
            [
                'name' => 'Matthews Mabelane',
                'aka' => 'Matthews Mojo Mabelane',
                'first_name' => 'Matthews',
                'last_name' => 'Mabelane',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'South Africa',
                'era' => '1970s',
                'ideologies' => ['Anti-apartheid', 'Black Consciousness'],
                'affiliation' => ['Soweto Students\' Representative Council'],
                'in_custody' => false,
                'released' => false,
                'awaiting_trial' => false,
                'description' => 'Matthews Mabelane was a 23-year-old former member of the Soweto Students\' Representative '
                    .'Council detained without trial under the Terrorism Act. The Black Panther reported that he "fell" '
                    .'to his death from the tenth floor of Johannesburg\'s John Vorster Square police headquarters during '
                    .'interrogation in February 1977 — the eighteenth person to die in South African police custody. The '
                    .'original inquest ruled the fall accidental; a reopened inquest decades later found he had probably '
                    .'been thrown or fell while handcuffed.',
                'cases' => [[
                    'charges' => 'Detained without trial under South Africa\'s Terrorism Act; held for interrogation at '
                        .'John Vorster Square.',
                    'arrest_date' => '1977-01-21',
                    'death_in_custody_date' => '1977-02-15',
                    'convicted' => 'Never charged — detained without trial and died in police custody on February 15, 1977.',
                    'institution_name' => 'John Vorster Square (police headquarters)',
                    'institution_city' => 'Johannesburg',
                    'institution_state' => 'South Africa',
                ]],
            ],
        ];
    }
}
