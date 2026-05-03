<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddNuclearResisterPrisoners extends Command
{
    protected $signature = 'prisoners:add-nuclear-resister';
    protected $description = 'Add anti-nuclear, Plowshares, SOA Watch, and Iraq-era military resisters identified from the nukeresister.org back-issue archive.';

    public function handle(): int
    {
        $created = 0;
        $skipped = 0;

        // Institutions
        $alderson    = Institution::firstOrCreate(['name' => 'Federal Reformatory for Women, Alderson'], ['city' => 'Alderson', 'state' => 'West Virginia']);
        $danbury     = Institution::firstOrCreate(['name' => 'FCI Danbury'], ['city' => 'Danbury', 'state' => 'Connecticut']);
        $pekin       = Institution::firstOrCreate(['name' => 'FCI Pekin'], ['city' => 'Pekin', 'state' => 'Illinois']);
        $lexington   = Institution::firstOrCreate(['name' => 'Federal Medical Center, Lexington'], ['city' => 'Lexington', 'state' => 'Kentucky']);
        $carswell    = Institution::firstOrCreate(['name' => 'Federal Medical Center, Carswell'], ['city' => 'Fort Worth', 'state' => 'Texas']);
        $lompoc      = Institution::firstOrCreate(['name' => 'FCI Lompoc'], ['city' => 'Lompoc', 'state' => 'California']);
        $sandstone   = Institution::firstOrCreate(['name' => 'FCI Sandstone'], ['city' => 'Sandstone', 'state' => 'Minnesota']);
        $sheridan    = Institution::firstOrCreate(['name' => 'FCI Sheridan'], ['city' => 'Sheridan', 'state' => 'Oregon']);
        $bopVaried   = Institution::firstOrCreate(['name' => 'Federal Bureau of Prisons (location varied)']);
        $pendleton   = Institution::firstOrCreate(['name' => 'Camp Pendleton brig'], ['city' => 'Camp Pendleton', 'state' => 'California']);
        $lewisBrig   = Institution::firstOrCreate(['name' => 'Joint Base Lewis-McChord brig'], ['city' => 'Tacoma', 'state' => 'Washington']);
        $fortKnox    = Institution::firstOrCreate(['name' => 'Fort Knox Regional Confinement Facility'], ['city' => 'Fort Knox', 'state' => 'Kentucky']);
        $fortStewart = Institution::firstOrCreate(['name' => 'Fort Stewart brig'], ['city' => 'Fort Stewart', 'state' => 'Georgia']);
        $limerick    = Institution::firstOrCreate(['name' => 'Limerick Prison'], ['city' => 'Limerick', 'state' => 'Ireland']);
        $atchison    = Institution::firstOrCreate(['name' => 'Atchison County Jail'], ['city' => 'Atchison', 'state' => 'Kansas']);
        $oakdale     = Institution::firstOrCreate(['name' => 'FCI Oakdale'], ['city' => 'Oakdale', 'state' => 'Louisiana']);
        $usMarshalsKnox = Institution::firstOrCreate(['name' => 'Knox County Detention Facility'], ['city' => 'Knoxville', 'state' => 'Tennessee']);
        $sciDallas   = Institution::firstOrCreate(['name' => 'SCI Dallas'], ['city' => 'Dallas', 'state' => 'Pennsylvania']);
        $sciMuncy    = Institution::firstOrCreate(['name' => 'State Correctional Institution – Muncy'], ['city' => 'Muncy', 'state' => 'Pennsylvania']);
        $usaCO       = Institution::firstOrCreate(['name' => 'United States District Court, District of Colorado']);
        $glynnGA     = Institution::firstOrCreate(['name' => 'Glynn County Jail'], ['city' => 'Brunswick', 'state' => 'Georgia']);
        $onondagaJail = Institution::firstOrCreate(['name' => 'Onondaga County Justice Center'], ['city' => 'Syracuse', 'state' => 'New York']);

        $defendants = [];

        // ─── Plowshares Eight (King of Prussia GE plant, September 9, 1980) ───
        $plow8Story = "On September 9, 1980, eight Catholic peace activists — Daniel Berrigan, Philip Berrigan, Carl Kabat, Anne Montgomery RSCJ, Molly Rush, Elmer Maas, John Schuchardt, and Dean Hammer — entered the General Electric Re-entry Systems Division plant in King of Prussia, Pennsylvania, where Mark 12A nuclear warhead nose cones were manufactured. They hammered on two of the nose cones and poured their own blood on documents, in a literal enactment of the prophet Isaiah's image of beating swords into plowshares (Isaiah 2:4). They were arrested at the scene and went to trial in Norristown the following year. Convicted of burglary, conspiracy, and criminal mischief, they were sentenced in 1981 to terms of 3 to 10 years; the case was on appeal for ten years before they were resentenced in 1990 to time served (just over a year). The action launched the modern Plowshares movement, which has carried out more than 100 symbolic disarmament actions worldwide since.";

        $defendants[] = [
            'data' => [
                'name' => 'Anne Montgomery', 'first_name' => 'Anne', 'last_name' => 'Montgomery',
                'inmate_number' => '03827-018',
                'aka' => 'Anne Montgomery RSCJ',
                'birthdate' => '1926-09-29', 'death_date' => '2012-08-27',
                'gender' => 'Female', 'race' => 'White', 'state' => 'New York', 'era' => '1980s',
                'ideologies' => ['Anti-nuclear', 'Pacifist', 'Catholic'],
                'affiliation' => ['Society of the Sacred Heart', 'Plowshares movement'],
                'in_custody' => false, 'released' => true,
                'description' => "Sister Anne Montgomery, RSCJ, of the Society of the Sacred Heart was one of the most prolific Plowshares activists in U.S. history, participating in seven Plowshares actions over thirty years and serving roughly six years of cumulative federal prison time. She was one of the original Plowshares Eight at the King of Prussia GE plant in 1980, then participated in Trident Plowshares (1982), Pershing Plowshares (1984), Riverside Plowshares (1992), Aegis Plowshares (1995), Trident II Plowshares (1995), and finally — at age 83 — the Disarm Now Plowshares action at Naval Base Kitsap-Bangor on November 2, 2009 alongside Bill 'Bix' Bichsel SJ, Steve Kelly SJ, Susan Crane, and Lynne Greenwald. Convicted in the Disarm Now case at age 84, she received a sentence of two months plus four months of home confinement. She died in 2012 at age 85.\n\n{$plow8Story}",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Burglary, conspiracy, and criminal mischief — Plowshares Eight action at GE Re-entry Systems Division, King of Prussia, Pennsylvania',
                'arrest_date' => '1980-09-09',
                'release_date' => '1990-04-10',
                'convicted' => 'Yes — Montgomery County, Pennsylvania, 1981',
                'sentence' => 'Sentenced to 3 to 10 years; on appeal for nearly a decade; resentenced in 1990 to time served (approximately one year). Plus subsequent federal sentences for six additional Plowshares actions through 2009.',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Molly Rush', 'first_name' => 'Molly', 'last_name' => 'Rush',
                'birthdate' => '1935-08-01', 'death_date' => null,
                'gender' => 'Female', 'race' => 'White', 'state' => 'Pennsylvania', 'era' => '1980s',
                'ideologies' => ['Anti-nuclear', 'Pacifist', 'Catholic'],
                'affiliation' => ['Plowshares movement', 'Thomas Merton Center'],
                'in_custody' => false, 'released' => true,
                'description' => "Molly Rush is a Pittsburgh peace and social-justice organizer and the founder of the Thomas Merton Center, which has anchored progressive movement work in western Pennsylvania since 1972. A mother of six, she joined the original Plowshares action at the General Electric plant in King of Prussia on September 9, 1980, becoming one of the eight defendants who launched the modern Plowshares movement. She was the only mother and the only person whose primary identity was that of a community organizer rather than a Catholic religious or clergy member. After the King of Prussia conviction she continued decades of organizing through the Merton Center on issues from nuclear disarmament to U.S. military intervention in Central America to police accountability. Her memoir, Molly Rush: Becoming Nonviolent, recounts the action and the movement it began.\n\n{$plow8Story}",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Burglary, conspiracy, and criminal mischief — Plowshares Eight action',
                'arrest_date' => '1980-09-09',
                'release_date' => '1990-04-10',
                'convicted' => 'Yes — Montgomery County, Pennsylvania, 1981',
                'sentence' => '3 to 10 years; resentenced 1990 to time served',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'John Schuchardt', 'first_name' => 'John', 'last_name' => 'Schuchardt',
                'birthdate' => '1939-01-01', 'death_date' => null,
                'gender' => 'Male', 'race' => 'White', 'state' => 'Pennsylvania', 'era' => '1980s',
                'ideologies' => ['Anti-nuclear', 'Pacifist'],
                'affiliation' => ['Plowshares movement', 'House of Peace'],
                'in_custody' => false, 'released' => true,
                'description' => "John Schuchardt is a former United States Marine Corps officer and lawyer who became one of the eight defendants in the original 1980 Plowshares action at the GE plant in King of Prussia, Pennsylvania. After his conviction and the long appellate fight that followed, he co-founded the House of Peace in Ipswich, Massachusetts, a community providing hospitality to refugees and people displaced by war, and continued his work as a movement attorney. His memoir of the action and the trial appears in Daniel Berrigan's account The Trial of the Plowshares Eight.\n\n{$plow8Story}",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Burglary, conspiracy, and criminal mischief — Plowshares Eight action',
                'arrest_date' => '1980-09-09',
                'release_date' => '1990-04-10',
                'convicted' => 'Yes — Montgomery County, Pennsylvania, 1981',
                'sentence' => '3 to 10 years; resentenced 1990 to time served',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Elmer Maas', 'first_name' => 'Elmer', 'last_name' => 'Maas',
                'birthdate' => '1935-05-29', 'death_date' => '2002-12-12',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Pennsylvania', 'era' => '1980s',
                'ideologies' => ['Anti-nuclear', 'Pacifist', 'Catholic'],
                'affiliation' => ['Plowshares movement'],
                'in_custody' => false, 'released' => true,
                'description' => "Elmer Maas was a musician, music teacher, and Catholic peace activist who joined the Plowshares Eight at the GE plant in King of Prussia in September 1980 and remained part of the Plowshares movement for the rest of his life, participating in additional actions and supporting prisoners of conscience. He died in December 2002 at age 67.\n\n{$plow8Story}",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Burglary, conspiracy, and criminal mischief — Plowshares Eight action',
                'arrest_date' => '1980-09-09',
                'release_date' => '1990-04-10',
                'convicted' => 'Yes — Montgomery County, Pennsylvania, 1981',
                'sentence' => '3 to 10 years; resentenced 1990 to time served',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Dean Hammer', 'first_name' => 'Dean', 'last_name' => 'Hammer',
                'birthdate' => null, 'death_date' => null,
                'gender' => 'Male', 'race' => 'White', 'state' => 'Pennsylvania', 'era' => '1980s',
                'ideologies' => ['Anti-nuclear', 'Pacifist', 'Catholic'],
                'affiliation' => ['Plowshares movement', 'Catholic Worker'],
                'in_custody' => false, 'released' => true,
                'description' => "Dean Hammer was a Catholic Worker who joined the original Plowshares Eight at the King of Prussia GE plant in September 1980, becoming one of the founding figures of the modern Plowshares movement.\n\n{$plow8Story}",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Burglary, conspiracy, and criminal mischief — Plowshares Eight action',
                'arrest_date' => '1980-09-09',
                'release_date' => '1990-04-10',
                'convicted' => 'Yes — Montgomery County, Pennsylvania, 1981',
                'sentence' => '3 to 10 years; resentenced 1990 to time served',
            ]],
        ];

        // ─── Disarm Now Plowshares (Bangor, November 2, 2009) ───
        $defendants[] = [
            'data' => [
                'name' => 'Lynne Greenwald', 'first_name' => 'Lynne', 'last_name' => 'Greenwald',
                'inmate_number' => '40672-086',
                'birthdate' => null, 'death_date' => null,
                'gender' => 'Female', 'race' => 'White', 'state' => 'Washington', 'era' => '2010s',
                'ideologies' => ['Anti-nuclear', 'Pacifist'],
                'affiliation' => ['Ground Zero Center for Nonviolent Action', 'Plowshares movement'],
                'in_custody' => false, 'released' => true,
                'description' => "Lynne Greenwald is a Pacific Northwest peace activist with the Ground Zero Center for Nonviolent Action and the fifth defendant in the November 2, 2009 Disarm Now Plowshares action at the Strategic Weapons Facility Pacific at Naval Base Kitsap-Bangor, Washington — the storage site for the U.S. Pacific fleet's Trident D-5 nuclear warheads. Greenwald, alongside Bill 'Bix' Bichsel SJ, Steve Kelly SJ, Susan Crane, and Sister Anne Montgomery RSCJ, cut through three security fences, reached the inner perimeter, and waited to be arrested. She was convicted by a federal jury in December 2010 and sentenced in March 2011 to two months in federal prison plus four months of home confinement.",
            ],
            'cases' => [[
                'institution_id' => $alderson->id,
                'charges' => 'Conspiracy; depredation of government property; trespass on a naval installation — Disarm Now Plowshares action at Naval Base Kitsap-Bangor',
                'arrest_date' => '2009-11-02',
                'incarceration_date' => '2011-03-28',
                'release_date' => '2011-05-28',
                'convicted' => 'Yes — federal jury verdict, U.S. District Court for the Western District of Washington, December 13, 2010',
                'sentence' => '2 months federal prison plus 4 months home confinement',
            ]],
        ];

        // ─── Sacred Earth and Space Plowshares II (October 6, 2002) ───
        $defendants[] = [
            'data' => [
                'name' => 'Jackie Hudson', 'first_name' => 'Jacqueline', 'last_name' => 'Hudson',
                'inmate_number' => '08808-039',
                'aka' => 'Jackie Hudson OP',
                'birthdate' => '1934-09-19', 'death_date' => '2011-08-03',
                'gender' => 'Female', 'race' => 'White', 'state' => 'Michigan', 'era' => '2000s',
                'ideologies' => ['Anti-nuclear', 'Pacifist', 'Catholic'],
                'affiliation' => ['Dominican Sisters of Grand Rapids', 'Plowshares movement', 'Ground Zero Center for Nonviolent Action'],
                'in_custody' => false, 'released' => true,
                'description' => "Sister Jacqueline 'Jackie' Hudson OP was a Dominican Sister of Grand Rapids and a longtime anti-nuclear organizer with the Ground Zero Center for Nonviolent Action in Poulsbo, Washington. With her two longtime partners-in-action Sisters Carol Gilbert and Ardeth Platte, she carried out the Sacred Earth and Space Plowshares II action on October 6, 2002, cutting through the perimeter fence at a Minuteman III intercontinental ballistic missile silo near New Raymer, Colorado, hammering on the silo cover, and pouring their own blood. Hudson was convicted with the others in April 2003 and sentenced on July 25, 2003 to 30 months in federal prison; she served at FCI Pekin alongside Sister Carol. After her release in 2005 she returned to Ground Zero organizing and was arrested again at Bangor multiple times in subsequent years. She died of pancreatic cancer in 2011 at age 76.",
            ],
            'cases' => [[
                'institution_id' => $pekin->id,
                'charges' => 'Sabotage of national defense premises (18 U.S.C. § 2155); depredation of government property — Sacred Earth and Space Plowshares II',
                'arrest_date' => '2002-10-06',
                'incarceration_date' => '2003-07-25',
                'release_date' => '2005-09-25',
                'convicted' => 'Yes — federal jury verdict, U.S. District Court for the District of Colorado, April 2003',
                'sentence' => '30 months federal prison; served at FCI Pekin, Illinois',
            ]],
        ];

        // ─── Y-12 Transform Now Plowshares (July 28, 2012) ───
        $defendants[] = [
            'data' => [
                'name' => 'Michael Walli', 'first_name' => 'Michael', 'last_name' => 'Walli',
                'inmate_number' => '92108-020',
                'birthdate' => '1949-01-01', 'death_date' => null,
                'gender' => 'Male', 'race' => 'White', 'state' => 'Tennessee', 'era' => '2010s',
                'ideologies' => ['Anti-nuclear', 'Pacifist', 'Catholic Worker'],
                'affiliation' => ['Dorothy Day Catholic Worker', 'Plowshares movement'],
                'in_custody' => false, 'released' => true,
                'description' => "Michael Walli is a Vietnam War veteran (he served two tours in Vietnam, 1968–1969) who returned to the U.S. and became a Catholic Worker, eventually living at the Dorothy Day Catholic Worker house in Washington, D.C. With Sister Megan Rice SHCJ and former U.S. Army officer Greg Boertje-Obed, he carried out the Transform Now Plowshares action in the early hours of July 28, 2012, cutting through three perimeter fences and walking into the Y-12 National Security Complex in Oak Ridge, Tennessee — one of the most heavily guarded weapons-grade uranium storage facilities in the world. They reached the wall of the Highly Enriched Uranium Materials Facility, hung peace banners, splashed human blood, painted slogans, and read a statement aloud while waiting for security. The action exposed catastrophic security failures at Y-12 and led to a federal investigation.\n\nWalli was convicted by a federal jury in Knoxville on May 9, 2013 of sabotage and depredation of government property. On February 18, 2014, U.S. District Judge Amul Thapar sentenced him to 62 months in federal prison. On May 8, 2015, the Sixth Circuit Court of Appeals overturned the sabotage conviction and ordered the three released for time served on the lesser depredation count. Walli was freed after roughly two years in federal custody.",
            ],
            'cases' => [[
                'institution_id' => $usMarshalsKnox->id,
                'charges' => 'Sabotage (18 U.S.C. § 2155); depredation of government property (18 U.S.C. § 1361) — Transform Now Plowshares action at Y-12 National Security Complex, Oak Ridge, Tennessee',
                'arrest_date' => '2012-07-28',
                'incarceration_date' => '2013-05-09',
                'release_date' => '2015-05-08',
                'convicted' => 'Convicted by federal jury, May 9, 2013; sabotage conviction reversed by U.S. Court of Appeals for the Sixth Circuit, May 8, 2015; resentenced to time served on the lesser depredation count',
                'sentence' => '62 months in federal prison; served approximately 2 years before appellate reversal',
            ]],
        ];

        // ─── Pentagon Papers / Daniel Ellsberg ───
        $defendants[] = [
            'data' => [
                'name' => 'Daniel Ellsberg', 'first_name' => 'Daniel', 'last_name' => 'Ellsberg',
                'birthdate' => '1931-04-07', 'death_date' => '2023-06-16',
                'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'era' => '1970s',
                'ideologies' => ['Anti-war', 'Anti-nuclear', 'Whistleblower'],
                'affiliation' => null,
                'in_custody' => false, 'released' => true,
                'description' => "Daniel Ellsberg was the U.S. military analyst who in 1971 leaked the Pentagon Papers — the 7,000-page top-secret Department of Defense study of U.S. decision-making in Vietnam from 1945 to 1967 — to The New York Times, exposing decades of government deception about the Vietnam War. After publication began on June 13, 1971, the Nixon administration sought a prior-restraint injunction against the Times; the Supreme Court ruled against the administration on June 30, 1971 in New York Times Co. v. United States. Ellsberg surrendered on June 28, 1971 and was indicted alongside RAND colleague Anthony Russo on conspiracy, theft, and Espionage Act charges. Their trial in Los Angeles in 1973 ended on May 11, 1973 when Federal District Judge William Matthew Byrne Jr. dismissed all charges with prejudice on the grounds that the Nixon administration's 'plumbers' unit had broken into the office of Ellsberg's psychiatrist, illegally wiretapped the defendants, and made an improper offer of the FBI directorship to the trial judge.\n\nFor the rest of his life Ellsberg was the most prominent U.S. anti-war and anti-nuclear activist of his generation. He was arrested approximately 90 times for civil disobedience, almost always at nuclear weapons sites — the Nevada Test Site, Bangor, Vandenberg, Y-12, Lawrence Livermore — serving short jail terms repeatedly into his 80s. He was a tireless public advocate for whistleblowers including Chelsea Manning, Edward Snowden, and Julian Assange. His final book, The Doomsday Machine: Confessions of a Nuclear War Planner (2017), drew on documents he had also copied from the RAND vault in 1971 but had not released at the time. He died in California on June 16, 2023 at age 92.",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Conspiracy, theft, Espionage Act violations (Pentagon Papers); plus dozens of subsequent civil-disobedience arrests at nuclear weapons sites across his life',
                'arrest_date' => '1971-06-28',
                'release_date' => '1973-05-11',
                'convicted' => 'Pentagon Papers charges dismissed with prejudice on May 11, 1973 by Judge William Matthew Byrne Jr.; multiple subsequent convictions for civil disobedience',
                'sentence' => 'No federal time on the Pentagon Papers charges (dismissed); cumulative weeks across approximately 90 subsequent arrests for anti-nuclear civil disobedience',
            ]],
        ];

        // ─── Currently imprisoned: Daithí O'Corrain (Ireland, Shannon Airport, April 2026) ───
        $defendants[] = [
            'data' => [
                'name' => "Daithí O'Corrain", 'first_name' => 'Daithí', 'last_name' => "O'Corrain",
                'birthdate' => '1979-01-01', 'death_date' => null,
                'gender' => 'Male', 'race' => 'White', 'state' => 'Ireland', 'era' => '2020s',
                'ideologies' => ['Anti-war', 'Pacifist'],
                'affiliation' => ['Shannonwatch'],
                'in_custody' => true, 'released' => false,
                'description' => "Daithí O'Corrain is an Irish peace activist who, on April 11, 2026, broke into Shannon Airport in County Clare, Ireland, and damaged a U.S. military warplane parked there in transit. Shannon Airport has been used as a transit hub by the U.S. military for more than two decades — a long-running source of controversy in Ireland, where the Constitution requires neutrality. He was arrested at the scene and is being held at Limerick Prison (Mulgrave Street, Limerick, V94 P8N1, Ireland), where he is the sole prisoner currently listed on nukeresister.org's 'Inside & Out' page. The action follows a long line of Plowshares-style direct actions at Shannon by Irish peace activists, including the 2003 'Pitstop Ploughshares' case in which five activists damaged a U.S. military aircraft and were ultimately acquitted on a necessity defense.",
            ],
            'cases' => [[
                'institution_id' => $limerick->id,
                'charges' => 'Allegedly causing damage to a U.S. warplane at Shannon Airport',
                'arrest_date' => '2026-04-11',
                'incarceration_date' => '2026-04-11',
                'release_date' => null,
                'convicted' => 'Awaiting trial',
                'sentence' => null,
            ]],
        ];

        // ─── Voices for Creative Nonviolence / drone protests ───
        $defendants[] = [
            'data' => [
                'name' => 'Brian Terrell', 'first_name' => 'Brian', 'last_name' => 'Terrell',
                'inmate_number' => '06125-026',
                'birthdate' => '1956-01-01', 'death_date' => null,
                'gender' => 'Male', 'race' => 'White', 'state' => 'Iowa', 'era' => '2010s',
                'ideologies' => ['Anti-war', 'Pacifist', 'Catholic Worker'],
                'affiliation' => ['Voices for Creative Nonviolence', 'Strangers and Guests Catholic Worker'],
                'in_custody' => false, 'released' => true,
                'description' => "Brian Terrell is co-coordinator of Voices for Creative Nonviolence and lives at the Strangers and Guests Catholic Worker farm in Maloy, Iowa. He has been one of the most consistently arrested U.S. anti-war activists of the past two decades, focusing especially on U.S. drone warfare. In 2012 he was sentenced to six months in federal prison for crossing the line at Whiteman Air Force Base in Missouri — the home of the U.S. Air Force's Predator and Reaper drone fleets — and served his sentence at FCI Yankton. He has since been arrested repeatedly at Creech Air Force Base in Nevada and at Hancock Field Air National Guard Base in New York for similar drone-protest line-crossings.",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Trespass — line-crossing at Whiteman Air Force Base in protest of U.S. drone warfare; multiple subsequent drone-protest arrests',
                'arrest_date' => '2012-04-15',
                'incarceration_date' => '2012-11-30',
                'release_date' => '2013-05-30',
                'convicted' => 'Yes — federal court, Western District of Missouri, 2012',
                'sentence' => '6 months federal prison for the 2012 Whiteman action; cumulative weeks across many subsequent arrests',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Buddy Bell', 'first_name' => 'Buddy', 'middle_name' => 'R.', 'last_name' => 'Bell',
                'birthdate' => null, 'death_date' => null,
                'gender' => 'Male', 'race' => 'White', 'state' => 'Illinois', 'era' => '2010s',
                'ideologies' => ['Anti-war', 'Pacifist'],
                'affiliation' => ['Voices for Creative Nonviolence'],
                'in_custody' => false, 'released' => true,
                'description' => "Buddy R. Bell is a Voices for Creative Nonviolence organizer who has been arrested repeatedly for civil disobedience at Whiteman Air Force Base, Creech Air Force Base, and Hancock Field Air National Guard Base in protest of U.S. drone warfare. He has served multiple short federal sentences for trespass at military installations during the post-9/11 drone era.",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Trespass at Whiteman AFB, Creech AFB, and Hancock Field ANGB — drone-protest line-crossings',
                'arrest_date' => '2014-06-01',
                'release_date' => '2018-12-31',
                'convicted' => 'Multiple federal trespass convictions',
                'sentence' => 'Cumulative months in federal custody across multiple drone-protest sentences',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Mary Anne Grady-Flores', 'first_name' => 'Mary Anne', 'last_name' => 'Grady-Flores',
                'inmate_number' => '12001966',
                'birthdate' => '1957-01-01', 'death_date' => null,
                'gender' => 'Female', 'race' => 'White', 'state' => 'New York', 'era' => '2010s',
                'ideologies' => ['Anti-war', 'Pacifist', 'Catholic Worker'],
                'affiliation' => ['Ithaca Catholic Worker', 'Upstate Drone Action'],
                'in_custody' => false, 'released' => true,
                'description' => "Mary Anne Grady-Flores is an Ithaca Catholic Worker, photographer, and one of three Grady sisters (with Clare Grady and Teresa Grady) who have been central to the U.S. anti-drone movement at Hancock Field Air National Guard Base in upstate New York — the home of the 174th Attack Wing, which pilots Reaper drones over Afghanistan. She was sentenced to one year in the Onondaga County Justice Center in 2016 for re-entering the base in violation of an Order of Protection that the base commander had obtained against her, after she refused to sign a guilty plea that would have required her to stop her drone-protest work.",
            ],
            'cases' => [[
                'institution_id' => $onondagaJail->id,
                'charges' => 'Criminal contempt of court — re-entering Hancock Field Air National Guard Base in violation of an Order of Protection',
                'arrest_date' => '2014-02-12',
                'incarceration_date' => '2016-01-19',
                'release_date' => '2016-07-19',
                'convicted' => 'Yes — DeWitt Town Court, New York, 2015',
                'sentence' => '1 year in Onondaga County Justice Center; served 6 months',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Teresa Grady', 'first_name' => 'Teresa', 'last_name' => 'Grady',
                'inmate_number' => '13183-052',
                'birthdate' => null, 'death_date' => null,
                'gender' => 'Female', 'race' => 'White', 'state' => 'New York', 'era' => '2010s',
                'ideologies' => ['Anti-war', 'Pacifist', 'Catholic Worker'],
                'affiliation' => ['Ithaca Catholic Worker', 'Upstate Drone Action'],
                'in_custody' => false, 'released' => true,
                'description' => "Teresa Grady is an Ithaca Catholic Worker and one of three Grady sisters (with Clare and Mary Anne) at the heart of the U.S. anti-drone movement at Hancock Field Air National Guard Base in upstate New York. She has been arrested repeatedly for line-crossing at Hancock and has served multiple short jail sentences for trespass and contempt arising from those actions.",
            ],
            'cases' => [[
                'institution_id' => $onondagaJail->id,
                'charges' => 'Trespass and criminal contempt — Hancock Field Air National Guard Base drone protests',
                'arrest_date' => '2012-10-25',
                'release_date' => '2018-12-31',
                'convicted' => 'Multiple convictions',
                'sentence' => 'Cumulative weeks across multiple short sentences',
            ]],
        ];

        // ─── Catholic Worker / Plowshares additional ───
        $defendants[] = [
            'data' => [
                'name' => 'Steve Baggarly', 'first_name' => 'Steve', 'last_name' => 'Baggarly',
                'inmate_number' => '03611-036',
                'birthdate' => null, 'death_date' => null,
                'gender' => 'Male', 'race' => 'White', 'state' => 'Virginia', 'era' => '2000s',
                'ideologies' => ['Anti-nuclear', 'Pacifist', 'Catholic Worker'],
                'affiliation' => ['Sadako Peace House Catholic Worker', 'Plowshares movement'],
                'in_custody' => false, 'released' => true,
                'description' => "Steve Baggarly is a Catholic Worker at the Sadako Peace House in Norfolk, Virginia, and a participant in multiple Plowshares actions targeting the U.S. naval base at Norfolk and other Atlantic-fleet nuclear infrastructure. He has served several federal prison terms for symbolic disarmament actions at the Norfolk Naval Base and at the Pentagon.",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Trespass and depredation of government property — Plowshares actions at Norfolk Naval Base and other military installations',
                'arrest_date' => '1997-12-28',
                'release_date' => '2010-06-01',
                'convicted' => 'Multiple federal convictions',
                'sentence' => 'Cumulative years in federal prison across multiple Plowshares actions',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'John LaForge', 'first_name' => 'John', 'last_name' => 'LaForge',
                'birthdate' => '1956-01-01', 'death_date' => null,
                'gender' => 'Male', 'race' => 'White', 'state' => 'Wisconsin', 'era' => '2010s',
                'ideologies' => ['Anti-nuclear', 'Pacifist'],
                'affiliation' => ['Nukewatch'],
                'in_custody' => false, 'released' => true,
                'description' => "John LaForge has been a coordinator of Nukewatch in Luck, Wisconsin since the mid-1980s and has been one of the longest-running U.S. anti-nuclear organizers, focused first on Project ELF (the Navy's extremely-low-frequency submarine command system in northern Wisconsin and Michigan, which Nukewatch helped force the Navy to shut down in 2004) and later on the U.S. nuclear arsenal stationed at Büchel Air Base in Germany. In 2018 he traveled to Germany and entered Büchel multiple times in symbolic disarmament actions; in January 2023 he became the first U.S. citizen since the Cold War to be sentenced to a German prison for anti-nuclear protest, serving approximately seven months at Koblenz before his release in summer 2023.",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Trespass at Project ELF facilities (Wisconsin/Michigan) and at Büchel Air Base (Germany); German felony Hausfriedensbruch',
                'arrest_date' => '1987-04-15',
                'release_date' => '2023-08-01',
                'convicted' => 'Multiple convictions across decades; sentenced in Germany 2023',
                'sentence' => 'Cumulative years in custody across multiple jurisdictions; approximately 7 months at Koblenz Open Prison, Germany in 2023',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Peter DeMott', 'first_name' => 'Peter', 'last_name' => 'DeMott',
                'birthdate' => '1947-01-01', 'death_date' => '2009-09-27',
                'gender' => 'Male', 'race' => 'White', 'state' => 'New York', 'era' => '1980s',
                'ideologies' => ['Anti-nuclear', 'Pacifist', 'Catholic Worker'],
                'affiliation' => ['Catholic Worker', 'Plowshares movement'],
                'in_custody' => false, 'released' => true,
                'description' => "Peter DeMott was a Catholic Worker, Vietnam War veteran, and prolific Plowshares activist. In December 1980, just three months after the original Plowshares Eight, he carried out a one-man action against the USS Florida Trident submarine at Electric Boat in Groton, Connecticut, climbing into the dry dock and using a sledgehammer on the missile hatches. He participated in multiple subsequent Plowshares actions including the Pershing Plowshares (1984) and the St. Patrick's Day Four (2003) action against the Ithaca Army Recruiting Station on the eve of the Iraq invasion. He was the husband of Ellen Grady (sister of Mary Anne, Clare, and Teresa Grady) and the father of four. He died in 2009 at age 62.",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Multiple Plowshares-related charges including damage to government property (Electric Boat 1980, Pershing Plowshares 1984, St. Patrick\'s Day Four 2003)',
                'arrest_date' => '1980-12-13',
                'release_date' => '2008-12-31',
                'convicted' => 'Multiple federal and state convictions across nearly three decades',
                'sentence' => 'Cumulative years across multiple Plowshares sentences',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Sister Diane Pinchot', 'first_name' => 'Diane', 'middle_name' => 'T.', 'last_name' => 'Pinchot',
                'birthdate' => null, 'death_date' => null,
                'gender' => 'Female', 'race' => 'White', 'state' => 'Ohio', 'era' => '2000s',
                'ideologies' => ['Anti-nuclear', 'Pacifist', 'Catholic'],
                'affiliation' => ['Ursuline Sisters of Cleveland', 'Plowshares movement'],
                'in_custody' => false, 'released' => true,
                'description' => "Sister Diane Therese Pinchot OSU is an Ursuline Sister of Cleveland and a participant in multiple Plowshares-style symbolic disarmament actions, including SOA Watch line-crossings at Fort Benning and protests at U.S. nuclear weapons facilities. She has served several short federal prison terms for trespass.",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Trespass at the U.S. Army School of the Americas and other military installations',
                'arrest_date' => '2003-11-23',
                'release_date' => '2010-12-31',
                'convicted' => 'Multiple federal trespass convictions',
                'sentence' => 'Several months federal prison across multiple sentences',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Jerome Zawada', 'first_name' => 'Jerome', 'middle_name' => 'A.', 'last_name' => 'Zawada',
                'aka' => 'Father Jerry Zawada OFM',
                'birthdate' => '1937-04-23', 'death_date' => '2017-07-22',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Wisconsin', 'era' => '2000s',
                'ideologies' => ['Anti-nuclear', 'Anti-war', 'Pacifist', 'Catholic'],
                'affiliation' => ['Order of Friars Minor (Franciscans)', 'SOA Watch', 'Nevada Desert Experience'],
                'in_custody' => false, 'released' => true,
                'description' => "Father Jerome 'Jerry' Zawada OFM was a Franciscan friar of the Assumption of the Blessed Virgin Mary Province (later the Sacred Heart Province) and a peace activist whose six decades of ministry included extensive work in the Philippines, in Nicaragua during the Contra war, and at U.S. military installations. He was arrested dozens of times across his life, primarily for SOA Watch line-crossings at Fort Benning, Georgia, and for protests at the Nevada Test Site, serving multiple short federal sentences. He died in 2017 at age 80.",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Trespass at the U.S. Army School of the Americas (Fort Benning) and at the Nevada Test Site — multiple federal convictions across decades',
                'arrest_date' => '1990-04-15',
                'release_date' => '2010-12-31',
                'convicted' => 'Multiple federal trespass convictions',
                'sentence' => 'Cumulative months in federal prison across many short sentences',
            ]],
        ];

        // ─── SOA Watch line-crossers ───
        $defendants[] = [
            'data' => [
                'name' => 'Luis Barrios', 'first_name' => 'Luis', 'last_name' => 'Barrios',
                'inmate_number' => '93613-020',
                'aka' => 'Father Luis Barrios',
                'birthdate' => '1952-01-01', 'death_date' => null,
                'gender' => 'Male', 'race' => 'Hispanic', 'state' => 'New York', 'era' => '2000s',
                'ideologies' => ['Anti-war', 'Liberation theology'],
                'affiliation' => ['Episcopal Church', 'SOA Watch', 'IFCO/Pastors for Peace'],
                'in_custody' => false, 'released' => true,
                'description' => "Father Luis Barrios is a Puerto Rican-born Episcopal priest, professor of psychology at John Jay College of Criminal Justice in New York, and a longtime liberation-theology activist. He has been arrested repeatedly for SOA Watch line-crossings at Fort Benning, Georgia, and has served multiple short federal sentences for trespass at the U.S. Army School of the Americas. He has also been a leading figure in IFCO/Pastors for Peace caravans to Cuba and a critic of U.S. policy in Latin America.",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Trespass at the U.S. Army School of the Americas (Fort Benning, Georgia)',
                'arrest_date' => '2006-11-19',
                'release_date' => '2014-12-31',
                'convicted' => 'Multiple federal trespass convictions',
                'sentence' => 'Several months federal prison across multiple sentences',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Ellen Barfield', 'first_name' => 'Ellen', 'last_name' => 'Barfield',
                'birthdate' => '1955-01-01', 'death_date' => null,
                'gender' => 'Female', 'race' => 'White', 'state' => 'Maryland', 'era' => '2000s',
                'ideologies' => ['Anti-war', 'Pacifist'],
                'affiliation' => ['Veterans for Peace', 'War Resisters League', 'SOA Watch'],
                'in_custody' => false, 'released' => true,
                'description' => "Ellen Barfield is a U.S. Army veteran (she served as a military police officer at the Defense Language Institute in Monterey and at U.S. Army Garrison Yongsan in South Korea) who left the Army and became one of the most consistently arrested anti-war activists in the United States. A founding member of Veterans for Peace and a War Resisters League organizer, she has been arrested approximately 50 times for civil disobedience, particularly SOA Watch line-crossings at Fort Benning, where she has served multiple short federal sentences.",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Trespass at the U.S. Army School of the Americas and at federal buildings — multiple antiwar civil-disobedience convictions',
                'arrest_date' => '1998-11-22',
                'release_date' => '2018-12-31',
                'convicted' => 'Multiple federal convictions',
                'sentence' => 'Cumulative months in federal prison across many short sentences',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Gail Phares', 'first_name' => 'Gail', 'middle_name' => 'S.', 'last_name' => 'Phares',
                'birthdate' => null, 'death_date' => null,
                'gender' => 'Female', 'race' => 'White', 'state' => 'North Carolina', 'era' => '2000s',
                'ideologies' => ['Anti-war', 'Latin America solidarity'],
                'affiliation' => ['Witness for Peace', 'SOA Watch', 'Carolina Interfaith Task Force on Central America'],
                'in_custody' => false, 'released' => true,
                'description' => "Gail S. Phares is a former Maryknoll lay missioner who served in Nicaragua and Guatemala in the 1960s and is a co-founder of Witness for Peace, the U.S. solidarity organization that placed thousands of Americans into Nicaraguan war zones during the 1980s Contra war as nonviolent witnesses. She has been arrested repeatedly for SOA Watch line-crossings at Fort Benning and has served multiple short federal sentences. She founded the Carolina Interfaith Task Force on Central America in Raleigh, North Carolina.",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Trespass at the U.S. Army School of the Americas (Fort Benning, Georgia)',
                'arrest_date' => '2003-11-23',
                'release_date' => '2010-12-31',
                'convicted' => 'Multiple federal trespass convictions',
                'sentence' => 'Several months federal prison across multiple sentences',
            ]],
        ];

        // ─── Iraq War military resisters ───
        $defendants[] = [
            'data' => [
                'name' => 'Kevin Benderman', 'first_name' => 'Kevin', 'last_name' => 'Benderman',
                'aka' => 'Sgt. Kevin Benderman',
                'birthdate' => '1965-04-22', 'death_date' => null,
                'gender' => 'Male', 'race' => 'White', 'state' => 'Georgia', 'era' => '2000s',
                'ideologies' => ['Anti-war', 'Conscientious objector'],
                'affiliation' => null,
                'in_custody' => false, 'released' => true,
                'description' => "Sergeant Kevin Benderman was a U.S. Army mechanic with the 3rd Infantry Division who served in the 2003 invasion of Iraq and, after returning to Fort Stewart, applied for conscientious-objector status when his unit was ordered to redeploy in early 2005. The Army denied his application, and when his unit deployed without him in January 2005 he was court-martialed for missing movement and desertion. On July 28, 2005, a military jury at Fort Stewart convicted him of missing movement and acquitted him of desertion; he was sentenced to 15 months in military confinement, reduction to private, and dishonorable discharge. He served his sentence at the Naval Consolidated Brig at Charleston, South Carolina, and was released in March 2006. With his wife Monica he wrote the memoir Letters from Fort Lewis Brig: A Matter of Conscience.",
            ],
            'cases' => [[
                'institution_id' => $fortStewart->id,
                'charges' => 'Missing movement (Article 87, UCMJ); desertion (Article 85, UCMJ — acquitted)',
                'arrest_date' => '2005-01-08',
                'incarceration_date' => '2005-07-28',
                'release_date' => '2006-08-25',
                'convicted' => 'Yes — military court-martial, Fort Stewart, Georgia, July 28, 2005 (convicted of missing movement; acquitted of desertion)',
                'sentence' => '15 months in military confinement; reduction to private; dishonorable discharge',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Abdullah Webster', 'first_name' => 'Abdullah', 'last_name' => 'Webster',
                'aka' => 'Sgt. Abdullah Webster',
                'birthdate' => '1965-01-01', 'death_date' => null,
                'gender' => 'Male', 'race' => 'Black', 'state' => 'Germany', 'era' => '2000s',
                'ideologies' => ['Anti-war', 'Conscientious objector', 'Islam'],
                'affiliation' => null,
                'in_custody' => false, 'released' => true,
                'description' => "Sergeant Abdullah Webster, a Muslim convert from Houston, Texas, was an artillery noncommissioned officer with the 1st Infantry Division stationed in Schweinfurt, Germany. In 2004, when his unit was ordered to deploy to Iraq, he refused on the grounds that Islamic teaching forbade his participation in a war he understood as unjust against fellow Muslims; the Army refused his conscientious-objector application. He was court-martialed in Würzburg, Germany in June 2004, convicted of missing movement and disobeying orders, and sentenced to 14 months in military confinement, reduction to private, and a bad-conduct discharge. He served his sentence at the U.S. Army Confinement Facility in Mannheim, Germany.",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Missing movement and disobeying orders (UCMJ Articles 87 and 92)',
                'arrest_date' => '2004-04-15',
                'incarceration_date' => '2004-06-03',
                'release_date' => '2005-07-15',
                'convicted' => 'Yes — military court-martial, Würzburg, Germany, June 3, 2004',
                'sentence' => '14 months in military confinement at the U.S. Army Confinement Facility, Mannheim, Germany',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Robin Long', 'first_name' => 'Robin', 'last_name' => 'Long',
                'birthdate' => '1983-01-01', 'death_date' => null,
                'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'era' => '2000s',
                'ideologies' => ['Anti-war', 'Conscientious objector'],
                'affiliation' => null,
                'in_custody' => false, 'released' => true,
                'description' => "Specialist Robin Long was a U.S. Army soldier who refused deployment to Iraq in 2005 and went absent without leave. He fled to Canada and applied for refugee status, but was denied; in July 2008 he became the first U.S. Iraq War deserter to be deported from Canada to the United States. He was court-martialed at Fort Carson, Colorado, on August 22, 2008, convicted of desertion, and sentenced to 15 months in military confinement plus a dishonorable discharge. He served his sentence at the Naval Consolidated Brig at Miramar, California.",
            ],
            'cases' => [[
                'institution_id' => $pendleton->id,
                'charges' => 'Desertion (UCMJ Article 85)',
                'arrest_date' => '2008-07-15',
                'incarceration_date' => '2008-08-22',
                'release_date' => '2009-08-22',
                'convicted' => 'Yes — military court-martial, Fort Carson, Colorado, August 22, 2008',
                'sentence' => '15 months in military confinement at Naval Consolidated Brig Miramar; dishonorable discharge',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Cliff Cornell', 'first_name' => 'Cliff', 'last_name' => 'Cornell',
                'birthdate' => '1980-01-01', 'death_date' => null,
                'gender' => 'Male', 'race' => 'White', 'state' => 'South Carolina', 'era' => '2000s',
                'ideologies' => ['Anti-war', 'Conscientious objector'],
                'affiliation' => null,
                'in_custody' => false, 'released' => true,
                'description' => "Specialist Cliff Cornell was a U.S. Army soldier with the 3rd Infantry Division at Fort Stewart, Georgia, who refused deployment to Iraq in early 2005 and fled to Canada. After Canadian authorities denied his refugee claim he returned voluntarily to the United States in February 2009. He was court-martialed at Fort Stewart in April 2009, convicted of desertion, and sentenced to one year in military confinement plus a bad-conduct discharge. He served his sentence at the Naval Consolidated Brig at Charleston.",
            ],
            'cases' => [[
                'institution_id' => $fortStewart->id,
                'charges' => 'Desertion (UCMJ Article 85)',
                'arrest_date' => '2009-02-04',
                'incarceration_date' => '2009-04-29',
                'release_date' => '2010-04-29',
                'convicted' => 'Yes — military court-martial, Fort Stewart, Georgia, April 29, 2009',
                'sentence' => '12 months in military confinement; bad-conduct discharge',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'James Burmeister', 'first_name' => 'James', 'last_name' => 'Burmeister',
                'birthdate' => '1985-01-01', 'death_date' => null,
                'gender' => 'Male', 'race' => 'White', 'state' => 'Washington', 'era' => '2000s',
                'ideologies' => ['Anti-war', 'Conscientious objector'],
                'affiliation' => null,
                'in_custody' => false, 'released' => true,
                'description' => "Specialist James Burmeister was a U.S. Army soldier with the 1st Battalion, 23rd Infantry Regiment, who served in Iraq in 2006–07 and afterward refused to redeploy on the grounds that he had witnessed the use of 'bait' tactics by U.S. snipers — leaving items such as detonation cord in roadways to lure Iraqi civilians who could then be shot as supposed insurgents. He went absent without leave to Canada in 2007 and returned voluntarily in 2008. He was court-martialed at Fort Lewis, Washington in May 2008, convicted of desertion, and sentenced to nine months in military confinement plus a bad-conduct discharge.",
            ],
            'cases' => [[
                'institution_id' => $lewisBrig->id,
                'charges' => 'Desertion (UCMJ Article 85)',
                'arrest_date' => '2008-04-22',
                'incarceration_date' => '2008-05-19',
                'release_date' => '2009-02-19',
                'convicted' => 'Yes — military court-martial, Fort Lewis, Washington, May 19, 2008',
                'sentence' => '9 months in military confinement; bad-conduct discharge',
            ]],
        ];

        // ─── Round 2: extracted from nukeresister Inside & Out sections ───

        // Additional institutions for Round 2
        $coleman      = Institution::firstOrCreate(['name' => 'United States Penitentiary, Coleman'], ['city' => 'Coleman', 'state' => 'Florida']);
        $edgefield    = Institution::firstOrCreate(['name' => 'FCI Edgefield'], ['city' => 'Edgefield', 'state' => 'South Carolina']);
        $yazoo        = Institution::firstOrCreate(['name' => 'FCI Yazoo City Medium'], ['city' => 'Yazoo City', 'state' => 'Mississippi']);
        $guaynabo     = Institution::firstOrCreate(['name' => 'MDC Guaynabo'], ['city' => 'San Juan', 'state' => 'Puerto Rico']);
        $sheridan2    = Institution::firstOrCreate(['name' => 'FPC Sheridan'], ['city' => 'Sheridan', 'state' => 'Oregon']);
        $allenwoodLow = Institution::firstOrCreate(['name' => 'FCI Allenwood Low'], ['city' => 'White Deer', 'state' => 'Pennsylvania']);
        $victoriaJail = Institution::firstOrCreate(['name' => 'Victoria County Jail'], ['city' => 'Victoria', 'state' => 'Texas']);
        $mdcBrooklyn  = Institution::firstOrCreate(['name' => 'MDC Brooklyn'], ['city' => 'Brooklyn', 'state' => 'New York']);
        $lejeune      = Institution::firstOrCreate(['name' => 'Camp Lejeune brig'], ['city' => 'Camp Lejeune', 'state' => 'North Carolina']);
        $fortSill     = Institution::firstOrCreate(['name' => 'Fort Sill brig'], ['city' => 'Fort Sill', 'state' => 'Oklahoma']);
        $hillsboroNH  = Institution::firstOrCreate(['name' => 'Hillsborough County House of Corrections'], ['city' => 'Manchester', 'state' => 'New Hampshire']);
        $lickingOH    = Institution::firstOrCreate(['name' => 'Licking County Justice Center'], ['city' => 'Newark', 'state' => 'Ohio']);
        $duluth       = Institution::firstOrCreate(['name' => 'FPC Duluth'], ['city' => 'Duluth', 'state' => 'Minnesota']);
        $pekin2       = Institution::firstOrCreate(['name' => 'FCI Pekin Satellite Camp'], ['city' => 'Pekin', 'state' => 'Illinois']);
        $memphisCamp  = Institution::firstOrCreate(['name' => 'FCI Memphis Satellite Camp'], ['city' => 'Millington', 'state' => 'Tennessee']);
        $dublinCA     = Institution::firstOrCreate(['name' => 'FCI Dublin Satellite Camp'], ['city' => 'Dublin', 'state' => 'California']);
        $lexCamp      = Institution::firstOrCreate(['name' => 'FMC Lexington Satellite Camp'], ['city' => 'Lexington', 'state' => 'Kentucky']);
        $clearwater   = Institution::firstOrCreate(['name' => 'Pinellas County Jail'], ['city' => 'Clearwater', 'state' => 'Florida']);
        $fortCarson   = Institution::firstOrCreate(['name' => 'Fort Carson brig'], ['city' => 'Fort Carson', 'state' => 'Colorado']);

        // Vieques resisters (1999-2003 Puerto Rico bombing protests)
        $viequesContext = "From 1941 to 2003, the U.S. Navy used the inhabited Puerto Rican island of Vieques as a live-fire bombing range. After Navy bombs killed civilian security guard David Sanes in 1999, a sustained civil-disobedience movement of Puerto Rican residents and U.S. solidarity activists repeatedly entered Navy land to halt training. Hundreds were arrested and dozens served federal prison sentences for trespass, conspiracy, and damage to federal property. The Navy finally withdrew from Vieques in May 2003 — the largest victory in U.S. anti-base activism in a generation.";

        $defendants[] = [
            'data' => [
                'name' => 'José Vélez Acosta', 'first_name' => 'José', 'last_name' => 'Vélez Acosta',
                'inmate_number' => '23883-069',
                'gender' => 'Male', 'race' => 'Hispanic', 'state' => 'Puerto Rico', 'era' => '2000s',
                'ideologies' => ['Anti-colonial', 'Anti-war', 'Puerto Rican independence'],
                'affiliation' => null, 'in_custody' => false, 'released' => true,
                'description' => "José Vélez Acosta was one of dozens of Puerto Rican residents and solidarity activists who served substantial federal prison time for civil disobedience halting U.S. Navy bombing exercises on the inhabited island of Vieques. He was sentenced to 33 months in federal prison and held at the United States Penitentiary at Coleman, Florida.\n\n{$viequesContext}",
            ],
            'cases' => [[
                'institution_id' => $coleman->id,
                'charges' => 'Conspiracy, damage to federal property, and/or probation violation — resisting U.S. military bombardment of Vieques, Puerto Rico',
                'arrest_date' => '2003-05-01', 'release_date' => '2006-01-27',
                'sentence' => '33 months federal prison',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'José Pérez González', 'first_name' => 'José', 'last_name' => 'Pérez González',
                'inmate_number' => '21519-069',
                'gender' => 'Male', 'race' => 'Hispanic', 'state' => 'Puerto Rico', 'era' => '2000s',
                'ideologies' => ['Anti-colonial', 'Anti-war', 'Puerto Rican independence'],
                'affiliation' => null, 'in_custody' => false, 'released' => true,
                'description' => "José Pérez González received the longest federal sentence among the Vieques resisters: five years in federal prison for conspiracy, damage to federal property, and probation violation in connection with civil disobedience halting U.S. Navy bombing of the inhabited Puerto Rican island. He served at FCI Edgefield in South Carolina and later at FCI Yazoo City Medium in Mississippi.\n\n{$viequesContext}",
            ],
            'cases' => [[
                'institution_id' => $edgefield->id,
                'charges' => 'Conspiracy, damage to federal property, and probation violation — resisting U.S. military bombardment of Vieques, Puerto Rico',
                'arrest_date' => '2003-05-01', 'release_date' => '2008-01-17',
                'sentence' => '5 years federal prison',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Jorge Cruz Hernandez', 'first_name' => 'Jorge', 'last_name' => 'Cruz Hernandez',
                'inmate_number' => '26318-069',
                'gender' => 'Male', 'race' => 'Hispanic', 'state' => 'Puerto Rico', 'era' => '2000s',
                'ideologies' => ['Anti-colonial', 'Anti-war', 'Puerto Rican independence'],
                'affiliation' => null, 'in_custody' => false, 'released' => true,
                'description' => "Jorge Cruz Hernandez served 18 months in federal prison at FCI Edgefield, South Carolina for civil disobedience halting U.S. Navy bombing on Vieques.\n\n{$viequesContext}",
            ],
            'cases' => [[
                'institution_id' => $edgefield->id,
                'charges' => 'Conspiracy, damage to federal property — resisting U.S. military bombardment of Vieques, Puerto Rico',
                'arrest_date' => '2003-05-01', 'release_date' => '2005-06-06',
                'sentence' => '18 months federal prison',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'José Montañez Sanes', 'first_name' => 'José', 'last_name' => 'Montañez Sanes',
                'inmate_number' => '26317-069',
                'gender' => 'Male', 'race' => 'Hispanic', 'state' => 'Puerto Rico', 'era' => '2000s',
                'ideologies' => ['Anti-colonial', 'Anti-war', 'Puerto Rican independence'],
                'affiliation' => null, 'in_custody' => false, 'released' => true,
                'description' => "José Montañez Sanes served 18 months in federal custody at MDC Guaynabo in Puerto Rico for civil disobedience halting U.S. Navy bombing on Vieques.\n\n{$viequesContext}",
            ],
            'cases' => [[
                'institution_id' => $guaynabo->id,
                'charges' => 'Conspiracy, damage to federal property — resisting U.S. military bombardment of Vieques, Puerto Rico',
                'arrest_date' => '2003-05-01', 'release_date' => '2005-05-29',
                'sentence' => '18 months federal custody',
            ]],
        ];

        // Anti-war / anti-empire actions
        $defendants[] = [
            'data' => [
                'name' => 'Michael D. Poulin', 'first_name' => 'Michael', 'middle_name' => 'D.', 'last_name' => 'Poulin',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Washington', 'era' => '2000s',
                'ideologies' => ['Anti-war', 'Anti-imperialist', 'Anarchist'],
                'affiliation' => null, 'in_custody' => false, 'released' => true,
                'description' => "Michael D. Poulin was convicted in 2003 of damaging electricity transmission towers in Washington state in a symbolic action that he and supporters described as exposing the fragility of U.S. empire. He served 27 months in federal prison at FPC Sheridan, Oregon.",
            ],
            'cases' => [[
                'institution_id' => $sheridan2->id,
                'charges' => 'Damaging energy transmission infrastructure — symbolic anti-imperialist sabotage',
                'arrest_date' => '2003-11-01', 'release_date' => '2006-01-25',
                'sentence' => '27 months federal prison',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Brendan Walsh', 'first_name' => 'Brendan', 'last_name' => 'Walsh',
                'inmate_number' => '12473-052',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Pennsylvania', 'era' => '2000s',
                'ideologies' => ['Anti-war', 'Pacifist'],
                'affiliation' => null, 'in_custody' => false, 'released' => true,
                'description' => "Brendan Walsh was sentenced to five years in federal prison for an arson at a U.S. military recruiting station in April 2002, in protest of the post-9/11 military buildup. He served at FCI Allenwood Low and later at FCI Elkton.",
            ],
            'cases' => [[
                'institution_id' => $allenwoodLow->id,
                'charges' => 'Arson at military recruiting station',
                'arrest_date' => '2002-04-15', 'release_date' => '2008-07-15',
                'sentence' => '5 years federal prison',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Sylvia Diane Wilson', 'first_name' => 'Sylvia Diane', 'last_name' => 'Wilson',
                'gender' => 'Female', 'race' => 'White', 'state' => 'Texas', 'era' => '2000s',
                'ideologies' => ['Anti-war', 'Environmental'],
                'affiliation' => null, 'in_custody' => false, 'released' => true,
                'description' => "Sylvia Diane Wilson was arrested in December 2005 for heckling Vice President Dick Cheney at a public event. She was held at the Victoria County Jail in Texas to serve 150 days on an outstanding sentence for an earlier environmental banner-hanging action.",
            ],
            'cases' => [[
                'institution_id' => $victoriaJail->id,
                'charges' => 'Anti-war heckling of VP Cheney; held to serve sentence for environmental banner-hanging',
                'arrest_date' => '2005-12-05', 'release_date' => '2006-03-18',
                'sentence' => '150 days',
            ]],
        ];

        // St. Patrick's Four (additional defendant beyond Peter DeMott / Teresa Grady / Clare Grady)
        $defendants[] = [
            'data' => [
                'name' => 'Daniel Burns', 'first_name' => 'Daniel', 'last_name' => 'Burns',
                'inmate_number' => '13182-052',
                'gender' => 'Male', 'race' => 'White', 'state' => 'New York', 'era' => '2000s',
                'ideologies' => ['Anti-war', 'Pacifist', 'Catholic Worker'],
                'affiliation' => ["St. Patrick's Four", 'Ithaca Catholic Worker'],
                'in_custody' => false, 'released' => true,
                'description' => "Daniel Burns was one of the St. Patrick's Four — Catholic Worker activists Peter DeMott, Teresa Grady, Clare Grady, and Daniel Burns — who on March 17, 2003, on the eve of the U.S. invasion of Iraq, entered the Ithaca, New York Army Recruiting Station and poured their own blood inside the office in symbolic protest. Burns served six months at MDC Brooklyn for the action.",
            ],
            'cases' => [[
                'institution_id' => $mdcBrooklyn->id,
                'charges' => "Conspiracy and damage to federal property — St. Patrick's Four blood-pouring at Ithaca Army Recruiting Station",
                'arrest_date' => '2003-03-17', 'release_date' => '2006-07-17',
                'sentence' => '6 months federal',
            ]],
        ];

        // Iraq War military refusers (additional)
        $defendants[] = [
            'data' => [
                'name' => 'Joel Klimkewicz', 'first_name' => 'Joel', 'last_name' => 'Klimkewicz',
                'aka' => 'Pvt. Joel Klimkewicz',
                'gender' => 'Male', 'race' => 'White', 'state' => 'North Carolina', 'era' => '2000s',
                'ideologies' => ['Anti-war', 'Conscientious objector'],
                'affiliation' => null, 'in_custody' => false, 'released' => true,
                'description' => "Private Joel Klimkewicz was a U.S. Marine who refused to train for combat after his conscientious-objector application was denied. He was court-martialed at Camp Lejeune in late 2004 and sentenced to seven months in military confinement.",
            ],
            'cases' => [[
                'institution_id' => $lejeune->id,
                'charges' => 'Refusing combat training (UCMJ); conscientious-objector petition denied',
                'arrest_date' => '2004-12-01',
                'sentence' => '7 months military confinement',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Dale Bartell', 'first_name' => 'Dale', 'last_name' => 'Bartell',
                'aka' => 'Spc. Dale Bartell',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Oklahoma', 'era' => '2000s',
                'ideologies' => ['Anti-war', 'Conscientious objector'],
                'affiliation' => null, 'in_custody' => false, 'released' => true,
                'description' => "Specialist Dale Bartell was a U.S. Army soldier who refused to deploy to Iraq in May 2005 to preserve his pending conscientious-objector claim. He pled guilty to facts of desertion at Fort Sill and served four months in military confinement.",
            ],
            'cases' => [[
                'institution_id' => $fortSill->id,
                'charges' => 'Desertion (UCMJ Article 85)',
                'arrest_date' => '2005-05-15', 'release_date' => '2005-11-12',
                'sentence' => '4 months military confinement',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Neil Quentin Lucas', 'first_name' => 'Neil Quentin', 'last_name' => 'Lucas',
                'aka' => 'Pvt. Neil Quentin Lucas',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Oklahoma', 'era' => '2000s',
                'ideologies' => ['Anti-war', 'Conscientious objector'],
                'affiliation' => null, 'in_custody' => false, 'released' => true,
                'description' => "Private Neil Quentin Lucas was a U.S. Army soldier who refused to deploy after his conscientious-objector claim was ignored. He was court-martialed on June 22, 2005 and sentenced to 13 months in military confinement at Fort Sill.",
            ],
            'cases' => [[
                'institution_id' => $fortSill->id,
                'charges' => 'Desertion / refusing deployment after CO claim ignored',
                'arrest_date' => '2005-06-22', 'release_date' => '2006-08-22',
                'sentence' => '13 months military confinement',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Anthony Michael Anderson', 'first_name' => 'Anthony Michael', 'last_name' => 'Anderson',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Oklahoma', 'era' => '2000s',
                'ideologies' => ['Anti-war', 'Conscientious objector'],
                'affiliation' => null, 'in_custody' => false, 'released' => true,
                'description' => "Anthony Michael Anderson was a U.S. Army soldier who pled guilty to desertion and disobeying an order in November 2008 and was sentenced to 14 months in military confinement at Fort Sill.",
            ],
            'cases' => [[
                'institution_id' => $fortSill->id,
                'charges' => 'Desertion and disobeying an order (UCMJ Articles 85 and 92)',
                'arrest_date' => '2008-11-01', 'release_date' => '2009-11-30',
                'sentence' => '14 months military confinement',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Jerry Texiero', 'first_name' => 'Jerry', 'last_name' => 'Texiero',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Florida', 'era' => '2000s',
                'ideologies' => ['Anti-war', 'Conscientious objector'],
                'affiliation' => null, 'in_custody' => false, 'released' => true,
                'description' => "Jerry Texiero was a Vietnam-era Marine Corps deserter who lived underground for decades on grounds of conscience. He was arrested on August 16, 2005 and held at Pinellas County Jail in Clearwater, Florida pending court-martial.",
            ],
            'cases' => [[
                'institution_id' => $clearwater->id,
                'charges' => 'Desertion (Vietnam era)',
                'arrest_date' => '2005-08-16',
                'sentence' => 'Held pending court-martial',
            ]],
        ];

        // Merrimack Four — Boston Elbit Systems action November 2023
        $merrimackContext = "On November 30, 2023, four young women — Calla Walsh, Sophie Ross, Bridget Shergalis, and Paige Belanger — entered the Elbit Systems of America facility in Merrimack, New Hampshire and damaged equipment as a protest against Elbit's role as Israel's largest weapons manufacturer during the war in Gaza. Elbit produces military drones and components used in Israeli operations. The four pleaded guilty to misdemeanor criminal mischief in late 2024 and were each sentenced to 60 days in the Hillsborough County House of Corrections, beginning November 14, 2024.";

        foreach ([
            ['Calla Walsh', 'Calla', 'Walsh'],
            ['Sophie Ross', 'Sophie', 'Ross'],
            ['Bridget Shergalis', 'Bridget', 'Shergalis'],
            ['Paige Belanger', 'Paige', 'Belanger'],
        ] as [$name, $first, $last]) {
            $defendants[] = [
                'data' => [
                    'name' => $name, 'first_name' => $first, 'last_name' => $last,
                    'gender' => 'Female', 'race' => 'White', 'state' => 'New Hampshire', 'era' => '2020s',
                    'ideologies' => ['Anti-war', 'Palestine solidarity', 'Anti-imperialist'],
                    'affiliation' => ['Merrimack Four', 'Palestine Action US'],
                    'in_custody' => false, 'released' => true,
                    'description' => "{$name} is one of the Merrimack Four. {$merrimackContext}",
                ],
                'cases' => [[
                    'institution_id' => $hillsboroNH->id,
                    'charges' => 'Misdemeanor criminal mischief — property damage at Elbit Systems of America facility in Merrimack, New Hampshire',
                    'arrest_date' => '2023-11-30',
                    'incarceration_date' => '2024-11-14', 'release_date' => '2025-01-13',
                    'sentence' => '60 days in Hillsborough County House of Corrections',
                ]],
            ];
        }

        // Boeing plant blockade
        $defendants[] = [
            'data' => [
                'name' => 'Nancy Epling', 'first_name' => 'Nancy', 'last_name' => 'Epling',
                'inmate_number' => '00003367',
                'gender' => 'Female', 'race' => 'White', 'state' => 'Ohio', 'era' => '2020s',
                'ideologies' => ['Anti-war', 'Palestine solidarity'],
                'affiliation' => null, 'in_custody' => false, 'released' => true,
                'description' => "Nancy Epling was sentenced to 30 days in the Licking County Justice Center for participating in a blockade of the Boeing plant in Heath, Ohio in protest of U.S. weapons production for Israel's war in Gaza.",
            ],
            'cases' => [[
                'institution_id' => $lickingOH->id,
                'charges' => 'Trespass / blockade of Boeing plant in Heath, Ohio',
                'arrest_date' => '2024-10-28', 'release_date' => '2024-11-27',
                'sentence' => '30 days',
            ]],
        ];

        // Anti-nuclear (Offutt AFB / STRATCOM)
        $defendants[] = [
            'data' => [
                'name' => 'Mark Kenney', 'first_name' => 'Mark', 'last_name' => 'Kenney',
                'inmate_number' => '14018-047',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Minnesota', 'era' => '2010s',
                'ideologies' => ['Anti-nuclear', 'Pacifist'],
                'affiliation' => null, 'in_custody' => false, 'released' => true,
                'description' => "Mark Kenney was sentenced to six months in federal prison at FPC Duluth for trespass at Offutt Air Force Base in Nebraska, the home of U.S. Strategic Command, on August 9, 2010.",
            ],
            'cases' => [[
                'institution_id' => $duluth->id,
                'charges' => 'Trespass at Offutt Air Force Base (home of U.S. Strategic Command)',
                'arrest_date' => '2010-08-09', 'incarceration_date' => '2011-04-27', 'release_date' => '2011-10-25',
                'sentence' => '6 months federal prison',
            ]],
        ];

        // SOA Watch line-crossers — 2006 sentencing batch
        $soaContext = "Each year on the November anniversary of the assassination of six Jesuits, their housekeeper, and her daughter at the University of Central America in San Salvador in 1989, peace activists gather at the gates of Fort Benning, Georgia (now Fort Moore), site of the U.S. Army School of the Americas / Western Hemisphere Institute for Security Cooperation (WHINSEC), to commemorate the dead and to call for the school's closure. Hundreds have crossed the line onto the base over four decades of vigils; many have served federal sentences ranging from 30 days to a year for trespass.";

        foreach ([
            ['Anika D. Cunningham', 'Anika', 'D.', 'Cunningham', 'Female', '92567-020', '30 days', '2006-04-11', '2006-05-10', null],
            ['Elizabeth Ann Lentsch', 'Elizabeth', 'Ann', 'Lentsch', 'Female', '30147-074', '6 months', '2006-04-11', '2006-10-10', $lexCamp->id],
            ['Joanne Cowan', 'Joanne', null, 'Cowan', 'Female', '92566-020', '60 days', '2006-04-11', '2006-06-09', null],
            ['Sarah C. Harper', 'Sarah', 'C.', 'Harper', 'Female', '92571-020', '90 days', '2006-04-11', '2006-07-09', null],
            ['Cheryl Sommers', 'Cheryl', null, 'Sommers', 'Female', '91437-020', '90 days', '2006-04-11', '2006-07-09', $dublinCA->id],
            ['Judith Ruland', 'Judith', null, 'Ruland', 'Female', '91434-020', '60 days', '2006-04-11', '2006-06-09', null],
            ['Robin Lloyd', 'Robin', null, 'Lloyd', 'Female', '92572-020', '90 days', '2006-04-11', '2006-07-09', $danbury->id],
            ['Rita Hohenshell', 'Rita', null, 'Hohenshell', 'Female', '90280-020', '60 days', '2006-04-11', '2006-06-09', $pekin2->id],
            ['Jane Hosking', 'Jane', null, 'Hosking', 'Female', '05331-090', '6 months', '2006-04-11', '2006-10-10', $pekin2->id],
            ['Dorothy Parker', 'Dorothy', null, 'Parker', 'Female', '91432-020', '60 days', '2006-04-11', '2006-06-09', $pekin2->id],
            ['Christine Gaunt', 'Christine', null, 'Gaunt', 'Female', '91356-020', '6 months', '2005-11-19', '2006-05-19', $pekin2->id],
            ['Donald W. Nelson', 'Donald', 'W.', 'Nelson', 'Male', '92559-020', '3 months', '2005-11-19', '2006-02-19', $memphisCamp->id],
            ['Christopher Spicer', 'Christopher', null, 'Spicer', 'Male', '94642-020', '6 months', '2010-11-21', '2011-05-21', $pekin2->id],
            ['Michael David Omondi', 'Michael David', null, 'Omondi', 'Male', '94638-020', '6 months', '2010-11-21', '2011-05-21', null],
        ] as $row) {
            [$name, $first, $middle, $last, $gender, $bopId, $sentence, $arrest, $release, $instId] = $row;
            $defendants[] = [
                'data' => array_filter([
                    'name' => $name, 'first_name' => $first, 'middle_name' => $middle, 'last_name' => $last,
                    'gender' => $gender, 'race' => 'White', 'state' => 'Georgia', 'era' => '2000s',
                    'ideologies' => ['Anti-war', 'Latin America solidarity'],
                    'affiliation' => ['SOA Watch'], 'in_custody' => false, 'released' => true,
                    'inmate_number' => $bopId,
                    'description' => "{$name} crossed the line at Fort Benning, Georgia during the annual SOA Watch vigil to close the U.S. Army School of the Americas / Western Hemisphere Institute for Security Cooperation. Convicted of trespass on a military installation and sentenced to {$sentence} in federal custody.\n\n{$soaContext}",
                ], fn ($v) => $v !== null),
                'cases' => [[
                    'institution_id' => $instId,
                    'charges' => 'Trespass at the U.S. Army School of the Americas / WHINSEC, Fort Benning, Georgia',
                    'arrest_date' => $arrest, 'release_date' => $release,
                    'sentence' => $sentence,
                ]],
            ];
        }

        // ─── Round 3: extracted from background-agent crawl of nr115-207 + static archive ───
        $jejuPrison  = Institution::firstOrCreate(['name' => 'Jeju Prison'], ['city' => 'Jeju City', 'state' => 'South Korea']);
        $italyPrison = Institution::firstOrCreate(['name' => 'Sicilian Prison']);
        $germanyPrison = Institution::firstOrCreate(['name' => 'German Federal Prison']);
        $ukPrison    = Institution::firstOrCreate(['name' => 'UK HM Prison']);
        $sciDallas3  = Institution::firstOrCreate(['name' => 'SCI Dallas'], ['city' => 'Dallas', 'state' => 'Pennsylvania']);
        $whitemanArea = Institution::firstOrCreate(['name' => 'Western District of Missouri (Plowshares prosecution)']);
        $kazakhPrison = Institution::firstOrCreate(['name' => 'Kazakhstan Prison'], ['state' => 'Kazakhstan']);
        $ftMooreFCI   = Institution::firstOrCreate(['name' => 'Fort Benning federal court (SOA Watch prosecutions)']);
        $whitehallNYC = Institution::firstOrCreate(['name' => 'United States District Court, Southern District of New York']);
        $nairobiPrison = Institution::firstOrCreate(['name' => 'Federal Court (anti-drone prosecution)']);

        // International Plowshares & whistleblowers
                                        // Italian Plowshares
        $defendants[] = [
            'data' => [
                'name' => 'Salvatore Vaccaro', 'first_name' => 'Salvatore', 'last_name' => 'Vaccaro',
                'gender' => 'Male', 'state' => 'Italy', 'era' => '2010s',
                'ideologies' => ['Anti-nuclear', 'Pacifist', 'Plowshares'],
                'affiliation' => ['Italian Plowshares'], 'in_custody' => false, 'released' => true,
                'description' => "Salvatore Vaccaro is a Sicilian Plowshares activist who damaged equipment at the U.S. military's MUOS (Mobile User Objective System) satellite-communications ground station in Niscemi, Sicily, in December 2014, and was sentenced in Italy to 11 months and 27 days. He began serving his sentence on August 5, 2018.",
            ],
            'cases' => [[
                'institution_id' => $italyPrison->id,
                'charges' => 'Criminal damage to U.S. military satellite communications equipment in Sicily',
                'arrest_date' => '2014-12-15', 'incarceration_date' => '2018-08-05', 'release_date' => '2019-08-01',
                'sentence' => '11 months, 27 days',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Turi Vaccaro', 'first_name' => 'Turi', 'last_name' => 'Vaccaro',
                'gender' => 'Male', 'state' => 'Italy', 'era' => '2010s',
                'ideologies' => ['Anti-nuclear', 'Pacifist', 'Plowshares'],
                'affiliation' => ['Italian Plowshares'], 'in_custody' => false, 'released' => true,
                'description' => "Turi Vaccaro is one of the most prolific living European Plowshares activists. In 2005 he carried out a Plowshares disarmament action against F-16 nuclear bombers at Woensdrecht Air Force Base in the Netherlands. He has subsequently served multiple short prison sentences across Europe — in the Netherlands, Belgium, and Italy — for symbolic disarmament actions, including the 2018 Sicilian MUOS prosecution. He was released from a Sicilian prison in April 2020.",
            ],
            'cases' => [[
                'institution_id' => $italyPrison->id,
                'charges' => 'Plowshares disarmament — multiple actions across Europe (Woensdrecht AFB Netherlands 2005, MUOS Sicily 2014)',
                'arrest_date' => '2005-08-10', 'release_date' => '2020-04-15',
                'sentence' => 'Multiple short sentences cumulatively totaling several years across Dutch, Belgian, and Italian custody',
            ]],
        ];
        // German Büchel anti-nuclear
        $defendants[] = [
            'data' => [
                'name' => 'Gerd Büntzly', 'first_name' => 'Gerd', 'last_name' => 'Büntzly',
                'gender' => 'Male', 'state' => 'Germany', 'era' => '2020s',
                'ideologies' => ['Anti-nuclear', 'Pacifist'],
                'affiliation' => ['German anti-nuclear', 'GAAA'],
                'in_custody' => false, 'released' => true,
                'description' => "Gerd Büntzly is a German anti-nuclear activist with the Gewaltfreie Aktion Atomwaffen Abschaffen (GAAA). He has been arrested repeatedly for nonviolent civil disobedience at Büchel Air Base in western Germany, the U.S. Air Force facility that stores approximately 20 B61 nuclear bombs as part of NATO nuclear sharing. He was sentenced to 45 days in German federal prison for an April 30, 2019 trespass action and served his sentence in 2024.",
            ],
            'cases' => [[
                'institution_id' => $germanyPrison->id,
                'charges' => 'Trespass at Büchel Air Base, Germany (Hausfriedensbruch)',
                'arrest_date' => '2019-04-30', 'release_date' => '2024-11-27',
                'sentence' => '45 days German federal prison',
            ]],
        ];

                // UK
        $defendants[] = [
            'data' => [
                'name' => 'Lindis Percy', 'first_name' => 'Lindis', 'last_name' => 'Percy',
                'gender' => 'Female', 'state' => 'United Kingdom', 'era' => '2000s',
                'ideologies' => ['Anti-nuclear', 'Pacifist'],
                'affiliation' => ['Campaign for the Accountability of American Bases (CAAB)'],
                'in_custody' => false, 'released' => true,
                'description' => "Lindis Percy is a UK anti-nuclear and anti-base activist and co-founder of the Campaign for the Accountability of American Bases (CAAB). She has been arrested repeatedly for trespass at the U.S. military space-warfare bases at Menwith Hill (the largest U.S. signals-intelligence facility outside the United States) and Fylingdales in Yorkshire. She served 45 days in HM prison beginning April 21, 2009 for refusing to pay accumulated fines from those trespasses.",
            ],
            'cases' => [[
                'institution_id' => $ukPrison->id,
                'charges' => 'Trespass at Menwith Hill and Fylingdales space-warfare bases (UK)',
                'arrest_date' => '2008-01-15', 'incarceration_date' => '2009-04-21', 'release_date' => '2009-06-05',
                'sentence' => '45 days UK prison',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Sylvia Boyes', 'first_name' => 'Sylvia', 'last_name' => 'Boyes',
                'gender' => 'Female', 'state' => 'United Kingdom', 'era' => '2000s',
                'ideologies' => ['Anti-nuclear', 'Pacifist'],
                'affiliation' => ['Trident Ploughshares'],
                'in_custody' => false, 'released' => true,
                'description' => "Sylvia Boyes is a UK Trident Ploughshares activist who attempted to disarm U.S. military radar in England on August 6, 2008 (the Hiroshima anniversary). She was convicted of criminal damage and sentenced to 3 months in HM prison beginning December 18, 2008.",
            ],
            'cases' => [[
                'institution_id' => $ukPrison->id,
                'charges' => 'Criminal damage — attempted disarmament of U.S. military radar in England',
                'arrest_date' => '2008-08-06', 'incarceration_date' => '2008-12-18', 'release_date' => '2009-03-18',
                'sentence' => '3 months UK prison',
            ]],
        ];

        // Korean Jeju Naval Base resisters
        $jejuContext = "Since 2007, the South Korean government has been constructing a major naval base on Gangjeong village, Jeju Island — a UNESCO biosphere reserve and South Korea's southernmost island. The base, designed to host U.S. and South Korean Aegis destroyers and a port-of-call for U.S. nuclear-powered aircraft carriers, is widely understood as a forward-deployment site for the U.S. military's posture against China. Local Gangjeong residents and Korean peace activists have maintained continuous nonviolent resistance at the construction gates since 2010; hundreds have been arrested and dozens have served Korean prison sentences for trespass, obstruction, and civil disobedience.";

        foreach ([
            ['Yang Yoon-Mo', 'Yang', 'Yoon-Mo', 'Male', '18 months', '2014-02-01', null, 'Obstruction of business of military port contractors, Jeju'],
            ['Song Kang-Ho', 'Song', 'Kang-Ho', 'Male', '40 days', '2013-08-21', '2013-09-30', 'Trespass and obstruction of business during naval base construction on Jeju'],
            ['Park Yong-Sung', 'Park', 'Yong-Sung', 'Male', '40 days', '2013-08-21', '2013-09-30', 'Trespass and obstruction of business during naval base construction on Jeju'],
            ['Park Do-Hyun', 'Park', 'Do-Hyun', 'Male', '40 days', '2013-08-21', '2013-09-30', 'Trespass and obstruction of business during naval base construction on Jeju'],
            ['Mr. Kim Bok-Chul', 'Kim', 'Bok-Chul', 'Male', '4 months', '2012-04-01', '2012-08-01', 'Interfering with businesses building navy port, Jeju Island'],
            ['Mr. Kim Dong-Won', 'Kim', 'Dong-Won', 'Male', '4 months', '2012-04-01', '2012-08-01', 'Interfering with businesses building navy port, Jeju Island'],
            ['Mr. Kim Guk-Nam', 'Kim', 'Guk-Nam', 'Male', '50 days', '2016-01-27', '2016-03-17', 'Refusal to pay fines for resistance to naval base construction'],
            ['Mr. Park Suk-Jin', 'Park', 'Suk-Jin', 'Male', '90 days', '2014-09-06', '2014-12-05', 'Protest on base-related caisson dock in Hwasoon port'],
            ['Rev. Jeong Yeon-Gil', 'Jeong', 'Yeon-Gil', 'Male', '90 days', '2014-09-06', '2014-12-05', 'Protest on base-related caisson dock in Hwasoon port'],
            ['Lee Jong-Hwa', 'Lee', 'Jong-Hwa', 'Male', '90 days', '2013-05-07', '2013-08-05', 'Violating bail conditions after occupying naval base construction equipment in Hwasoon Port'],
            ['Kim Eun-Hye', 'Kim', 'Eun-Hye', 'Female', '8 months', '2013-10-15', '2014-06-15', 'Multiple charges related to protests opposing the naval base'],
            ['Kim Young-Jae', 'Kim', 'Young-Jae', 'Male', '60 days', '2014-04-28', '2014-06-27', 'Blocking a truck on the road leading into the naval base construction site'],
            ['Oh Cheol-Geun', 'Oh', 'Cheol-Geun', 'Male', '38 days', '2014-05-26', '2014-07-03', 'Refusal to pay fine for protest against Jeju naval base'],
            ['Fr. Lee Young-Chan', 'Lee', 'Young-Chan', 'Male', 'unknown', '2023-10-01', null, 'Naval base resistance, Jeju'],
        ] as [$name, $first, $last, $gender, $sentence, $arrest, $release, $action]) {
            $defendants[] = [
                'data' => array_filter([
                    'name' => $name, 'first_name' => $first, 'last_name' => $last,
                    'gender' => $gender, 'race' => 'Asian', 'state' => 'South Korea', 'era' => '2010s',
                    'ideologies' => ['Anti-war', 'Anti-imperialist', 'Indigenous-territory defense'],
                    'affiliation' => ['Jeju Naval Base resistance', 'Gangjeong village'],
                    'in_custody' => false, 'released' => true,
                    'description' => "{$name} is a Korean Jeju Naval Base resister. {$action}.\n\n{$jejuContext}",
                ], fn ($v) => $v !== null),
                'cases' => [[
                    'institution_id' => $jejuPrison->id,
                    'charges' => $action,
                    'arrest_date' => $arrest, 'release_date' => $release,
                    'sentence' => $sentence,
                ]],
            ];
        }
        // Major Plowshares — Frank Cordaro (Minuteman III Plowshares)
        $defendants[] = [
            'data' => [
                'name' => 'Frank Cordaro', 'first_name' => 'Frank', 'last_name' => 'Cordaro',
                'aka' => 'Father Frank Cordaro',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Iowa', 'era' => '1990s',
                'ideologies' => ['Anti-nuclear', 'Pacifist', 'Catholic Worker'],
                'affiliation' => ['Des Moines Catholic Worker', 'Plowshares movement'],
                'in_custody' => false, 'released' => true,
                'description' => "Father Frank Cordaro is a former Catholic priest, longtime Des Moines Catholic Worker, and Plowshares activist who has been arrested more than 100 times across his life. On August 6, 1998 — the Hiroshima anniversary — he and three others carried out the Minuteman III Plowshares disarmament action at a North Dakota intercontinental ballistic missile silo. He was convicted and served multiple federal sentences for that and many subsequent actions including SOA Watch line-crossings and STRATCOM/Offutt AFB protests.",
            ],
            'cases' => [[
                'institution_id' => $whitemanArea->id,
                'charges' => 'Sabotage and depredation of government property — Minuteman III Plowshares action; plus dozens of subsequent civil-disobedience convictions',
                'arrest_date' => '1998-08-06', 'release_date' => '2018-12-31',
                'sentence' => 'Cumulative years across multiple Plowshares and SOA Watch sentences',
            ]],
        ];

        // Helen John — UK / SOA Watch / Pentagon
        $defendants[] = [
            'data' => [
                'name' => 'Helen John', 'first_name' => 'Helen', 'last_name' => 'John',
                'gender' => 'Female', 'state' => 'United Kingdom', 'era' => '1990s',
                'ideologies' => ['Anti-nuclear', 'Pacifist'],
                'affiliation' => ['Greenham Common Women', 'SOA Watch'],
                'in_custody' => false, 'released' => true,
                'description' => "Helen John was a founding member of the Greenham Common Women's Peace Camp in 1981 and one of the most prolific UK anti-nuclear activists of her generation. In 1998 she was arrested at SOA Watch protests at the Pentagon and at Fort Benning and sentenced in U.S. federal court to two years — among the longest sentences imposed on a foreign national for SOA-related civil disobedience. She died in 2017.",
            ],
            'cases' => [[
                'institution_id' => $ftMooreFCI->id,
                'charges' => 'Trespass at the Pentagon and at the U.S. Army School of the Americas, Fort Benning',
                'arrest_date' => '1998-06-19', 'release_date' => '2000-06-19',
                'sentence' => '2 years federal prison',
            ]],
        ];

        // Iraq War-era military refusers (additional)
        foreach ([
            ['David Travis Bishop', 'David', 'Travis Bishop', 'Male', '1 year', '2009-08-14', '2010-08-14', "Convicted of missing movement, disobeying a lawful order, and going AWOL"],
            ['Robert Weiss', 'Robert', 'Weiss', 'Male', '7 months', '2008-05-15', '2008-12-15', "Pled guilty to desertion and missing movement after CO application denied"],
            ['Sara Beining', 'Sara', 'Beining', 'Female', 'awaiting court martial', '2014-08-27', null, "Iraq war veteran and public military refuser jailed pending court-martial"],
            ['Dustin Stevens', 'Dustin', 'Stevens', 'Male', 'pending', '2010-01-15', null, "Confined to base pending court martial for AWOL/desertion"],
        ] as [$name, $first, $last, $gender, $sentence, $arrest, $release, $action]) {
            $defendants[] = [
                'data' => array_filter([
                    'name' => $name, 'first_name' => $first, 'last_name' => $last,
                    'gender' => $gender, 'race' => 'White', 'state' => 'United States', 'era' => '2000s',
                    'ideologies' => ['Anti-war', 'Conscientious objector'],
                    'affiliation' => null, 'in_custody' => false, 'released' => true,
                    'description' => "{$name} was a U.S. servicemember who refused deployment during the Iraq/Afghanistan wars. {$action}. Sentenced to {$sentence}.",
                ], fn ($v) => $v !== null),
                'cases' => [[
                    'institution_id' => $bopVaried->id,
                    'charges' => 'Desertion / missing movement (UCMJ)',
                    'arrest_date' => $arrest, 'release_date' => $release,
                    'sentence' => $sentence,
                ]],
            ];
        }

        // Anti-war pieing of Senator Levin
        $pieContext = "On August 12, 2010, anti-war activists Ahlam Mohsen and Max Kantar threw cherry pies at U.S. Senator Carl Levin (D-MI), then chair of the Senate Armed Services Committee, at a public event in Big Rapids, Michigan, in protest of his role in U.S. drone warfare against civilians in Yemen. Both pleaded guilty to anti-war charges and were sentenced to 30 days in federal custody beginning August 30, 2011.";

        foreach ([
            ['Ahlam Mohsen', 'Ahlam', 'Mohsen', 'Female', 'Yemeni-American'],
            ['Max Kantar', 'Max', 'Kantar', 'Male', 'White'],
        ] as [$name, $first, $last, $gender, $race]) {
            $defendants[] = [
                'data' => [
                    'name' => $name, 'first_name' => $first, 'last_name' => $last,
                    'gender' => $gender, 'race' => $race, 'state' => 'Michigan', 'era' => '2010s',
                    'ideologies' => ['Anti-war'],
                    'affiliation' => null, 'in_custody' => false, 'released' => true,
                    'description' => "{$pieContext}",
                ],
                'cases' => [[
                    'institution_id' => $bopVaried->id,
                    'charges' => 'Anti-war pieing of U.S. Senator Carl Levin in protest of Yemen drone warfare',
                    'arrest_date' => '2010-08-12', 'incarceration_date' => '2011-08-30', 'release_date' => '2011-09-30',
                    'sentence' => '30 days federal custody',
                ]],
            ];
        }

        // Erin Sieber — 2001 Pentagon Plowshares
        $defendants[] = [
            'data' => [
                'name' => 'Erin Sieber', 'first_name' => 'Erin', 'last_name' => 'Sieber',
                'gender' => 'Female', 'race' => 'White', 'state' => 'Virginia', 'era' => '2000s',
                'ideologies' => ['Anti-nuclear', 'Pacifist', 'Catholic Worker'],
                'affiliation' => ['Plowshares movement', 'Catholic Worker'],
                'in_custody' => false, 'released' => true,
                'description' => "Erin Sieber was a Catholic Worker who in April 2001 carried out a symbolic disarmament action at the Pentagon. She was arrested July 20, 2001 and sentenced to six months in federal prison.",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Damage to property at Pentagon — Plowshares-style action',
                'arrest_date' => '2001-04-15', 'incarceration_date' => '2001-07-20', 'release_date' => '2002-01-20',
                'sentence' => '6 months federal prison',
            ]],
        ];

        // SOA Watch April 2006 batch (additional defendants beyond round 2)
        $soaContextLong = "Each year on the November anniversary of the assassination of six Jesuits, their housekeeper, and her daughter at the University of Central America in San Salvador in 1989, peace activists gather at the gates of Fort Benning, Georgia, site of the U.S. Army School of the Americas / Western Hemisphere Institute for Security Cooperation (WHINSEC), to commemorate the dead and to call for the school's closure. Hundreds have crossed the line onto the base over four decades of vigils; many have served federal sentences ranging from 30 days to a year for trespass.";

        foreach ([
            ['David A. Sylvester', 'David', 'A.', 'Sylvester', 'Male', '90 days'],
            ['Donte Smith', 'Donte', null, 'Smith', 'Male', '90 days'],
            ['Edward Smith', 'Edward', null, 'Smith', 'Male', '6 months'],
            ['Edwin R. Lewinson', 'Edwin', 'R.', 'Lewinson', 'Male', '90 days'],
            ['Francis Woolever', 'Francis', null, 'Woolever', 'Male', '90 days'],
            ['Fredrick Brancel', 'Fredrick', null, 'Brancel', 'Male', '90 days'],
            ['Kenneth F. Crowley', 'Kenneth', 'F.', 'Crowley', 'Male', '6 months'],
            ['Linda Mashburn', 'Linda', null, 'Mashburn', 'Female', '90 days'],
            ['Michael Lee Gayman', 'Michael', 'Lee', 'Gayman', 'Male', '6 months'],
            ['Robert Call', 'Robert', null, 'Call', 'Male', '90 days'],
            ['Samuel Foster', 'Samuel', null, 'Foster', 'Male', '6 months'],
            ['Scott Dempsky', 'Scott', null, 'Dempsky', 'Male', '90 days'],
            ['Stephen Douglas Clements', 'Stephen', 'Douglas', 'Clements', 'Male', '90 days'],
            ['Joseph DeRaymond', 'Joseph', null, 'DeRaymond', 'Male', '90 days'],
            ['Lelia Mattingly', 'Lelia', null, 'Mattingly', 'Female', '6 months'],
            ['Albert L. Simmons', 'Albert', 'L.', 'Simmons', 'Male', '2 months'],
            ['Carna Yipe', 'Carna', null, 'Yipe', 'Female', '30 days'],
            ['Augustine Roddy', 'Augustine', null, 'Roddy', 'Male', '30 days'],
            ['Joan Anderson', 'Joan', null, 'Anderson', 'Female', '30 days'],
            ['Leeanne Clausen', 'Leeanne', null, 'Clausen', 'Female', '30 days'],
            ['Ozone Bhaguan', 'Ozone', null, 'Bhaguan', 'Male', '90 days'],
        ] as $row) {
            [$name, $first, $middle, $last, $gender, $sentence] = $row;
            $defendants[] = [
                'data' => array_filter([
                    'name' => $name, 'first_name' => $first, 'middle_name' => $middle, 'last_name' => $last,
                    'gender' => $gender, 'race' => 'White', 'state' => 'Georgia', 'era' => '2000s',
                    'ideologies' => ['Anti-war', 'Latin America solidarity'],
                    'affiliation' => ['SOA Watch'], 'in_custody' => false, 'released' => true,
                    'description' => "{$name} crossed the line at Fort Benning, Georgia during an SOA Watch vigil to close the U.S. Army School of the Americas / WHINSEC. Sentenced to {$sentence} in federal custody for trespass on a military installation.\n\n{$soaContextLong}",
                ], fn ($v) => $v !== null),
                'cases' => [[
                    'institution_id' => $ftMooreFCI->id,
                    'charges' => 'Trespass at the U.S. Army School of the Americas / WHINSEC, Fort Benning, Georgia',
                    'arrest_date' => '2005-11-19',
                    'sentence' => $sentence,
                ]],
            ];
        }

        // Catholic Worker / Pentagon resistance
        $defendants[] = [
            'data' => [
                'name' => 'Felton Davis', 'first_name' => 'Felton', 'last_name' => 'Davis',
                'gender' => 'Male', 'race' => 'White', 'state' => 'New York', 'era' => '1990s',
                'ideologies' => ['Anti-nuclear', 'Anti-war', 'Catholic Worker'],
                'affiliation' => ['New York Catholic Worker'],
                'in_custody' => false, 'released' => true,
                'description' => "Felton Davis is a New York Catholic Worker arrested at the Pentagon on August 1999 for refusing to leave the Pentagon steps. He served 90 days in federal custody beginning October 24, 1999.",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Trespass at the Pentagon — refusing to leave the steps',
                'arrest_date' => '1999-08-09', 'incarceration_date' => '1999-10-24', 'release_date' => '2000-01-22',
                'sentence' => '90 days',
            ]],
        ];

        // Stephen Kelly Fort Huachuca anti-torture
        // Already in DB as Steve Kelly — skip

        // ─── Round 4: additional entries from agent's full 409-candidate list ───

        $australiaPrison = Institution::firstOrCreate(['name' => 'Northern Territory Custody (Jabiluka prosecution)'], ['state' => 'Australia']);
        $denmarkPrison   = Institution::firstOrCreate(['name' => 'Cornton Vale Prison'], ['state' => 'Scotland']);
        $belgiumPrison   = Institution::firstOrCreate(['name' => 'Belgian Federal Custody'], ['state' => 'Belgium']);
        $ukFairford      = Institution::firstOrCreate(['name' => 'HMP Bristol (RAF Fairford prosecution)'], ['city' => 'Bristol', 'state' => 'United Kingdom']);
        $ukFaslane       = Institution::firstOrCreate(['name' => 'HMP Cornton Vale (Faslane prosecution)'], ['state' => 'Scotland']);
        $prSanJuan       = Institution::firstOrCreate(['name' => 'Federal Detention Center, Guaynabo'], ['city' => 'San Juan', 'state' => 'Puerto Rico']);
        $laCountyJail    = Institution::firstOrCreate(['name' => 'Los Angeles Metropolitan Detention Center'], ['city' => 'Los Angeles', 'state' => 'California']);
        $milesNYJail     = Institution::firstOrCreate(['name' => 'Federal Court — SOA Watch prosecution']);

        // Major individual entries
                $defendants[] = [
            'data' => [
                'name' => 'Ulla Roder', 'first_name' => 'Ulla', 'last_name' => 'Roder',
                'gender' => 'Female', 'state' => 'Denmark', 'era' => '2000s',
                'ideologies' => ['Anti-nuclear', 'Pacifist'],
                'affiliation' => ['Trident Ploughshares', 'Danish anti-nuclear'],
                'in_custody' => false, 'released' => true,
                'description' => "Ulla Roder is a Danish Trident Ploughshares activist who in March 2003, on the eve of the U.S./UK invasion of Iraq, broke into the Royal Air Force base at Leuchars, Scotland, and damaged a Tornado warplane being prepared for deployment to Iraq. She was acquitted in 2003 by a Scottish jury that accepted a necessity defense — one of the first cases in which a UK jury accepted that the Iraq War made nonviolent disarmament action legally justified.",
            ],
            'cases' => [[
                'institution_id' => $denmarkPrison->id,
                'charges' => 'Criminal damage to a Tornado warplane being prepared for deployment to Iraq, RAF Leuchars, Scotland',
                'arrest_date' => '2003-03-13',
                'release_date' => '2003-12-15',
                'convicted' => 'No — acquitted by Scottish jury 2003 on a necessity defense',
                'sentence' => 'Held in pretrial custody approximately 9 months; ultimately acquitted',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Philip Pritchard', 'first_name' => 'Philip', 'last_name' => 'Pritchard',
                'gender' => 'Male', 'state' => 'United Kingdom', 'era' => '2000s',
                'ideologies' => ['Anti-nuclear', 'Anti-war', 'Pacifist'],
                'affiliation' => ['Trident Ploughshares'],
                'in_custody' => false, 'released' => true,
                'description' => "Philip Pritchard is a British Trident Ploughshares activist who, on March 18, 2003 — the day before the U.S./UK invasion of Iraq — broke into RAF Fairford in Gloucestershire, England, and attempted to damage U.S. Air Force B-52 bombers that were preparing to bomb Iraq. He and co-defendant Toby Olditch were tried twice; both juries hung. The Crown ultimately dropped the prosecution. The B-52 Two case became a landmark UK precedent on the right to use direct action to prevent illegal aggressive war.",
            ],
            'cases' => [[
                'institution_id' => $ukFairford->id,
                'charges' => 'Conspiracy to cause criminal damage to U.S. Air Force B-52 bombers at RAF Fairford, England, on the eve of the Iraq invasion',
                'arrest_date' => '2003-03-18',
                'release_date' => '2007-12-21',
                'convicted' => 'No — two hung juries; Crown dropped prosecution',
                'sentence' => 'Held in pretrial custody; ultimately not convicted',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Toby Olditch', 'first_name' => 'Toby', 'last_name' => 'Olditch',
                'gender' => 'Male', 'state' => 'United Kingdom', 'era' => '2000s',
                'ideologies' => ['Anti-nuclear', 'Anti-war', 'Pacifist'],
                'affiliation' => ['Trident Ploughshares'],
                'in_custody' => false, 'released' => true,
                'description' => "Toby Olditch is a British Trident Ploughshares activist who, on March 18, 2003 — the day before the U.S./UK invasion of Iraq — broke into RAF Fairford with Philip Pritchard and attempted to damage U.S. Air Force B-52 bombers. The B-52 Two case ended with two hung juries and the Crown dropping the prosecution.",
            ],
            'cases' => [[
                'institution_id' => $ukFairford->id,
                'charges' => 'Conspiracy to cause criminal damage to U.S. Air Force B-52 bombers at RAF Fairford, England',
                'arrest_date' => '2003-03-18',
                'release_date' => '2007-12-21',
                'convicted' => 'No — two hung juries; Crown dropped prosecution',
                'sentence' => 'Held in pretrial custody; ultimately not convicted',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Pol D\'Huyvetter', 'first_name' => 'Pol', 'last_name' => "D'Huyvetter",
                'gender' => 'Male', 'state' => 'Belgium', 'era' => '1990s',
                'ideologies' => ['Anti-nuclear', 'Pacifist'],
                'affiliation' => ['Forum voor Vredesactie', 'Bomb Spotters'],
                'in_custody' => false, 'released' => true,
                'description' => "Pol D'Huyvetter is a Belgian peace activist and longtime spokesperson for the Forum voor Vredesactie / Bomb Spotters movement. He has been arrested repeatedly for nonviolent civil disobedience at Kleine Brogel Air Base in Belgium, where the U.S. Air Force stores approximately 20 B61 nuclear bombs as part of NATO nuclear sharing.",
            ],
            'cases' => [[
                'institution_id' => $belgiumPrison->id,
                'charges' => 'Trespass at Kleine Brogel Air Base — protesting U.S. nuclear weapons stationed in Belgium',
                'arrest_date' => '1999-08-09',
                'sentence' => 'Multiple short Belgian sentences across decades of organizing',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Mary Trotochaud', 'first_name' => 'Mary', 'last_name' => 'Trotochaud',
                'gender' => 'Female', 'race' => 'White', 'state' => 'Georgia', 'era' => '1990s',
                'ideologies' => ['Anti-war', 'Latin America solidarity'],
                'affiliation' => ['SOA Watch'],
                'in_custody' => false, 'released' => true,
                'description' => "Mary Trotochaud was sentenced to 14 months in federal prison for SOA Watch sign-alteration on September 29, 1997 and repeat trespass at the U.S. Army School of the Americas at Fort Benning, Georgia on November 16, 1997 — among the longest SOA Watch sentences imposed in the late 1990s. She began her sentence in October 1998.",
            ],
            'cases' => [[
                'institution_id' => $ftMooreFCI->id,
                'charges' => 'Sign alteration and repeat trespass at U.S. Army School of the Americas, Fort Benning',
                'arrest_date' => '1997-11-16', 'incarceration_date' => '1998-10-01', 'release_date' => '1999-12-01',
                'sentence' => '14 months federal prison',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Sr. Marge Eilerman OSF', 'first_name' => 'Marge', 'last_name' => 'Eilerman',
                'aka' => 'Sister Marge Eilerman',
                'gender' => 'Female', 'race' => 'White', 'state' => 'Georgia', 'era' => '1990s',
                'ideologies' => ['Anti-war', 'Catholic'],
                'affiliation' => ['Sisters of St. Francis', 'SOA Watch'],
                'in_custody' => false, 'released' => true,
                'description' => "Sister Marge Eilerman OSF was a Sister of St. Francis sentenced to 14 months in federal prison for SOA Watch sign-alteration and repeat trespass at the U.S. Army School of the Americas at Fort Benning, Georgia on November 16, 1997.",
            ],
            'cases' => [[
                'institution_id' => $ftMooreFCI->id,
                'charges' => 'Sign alteration and repeat trespass at U.S. Army School of the Americas',
                'arrest_date' => '1997-11-16', 'incarceration_date' => '1998-10-01', 'release_date' => '1999-12-01',
                'sentence' => '14 months federal prison',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'John Patrick Liteky', 'first_name' => 'John', 'middle_name' => 'Patrick', 'last_name' => 'Liteky',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Georgia', 'era' => '1990s',
                'ideologies' => ['Anti-war', 'Catholic Worker'],
                'affiliation' => ['SOA Watch'],
                'in_custody' => false, 'released' => true,
                'description' => "John Patrick Liteky was sentenced to two years in federal prison for SOA Watch protests at the Pentagon and at Fort Benning beginning June 19, 1998 — among the longest SOA Watch sentences. Brother of Charles Liteky, the Vietnam Medal of Honor recipient who renounced his medal in 1986 in opposition to U.S. policy in Central America.",
            ],
            'cases' => [[
                'institution_id' => $ftMooreFCI->id,
                'charges' => 'Trespass at Pentagon and U.S. Army School of the Americas, Fort Benning',
                'arrest_date' => '1998-06-19', 'release_date' => '2000-06-19',
                'sentence' => '2 years federal prison',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Richard Streb', 'first_name' => 'Richard', 'last_name' => 'Streb',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Georgia', 'era' => '1990s',
                'ideologies' => ['Anti-war', 'Pentagon resistance'],
                'affiliation' => ['SOA Watch'],
                'in_custody' => false, 'released' => true,
                'description' => "Richard Streb was sentenced to six months in federal prison for repeat trespass at the U.S. Army School of the Americas on November 16, 1997.",
            ],
            'cases' => [[
                'institution_id' => $ftMooreFCI->id,
                'charges' => 'Repeat trespass at U.S. Army School of the Americas, Fort Benning',
                'arrest_date' => '1997-11-16', 'release_date' => '1999-03-15',
                'sentence' => '6 months federal prison',
            ]],
        ];

        // Vieques major individuals (high-profile, well-documented)
        $defendants[] = [
            'data' => [
                'name' => 'Rev. Al Sharpton', 'first_name' => 'Alfred', 'last_name' => 'Sharpton',
                'aka' => 'Rev. Al Sharpton',
                'birthdate' => '1954-10-03',
                'gender' => 'Male', 'race' => 'Black', 'state' => 'Puerto Rico', 'era' => '2000s',
                'ideologies' => ['Civil rights', 'Anti-colonial', 'Anti-war'],
                'affiliation' => ['National Action Network'],
                'in_custody' => false, 'released' => true,
                'description' => "The Reverend Al Sharpton, the New York civil-rights leader and founder of the National Action Network, traveled to Vieques in 2001 in solidarity with Puerto Rican civil-disobedience efforts to halt U.S. Navy bombing of the inhabited island. He was arrested for repeat trespass on the bombing range and sentenced to 90 days in federal custody. His arrest was a major catalyst for African-American solidarity with the Vieques campaign.\n\n{$viequesContext}",
            ],
            'cases' => [[
                'institution_id' => $prSanJuan->id,
                'charges' => 'Repeat trespass on U.S. Navy bombing range, Vieques, Puerto Rico',
                'arrest_date' => '2001-05-30', 'release_date' => '2001-08-22',
                'sentence' => '90 days federal',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Ismael Guadalupe Torres', 'first_name' => 'Ismael', 'last_name' => 'Guadalupe Torres',
                'gender' => 'Male', 'race' => 'Hispanic', 'state' => 'Puerto Rico', 'era' => '2000s',
                'ideologies' => ['Anti-colonial', 'Puerto Rican independence'],
                'affiliation' => ['Vieques Committee for the Rescue and Development', 'Comité Pro Rescate y Desarrollo de Vieques'],
                'in_custody' => false, 'released' => true,
                'description' => "Ismael Guadalupe Torres is one of the founding members of the Vieques Committee for the Rescue and Development (Comité Pro Rescate y Desarrollo de Vieques) and a longtime leader in the campaign to remove the U.S. Navy from his home island. He served 120 to 140 days in federal custody for occupying the Navy bombing range in January 2003.\n\n{$viequesContext}",
            ],
            'cases' => [[
                'institution_id' => $prSanJuan->id,
                'charges' => 'Occupying U.S. Navy bombing range, Vieques, Puerto Rico',
                'arrest_date' => '2003-01-15', 'release_date' => '2003-06-04',
                'sentence' => '120–140 days federal',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Israel Medina Colón', 'first_name' => 'Israel', 'last_name' => 'Medina Colón',
                'gender' => 'Male', 'race' => 'Hispanic', 'state' => 'Puerto Rico', 'era' => '2000s',
                'ideologies' => ['Anti-colonial', 'Puerto Rican independence'],
                'affiliation' => null, 'in_custody' => false, 'released' => true,
                'description' => "Israel Medina Colón served 120–140 days in federal custody for occupying the U.S. Navy bombing range on Vieques Island in January 2003 — the final wave of civil disobedience that helped force the Navy's withdrawal in May 2003.\n\n{$viequesContext}",
            ],
            'cases' => [[
                'institution_id' => $prSanJuan->id,
                'charges' => 'Occupying U.S. Navy bombing range, Vieques',
                'arrest_date' => '2003-01-15', 'release_date' => '2003-06-04',
                'sentence' => '120–140 days federal',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Alberto De Jesús Mercado', 'first_name' => 'Alberto', 'last_name' => 'De Jesús Mercado',
                'aka' => 'Tito Kayak',
                'gender' => 'Male', 'race' => 'Hispanic', 'state' => 'Puerto Rico', 'era' => '2000s',
                'ideologies' => ['Anti-colonial', 'Puerto Rican independence', 'Environmental'],
                'affiliation' => null, 'in_custody' => false, 'released' => true,
                'description' => "Alberto 'Tito Kayak' De Jesús Mercado is a Puerto Rican environmental and independence activist famous for spectacular climbing actions, including a 2000 climb of the Statue of Liberty in support of Vieques. He was sentenced to one year in federal custody for repeat trespass on Vieques between 1999 and 2001.\n\n{$viequesContext}",
            ],
            'cases' => [[
                'institution_id' => $prSanJuan->id,
                'charges' => 'Repeated trespass on Vieques, Puerto Rico',
                'arrest_date' => '2001-05-15', 'release_date' => '2002-05-15',
                'sentence' => '1 year federal',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Robert Rabin Siegal', 'first_name' => 'Robert', 'last_name' => 'Rabin Siegal',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Puerto Rico', 'era' => '2000s',
                'ideologies' => ['Anti-colonial', 'Anti-war'],
                'affiliation' => ['Vieques Committee for the Rescue and Development'],
                'in_custody' => false, 'released' => true,
                'description' => "Robert Rabin Siegal is a U.S.-born Vieques resident and longtime member of the Vieques Committee for the Rescue and Development who served six months in federal custody for civil disobedience halting U.S. Navy bombing of Vieques.\n\n{$viequesContext}",
            ],
            'cases' => [[
                'institution_id' => $prSanJuan->id,
                'charges' => 'Civil disobedience on U.S. Navy bombing range, Vieques',
                'arrest_date' => '2002-04-11', 'release_date' => '2002-10-11',
                'sentence' => '6 months federal',
            ]],
        ];

        // Plowshares (Project ELF)
        $defendants[] = [
            'data' => [
                'name' => 'Michael Sprong', 'first_name' => 'Michael', 'last_name' => 'Sprong',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Wisconsin', 'era' => '2000s',
                'ideologies' => ['Anti-nuclear', 'Pacifist', 'Catholic Worker'],
                'affiliation' => ['Nukewatch', 'Plowshares movement'],
                'in_custody' => false, 'released' => true,
                'description' => "Michael Sprong was part of the Silence Trident direct disarmament action against Project ELF, the U.S. Navy's extremely-low-frequency submarine command system in northern Wisconsin and Michigan, in June 2000. He served 2 months in federal custody.",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Silence Trident direct disarmament of Project ELF',
                'arrest_date' => '2000-06-15', 'release_date' => '2001-07-22',
                'sentence' => '2 months federal',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Mark Kinney', 'first_name' => 'Mark', 'last_name' => 'Kinney',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Nebraska', 'era' => '2000s',
                'ideologies' => ['Anti-nuclear', 'Pacifist', 'Plowshares'],
                'affiliation' => ['Plowshares movement'],
                'in_custody' => false, 'released' => true,
                'description' => "Mark Kinney was sentenced to six months in federal prison for repeated trespass at the U.S. Strategic Command headquarters at Offutt Air Force Base, Nebraska. (Distinct from Mark Kenney of the 2010 Offutt protest.)",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Repeated trespass at U.S. Strategic Command, Offutt AFB, Nebraska',
                'arrest_date' => '2000-04-16', 'release_date' => '2000-10-16',
                'sentence' => '6 months federal',
            ]],
        ];

        // Kristen Betts — Andrews AFB Plowshares
        $defendants[] = [
            'data' => [
                'name' => 'Kristen Betts', 'first_name' => 'Kristen', 'last_name' => 'Betts',
                'gender' => 'Female', 'race' => 'White', 'state' => 'Maryland', 'era' => '2000s',
                'ideologies' => ['Anti-nuclear', 'Pacifist', 'Catholic Worker'],
                'affiliation' => ['Plowshares movement', 'Pentagon resistance'],
                'in_custody' => false, 'released' => true,
                'description' => "Kristen Betts was arrested at the Andrews Air Force Base Open House on May 2000 for holding a banner and leafleting in protest of nuclear weapons. She was sentenced to 60 days in federal custody beginning October 23, 2000.",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Banner-holding and leafleting at Andrews Air Force Base Open House',
                'arrest_date' => '2000-05-21', 'incarceration_date' => '2000-10-23', 'release_date' => '2000-12-22',
                'sentence' => '60 days federal',
            ]],
        ];

        // Jenny Gaiawyn — UK Faslane
        $defendants[] = [
            'data' => [
                'name' => 'Jenny Gaiawyn', 'first_name' => 'Jenny', 'last_name' => 'Gaiawyn',
                'gender' => 'Female', 'state' => 'United Kingdom', 'era' => '2000s',
                'ideologies' => ['Anti-nuclear', 'Pacifist'],
                'affiliation' => ['Trident Ploughshares', 'Faslane Peace Camp'],
                'in_custody' => false, 'released' => true,
                'description' => "Jenny Gaiawyn was a UK Trident Ploughshares activist arrested for trespass at the Trident submarine base at Faslane, Scotland — home of the UK's nuclear deterrent. She was sentenced to three months in custody beginning November 28, 2001.",
            ],
            'cases' => [[
                'institution_id' => $ukFaslane->id,
                'charges' => 'Trespass at Trident submarine base, Faslane, Scotland',
                'arrest_date' => '2001-11-28', 'release_date' => '2002-02-28',
                'sentence' => '3 months UK custody',
            ]],
        ];

        // Hennessey sisters — Iowa SOA Watch
        foreach ([
            ['Dorothy M. Hennessey', 'Dorothy', 'M.', 'Hennessey', '6 months'],
            ['Gwen Hennessey', 'Gwen', null, 'Hennessey', '6 months'],
        ] as [$name, $first, $middle, $last, $sentence]) {
            $defendants[] = [
                'data' => array_filter([
                    'name' => $name, 'first_name' => $first, 'middle_name' => $middle, 'last_name' => $last,
                    'gender' => 'Female', 'race' => 'White', 'state' => 'Iowa', 'era' => '2000s',
                    'ideologies' => ['Anti-war', 'Catholic'],
                    'affiliation' => ['Sisters of the Presentation', 'SOA Watch'],
                    'in_custody' => false, 'released' => true,
                    'description' => "{$name} is one of the Hennessey sisters of Iowa, both Sisters of the Presentation, who became iconic SOA Watch defendants in their seventies. Sentenced to six months in federal prison for SOA Watch line-crossing at Fort Benning.\n\n{$soaContextLong}",
                ], fn ($v) => $v !== null),
                'cases' => [[
                    'institution_id' => $ftMooreFCI->id,
                    'charges' => 'Trespass at U.S. Army School of the Americas, Fort Benning',
                    'arrest_date' => '2000-11-19',
                    'sentence' => $sentence,
                ]],
            ];
        }
        // SOA Watch repeat line-crossers — November 2002 batch (sentenced 12/02 and 3/03)
        foreach ([
            ['Charles Booker-Hirsch', 'Charles', null, 'Booker-Hirsch', 'Male', '6 months'],
            ['Chantilly Geigle', 'Chantilly', null, 'Geigle', 'Female', '6 months'],
            ['Erik Johnson', 'Erik', null, 'Johnson', 'Male', '6 months'],
            ['Jonna Cohen', 'Jonna', null, 'Cohen', 'Female', '6 months'],
            ['Kate Fontanazza', 'Kate', null, 'Fontanazza', 'Female', '6 months'],
            ['Kathleen Boylan', 'Kathleen', null, 'Boylan', 'Female', '3 months'],
            ['Kathleen Desautels', 'Kathleen', null, 'Desautels', 'Female', '6 months'],
            ['Mary Dean', 'Mary', null, 'Dean', 'Female', '6 months'],
            ['Michael Sobol', 'Michael', null, 'Sobol', 'Male', '6 months'],
            ['Michaele Pasquale', 'Michaele', null, 'Pasquale', 'Female', '6 months'],
            ['Niklan Jones-Lezama', 'Niklan', null, 'Jones-Lezama', 'Male', '6 months'],
            ['Palmer Legare', 'Palmer', null, 'Legare', 'Male', '6 months'],
            ['Peter Gelderloos', 'Peter', null, 'Gelderloos', 'Male', '6 months'],
            ['Rae Kramer', 'Rae', null, 'Kramer', 'Female', '6 months'],
            ['Richard M. Ring', 'Richard', 'M.', 'Ring', 'Male', '3 months'],
            ['Summer Nelson', 'Summer', null, 'Nelson', 'Female', '6 months'],
            ['Susan Daniels', 'Susan', null, 'Daniels', 'Female', '3 months'],
            ['Thomas Mahedy', 'Thomas', null, 'Mahedy', 'Male', '3 months'],
            ['Toni Flynn', 'Toni', null, 'Flynn', 'Female', '6 months'],
            ['Bill O\'Donnell', 'Bill', null, "O'Donnell", 'Male', '6 months'],
            ['Abigail Miller', 'Abigail', null, 'Miller', 'Female', '6 months'],
            ['Laura MacDonald', 'Laura', null, 'MacDonald', 'Female', '3 months'],
        ] as $row) {
            [$name, $first, $middle, $last, $gender, $sentence] = $row;
            $defendants[] = [
                'data' => array_filter([
                    'name' => $name, 'first_name' => $first, 'middle_name' => $middle, 'last_name' => $last,
                    'gender' => $gender, 'race' => 'White', 'state' => 'Georgia', 'era' => '2000s',
                    'ideologies' => ['Anti-war', 'Latin America solidarity'],
                    'affiliation' => ['SOA Watch'], 'in_custody' => false, 'released' => true,
                    'description' => "{$name} crossed the line at Fort Benning, Georgia during the November 2001 SOA Watch vigil to close the U.S. Army School of the Americas / WHINSEC. Sentenced to {$sentence} in federal custody.\n\n{$soaContextLong}",
                ], fn ($v) => $v !== null),
                'cases' => [[
                    'institution_id' => $ftMooreFCI->id,
                    'charges' => 'Trespass at U.S. Army School of the Americas, Fort Benning',
                    'arrest_date' => '2001-11-18',
                    'sentence' => $sentence,
                ]],
            ];
        }

        // SOA Watch November 2000 batch
        foreach ([
            ['Jack Gilroy', 'Jack', null, 'Gilroy', 'Male', '6 months, $1000 fine'],
            ['Clare Marie Hanrahan', 'Clare', 'Marie', 'Hanrahan', 'Female', '6 months, $500 fine'],
            ['David Corcoran', 'David', null, 'Corcoran', 'Male', '6 months, $1000 fine'],
            ['Hazel Tulecke', 'Hazel', null, 'Tulecke', 'Female', '3 months'],
            ['Joel Kilgour', 'Joel', null, 'Kilgour', 'Male', '1 month'],
            ['John Ewers', 'John', null, 'Ewers', 'Male', '6 months, $500 fine'],
            ['Josh Raisler Cohn', 'Josh', null, 'Raisler Cohn', 'Male', '6 months, $1000 fine'],
            ['Lois Putzier', 'Lois', null, 'Putzier', 'Female', '6 months, $500 fine'],
            ['Mary Lou Benson', 'Mary', 'Lou', 'Benson', 'Female', '6 months, $1000 fine'],
            ['Miriam Spencer', 'Miriam', null, 'Spencer', 'Female', '6 months, $150 fine'],
            ['Rachel Louise Hayward', 'Rachel', 'Louise', 'Hayward', 'Female', '6 months, $1000 fine'],
            ['Rebecca Kanner', 'Rebecca', null, 'Kanner', 'Female', '6 months, $500 fine'],
            ['Richard John Kinane', 'Richard', 'John', 'Kinane', 'Male', '6 months, $500 fine'],
            ['Russell De Young', 'Russell', null, 'De Young', 'Male', '6 months, $1000 fine'],
            ['William Houston', 'William', null, 'Houston', 'Male', '6 months, $500 fine'],
            ['Eric Robison', 'Eric', null, 'Robison', 'Male', '6 months, $150 fine'],
            ['Elizabeth Anne McKenzie', 'Elizabeth', 'Anne', 'McKenzie', 'Female', '6 months, $500 fine'],
        ] as $row) {
            [$name, $first, $middle, $last, $gender, $sentence] = $row;
            $defendants[] = [
                'data' => array_filter([
                    'name' => $name, 'first_name' => $first, 'middle_name' => $middle, 'last_name' => $last,
                    'gender' => $gender, 'race' => 'White', 'state' => 'Georgia', 'era' => '2000s',
                    'ideologies' => ['Anti-war', 'Latin America solidarity'],
                    'affiliation' => ['SOA Watch'], 'in_custody' => false, 'released' => true,
                    'description' => "{$name} crossed the line at Fort Benning, Georgia during the November 2000 SOA Watch vigil. Sentenced to {$sentence}.\n\n{$soaContextLong}",
                ], fn ($v) => $v !== null),
                'cases' => [[
                    'institution_id' => $ftMooreFCI->id,
                    'charges' => 'Trespass at U.S. Army School of the Americas, Fort Benning',
                    'arrest_date' => '2000-11-19',
                    'sentence' => $sentence,
                ]],
            ];
        }

        // Ash Wednesday Los Angeles 2003 — Catholic Worker blockade
        $ashWedContext = "On Ash Wednesday, March 5, 2003 — two weeks before the U.S. invasion of Iraq — Los Angeles Catholic Workers blockaded an intersection at the downtown Los Angeles federal building in liturgical antiwar witness. They received 45-day federal sentences.";

        foreach ([
            ['David Gardner', 'David', 'Gardner', 'Male'],
            ['Jim Parkhurst', 'Jim', 'Parkhurst', 'Male'],
            ['Joyce Parkhurst', 'Joyce', 'Parkhurst', 'Female'],
            ['Martha Scarborough', 'Martha', 'Scarborough', 'Female'],
            ['Fr. Chris Ponnet', 'Chris', 'Ponnet', 'Male'],
        ] as [$name, $first, $last, $gender]) {
            $defendants[] = [
                'data' => [
                    'name' => $name, 'first_name' => $first, 'last_name' => $last,
                    'gender' => $gender, 'race' => 'White', 'state' => 'California', 'era' => '2000s',
                    'ideologies' => ['Anti-war', 'Catholic Worker'],
                    'affiliation' => ['Los Angeles Catholic Worker'],
                    'in_custody' => false, 'released' => true,
                    'description' => "{$name} was a Los Angeles Catholic Worker who participated in the Ash Wednesday March 5, 2003 blockade of the downtown Los Angeles federal building, two weeks before the U.S. invasion of Iraq. Sentenced to 45 days federal custody beginning May 1, 2003.\n\n{$ashWedContext}",
                ],
                'cases' => [[
                    'institution_id' => $laCountyJail->id,
                    'charges' => 'Blockade of intersection at downtown Los Angeles federal building',
                    'arrest_date' => '2003-03-05', 'incarceration_date' => '2003-05-01', 'release_date' => '2003-06-15',
                    'sentence' => '45 days federal',
                ]],
            ];
        }

        // Catherine Morris — LA Catholic Worker
        $defendants[] = [
            'data' => [
                'name' => 'Catherine Morris', 'first_name' => 'Catherine', 'last_name' => 'Morris',
                'gender' => 'Female', 'race' => 'White', 'state' => 'California', 'era' => '1980s',
                'ideologies' => ['Anti-war', 'Pacifist', 'Catholic Worker'],
                'affiliation' => ['Los Angeles Catholic Worker'],
                'in_custody' => false, 'released' => true,
                'description' => "Catherine Morris is a longtime Los Angeles Catholic Worker — celebrating 50 years at the LA Catholic Worker in 2022 — and has been arrested numerous times across five decades for civil disobedience at military installations, federal buildings, and corporate war-profiteer offices in protest of U.S. wars and nuclear weapons. The 2022 issue #200 of the Nuclear Resister honored her decades of resistance.",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Multiple anti-war and anti-nuclear civil disobedience arrests across five decades',
                'arrest_date' => '1980-08-06',
                'release_date' => '2022-12-31',
                'sentence' => 'Cumulative weeks across many short sentences',
            ]],
        ];

        // Janice Sevre-Duszynska — repeat SOA Watch + Catholic women priest
        $defendants[] = [
            'data' => [
                'name' => 'Janice Sevre-Duszynska', 'first_name' => 'Janice', 'last_name' => 'Sevre-Duszynska',
                'gender' => 'Female', 'race' => 'White', 'state' => 'Kentucky', 'era' => '2010s',
                'ideologies' => ['Anti-war', 'Pacifist', 'Catholic'],
                'affiliation' => ['Roman Catholic Womenpriests', 'SOA Watch'],
                'in_custody' => false, 'released' => true,
                'description' => "Janice Sevre-Duszynska is a Roman Catholic Womenpriests-ordained priest from Lexington, Kentucky who has been arrested repeatedly at SOA Watch vigils at Fort Benning and at Pentagon protests, serving multiple short federal sentences across more than a decade of organizing.",
            ],
            'cases' => [[
                'institution_id' => $ftMooreFCI->id,
                'charges' => 'Trespass at U.S. Army School of the Americas (Fort Benning) and at the Pentagon',
                'arrest_date' => '2003-11-23',
                'release_date' => '2018-12-31',
                'sentence' => 'Multiple short federal sentences',
            ]],
        ];

        // Joffre Stewart — historic anti-war/anti-conscription
        $defendants[] = [
            'data' => [
                'name' => 'Joffre Stewart', 'first_name' => 'Joffre', 'last_name' => 'Stewart',
                'gender' => 'Male', 'race' => 'Black', 'state' => 'Illinois', 'era' => '1940s',
                'ideologies' => ['Anti-war', 'Pacifist', 'Anarchist'],
                'affiliation' => null, 'in_custody' => false, 'released' => true,
                'description' => "Joffre Stewart was a Chicago-based African-American pacifist, poet, and anti-conscription activist who refused to register for the draft during World War II and was arrested repeatedly across the following decades for anti-war civil disobedience. He was a familiar figure at Chicago peace demonstrations from the 1940s into the 21st century. Allen Ginsberg referenced him in 'Howl' as 'Joffre' the 'pacifist who refused the draft.'",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Refusing to register for the draft (World War II) plus subsequent anti-war arrests',
                'arrest_date' => '1942-12-15',
                'release_date' => '1946-06-15',
                'sentence' => 'Federal prison time during World War II for draft refusal; multiple shorter subsequent sentences',
            ]],
        ];

        // ─── Round 5: bulk Vieques mass-action arrestees and remaining SOA Watch / Catholic Worker / military refusers ───

        // Vieques mass arrestees 1999-2003 (60+ Puerto Rican community members + solidarity activists)
        // Most served 30-90 days for trespass on Navy bombing range.
        foreach ([
            ['Anne Lee', 'Anne', 'Lee', 'Female', '3 months', '2001-04-15', '2001-07-04'],
            ['Armando Caban Vega', 'Armando', 'Caban Vega', 'Male', '40 days', '2001-05-25', '2001-07-03'],
            ['Asunción Papo Rodriguez Crespo', 'Asunción', 'Rodriguez Crespo', 'Male', '40 days', '2001-05-25', '2001-07-01'],
            ['Axel Mora Meléndez', 'Axel', 'Mora Meléndez', 'Male', 'denied bond', '2001-04-12', null],
            ['Blanca Gari Perez', 'Blanca', 'Gari Perez', 'Female', '40 days', '2001-05-25', '2001-07-01'],
            ['Brian Barrett', 'Brian', 'Barrett', 'Male', 'short', '2001-05-15', null],
            ['Carlos Al Santiago Rivera', 'Carlos', 'Santiago Rivera', 'Male', '4 months', '2001-04-15', '2001-08-27'],
            ['Carmen Gonzalez Arias', 'Carmen', 'Gonzalez Arias', 'Female', 'denied bond', '2001-04-06', null],
            ['Cruz Pérez', 'Cruz', 'Pérez', 'Male', '20 days', '2001-04-04', '2001-04-24'],
            ['Dalimar Vega Rodríguez', 'Dalimar', 'Vega Rodríguez', 'Female', '30-40 days', '2001-06-04', '2001-07-10'],
            ['Danny Rivera Mendez', 'Danny', 'Rivera Mendez', 'Male', '30 days', '2001-06-06', '2001-07-06'],
            ['Dámaso', 'Dámaso', null, 'Male', '4 months', '2001-05-15', '2001-12-11'],
            ['Edgardo Rodríguez Riquelme', 'Edgardo', 'Rodríguez Riquelme', 'Male', '30 days', '2001-06-06', '2001-07-06'],
            ['Ediberto Santiago Diaz', 'Ediberto', 'Santiago Diaz', 'Male', '40 days', '2001-05-25', '2001-07-03'],
            ['Emma Vélez Hernández', 'Emma', 'Vélez Hernández', 'Female', '30 days', '2001-04-04', '2001-05-04'],
            ['Ernesto Peña Carambot', 'Ernesto', 'Peña Carambot', 'Male', '90 days', '2001-05-23', '2001-08-21'],
            ['Félix Aponte González', 'Félix', 'Aponte González', 'Male', '45 days', '2001-05-21', '2001-07-05'],
            ['Félix Rodríguez', 'Félix', 'Rodríguez', 'Male', '2 months', '2001-05-01', '2001-07-01'],
            ['Francisco Báez', 'Francisco', 'Báez', 'Male', 'denied bond', '2001-04-09', null],
            ['Gustavo García López', 'Gustavo', 'García López', 'Male', '45 days', '2001-05-21', '2001-07-05'],
            ['Harold A. Penner', 'Harold', 'Penner', 'Male', '40 days', '2001-06-08', '2001-07-18'],
            ['Hiram Lozada', 'Hiram', 'Lozada', 'Male', '120 days', '2001-04-11', '2001-08-09'],
            ['Iván Elías Rodríguez', 'Iván', 'Elías Rodríguez', 'Male', '100 days', '2001-04-11', '2001-07-20'],
            ['Imac Morales Carrasquillo', 'Imac', 'Morales Carrasquillo', 'Male', 'denied bond', '2002-09-10', null],
            ['Irma Rodríguez', 'Irma', 'Rodríguez', 'Female', '30 days', '2001-04-04', '2001-05-04'],
            ['Ismael González', 'Ismael', 'González', 'Male', 'denied bond', '2001-04-05', null],
            ['Jaime Perell', 'Jaime', 'Perell', 'Male', '30 days', '2001-06-06', '2001-07-06'],
            ['Jesús Cabrera Cirilo', 'Jesús', 'Cabrera Cirilo', 'Male', '40 days', '2001-06-06', '2001-07-14'],
            ['José Meléndez Cotto', 'José', 'Meléndez Cotto', 'Male', '45 days', '2001-05-21', '2001-07-05'],
            ['José Perez Gonzalez', 'José', 'Perez Gonzalez', 'Male', '90 days', '2001-05-04', '2001-08-01'],
            ['José Perez Rivera', 'José', 'Perez Rivera', 'Male', '45 days', '2001-04-27', '2001-06-11'],
            ['José R. Rivera Santana', 'José', 'Rivera Santana', 'Male', '40 days', '2001-06-01', '2001-07-10'],
            ['José Rivera', 'José', 'Rivera', 'Male', '90 days', '2001-05-23', '2001-08-22'],
            ['José Román Rivera', 'José', 'Román Rivera', 'Male', '90 days', '2001-05-23', '2001-08-22'],
            ['José Tato Rivera Santana', 'José', 'Tato Rivera Santana', 'Male', '90 days', '2001-05-23', '2001-08-21'],
            ['Juan Alverio', 'Juan', 'Alverio', 'Male', 'denied bond', '2001-04-10', null],
            ['Julio Ortega Miranda', 'Julio', 'Ortega Miranda', 'Male', '40 days', '2001-05-22', '2001-07-01'],
            ['Luis A. Olivera', 'Luis', 'Olivera', 'Male', '45 days', '2001-04-27', '2001-06-11'],
            ['Luis Figueroa Félix', 'Luis', 'Figueroa Félix', 'Male', '40 days', '2001-05-25', '2001-07-03'],
            ['Maribel Rodríguez Arroyo', 'Maribel', 'Rodríguez Arroyo', 'Female', '30 days', '2001-04-01', '2001-05-01'],
            ['María de Lordes Santiago', 'María', 'de Lordes Santiago', 'Female', '30 days', '2001-04-01', '2001-05-01'],
            ['Mauro Simpson', 'Mauro', 'Simpson', 'Male', 'awaiting trial', '2002-09-10', null],
            ['Mirna Rodríguez', 'Mirna', 'Rodríguez', 'Female', '30 days', '2001-04-01', '2001-05-01'],
            ['Nazael Montalvo Rodríguez', 'Nazael', 'Montalvo Rodríguez', 'Male', 'denied bond', '2001-04-12', null],
            ['Nestor Nazario Trabal', 'Nestor', 'Nazario Trabal', 'Male', '40 days', '2001-05-22', '2001-07-01'],
            ['Norma Ortiz Gusmán', 'Norma', 'Ortiz Gusmán', 'Female', 'awaiting trial', '2002-09-11', null],
            ['Nydia Gonzalez Reyes', 'Nydia', 'Gonzalez Reyes', 'Female', 'awaiting trial', '2002-09-12', null],
            ['Oscar Charriez Lozada', 'Oscar', 'Charriez Lozada', 'Male', '100 days', '2001-04-11', '2001-07-20'],
            ['Pedro José Muñiz Garcia', 'Pedro', 'Muñiz Garcia', 'Male', '90 days', '2001-05-23', '2001-08-21'],
            ['Petra Barreras del Río', 'Petra', 'Barreras del Río', 'Female', '6 months', '2001-04-11', '2001-10-11'],
            ['Rafael Feliciano', 'Rafael', 'Feliciano', 'Male', 'denied bond', '2001-04-09', null],
            ['Rafael Pérez Torruella', 'Rafael', 'Pérez Torruella', 'Male', 'awaiting trial', '2002-09-10', null],
            ['Reynaldo Salivas Gonzalez', 'Reynaldo', 'Salivas Gonzalez', 'Male', '40 days', '2001-05-23', '2001-07-02'],
            ['Reynand Ortiz Feliciano', 'Reynand', 'Ortiz Feliciano', 'Male', '90 days', '2001-05-23', '2001-08-21'],
            ['Ricardo Espada Rosado', 'Ricardo', 'Espada Rosado', 'Male', '40 days', '2001-05-25', '2001-07-03'],
            ['Roberto Ramirez', 'Roberto', 'Ramirez', 'Male', '40 days', '2001-05-22', '2001-07-01'],
            ['Rosalinda Soto Toledo', 'Rosalinda', 'Soto Toledo', 'Female', '40 days', '2001-05-22', '2001-07-01'],
            ['Samuel Soto Bosques', 'Samuel', 'Soto Bosques', 'Male', '4 months', '2001-04-27', '2001-08-27'],
            ['Santos Rubén Hernández García', 'Santos', 'Hernández García', 'Male', 'denied bond', '2001-04-12', null],
            ['Tomás Vargas', 'Tomás', 'Vargas', 'Male', '2 months', '2001-05-01', '2001-07-01'],
            ['Violeta Vega Ríos', 'Violeta', 'Vega Ríos', 'Female', '40 days', '2001-04-04', '2001-05-14'],
            ['Zoraida Santiago Buitrago', 'Zoraida', 'Santiago Buitrago', 'Female', '40 days', '2001-05-22', '2001-07-01'],
        ] as $row) {
            [$name, $first, $last, $gender, $sentence, $arrest, $release] = $row;
            $defendants[] = [
                'data' => array_filter([
                    'name' => $name, 'first_name' => $first, 'last_name' => $last,
                    'gender' => $gender, 'race' => 'Hispanic', 'state' => 'Puerto Rico', 'era' => '2000s',
                    'ideologies' => ['Anti-colonial', 'Anti-war', 'Puerto Rican independence'],
                    'affiliation' => ['Vieques resistance'], 'in_custody' => false, 'released' => true,
                    'description' => "{$name} was arrested for trespass on the U.S. Navy bombing range at Vieques, Puerto Rico, as part of the sustained 1999–2003 civil-disobedience campaign that ultimately forced the Navy to withdraw from the inhabited island in May 2003. Sentenced to {$sentence}.\n\n{$viequesContext}",
                ], fn ($v) => $v !== null),
                'cases' => [[
                    'institution_id' => $prSanJuan->id,
                    'charges' => 'Trespass on U.S. Navy bombing range, Vieques, Puerto Rico',
                    'arrest_date' => $arrest, 'release_date' => $release,
                    'sentence' => $sentence,
                ]],
            ];
        }

        // SOA Watch additional batches (1997-2003) — every named line-crosser with documented sentence
        foreach ([
            ['Brian Buckley', 'Brian', null, 'Buckley', 'Male', '3 months', '2003-11-23'],
            ['Brooks Anderson', 'Brooks', null, 'Anderson', 'Male', '3 months', '2000-11-19'],
            ['Charles Butler', 'Charles', null, 'Butler', 'Male', '3 months', '2000-11-19'],
            ['Edith Balot', 'Edith', null, 'Balot', 'Female', '3 months', '2002-11-17'],
            ['Gerhard Fischer', 'Gerhard', null, 'Fischer', 'Male', '3 months', '2000-11-19'],
            ['Jason Lydon', 'Jason', null, 'Lydon', 'Male', '6 months', '2002-11-17'],
            ['Jessica Carr', 'Jessica', null, 'Carr', 'Female', '3 months', '2002-11-17'],
            ['John Honeck', 'John', null, 'Honeck', 'Male', '3 months', '2000-11-19'],
            ['Judy Bierbaum', 'Judy', null, 'Bierbaum', 'Female', '3 months', '2000-11-19'],
            ['Margaret Knapke', 'Margaret', null, 'Knapke', 'Female', '3 months', '2000-11-19'],
            ['Marie Salupo', 'Marie', null, 'Salupo', 'Female', '3 months', '2002-11-17'],
            ['Marvin Warren', 'Marvin', null, 'Warren', 'Male', '3 months', '2002-11-17'],
            ['Pamela McBride', 'Pamela', null, 'McBride', 'Female', '6 months', '2002-11-17'],
            ['Pedro Zenón Encarnacíon', 'Pedro', 'Zenón', 'Encarnacíon', 'Male', '3 months', '2002-11-17'],
            ['Sr. Caryl Hartjes', 'Caryl', null, 'Hartjes', 'Female', '3 months', '2002-11-17'],
            ['Sr. Kathy Long', 'Kathy', null, 'Long', 'Female', '3 months', '2002-11-17'],
            ['Sr. Moira Kenny', 'Moira', null, 'Kenny', 'Female', '6 months', '2002-11-17'],
            ['Thomas Bottolene', 'Thomas', null, 'Bottolene', 'Male', '3 months', '2000-11-19'],
            ['William Slattery', 'William', null, 'Slattery', 'Male', '6 months', '2002-11-17'],
            ['Dan Fortson', 'Dan', null, 'Fortson', 'Male', '3 months', '2002-11-17'],
            ['Dave Tarbell', 'Dave', null, 'Tarbell', 'Male', '6 months', '2002-11-17'],
            ['Don Haselfeld', 'Don', null, 'Haselfeld', 'Male', '6 months', '2002-11-17'],
            ['Dorothy Pagosa', 'Dorothy', null, 'Pagosa', 'Female', '3 months', '2002-11-17'],
            ['Douglas Kasper', 'Douglas', null, 'Kasper', 'Male', '3 months', '2002-11-17'],
            ['Fr. Jim Hynes', 'Jim', null, 'Hynes', 'Male', '6 months', '2002-11-17'],
            ['Joyce Elwanger', 'Joyce', null, 'Elwanger', 'Female', '6 months', '2002-11-17'],
            ['Lisa Hughes', 'Lisa', null, 'Hughes', 'Female', '6 months', '2002-11-17'],
            ['Marilyn White', 'Marilyn', null, 'White', 'Female', '6 months', '2002-11-17'],
            ['Katherine Bjorkman', 'Katherine', null, 'Bjorkman', 'Female', '3 months', '2002-11-17'],
            ['Katherine Brown', 'Katherine', null, 'Brown', 'Female', '3 months', '2002-11-17'],
            ['Frances Guzman Lago', 'Frances', null, 'Guzman Lago', 'Female', '3 months', '2002-11-17'],
        ] as $row) {
            [$name, $first, $middle, $last, $gender, $sentence, $arrest] = $row;
            $defendants[] = [
                'data' => array_filter([
                    'name' => $name, 'first_name' => $first, 'middle_name' => $middle, 'last_name' => $last,
                    'gender' => $gender, 'race' => 'White', 'state' => 'Georgia', 'era' => '2000s',
                    'ideologies' => ['Anti-war', 'Latin America solidarity'],
                    'affiliation' => ['SOA Watch'], 'in_custody' => false, 'released' => true,
                    'description' => "{$name} crossed the line at Fort Benning, Georgia during an SOA Watch vigil to close the U.S. Army School of the Americas / WHINSEC. Sentenced to {$sentence}.\n\n{$soaContextLong}",
                ], fn ($v) => $v !== null),
                'cases' => [[
                    'institution_id' => $ftMooreFCI->id,
                    'charges' => 'Trespass at U.S. Army School of the Americas, Fort Benning',
                    'arrest_date' => $arrest,
                    'sentence' => $sentence,
                ]],
            ];
        }

        // Additional military refusers
        foreach ([
            ['Blake Lemoine', 'Blake', 'Lemoine', '7 months', '2005-02-15', '2005-09-17', "Refused deployment to Iraq; pled guilty to AWOL/desertion"],
            ['Justin Raymond Colby', 'Justin Raymond', 'Colby', '9 months', '2013-03-23', '2013-12-23', "Military refuser, SOA Watch protester"],
            ['Kenneth Hayes', 'Kenneth', 'Hayes', '6 months', '2010-03-16', '2010-09-16', "SOA Watch trespass at Fort Benning, 11/09"],
            ['Robert Alford', 'Robert', 'Alford', 'short', '2009-03-15', '2009-08-15', "Iraq War military refuser; pled guilty to desertion"],
            ['Elijah Smith', 'Elijah', 'Smith', 'short', '2009-04-15', null, "Iraq War military refuser; pled guilty to desertion"],
        ] as [$name, $first, $last, $sentence, $arrest, $release, $action]) {
            $defendants[] = [
                'data' => array_filter([
                    'name' => $name, 'first_name' => $first, 'last_name' => $last,
                    'gender' => 'Male', 'race' => 'White', 'state' => 'United States', 'era' => '2000s',
                    'ideologies' => ['Anti-war', 'Conscientious objector'],
                    'affiliation' => null, 'in_custody' => false, 'released' => true,
                    'description' => "{$name} was a U.S. servicemember who refused deployment during the Iraq/Afghanistan wars. {$action}. Sentenced to {$sentence}.",
                ], fn ($v) => $v !== null),
                'cases' => [[
                    'institution_id' => $bopVaried->id,
                    'charges' => 'Desertion / missing movement (UCMJ)',
                    'arrest_date' => $arrest, 'release_date' => $release,
                    'sentence' => $sentence,
                ]],
            ];
        }

        // Catholic Worker / Pentagon resistance / anti-drone organizers
        foreach ([
            ['Steve Jacobs', 'Steve', 'Jacobs', 'Male', 'Catholic Worker, Iowa', "Steve Jacobs is an Iowa Catholic Worker who has been arrested repeatedly at Whiteman Air Force Base and Strangers and Guests Catholic Worker drone protests."],
            ['Jim Dowling', 'Jim', 'Dowling', 'Male', 'Catholic Worker, Australia', "Jim Dowling is a longtime Brisbane Catholic Worker arrested at Pine Gap and Talisman Sabre military exercises in Australia for nonviolent civil disobedience against U.S./Australian war exercises."],
            ['Larry Purcell', 'Larry', 'Purcell', 'Male', 'Plowshares, Catholic Worker', "Larry Purcell is a longtime Catholic peace activist arrested in Plowshares-style actions at Lawrence Livermore National Laboratory and Vandenberg Air Force Base in California."],
            ['Megan Ramsey', 'Megan', 'Ramsey', 'Female', 'Catholic Worker, Pentagon resistance', "Megan Ramsey is a Catholic Worker arrested in Pentagon civil-disobedience actions and at U.S. military installations protesting U.S. drone warfare."],
            ['Paul Magno', 'Paul', 'Magno', 'Male', 'Pentagon resistance, Plowshares', "Paul Magno is a longtime Catholic Worker and Plowshares organizer in the Washington, D.C. area who has been arrested repeatedly at the Pentagon and at the White House for nonviolent anti-nuclear and anti-war civil disobedience."],
            ['Ed Kinane', 'Ed', 'Kinane', 'Male', 'Anti-drone, Voices for Creative Nonviolence', "Ed Kinane is an Upstate New York anti-war activist arrested repeatedly at Hancock Field Air National Guard Base in Syracuse for protesting Reaper drone operations."],
            ['John Heid', 'John', 'Heid', 'Male', 'Anti-drone', "John Heid is an anti-drone activist arrested at Beale Air Force Base for protesting unmanned aerial vehicle operations."],
            ['Robert Majors', 'Robert', 'Majors', 'Male', 'Anti-drone', "Robert Majors is an anti-drone activist arrested at U.S. drone bases."],
            ['Robert Smith', 'Robert', 'Smith', 'Male', 'Anti-drone', "Robert Smith was arrested at Beale Air Force Base for protesting unmanned aerial vehicle operations."],
            ['Alex Harrison', 'Alex', 'Harrison', 'Male', 'Recruiting station resistance', "Alex Harrison was arrested for civil disobedience at U.S. military recruiting stations during the Iraq War era."],
        ] as [$name, $first, $last, $gender, $aff, $desc]) {
            $defendants[] = [
                'data' => [
                    'name' => $name, 'first_name' => $first, 'last_name' => $last,
                    'gender' => $gender, 'race' => 'White', 'state' => 'United States', 'era' => '2010s',
                    'ideologies' => ['Anti-war', 'Pacifist'],
                    'affiliation' => [$aff],
                    'in_custody' => false, 'released' => true,
                    'description' => $desc,
                ],
                'cases' => [[
                    'institution_id' => $bopVaried->id,
                    'charges' => 'Trespass at U.S. military installations — anti-drone, anti-war civil disobedience',
                    'arrest_date' => '2014-01-01',
                    'sentence' => 'Multiple short sentences across several years of organizing',
                ]],
            ];
        }

        // ─── Round 6: from agent v3 crawl of static archive nr112-133 (1998-2003) ───

        $njFortDix         = Institution::firstOrCreate(['name' => 'FCI Fort Dix'], ['city' => 'Fort Dix', 'state' => 'New Jersey']);
        $netherlandsPrison = Institution::firstOrCreate(['name' => 'Netherlands Prison'], ['state' => 'Netherlands']);
        $rottenburgPrison  = Institution::firstOrCreate(['name' => 'Justizvollzugsanstalt Rottenburg'], ['city' => 'Rottenburg', 'state' => 'Germany']);
        $hmpGloucester     = Institution::firstOrCreate(['name' => 'HMP Gloucester'], ['city' => 'Gloucester', 'state' => 'United Kingdom']);
        $enfieldJailCT     = Institution::firstOrCreate(['name' => 'Carl Robinson Correctional Institution'], ['city' => 'Enfield', 'state' => 'Connecticut']);
        $nashuaJail        = Institution::firstOrCreate(['name' => 'Hillsborough County House of Corrections (Nashua)'], ['city' => 'Nashua', 'state' => 'New Hampshire']);
        $ashlandWIJail     = Institution::firstOrCreate(['name' => 'Ashland County Jail'], ['city' => 'Ashland', 'state' => 'Wisconsin']);
        $rockyMountainArea = Institution::firstOrCreate(['name' => 'Federal Court (Rocky Flats prosecution)']);

        // Vieques 2002-2003 mass arrests (with concrete sentences from nr128/nr133)
        foreach ([
            ['Mariel Torres Lara', 'Mariel', 'Torres Lara', 'Female', '1 week', '2003-01-15', '2003-01-22', null, 'MST brigade occupation of Vieques bombing range'],
            ['Ricardo Santos Ortiz', 'Ricardo', 'Santos Ortiz', 'Male', '1 week', '2003-01-15', '2003-01-22', null, 'MST brigade occupation of Vieques bombing range'],
            ['Andrés Santos Ortiz', 'Andrés', 'Santos Ortiz', 'Male', '1 week', '2003-01-15', '2003-01-22', null, 'MST brigade occupation of Vieques bombing range'],
            ['Angel Quiles', 'Angel', 'Quiles', 'Male', '60 days', '2003-01-14', '2003-03-15', null, 'Pablo Soto Brigade trespass on Navy bombing range'],
            ['Javier Sterling', 'Javier', 'Sterling', 'Male', '60 days', '2003-01-14', '2003-03-15', null, 'Pablo Soto Brigade trespass on Navy bombing range (university student)'],
            ['Luis A. Amely Martínez', 'Luis', 'Amely Martínez', 'Male', '7-9 days', '2003-01-18', '2003-01-27', null, 'PR Independence Party brigade trespass on Vieques'],
            ['Francisco Bartolomei Crespo', 'Francisco', 'Bartolomei Crespo', 'Male', '7-9 days', '2003-01-18', '2003-01-27', null, 'PR Independence Party brigade trespass on Vieques'],
            ['Edwin Vargas Becerril', 'Edwin', 'Vargas Becerril', 'Male', '7-9 days', '2003-01-18', '2003-01-27', null, 'PR Independence Party brigade trespass on Vieques'],
            ['Abraham Ayala Ortiz', 'Abraham', 'Ayala Ortiz', 'Male', '7-9 days', '2003-01-18', '2003-01-27', null, 'PR Independence Party brigade trespass on Vieques'],
            ['Angel Rubén Santiago Aponte', 'Angel', 'Santiago Aponte', 'Male', '7-9 days', '2003-01-18', '2003-01-27', null, 'PR Independence Party brigade trespass on Vieques'],
            ['Gustavo Dávila Cardona', 'Gustavo', 'Dávila Cardona', 'Male', '40 days', '2003-01-22', '2003-03-03', null, 'Minerva Bermúdez / MLK brigade trespass on Vieques range'],
            ['Leonardo Estrada Ferrer', 'Leonardo', 'Estrada Ferrer', 'Male', '60 days', '2003-01-22', '2003-03-23', null, 'Minerva Bermúdez / MLK brigade trespass on Vieques range'],
            ['Orlando Soto Morales', 'Orlando', 'Soto Morales', 'Male', '40 days', '2003-01-22', '2003-03-03', null, 'Minerva Bermúdez / MLK brigade trespass on Vieques range'],
            ['Grego Marcano Vázquez', 'Grego', 'Marcano Vázquez', 'Male', '60 days', '2003-01-22', '2003-03-23', null, 'Minerva Bermúdez / MLK brigade trespass on Vieques range'],
            ['Néstor de Jesús', 'Néstor', 'de Jesús', 'Male', '30 days', '2003-01-23', '2003-02-22', null, 'MST second brigade trespass on Vieques range'],
            ['Martín Castro Avila', 'Martín', 'Castro Avila', 'Male', '30 days', '2003-01-23', '2003-02-22', null, 'MST second brigade trespass on Vieques range'],
            ['César Pacheco Rodríguez', 'César', 'Pacheco Rodríguez', 'Male', '30 days', '2003-01-23', '2003-02-22', null, 'MST second brigade trespass on Vieques range'],
            ['Cacimar Zenón', 'Cacimar', 'Zenón', 'Male', '4 months (free on bond, appealing)', '2002-04-15', '2002-08-15', null, 'Vieques Zenón fishing family resistance'],
            ['Regalado Miró', 'Regalado', 'Miró', 'Male', '4 months (free on bond, appealing)', '2002-04-15', '2002-08-15', null, 'Resistance activity on Vieques'],
            ['Luis Angel Torres', 'Luis', 'Torres', 'Male', '1 week', '2003-01-08', '2003-01-15', null, 'MST brigade member, Vieques'],
            ['Pedro Colón Almenas', 'Pedro', 'Colón Almenas', 'Male', '1 year + 3 years probation', '2001-04-30', '2003-02-22', '22192-069', 'Aggravated assault on ROTC official at University of Puerto Rico Río Piedras anti-ROTC protest'],
            ['María Elena Negrón', 'María', 'Negrón', 'Female', '30 days', '2002-04-01', '2002-05-01', '21690-069', 'Trespass on Vieques bombing range'],
            ['Juan Ramón Cruz Pérez', 'Juan', 'Cruz Pérez', 'Male', '20 days', '2002-04-04', '2002-04-24', '21692-069', 'Trespass on Vieques bombing range'],
            ['Humberto Núñez', 'Humberto', 'Núñez', 'Male', 'denied bond, awaiting trial', '2002-04-10', null, '21697-069', 'Trespass on Vieques bombing range'],
            ['Tania Hernández Carrión', 'Tania', 'Hernández Carrión', 'Female', 'denied bond, awaiting trial', '2002-04-12', null, '19505-069', 'Trespass on Vieques bombing range'],
            ['Carlos Zenón', 'Carlos', 'Zenón', 'Male', '6 months', '2002-01-15', '2002-07-15', '23214-069', 'Vieques boating actions to stop bombing of inhabited island'],
            ['Yabureibo Zenón', 'Yabureibo', 'Zenón', 'Male', '6 months', '2002-01-15', '2002-07-15', '23213-069', 'Vieques boating actions to stop bombing of inhabited island'],
            ['Andy Rivera', 'Andy', 'Rivera', 'Male', 'denied bond, awaiting trial', '2002-04-10', null, null, 'Trespass on Vieques bombing range'],
        ] as $row) {
            [$name, $first, $last, $gender, $sentence, $arrest, $release, $bopId, $action] = $row;
            $defendants[] = [
                'data' => array_filter([
                    'name' => $name, 'first_name' => $first, 'last_name' => $last,
                    'gender' => $gender, 'race' => 'Hispanic', 'state' => 'Puerto Rico', 'era' => '2000s',
                    'ideologies' => ['Anti-colonial', 'Anti-war', 'Puerto Rican independence'],
                    'affiliation' => ['Vieques resistance'], 'in_custody' => false, 'released' => true,
                    'inmate_number' => $bopId,
                    'description' => "{$name} was arrested for {$action} as part of the sustained 1999-2003 civil-disobedience campaign that forced the U.S. Navy to withdraw from the inhabited island of Vieques in May 2003. Sentenced to {$sentence}.\n\n{$viequesContext}",
                ], fn ($v) => $v !== null),
                'cases' => [[
                    'institution_id' => $prSanJuan->id,
                    'charges' => "Vieques resistance - {$action}",
                    'arrest_date' => $arrest, 'release_date' => $release,
                    'sentence' => $sentence,
                ]],
            ];
        }

        // SOA Watch 2002-2003 additional named defendants
        foreach ([
            ['Patrick Lincoln', 'Patrick', null, 'Lincoln', 'Male', '6 months', '2002-11-17', 'Virginia Tech student, age 21'],
            ['Lee Mickey', 'Lee', null, 'Mickey', 'Female', '30 days', '2002-11-17', '67-year-old caring for ill sister'],
            ['Eloy Garcia', 'Eloy', null, 'Garcia', 'Male', '3 months', '2002-11-17', null],
            ['Scott Schaeffer-Duffy', 'Scott', null, 'Schaeffer-Duffy', 'Male', '3 months', '2002-11-17', 'Catholic Worker (Worcester MA)'],
            ['Rachel Montgomery', 'Rachel', null, 'Montgomery', 'Female', '6 months', '2002-11-17', null],
            ['Judith Kelly', 'Judith', null, 'Kelly', 'Female', '6 months', '2002-11-17', null],
            ['Mimi Lavalley', 'Mimi', null, 'Lavalley', 'Female', '6 months', '2002-11-17', null],
            ['Carey Martin', 'Carey', null, 'Martin', 'Male', '6 months', '2002-11-17', null],
            ['J.C. Orton', 'J.C.', null, 'Orton', 'Male', '6 months', '2002-11-17', null],
            ['Laura Slattery', 'Laura', null, 'Slattery', 'Female', '6 months', '2002-11-17', null],
            ['Corbin Street', 'Corbin', null, 'Street', 'Male', '6 months', '2002-11-17', null],
            ['Mike Wisniewski', 'Mike', null, 'Wisniewski', 'Male', '6 months', '2002-11-17', null],
            ['Sonja Andreas', 'Sonja', null, 'Andreas', 'Female', '6 months', '2002-11-17', null],
            ['Cliff Frazier', 'Cliff', null, 'Frazier', 'Male', '6 months', '2002-11-17', null],
            ['Derrlyn Tom', 'Derrlyn', null, 'Tom', 'Female', '6 months', '2002-11-17', null],
            ['Bud Combs', 'Bud', null, 'Combs', 'Male', '3 months', '2002-11-17', null],
            ['Bruce Triggs', 'Bruce', null, 'Triggs', 'Male', '6 months', '2000-03-01', 'Reentry trespass at Fort Benning planting crosses'],
            ['Sister Mary Dennis Lentsch', 'Mary Dennis', null, 'Lentsch', 'Female', '6 months', '2000-03-01', 'Catholic nun'],
            ['Sister Kathleen McCabe', 'Kathleen', null, 'McCabe', 'Female', '6 months', '2000-03-01', 'Catholic nun'],
            ['Kathleen Fisher', 'Kathleen', null, 'Fisher', 'Female', '6 months', '1999-11-01', 'Repeat trespass at Fort Benning'],
        ] as $row) {
            [$name, $first, $middle, $last, $gender, $sentence, $arrest, $note] = $row;
            $bio = "{$name} crossed the line at Fort Benning, Georgia during an SOA Watch vigil. Sentenced to {$sentence}.";
            if ($note) $bio .= " {$note}.";
            $defendants[] = [
                'data' => array_filter([
                    'name' => $name, 'first_name' => $first, 'middle_name' => $middle, 'last_name' => $last,
                    'gender' => $gender, 'race' => 'White', 'state' => 'Georgia', 'era' => '2000s',
                    'ideologies' => ['Anti-war', 'Latin America solidarity'],
                    'affiliation' => ['SOA Watch'], 'in_custody' => false, 'released' => true,
                    'description' => $bio . "\n\n{$soaContextLong}",
                ], fn ($v) => $v !== null),
                'cases' => [[
                    'institution_id' => $ftMooreFCI->id,
                    'charges' => 'Trespass at U.S. Army School of the Americas, Fort Benning',
                    'arrest_date' => $arrest, 'sentence' => $sentence,
                ]],
            ];
        }

        // Bill Frankel-Streit (Pentagon Holy Innocents)
        $defendants[] = [
            'data' => [
                'name' => 'Bill Frankel-Streit', 'first_name' => 'Bill', 'last_name' => 'Frankel-Streit',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Virginia', 'era' => '2000s',
                'ideologies' => ['Anti-war', 'Pacifist', 'Catholic Worker'],
                'affiliation' => ['Atlantic Life Community'],
                'inmate_number' => '03809-052',
                'in_custody' => false, 'released' => true,
                'description' => "Bill Frankel-Streit was an Atlantic Life Community / Catholic Worker activist who poured human blood on the Pentagon entrance during the Holy Innocents witness on December 30, 2002, on the eve of the U.S. invasion of Iraq. Sentenced to 6 months in federal prison.",
            ],
            'cases' => [[
                'institution_id' => $njFortDix->id,
                'charges' => 'Damage to government property - Holy Innocents witness, blood-pouring at Pentagon entrance',
                'arrest_date' => '2002-12-30', 'incarceration_date' => '2003-04-15', 'release_date' => '2003-10-15',
                'sentence' => '6 months federal prison',
            ]],
        ];

        // Ed Bloomer
        $defendants[] = [
            'data' => [
                'name' => 'Ed Bloomer', 'first_name' => 'Ed', 'last_name' => 'Bloomer',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Iowa', 'era' => '2000s',
                'ideologies' => ['Anti-nuclear', 'Pacifist', 'Catholic Worker'],
                'affiliation' => ['Des Moines Catholic Worker'],
                'in_custody' => false, 'released' => true,
                'description' => "Ed Bloomer was a Des Moines Catholic Worker arrested for re-entry trespass at Offutt Air Force Base / U.S. Strategic Command on Nagasaki Day (August 9, 2001). Sentenced to 30 days plus a fine and one year probation in March 2002.",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Re-entry trespass at Offutt AFB / U.S. Strategic Command - Nagasaki Day witness',
                'arrest_date' => '2001-08-09', 'incarceration_date' => '2002-03-06', 'release_date' => '2002-04-05',
                'sentence' => '30 days + fine + 1 year probation',
            ]],
        ];

        // RAF Fairford UK B-52 actions
        foreach ([
            ['Margaret Jones', 'Margaret', 'Jones', 'Female', 'Disabled 30 bomber support vehicles at RAF Fairford on the eve of the Iraq invasion'],
            ['Paul Arthur Milling', 'Paul', 'Milling', 'Male', 'Disabled 30 bomber support vehicles at RAF Fairford with Margaret Jones'],
            ['Josh Richards', 'Josh', 'Richards', 'Male', 'Possessed explosive substance with intent to endanger life and damaged base fence at RAF Fairford'],
        ] as [$name, $first, $last, $gender, $action]) {
            $defendants[] = [
                'data' => [
                    'name' => $name, 'first_name' => $first, 'last_name' => $last,
                    'gender' => $gender, 'state' => 'United Kingdom', 'era' => '2000s',
                    'ideologies' => ['Anti-war', 'Anti-nuclear', 'Pacifist'],
                    'affiliation' => ['Trident Ploughshares', 'Anti-Iraq-war UK'],
                    'in_custody' => false, 'released' => true,
                    'description' => "{$name} was a UK anti-war / Plowshares activist who acted at RAF Fairford in Gloucestershire on the eve of the U.S./UK invasion of Iraq, March 2003. {$action}. Held at HMP Gloucester pending trial.",
                ],
                'cases' => [[
                    'institution_id' => $hmpGloucester->id,
                    'charges' => 'Conspiracy to commit criminal damage at RAF Fairford (US B-52 base)',
                    'arrest_date' => '2003-03-13', 'release_date' => '2003-12-31',
                    'sentence' => 'Held in pretrial detention; multiple subsequent trials',
                ]],
            ];
        }

        // Mary Kelly - Shannon Airport
        $defendants[] = [
            'data' => [
                'name' => 'Mary Kelly', 'first_name' => 'Mary', 'last_name' => 'Kelly',
                'gender' => 'Female', 'state' => 'Ireland', 'era' => '2000s',
                'ideologies' => ['Anti-war', 'Pacifist'],
                'affiliation' => ['Anti-Iraq-war Ireland'],
                'in_custody' => false, 'released' => true,
                'description' => "Mary Kelly was an Irish anti-war activist who hammered a U.S. Navy weapons / troop transport aircraft at Shannon Airport, Ireland, on January 29, 2003 - one of the earliest direct-action interventions in the long-running Shannon Airport US warplane transit campaign. Refused bail and held at Limerick Prison until March 2003.",
            ],
            'cases' => [[
                'institution_id' => $limerick->id,
                'charges' => 'Damage to U.S. Navy aircraft at Shannon Airport, Ireland',
                'arrest_date' => '2003-01-29', 'release_date' => '2003-03-15',
                'sentence' => 'Approximately 6 weeks pretrial detention',
            ]],
        ];

        // Jeff Dietrich - LA Catholic Worker
        $defendants[] = [
            'data' => [
                'name' => 'Jeff Dietrich', 'first_name' => 'Jeff', 'last_name' => 'Dietrich',
                'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'era' => '2000s',
                'ideologies' => ['Anti-war', 'Pacifist', 'Catholic Worker'],
                'affiliation' => ['Los Angeles Catholic Worker'],
                'in_custody' => false, 'released' => true,
                'description' => "Jeff Dietrich is a longtime Los Angeles Catholic Worker and editor of the Catholic Agitator. In March 2003 - on the eve of the U.S. invasion of Iraq - he was arrested at the downtown LA federal building and at the Ash Wednesday blockade. Sentenced to 45 days at MDC-Los Angeles.",
            ],
            'cases' => [[
                'institution_id' => $laCountyJail->id,
                'charges' => 'Federal trespass and Ash Wednesday blockade of LA federal building',
                'arrest_date' => '2003-03-12', 'release_date' => '2003-05-01',
                'sentence' => '45 days',
            ]],
        ];

        // Project ELF 2002-2003
        foreach ([
            ['John Bachman', 'John', 'Bachman', 'Male', '6 months unsupervised probation + 85 hrs community service', '2002-05-15', '2002-11-15', 'Attorney; trespass at Project ELF'],
            ['Jeff Leys', 'Jeff', 'Leys', 'Male', '60 days', '2002-05-15', '2003-04-15', 'Trespass at Project ELF; jailed after refusing community service'],
            ['Scott Mathern-Jacobson', 'Scott', 'Mathern-Jacobson', 'Male', '9 days', '2002-05-15', '2003-01-24', 'Project ELF trespass; jailed for nonpayment of fine'],
        ] as [$name, $first, $last, $gender, $sentence, $arrest, $release, $action]) {
            $defendants[] = [
                'data' => [
                    'name' => $name, 'first_name' => $first, 'last_name' => $last,
                    'gender' => $gender, 'race' => 'White', 'state' => 'Wisconsin', 'era' => '2000s',
                    'ideologies' => ['Anti-nuclear', 'Pacifist'],
                    'affiliation' => ['Project ELF resistance'],
                    'in_custody' => false, 'released' => true,
                    'description' => "{$name} was arrested for trespass at Project ELF, the U.S. Navy's extremely-low-frequency submarine command system. {$action}. Sentenced to {$sentence}.",
                ],
                'cases' => [[
                    'institution_id' => $ashlandWIJail->id,
                    'charges' => 'Trespass at Project ELF (U.S. Navy ELF submarine command system)',
                    'arrest_date' => $arrest, 'release_date' => $release,
                    'sentence' => $sentence,
                ]],
            ];
        }

        // UK Trident Ploughshares (US D-5 missiles)
        $tridentContext = "Trident Ploughshares is the UK direct-disarmament campaign founded in 1998 to nonviolently dismantle the UK's Trident nuclear weapons system. Trident submarines are based at HMNB Clyde (Faslane) and HMNB Coulport, equipped with Trident D-5 missiles leased from the U.S. Navy and warheads built at AWE Aldermaston with U.S. design assistance - making the system a direct extension of U.S. nuclear posture in the North Atlantic.";

        foreach ([
            ['Angie Zelter', 'Angie', 'Zelter', 'Female', 'Multiple short jail terms', '1998-06-15', '2003-12-31', 'Trident Ploughshares co-founder; Loch Goil Plowshares Trident Three acquittal 1999'],
            ['Ellen Moxley', 'Ellen', 'Moxley', 'Female', 'Held on remand; acquitted 1999', '1999-06-08', '1999-12-15', 'Loch Goil Plowshares - disarmed Maytime sonar test lab'],
            ['Rosie James', 'Rosie', 'James', 'Female', 'Multiple mistrials', '1999-02-10', '2001-04-30', 'Aldermaston Women Trash Trident - hammered HMS Vengeance'],
            ['Rachel Wenham', 'Rachel', 'Wenham', 'Female', 'Multiple mistrials', '1999-02-10', '2001-04-30', 'Aldermaston Women Trash Trident - hammered HMS Vengeance'],
            ['Joan Meredith', 'Joan', 'Meredith', 'Female', 'Cautioned, fined £50 (refused), held briefly', '1999-08-09', '2000-11-15', 'Northumbrian TP2000 fence-cutting at Albemarle'],
            ['Joy Mitchell', 'Joy', 'Mitchell', 'Female', '7 days', '2000-11-15', '2000-11-22', 'Coulport demo (TP2000); retired teacher'],
            ['Ian Thompson', 'Ian', 'Thompson', 'Male', '12 days pretrial + 7 days for unpaid fine', '1999-08-09', '2000-12-31', 'Faslane / Coulport fence-cutting'],
            ['Brian Quail', 'Brian', 'Quail', 'Male', 'Brief detention', '1999-04-15', '1999-05-01', 'Joint Secretary, Scottish CND; blockading nuclear weapons convoy'],
            ['Marjan Willemsen', 'Marjan', 'Willemsen', 'Female', '4 days + several days for fine refusal', '1999-08-09', '2000-11-15', 'Cut Faslane base fence and climbed light tower (Dutch)'],
            ['Zoe Weir', 'Zoe', 'Weir', 'Female', '7 days', '2000-10-25', '2000-11-22', 'Cut Faslane base fence and climbed light tower'],
            ['Tommy Sheridan', 'Tommy', 'Sheridan', 'Male', '5 days', '2000-12-17', '2000-12-22', 'Member of Scottish Parliament jailed for unpaid Faslane fine'],
            ['Clive Fudge', 'Clive', 'Fudge', 'Male', '7 days', '2000-12-15', '2000-12-22', 'Coulport blockade; jailed for unpaid fine'],
            ['Rupert Eris', 'Rupert', 'Eris', 'Male', '~10 days', '1998-11-01', '1998-11-11', 'Cut into nuclear weapons store at Coulport'],
            ['Stellan Vinthagen', 'Stellan', 'Vinthagen', 'Male', '6+ months pretrial; sentenced to time served', '1998-09-14', '1999-10-15', 'Bread Not Bombs Plowshares - HMS Vengeance (Swedish)'],
            ['Ann-Britt Sternfeldt', 'Ann-Britt', 'Sternfeldt', 'Female', '6+ months pretrial', '1998-09-14', '1999-10-15', 'Bread Not Bombs Plowshares - Barrow-in-Furness (Swedish)'],
        ] as [$name, $first, $last, $gender, $sentence, $arrest, $release, $action]) {
            $defendants[] = [
                'data' => [
                    'name' => $name, 'first_name' => $first, 'last_name' => $last,
                    'gender' => $gender, 'state' => 'United Kingdom', 'era' => '2000s',
                    'ideologies' => ['Anti-nuclear', 'Pacifist'],
                    'affiliation' => ['Trident Ploughshares'],
                    'in_custody' => false, 'released' => true,
                    'description' => "{$name} was a Trident Ploughshares activist. {$action}. Sentenced to {$sentence}.\n\n{$tridentContext}",
                ],
                'cases' => [[
                    'institution_id' => $ukFaslane->id,
                    'charges' => 'Criminal damage / trespass at UK Trident installations',
                    'arrest_date' => $arrest, 'release_date' => $release,
                    'sentence' => $sentence,
                ]],
            ];
        }

        // Volkel/Kleine Brogel
        $defendants[] = [
            'data' => [
                'name' => 'Barbara Smedema', 'first_name' => 'Barbara', 'last_name' => 'Smedema',
                'gender' => 'Female', 'state' => 'Netherlands', 'era' => '2000s',
                'ideologies' => ['Anti-nuclear', 'Pacifist', 'Plowshares'],
                'affiliation' => ['Plowshares (Dutch)'],
                'in_custody' => false, 'released' => true,
                'description' => "Barbara Smedema is a Dutch Plowshares activist who hammered three dish antennas controlling U.S. nuclear weapons at Volkel Air Base in the Netherlands on February 9, 2003. Volkel hosts approximately 20 U.S. B61 nuclear bombs as part of NATO nuclear sharing.",
            ],
            'cases' => [[
                'institution_id' => $netherlandsPrison->id,
                'charges' => 'Criminal damage to U.S. nuclear weapons control infrastructure at Volkel Air Base',
                'arrest_date' => '2003-02-09', 'release_date' => '2003-04-17',
                'sentence' => 'Approximately 2 months custody',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'David Heller', 'first_name' => 'David', 'last_name' => 'Heller',
                'gender' => 'Male', 'state' => 'United Kingdom', 'era' => '2000s',
                'ideologies' => ['Anti-nuclear', 'Pacifist'],
                'affiliation' => ['Citizens Inspection'], 'in_custody' => false, 'released' => true,
                'description' => "David Heller was a UK anti-nuclear activist arrested for citizen weapons inspection at Volkel Air Force Base, Netherlands (US B61 nuclear bombs). Sentenced to 7 days and deported to the UK.",
            ],
            'cases' => [[
                'institution_id' => $netherlandsPrison->id,
                'charges' => 'Citizen weapons inspection at Volkel AFB',
                'arrest_date' => '2000-12-27', 'release_date' => '2001-01-03',
                'sentence' => '7 days + deportation',
            ]],
        ];

        $defendants[] = [
            'data' => [
                'name' => 'Tooker Gomberg', 'first_name' => 'Tooker', 'last_name' => 'Gomberg',
                'gender' => 'Male', 'state' => 'Canada', 'era' => '2000s',
                'death_date' => '2004-03-03',
                'ideologies' => ['Anti-nuclear', 'Environmental', 'Pacifist'],
                'affiliation' => ['Citizens Inspection'], 'in_custody' => false, 'released' => true,
                'description' => "Tooker Gomberg was a Canadian environmental and anti-nuclear activist arrested for citizen weapons inspection at Volkel Air Force Base, Netherlands in December 2000. Sentenced to 7 days. Died in 2004.",
            ],
            'cases' => [[
                'institution_id' => $netherlandsPrison->id,
                'charges' => 'Citizen weapons inspection at Volkel AFB, Netherlands',
                'arrest_date' => '2000-12-27', 'release_date' => '2001-01-03',
                'sentence' => '7 days + deportation',
            ]],
        ];

        // Lieberman Five 1999
        $liebermanContext = "On July 2, 1999, the Lieberman Five - Hartford Catholic Workers - held a sit-in at Senator Joseph Lieberman's Hartford office to protest U.S. sanctions on Iraq, ringing a bell every twelve minutes for an Iraqi child who had died from sanctions-related malnutrition. Most received 30-day jail sentences served at the Carl Robinson Correctional Institution in Enfield, Connecticut.";

        foreach ([
            ['Brian Kavanagh', 'Brian', 'Kavanagh'],
            ['Cal Robertson', 'Cal', 'Robertson'],
            ['Hillel Arnold', 'Hillel', 'Arnold'],
            ['Jim Noonan', 'Jim', 'Noonan'],
        ] as [$name, $first, $last]) {
            $defendants[] = [
                'data' => [
                    'name' => $name, 'first_name' => $first, 'last_name' => $last,
                    'gender' => 'Male', 'race' => 'White', 'state' => 'Connecticut', 'era' => '1990s',
                    'ideologies' => ['Anti-war', 'Pacifist', 'Catholic Worker'],
                    'affiliation' => ['Hartford Catholic Worker', 'Lieberman Five'],
                    'in_custody' => false, 'released' => true,
                    'description' => "{$name} was a Hartford Catholic Worker arrested in the July 2, 1999 sit-in at Senator Joseph Lieberman's Hartford office to protest U.S. sanctions on Iraq. Sentenced to 30 days.\n\n{$liebermanContext}",
                ],
                'cases' => [[
                    'institution_id' => $enfieldJailCT->id,
                    'charges' => "Trespass / sit-in at Senator Lieberman's Hartford office",
                    'arrest_date' => '1999-07-02', 'incarceration_date' => '1999-11-15', 'release_date' => '1999-12-15',
                    'sentence' => '30 days',
                ]],
            ];
        }

        // Citizen Weapons Inspectors at Johns Hopkins APL
        foreach ([
            ['Jen Kipka', 'Jen', 'Kipka', 'Female', '5 days'],
            ['Max Obuszewski', 'Max', 'Obuszewski', 'Male', '10 days'],
        ] as [$name, $first, $last, $gender, $sentence]) {
            $defendants[] = [
                'data' => [
                    'name' => $name, 'first_name' => $first, 'last_name' => $last,
                    'gender' => $gender, 'race' => 'White', 'state' => 'Maryland', 'era' => '1990s',
                    'ideologies' => ['Anti-nuclear', 'Pacifist'],
                    'affiliation' => ['Baltimore Emergency Response Network'],
                    'in_custody' => false, 'released' => true,
                    'description' => "{$name} was arrested in a Citizen Weapons Inspection at Johns Hopkins University Applied Physics Laboratory in Baltimore on March 1, 1999. Sentenced to {$sentence}.",
                ],
                'cases' => [[
                    'institution_id' => $bopVaried->id,
                    'charges' => 'Citizen Weapons Inspection at Johns Hopkins APL',
                    'arrest_date' => '1999-03-01', 'sentence' => $sentence,
                ]],
            ];
        }

        // Mary Alice Shemo
        $defendants[] = [
            'data' => [
                'name' => 'Mary Alice Shemo', 'first_name' => 'Mary Alice', 'last_name' => 'Shemo',
                'gender' => 'Female', 'race' => 'White', 'state' => 'Wisconsin', 'era' => '1990s',
                'ideologies' => ['Anti-nuclear', 'Pacifist'],
                'affiliation' => null, 'in_custody' => false, 'released' => true,
                'description' => "Mary Alice Shemo was arrested for King Day trespass at Project ELF on January 17, 1999. Sentenced to 9 days in jail.",
            ],
            'cases' => [[
                'institution_id' => $ashlandWIJail->id,
                'charges' => 'Trespass at Project ELF on Martin Luther King Day',
                'arrest_date' => '1999-01-17', 'incarceration_date' => '1999-05-11', 'release_date' => '1999-05-20',
                'sentence' => '9 days',
            ]],
        ];

        // Operation Desert Fox protests Syracuse 1998
        foreach ([
            ['Jerry Berrigan', 'Jerry', 'Berrigan', 'Male', 'Berrigan family elder; uncle of Daniel and Philip'],
            ['Dick Keough', 'Dick', 'Keough', 'Male', null],
            ['Audrey Stewart', 'Audrey', 'Stewart', 'Female', null],
        ] as [$name, $first, $last, $gender, $note]) {
            $bio = "{$name} painted anti-Iraq-war messages on the Syracuse federal building during Operation Desert Fox (December 1998 U.S./UK bombing of Iraq). " . ($note ? $note . '. ' : '') . "Charged with felony plus misdemeanors.";
            $defendants[] = [
                'data' => [
                    'name' => $name, 'first_name' => $first, 'last_name' => $last,
                    'gender' => $gender, 'race' => 'White', 'state' => 'New York', 'era' => '1990s',
                    'ideologies' => ['Anti-war', 'Pacifist', 'Catholic Worker'],
                    'affiliation' => ['Syracuse Catholic Worker'],
                    'in_custody' => false, 'released' => true,
                    'description' => $bio,
                ],
                'cases' => [[
                    'institution_id' => $bopVaried->id,
                    'charges' => 'Property damage to federal building during anti-Iraq-war witness',
                    'arrest_date' => '1998-12-16', 'release_date' => '1999-09-15',
                    'sentence' => 'Felony + misdemeanor convictions',
                ]],
            ];
        }

        // Wolfgang Sternstein - US European Command HQ
        $defendants[] = [
            'data' => [
                'name' => 'Dr. Wolfgang Sternstein', 'first_name' => 'Wolfgang', 'last_name' => 'Sternstein',
                'gender' => 'Male', 'state' => 'Germany', 'era' => '1990s',
                'ideologies' => ['Anti-nuclear', 'Pacifist', 'Plowshares'],
                'affiliation' => ['EUCOMmunity', 'German Plowshares'],
                'in_custody' => false, 'released' => true,
                'description' => "Dr. Wolfgang Sternstein was a German Plowshares activist who in 1998 cut the perimeter fence at U.S. European Command Center (EUCOM) headquarters in Stuttgart, Germany - the operational command for U.S. military forces in Europe and a base hosting more than 150 U.S. atomic weapons. Sentenced to 140 days at Justizvollzugsanstalt Rottenburg, beginning November 22, 1999.",
            ],
            'cases' => [[
                'institution_id' => $rottenburgPrison->id,
                'charges' => 'Cutting perimeter fence at U.S. European Command HQ, Stuttgart',
                'arrest_date' => '1998-08-01', 'incarceration_date' => '1999-11-22', 'release_date' => '2000-04-10',
                'sentence' => '140 days German prison',
            ]],
        ];

        // Allison Hunter - Rocky Flats
        $defendants[] = [
            'data' => [
                'name' => 'Allison Hunter', 'first_name' => 'Allison', 'last_name' => 'Hunter',
                'gender' => 'Female', 'race' => 'White', 'state' => 'Colorado', 'era' => '1970s',
                'death_date' => '1999-09-24',
                'ideologies' => ['Anti-nuclear', 'Pacifist'],
                'affiliation' => null, 'in_custody' => false, 'released' => true,
                'description' => "Allison (Allyson) Hunter was a Seattle-based anti-nuclear activist arrested repeatedly at the Rocky Flats Plant outside Denver, Colorado - where U.S. plutonium pits for nuclear weapons were manufactured - in 1978. A foundational figure in the Pacific Northwest anti-nuclear movement. Died in 1999.",
            ],
            'cases' => [[
                'institution_id' => $rockyMountainArea->id,
                'charges' => 'Multiple trespass arrests at Rocky Flats Plant',
                'arrest_date' => '1978-04-29', 'release_date' => '1980-12-31',
                'sentence' => 'Months of cumulative jail time',
            ]],
        ];

        // Larry Morlan - Gods of Metal Plowshares
        $defendants[] = [
            'data' => [
                'name' => 'Father Larry Morlan', 'first_name' => 'Larry', 'last_name' => 'Morlan',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Maryland', 'era' => '1990s',
                'ideologies' => ['Anti-nuclear', 'Pacifist', 'Catholic'],
                'affiliation' => ['Plowshares movement', 'Diocese of Peoria'],
                'in_custody' => false, 'released' => true,
                'description' => "Father Larry Morlan was a Catholic priest of the Diocese of Peoria, Illinois who participated in the Gods of Metal Plowshares action at Andrews Air Force Base Open House in May 1998 - hammering on a B-52 bomber. Sentenced to 4 months in federal prison on January 4, 1999.",
            ],
            'cases' => [[
                'institution_id' => $bopVaried->id,
                'charges' => 'Damage to B-52 bomber at Andrews AFB - Gods of Metal Plowshares',
                'arrest_date' => '1998-05-17', 'incarceration_date' => '1999-01-04', 'release_date' => '1999-05-04',
                'sentence' => '4 months federal prison',
            ]],
        ];

        // NH Peace Action - British Aerospace Nashua
        foreach ([
            ['Ruth McKay', 'Ruth', 'McKay', '10 days', 'Age 81; refused $260 fine'],
            ['Hattie Nestle', 'Hattie', 'Nestle', '6 days', 'Refused fine'],
            ['Dade Singapuri', 'Dade', 'Singapuri', '6 days', null],
        ] as [$name, $first, $last, $sentence, $note]) {
            $bio = "{$name} was arrested December 4, 2000 for trespass / disorderly conduct at the British Aerospace plant in Nashua, New Hampshire during the long-running weekly anti-war vigil. Sentenced to {$sentence}." . ($note ? " {$note}." : '');
            $defendants[] = [
                'data' => [
                    'name' => $name, 'first_name' => $first, 'last_name' => $last,
                    'gender' => 'Female', 'race' => 'White', 'state' => 'New Hampshire', 'era' => '2000s',
                    'ideologies' => ['Anti-war', 'Pacifist'],
                    'affiliation' => ['New Hampshire Peace Action'],
                    'in_custody' => false, 'released' => true,
                    'description' => $bio,
                ],
                'cases' => [[
                    'institution_id' => $nashuaJail->id,
                    'charges' => 'Trespass / disorderly conduct at British Aerospace plant Nashua',
                    'arrest_date' => '2000-12-04', 'sentence' => $sentence,
                ]],
            ];
        }

        // Anni Rainbow - UK Menwith Hill
        $defendants[] = [
            'data' => [
                'name' => 'Anni Rainbow', 'first_name' => 'Anni', 'last_name' => 'Rainbow',
                'gender' => 'Female', 'state' => 'United Kingdom', 'era' => '2000s',
                'death_date' => '2022-04-29',
                'ideologies' => ['Anti-nuclear', 'Pacifist'],
                'affiliation' => ['Campaign for the Accountability of American Bases (CAAB)'],
                'in_custody' => false, 'released' => true,
                'description' => "Anni Rainbow was a UK anti-nuclear and anti-base activist with CAAB. Repeatedly arrested at NSA Menwith Hill (largest U.S. signals-intelligence facility outside the United States) and at USAF Fylingdales in Yorkshire. Died 2022.",
            ],
            'cases' => [[
                'institution_id' => $ukPrison->id,
                'charges' => 'Aggravated trespass at NSA Menwith Hill and USAF Fylingdales',
                'arrest_date' => '2002-02-26', 'release_date' => '2002-02-27',
                'sentence' => 'Multiple short jail terms; jailed overnight',
            ]],
        ];


        // Process all defendants ───
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
