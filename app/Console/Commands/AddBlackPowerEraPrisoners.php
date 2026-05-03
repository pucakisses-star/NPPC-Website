<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddBlackPowerEraPrisoners extends Command
{
    protected $signature = 'prisoners:add-black-power-era';
    protected $description = 'Add Chicago 7/8 defendants, additional BPP leaders, AIM leaders, and SNCC organizers.';

    public function handle(): int
    {
        $created = 0;
        $skipped = 0;

        $cookCounty = Institution::firstOrCreate(['name' => 'Cook County Jail'], ['city' => 'Chicago', 'state' => 'Illinois']);
        $oakland    = Institution::firstOrCreate(['name' => 'Alameda County Jail'], ['city' => 'Oakland', 'state' => 'California']);
        $sanLuis    = Institution::firstOrCreate(['name' => 'California Men\'s Colony'], ['city' => 'San Luis Obispo', 'state' => 'California']);
        $folsom     = Institution::firstOrCreate(['name' => 'Folsom State Prison'], ['city' => 'Folsom', 'state' => 'California']);
        $newHaven   = Institution::firstOrCreate(['name' => 'New Haven County Jail'], ['city' => 'New Haven', 'state' => 'Connecticut']);
        $parchman   = Institution::firstOrCreate(['name' => 'Mississippi State Penitentiary (Parchman Farm)'], ['city' => 'Sunflower County', 'state' => 'Mississippi']);
        $stPaul     = Institution::firstOrCreate(['name' => 'Federal courthouse, St. Paul (Wounded Knee trial)'], ['city' => 'St. Paul', 'state' => 'Minnesota']);
        $sdJail     = Institution::firstOrCreate(['name' => 'Pennington County Jail'], ['city' => 'Rapid City', 'state' => 'South Dakota']);
        $hennepin   = Institution::firstOrCreate(['name' => 'Hennepin County Jail'], ['city' => 'Minneapolis', 'state' => 'Minnesota']);

        $defendants = [];

        // ─── Chicago 7/8 ───
        $chicagoTrial = "The Chicago conspiracy trial (September 24, 1969 – February 20, 1970) prosecuted eight organizers of the demonstrations outside the 1968 Democratic National Convention under the new federal anti-riot provisions of the 1968 Civil Rights Act. The proceedings before Judge Julius Hoffman became one of the most chaotic and politically charged trials in American history. Bobby Seale's case was severed after Hoffman ordered him bound and gagged in court for demanding the right to represent himself; the remaining seven became known as the Chicago Seven. The jury acquitted all seven of conspiracy and acquitted Froines and Weiner on all charges, but convicted Davis, Dellinger, Hayden, Hoffman, and Rubin of crossing state lines with intent to incite a riot.\n\nThe defendants were arrested in March 1969, booked, and held in the Cook County Jail before posting bail in the days that followed. During the trial itself Judge Hoffman repeatedly held them in contempt and ordered them taken into custody from the courtroom — Bobby Seale most famously, but the other seven and their attorneys as well, accumulating 159 contempt citations between them. Each convicted defendant was sentenced to five years in prison and a $5,000 fine, with bail pending appeal granted within weeks; the Seventh Circuit Court of Appeals reversed all the riot-act convictions in November 1972 and the contempt convictions in May 1972, and the government declined to retry, so the underlying five-year sentences were never served.";

        $c8 = function (string $name, string $first, string $last, string $birth, ?string $death, string $bio, ?int $contemptMonths = null) use ($cookCounty, $chicagoTrial) {
            return [
                'data' => [
                    'name' => $name, 'first_name' => $first, 'last_name' => $last,
                    'birthdate' => $birth, 'death_date' => $death, 'gender' => 'Male',
                    'state' => 'Illinois', 'era' => '1960s',
                    'ideologies' => ['Anti-war', 'New Left'], 'in_custody' => false, 'released' => true,
                    'description' => $bio."\n\n".$chicagoTrial,
                ],
                'cases' => [[
                    'institution_id' => $cookCounty->id,
                    'charges'        => 'Conspiracy and crossing state lines with intent to incite a riot (Anti-Riot Act of 1968); contempt of court (during trial)',
                    'arrest_date'    => '1969-03-20',
                    'release_date'   => '1972-11-21',
                    'convicted'      => 'Convicted by jury (where applicable) of crossing state lines to incite a riot; all riot-act convictions reversed by the U.S. Court of Appeals for the Seventh Circuit in November 1972 and all contempt convictions reversed in May 1972',
                    'sentence'       => 'Initial booking custody after March 1969 arrest before bail was posted'.($contemptMonths ? "; cited for approximately {$contemptMonths} months of contempt by Judge Hoffman during trial (also reversed on appeal)" : '').'; sentenced to 5 years and a $5,000 fine on the riot-act count but released on bail pending appeal and never served the underlying sentence after convictions were reversed',
                    'judge'          => 'Julius J. Hoffman',
                ]],
            ];
        };

        $defendants[] = $c8('Abbie Hoffman', 'Abbott', 'Hoffman', '1936-11-30', '1989-04-12',
            'Abbie Hoffman was a co-founder of the Youth International Party ("Yippies") and one of the most flamboyant figures of the New Left, known for guerrilla-theater protests including the 1967 attempt to levitate the Pentagon and the disruption of the New York Stock Exchange. He was a central organizer of the 1968 Democratic National Convention demonstrations. After conviction in the Chicago trial he later went on the run for six and a half years following an unrelated 1973 cocaine arrest, surrendering in 1980. He died by suicide in 1989.',
            8);

        $defendants[] = $c8('Jerry Rubin', 'Jerry', 'Rubin', '1938-07-14', '1994-11-28',
            'Jerry Rubin was a Berkeley anti-Vietnam War organizer and co-founder, with Abbie Hoffman, of the Youth International Party. He helped plan the demonstrations outside the 1968 Democratic National Convention and was prosecuted as one of the Chicago Eight. He later abandoned radical politics and became a Wall Street investor — a transformation that itself became a 1970s cultural touchstone.',
            7);

        $defendants[] = $c8('Tom Hayden', 'Thomas', 'Hayden', '1939-12-11', '2016-10-23',
            'Tom Hayden was a founder of Students for a Democratic Society and the principal author of its 1962 Port Huron Statement, the founding document of the New Left. As a coordinator of the demonstrations against the 1968 Democratic National Convention he was prosecuted as one of the Chicago Eight. He went on to a 40-year career in California politics, serving in the State Assembly and Senate.',
            5);

        $defendants[] = $c8('Rennie Davis', 'Rennard', 'Davis', '1940-05-23', '2021-02-02',
            'Rennie Davis was a leading SDS organizer and one of the principal coordinators of the Mobe (National Mobilization Committee to End the War in Vietnam) protests at the 1968 Democratic National Convention. After his Chicago Eight conviction was reversed he gradually withdrew from political organizing.',
            null);

        $defendants[] = $c8('David Dellinger', 'David', 'Dellinger', '1915-08-22', '2004-05-25',
            'David Dellinger was the elder pacifist of the Chicago Eight defendants, a longtime Catholic Worker–adjacent radical pacifist who had served three years in federal prison during World War II for refusing to register for the draft. As chairman of the National Mobilization Committee against the Vietnam War he was a key planner of the 1968 Chicago demonstrations. He continued antiwar and labor organizing into his late 80s.',
            29);

        $defendants[] = $c8('John Froines', 'John', 'Froines', '1939-06-13', '2022-07-13',
            'John Froines was a chemistry professor and antiwar organizer who was acquitted of all charges in the Chicago Eight trial. He later went on to a long career in occupational health, serving in the Carter administration as a senior official at OSHA.',
            null);

        $defendants[] = $c8('Lee Weiner', 'Lee', 'Weiner', '1939-08-09', null,
            'Lee Weiner was a sociology graduate student at Northwestern University and one of the two Chicago Eight defendants acquitted on all charges. He later worked as a community organizer and as a senior staffer at the Anti-Defamation League.',
            null);

        // Bobby Seale - separate record because his case was severed
        $defendants[] = [
            'data' => [
                'name' => 'Bobby Seale', 'first_name' => 'Robert', 'middle_name' => 'George', 'last_name' => 'Seale',
                'birthdate' => '1936-10-22', 'death_date' => null, 'gender' => 'Male',
                'state' => 'California', 'era' => '1960s',
                'ideologies' => ['Black liberation', 'Socialist'],
                'affiliation' => ['Black Panther Party'], 'in_custody' => false, 'released' => true,
                'description' => "Bobby Seale was the co-founder, with Huey P. Newton, of the Black Panther Party for Self-Defense in Oakland in October 1966 and served as the party's chairman through its peak years. As one of the original Chicago Eight defendants charged in connection with the 1968 Democratic National Convention demonstrations, he repeatedly demanded the right to represent himself; Judge Julius Hoffman responded by ordering him bound to his chair and gagged for several days of the trial — an image that became one of the defining scenes of American legal history. Hoffman finally severed his case from the others' (which is why the remaining defendants became known as the Chicago Seven) and sentenced Seale to four years for contempt; that sentence was overturned on appeal.\n\nSeale was then transferred to Connecticut to stand trial in the New Haven Black Panther trials for the 1969 murder of party member Alex Rackley, in which the government alleged he had ordered the killing. After more than two years held in pretrial detention, the case ended in a hung jury in May 1971 and the prosecution was dropped. He was released and ran for mayor of Oakland in 1973, finishing a strong second.",
            ],
            'cases' => [
                [
                    'institution_id' => $cookCounty->id,
                    'charges'        => 'Conspiracy and crossing state lines with intent to incite a riot; contempt of court (Chicago Eight prosecution; case severed from other defendants)',
                    'arrest_date'    => '1969-03-20',
                    'release_date'   => '1972-05-11',
                    'convicted'      => 'Convicted of contempt by Judge Hoffman (4 years); contempt convictions reversed by Seventh Circuit, May 1972',
                    'sentence'       => '4 years for contempt; reversed on appeal',
                ],
                [
                    'institution_id' => $newHaven->id,
                    'charges'        => 'Murder, kidnapping, conspiracy (New Haven Black Panther trials, prosecution for the 1969 killing of Alex Rackley)',
                    'arrest_date'    => '1969-08-19',
                    'release_date'   => '1972-03-21',
                    'convicted'      => 'No — hung jury, prosecution dropped May 1971; Seale held in pretrial detention more than two and a half years',
                    'sentence'       => 'No conviction',
                ],
            ],
        ];

        // ─── Other major BPP leaders ───
        $defendants[] = [
            'data' => [
                'name' => 'Huey P. Newton', 'first_name' => 'Huey', 'middle_name' => 'Percy', 'last_name' => 'Newton',
                'birthdate' => '1942-02-17', 'death_date' => '1989-08-22', 'gender' => 'Male',
                'state' => 'California', 'era' => '1960s',
                'ideologies' => ['Black liberation', 'Marxist'],
                'affiliation' => ['Black Panther Party'], 'in_custody' => false, 'released' => true,
                'description' => "Huey P. Newton was the co-founder, with Bobby Seale, of the Black Panther Party for Self-Defense in October 1966 and served as the party's Minister of Defense and chief theorist throughout its life. On October 28, 1967, he was wounded in an Oakland traffic stop in which Officer John Frey was killed and Officer Herbert Heanes was wounded. Newton was charged with first-degree murder; the 'Free Huey' campaign that followed his arrest became the largest single mobilization of Black Panther Party history.\n\nHe was convicted of voluntary manslaughter in September 1968 and sentenced to two-to-fifteen years. The California Court of Appeal reversed the conviction in May 1970 on procedural grounds (an erroneous jury instruction); two retrials in 1971 both ended in hung juries and the charges were finally dismissed in December 1971. After several years of exile in Cuba (1974–1977) following an unrelated 1974 prosecution, he returned voluntarily and was eventually acquitted at retrial in 1979. He was killed in Oakland in 1989 in a dispute unrelated to his politics.",
            ],
            'cases' => [[
                'institution_id' => $oakland->id,
                'charges'        => 'First-degree murder of Oakland police officer John Frey',
                'arrest_date'    => '1967-10-28',
                'release_date'   => '1970-08-05',
                'convicted'      => 'Convicted of voluntary manslaughter, September 8, 1968; reversed by California Court of Appeal May 29, 1970; two retrials hung; charges dismissed December 1971',
                'sentence'       => 'Two-to-fifteen years; conviction overturned on appeal; held approximately 33 months',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Eldridge Cleaver', 'first_name' => 'Leroy', 'middle_name' => 'Eldridge', 'last_name' => 'Cleaver',
                'birthdate' => '1935-08-31', 'death_date' => '1998-05-01', 'gender' => 'Male',
                'state' => 'California', 'era' => '1960s',
                'ideologies' => ['Black liberation', 'Marxist'],
                'affiliation' => ['Black Panther Party'], 'in_custody' => false, 'released' => true,
                'description' => "Eldridge Cleaver was the Black Panther Party's Minister of Information and the author of Soul on Ice (1968), one of the foundational texts of Black liberation literature. He had spent most of the previous decade in Folsom and San Quentin on convictions for assault and rape (convictions he later disavowed and which independent historians have substantially questioned); he joined the Panthers shortly after his 1966 parole. On April 6, 1968, two days after the assassination of Martin Luther King Jr., he and 17-year-old Panther Bobby Hutton were involved in a shootout with Oakland police; Hutton was killed by police while attempting to surrender, and Cleaver was wounded and arrested. Facing reimprisonment as a parole violator, he jumped bail in November 1968 and fled first to Cuba, then to Algeria, then to France. He returned voluntarily to the United States in 1975 and surrendered; the original charges were eventually reduced and he served no further significant time.",
            ],
            'cases' => [[
                'institution_id' => $oakland->id,
                'charges'        => 'Attempted murder of Oakland police officers (April 6, 1968 shootout); parole violation',
                'arrest_date'    => '1968-04-06',
                'release_date'   => '1968-06-12',
                'convicted'      => 'Pleaded guilty to lesser charges after voluntary 1975 return from exile',
                'sentence'       => 'Served approximately 8 years on the run in Cuba, Algeria, and France; minor probation after surrender',
            ]],
        ];

        // ─── AIM ───
        $woundedKnee = "The 1973 Wounded Knee occupation began on February 27, 1973, when approximately 200 members of the Oglala Lakota and the American Indian Movement seized the town of Wounded Knee on the Pine Ridge Reservation, demanding hearings on broken treaties and the removal of corrupt tribal president Dick Wilson. The occupation lasted 71 days, during which two AIM members were killed by federal gunfire, twelve were wounded, and approximately 1,200 people were arrested. The federal prosecution of Means and Banks ran for nine months; on September 16, 1974, Chief Judge Fred Nichol of the District of South Dakota dismissed all charges, ruling that the FBI had altered evidence, conducted illegal electronic surveillance, and that an FBI special agent had committed perjury on the stand.";

        $defendants[] = [
            'data' => [
                'name' => 'Russell Means', 'first_name' => 'Russell', 'middle_name' => 'Charles', 'last_name' => 'Means',
                'birthdate' => '1939-11-10', 'death_date' => '2012-10-22', 'gender' => 'Male',
                'race' => 'Native American', 'state' => 'South Dakota', 'era' => '1970s',
                'ideologies' => ['Indigenous sovereignty', 'Anti-colonial'],
                'affiliation' => ['American Indian Movement', 'Oglala Lakota Nation'],
                'in_custody' => false, 'released' => true,
                'description' => "Russell Means was an Oglala Lakota leader of the American Indian Movement and one of the most visible Indigenous activists of his generation. He led major AIM actions including the 1969–71 occupation of Alcatraz, the 1972 Trail of Broken Treaties march and occupation of the Bureau of Indian Affairs in Washington, the 1972 occupation of Mount Rushmore, and — most prominently — the 1973 occupation of Wounded Knee, South Dakota. Federal prosecutors brought Means and Dennis Banks to trial in St. Paul on conspiracy, assault, and other charges; after a nine-month trial Judge Fred Nichol dismissed all charges on grounds of governmental misconduct.\n\nMeans was arrested more than a dozen times across his life on politically related charges. After a chaotic protest at the Sioux Falls courthouse in April 1974, he was prosecuted under South Dakota state law for assault and riot; he was convicted and, after his appeals were exhausted, served roughly a year at the South Dakota State Penitentiary at Sioux Falls beginning in 1979.\n\n".$woundedKnee,
            ],
            'cases' => [[
                'institution_id' => $stPaul->id,
                'charges'        => 'Conspiracy, assault, larceny, possession of firearms, interfering with federal officers (Wounded Knee occupation); plus subsequent South Dakota state prosecution for the 1974 Sioux Falls courthouse protest',
                'arrest_date'    => '1973-05-08',
                'release_date'   => '1980-12-01',
                'convicted'      => 'Wounded Knee charges dismissed September 16, 1974 by Chief Judge Fred Nichol citing FBI perjury, evidence tampering, and illegal surveillance; later convicted in South Dakota state court for the Sioux Falls courthouse protest',
                'sentence'       => 'Held briefly during initial arrests and pretrial detention; later served approximately one year at South Dakota State Penitentiary, Sioux Falls (1979–1980) on the state Sioux Falls protest conviction',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Dennis Banks', 'first_name' => 'Dennis', 'middle_name' => 'James', 'last_name' => 'Banks',
                'birthdate' => '1937-04-12', 'death_date' => '2017-10-29', 'gender' => 'Male',
                'race' => 'Native American', 'state' => 'Minnesota', 'era' => '1970s',
                'ideologies' => ['Indigenous sovereignty', 'Anti-colonial'],
                'affiliation' => ['American Indian Movement', 'Anishinaabe Nation'],
                'in_custody' => false, 'released' => true,
                'description' => "Dennis Banks was an Anishinaabe organizer and a co-founder of the American Indian Movement in Minneapolis in 1968. With Russell Means he led the 1972 Trail of Broken Treaties and the 1973 Wounded Knee occupation, and faced the same nine-month federal prosecution that ended in dismissal of all charges in September 1974. He later faced an unrelated South Dakota state prosecution arising from a 1973 protest at the Custer County courthouse; rather than surrender to that sentence he went on the run in 1975, living under sanctuary in California (granted by Governor Jerry Brown) and later on the Onondaga Nation in New York. He finally surrendered in 1984 and served 18 months at the South Dakota State Penitentiary at Sioux Falls.\n\n".$woundedKnee,
            ],
            'cases' => [[
                'institution_id' => $stPaul->id,
                'charges'        => 'Conspiracy, assault, larceny, possession of firearms, interfering with federal officers (Wounded Knee occupation); plus separate South Dakota state prosecution for the 1973 Custer County courthouse protest',
                'arrest_date'    => '1973-05-08',
                'release_date'   => '1986-04-01',
                'convicted'      => 'Wounded Knee charges dismissed by Chief Judge Fred Nichol on September 16, 1974; convicted in South Dakota state court for the Custer protest, served sentence after surrendering in 1984',
                'sentence'       => 'Held briefly during initial 1973 arrest and pretrial detention; later served 18 months at South Dakota State Penitentiary (1984–1986) on the state Custer protest conviction',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'John Trudell', 'first_name' => 'John', 'last_name' => 'Trudell',
                'birthdate' => '1946-02-15', 'death_date' => '2015-12-08', 'gender' => 'Male',
                'race' => 'Native American', 'state' => 'California', 'era' => '1970s',
                'ideologies' => ['Indigenous sovereignty', 'Anti-colonial'],
                'affiliation' => ['American Indian Movement'],
                'in_custody' => false, 'released' => true,
                'description' => "John Trudell was a Santee Dakota poet, musician, and the spokesperson for the 1969–71 Indigenous occupation of Alcatraz. He served as national chairman of the American Indian Movement from 1973 to 1979, during the height of FBI repression of AIM. On February 11, 1979 — twelve hours after he burned an American flag on the steps of the FBI headquarters in Washington in protest of the prosecution of Leonard Peltier — his pregnant wife Tina Manning Trudell, his three children Ricarda, Sunshine, and Eli, and his mother-in-law Leah Hicks-Manning all died in a fire that destroyed their home on the Duck Valley Reservation in Nevada. The fire was officially ruled accidental; Trudell and many AIM supporters maintained until his death that it was arson in retaliation for his organizing. Despite years of FBI surveillance documented in a roughly 17,000-page file, he was never convicted of a serious offense.",
            ],
            'cases' => [[
                'institution_id' => $sdJail->id,
                'charges'        => 'Various civil disobedience and protest-related charges across the 1970s',
                'arrest_date'    => '1969-11-20',
                'release_date'   => '1979-02-11',
                'convicted'      => 'Multiple minor convictions for civil disobedience; never convicted of a serious offense despite extensive FBI investigation',
                'sentence'       => 'Various short sentences',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Anna Mae Aquash', 'first_name' => 'Anna', 'middle_name' => 'Mae', 'last_name' => 'Aquash',
                'aka' => 'Naguset Eask', 'birthdate' => '1945-03-27', 'death_date' => '1975-12-12',
                'gender' => 'Female', 'race' => 'Native American', 'state' => 'South Dakota', 'era' => '1970s',
                'ideologies' => ['Indigenous sovereignty', 'Anti-colonial'],
                'affiliation' => ['American Indian Movement', "Mi'kmaq Nation"],
                'in_custody' => false, 'released' => false,
                'description' => "Anna Mae Pictou Aquash was a Mi'kmaq organizer from Nova Scotia who became one of the most prominent women in the American Indian Movement during the early 1970s, participating in the 1973 Wounded Knee occupation and traveling extensively for AIM. She was arrested multiple times during the period of intense FBI pressure on AIM. After a September 5, 1975 raid in Pine Ridge she was held in federal custody at the Pennington County Jail in Rapid City for several days on weapons charges before being released on bond; she was arrested again in November 1975 in Oregon and held briefly before once more being released. The FBI repeatedly tried to recruit her as an informant; she refused.\n\nShe disappeared shortly after that second release. In February 1976 her body was found at the bottom of an embankment on the Pine Ridge Reservation. The original autopsy ordered by the FBI ruled the death exposure; her hands were severed before her family could identify her. A second autopsy demanded by her family found that she had been killed by a single execution-style gunshot to the back of the head. Her death was investigated for decades; in 2004 and 2010, two AIM members — Arlo Looking Cloud and John Graham — were ultimately convicted of her murder. Significant evidence, much of it unresolved, has long pointed to a wider conspiracy involving allegations that she had been killed because AIM leadership came to suspect (incorrectly, in the view of her family) that she was an FBI informant.",
            ],
            'cases' => [[
                'institution_id'        => $sdJail->id,
                'charges'               => 'Weapons and explosives charges arising from the September 5, 1975 federal raid on the AIM property at Pine Ridge; later additional charges in Oregon',
                'arrest_date'           => '1975-09-05',
                'death_in_custody_date' => '1975-12-12',
                'convicted'             => 'Held in pretrial federal custody before release on bond; never tried',
                'sentence'              => 'Several days at Pennington County Jail on the September 1975 charges; further brief custody after the November 1975 Oregon arrest; killed shortly after her second release in unresolved circumstances on or about December 12, 1975',
            ]],
        ];

        // ─── SNCC ───
        $defendants[] = [
            'data' => [
                'name' => 'Stokely Carmichael', 'first_name' => 'Stokely', 'last_name' => 'Carmichael',
                'aka' => 'Kwame Ture', 'birthdate' => '1941-06-29', 'death_date' => '1998-11-15',
                'gender' => 'Male', 'race' => 'Black', 'state' => 'New York', 'era' => '1960s',
                'ideologies' => ['Black liberation', 'Pan-Africanist', 'Socialist'],
                'affiliation' => ['Student Nonviolent Coordinating Committee', 'Black Panther Party', "All-African People's Revolutionary Party"],
                'in_custody' => false, 'released' => true,
                'description' => "Stokely Carmichael (later Kwame Ture) was a Trinidadian-American civil rights organizer who became chairman of the Student Nonviolent Coordinating Committee in 1966 and popularized the slogan 'Black Power' that summer in Greenwood, Mississippi. He was arrested for the first time as a 19-year-old Freedom Rider in 1961 and served 49 days in Mississippi's Parchman Penitentiary in solitary confinement, in maximum security with the men on death row. He was arrested at least 32 more times over the following decade for civil rights organizing. He spent his last decades in Conakry, Guinea, organizing through the All-African People's Revolutionary Party.",
            ],
            'cases' => [[
                'institution_id' => $parchman->id,
                'charges'        => 'Breach of peace (Freedom Rides; refusing to leave segregated facilities)',
                'arrest_date'    => '1961-06-04',
                'release_date'   => '1961-07-23',
                'convicted'      => 'Yes — Mississippi state court, 1961',
                'sentence'       => '49 days at Mississippi State Penitentiary (Parchman); first of more than 30 civil-rights-era arrests',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Bob Moses', 'first_name' => 'Robert', 'middle_name' => 'Parris', 'last_name' => 'Moses',
                'birthdate' => '1935-01-23', 'death_date' => '2021-07-25',
                'gender' => 'Male', 'race' => 'Black', 'state' => 'Mississippi', 'era' => '1960s',
                'ideologies' => ['Civil rights', 'Educational justice'],
                'affiliation' => ['Student Nonviolent Coordinating Committee', 'Council of Federated Organizations', 'Algebra Project'],
                'in_custody' => false, 'released' => true,
                'description' => "Robert Parris Moses was the principal SNCC organizer of voter registration in Mississippi from 1961 onward and the architect of the 1964 Mississippi Freedom Summer. Working at enormous personal risk, he and the local Black Mississippians who worked with him built the Mississippi Freedom Democratic Party and its challenge to the regular all-white state delegation at the 1964 Democratic National Convention. He was arrested and beaten repeatedly across the early 1960s, including jailings in Liberty, McComb, and Greenwood; in 1961 he was charged in McComb after being beaten unconscious by a white assailant whom the local sheriff refused to prosecute. He later founded the Algebra Project to extend mathematical literacy as a civil rights tool.",
            ],
            'cases' => [[
                'institution_id' => $parchman->id,
                'charges'        => 'Various civil rights organizing charges (breach of peace, contributing to the delinquency of minors, parading without a permit)',
                'arrest_date'    => '1961-08-15',
                'release_date'   => '1961-12-06',
                'convicted'      => 'Multiple convictions across SNCC organizing in Mississippi 1961–1964',
                'sentence'       => 'Multiple short sentences',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'James Forman', 'first_name' => 'James', 'last_name' => 'Forman',
                'birthdate' => '1928-10-04', 'death_date' => '2005-01-10',
                'gender' => 'Male', 'race' => 'Black', 'state' => 'Tennessee', 'era' => '1960s',
                'ideologies' => ['Civil rights', 'Black liberation', 'Pan-Africanist'],
                'affiliation' => ['Student Nonviolent Coordinating Committee', 'Black Panther Party'],
                'in_custody' => false, 'released' => true,
                'description' => "James Forman served as executive secretary of the Student Nonviolent Coordinating Committee from 1961 to 1966, the years of SNCC's greatest impact, and was the principal administrator behind the southern voter registration campaigns. He was arrested numerous times during civil rights organizing in Mississippi, Alabama, and Georgia, including major arrests during the 1961 McComb voter registration drive and the 1965 Selma campaign. He later authored the 1969 Black Manifesto, which demanded reparations from white American churches and synagogues for slavery and segregation.",
            ],
            'cases' => [[
                'institution_id' => $parchman->id,
                'charges'        => 'Various civil rights organizing charges (breach of peace, parading without a permit)',
                'arrest_date'    => '1961-08-15',
                'release_date'   => '1965-03-15',
                'convicted'      => 'Multiple short convictions across the 1961–1965 SNCC organizing campaigns',
                'sentence'       => 'Multiple short sentences',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Diane Nash', 'first_name' => 'Diane', 'last_name' => 'Nash',
                'birthdate' => '1938-05-15', 'death_date' => null,
                'gender' => 'Female', 'race' => 'Black', 'state' => 'Tennessee', 'era' => '1960s',
                'ideologies' => ['Civil rights', 'Nonviolence'],
                'affiliation' => ['Student Nonviolent Coordinating Committee', 'Nashville Student Movement', 'Southern Christian Leadership Conference'],
                'in_custody' => false, 'released' => true,
                'description' => "Diane Nash was the central organizer of the Nashville sit-in movement, a co-founder of SNCC, and the strategist who refused to allow the Freedom Rides to be halted after the firebombing of the Anniston bus and the violence in Birmingham in May 1961 — recruiting Nashville student volunteers to continue the rides into Mississippi. She and the rest of that group were arrested in Jackson on May 24, 1961 and served 39 days at Mississippi's Parchman Penitentiary. In April 1962, two months pregnant, she chose not to appeal a sentence for 'contributing to the delinquency of minors' (for organizing high school students into the freedom movement) and was prepared to give birth in jail rather than appeal her conviction. She was a principal organizer of the 1965 Selma to Montgomery campaign. She was awarded the Presidential Medal of Freedom in 2022.",
            ],
            'cases' => [[
                'institution_id' => $parchman->id,
                'charges'        => 'Breach of peace (Freedom Rides); contributing to the delinquency of minors (Mississippi)',
                'arrest_date'    => '1961-05-24',
                'release_date'   => '1961-07-02',
                'convicted'      => 'Yes — Mississippi, 1961 and 1962',
                'sentence'       => '39 days at Parchman Penitentiary; later chose not to appeal a longer sentence',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'James Lawson', 'first_name' => 'James', 'middle_name' => 'Morris', 'last_name' => 'Lawson Jr.',
                'birthdate' => '1928-09-22', 'death_date' => '2024-06-09',
                'gender' => 'Male', 'race' => 'Black', 'state' => 'Tennessee', 'era' => '1960s',
                'ideologies' => ['Civil rights', 'Nonviolence', 'Pacifist'],
                'affiliation' => ['Fellowship of Reconciliation', 'Southern Christian Leadership Conference', 'Nashville Student Movement'],
                'in_custody' => false, 'released' => true,
                'description' => "Reverend James M. Lawson Jr. was the principal teacher of nonviolent direct action to the Southern civil rights movement. After spending 13 months in federal prison in 1951–52 as a draft refuser during the Korean War (he refused conscientious-objector status on the grounds that registration itself was cooperation with militarism), he traveled to India to study Gandhian satyagraha. From Nashville he led the workshops that produced the Nashville sit-in movement of 1959–60 and trained the activists who would become the core of SNCC, including Diane Nash, John Lewis, and James Bevel. He was repeatedly jailed for civil rights organizing across the South and was the principal architect of the 1968 Memphis sanitation workers' strike.",
            ],
            'cases' => [[
                'institution_id' => $parchman->id,
                'charges'        => 'Refusing to register for the draft (1951); various civil rights organizing charges across the South in the 1960s',
                'arrest_date'    => '1951-04-25',
                'release_date'   => '1952-05-01',
                'convicted'      => 'Yes — federal court, Ohio, 1951; multiple subsequent civil rights organizing convictions',
                'sentence'       => '13 months in federal prison (Mill Point Federal Prison Camp, West Virginia) for draft refusal; later, multiple short civil rights organizing sentences',
            ]],
        ];

        foreach ($defendants as $entry) {
            DB::transaction(function () use ($entry, &$created, &$skipped) {
                $name = $entry['data']['name'];
                if (Prisoner::where('name', $name)->exists()) {
                    $this->warn("Skipping {$name} — already exists.");
                    $skipped++;
                    return;
                }

                $prisoner = Prisoner::create($entry['data']);
                foreach ($entry['cases'] as $case) {
                    PrisonerCase::create(array_merge(['prisoner_id' => $prisoner->id], $case));
                }
                $this->info("Added {$prisoner->name}");
                $created++;
            });
        }

        $this->info("\nDone. Created {$created}, skipped {$skipped}.");

        return self::SUCCESS;
    }
}
