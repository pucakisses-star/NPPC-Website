<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddPeaceActivists extends Command
{
    protected $signature = 'prisoners:add-peace-activists';
    protected $description = 'Add 12 US peace and civil-rights activists from the Wikipedia list of peace activists who served real prison time.';

    public function handle(): int
    {
        $created = 0;
        $skipped = 0;

        $civilRights = Institution::firstOrCreate(['name' => 'Various Southern jails (civil-rights organizing arrests)'], ['city' => null, 'state' => null]);
        $monterey   = Institution::firstOrCreate(['name' => 'Monterey County Jail (Salinas)'], ['city' => 'Salinas', 'state' => 'California']);
        $lexington  = Institution::firstOrCreate(['name' => 'Federal Medical Center, Lexington (women\'s)'], ['city' => 'Lexington', 'state' => 'Kentucky']);
        $marianna   = Institution::firstOrCreate(['name' => 'FCI Marianna'], ['city' => 'Marianna', 'state' => 'Florida']);
        $brooklyn   = Institution::firstOrCreate(['name' => 'Metropolitan Detention Center, Brooklyn'], ['city' => 'Brooklyn', 'state' => 'New York']);
        $alderson   = Institution::firstOrCreate(['name' => 'Federal Reformatory for Women, Alderson'], ['city' => 'Alderson', 'state' => 'West Virginia']);
        $seatac     = Institution::firstOrCreate(['name' => 'SeaTac Federal Detention Center'], ['city' => 'SeaTac', 'state' => 'Washington']);
        $sandiego   = Institution::firstOrCreate(['name' => 'Federal Bureau of Prisons (various)'], ['city' => null, 'state' => null]);
        $danbury    = Institution::firstOrCreate(['name' => 'FCI Danbury'], ['city' => 'Danbury', 'state' => 'Connecticut']);
        $seymour    = Institution::firstOrCreate(['name' => 'Federal court, Eastern District of North Carolina (Seymour Johnson AFB Plowshares)'], ['city' => 'Goldsboro', 'state' => 'North Carolina']);
        $concord    = Institution::firstOrCreate(['name' => 'Concord Naval Weapons Station'], ['city' => 'Concord', 'state' => 'California']);
        $hampshire  = Institution::firstOrCreate(['name' => 'Hampshire County Jail (Northampton)'], ['city' => 'Northampton', 'state' => 'Massachusetts']);

        $entries = [
            // ── James Bevel ────────────────────────────────────────────
            [
                'data' => [
                    'name' => 'James Bevel', 'first_name' => 'James', 'middle_name' => 'Luther', 'last_name' => 'Bevel',
                    'birthdate' => '1936-10-19', 'death_date' => '2008-12-19',
                    'gender' => 'Male', 'race' => 'Black',
                    'state' => 'Mississippi', 'era' => '1960s',
                    'ideologies' => ['Civil rights', 'Nonviolence', 'Anti-war'],
                    'affiliation' => ['Student Nonviolent Coordinating Committee', 'Southern Christian Leadership Conference', 'Nashville Student Movement'],
                    'in_custody' => false, 'released' => true,
                    'description' => "Reverend James Luther Bevel was a Mississippi-born civil rights organizer trained by James Lawson in the Nashville student movement. He became one of the most consequential field strategists of the Southern freedom movement: he organized the 1961 Nashville Freedom Riders' continuation into Mississippi after the original riders were beaten in Anniston and Birmingham, helped lead the 1962 Albany Movement, and as SCLC Director of Direct Action conceived and led the May 1963 Birmingham Children's Crusade — the action whose images of fire hoses and police dogs turned U.S. and world public opinion against Southern segregation. He co-wrote and helped strategize the Selma to Montgomery March in 1965, and persuaded Martin Luther King Jr. to publicly oppose the Vietnam War, helping draft King's April 4, 1967 Riverside Church speech 'Beyond Vietnam.'\n\nBevel was arrested repeatedly during civil rights organizing — in Nashville sit-ins (1960), on the Freedom Rides (1961, served time at Mississippi's Parchman Penitentiary alongside the rest of that group), in Albany, in Birmingham, and in Selma. His total time in Southern jails for civil rights organizing across the early 1960s ran into months. His public legacy is complicated by a 2008 conviction in Virginia for incest (for which he was sentenced to 15 years and which is unrelated to his civil-rights work); he is included in this database for the years he served in the South for organizing.",
                ],
                'case' => [
                    'institution_id' => $civilRights->id,
                    'charges'        => 'Multiple Southern jurisdictions: breach of peace, parading without a permit, contributing to the delinquency of minors, and related civil-rights organizing charges across Tennessee, Mississippi, Georgia, Alabama, and elsewhere from 1960 through the late 1960s',
                    'arrest_date'    => '1960-02-27',
                    'release_date'   => '1968-04-04',
                    'convicted'      => 'Multiple short convictions across SNCC and SCLC organizing in the South 1960–1968',
                    'sentence'       => 'Aggregate of several months across many short sentences during civil-rights organizing — including 39 days at the Mississippi State Penitentiary (Parchman) on the 1961 Freedom Rides',
                ],
            ],

            // ── Cesar Chavez ───────────────────────────────────────────
            [
                'data' => [
                    'name' => 'Cesar Chavez', 'first_name' => 'Cesar', 'middle_name' => 'Estrada', 'last_name' => 'Chavez',
                    'birthdate' => '1927-03-31', 'death_date' => '1993-04-23',
                    'gender' => 'Male', 'race' => 'Latino',
                    'state' => 'California', 'era' => '1970s',
                    'ideologies' => ['Labor', 'Civil rights', 'Nonviolence', 'Catholic Worker'],
                    'affiliation' => ['United Farm Workers', 'Community Service Organization'],
                    'in_custody' => false, 'released' => true,
                    'description' => "Cesar Estrada Chavez was the co-founder, with Dolores Huerta, of the United Farm Workers and the most prominent Mexican-American labor leader and civil-rights organizer of the 20th century. Born to a Yuma, Arizona farm family who lost their land during the Depression and became migrant farmworkers, he served in the U.S. Navy in the Pacific in World War II and afterwards spent the 1950s as a community organizer for Saul Alinsky's Community Service Organization. In 1962 he founded what became the United Farm Workers and built it into a national organization through the Delano grape strike (1965–1970), which forced the first significant union contracts in California agriculture.\n\nOn December 4, 1970 he was arrested in Salinas, California by Monterey County Superior Court Judge Gordon Campbell for refusing to comply with a court injunction against the UFW's secondary boycott of the Bud Antle lettuce growers. He was held at the Monterey County Jail in Salinas from December 10 to December 24, 1970 — released only when the California Supreme Court ordered his release on Christmas Eve. While he was in jail, Coretta Scott King and Ethel Kennedy made widely publicized visits, and tens of thousands of supporters held nightly rosary vigils outside the jail.\n\nHe was arrested in numerous other actions across his organizing life. His leadership combined labor militancy with religious nonviolence and the public hunger strike (most famously his 25-day fast in 1968 and his 36-day fast in 1988) as a tool of moral pressure. He died in San Luis, Arizona on April 23, 1993. He was posthumously awarded the Presidential Medal of Freedom in 1994.",
                ],
                'case' => [
                    'institution_id' => $monterey->id,
                    'charges'        => 'Two counts of contempt of court — refusing to comply with a Monterey County Superior Court injunction against the United Farm Workers Organizing Committee\'s national consumer boycott of Bud Antle lettuce',
                    'arrest_date'    => '1970-12-04',
                    'incarceration_date' => '1970-12-10',
                    'release_date'   => '1970-12-24',
                    'convicted'      => 'Yes — Monterey County Superior Court, December 4, 1970; California Supreme Court ordered his release on Christmas Eve',
                    'sentence'       => 'Held in the Monterey County Jail in Salinas for 14 days; $500 fine on each of two counts. (One of multiple jailings across his organizing life.)',
                    'judge'          => 'Gordon Campbell',
                ],
            ],

            // ── Kathy Kelly ───────────────────────────────────────────
            [
                'data' => [
                    'name' => 'Kathy Kelly', 'first_name' => 'Kathy', 'last_name' => 'Kelly',
                    'birthdate' => '1952-12-10', 'death_date' => '2025-01-09',
                    'gender' => 'Female',
                    'state' => 'Illinois', 'era' => '2010s',
                    'ideologies' => ['Anti-war', 'Pacifist', 'Catholic Worker', 'Anti-nuclear', 'Tax resistance'],
                    'affiliation' => ['Voices in the Wilderness', 'Voices for Creative Nonviolence', 'Ban Killer Drones'],
                    'in_custody' => false, 'released' => true,
                    'description' => "Kathy Kelly was an American peace activist nominated three times for the Nobel Peace Prize and one of the most arrested anti-war organizers of her generation. Raised on Chicago's South Side and trained at Loyola University Chicago and the Chicago Theological Seminary, she became active in Catholic Worker circles in the 1970s and built her organizing around tax resistance, the Plowshares movement, and direct intervention in war zones.\n\nIn 1988 she and others planted corn on the silo of a Minuteman II nuclear missile in Missouri as an act of symbolic disarmament. She was sentenced to one year in federal prison and served nine months at the Federal Medical Center in Lexington, Kentucky (the women's facility). In 2004 she was sentenced to three months at FCI Pekin for crossing the line at the U.S. Army School of the Americas / WHINSEC at Fort Benning, Georgia. In 2014 she was sentenced to three months at FMC Lexington for crossing the line at Whiteman Air Force Base in protest of U.S. drone warfare.\n\nIn 1996 she co-founded Voices in the Wilderness, which over the next seven years organized roughly seventy delegations to bring medicine to Iraqi children in open defiance of UN/U.S. economic sanctions. She traveled to Iraq more than 26 times, including remaining in Baghdad during the opening days of the 1991 and 2003 U.S. bombing campaigns. After Voices in the Wilderness was fined \$20,000 by the U.S. Treasury Department for sanctions violations, the campaign closed in 2003 and was succeeded by Voices for Creative Nonviolence. She was arrested more than 60 times across her life. She died on January 9, 2025.",
                ],
                'case' => [
                    'institution_id' => $lexington->id,
                    'charges'        => 'Trespass on a Minuteman II nuclear missile silo, Whiteman AFB, and U.S. Army School of the Americas — across multiple federal prosecutions 1988, 2004, and 2014',
                    'arrest_date'    => '1988-08-15',
                    'release_date'   => '2014-09-15',
                    'convicted'      => 'Multiple convictions for trespass and related civil-disobedience charges across more than 60 arrests',
                    'sentence'       => '1989: 1 year federal, served 9 months at FMC Lexington (corn-planting on Minuteman II missile silo); 2004: 3 months federal at FCI Pekin (SOA Watch line crossing); 2014: 3 months at FMC Lexington (Whiteman AFB drone protest)',
                ],
            ],

            // ── Megan Rice ────────────────────────────────────────────
            [
                'data' => [
                    'name' => 'Megan Rice', 'first_name' => 'Megan', 'middle_name' => 'Gillespie', 'last_name' => 'Rice',
                    'birthdate' => '1930-01-31', 'death_date' => '2021-10-10',
                    'gender' => 'Female',
                    'state' => 'New York', 'era' => '2010s',
                    'ideologies' => ['Anti-nuclear', 'Pacifist', 'Catholic'],
                    'affiliation' => ['Society of the Holy Child Jesus', 'Transform Now Plowshares', 'Nevada Desert Experience'],
                    'in_custody' => false, 'released' => true,
                    'description' => "Sister Megan Gillespie Rice SHCJ was an American Roman Catholic nun of the Society of the Holy Child Jesus, a missionary teacher who spent four decades teaching children in Nigeria and Ghana, and after returning to the United States in the 2000s became a full-time anti-nuclear activist with the Nevada Desert Experience and the Plowshares movement. She had been arrested at the Nevada Test Site, the U.S. Army School of the Americas, and elsewhere more than forty times by her seventies.\n\nIn the early hours of July 28, 2012 — the day before the Hiroshima anniversary — she, Vietnam veteran Michael Walli (then 63), and former U.S. Army officer Greg Boertje-Obed (then 57), all three Catholic peace activists, cut through three perimeter fences and walked into the Y-12 National Security Complex at Oak Ridge, Tennessee. Y-12 is one of the United States' most heavily guarded weapons-grade uranium storage facilities. They reached the wall of the Highly Enriched Uranium Materials Facility, hung peace banners, splashed human blood on the walls, painted slogans, and read a statement aloud while waiting for security to arrive. The action, which exposed catastrophic security failures at the facility, became known as the Transform Now Plowshares.\n\nThe three were charged with sabotage and depredation of government property. On May 9, 2013 a federal jury in Knoxville convicted them on both counts. On February 18, 2014, U.S. District Judge Amul Thapar sentenced Rice — then 84 years old — to 35 months in federal prison. On May 8, 2015, the Sixth Circuit Court of Appeals threw out the sabotage conviction and ordered the three released for time served on the lesser depredation count. Sister Megan was freed after roughly two years in federal custody. She died on October 10, 2021 at age 91.",
                ],
                'case' => [
                    'institution_id' => $brooklyn->id,
                    'charges'        => 'Sabotage (18 U.S.C. § 2155); depredation of government property (18 U.S.C. § 1361) — Transform Now Plowshares action at the Y-12 National Security Complex in Oak Ridge, Tennessee',
                    'arrest_date'    => '2012-07-28',
                    'incarceration_date' => '2013-05-09',
                    'release_date'   => '2015-05-08',
                    'convicted'      => 'Convicted by federal jury, May 9, 2013 (Knoxville, TN); sabotage conviction reversed by U.S. Court of Appeals for the Sixth Circuit, May 8, 2015; resentenced to time served on the lesser depredation count',
                    'sentence'       => '35 months in federal prison; served approximately 2 years before appellate reversal',
                    'judge'          => 'Amul Thapar',
                ],
            ],

            // ── Ardeth Platte ──────────────────────────────────────────
            [
                'data' => [
                    'name' => 'Ardeth Platte', 'first_name' => 'Ardeth', 'last_name' => 'Platte',
                    'birthdate' => '1936-04-10', 'death_date' => '2020-09-30',
                    'gender' => 'Female',
                    'state' => 'Michigan', 'era' => '2000s',
                    'ideologies' => ['Anti-nuclear', 'Pacifist', 'Catholic'],
                    'affiliation' => ['Dominican Sisters of Grand Rapids', 'Plowshares movement', 'Jonah House'],
                    'in_custody' => false, 'released' => true,
                    'description' => "Sister Ardeth Platte OP was a Dominican nun of the Grand Rapids Dominicans whose six decades of social-justice organizing began in Saginaw, Michigan, where she helped organize against urban poverty and educational inequity, and culminated in her years as one of the most arrested anti-nuclear activists in the United States. She lived in community at Jonah House in Baltimore for the last twenty-five years of her life with longtime partners-in-action Carol Gilbert and the late Philip Berrigan.\n\nOn October 6, 2002, with Sister Carol Gilbert and Sister Jackie Hudson, she cut through perimeter fencing and walked onto the launching pad of an N-8 Minuteman III intercontinental ballistic missile silo near New Raymer, Colorado, splashed her own blood on the silo cover in the shape of a cross, and hammered on the silo with household hammers — an act intended as a literal beating of swords into plowshares, in the symbolic tradition of the Plowshares movement co-founded by Philip Berrigan in 1980. The action, called the Sacred Earth and Space Plowshares II, took place three weeks before the Bush administration's congressional vote on Iraq. The three were arrested at the silo and charged with sabotage and depredation of property.\n\nA federal jury in Denver convicted them in April 2003. On July 25, 2003, U.S. District Judge Robert E. Blackburn sentenced Sister Ardeth — then 67 years old — to 41 months in federal prison, the longest sentence given to any of the three. She served at FCI Danbury in Connecticut, where she was housed alongside, and later widely identified as the inspiration for, the character Sister Jane Ingalls in the Netflix series Orange Is the New Black. She was released in November 2005 and continued anti-nuclear organizing until her death in her sleep at Jonah House on September 30, 2020.",
                ],
                'case' => [
                    'institution_id' => $danbury->id,
                    'charges'        => 'Sabotage of national defense premises (18 U.S.C. § 2155); depredation of government property — Sacred Earth and Space Plowshares II action at a Minuteman III missile silo near New Raymer, Colorado',
                    'arrest_date'    => '2002-10-06',
                    'incarceration_date' => '2003-07-25',
                    'release_date'   => '2005-11-25',
                    'convicted'      => 'Yes — federal jury verdict, U.S. District Court for the District of Colorado, April 2003',
                    'sentence'       => '41 months in federal prison; served approximately 28 months at FCI Danbury, Connecticut',
                    'judge'          => 'Robert E. Blackburn',
                ],
            ],

            // ── Carol Gilbert ──────────────────────────────────────────
            [
                'data' => [
                    'name' => 'Carol Gilbert', 'first_name' => 'Carol', 'last_name' => 'Gilbert',
                    'birthdate' => '1947-01-01', 'death_date' => null,
                    'gender' => 'Female',
                    'state' => 'Michigan', 'era' => '2000s',
                    'ideologies' => ['Anti-nuclear', 'Pacifist', 'Catholic'],
                    'affiliation' => ['Dominican Sisters of Grand Rapids', 'Plowshares movement', 'Jonah House'],
                    'in_custody' => false, 'released' => true,
                    'description' => "Sister Carol Gilbert OP is a Dominican nun of the Grand Rapids Dominicans and longtime resident of Jonah House in Baltimore, where she lived in community with the late Sister Ardeth Platte and the late Philip Berrigan. With Platte and Sister Jackie Hudson she carried out the October 6, 2002 Sacred Earth and Space Plowshares II action against a Minuteman III intercontinental ballistic missile silo near New Raymer, Colorado, in which the three women hammered on the silo cover and poured their own blood as a literal enactment of beating swords into plowshares.\n\nA federal jury in Denver convicted them in April 2003. On July 25, 2003, U.S. District Judge Robert E. Blackburn sentenced Sister Carol — then 56 years old — to 30 months in federal prison plus three years of supervised release and \$3,082 in restitution. She served at FCI Alderson in West Virginia. After her 2005 release she continued Plowshares and anti-nuclear organizing alongside Sister Ardeth at Jonah House, including subsequent prosecutions for additional civil-disobedience actions at U.S. military installations. She remains active in the Plowshares and Jonah House communities.",
                ],
                'case' => [
                    'institution_id' => $alderson->id,
                    'charges'        => 'Sabotage of national defense premises (18 U.S.C. § 2155); depredation of government property — Sacred Earth and Space Plowshares II action at a Minuteman III missile silo near New Raymer, Colorado',
                    'arrest_date'    => '2002-10-06',
                    'incarceration_date' => '2003-07-25',
                    'release_date'   => '2005-09-25',
                    'convicted'      => 'Yes — federal jury verdict, U.S. District Court for the District of Colorado, April 2003',
                    'sentence'       => '30 months in federal prison plus three years supervised release and $3,082 in restitution; served at FCI Alderson, West Virginia',
                    'judge'          => 'Robert E. Blackburn',
                ],
            ],

            // ── William "Bix" Bichsel ──────────────────────────────────
            [
                'data' => [
                    'name' => 'William Bichsel', 'first_name' => 'William', 'middle_name' => 'J.', 'last_name' => 'Bichsel',
                    'aka' => 'Bix Bichsel',
                    'birthdate' => '1928-11-28', 'death_date' => '2015-02-28',
                    'gender' => 'Male',
                    'state' => 'Washington', 'era' => '2010s',
                    'ideologies' => ['Anti-nuclear', 'Pacifist', 'Catholic Worker'],
                    'affiliation' => ['Society of Jesus (Jesuits)', 'Disarm Now Plowshares', 'Ground Zero Center for Nonviolent Action', 'G.I. Joe Catholic Worker House (Tacoma)'],
                    'in_custody' => false, 'released' => true,
                    'description' => "Father William J. 'Bix' Bichsel SJ was an American Jesuit priest, founder of the G.I. Joe Catholic Worker House in Tacoma, Washington, and one of the founding figures of the Pacific Northwest anti-nuclear movement. He spent six decades organizing against the U.S. Trident submarine base at Bangor on Hood Canal — home to the largest concentration of nuclear weapons in the United States — and against the U.S. Army School of the Americas at Fort Benning, Georgia. He was arrested more than thirty times for civil-disobedience actions, beginning in the 1980s.\n\nIn the early hours of November 2, 2009, Bix — then 81 years old — and four other Catholic activists (including the late Anne Montgomery RSCJ, Susan Crane, Steve Kelly SJ, and Lynne Greenwald) cut through three security fences and entered the Strategic Weapons Facility Pacific at Naval Base Kitsap-Bangor, where the U.S. Pacific fleet's Trident D-5 nuclear warheads are stored. They reached the inner perimeter, where they hung banners, scattered sunflower seeds, and waited to be arrested. The action became known as the Disarm Now Plowshares. The five were charged with conspiracy, trespass, destruction of property, and depredation of government property. After a December 2010 trial they were convicted by a federal jury in Tacoma. Bix was sentenced in March 2011 to three months in federal prison plus four months of home confinement.\n\nIn 2010 he had also served three months at FCI Marianna for an SOA Watch border crossing at Fort Benning. He died on February 28, 2015 at age 86.",
                ],
                'case' => [
                    'institution_id' => $seatac->id,
                    'charges'        => 'Conspiracy; depredation of property of the United States; trespass on a naval installation — Disarm Now Plowshares action at the Strategic Weapons Facility Pacific, Naval Base Kitsap-Bangor, Washington',
                    'arrest_date'    => '2009-11-02',
                    'incarceration_date' => '2011-03-28',
                    'release_date'   => '2011-06-28',
                    'convicted'      => 'Yes — federal jury verdict, U.S. District Court for the Western District of Washington, December 13, 2010',
                    'sentence'       => '3 months federal prison plus 4 months home confinement; restitution. (Plus 3 months at FCI Marianna in 2010 for separate SOA Watch trespass at Fort Benning.)',
                ],
            ],

            // ── Louis Vitale ───────────────────────────────────────────
            [
                'data' => [
                    'name' => 'Louis Vitale', 'first_name' => 'Louis', 'last_name' => 'Vitale',
                    'birthdate' => '1932-06-01', 'death_date' => '2023-06-21',
                    'gender' => 'Male',
                    'state' => 'California', 'era' => '2000s',
                    'ideologies' => ['Anti-nuclear', 'Anti-war', 'Pacifist', 'Catholic Worker'],
                    'affiliation' => ['Order of Friars Minor (Franciscans)', 'Nevada Desert Experience', 'Pace e Bene Nonviolence Service'],
                    'in_custody' => false, 'released' => true,
                    'description' => "Father Louis Vitale OFM was an American Franciscan friar, U.S. Air Force veteran (he served as a pilot before entering religious life), and one of the most prolific civil-disobedience peace activists in modern U.S. history — arrested more than 200 times across five decades. He co-founded the Nevada Desert Experience in 1982, the long-running organizing campaign against U.S. nuclear testing at the Nevada Test Site, and Pace e Bene Nonviolence Service in 1989. He served as Pastor of St. Boniface Church in San Francisco's Tenderloin neighborhood from 1992 to 2005, where he ran the largest meal program for unhoused people in the city.\n\nMost of his arrests were short Nevada Test Site trespasses, but he served substantial federal time for two later actions. On November 19, 2006, with Father Steve Kelly SJ, he crossed the line at Fort Huachuca, Arizona — the U.S. Army's interrogation school where post-9/11 'enhanced interrogation' techniques were taught — and attempted to deliver a letter to the commanding general protesting U.S. torture policy. They were convicted of trespass and sentenced to five months federal in May 2007; both were 75 years old at the time. In 2007 he was also sentenced to five months for the annual SOA Watch line-crossing at Fort Benning, Georgia, served at FCI Lompoc. He died on June 21, 2023 at age 91.",
                ],
                'case' => [
                    'institution_id' => $sandiego->id,
                    'charges'        => 'Trespass — Fort Huachuca anti-torture protest (November 2006); trespass — SOA Watch line-crossing at Fort Benning (2007); plus more than 200 arrests over five decades, many at the Nevada Test Site',
                    'arrest_date'    => '2006-11-19',
                    'incarceration_date' => '2007-05-15',
                    'release_date'   => '2008-04-15',
                    'convicted'      => 'Multiple federal convictions for trespass on military installations',
                    'sentence'       => '5 months federal at FCI Lompoc for the Fort Huachuca anti-torture action (2007); separately 5 months federal at FCI Lompoc for the 2007 SOA Watch action; numerous shorter sentences across more than 200 arrests',
                ],
            ],

            // ── Tom Cornell ────────────────────────────────────────────
            [
                'data' => [
                    'name' => 'Tom Cornell', 'first_name' => 'Thomas', 'middle_name' => 'Charles', 'last_name' => 'Cornell',
                    'birthdate' => '1934-04-09', 'death_date' => '2022-08-01',
                    'gender' => 'Male',
                    'state' => 'New York', 'era' => '1960s',
                    'ideologies' => ['Pacifist', 'Catholic Worker', 'Anti-war'],
                    'affiliation' => ['Catholic Worker Movement', 'Catholic Peace Fellowship', 'Fellowship of Reconciliation'],
                    'in_custody' => false, 'released' => true,
                    'description' => "Thomas Charles Cornell was an American pacifist organizer, ordained Catholic deacon, longtime member of the Catholic Worker Movement, and a co-founder of the Catholic Peace Fellowship. As a young Catholic Worker in the early 1960s, he played a central role in initiating the first organized U.S. opposition to the Vietnam War. On July 29, 1965, just weeks after Congress passed the new federal law making destruction of a draft card a crime punishable by five years in prison, Cornell publicly burned his draft card at a New York City rally — an act of civil disobedience explicitly designed to test the constitutionality of the new statute.\n\nOn November 6, 1965 he again publicly burned his draft card, this time alongside David Miller, A. J. Muste, Dorothy Day, and three others, at an even larger New York rally. He was prosecuted federally and was convicted in 1968. The Supreme Court upheld the constitutionality of the draft-card-burning statute the same year (United States v. O'Brien, 1968). Cornell served six months in the Federal Correctional Institution at Danbury, Connecticut.\n\nAfter his release he spent the remainder of his life as a Catholic Worker organizer, a co-editor of the Catholic Worker newspaper, and a columnist on labor and Christian-pacifist politics. He was ordained a permanent deacon of the Roman Catholic Church in 1988. He died on August 1, 2022 at age 88.",
                ],
                'case' => [
                    'institution_id' => $danbury->id,
                    'charges'        => 'Knowing destruction of a Selective Service registration certificate (50 U.S.C. § 462) — for publicly burning his draft card at the November 6, 1965 New York City antiwar rally',
                    'arrest_date'    => '1965-11-06',
                    'incarceration_date' => '1968-04-01',
                    'release_date'   => '1968-10-01',
                    'convicted'      => 'Yes — U.S. District Court for the Southern District of New York, 1968 (statute upheld as constitutional in United States v. O\'Brien, 391 U.S. 367, the same year)',
                    'sentence'       => '6 months in federal prison at FCI Danbury, Connecticut',
                ],
            ],

            // ── John Dear ──────────────────────────────────────────────
            [
                'data' => [
                    'name' => 'John Dear', 'first_name' => 'John', 'last_name' => 'Dear',
                    'birthdate' => '1959-08-13', 'death_date' => null,
                    'gender' => 'Male',
                    'state' => 'New Mexico', 'era' => '1990s',
                    'ideologies' => ['Anti-nuclear', 'Anti-war', 'Pacifist', 'Catholic'],
                    'affiliation' => ['Society of Jesus (Jesuits)', 'Plowshares movement', 'Pax Christi USA', 'Fellowship of Reconciliation'],
                    'in_custody' => false, 'released' => true,
                    'description' => "John Dear is an American peace activist, Catholic priest (formerly a Jesuit; he was dismissed from the Society of Jesus in 2014 over his political activism, and has continued his ministry as a diocesan priest), author, and lecturer. He has been arrested approximately 80 times for civil-disobedience actions against war, nuclear weapons, the U.S. School of the Americas, and U.S. drone warfare since the 1980s.\n\nHis most significant federal sentence came after the December 7, 1993 Pax Christi-USA Plowshares action at Seymour Johnson Air Force Base in Goldsboro, North Carolina. With three other Catholic activists (Bruce Friedrich, Lynn Fredriksson, and Philip Berrigan), he cut through the base perimeter fence and hammered on an F-15E fighter-bomber loaded for deployment to Iraq, in symbolic disarmament. They were convicted of destruction of government property and conspiracy in federal court in Greenville, North Carolina; Dear was sentenced to one year in federal prison and served eight months at the federal jail in Edenton, NC. He was also sentenced to numerous shorter terms over the years for SOA Watch and similar actions.\n\nHe served as the executive director of the Fellowship of Reconciliation USA from 1998 to 2003 and as a Red Cross coordinator at New York's Family Assistance Center after September 11, 2001.",
                ],
                'case' => [
                    'institution_id' => $seymour->id,
                    'charges'        => 'Conspiracy; destruction of government property — Pax Christi-USA Plowshares action against an F-15E fighter-bomber at Seymour Johnson Air Force Base, December 7, 1993',
                    'arrest_date'    => '1993-12-07',
                    'incarceration_date' => '1994-04-01',
                    'release_date'   => '1994-12-01',
                    'convicted'      => 'Yes — U.S. District Court for the Eastern District of North Carolina, 1994',
                    'sentence'       => '1 year in federal prison; served 8 months at the federal jail in Edenton, North Carolina. Plus dozens of shorter sentences across approximately 80 lifetime arrests',
                ],
            ],

            // ── Brian Willson ──────────────────────────────────────────
            [
                'data' => [
                    'name' => 'Brian Willson', 'first_name' => 'S.', 'middle_name' => 'Brian', 'last_name' => 'Willson',
                    'birthdate' => '1941-07-04', 'death_date' => null,
                    'gender' => 'Male',
                    'state' => 'California', 'era' => '1980s',
                    'ideologies' => ['Anti-war', 'Pacifist'],
                    'affiliation' => ['Veterans Peace Action Team', 'Vietnam Veterans Against the War'],
                    'in_custody' => false, 'released' => true,
                    'description' => "S. Brian Willson is an American attorney, U.S. Air Force veteran of the Vietnam War (he served as a combat security officer in the Mekong Delta in 1969 and was deeply traumatized by the killing of civilians he witnessed there), and one of the most prominent U.S. anti-war activists of the late 20th century. After leaving the military he became a lawyer and a Vietnam Veterans Against the War organizer, and in the 1980s helped found the Veterans Peace Action Team.\n\nOn September 1, 1987, Willson and three other VPAT veterans began what they had publicly announced for weeks would be a 40-day water-only fast on the railroad tracks at the Concord Naval Weapons Station in Concord, California, to block U.S. munitions trains carrying weapons to the Reagan-administration-backed Contras in Nicaragua and to U.S.-backed forces in El Salvador. As the first scheduled munitions train approached the protesters, the Navy crew accelerated to nearly three times the posted 5-mph speed limit and ran directly through the protesters; Willson was struck and dragged. He survived but lost both legs below the knee, fractured his skull, and was left with a permanent skull plate. The driver was federally charged but acquitted; Willson later won a civil settlement of \$920,000 against the Navy and the railroad. The image of his blood-soaked stretcher being carried away catalyzed a national antiwar response: more than 9,000 people protested at the gate within days, the tracks were physically torn up by demonstrators, and no munitions train rolled out of Concord for two and a half years.\n\nHe has been arrested dozens of times across his life for related civil disobedience, including jail time for fasts and trespasses at the Nevada Test Site, the SOA at Fort Benning, and U.S. consulates and federal buildings in solidarity with Central American liberation movements. He is included in this database both for the substantial cumulative time he has spent in custody for political activity and for the iconic state-violence event of 1987.",
                ],
                'case' => [
                    'institution_id' => $concord->id,
                    'charges'        => 'Multiple counts of trespass and civil disobedience across decades of antiwar organizing; the September 1, 1987 Concord Naval Weapons Station fast was protest activity that resulted in his being struck by a U.S. Navy munitions train rather than in his prosecution',
                    'arrest_date'    => '1987-09-01',
                    'death_in_custody_date' => null,
                    'release_date'   => '2010-01-01',
                    'convicted'      => 'Multiple convictions for trespass at U.S. military installations and federal buildings across the late 1970s through the 2000s',
                    'sentence'       => 'Cumulatively several months across multiple short federal and state sentences — Nevada Test Site, Fort Benning (SOA), and other antiwar civil-disobedience actions. The 1987 Concord incident left him a double amputee with a permanent skull plate; he won a $920,000 civil settlement from the Navy and the railroad',
                ],
            ],

            // ── Frances Crowe ──────────────────────────────────────────
            [
                'data' => [
                    'name' => 'Frances Crowe', 'first_name' => 'Frances', 'last_name' => 'Crowe',
                    'birthdate' => '1919-03-15', 'death_date' => '2019-08-27',
                    'gender' => 'Female',
                    'state' => 'Massachusetts', 'era' => '2010s',
                    'ideologies' => ['Anti-nuclear', 'Anti-war', 'Pacifist', 'Quaker'],
                    'affiliation' => ['American Friends Service Committee', 'War Resisters League', 'Western Massachusetts Mobilization for Survival'],
                    'in_custody' => false, 'released' => true,
                    'description' => "Frances Crowe was an American Quaker, longtime Western Massachusetts peace and anti-nuclear-power organizer, and one of the most prolific draft counselors of the Vietnam era — she counseled an estimated 2,000 conscientious objectors and draft resisters out of her Northampton home in the 1960s and early 1970s. She was a central organizer of the regional movement against the Vermont Yankee and Indian Point nuclear power plants, and of the Western Massachusetts opposition to U.S. military intervention from Vietnam through the Iraq wars.\n\nShe was arrested more than fifteen times for civil disobedience over the course of her life, almost all for short terms, with longer stretches at the Hampshire County Jail in Northampton. She was first arrested in 1972 (at age 53) for blocking Selective Service offices in protest of the Vietnam War; her last documented arrest came in November 2018 at age 99, when she sat in protest outside a TD Bank in Northampton against the bank's financing of the Dakota Access Pipeline. She wrote about her arrests, draft counseling, and Quaker pacifism in her 2014 memoir Finding My Radical Soul. She died on August 27, 2019 at age 100.",
                ],
                'case' => [
                    'institution_id' => $hampshire->id,
                    'charges'        => 'Multiple counts of trespass and civil disobedience over five decades — draft resistance, anti-nuclear, anti-war, and anti-pipeline actions in Massachusetts and elsewhere',
                    'arrest_date'    => '1972-04-01',
                    'release_date'   => '2018-11-01',
                    'convicted'      => 'Multiple convictions for trespass and civil disobedience across more than 15 arrests',
                    'sentence'       => 'Several short jail terms, mostly at Hampshire County Jail (Northampton, MA); cumulative time in custody totaling several weeks across her life',
                ],
            ],
        ];

        foreach ($entries as $entry) {
            DB::transaction(function () use ($entry, &$created, &$skipped) {
                $name = $entry['data']['name'];
                if (Prisoner::where('name', $name)->exists()) {
                    $this->warn("  skipped: {$name} already exists");
                    $skipped++;
                    return;
                }

                $prisoner = Prisoner::create($entry['data']);
                PrisonerCase::create(array_merge(['prisoner_id' => $prisoner->id], $entry['case']));

                $this->info("  added {$prisoner->name}");
                $created++;
            });
        }

        $this->info("\nDone. {$created} created, {$skipped} skipped.");

        return self::SUCCESS;
    }
}
