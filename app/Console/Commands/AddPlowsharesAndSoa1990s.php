<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddPlowsharesAndSoa1990s extends Command
{
    protected $signature = 'prisoners:add-plowshares-soa-1990s';
    protected $description = 'Add (or update with case info) the late-1990s Plowshares + SOA Watch / School of the Americas defendants from the Nuclear Resister inside-and-out source list: Prince of Peace Plowshares (USS The Sullivans), Gods of Metal Plowshares (B-52), Minuteman III Plowshares (Greeley CO silo), and the SOA sign-altering / repeated-trespass cluster.';

    public function handle(): int
    {
        // ─── Institutions ───
        $fciDublin       = Institution::firstOrCreate(['name' => 'FCI Dublin'], ['city' => 'Dublin', 'state' => 'California']);
        $allenwoodLow    = Institution::firstOrCreate(['name' => 'LSCI Allenwood'], ['city' => 'White Deer', 'state' => 'Pennsylvania']);
        $allenwoodCamp   = Institution::firstOrCreate(['name' => 'FPC Allenwood'], ['city' => 'Montgomery', 'state' => 'Pennsylvania']);
        $kentCounty      = Institution::firstOrCreate(['name' => 'Kent County Detention Center'], ['city' => 'Chestertown', 'state' => 'Maryland']);
        $charlesCounty   = Institution::firstOrCreate(['name' => 'Charles County Detention Center'], ['city' => 'La Plata', 'state' => 'Maryland']);
        $fpcSheridan     = Institution::firstOrCreate(['name' => 'FPC Sheridan'], ['city' => 'Sheridan', 'state' => 'Oregon']);
        $fpcBeckley      = Institution::firstOrCreate(['name' => 'FPC Beckley'], ['city' => 'Beaver', 'state' => 'West Virginia']);
        $fciDanbury      = Institution::firstOrCreate(['name' => 'FCI Danbury'], ['city' => 'Danbury', 'state' => 'Connecticut']);
        $fpcLexington    = Institution::firstOrCreate(['name' => 'FPC Lexington'], ['city' => 'Lexington', 'state' => 'Kentucky']);
        $fpcAlderson     = Institution::firstOrCreate(['name' => 'FPC Alderson'], ['city' => 'Alderson', 'state' => 'West Virginia']);
        $fdcEnglewood    = Institution::firstOrCreate(['name' => 'FDC Englewood'], ['city' => 'Littleton', 'state' => 'Colorado']);

        $popContext   = "On February 12, 1997, four Plowshares activists — Susan Crane, Steve Kelly SJ, Tom Lewis, and Steve Baggarly — boarded the USS The Sullivans, a guided-missile destroyer being outfitted with the Tomahawk cruise launching system at the Bath Iron Works shipyard in Maine, and hammered on and poured blood over the missile launchers. The action took the name Prince of Peace Plowshares.";
        $gomContext   = "On May 17, 1998, five Plowshares activists — Sister Ardeth Platte OP, Sister Carol Gilbert OP, Kathy Boylan, Father Frank Cordaro, and Father Larry Morlan — entered the Andrews Air Force Base flightline in Maryland and disarmed a B-52 bomber with hammers and blood. The action took the name Gods of Metal Plowshares.";
        $miiiContext  = "On August 6, 1998 — the 53rd anniversary of the U.S. atomic bombing of Hiroshima — Daniel Sicken and Sachio Oliver Coe entered Minuteman III intercontinental ballistic missile silo N-8 near Greeley, Colorado, and hammered on the silo cover and equipment. The action took the name Minuteman III Plowshares.";
        $soaSignContext = "On September 29, 1997, five activists — Father Bill Bichsel SJ, Sister Marge Eilerman OSF, Ed Kinane, Mary Trotochaud, and others — altered the entrance sign at the U.S. Army School of the Americas at Fort Benning, Georgia. The five were tried in October 1998 in federal court in Columbus, GA and sentenced to 8 to 12 months in federal prison.";

        $entries = [
            // ─── Prince of Peace Plowshares (Feb 14 1997) ───
            [
                'data' => [
                    'name' => 'Susan Crane', 'first_name' => 'Susan', 'last_name' => 'Crane',
                    'gender' => 'Female', 'race' => 'White', 'state' => 'California', 'era' => '1990s',
                    'ideologies' => ['Anti-war', 'Anti-nuclear', 'Pacifist', 'Catholic Worker'],
                    'affiliation' => ['Jonah House', 'Atlantic Life Community', 'Prince of Peace Plowshares'],
                    'in_custody' => false, 'released' => true, 'inmate_number' => '87783-011',
                    'description' => "Susan Crane is a longtime peace activist with Jonah House and the Atlantic Life Community and one of the most prolific U.S. Plowshares activists. She was a member of the Prince of Peace Plowshares affinity group that boarded the USS The Sullivans at the Bath Iron Works in Maine on February 12, 1997 and hammered on and poured blood over the Tomahawk cruise missile launching system. Crane and Steve Kelly SJ were sentenced to 27 months in federal prison; Susan served at FCI Dublin in California. She has participated in additional Plowshares actions before and since.\n\n{$popContext}",
                ],
                'cases' => [[
                    'institution_id' => $fciDublin->id,
                    'charges' => 'Conspiracy and \$28,000 in damage to government property — boarding the USS The Sullivans and hammering on / pouring blood over the Tomahawk cruise launching system at Bath Iron Works, Maine, February 12, 1997',
                    'arrest_date' => '1997-02-12',
                    'incarceration_date' => '1997-02-14',
                    'sentence' => '27 months federal prison + 2 years supervised release + \$4,000 restitution',
                    'convicted' => 'Yes — federal jury verdict, U.S. District Court for the District of Maine',
                ]],
            ],
            [
                'data' => [
                    'name' => 'Steve Kelly SJ', 'first_name' => 'Steve', 'last_name' => 'Kelly',
                    'aka' => 'Stephen Kelly SJ', 'gender' => 'Male', 'race' => 'White',
                    'state' => 'California', 'era' => '1990s',
                    'ideologies' => ['Anti-war', 'Anti-nuclear', 'Pacifist', 'Liberation theology'],
                    'affiliation' => ['Society of Jesus (Jesuits)', 'Atlantic Life Community', 'Prince of Peace Plowshares', 'Kings Bay Plowshares 7'],
                    'in_custody' => false, 'released' => true, 'inmate_number' => '00816-111',
                    'description' => "Father Steve Kelly SJ is an American Jesuit priest and one of the most prolific Plowshares activists in U.S. history. He was a member of the Prince of Peace Plowshares affinity group that boarded the USS The Sullivans at the Bath Iron Works in Maine on February 12, 1997 and hammered on the Tomahawk cruise missile launching system. Kelly was sentenced to ~28 months in federal prison and served at LSCI Allenwood, Pennsylvania, released in June 1999. He has participated in additional Plowshares actions including the November 2, 2009 Disarm Now Plowshares at Naval Base Kitsap-Bangor and the April 4, 2018 Kings Bay Plowshares 7 action at the Trident submarine base in Georgia.\n\n{$popContext}",
                ],
                'cases' => [[
                    'institution_id' => $allenwoodLow->id,
                    'charges' => 'Conspiracy and \$28,000 in damage to government property — boarding the USS The Sullivans and hammering on / pouring blood over the Tomahawk cruise launching system at Bath Iron Works, Maine, February 12, 1997',
                    'arrest_date' => '1997-02-12',
                    'incarceration_date' => '1997-02-14',
                    'release_date' => '1999-06-30',
                    'sentence' => '~28 months federal prison + 2 years supervised release + \$4,000 restitution',
                    'convicted' => 'Yes — federal jury verdict, U.S. District Court for the District of Maine',
                ]],
            ],

            // ─── Gods of Metal Plowshares (May 17 1998) ───
            [
                'data' => [
                    'name' => 'Kathy Boylan', 'first_name' => 'Kathy', 'last_name' => 'Boylan',
                    'gender' => 'Female', 'race' => 'White', 'state' => 'Maryland', 'era' => '1990s',
                    'ideologies' => ['Anti-war', 'Anti-nuclear', 'Pacifist', 'Catholic Worker'],
                    'affiliation' => ['Jonah House', 'Atlantic Life Community', 'Gods of Metal Plowshares'],
                    'in_custody' => false, 'released' => true,
                    'description' => "Kathy Boylan is a longtime peace activist with Jonah House in Baltimore and a member of the Atlantic Life Community. She was one of five members of the Gods of Metal Plowshares affinity group that disarmed a B-52 bomber at Andrews Air Force Base in Maryland on May 17, 1998. She was held at the Kent County Detention Center in Chestertown, Maryland during the federal proceedings.\n\n{$gomContext}",
                ],
                'cases' => [[
                    'institution_id' => $kentCounty->id,
                    'charges' => 'Damage to government property — disarming of a B-52 bomber at Andrews Air Force Base, Maryland (Gods of Metal Plowshares)',
                    'arrest_date' => '1998-05-17',
                    'incarceration_date' => '1998-05-17',
                    'sentenced_date' => '1999-01-15',
                    'convicted' => 'Yes — federal jury verdict, U.S. District Court for the District of Maryland, September 22, 1998',
                    'sentence' => 'Federal prison time-served plus probation',
                ]],
            ],
            [
                'data' => [
                    'name' => 'Larry Morlan', 'first_name' => 'Larry', 'last_name' => 'Morlan',
                    'aka' => 'Father Larry Morlan', 'gender' => 'Male', 'race' => 'White',
                    'state' => 'Maryland', 'era' => '1990s',
                    'ideologies' => ['Anti-war', 'Anti-nuclear', 'Pacifist', 'Liberation theology'],
                    'affiliation' => ['Roman Catholic Diocese', 'Atlantic Life Community', 'Gods of Metal Plowshares'],
                    'in_custody' => false, 'released' => true,
                    'description' => "Father Larry Morlan is a Roman Catholic priest and one of the five members of the Gods of Metal Plowshares affinity group that disarmed a B-52 bomber at Andrews Air Force Base in Maryland on May 17, 1998. He was held at the Charles County Detention Center in La Plata, Maryland during federal proceedings.\n\n{$gomContext}",
                ],
                'cases' => [[
                    'institution_id' => $charlesCounty->id,
                    'charges' => 'Damage to government property — disarming of a B-52 bomber at Andrews Air Force Base, Maryland (Gods of Metal Plowshares)',
                    'arrest_date' => '1998-05-17',
                    'incarceration_date' => '1998-05-17',
                    'sentenced_date' => '1999-01-15',
                    'convicted' => 'Yes — federal jury verdict, U.S. District Court for the District of Maryland, September 22, 1998',
                    'sentence' => 'Federal prison time-served plus probation',
                ]],
            ],

            // ─── SOA Watch — blood-pouring (Liteky) ───
            [
                'data' => [
                    'name' => 'John Patrick Liteky', 'first_name' => 'John', 'middle_name' => 'Patrick', 'last_name' => 'Liteky',
                    'aka' => 'John Liteky', 'gender' => 'Male', 'race' => 'White',
                    'state' => 'Florida', 'era' => '1990s',
                    'ideologies' => ['Anti-war', 'Latin America solidarity', 'Catholic Worker'],
                    'affiliation' => ['SOA Watch', 'Catholic Worker'],
                    'in_custody' => false, 'released' => true, 'inmate_number' => '83725-020',
                    'description' => "John Patrick Liteky is a Catholic Worker and the brother of Charlie Liteky, the Vietnam War chaplain who returned his Medal of Honor in protest of U.S. policy in Central America. John Liteky was sentenced to two years in federal prison for a series of blood-pouring actions against the U.S. Army School of the Americas — at the Pentagon on September 29 and October 20, 1997, and at Fort Benning on February 25, 1998. He served at FPC Sheridan in Oregon and was released in June 2000.",
                ],
                'cases' => [[
                    'institution_id' => $fpcSheridan->id,
                    'charges' => 'Federal trespass and damage to property — blood-pouring at the Pentagon (Sept 29 and Oct 20 1997) and at the U.S. Army School of the Americas, Fort Benning (Feb 25 1998)',
                    'arrest_date' => '1998-02-25',
                    'incarceration_date' => '1998-06-01',
                    'release_date' => '2000-06-01',
                    'sentence' => '2 years federal prison',
                    'convicted' => 'Yes — federal court conviction, 1998',
                ]],
            ],

            // ─── SOA Watch — repeated trespass ───
            [
                'data' => [
                    'name' => 'Richard Streb', 'first_name' => 'Richard', 'last_name' => 'Streb',
                    'gender' => 'Male', 'race' => 'White', 'state' => 'West Virginia', 'era' => '1990s',
                    'ideologies' => ['Anti-war', 'Latin America solidarity'],
                    'affiliation' => ['SOA Watch'],
                    'in_custody' => false, 'released' => true, 'inmate_number' => '88113-020',
                    'description' => "Richard Streb was sentenced to six months in federal prison for repeated trespass at the U.S. Army School of the Americas at Fort Benning, Georgia. He served his sentence at FPC Beckley in Beaver, West Virginia and was released in March 1999.",
                ],
                'cases' => [[
                    'institution_id' => $fpcBeckley->id,
                    'charges' => 'Repeated trespass at the U.S. Army School of the Americas, Fort Benning, Georgia',
                    'arrest_date' => '1998-09-01',
                    'incarceration_date' => '1998-09-01',
                    'release_date' => '1999-03-01',
                    'sentence' => '6 months federal prison',
                    'convicted' => 'Yes — federal court conviction',
                ]],
            ],

            // ─── SOA sign-altering cluster (Sept 29 1997 action; sentenced Oct 1998) ───
            [
                'data' => [
                    'name' => 'Kathleen Rumpf', 'first_name' => 'Kathleen', 'last_name' => 'Rumpf',
                    'gender' => 'Female', 'race' => 'White', 'state' => 'New York', 'era' => '1990s',
                    'ideologies' => ['Anti-war', 'Latin America solidarity', 'Catholic Worker'],
                    'affiliation' => ['SOA Watch', 'Catholic Worker', 'Atlantic Life Community'],
                    'in_custody' => false, 'released' => true, 'inmate_number' => '02117-052',
                    'description' => "Kathleen Rumpf is a Syracuse Catholic Worker and longtime Atlantic Life Community peace activist. She was sentenced to six months in federal prison in connection with the September 29, 1997 SOA Watch sign-altering action at the U.S. Army School of the Americas at Fort Benning, Georgia. She entered FCI Danbury on July 23, 1998.\n\n{$soaSignContext}",
                ],
                'cases' => [[
                    'institution_id' => $fciDanbury->id,
                    'charges' => 'Damage to government property — altering the entrance sign at the U.S. Army School of the Americas, Fort Benning, Georgia, September 29, 1997',
                    'arrest_date' => '1997-09-29',
                    'incarceration_date' => '1998-07-23',
                    'release_date' => '1999-01-23',
                    'sentence' => '6 months federal prison',
                    'convicted' => 'Yes — federal court conviction, October 1998',
                ]],
            ],
            [
                'data' => [
                    'name' => 'Bill Bichsel', 'first_name' => 'William', 'last_name' => 'Bichsel',
                    'aka' => 'Father Bill Bichsel SJ', 'gender' => 'Male', 'race' => 'White',
                    'state' => 'Washington', 'era' => '1990s',
                    'ideologies' => ['Anti-war', 'Anti-nuclear', 'Latin America solidarity', 'Liberation theology'],
                    'affiliation' => ['Society of Jesus (Jesuits)', 'SOA Watch', 'Ground Zero Center for Nonviolent Action'],
                    'in_custody' => false, 'released' => true, 'inmate_number' => '86275-020',
                    'description' => "Father Bill 'Bix' Bichsel SJ was an American Jesuit priest, peace activist with the Ground Zero Center for Nonviolent Action in Poulsbo, Washington, and one of the most consistently arrested U.S. anti-nuclear and SOA Watch activists. He was sentenced to twelve months in federal prison in connection with the September 29, 1997 SOA sign-altering action at Fort Benning. He served at FPC Sheridan, Oregon. Bichsel later participated in the November 2, 2009 Disarm Now Plowshares action at Naval Base Kitsap-Bangor and died in 2015.\n\n{$soaSignContext}",
                ],
                'cases' => [[
                    'institution_id' => $fpcSheridan->id,
                    'charges' => 'Damage to government property — altering the entrance sign at the U.S. Army School of the Americas, Fort Benning, Georgia, September 29, 1997',
                    'arrest_date' => '1997-09-29',
                    'incarceration_date' => '1998-10-01',
                    'release_date' => '1999-10-01',
                    'sentence' => '12 months federal prison',
                    'convicted' => 'Yes — federal court conviction, October 1998',
                ]],
            ],
            [
                'data' => [
                    'name' => 'Marge Eilerman', 'first_name' => 'Marge', 'last_name' => 'Eilerman',
                    'aka' => 'Sister Marge Eilerman OSF', 'gender' => 'Female', 'race' => 'White',
                    'state' => 'Kentucky', 'era' => '1990s',
                    'ideologies' => ['Anti-war', 'Latin America solidarity', 'Liberation theology'],
                    'affiliation' => ['Sisters of St. Francis', 'SOA Watch'],
                    'in_custody' => false, 'released' => true, 'inmate_number' => '88106-020',
                    'description' => "Sister Marge Eilerman OSF is a Sister of St. Francis and longtime peace and justice activist. She was sentenced to twelve months in federal prison in connection with the September 29, 1997 SOA Watch sign-altering action at Fort Benning, and served her sentence at FPC Lexington, Kentucky.\n\n{$soaSignContext}",
                ],
                'cases' => [[
                    'institution_id' => $fpcLexington->id,
                    'charges' => 'Damage to government property — altering the entrance sign at the U.S. Army School of the Americas, Fort Benning, Georgia, September 29, 1997',
                    'arrest_date' => '1997-09-29',
                    'incarceration_date' => '1998-10-01',
                    'release_date' => '1999-10-01',
                    'sentence' => '12 months federal prison',
                    'convicted' => 'Yes — federal court conviction, October 1998',
                ]],
            ],
            [
                'data' => [
                    'name' => 'Ed Kinane', 'first_name' => 'Ed', 'last_name' => 'Kinane',
                    'aka' => 'Edward Kinane', 'gender' => 'Male', 'race' => 'White',
                    'state' => 'New York', 'era' => '1990s',
                    'ideologies' => ['Anti-war', 'Latin America solidarity', 'Catholic Worker'],
                    'affiliation' => ['Syracuse Catholic Worker', 'SOA Watch', 'Voices for Creative Nonviolence'],
                    'in_custody' => false, 'released' => true, 'inmate_number' => '86279-020',
                    'description' => "Ed Kinane is a Syracuse Catholic Worker and longtime peace activist. He was sentenced to ten months in federal prison in connection with the September 29, 1997 SOA Watch sign-altering action at Fort Benning, and served his sentence at FPC Allenwood, Pennsylvania. He has been arrested repeatedly since for nonviolent civil disobedience at military installations including Hancock Field Air National Guard Base in New York during the U.S. drone-warfare era.\n\n{$soaSignContext}",
                ],
                'cases' => [[
                    'institution_id' => $allenwoodCamp->id,
                    'charges' => 'Damage to government property — altering the entrance sign at the U.S. Army School of the Americas, Fort Benning, Georgia, September 29, 1997',
                    'arrest_date' => '1997-09-29',
                    'incarceration_date' => '1998-10-01',
                    'release_date' => '1999-08-01',
                    'sentence' => '10 months federal prison',
                    'convicted' => 'Yes — federal court conviction, October 1998',
                ]],
            ],
            [
                'data' => [
                    'name' => 'Mary Trotochaud', 'first_name' => 'Mary', 'last_name' => 'Trotochaud',
                    'gender' => 'Female', 'race' => 'White', 'state' => 'Georgia', 'era' => '1990s',
                    'ideologies' => ['Anti-war', 'Latin America solidarity'],
                    'affiliation' => ['SOA Watch', 'Atlantic Life Community'],
                    'in_custody' => false, 'released' => true, 'inmate_number' => '88102-020',
                    'description' => "Mary Trotochaud is a peace activist who was sentenced to eight months in federal prison in connection with the September 29, 1997 SOA Watch sign-altering action at Fort Benning. She served her sentence at FPC Alderson, the federal women's prison in West Virginia.\n\n{$soaSignContext}",
                ],
                'cases' => [[
                    'institution_id' => $fpcAlderson->id,
                    'charges' => 'Damage to government property — altering the entrance sign at the U.S. Army School of the Americas, Fort Benning, Georgia, September 29, 1997',
                    'arrest_date' => '1997-09-29',
                    'incarceration_date' => '1998-10-01',
                    'release_date' => '1999-06-01',
                    'sentence' => '8 months federal prison',
                    'convicted' => 'Yes — federal court conviction, October 1998',
                ]],
            ],

            // ─── Minuteman III Plowshares (Aug 6 1998) ───
            [
                'data' => [
                    'name' => 'Daniel Sicken', 'first_name' => 'Daniel', 'last_name' => 'Sicken',
                    'gender' => 'Male', 'race' => 'White', 'state' => 'Vermont', 'era' => '1990s',
                    'ideologies' => ['Anti-war', 'Anti-nuclear', 'Pacifist'],
                    'affiliation' => ['Atlantic Life Community', 'Minuteman III Plowshares'],
                    'in_custody' => false, 'released' => true, 'inmate_number' => '28360-013',
                    'description' => "Daniel Sicken was one of two members of the Minuteman III Plowshares affinity group that entered Minuteman III intercontinental ballistic missile silo N-8 near Greeley, Colorado on August 6, 1998 — the 53rd anniversary of the U.S. atomic bombing of Hiroshima — and hammered on the silo cover and equipment. He was found guilty of conspiracy to destroy national defense property, intent to damage / destroy national defense property (sabotage), and destruction of government property. After he and Coe told the judge they would not promise to return for January 1999 sentencing, their bond was revoked and they were taken into custody.\n\n{$miiiContext}",
                ],
                'cases' => [[
                    'institution_id' => $fdcEnglewood->id,
                    'charges' => 'Conspiracy to destroy national defense property; intent to damage / destroy national defense property (sabotage); destruction of government property — Minuteman III silo N-8, near Greeley, Colorado, August 6, 1998',
                    'arrest_date' => '1998-08-06',
                    'incarceration_date' => '1998-08-15',
                    'sentenced_date' => '1999-01-20',
                    'convicted' => 'Yes — federal jury verdict, U.S. District Court for the District of Colorado',
                    'sentence' => '~30 months federal prison (sentencing January 1999)',
                ]],
            ],
            [
                'data' => [
                    'name' => 'Sachio Oliver Coe', 'first_name' => 'Sachio', 'middle_name' => 'Oliver', 'last_name' => 'Coe',
                    'aka' => 'Oliver Sachio Coe', 'gender' => 'Male', 'race' => 'Asian',
                    'state' => 'California', 'era' => '1990s',
                    'ideologies' => ['Anti-war', 'Anti-nuclear', 'Pacifist'],
                    'affiliation' => ['Atlantic Life Community', 'Minuteman III Plowshares'],
                    'in_custody' => false, 'released' => true, 'inmate_number' => '28361-013',
                    'description' => "Sachio Oliver Coe was one of two members of the Minuteman III Plowshares affinity group that entered Minuteman III intercontinental ballistic missile silo N-8 near Greeley, Colorado on August 6, 1998 — the 53rd anniversary of the U.S. atomic bombing of Hiroshima — and hammered on the silo cover and equipment. He was found guilty of conspiracy to destroy national defense property, sabotage, and destruction of government property. After he and Sicken told the judge they would not promise to return for January 1999 sentencing, their bond was revoked and they were taken into custody.\n\n{$miiiContext}",
                ],
                'cases' => [[
                    'institution_id' => $fdcEnglewood->id,
                    'charges' => 'Conspiracy to destroy national defense property; intent to damage / destroy national defense property (sabotage); destruction of government property — Minuteman III silo N-8, near Greeley, Colorado, August 6, 1998',
                    'arrest_date' => '1998-08-06',
                    'incarceration_date' => '1998-08-15',
                    'sentenced_date' => '1999-01-20',
                    'convicted' => 'Yes — federal jury verdict, U.S. District Court for the District of Colorado',
                    'sentence' => '~30 months federal prison (sentencing January 1999)',
                ]],
            ],

            // ─── Existing prisoners with case backfill (Gods of Metal members) ───
            // Ardeth Platte, Carol Gilbert, Frank Cordaro: already in DB. We attach the
            // Gods of Metal case if they don't have one for that arrest_date.
            [
                'data'      => null,                 // skip create — find-only
                'find_name' => 'Ardeth Platte',
                'cases' => [[
                    'institution_id' => $kentCounty->id,
                    'charges' => 'Damage to government property — disarming of a B-52 bomber at Andrews Air Force Base, Maryland (Gods of Metal Plowshares)',
                    'arrest_date' => '1998-05-17',
                    'incarceration_date' => '1998-05-17',
                    'sentenced_date' => '1999-01-15',
                    'convicted' => 'Yes — federal jury verdict, U.S. District Court for the District of Maryland, September 22, 1998',
                    'sentence' => 'Federal prison time-served plus probation',
                ]],
            ],
            [
                'data'      => null,
                'find_name' => 'Carol Gilbert',
                'cases' => [[
                    'institution_id' => $kentCounty->id,
                    'charges' => 'Damage to government property — disarming of a B-52 bomber at Andrews Air Force Base, Maryland (Gods of Metal Plowshares)',
                    'arrest_date' => '1998-05-17',
                    'incarceration_date' => '1998-05-17',
                    'sentenced_date' => '1999-01-15',
                    'convicted' => 'Yes — federal jury verdict, U.S. District Court for the District of Maryland, September 22, 1998',
                    'sentence' => 'Federal prison time-served plus probation',
                ]],
            ],
            [
                'data'      => null,
                'find_name' => 'Frank Cordaro',
                'cases' => [[
                    'institution_id' => $charlesCounty->id,
                    'charges' => 'Damage to government property — disarming of a B-52 bomber at Andrews Air Force Base, Maryland (Gods of Metal Plowshares)',
                    'arrest_date' => '1998-05-17',
                    'incarceration_date' => '1998-05-17',
                    'sentenced_date' => '1999-01-15',
                    'convicted' => 'Yes — federal jury verdict, U.S. District Court for the District of Maryland, September 22, 1998',
                    'sentence' => 'Federal prison time-served plus probation',
                ]],
            ],
        ];

        $createdPrisoners = 0;
        $updatedPrisoners = 0;
        $createdCases = 0;
        $skippedCases = 0;
        $notFound = 0;

        foreach ($entries as $entry) {
            DB::transaction(function () use ($entry, &$createdPrisoners, &$updatedPrisoners, &$createdCases, &$skippedCases, &$notFound) {
                $name = $entry['data']['name'] ?? $entry['find_name'] ?? null;
                if (! $name) return;

                $prisoner = $this->findPrisoner($name);

                // Find-only entries (existing prisoners we want to attach a case to)
                if ($entry['data'] === null) {
                    if (! $prisoner) {
                        $this->warn("Not in DB (skipping case attach): {$name}");
                        $notFound++;
                        return;
                    }
                } elseif (! $prisoner) {
                    // Create new prisoner
                    $prisoner = Prisoner::create($entry['data']);
                    $this->info("Created prisoner: {$prisoner->name}");
                    $createdPrisoners++;
                } else {
                    // Existing prisoner — fill in any missing inmate_number
                    if (! empty($entry['data']['inmate_number']) && empty($prisoner->inmate_number)) {
                        $prisoner->inmate_number = $entry['data']['inmate_number'];
                        $prisoner->save();
                        $this->line("Patched inmate_number on existing: {$prisoner->name}");
                        $updatedPrisoners++;
                    } else {
                        $this->line("Prisoner exists, no field updates: {$prisoner->name}");
                    }
                }

                foreach ($entry['cases'] ?? [] as $case) {
                    $exists = PrisonerCase::where('prisoner_id', $prisoner->id)
                        ->whereDate('arrest_date', $case['arrest_date'])
                        ->exists();
                    if ($exists) {
                        $this->line("  case for {$case['arrest_date']} already exists — skipping");
                        $skippedCases++;
                        continue;
                    }
                    PrisonerCase::create(array_merge(['prisoner_id' => $prisoner->id], $case));
                    $this->info("  added case: {$case['arrest_date']}");
                    $createdCases++;
                }
            });
        }

        $this->info("\nDone. Prisoners created: {$createdPrisoners}, updated: {$updatedPrisoners}; cases created: {$createdCases}, skipped (existed): {$skippedCases}; not in DB: {$notFound}.");

        return self::SUCCESS;
    }

    private function findPrisoner(string $name): ?Prisoner
    {
        $p = Prisoner::where('name', $name)->first();
        if ($p) return $p;

        // Try aka
        $p = Prisoner::where('aka', $name)->first();
        if ($p) return $p;

        // Curly/straight quote variants
        $variants = [
            str_replace(['"', "'"], ["\u{201C}", "\u{2019}"], $name),
            str_replace(["\u{201C}", "\u{201D}", "\u{2019}", "\u{2018}"], ['"', '"', "'", "'"], $name),
        ];
        foreach ($variants as $v) {
            if ($v === $name) continue;
            $p = Prisoner::where('name', $v)->orWhere('aka', $v)->first();
            if ($p) return $p;
        }

        // LIKE fallback on name and aka
        $needle = '%' . str_replace(['"', "'"], '%', $name) . '%';
        return Prisoner::where('name', 'like', $needle)->orWhere('aka', 'like', $needle)->first();
    }
}
