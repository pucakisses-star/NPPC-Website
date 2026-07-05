<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * New Masses mining, batch 8 — 1940.
 *
 * 1940 was the peak of the pre-war red scare: the Smith (Alien Registration)
 * and Voorhis Acts, the election-year drive to keep the Communist Party off
 * the ballot, the Dies Committee, and a nationwide wave of criminal-syndicalism
 * and "treason" prosecutions. New Masses documented it in unusual density, so
 * this is the largest of the post-Labor-Defender batches. The famous figures
 * are already in the database (Earl Browder, Sam Darcy, William L. Patterson,
 * Philip Frankfeld, Ben Gold, Irving Potash, Clarence Hathaway, Louis Budenz,
 * Jack Schneider, J.B. McNamara, the Scottsboro defendants) and are skipped.
 *
 * This adds the genuinely-new US class-war prisoners of 1940: the Oklahoma City
 * criminal-syndicalism defendants; the Lewistown/Fulton County Illinois
 * "treason" petition-solicitors; other ballot-petition and anti-war arrests
 * (Pekin IL, the University of Illinois, West Virginia); a Visalia CA contempt
 * case; the Birmingham Alabama arrests; New York cases (the CP treasurer, the
 * IBEW Local 3 leader, a framed Brooklyn defendant); the Detroit Spanish-
 * recruiter raid; a Massachusetts Dies-contempt case; a Chicago ILD contempt
 * case; a Pennsylvania criminal-libel jailing; a Texas shrimp-workers' frame-up;
 * and a South Carolina NAACP chain-gang case.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddNewMasses1940Cases extends Command
{
    protected $signature = 'prisoners:add-new-masses-1940';

    protected $description = 'Add the genuinely-new US class-war prisoners surfaced mining New Masses 1940 (the Oklahoma City criminal-syndicalism case, the Illinois "treason" petition prosecutions, and the wider Smith/Voorhis-Act repression wave)';

    public function handle(): int
    {
        $people = [];
        $mk = function (array $p, array $dates = []) use (&$people) {
            $people[] = ['payload' => $p, 'dates' => $dates];
        };

        // ── OKLAHOMA CITY CRIMINAL-SYNDICALISM CASE (Aug 1940) ──────────
        $okBase = "in the August 1940 Oklahoma City criminal-syndicalism raids on the Communist Party, the Workers Alliance and the Progressive Bookstore — a landmark prosecution under Oklahoma's criminal-syndicalism law fought by the International Labor Defense.";
        $mk([
            'name' => 'Robert Wood', 'first_name' => 'Robert', 'last_name' => 'Wood',
            'description' => "Robert 'Bob' Wood was the Oklahoma state secretary of the Communist Party and the first defendant tried {$okBase} Arrested on August 17, 1940 for the Marxist literature at his Progressive Bookstore, he was convicted and given the maximum sentence of ten years plus a \$5,000 fine, held on bail originally set at \$100,000.",
            'state' => 'Oklahoma', 'gender' => 'Male',
            'ideologies' => ['Communism'], 'affiliation' => ['Communist Party'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Convicted under the Oklahoma criminal-syndicalism law for Communist literature and organizing.',
                'convicted' => 'Convicted, 1940', 'sentence' => 'Ten years plus a $5,000 fine (on appeal).',
                'institution_city' => 'Oklahoma City', 'institution_state' => 'Oklahoma',
            ]],
        ], ['arrest_date' => [1940, 8, 17]]);

        $mk([
            'name' => 'Ina Wood', 'first_name' => 'Ina', 'last_name' => 'Wood',
            'description' => "Ina Wood, a Communist organizer and the wife of Oklahoma CP secretary Robert Wood, was arrested and arraigned alongside her husband on criminal-syndicalism charges {$okBase}",
            'state' => 'Oklahoma', 'gender' => 'Female',
            'ideologies' => ['Communism'], 'affiliation' => ['Communist Party'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arraigned on criminal-syndicalism charges in the Oklahoma City raids.',
                'convicted' => 'Arrested, 1940', 'sentence' => 'Held for trial.',
                'institution_city' => 'Oklahoma City', 'institution_state' => 'Oklahoma',
            ]],
        ], ['arrest_date' => [1940, 8, null]]);

        $mk([
            'name' => 'Eli Jaffe', 'first_name' => 'Eli', 'last_name' => 'Jaffe',
            'description' => "Eli Jaffe was a Communist and farm organizer (and a New Masses contributor) jailed in the Oklahoma County Jail and arraigned on criminal syndicalism as one of the defendants {$okBase} He faced up to twenty years with bail set at \$50,000, and reported being repeatedly beaten in his cell; his case was raised in Congress by Rep. Vito Marcantonio.",
            'state' => 'Oklahoma', 'gender' => 'Male',
            'ideologies' => ['Communism'], 'affiliation' => ['Communist Party'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arraigned on criminal syndicalism; beaten in the Oklahoma County Jail.',
                'convicted' => 'Held, 1940', 'sentence' => 'Held on $50,000 bail, facing up to 20 years.',
                'institution_name' => 'Oklahoma County Jail',
                'institution_city' => 'Oklahoma City', 'institution_state' => 'Oklahoma',
            ]],
        ], ['arrest_date' => [1940, 8, null]]);

        $mk([
            'name' => 'Elizabeth Green', 'first_name' => 'Elizabeth', 'last_name' => 'Green',
            'description' => "Elizabeth Green was an Oklahoma unemployed-workers leader arrested and arraigned on criminal-syndicalism charges {$okBase}",
            'state' => 'Oklahoma', 'gender' => 'Female',
            'ideologies' => ['Communism'], 'affiliation' => ['Workers Alliance of America'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arraigned on criminal-syndicalism charges in the Oklahoma City raids.',
                'convicted' => 'Arrested, 1940', 'sentence' => 'Held for trial.',
                'institution_city' => 'Oklahoma City', 'institution_state' => 'Oklahoma',
            ]],
        ], ['arrest_date' => [1940, 8, null]]);

        $mk([
            'name' => 'Otis Nation', 'first_name' => 'Otis', 'last_name' => 'Nation',
            'description' => "Otis Nation was a UCAPAWA / Oklahoma Tenant Farmers Union organizer — whom New Masses called 'our own Tom Joad' — jailed in the 1940 Oklahoma City repression, arrested for meeting with the Communist Party's city secretary as part of the criminal-syndicalism crackdown.",
            'state' => 'Oklahoma', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'], 'affiliation' => ['United Cannery, Agricultural, Packing & Allied Workers of America'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed in the Oklahoma City criminal-syndicalism crackdown.',
                'convicted' => 'Arrested, 1940', 'sentence' => 'Jailed.',
                'institution_city' => 'Oklahoma City', 'institution_state' => 'Oklahoma',
            ]],
        ], ['arrest_date' => [1940, 8, null]]);

        $mk([
            'name' => 'Alan Shaw', 'first_name' => 'Alan', 'last_name' => 'Shaw',
            'description' => "Alan Shaw was the 22-year-old Oklahoma City secretary of the Communist Party, arrested in August 1940 and held in the Oklahoma County Jail on \$20,000 bond — spending his 22nd birthday in jail. Tried in December 1940, he was convicted and sentenced to ten years plus a \$5,000 fine under the criminal-syndicalism law.",
            'state' => 'Oklahoma', 'gender' => 'Male',
            'ideologies' => ['Communism'], 'affiliation' => ['Communist Party'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Convicted under the Oklahoma criminal-syndicalism law.',
                'convicted' => 'Convicted, 1940', 'sentence' => 'Ten years plus a $5,000 fine.',
                'institution_name' => 'Oklahoma County Jail',
                'institution_city' => 'Oklahoma City', 'institution_state' => 'Oklahoma',
            ]],
        ], ['arrest_date' => [1940, 8, null]]);

        // ── ILLINOIS — FULTON COUNTY "TREASON" PETITION PROSECUTIONS ────
        $ilBase = "was jailed at Lewistown (Fulton County), Illinois in July 1940 and charged with two counts of 'treason' under the state's revived criminal-syndicalism law for canvassing Communist ballot-petition signatures — part of a sweep of over a hundred Communists across nineteen Illinois counties, with a combined \$80,000 bail and penalties of one to ten years.";
        foreach ([
            ['Mary Wilson', 'Mary', 'Wilson', 'Female', 'a Communist Party election-petition solicitor'],
            ['Jane Curtiss', 'Jane', 'Curtiss', 'Female', 'a Communist Party petition solicitor'],
            ['George Gibbs', 'George', 'Gibbs', 'Male', 'a Communist Party petition solicitor'],
            ['Ira Silbar', 'Ira', 'Silbar', 'Male', 'the defense attorney for the three petition solicitors, himself jailed alongside his clients and'],
        ] as [$name, $first, $last, $gender, $role]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$role} who {$ilBase}",
                'state' => 'Illinois', 'gender' => $gender,
                'ideologies' => ['Communism'], 'affiliation' => ['Communist Party'],
                'era' => '1940s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => "Charged with 'treason' under the Illinois criminal-syndicalism law for gathering Communist ballot signatures.",
                    'convicted' => 'Jailed, 1940', 'sentence' => 'Held; faced one to ten years.',
                    'institution_city' => 'Lewistown', 'institution_state' => 'Illinois',
                ]],
            ], ['arrest_date' => [1940, 7, null]]);
        }

        // ── ILLINOIS — PEKIN AND UNIVERSITY OF ILLINOIS ─────────────────
        $mk([
            'name' => 'John Leslie', 'first_name' => 'John', 'last_name' => 'Leslie',
            'description' => "John Leslie headed the Chicago Communist Party delegation arrested and jailed by Sheriff Guy Donahue at Pekin (Tazewell County), Illinois on May 25, 1940 while collecting Communist ballot-petition signatures. The delegation was nearly lynched by an American Legion mob and their cars burned; Leslie gave the sworn affidavit describing the jail mistreatment.",
            'state' => 'Illinois', 'gender' => 'Male',
            'ideologies' => ['Communism'], 'affiliation' => ['Communist Party'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed while collecting Communist ballot-petition signatures at Pekin.',
                'convicted' => 'Jailed, 1940', 'sentence' => 'Held; delegation attacked by a Legion mob.',
                'institution_city' => 'Pekin', 'institution_state' => 'Illinois',
            ]],
        ], ['arrest_date' => [1940, 5, 25]]);

        $mk([
            'name' => 'Aaron Bindman', 'first_name' => 'Aaron', 'last_name' => 'Bindman',
            'description' => "Aaron Bindman was a student at the University of Illinois at Champaign who was arrested and beaten by police in 1940 for delivering an anti-war street speech, part of the wartime crackdown on Communist and anti-war organizing.",
            'state' => 'Illinois', 'gender' => 'Male',
            'ideologies' => ['Anti-war', 'Communism'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested for an anti-war street speech.',
                'convicted' => 'Arrested, 1940', 'sentence' => 'Held and beaten by police.',
                'institution_city' => 'Champaign', 'institution_state' => 'Illinois',
            ]],
        ], ['arrest_date' => [1940, null, null]]);

        // ── WEST VIRGINIA — BALLOT-PETITION PROSECUTION ─────────────────
        $mk([
            'name' => 'Oscar Wheeler', 'first_name' => 'Oscar', 'last_name' => 'Wheeler',
            'description' => "Oscar O. Wheeler was the Communist Party's candidate for governor of West Virginia, sentenced to six to fifteen years in prison on charges of fraud in the collection of Communist nominating-petition signatures in Raleigh County — part of the 1940 election-year drive to keep the party off the ballot. Arrested on the highway to Charleston, he was jailed on \$5,000 bail.",
            'state' => 'West Virginia', 'gender' => 'Male',
            'ideologies' => ['Communism'], 'affiliation' => ['Communist Party'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Convicted of fraud over Communist nominating-petition signatures.',
                'convicted' => 'Convicted, 1940', 'sentence' => 'Six to fifteen years.',
                'institution_city' => 'Beckley', 'institution_state' => 'West Virginia',
            ]],
        ], ['arrest_date' => [1940, null, null]]);

        // ── CALIFORNIA — VISALIA CONTEMPT ───────────────────────────────
        $mk([
            'name' => 'Joseph Zukas', 'first_name' => 'Joseph', 'last_name' => 'Zukas',
            'description' => "Joseph Zukas was financial secretary of a State, County & Municipal Workers of America (SCMWA-CIO) local in Visalia, California, convicted of 'contempt of committee' for refusing to hand union dues records to the Yorty state 'little Dies' committee and sentenced to sixty days in jail plus a \$100 fine.",
            'state' => 'California', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'], 'affiliation' => ['State, County & Municipal Workers of America'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Convicted of 'contempt of committee' for refusing to surrender union records.",
                'convicted' => 'Convicted, 1940', 'sentence' => 'Sixty days plus a $100 fine.',
                'institution_city' => 'Visalia', 'institution_state' => 'California',
            ]],
        ], ['arrest_date' => [1940, null, null]]);

        // ── ALABAMA — BIRMINGHAM ────────────────────────────────────────
        $mk([
            'name' => 'Joseph Gelders', 'first_name' => 'Joseph', 'last_name' => 'Gelders',
            'description' => "Joseph Gelders was a physicist-turned-organizer associated with the Southern Conference for Human Welfare, arrested in Birmingham, Alabama in 1940 as a 'vagrant' for distributing leaflets challenging the city's Ordinance 4902, which authorized warrantless arrest of 'suspicious' persons. A prominent Southern labor and civil-liberties activist, he had earlier survived a notorious 1936 kidnapping and flogging by anti-union vigilantes.",
            'state' => 'Alabama', 'gender' => 'Male',
            'ideologies' => ['Civil liberties', 'Labor organizing'],
            'affiliation' => ['Southern Conference for Human Welfare'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Arrested as a 'vagrant' for leafleting against Birmingham's warrantless-arrest ordinance.",
                'convicted' => 'Arrested, 1940', 'sentence' => 'Held.',
                'institution_city' => 'Birmingham', 'institution_state' => 'Alabama',
            ]],
        ], ['arrest_date' => [1940, null, null]]);

        $mk([
            'name' => 'Laurent Frantz', 'first_name' => 'Laurent', 'last_name' => 'Frantz',
            'description' => "Laurent Frantz was a Birmingham, Alabama attorney for the Communist Party who was illegally arrested and held incommunicado by federal agents working with the Dies Committee in 1940. He went on to a career as a civil-liberties lawyer and legal scholar.",
            'state' => 'Alabama', 'gender' => 'Male',
            'ideologies' => ['Civil liberties'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Illegally arrested and held incommunicado by federal agents.',
                'convicted' => 'Held, 1940', 'sentence' => 'Held incommunicado.',
                'institution_city' => 'Birmingham', 'institution_state' => 'Alabama',
            ]],
        ], ['arrest_date' => [1940, null, null]]);

        // ── NEW YORK ────────────────────────────────────────────────────
        $mk([
            'name' => 'William Weiner', 'first_name' => 'William', 'last_name' => 'Weiner',
            'description' => "William Weiner was the Communist Party USA's national treasurer, convicted in February 1940 on a passport-fraud technicality — the jury deliberating about twenty-five minutes — the same class of charge used to imprison Earl Browder. The U.S. Supreme Court agreed to review his case alongside Browder's; the left argued he was effectively convicted of being a Communist.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Communism'], 'affiliation' => ['Communist Party'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Convicted on a passport-fraud technicality (the Browder-type prosecution).',
                'convicted' => 'Convicted, 1940', 'sentence' => 'Convicted; case reviewed by the Supreme Court.',
                'institution_city' => 'New York', 'institution_state' => 'New York',
            ]],
        ], ['arrest_date' => [1940, 2, null]]);

        $mk([
            'name' => 'Harry Van Arsdale', 'first_name' => 'Harry', 'last_name' => 'Van Arsdale',
            'description' => "Harry Van Arsdale was the business manager of Local 3 of the International Brotherhood of Electrical Workers in New York City, arrested in 1940 with four other union officers on a 'riot' charge revived from a 1794 statute during a labor dispute.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'], 'affiliation' => ['IBEW Local 3'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Arrested on a 'riot' charge revived from a 1794 statute during a labor dispute.",
                'convicted' => 'Arrested, 1940', 'sentence' => 'Held.',
                'institution_city' => 'New York', 'institution_state' => 'New York',
            ]],
        ], ['arrest_date' => [1940, null, null]]);

        $mk([
            'name' => 'John Williams', 'first_name' => 'John', 'last_name' => 'Williams',
            'description' => "John Williams was a 23-year-old Black worker framed on a rape charge in the Brooklyn, New York neighborhood where he had long lived. He was convicted and sentenced to seven to fifteen years in Sing Sing — the sentencing judge remarking 'I wish I could give him life!' — with the International Labor Defense taking up an appeal.",
            'state' => 'New York', 'gender' => 'Male', 'race' => 'Black',
            'ideologies' => [],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Framed on a rape charge in Brooklyn.',
                'convicted' => 'Convicted, 1940', 'sentence' => 'Seven to fifteen years in Sing Sing.',
                'institution_name' => 'Sing Sing', 'institution_state' => 'New York',
            ]],
        ], ['arrest_date' => [1940, null, null]]);

        // ── MICHIGAN — DETROIT SPANISH-RECRUITER RAID ───────────────────
        $mk([
            'name' => 'Mary Paige', 'first_name' => 'Mary', 'last_name' => 'Paige',
            'description' => "Mary Paige was a young American-born Detroit woman arrested in a dawn FBI raid on February 6, 1940, one of the 'Detroit 16' charged under a revived 1818 anti-recruiting statute for aiding the recruitment of American volunteers for the Spanish Loyalist forces. Held in solitary in a cold cell at the Wayne County Jail with \$10,000 bail, she saw the indictments dismissed by Attorney General Robert Jackson amid national protest.",
            'state' => 'Michigan', 'gender' => 'Female',
            'ideologies' => ['Anti-fascism'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Charged under an 1818 anti-recruiting statute for aiding Spanish Loyalist recruitment.',
                'convicted' => 'Held, 1940', 'sentence' => 'Held in solitary; indictments later dismissed.',
                'institution_name' => 'Wayne County Jail',
                'institution_city' => 'Detroit', 'institution_state' => 'Michigan',
            ]],
        ], ['arrest_date' => [1940, 2, 6]]);

        // ── MASSACHUSETTS — DIES CONTEMPT ───────────────────────────────
        $mk([
            'name' => 'Patrick O\'Dea', 'first_name' => 'Patrick', 'last_name' => 'O\'Dea',
            'description' => "Patrick O'Dea was a Massachusetts Communist Party leader jailed in 1940 on contempt-of-Congress charges arising from the Dies Committee, alongside fellow state leader Philip Frankfeld. Judge F. Dickinson Letts ordered the two released, holding the contempt citation unconstitutional.",
            'state' => 'Massachusetts', 'gender' => 'Male',
            'ideologies' => ['Communism'], 'affiliation' => ['Communist Party'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed on contempt-of-Congress charges from the Dies Committee.',
                'convicted' => 'Jailed, 1940', 'sentence' => 'Held; released as unconstitutional.',
                'institution_state' => 'Massachusetts',
            ]],
        ], ['arrest_date' => [1940, null, null]]);

        // ── ILLINOIS — CHICAGO ILD CONTEMPT ─────────────────────────────
        $mk([
            'name' => 'Bob Wirtz', 'first_name' => 'Bob', 'last_name' => 'Wirtz',
            'description' => "Bob Wirtz was the local secretary of the International Labor Defense in Chicago, prosecuted on criminal-contempt-of-court charges in March 1940 as a co-defendant alongside editor Louis Budenz and ILD national vice-president William L. Patterson, over public protest against a judge's anti-labor injunction.",
            'state' => 'Illinois', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'], 'affiliation' => ['International Labor Defense'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Prosecuted for criminal contempt over protest against an anti-labor injunction.',
                'convicted' => 'Charged, 1940', 'sentence' => 'Prosecuted for contempt.',
                'institution_city' => 'Chicago', 'institution_state' => 'Illinois',
            ]],
        ], ['arrest_date' => [1940, 3, null]]);

        // ── PENNSYLVANIA — CRIMINAL LIBEL ───────────────────────────────
        $mk([
            'name' => 'John L. Spivak', 'first_name' => 'John', 'last_name' => 'Spivak',
            'description' => "John L. Spivak was an anti-fascist investigative journalist — author of 'Secret Armies,' exposing native fascist networks — who was jailed twice in one week in late March 1940 on criminal-libel charges, first in Coraopolis near Pittsburgh, Pennsylvania on a complaint by an ex-Dies-committee investigator. Handcuffed and, per New Masses, 'mauled' by police, he was held on \$5,000 bail in prosecutions widely seen as retaliation for his exposés.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Anti-fascism'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed on criminal-libel charges over his anti-fascist exposés.',
                'convicted' => 'Jailed, 1940', 'sentence' => 'Held on $5,000 bail.',
                'institution_city' => 'Coraopolis', 'institution_state' => 'Pennsylvania',
            ]],
        ], ['arrest_date' => [1940, 3, null]]);

        // ── TEXAS — SHRIMP PEELERS UNION ────────────────────────────────
        $mk([
            'name' => 'Christopher Clarich', 'first_name' => 'Christopher', 'last_name' => 'Clarich',
            'description' => "Christopher Clarich was president of the Shrimp Peelers Union of UCAPAWA, convicted and sentenced to twenty years for the murder of a vigilante killed when an armed anti-union mob attacked a picket line during the union's strike at Aransas Pass, Texas — a case the labor press treated as a class-war frame-up growing out of employer-backed violence against Gulf-coast seafood workers.",
            'state' => 'Texas', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'], 'affiliation' => ['United Cannery, Agricultural, Packing & Allied Workers of America'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Convicted of murdering a vigilante who attacked a shrimp-workers picket line.',
                'convicted' => 'Convicted, 1940', 'sentence' => 'Twenty years.',
                'institution_city' => 'Aransas Pass', 'institution_state' => 'Texas',
            ]],
        ], ['incarceration_date' => [1940, null, null]]);

        // ── SOUTH CAROLINA — GREENVILLE NAACP ───────────────────────────
        $mk([
            'name' => 'William Anderson', 'first_name' => 'William', 'last_name' => 'Anderson',
            'description' => "William Anderson was a 19-year-old assistant secretary of the Greenville, South Carolina NAACP branch and head of its youth council, arrested on a fabricated charge (using a school telephone to call a white girl), convicted, and sentenced to thirty days on the chain gang plus a \$100 fine and \$5,000 bond, amid a wave of Klan terror in Greenville.",
            'state' => 'South Carolina', 'gender' => 'Male', 'race' => 'Black',
            'ideologies' => ['Civil rights'], 'affiliation' => ['NAACP'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Convicted on a fabricated charge amid Klan terror in Greenville.',
                'convicted' => 'Convicted, 1940', 'sentence' => 'Thirty days on the chain gang plus a $100 fine.',
                'institution_city' => 'Greenville', 'institution_state' => 'South Carolina',
            ]],
        ], ['arrest_date' => [1940, null, null]]);

        // ── INSERT ───────────────────────────────────────────────────────
        $added = 0;
        foreach ($people as $person) {
            $payload = $person['payload'];
            $payload['in_custody'] = false;
            if (! array_key_exists('released', $payload)) {
                $payload['released'] = true;
            }

            $existing = Prisoner::withoutGlobalScopes()
                ->where('name', 'like', '%'.$payload['first_name'].'%')
                ->where('name', 'like', '%'.$payload['last_name'].'%')
                ->first();
            if ($existing) {
                $this->line("  already in database as \"{$existing->name}\" — skipping {$payload['name']}.");

                continue;
            }

            $this->call('prisoner:add', ['json' => json_encode($payload)]);

            $prisoner = Prisoner::withoutGlobalScopes()->where('name', $payload['name'])->first();
            if (! $prisoner) {
                continue;
            }
            $prisoner->in_custody = false;
            $prisoner->released = $payload['released'];
            $prisoner->save();

            $case = $prisoner->cases()->first();
            if ($case && ! empty($person['dates'])) {
                foreach ($person['dates'] as $field => [$y, $m, $d]) {
                    $case->setPartialDate($field, $y, $m, $d);
                }
                $case->save();
            }
            $added++;
        }

        $this->info("\nDone. Processed {$added} of ".count($people)." New Masses 1940 prisoner(s).");

        return self::SUCCESS;
    }
}
