<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds 24 PPs surfaced from the deep crawl of Wikipedia's "List of
 * protests in the United States" article and its highest-yield
 * sub-pages. Gaps remaining after the prior US-rebellions, NA-conflicts,
 * civil-unrest, and Anti-Rent batches:
 *
 *   - Shays' Rebellion leadership (4): Daniel Shays, Job Shattuck,
 *     Luke Day, Eli Parsons
 *   - Whiskey Rebellion martyrs (2): James McFarlane (KIA Bower Hill),
 *     Oliver Miller (mortally wounded Bower Hill)
 *   - 1898 Wilmington coup d'état (2): Alexander Manly, Frank Manly
 *   - Centralia Massacre 1919 IWW (10): Wesley Everest (lynched);
 *     8 IWW co-defendants convicted in the murder trial of Warren
 *     Grimm — Eugene Barnett, Bert Bland, O.C. Bland, Ray Becker,
 *     John Lamb, Britt Smith, Loren Roberts; plus defense lawyer
 *     Elmer Smith tried as co-conspirator and acquitted
 *   - Matewan / Mine War martyrs (2): Sid Hatfield, Ed Chambers
 *     (assassinated by Baldwin-Felts agents at McDowell County
 *     courthouse, Aug 1, 1921)
 *   - Greensboro Massacre 1979 CWP martyrs (5): Cesar Cauce, James
 *     Waller, William Evan Sampson, Sandra Neely Smith, Michael Nathan
 *
 * Era values per project decade-string convention.
 */
final class AddUsProtestsListPps extends Command {
    protected $signature = 'archive:add-us-protests-list-pps';
    protected $description = 'Add 24 PPs from US-protests-list crawl';

    public function handle(): int {
        $added = 0; $skipped = 0;
        foreach ($this->prisoners() as $p) {
            $exit = $this->call('prisoner:add', ['json' => json_encode($p)]);
            if ($exit === self::SUCCESS) { $this->info('ADD: '.$p['name']); $added++; }
            else { $skipped++; }
        }
        $this->info("Done — added {$added}, skipped {$skipped}.");
        return self::SUCCESS;
    }

