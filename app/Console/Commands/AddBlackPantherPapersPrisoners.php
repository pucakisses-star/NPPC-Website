<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds political prisoners documented in The Black Panther newspaper through
 * 1970 (the BPP organ archived at marxists.org). Four clusters the paper
 * organized around, limited to people NOT already in the database:
 *
 *  - The "Panther 21" (NY BPP, arrested April 2, 1969; acquitted May 13, 1971):
 *    the members who were jailed and are not yet recorded. (Already present and
 *    skipped: Dhoruba bin Wahad, Sundiata Acoli, Kuwasi Balagoon, Robert
 *    Collier.)
 *  - The New Haven Black Panther trials (Alex Rackley case, May 1969): the
 *    "New Haven Nine" and the cooperating-witness defendants. (Bobby Seale is
 *    already recorded and skipped.)
 *  - The Soledad Brothers (Fleeta Drumgo, John Clutchette — George Jackson is
 *    already recorded and skipped).
 *  - Allied movement prisoners the paper covered: Cleveland Sellers (Orangeburg),
 *    Herman Ferguson and Max Stanford (RAM), LeRoi Jones / Amiri Baraka (Newark),
 *    and BPP captain Warren Wells (the April 6, 1968 Oakland shootout).
 *
 * Honest gaps: where reliable sources give only a year, or conflict on a
 * day/year (e.g. Curtis Powell's and Warren Wells's birth/death), the field is
 * omitted rather than guessed and the detail is carried in prose. Idempotent:
 * skips any name already present (withUnderReview scope).
 */
final class AddBlackPantherPapersPrisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers';

    protected $description = 'Add Black Panther newspaper-era political prisoners (1967-1970) not yet in the database';

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
            $this->panther21(),
            $this->newHaven(),
            $this->soledadBrothers(),
            $this->alliedPrisoners(),
        );
    }

    /**
     * The Panther 21 — New York BPP members indicted April 2, 1969 for a
     * conspiracy to bomb stores, police stations and other sites, and acquitted
     * on all counts May 13, 1971. Shared scaffold; per-person bios and custody
     * details differ.
     */
    private function panther21(): array
    {
        $make = function (string $name, string $first, string $last, string $desc, array $o = []): array {
            $case = [
                'institution_name' => $o['institution'] ?? 'Manhattan House of Detention (The Tombs)',
                'institution_city' => 'New York',
                'institution_state' => 'New York',
                'charges' => 'Conspiracy to bomb department stores, police stations and other public sites and to murder police officers — the "Panther 21" indictment',
                'arrest_date' => '1969-04-02',
                'convicted' => $o['convicted'] ?? 'No — acquitted on all counts (May 13, 1971)',
                'prosecutor' => 'Assistant District Attorney Joseph A. Phillips (under Manhattan D.A. Frank Hogan)',
                'judge' => 'John M. Murtagh',
            ];
            if (! empty($o['heldToVerdict'])) {
                $case['incarceration_date'] = '1969-04-02';
                $case['release_date'] = '1971-05-13';
            }
            $r = [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => ($o['female'] ?? false) ? 'Female' : 'Male',
                'race' => 'Black',
                'state' => 'New York',
                'era' => '1960s',
                'ideologies' => ['Black Power', 'Black nationalism', 'Revolutionary socialism'],
                'affiliation' => ['Black Panther Party'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => $desc,
                'cases' => [$case],
            ];
            foreach (['aka', 'birthdate', 'death_date'] as $k) {
                if (! empty($o[$k])) {
                    $r[$k] = $o[$k];
                }
            }

            return $r;
        };

        return [
            $make('Lumumba Shakur', 'Lumumba', 'Shakur',
                'Lumumba Abdul Shakur (legal name Anthony Coston) co-founded and led the Harlem section of the New York Black Panther Party and was the lead-named defendant in the "Panther 21" case. After pre-dawn police raids on April 2, 1969 he was charged with conspiracy to bomb department stores, police stations and other sites and to murder police officers, and held on $100,000 bail in the Manhattan House of Detention known as "The Tombs." He was acquitted with all of his co-defendants on May 13, 1971, after the jury deliberated about 90 minutes. He was married to fellow defendant Afeni Shakur during the case and died in 1986.',
                ['aka' => 'Anthony Coston', 'birthdate' => '1943-01-09', 'death_date' => '1986-02-03', 'heldToVerdict' => true]),
            $make('Afeni Shakur', 'Afeni', 'Shakur',
                'Afeni Shakur (born Alice Faye Williams) was a leading member of the New York Black Panther Party and one of two women among the Panther 21 arrested April 2, 1969. Held on $100,000 bail at the Women\'s House of Detention, she was released on bail in January 1970 but returned to jail when her bail was revoked during the trial. Acting as her own co-counsel while pregnant, she helped win acquittal on all counts on May 13, 1971; a month later she gave birth to a son, the future rapper Tupac Amaru Shakur. She died in 2016.',
                ['female' => true, 'institution' => 'Women\'s House of Detention', 'aka' => 'Alice Faye Williams', 'birthdate' => '1947-01-10', 'death_date' => '2016-05-02', 'heldToVerdict' => true]),
            $make('Michael Tabor', 'Michael', 'Tabor',
                'Michael "Cetewayo" Tabor was a member and leader of the New York Black Panther Party and author of the widely circulated pamphlet "Capitalism Plus Dope Equals Genocide." Arrested in the Panther 21 case on April 2, 1969, he was held and then released on $100,000 bail. In February 1971, fearing for his life amid the COINTELPRO-driven split in the Party, he and co-defendant Dhoruba bin Wahad jumped bail and fled; both were acquitted in absentia when all defendants were cleared on May 13, 1971. He later settled in Lusaka, Zambia, where he worked as a writer and broadcaster until his death in 2010.',
                ['aka' => 'Cetewayo', 'birthdate' => '1946-12-13', 'death_date' => '2010-10-17',
                    'convicted' => 'No — acquitted on all counts in absentia (May 13, 1971); had jumped bail in February 1971']),
            $make('Joan Bird', 'Joan', 'Bird',
                'Joan Bird was a nursing student at Bronx Community College and a member of the New York Black Panther Party, one of the two women among the Panther 21. She had already been arrested in a separate January 1969 incident before being indicted in the April 2, 1969 conspiracy case and held on $100,000 bail at the Women\'s House of Detention, where she joined hunger strikes protesting jail conditions. She became one of the public faces of the case and a symbol of police brutality, and was acquitted with all co-defendants on May 13, 1971.',
                ['female' => true, 'institution' => 'Women\'s House of Detention', 'heldToVerdict' => true]),
            $make('Curtis Powell', 'Curtis', 'Powell',
                'Curtis Powell was an African-American biochemist who earned a PhD from the University of Stockholm in 1968 and held a cancer-research post before joining the New York Black Panther Party. He was arrested as one of the Panther 21 on April 2, 1969 and charged with conspiracy, attempted murder and arson, and was jailed until the acquittal of all the tried defendants on May 13, 1971. After his release he moved to Africa and worked on a vaccine for sleeping sickness. Born in Orange, New Jersey in 1935, he died in Queens, New York in 2002.',
                ['heldToVerdict' => true]),
            $make('Lee Berry', 'Lee', 'Berry',
                'Lee Berry was a New York Black Panther Party member and an Army veteran with epilepsy who, at the time of the April 2, 1969 Panther 21 indictment, was being treated at a Manhattan veterans\' hospital. Seized and held on $100,000 bail at the Manhattan House of Detention, he suffered repeated seizures and a stretch in solitary confinement during which his medication was withheld — becoming a notorious example of the defendants\' mistreatment. Too ill to continue, his case was severed by the court; the remaining defendants were acquitted on May 13, 1971.',
                ['convicted' => 'No — case severed because of his medical condition; the remaining Panther 21 defendants were acquitted (May 13, 1971)']),
            $make('Kwando Kinshasa', 'Kwando', 'Kinshasa',
                'Kwando Kinshasa (legal name William King Jr.) was a member of the New York Black Panther Party and one of the Panther 21 arrested April 2, 1969 on charges of conspiracy to bomb New York City sites and kill police. Held on $100,000 bail, he was among the 13 defendants who stood trial and were acquitted on all counts on May 13, 1971. He later became a scholar and professor of African-American studies.',
                ['aka' => 'William King Jr.', 'heldToVerdict' => true]),
            $make('Ali Bey Hassan', 'Ali Bey', 'Hassan',
                'Ali Bey Hassan (legal name John J. Casson) was a member of the New York Black Panther Party and one of the Panther 21 arrested April 2, 1969 on conspiracy charges to bomb New York City sites and kill police. Held on $100,000 bail at the Manhattan House of Detention, he was among the 13 defendants who stood trial and were acquitted on all counts on May 13, 1971.',
                ['aka' => 'John J. Casson', 'heldToVerdict' => true]),
            $make('Baba Odinga', 'Baba', 'Odinga',
                'Baba Odinga (legal name Walter Johnson) was a member of the New York Black Panther Party and one of the Panther 21 arrested April 2, 1969 on conspiracy charges to bomb New York City sites and kill police. Held on $100,000 bail, he was among the 13 defendants who stood trial and were acquitted on all counts on May 13, 1971. (He is a distinct person from the BPP/BLA member Sekou Odinga.)',
                ['aka' => 'Walter Johnson', 'heldToVerdict' => true]),
            $make('Abayama Katara', 'Abayama', 'Katara',
                'Abayama Katara (legal name Alex McKiever) was a young member of the New York Black Panther Party and one of the Panther 21 arrested April 2, 1969 on conspiracy charges to bomb New York City sites and kill police. He was among the 13 defendants who stood trial and were acquitted on all counts on May 13, 1971.',
                ['aka' => 'Alex McKiever', 'heldToVerdict' => true]),
            $make('Lee Roper', 'Lee', 'Roper',
                'Lee Roper (Panther name Shaba Om) was a member of the New York Black Panther Party and one of the Panther 21 arrested April 2, 1969 on conspiracy charges to bomb New York City sites and kill police. He was among the 13 defendants who stood trial and were acquitted on all counts on May 13, 1971.',
                ['aka' => 'Shaba Om', 'heldToVerdict' => true]),
            $make('Jamal Joseph', 'Jamal', 'Joseph',
                'Jamal Joseph (born Eddie Joseph) was, at age 16, the youngest of the Panther 21, arrested in the April 2, 1969 raids on charges of conspiracy to bomb New York City sites. Unable to raise the $100,000 bail, he spent about a year on Rikers Island before his case was severed because of his age; the remaining defendants were acquitted on May 13, 1971. He was later imprisoned again on a related matter, earned two degrees behind bars, and became a Columbia University film professor and author of the memoir "Panther Baby."',
                ['institution' => 'Rikers Island', 'birthdate' => '1953-01-10',
                    'convicted' => 'No — case severed because of his age; the remaining Panther 21 defendants were acquitted (May 13, 1971)']),
        ];
    }

    /**
     * The New Haven Black Panther trials, arising from the May 1969 killing of
     * Alex Rackley. Prosecutor Arnold Markle, Judge Harold M. Mulvey. The women
     * were held at the Connecticut Correctional Institution at Niantic.
     */
    private function newHaven(): array
    {
        $markle = 'State\'s Attorney Arnold Markle';
        $mulvey = 'Harold M. Mulvey';
        $niantic = 'Connecticut Correctional Institution, Niantic (state prison for women)';

        $base = function (string $name, string $first, string $last, string $desc, array $case, bool $female = false): array {
            return [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => $female ? 'Female' : 'Male',
                'race' => 'Black',
                'state' => 'Connecticut',
                'era' => '1960s',
                'ideologies' => ['Black Power', 'Black nationalism'],
                'affiliation' => ['Black Panther Party'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => $desc,
                'cases' => [$case],
            ];
        };

        return [
            array_merge($base('Ericka Huggins', 'Ericka', 'Huggins',
                'Ericka Huggins founded and led the New Haven chapter of the Black Panther Party after her husband, Panther leader John Huggins, was assassinated at UCLA in January 1969. Arrested in May 1969 in the Alex Rackley case, she was held for roughly two years at the Niantic women\'s prison and tried jointly with national chairman Bobby Seale. The "Free Bobby, Free Ericka" campaign drew some 12,000-15,000 demonstrators to the New Haven Green on May Day 1970. After the jury deadlocked 10-2 for her acquittal, Judge Mulvey dismissed the charges on May 25, 1971, and she went on to a long career as an educator.',
                [
                    'institution_name' => $niantic, 'institution_city' => 'Niantic', 'institution_state' => 'Connecticut',
                    'charges' => 'Murder, kidnapping and conspiracy in the death of Alex Rackley',
                    'arrest_date' => '1969-05-22',
                    'convicted' => 'No — charges dismissed by Judge Mulvey on May 25, 1971 after a hung jury (10-2 to acquit)',
                    'prosecutor' => $markle, 'judge' => $mulvey, 'release_date' => '1971-05-25',
                ], true), ['birthdate' => '1948-01-05']),
            $base('Lonnie McLucas', 'Lonnie', 'McLucas',
                'Lonnie McLucas was a Bridgeport, Connecticut member of the Black Panther Party and the only New Haven defendant convicted at trial. Arrested in Salt Lake City about a month after the May 1969 killing of Alex Rackley, he admitted firing a shot under the direction of George Sams. A racially mixed jury acquitted him of the most serious charges — including the capital charge of kidnapping resulting in death — but convicted him of conspiracy to commit murder after a then-record 33-hour deliberation. He was sentenced on September 18, 1970 to 12 to 15 years, of which he served only a few years, and died in 2016.',
                [
                    'charges' => 'Conspiracy to commit murder in the death of Alex Rackley (acquitted of kidnapping resulting in death, conspiracy to kidnap, and binding)',
                    'convicted' => 'Yes — convicted of conspiracy to commit murder; acquitted of the more serious charges',
                    'sentence' => '12 to 15 years (sentenced September 18, 1970); served only a few years',
                    'prosecutor' => $markle, 'judge' => $mulvey,
                ]) + ['birthdate' => '1945-10-17', 'death_date' => '2016-08-29'],
            $base('Warren Kimbro', 'Warren', 'Kimbro',
                'Warren Kimbro was the New Haven Black Panther in whose apartment Alex Rackley was held, and who fired the first shot in Rackley\'s killing on the order of George Sams. He pleaded guilty to second-degree murder and testified for the state, receiving a mandatory life sentence of which he served about four years. Granted educational parole in 1972, he earned a master\'s degree at Harvard and spent decades running a New Haven nonprofit helping formerly incarcerated people; a New Haven reentry program is named in his honor. He died in 2009.',
                [
                    'charges' => 'Second-degree murder in the death of Alex Rackley',
                    'convicted' => 'Yes — pleaded guilty to second-degree murder (turned state\'s evidence)',
                    'sentence' => 'Life (mandatory for second-degree murder); served about four years, granted educational parole in 1972',
                    'prosecutor' => $markle, 'judge' => $mulvey,
                ]) + ['birthdate' => '1934-04-29', 'death_date' => '2009-02-03'],
            $base('George Sams Jr.', 'George', 'Sams',
                'George W. Sams Jr. was a national Black Panther Party "field marshal" who supervised the interrogation of Alex Rackley at the New Haven Panther headquarters in May 1969 and gave the order for Rackley to be shot. He pleaded guilty to second-degree murder and became the state\'s key witness, testifying that national chairman Bobby Seale had ordered the killing — uncorroborated testimony that was the crux of the case against Seale. Captured in Toronto in August 1969, he was sentenced to life and paroled in 1974 after serving about four to five years.',
                [
                    'charges' => 'Second-degree murder in the death of Alex Rackley',
                    'convicted' => 'Yes — pleaded guilty to second-degree murder (turned state\'s evidence)',
                    'sentence' => 'Life; sentenced June 23, 1971 by Judge Mulvey; paroled in 1974',
                    'prosecutor' => $markle, 'judge' => $mulvey,
                ]),
            $base('Frances Carter', 'Frances', 'Carter',
                'Frances Carter was secretary of the New Haven chapter of the Black Panther Party, arrested in 1969 in connection with the Alex Rackley murder as one of the "New Haven Nine." A federal court ordered her freed on bail for insufficient evidence, but when she refused to testify before a grand jury she was jailed for contempt, serving about five and a half months at the Niantic women\'s prison — where, pregnant, she reportedly gave birth under guard. After being granted immunity she agreed to testify and was released; she was never convicted.',
                [
                    'institution_name' => $niantic, 'institution_city' => 'Niantic', 'institution_state' => 'Connecticut',
                    'charges' => 'Charges related to the death of Alex Rackley; later jailed for contempt of court for refusing to testify',
                    'convicted' => 'No — freed on bail for insufficient evidence; released after about 5.5 months once granted immunity',
                    'prosecutor' => $markle,
                ], true),
            $base('Loretta Luckes', 'Loretta', 'Luckes',
                'Loretta Luckes was a Black Panther from Bridgeport, Connecticut who was in New Haven on a weekend visit when she was arrested on May 28, 1969 in connection with the killing of Alex Rackley, becoming one of the "New Haven Nine." Pregnant when arrested, she had her baby while held at the Niantic women\'s prison. Along with Warren Kimbro and George Sams she pleaded guilty and agreed to testify for the state in exchange for the state\'s assurance that her parole would not be opposed.',
                [
                    'institution_name' => $niantic, 'institution_city' => 'Niantic', 'institution_state' => 'Connecticut',
                    'charges' => 'Charges related to the death of Alex Rackley',
                    'convicted' => 'Yes — pleaded guilty and testified for the state (parole not opposed)',
                    'prosecutor' => $markle,
                ], true),
            $base('Margaret Hudgins', 'Margaret', 'Hudgins',
                'Margaret "Peggy" Hudgins was one of the six women of the New Haven Black Panther chapter arrested in 1969 in connection with the Alex Rackley murder, part of the "New Haven Nine." She was held without bail at the Niantic women\'s prison and rearraigned on charges ranging from unlawful binding to kidnapping and murder, though contemporaneous reporting noted there was essentially no evidence against her beyond her occasional presence in the Kimbro apartment. She later testified as a defense witness, and the charges against her were ultimately dropped.',
                [
                    'institution_name' => $niantic, 'institution_city' => 'Niantic', 'institution_state' => 'Connecticut',
                    'charges' => 'Charges related to the death of Alex Rackley (from unlawful binding to kidnapping and murder)',
                    'convicted' => 'No — charges dropped; never convicted',
                    'prosecutor' => $markle,
                ], true),
            $base('Rose Marie Smith', 'Rose', 'Smith',
                'Rose Marie Smith was one of the six women of the New Haven Black Panther chapter arrested in 1969 over the Alex Rackley murder and prosecuted as part of the "New Haven Nine." Contemporaneous reporting described the evidence against her as scanty, yet she was held without bail at the Niantic women\'s prison during the 1970-71 proceedings. Her detention, like that of the other low-level New Haven women, became a rallying point in the "Free Bobby, Free Ericka" campaign, and she was never convicted.',
                [
                    'institution_name' => $niantic, 'institution_city' => 'Niantic', 'institution_state' => 'Connecticut',
                    'charges' => 'Charges related to the death of Alex Rackley',
                    'convicted' => 'No — charges dropped; never convicted',
                    'prosecutor' => $markle,
                ], true),
            $base('George Edwards', 'George', 'Edwards',
                'George Edwards was one of the two men (with Warren Kimbro) among the New Haven Black Panthers arrested in the city in May 1969 in connection with the Alex Rackley murder, part of the group prosecuted as the "New Haven Nine." He was held awaiting trial; contemporaneous accounts described his alleged role in the events as limited. His prosecution formed part of the broad sweep of New Haven Panthers whose detention fueled the national "Free Bobby, Free Ericka" campaign.',
                [
                    'institution_city' => 'New Haven', 'institution_state' => 'Connecticut',
                    'charges' => 'Charges related to the death of Alex Rackley',
                    'convicted' => 'No — not convicted',
                    'prosecutor' => $markle,
                ]),
            $base('Landon Williams', 'Landon', 'Williams',
                'Landon Williams was a national Black Panther Party field marshal sent from the California headquarters to New Haven in May 1969, who (with George Sams) directed the interrogation of Alex Rackley in the days before his murder. Arrested in Denver in June 1969, he fought extradition from Colorado for an extended period before being returned to Connecticut. In late 1971 he pleaded guilty to conspiracy to commit murder and received a suspended sentence.',
                [
                    'institution_city' => 'New Haven', 'institution_state' => 'Connecticut',
                    'charges' => 'Conspiracy to commit murder in the death of Alex Rackley',
                    'arrest_date' => '1969-06-05',
                    'convicted' => 'Yes — pleaded guilty to conspiracy to commit murder (late 1971)',
                    'sentence' => 'Suspended sentence',
                    'prosecutor' => $markle,
                ]),
            $base('Rory Hithe', 'Rory', 'Hithe',
                'Rory Hithe was one of the national Black Panther Party enforcers sent from the West Coast to New Haven in May 1969 and was implicated, with Landon Williams, in directing the killing of Alex Rackley. Arrested in Denver in June 1969, he fought extradition from Colorado alongside Williams. In late 1971 he pleaded guilty to conspiracy to commit murder and received a suspended sentence.',
                [
                    'institution_city' => 'New Haven', 'institution_state' => 'Connecticut',
                    'charges' => 'Conspiracy to commit murder in the death of Alex Rackley',
                    'arrest_date' => '1969-06-05',
                    'convicted' => 'Yes — pleaded guilty to conspiracy to commit murder (late 1971)',
                    'sentence' => 'Suspended sentence',
                    'prosecutor' => $markle,
                ]),
        ];
    }

    /**
     * The Soledad Brothers (Fleeta Drumgo and John Clutchette; George Jackson is
     * already in the database). Indicted February 1970 for the killing of
     * Soledad guard John V. Mills, acquitted March 27, 1972.
     */
    private function soledadBrothers(): array
    {
        $soledad = [
            'institution_name' => 'Soledad State Prison (California Training Facility)',
            'institution_city' => 'Soledad',
            'institution_state' => 'California',
        ];

        return [
            [
                'name' => 'Fleeta Drumgo',
                'first_name' => 'Fleeta',
                'last_name' => 'Drumgo',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1960s',
                'birthdate' => '1946-05-31',
                'death_date' => '1979-11-26',
                'ideologies' => ['Black revolutionary nationalism'],
                'affiliation' => ['Soledad Brothers'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Fleeta Drumgo was one of the three "Soledad Brothers," Black prisoners at Soledad State Prison indicted in February 1970 for the murder of correctional officer John V. Mills. The killing came days after a guard shot three Black prisoners in the prison yard, and the charges against Drumgo, George Jackson and John Clutchette were widely seen as political prosecutions of outspoken Black radicals. After an international "Free the Soledad Brothers" campaign, Drumgo and Clutchette were acquitted by a San Francisco jury on March 27, 1972. He was released in 1976 and was shot to death in Oakland in 1979.',
                'cases' => [array_merge($soledad, [
                    'charges' => 'First-degree murder of correctional officer John V. Mills',
                    'convicted' => 'No — acquitted March 27, 1972',
                    'release_date' => '1976-08-26',
                ])],
            ],
            [
                'name' => 'John Clutchette',
                'first_name' => 'John',
                'last_name' => 'Clutchette',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1960s',
                'birthdate' => '1943-03-24',
                'ideologies' => ['Black revolutionary nationalism'],
                'affiliation' => ['Soledad Brothers'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'John Clutchette was one of the three "Soledad Brothers," Black prisoners at Soledad State Prison indicted in February 1970 for the murder of guard John V. Mills — a charge supporters condemned as a politically motivated frame-up of militant Black inmates. The case, which followed the fatal shooting of three Black prisoners by a guard in the Soledad yard, became an international cause célèbre. Clutchette and Fleeta Drumgo were acquitted of the Mills killing on March 27, 1972 (George Jackson having been killed in 1971). He was later returned to prison on a separate charge and was finally paroled in 2018.',
                'cases' => [array_merge($soledad, [
                    'charges' => 'First-degree murder of correctional officer John V. Mills',
                    'convicted' => 'No — acquitted March 27, 1972',
                ])],
            ],
        ];
    }

    /**
     * Allied movement prisoners The Black Panther organized around in 1967-1970:
     * Cleveland Sellers (Orangeburg), Herman Ferguson and Max Stanford (RAM),
     * LeRoi Jones / Amiri Baraka (Newark), and BPP captain Warren Wells.
     */
    private function alliedPrisoners(): array
    {
        return [
            [
                'name' => 'Cleveland Sellers',
                'first_name' => 'Cleveland',
                'last_name' => 'Sellers',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'South Carolina',
                'era' => '1960s',
                'birthdate' => '1944-11-08',
                'ideologies' => ['Civil rights', 'Black Power'],
                'affiliation' => ['Student Nonviolent Coordinating Committee (SNCC)'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Cleveland Sellers was the national program director of the Student Nonviolent Coordinating Committee (SNCC). On the night of February 8, 1968, South Carolina highway patrolmen opened fire on student protesters at South Carolina State College in Orangeburg, killing three young Black men and wounding 27; Sellers was shot and then arrested, becoming the only person imprisoned in connection with the Orangeburg Massacre. Though acquitted of the charges tied to the campus shooting, he was convicted of "riot" over an earlier protest and served about seven months in 1973. South Carolina granted him a full pardon in 1993.',
                'cases' => [[
                    'institution_name' => 'South Carolina Department of Corrections',
                    'institution_state' => 'South Carolina',
                    'charges' => 'Riot — Orangeburg Massacre (acquitted of the charges tied to the February 8, 1968 campus shooting; convicted on the count tied to an earlier protest)',
                    'arrest_date' => '1968-02-08',
                    'incarceration_date' => '1973-02-01',
                    'release_date' => '1973-08-31',
                    'convicted' => 'Yes — convicted of riot (1970); pardoned July 20, 1993',
                    'sentence' => 'One year (served about seven months in 1973)',
                ]],
            ],
            [
                'name' => 'Herman Ferguson',
                'first_name' => 'Herman',
                'last_name' => 'Ferguson',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'New York',
                'era' => '1960s',
                'birthdate' => '1920-12-31',
                'death_date' => '2014-09-25',
                'ideologies' => ['Revolutionary Black nationalism', 'Black liberation', 'Pan-Africanism'],
                'affiliation' => ['Revolutionary Action Movement (RAM)', 'Organization of Afro-American Unity'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Herman Ferguson was a New York City educator and Black-liberation activist affiliated with Malcolm X\'s Organization of Afro-American Unity and the Revolutionary Action Movement (RAM). On June 21, 1967 he was arrested in a coordinated predawn police raid on RAM members in New York and Philadelphia, and in 1968 he was convicted in Queens of conspiracy to murder civil-rights leaders Roy Wilkins and Whitney Young. Released on bond, he fled to Guyana and lived in exile for some 19 years before voluntarily returning in 1989 to serve his sentence; FBI files later revealed COINTELPRO involvement in his prosecution.',
                'cases' => [[
                    'institution_state' => 'New York',
                    'charges' => 'Conspiracy to murder civil-rights leaders Roy Wilkins (NAACP) and Whitney Young (National Urban League)',
                    'arrest_date' => '1967-06-21',
                    'convicted' => 'Yes — convicted in 1968 in Queens, New York with co-defendant Arthur Harris',
                    'sentence' => '3½ to 7 years',
                ]],
            ],
            [
                'name' => 'Max Stanford',
                'first_name' => 'Maxwell',
                'last_name' => 'Stanford',
                'aka' => 'Muhammad Ahmad',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Pennsylvania',
                'era' => '1960s',
                'birthdate' => '1941-07-31',
                'ideologies' => ['Black nationalism', 'Revolutionary nationalism', 'Marxism-Leninism', 'Black Power'],
                'affiliation' => ['Revolutionary Action Movement (RAM)'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Maxwell "Max" Stanford (later Muhammad Ahmad) was a co-founder and national field chairman of the Revolutionary Action Movement (RAM), a revolutionary Black-nationalist organization and a primary target of the FBI\'s COINTELPRO. On June 21, 1967, amid nationwide RAM roundups, he was arrested in Philadelphia, with police alleging the group planned to incite a riot. He was not convicted in connection with these arrests; he dissolved RAM in 1968, converted to Islam in 1970, and became a professor of African-American studies.',
                'cases' => [[
                    'institution_city' => 'Philadelphia',
                    'institution_state' => 'Pennsylvania',
                    'charges' => 'Arrested in the 1967 nationwide RAM roundup; police alleged a plan to incite a riot (no conviction resulted)',
                    'arrest_date' => '1967-06-21',
                    'convicted' => 'No conviction resulted from the 1967 arrest',
                ]],
            ],
            [
                'name' => 'LeRoi Jones',
                'first_name' => 'LeRoi',
                'last_name' => 'Jones',
                'aka' => 'Amiri Baraka',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'New Jersey',
                'era' => '1960s',
                'birthdate' => '1934-10-07',
                'death_date' => '2014-01-09',
                'ideologies' => ['Black nationalism', 'Black Arts Movement', 'Black Power'],
                'affiliation' => ['Black Arts Movement'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Everett LeRoi Jones — later the poet and playwright Amiri Baraka — was arrested and beaten by Newark police during the July 1967 Newark rebellion and charged with unlawfully carrying two revolvers. An all-white jury convicted him in November 1967, and in January 1968 Judge Leon Kapp imposed the maximum sentence, reading Baraka\'s poem "Black People!" aloud in court as justification — a move that drew national protest from writers and civil-liberties advocates. The New Jersey Appellate Division reversed the conviction on April 21, 1969 and ordered a new trial.',
                'cases' => [[
                    'institution_city' => 'Newark',
                    'institution_state' => 'New Jersey',
                    'charges' => 'Unlawful possession of two revolvers; resisting arrest (Newark rebellion, July 1967)',
                    'arrest_date' => '1967-07-14',
                    'convicted' => 'Yes — convicted November 6, 1967; conviction reversed on appeal April 21, 1969',
                    'judge' => 'Leon W. Kapp',
                    'sentence' => '2½ to 3 years plus a $1,000 fine (sentenced January 4, 1968); reversed on appeal April 21, 1969',
                ]],
            ],
            [
                'name' => 'Warren Wells',
                'first_name' => 'Warren',
                'last_name' => 'Wells',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1960s',
                'ideologies' => ['Black Power', 'Black nationalism', 'Revolutionary socialism'],
                'affiliation' => ['Black Panther Party'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Warren William Wells was a captain and sergeant-at-arms of the Black Panther Party who was wounded in the April 6, 1968 West Oakland gun battle in which the police killed 17-year-old Bobby Hutton and wounded Eldridge Cleaver. Indicted with seven co-defendants on attempted-murder and assault charges, Wells was tried separately; after two trials ended in hung juries, a third found him guilty of assault with a deadly weapon while acquitting him of attempted murder, and he was given an indeterminate term of 1 to 15 years. The conviction was later reversed on appeal over the composition of the Alameda County grand jury (In re Wells, 1971). He became a leader of the California prison movement and a co-author of the 1970 Folsom Manifesto, and died in 2001. (Not to be confused with the NFL player of the same name.)',
                'cases' => [[
                    'institution_name' => 'Folsom State Prison',
                    'institution_city' => 'Folsom',
                    'institution_state' => 'California',
                    'charges' => 'Two counts of attempted murder and two counts of assault with a deadly weapon against Oakland police officers, arising from the April 6, 1968 West Oakland gun battle',
                    'arrest_date' => '1968-04-06',
                    'convicted' => 'Guilty of assault with a deadly weapon; acquitted of attempted murder (after two prior hung juries); conviction later reversed on grand-jury-composition grounds (In re Wells, 1971)',
                    'sentence' => 'Indeterminate term of 1 to 15 years',
                ]],
            ],
        ];
    }
}
