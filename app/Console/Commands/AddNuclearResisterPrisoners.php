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

        // ─── Process all defendants ───
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