    /** @return array<int, array<string, mixed>> */
    private function prisoners(): array {
        $out = [];

        // === Shays' Rebellion 1786-87 — leadership ===
        $shaysBase = [
            'state' => 'Massachusetts',
            'race' => 'White',
            'gender' => 'Male',
            'ideologies' => ['Agrarian populism', 'Anti-debt courts'],
            'affiliation' => ['Shays\' Rebellion'],
            'era' => '1780s',
            'in_custody' => false,
            'released' => true,
        ];
        $out[] = [
            'name' => 'Daniel Shays',
            'first_name' => 'Daniel',
            'last_name' => 'Shays',
            'description' => 'Revolutionary War veteran (captain, 5th Massachusetts Regiment, wounded at Saratoga) and Pelham, MA farmer who led the 1786-87 western Massachusetts uprising of indebted veterans and farmers against state foreclosures, regressive taxes, and creditor courts (Shays\' Rebellion). After the state militia broke the rebels at the Springfield Armory in January 1787, Shays fled to Vermont. Indicted as "disorderly, riotous, and seditious" and sentenced to death in absentia; pardoned by Governor Hancock on June 13, 1788. Returned to Massachusetts and lived out his life in poverty in Sparta, NY.',
            'birthdate' => '1747-08-01',
            'death_date' => '1825-09-29',
            'in_exile' => true,
            'cases' => [[
                'institution_state' => 'Massachusetts',
                'charges' => 'Treason; sedition; armed insurrection (Shays\' Rebellion).',
                'arrest_date' => '1787-02-04',
                'sentenced_date' => '1787-04-01',
                'convicted' => 'Yes (in absentia) — pardoned 1788.',
                'sentence' => 'Death sentence in absentia; fled to Vermont; pardoned by Gov. Hancock June 13, 1788.',
            ]],
        ] + $shaysBase;
        $out[] = [
            'name' => 'Job Shattuck',
            'first_name' => 'Job',
            'last_name' => 'Shattuck',
            'description' => 'Revolutionary War veteran and Groton, MA farmer; one of the principal Shays\' Rebellion captains in eastern Massachusetts. Led the November 1786 closing of the Concord court. Captured by state militia at his farm on November 30, 1786 after being slashed across the leg with a sword by Sheriff Job Lane; the wound left him permanently lame. Convicted of treason and sentenced to death; pardoned by Governor Hancock in 1787.',
            'birthdate' => '1736-09-11',
            'death_date' => '1819-01-13',
            'cases' => [[
                'institution_state' => 'Massachusetts',
                'charges' => 'Treason; armed insurrection (Shays\' Rebellion).',
                'arrest_date' => '1786-11-30',
                'sentenced_date' => '1787-04-09',
                'convicted' => 'Yes — pardoned 1787.',
                'sentence' => 'Death sentence; pardoned by Gov. Hancock 1787.',
            ]],
        ] + $shaysBase;
        $out[] = [
            'name' => 'Luke Day',
            'first_name' => 'Luke',
            'last_name' => 'Day',
            'description' => 'Revolutionary War veteran (captain, Massachusetts Continentals) and West Springfield, MA farmer; co-leader of Shays\' Rebellion in the Connecticut River valley. Commanded the western wing of the rebel attempt to seize the Springfield Armory on January 25, 1787. After the rout, fled to Vermont and then New York to avoid capital prosecution. Pardoned with the other rebellion leaders.',
            'birthdate' => '1743-07-25',
            'death_date' => '1801-05-03',
            'in_exile' => true,
            'cases' => [[
                'institution_state' => 'Massachusetts',
                'charges' => 'Treason; armed insurrection (Shays\' Rebellion).',
                'arrest_date' => '1787-01-26',
                'sentenced_date' => '1787-04-01',
                'convicted' => 'Yes (in absentia) — later pardoned.',
                'sentence' => 'Death sentence in absentia; fled to Vermont; pardoned.',
            ]],
        ] + $shaysBase;
        $out[] = [
            'name' => 'Eli Parsons',
            'first_name' => 'Eli',
            'last_name' => 'Parsons',
            'description' => 'Adams, MA Revolutionary War veteran and Shays\' Rebellion co-commander; led the northern Berkshire contingent in the January 1787 attempt on the Springfield Armory. After the militia broke the rebels, Parsons fled to Vermont. Sentenced to death in absentia for treason; later pardoned.',
            'in_exile' => true,
            'cases' => [[
                'institution_state' => 'Massachusetts',
                'charges' => 'Treason; armed insurrection (Shays\' Rebellion).',
                'arrest_date' => '1787-01-26',
                'sentenced_date' => '1787-04-01',
                'convicted' => 'Yes (in absentia) — later pardoned.',
                'sentence' => 'Death sentence in absentia; fled to Vermont; pardoned.',
            ]],
        ] + $shaysBase;

        // === Whiskey Rebellion 1794 — Bower Hill martyrs ===
        $whiskeyBase = [
            'state' => 'Pennsylvania',
            'race' => 'White',
            'gender' => 'Male',
            'ideologies' => ['Anti-tax', 'Agrarian populism'],
            'affiliation' => ['Whiskey Rebellion'],
            'era' => '1790s',
            'in_custody' => false,
            'released' => false,
        ];
        $out[] = [
            'name' => 'James McFarlane',
            'first_name' => 'James',
            'last_name' => 'McFarlane',
            'description' => 'Revolutionary War major (4th Pennsylvania Battalion) and Mingo Creek militia commander during the Whiskey Rebellion. Led the rebel force of roughly 600 western Pennsylvania farmers that besieged federal tax inspector John Neville\'s fortified home at Bower Hill on July 17, 1794. Killed by a Neville-side sniper during a parley flag; his death enraged the rebels, who then burned Bower Hill, and was the proximate trigger for Washington\'s federalization of 13,000 militia and the Insurrection Act invocation that crushed the rebellion. Martyred at age 43.',
            'death_date' => '1794-07-17',
            'cases' => [[
                'institution_state' => 'Pennsylvania',
                'charges' => 'Armed insurrection (Whiskey Rebellion); killed by federal-side fire during siege of Bower Hill.',
                'arrest_date' => '1794-07-17',
                'sentence' => 'Killed in action at Bower Hill, July 17, 1794 (martyr).',
            ]],
        ] + $whiskeyBase;
        $out[] = [
            'name' => 'Oliver Miller',
            'first_name' => 'Oliver',
            'last_name' => 'Miller',
            'description' => 'Western Pennsylvania farmer and Whiskey Rebellion participant. Mortally wounded by federal-tax-inspector John Neville on July 16, 1794 in the opening confrontation of the Bower Hill siege, the day before James McFarlane was killed at the same site. His death and McFarlane\'s catalyzed the federal march into western PA.',
            'death_date' => '1794-07-17',
            'cases' => [[
                'institution_state' => 'Pennsylvania',
                'charges' => 'Armed insurrection (Whiskey Rebellion); shot by federal tax inspector John Neville at Bower Hill.',
                'arrest_date' => '1794-07-16',
                'sentence' => 'Mortally wounded at Bower Hill, died July 17, 1794 (martyr).',
            ]],
        ] + $whiskeyBase;

        // === 1898 Wilmington coup d'état — exiled Manly brothers ===
        $manlyDesc = 'Co-publisher of the Wilmington Daily Record, North Carolina\'s only Black-owned daily newspaper. Driven into exile on November 10, 1898 when the white-supremacist coup d\'état against Wilmington\'s elected Fusionist (Republican / Populist / Black) government — the only successful coup in U.S. history — was launched in part as retaliation for the paper\'s August 1898 editorial defending interracial relationships. The Daily Record office was burned by the white mob; the Manly brothers had fled north days earlier after receiving warnings. As many as 60 Black residents were killed and roughly 2,000 driven into permanent exile; the Manlys never returned to North Carolina.';
        $manlyBase = [
            'state' => 'North Carolina',
            'race' => 'Black',
            'gender' => 'Male',
            'ideologies' => ['Black freedom', 'Black press'],
            'affiliation' => ['Wilmington Daily Record'],
            'era' => '1890s',
            'in_custody' => false,
            'released' => true,
            'in_exile' => true,
        ];
        $out[] = [
            'name' => 'Alexander Manly',
            'aka' => 'Alex Manly',
            'first_name' => 'Alexander',
            'last_name' => 'Manly',
            'description' => $manlyDesc.' Alexander was the editor whose August 18, 1898 editorial — responding to a speech by Rebecca Latimer Felton calling for the lynching of Black men accused of touching white women — was singled out as the coup\'s pretext.',
            'birthdate' => '1866-05-13',
            'death_date' => '1944-09-30',
            'cases' => [[
                'institution_state' => 'North Carolina',
                'charges' => 'No formal charges — driven from Wilmington by white-supremacist coup.',
                'arrest_date' => '1898-11-10',
                'sentence' => 'Permanently exiled from North Carolina; newspaper office burned by white mob.',
            ]],
        ] + $manlyBase;
        $out[] = [
            'name' => 'Frank Manly',
            'first_name' => 'Frank',
            'last_name' => 'Manly',
            'description' => $manlyDesc.' Frank co-managed the paper\'s business operations.',
            'cases' => [[
                'institution_state' => 'North Carolina',
                'charges' => 'No formal charges — driven from Wilmington by white-supremacist coup.',
                'arrest_date' => '1898-11-10',
                'sentence' => 'Permanently exiled from North Carolina; newspaper office burned by white mob.',
            ]],
        ] + $manlyBase;

        // === Centralia Massacre 1919 — IWW ===
        $centraliaDesc = 'Member of the Industrial Workers of the World (IWW) defending the Centralia, Washington IWW union hall against the American Legion Armistice Day parade attack of November 11, 1919. After American Legion marchers diverted the parade to attack the IWW hall, IWW members inside fired in self-defense, killing four Legion members. The IWW response and the subsequent lynching of fellow Wobbly Wesley Everest that night became known as the Centralia Massacre. Local IWW members were rounded up and tried for the murder of Legion commander Warren Grimm in a trial broadly considered a frame-up — defense witnesses were intimidated, jurors later signed affidavits regretting their verdicts, and Elmer Smith (the IWW\'s defense lawyer, prosecuted as a co-conspirator) was disbarred.';
        $centraliaCase = [
            'institution_name' => 'Walla Walla State Penitentiary',
            'institution_state' => 'Washington',
            'charges' => 'Second-degree murder of American Legion commander Warren O. Grimm (Centralia Massacre, November 11, 1919).',
            'arrest_date' => '1919-11-11',
            'sentenced_date' => '1920-04-05',
            'convicted' => 'Yes — widely regarded as a frame-up; jurors later signed affidavits of regret.',
            'sentence' => '25-40 years at Walla Walla.',
        ];
        $centraliaBase = [
            'state' => 'Washington',
            'race' => 'White',
            'gender' => 'Male',
            'ideologies' => ['Labor', 'Syndicalist', 'IWW'],
            'affiliation' => ['Industrial Workers of the World'],
            'era' => '1910s',
            'in_custody' => false,
            'released' => true,
        ];
        $out[] = [
            'name' => 'Wesley Everest',
            'first_name' => 'Wesley',
            'last_name' => 'Everest',
            'description' => 'IWW logger and U.S. Army WWI veteran (4th Engineers, France). Defended the Centralia, WA IWW union hall during the American Legion attack of November 11, 1919; pursued by the mob, captured at the river, and held in the Centralia city jail. That night the city power was cut, the jail was stormed by a masked mob (widely understood to include Legion members), and Everest was castrated, lynched from the Mellen Street bridge, and shot. No one was ever prosecuted for his murder.',
            'birthdate' => '1890-01-01',
            'death_date' => '1919-11-11',
            'cases' => [[
                'institution_state' => 'Washington',
                'charges' => 'Held in Centralia city jail after defending IWW hall.',
                'arrest_date' => '1919-11-11',
                'sentence' => 'Lynched from Mellen Street bridge by masked mob, November 11, 1919 (martyr).',
            ]],
        ] + $centraliaBase;
        foreach ([
            ['Eugene Barnett',  null, 'Cumberland-born coal miner and IWW member.', '25-40 years; paroled 1931.'],
            ['Bert Bland',      null, 'IWW timber worker; brother of O.C. Bland.', '25-40 years; paroled 1932.'],
            ['O.C. Bland',      null, 'IWW timber worker; brother of Bert Bland.', '25-40 years; paroled 1932.'],
            ['Ray Becker',      null, 'IWW member who refused parole on principle; served the longest of the Centralia defendants.', '25-40 years; sentence commuted 1939 after 19 years.'],
            ['John Lamb',       null, 'IWW logger.', '25-40 years; pardoned 1933.'],
            ['Britt Smith',     null, 'Secretary of the Centralia IWW hall.', '25-40 years; pardoned 1933.'],
            ['Loren Roberts',   null, 'IWW member; the jury returned a verdict of not guilty by reason of insanity, but he was nonetheless committed and not released until 1930.', 'Committed (NGRI); released 1930.'],
        ] as [$name, $birth, $bio, $sentence]) {
            $parts = preg_split('/\s+/', $name);
            $case = $centraliaCase;
            $case['sentence'] = $sentence;
            $out[] = [
                'name' => $name,
                'first_name' => $parts[0],
                'last_name' => end($parts),
                'description' => $centraliaDesc.' '.$bio,
                'cases' => [$case],
            ] + $centraliaBase;
        }
        $out[] = [
            'name' => 'Elmer Smith',
            'first_name' => 'Elmer',
            'last_name' => 'Smith',
            'description' => 'Centralia, WA attorney and the IWW\'s local defense lawyer. After the Armistice Day 1919 raid on the Centralia IWW hall, Smith was charged alongside the IWW defendants as a co-conspirator in the murder of American Legion commander Warren Grimm. Acquitted at trial but disbarred for his IWW work. Spent the rest of his life campaigning for the release of the convicted Centralia defendants until his death in 1932.',
            'state' => 'Washington',
            'race' => 'White',
            'gender' => 'Male',
            'birthdate' => '1888-01-01',
            'death_date' => '1932-09-25',
            'ideologies' => ['Labor', 'IWW', 'Civil liberties'],
            'affiliation' => ['Industrial Workers of the World'],
            'era' => '1910s',
            'in_custody' => false,
            'released' => true,
            'cases' => [[
                'institution_state' => 'Washington',
                'charges' => 'Co-conspirator to second-degree murder of Warren Grimm (Centralia Massacre).',
                'arrest_date' => '1919-11-11',
                'sentenced_date' => '1920-04-05',
                'convicted' => 'No — acquitted at trial; nonetheless disbarred for IWW work.',
                'sentence' => 'Acquitted; disbarred.',
            ]],
        ];

        // === Matewan / Mine Wars — Sid Hatfield & Ed Chambers assassinated 1921 ===
        $matewanBase = [
            'state' => 'West Virginia',
            'race' => 'White',
            'gender' => 'Male',
            'ideologies' => ['Labor', 'UMWA'],
            'affiliation' => ['United Mine Workers of America', 'Matewan defenders'],
            'era' => '1920s',
            'in_custody' => false,
            'released' => false,
        ];
        $out[] = [
            'name' => 'Sid Hatfield',
            'first_name' => 'Sid',
            'last_name' => 'Hatfield',
            'description' => 'Police chief of Matewan, West Virginia, and pro-UMWA hero of the May 19, 1920 Battle of Matewan — when Hatfield, Mayor Cabell Testerman, and union miners confronted Baldwin-Felts Detective Agency gunmen who had been evicting striking miner families from company-owned housing. In the ensuing gunfight 7 Baldwin-Felts men and 4 townspeople (including Mayor Testerman) were killed. Hatfield was charged with the murder of Albert Felts; acquitted. On August 1, 1921, while walking up the steps of the McDowell County courthouse in Welch, WV to answer additional shooting charges, Hatfield and his friend Ed Chambers were assassinated by Baldwin-Felts agents — both unarmed, both shot in front of their wives. The murders triggered the Battle of Blair Mountain. None of the Baldwin-Felts shooters were convicted.',
            'birthdate' => '1893-05-15',
            'death_date' => '1921-08-01',
            'cases' => [[
                'institution_state' => 'West Virginia',
                'charges' => 'Murder of Baldwin-Felts agent Albert Felts (Battle of Matewan, May 19, 1920); additional shooting charges pending at time of death.',
                'arrest_date' => '1920-05-19',
                'sentenced_date' => '1921-03-01',
                'convicted' => 'No — acquitted in Matewan prosecution.',
                'sentence' => 'Acquitted at Matewan trial; assassinated by Baldwin-Felts agents on McDowell County courthouse steps Aug 1, 1921 (martyr).',
            ]],
        ] + $matewanBase;
        $out[] = [
            'name' => 'Ed Chambers',
            'first_name' => 'Ed',
            'last_name' => 'Chambers',
            'description' => 'Matewan, WV resident and close ally of Police Chief Sid Hatfield in the 1920-21 West Virginia coal wars. Co-defendant with Hatfield in the Battle of Matewan murder prosecution (Aug 19, 1920); acquitted. Assassinated alongside Hatfield by Baldwin-Felts Detective Agency gunmen on the steps of the McDowell County courthouse in Welch, WV on August 1, 1921 as both were arriving unarmed with their wives to answer pending charges. The assassinations were a major trigger of the Battle of Blair Mountain.',
            'death_date' => '1921-08-01',
            'cases' => [[
                'institution_state' => 'West Virginia',
                'charges' => 'Murder co-defendant (Battle of Matewan).',
                'arrest_date' => '1920-05-19',
                'sentenced_date' => '1921-03-01',
                'convicted' => 'No — acquitted in Matewan prosecution.',
                'sentence' => 'Acquitted at Matewan trial; assassinated by Baldwin-Felts agents Aug 1, 1921 (martyr).',
            ]],
        ] + $matewanBase;

        // === Greensboro Massacre 1979 — CWP martyrs ===
        $greensboroDesc = 'Member of the Communist Workers Party (CWP) killed in the Greensboro Massacre of November 3, 1979 — when a caravan of KKK and American Nazi Party members opened fire on a CWP-organized "Death to the Klan" rally in the Morningside Homes housing project in Greensboro, North Carolina. The attack was filmed by four television crews; the police presence was deliberately withdrawn beforehand, and an ATF informant inside the Klan caravan and a Greensboro Police Department informant had advance knowledge. The 14 Klan/Nazi defendants in the two state and federal criminal trials were acquitted by all-white juries; a 1985 civil verdict found Greensboro Police, the KKK, and the American Nazi Party jointly liable for the deaths.';
        $greensboroBase = [
            'state' => 'North Carolina',
            'ideologies' => ['Communist', 'Anti-Klan', 'Labor'],
            'affiliation' => ['Communist Workers Party'],
            'era' => '1970s',
            'in_custody' => false,
            'released' => false,
        ];
        $out[] = [
            'name' => 'Cesar Cauce',
            'first_name' => 'Cesar',
            'last_name' => 'Cauce',
            'description' => $greensboroDesc.' Cauce was a Cuban-born Duke University graduate, ACTWU/CWP organizer at Duke Medical Center, and the first of the five killed at Morningside Homes.',
            'race' => 'Latinx',
            'gender' => 'Male',
            'birthdate' => '1953-04-01',
            'death_date' => '1979-11-03',
            'cases' => [[
                'institution_state' => 'North Carolina',
                'charges' => 'No charges — victim of armed attack on the "Death to the Klan" rally.',
                'arrest_date' => '1979-11-03',
                'sentence' => 'Killed by KKK/Nazi gunfire at Morningside Homes, November 3, 1979 (martyr).',
            ]],
        ] + $greensboroBase;
        $out[] = [
            'name' => 'James Waller',
            'first_name' => 'James',
            'last_name' => 'Waller',
            'description' => $greensboroDesc.' Waller was a Harvard-trained MD who took a textile-mill job and was elected president of ACTWU Local 1113T at Cone Mills\' Granite Finishing plant; killed at the rally.',
            'race' => 'White',
            'gender' => 'Male',
            'birthdate' => '1942-08-25',
            'death_date' => '1979-11-03',
            'cases' => [[
                'institution_state' => 'North Carolina',
                'charges' => 'No charges — victim of armed attack on the "Death to the Klan" rally.',
                'arrest_date' => '1979-11-03',
                'sentence' => 'Killed by KKK/Nazi gunfire at Morningside Homes, November 3, 1979 (martyr).',
            ]],
        ] + $greensboroBase;
        $out[] = [
            'name' => 'William Evan Sampson',
            'aka' => 'Bill Sampson',
            'first_name' => 'William',
            'middle_name' => 'Evan',
            'last_name' => 'Sampson',
            'description' => $greensboroDesc.' Sampson was a Harvard Divinity School graduate who took a textile-mill job; an ACTWU/CWP organizer at Cone Mills\' White Oak plant. Killed at the rally.',
            'race' => 'White',
            'gender' => 'Male',
            'birthdate' => '1948-12-13',
            'death_date' => '1979-11-03',
            'cases' => [[
                'institution_state' => 'North Carolina',
                'charges' => 'No charges — victim of armed attack on the "Death to the Klan" rally.',
                'arrest_date' => '1979-11-03',
                'sentence' => 'Killed by KKK/Nazi gunfire at Morningside Homes, November 3, 1979 (martyr).',
            ]],
        ] + $greensboroBase;
        $out[] = [
            'name' => 'Sandra Neely Smith',
            'aka' => 'Sandi',
            'first_name' => 'Sandra',
            'middle_name' => 'Neely',
            'last_name' => 'Smith',
            'description' => $greensboroDesc.' Smith was a Bennett College alumna and former student-government president, ACTWU/CWP organizer at Revolution Mills in Greensboro. The only Black person and the only woman of the five killed at the rally.',
            'race' => 'Black',
            'gender' => 'Female',
            'birthdate' => '1950-09-01',
            'death_date' => '1979-11-03',
            'cases' => [[
                'institution_state' => 'North Carolina',
                'charges' => 'No charges — victim of armed attack on the "Death to the Klan" rally.',
                'arrest_date' => '1979-11-03',
                'sentence' => 'Killed by KKK/Nazi gunfire at Morningside Homes, November 3, 1979 (martyr).',
            ]],
        ] + $greensboroBase;
        $out[] = [
            'name' => 'Michael Nathan',
            'first_name' => 'Michael',
            'last_name' => 'Nathan',
            'description' => $greensboroDesc.' Nathan was a pediatrician at Lincoln Community Health Center in Durham and chief of pediatrics there; supported CWP organizing and attended the rally as a CWP supporter and medic. Killed at the rally.',
            'race' => 'White',
            'gender' => 'Male',
            'birthdate' => '1947-12-10',
            'death_date' => '1979-11-03',
            'cases' => [[
                'institution_state' => 'North Carolina',
                'charges' => 'No charges — victim of armed attack on the "Death to the Klan" rally.',
                'arrest_date' => '1979-11-03',
                'sentence' => 'Killed by KKK/Nazi gunfire at Morningside Homes, November 3, 1979 (martyr).',
            ]],
        ] + $greensboroBase;

        return $out;
    }
}
