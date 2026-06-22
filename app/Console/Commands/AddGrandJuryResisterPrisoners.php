<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Adds grand jury resisters surfaced by a decade-by-decade sweep (2020s back
 * through the 1970s) of people JAILED for refusing to cooperate with a grand
 * jury. The database proved remarkably complete for this category — the entire
 * Puerto Rican FALN cluster (Cueto, Nemikin, the Rosados, Romero, Archuleta,
 * López, Caldero, Guerra), the 2012 Pacific Northwest cohort, the WikiLeaks
 * resisters, the SF8, Jonathan Paul, Ashqar, Jeff Hogg, and Jesse Shackelford
 * were all already present. The genuine gaps it adds:
 *
 *  - Rik Scarce — 1993 Spokane; 159 days civil contempt on scholar's-privilege
 *    grounds (the landmark Scarce v. United States case).
 *  - Jill Raymond — the lone Lexington Six member who held out (~14 months,
 *    1975-76) refusing the grand jury hunting Susan Saxe and Katherine Power.
 *  - Norberto Cintrón Fiallo — Puerto Rican independence / FARP; ~18 months
 *    civil contempt (1984-85) for refusing NY grand juries.
 *  - The Fort Worth Five (Kenneth Tierney, Paschal Morahan, Daniel Crawford,
 *    Matthias Reilly, Thomas Laffey) — Irish-American republicans jailed ~3
 *    months in 1972 for refusing a Texas grand jury probing IRA gun-running.
 *  - Early-1970s antiwar grand jury resisters: Anthony Russo (Pentagon Papers,
 *    47 days, 1971), Leslie Bacon (1971 Capitol-bombing material witness), and
 *    Sister Jogues Egan and Anne Walsh (the 1971 Harrisburg Catholic-left grand
 *    jury, whose wiretap challenge became Gelbard v. United States).
 *
 * Genuine pre-1970 grand jury resisters proved to be essentially an empty set:
 * coercive civil-contempt jailing only became a systematic repression tool
 * after the 1970 use-immunity statute, and earlier testimony-refusal cases
 * (HUAC, Smith Act) ran through congressional/trial contempt — a different
 * mechanism — and are already in the database (Braden, Wilkinson, Trumbo, etc.).
 *
 * The other five Lexington Six members (Cohee, Junkin, Hands, Link, Seymour)
 * are also included: each was jailed in 1975 for refusing the grand jury, but
 * unlike Raymond most were released after agreeing to testify, which their
 * profiles note explicitly.
 *
 * Idempotent: prisoner:add refuses duplicate names, so re-running skips anyone
 * already present.
 */
final class AddGrandJuryResisterPrisoners extends Command {
    protected $signature = 'prisoners:add-gj-resisters';
    protected $description = 'Add the grand jury resisters missing from the decade-by-decade GJ-resistance sweep';

    public function handle(): int {
        $fortWorth = function (string $name, string $first, string $last): array {
            return [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'description' => $name.' was one of the Fort Worth Five, five Irish-American men jailed in Texas in 1972 for refusing to testify before a federal grand jury investigating Irish Republican Army gun-running. The grand jury, sitting in Fort Worth, was probing an alleged scheme to buy weapons in the United States and ship them to the IRA in Northern Ireland. Granted immunity and ordered to testify, '.$first.' '.$last.' and his four co-resisters refused, regarding the grand jury as an instrument of political repression against Irish republican solidarity. They were jailed for civil contempt in mid-1972 and held without bail at the Federal Correctional Institution at Seagoville, near Dallas, until their release in September 1972, when they were greeted by relatives and Irish bagpipers at LaGuardia Airport in New York. The case became a cause celebre for Irish-American and civil-liberties supporters opposed to the use of grand juries to investigate political associations.',
                'race' => 'White',
                'gender' => 'Male',
                'state' => 'New York',
                'ideologies' => ['Irish republicanism', 'Anti-imperialism'],
                'affiliation' => ['Fort Worth Five'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Federal Correctional Institution, Seagoville',
                    'institution_state' => 'Texas',
                    'charges' => 'Civil contempt of court for refusing to testify before a federal grand jury investigating IRA gun-running',
                    'convicted' => 'Civil contempt (no underlying criminal conviction)',
                    'sentence' => 'Jailed roughly three months for civil contempt (mid-1972 to September 1972)',
                    'imprisoned_for_days' => 90,
                ]],
            ];
        };

        $prisoners = [
            [
                'name' => 'Rik Scarce',
                'first_name' => 'Rik',
                'last_name' => 'Scarce',
                'aka' => 'James Richard Scarce',
                'description' => 'James Richard Scarce, known as Rik Scarce, is a sociologist and author who was jailed for civil contempt in 1993 after refusing, on scholarly-privilege grounds, to tell a federal grand jury about confidential conversations with a research subject. As a doctoral student at Washington State University, Scarce had interviewed radical environmental and animal-liberation activists for his book Eco-Warriors. After a 1991 Animal Liberation Front raid caused roughly 100,000 dollars in damage to university animal-research laboratories — an action linked to Rod Coronado, who had stayed at the Scarce family home — a grand jury in Spokane, Washington demanded that Scarce reveal what his sources had told him. Invoking a researcher privilege analogous to a journalist privilege, and the code of ethics of the American Sociological Association, he refused. U.S. District Judge Wm. Fremming Nielsen jailed him on May 14, 1993, and he was held for 159 days — at the time the longest U.S. jailing of a scholar for protecting research sources — before being released that October when the judge found further confinement merely punitive rather than coercive. Scarce later completed his doctorate, became a professor of sociology, and wrote Contempt of Court: A Scholar\'s Battle for Free Speech from Behind Bars.',
                'race' => 'White',
                'gender' => 'Male',
                'state' => 'Washington',
                'ideologies' => ['Animal liberation', 'Environmentalism', 'Academic freedom'],
                'era' => '1990s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Washington',
                    'charges' => 'Civil contempt of court for refusing to testify before a federal grand jury',
                    'incarceration_date' => '1993-05-14',
                    'release_date' => '1993-10-20',
                    'convicted' => 'Civil contempt (no underlying criminal conviction)',
                    'sentence' => 'Jailed 159 days for civil contempt',
                    'imprisoned_for_days' => 159,
                ]],
            ],
            [
                'name' => 'Jill Raymond',
                'first_name' => 'Jill',
                'last_name' => 'Raymond',
                'description' => 'Jill Raymond was a member of a Lexington, Kentucky lesbian-feminist collective who became the longest-jailed of the grand jury resisters known as the Lexington Six. In 1975 a federal grand jury in Lexington, ostensibly hunting the antiwar fugitives Susan Saxe and Katherine Power — wanted in connection with a 1970 Boston bank robbery in which a police officer was killed — subpoenaed six young gay and lesbian activists, seeking to map the local radical and lesbian community. All six refused to cooperate and were jailed for civil contempt in March 1975. One by one the others were released, several after agreeing to testify, but Raymond refused to the end. She was held for roughly fourteen months — far longer than any of her co-resisters — and was finally released on May 3, 1976. Her sustained refusal made her a landmark figure in the history of grand jury resistance and of lesbian and gay political defense.',
                'race' => 'White',
                'gender' => 'Female',
                'state' => 'Kentucky',
                'ideologies' => ['Lesbian feminism', 'Gay liberation', 'Anti-war'],
                'affiliation' => ['Lexington Six'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Kentucky',
                    'charges' => 'Civil contempt of court for refusing to testify before a federal grand jury',
                    'incarceration_date' => '1975-03-08',
                    'release_date' => '1976-05-03',
                    'convicted' => 'Civil contempt (no underlying criminal conviction)',
                    'sentence' => 'Jailed approximately fourteen months for civil contempt',
                    'imprisoned_for_days' => 422,
                ]],
            ],
            [
                'name' => 'Gail Cohee',
                'first_name' => 'Gail',
                'last_name' => 'Cohee',
                'description' => 'Gail Cohee was a member of the Lexington, Kentucky lesbian-feminist and gay-liberation collective subpoenaed in 1975 by a federal grand jury that was ostensibly hunting the antiwar fugitives Susan Saxe and Katherine Power, but which activists said was being used to map the local radical and gay community. Cohee, then about 21 and openly lesbian, refused to cooperate and was jailed for civil contempt in March 1975 as one of the six subpoenaed activists who became known as the Lexington Six. After roughly two months in jail she agreed to testify and was released; of the six, only Jill Raymond held out for the full grand jury term of about fourteen months.',
                'race' => 'White',
                'gender' => 'Female',
                'state' => 'Kentucky',
                'ideologies' => ['Lesbian feminism', 'Gay liberation', 'Anti-war'],
                'affiliation' => ['Lexington Six'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Kentucky',
                    'charges' => 'Civil contempt of court for refusing to testify before a federal grand jury',
                    'incarceration_date' => '1975-03-08',
                    'convicted' => 'Civil contempt; released after agreeing to testify',
                    'sentence' => 'Jailed approximately two months for civil contempt before agreeing to testify',
                    'imprisoned_for_days' => 60,
                ]],
            ],
            [
                'name' => 'Carey Junkin',
                'first_name' => 'Carey',
                'last_name' => 'Junkin',
                'description' => 'Carey Junkin was a member of the Lexington, Kentucky gay-liberation and lesbian-feminist collective subpoenaed in 1975 by a federal grand jury that was ostensibly hunting the antiwar fugitives Susan Saxe and Katherine Power, but which activists said was being used to map the local radical and gay community. Junkin refused to cooperate and was jailed for civil contempt in March 1975 as one of the six subpoenaed activists who became known as the Lexington Six. He was released on March 31, 1975; of the six, only Jill Raymond held out for the full grand jury term of about fourteen months.',
                'race' => 'White',
                'gender' => 'Male',
                'state' => 'Kentucky',
                'ideologies' => ['Gay liberation', 'Anti-war'],
                'affiliation' => ['Lexington Six'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Kentucky',
                    'charges' => 'Civil contempt of court for refusing to testify before a federal grand jury',
                    'incarceration_date' => '1975-03-08',
                    'release_date' => '1975-03-31',
                    'convicted' => 'Civil contempt (no underlying criminal charge)',
                    'sentence' => 'Jailed approximately three weeks for civil contempt in 1975',
                    'imprisoned_for_days' => 23,
                ]],
            ],
            [
                'name' => 'Debbie Hands',
                'first_name' => 'Debbie',
                'last_name' => 'Hands',
                'description' => 'Debbie Hands was a member of the Lexington, Kentucky lesbian-feminist and gay-liberation collective subpoenaed in 1975 by a federal grand jury that was ostensibly hunting the antiwar fugitives Susan Saxe and Katherine Power, but which activists said was being used to map the local radical and gay community. Hands refused to cooperate and was jailed for civil contempt on March 8, 1975 as one of the six subpoenaed activists who became known as the Lexington Six. She was released six days later, on March 14, 1975, after agreeing to testify; of the six, only Jill Raymond held out for the full grand jury term of about fourteen months.',
                'race' => 'White',
                'gender' => 'Female',
                'state' => 'Kentucky',
                'ideologies' => ['Lesbian feminism', 'Gay liberation', 'Anti-war'],
                'affiliation' => ['Lexington Six'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Kentucky',
                    'charges' => 'Civil contempt of court for refusing to testify before a federal grand jury',
                    'incarceration_date' => '1975-03-08',
                    'release_date' => '1975-03-14',
                    'convicted' => 'Civil contempt; testified after six days',
                    'sentence' => 'Jailed six days for civil contempt before testifying',
                    'imprisoned_for_days' => 6,
                ]],
            ],
            [
                'name' => 'Linda Link',
                'first_name' => 'Linda',
                'last_name' => 'Link',
                'description' => 'Linda Link was a member of the Lexington, Kentucky lesbian-feminist and gay-liberation collective subpoenaed in 1975 by a federal grand jury that was ostensibly hunting the antiwar fugitives Susan Saxe and Katherine Power, but which activists said was being used to map the local radical and gay community. Link refused to cooperate and was jailed for civil contempt in March 1975 as one of the six subpoenaed activists who became known as the Lexington Six. She was released later that spring after agreeing to testify; of the six, only Jill Raymond held out for the full grand jury term of about fourteen months.',
                'race' => 'White',
                'gender' => 'Female',
                'state' => 'Kentucky',
                'ideologies' => ['Lesbian feminism', 'Gay liberation', 'Anti-war'],
                'affiliation' => ['Lexington Six'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Kentucky',
                    'charges' => 'Civil contempt of court for refusing to testify before a federal grand jury',
                    'incarceration_date' => '1975-03-08',
                    'convicted' => 'Civil contempt; released after agreeing to testify',
                    'sentence' => 'Jailed for civil contempt in 1975 before agreeing to testify',
                    'imprisoned_for_days' => 60,
                ]],
            ],
            [
                'name' => 'Marla Seymour',
                'first_name' => 'Marla',
                'last_name' => 'Seymour',
                'description' => 'Marla Seymour was a member of the Lexington, Kentucky lesbian-feminist and gay-liberation collective subpoenaed in 1975 by a federal grand jury that was ostensibly hunting the antiwar fugitives Susan Saxe and Katherine Power, but which activists said was being used to map the local radical and gay community. Seymour, then about 22 and openly lesbian, refused to cooperate and was jailed for civil contempt in March 1975 as one of the six subpoenaed activists who became known as the Lexington Six. She spent roughly two months in jail before agreeing to testify and being released; of the six, only Jill Raymond held out for the full grand jury term of about fourteen months.',
                'race' => 'White',
                'gender' => 'Female',
                'state' => 'Kentucky',
                'ideologies' => ['Lesbian feminism', 'Gay liberation', 'Anti-war'],
                'affiliation' => ['Lexington Six'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Kentucky',
                    'charges' => 'Civil contempt of court for refusing to testify before a federal grand jury',
                    'incarceration_date' => '1975-03-08',
                    'convicted' => 'Civil contempt; released after agreeing to testify',
                    'sentence' => 'Jailed approximately two months for civil contempt before agreeing to testify',
                    'imprisoned_for_days' => 60,
                ]],
            ],
            [
                'name' => 'Norberto Cintrón Fiallo',
                'first_name' => 'Norberto',
                'last_name' => 'Cintrón Fiallo',
                'aka' => 'Norberto A. Cintrón Fiallo',
                'description' => 'Norberto Cintrón Fiallo was a Puerto Rican independence militant and a leader of the Armed Forces of Popular Resistance (Fuerzas Armadas de Resistencia Popular, FARP). In the early 1980s he was subpoenaed by federal grand juries in New York investigating the Puerto Rican independence movement and was ordered to provide hair samples and testimony. Refusing to recognize the authority of the grand jury, he declined to cooperate. A first grand jury impaneled in 1980 was dissolved without holding him in contempt; a second then jailed him for civil contempt. He served roughly eighteen months in prison, from 1984 to 1985, before a judge ordered his release. A longtime organizer for Puerto Rican independence and a board member of the anti-repression committee CUCRE, Cintrón Fiallo remained active in the movement until his death in 2024.',
                'race' => 'Hispanic',
                'gender' => 'Male',
                'state' => 'Puerto Rico',
                'ideologies' => ['Puerto Rican Independence', 'Anti-imperialism', 'Revolutionary nationalism'],
                'affiliation' => ['Armed Forces of Popular Resistance (FARP)'],
                'era' => '1980s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'charges' => 'Civil contempt for refusing to provide testimony and hair samples to federal grand juries investigating the Puerto Rican independence movement',
                    'convicted' => 'Civil contempt (no underlying criminal conviction)',
                    'sentence' => 'Approximately eighteen months for civil contempt (1984-1985)',
                    'imprisoned_for_days' => 540,
                ]],
            ],
            $fortWorth('Kenneth Tierney', 'Kenneth', 'Tierney'),
            $fortWorth('Paschal Morahan', 'Paschal', 'Morahan'),
            $fortWorth('Daniel Crawford', 'Daniel', 'Crawford'),
            $fortWorth('Matthias Reilly', 'Matthias', 'Reilly'),
            $fortWorth('Thomas Laffey', 'Thomas', 'Laffey'),
            [
                'name' => 'Anthony Russo',
                'first_name' => 'Anthony',
                'last_name' => 'Russo',
                'aka' => 'Tony Russo; Anthony Joseph Russo Jr.',
                'description' => 'Anthony Russo was a former RAND Corporation analyst who helped Daniel Ellsberg photocopy the Pentagon Papers, the secret government history of U.S. decision-making in the Vietnam War. In 1971, after the papers were published, Russo was subpoenaed before a federal grand jury in Los Angeles investigating the leak. Granted immunity and ordered to testify against Ellsberg, he refused, and was jailed for 47 days for civil contempt. He was then indicted alongside Ellsberg in December 1971 on charges including espionage and conspiracy, facing decades in prison; the case was dismissed in 1973 after the court found gross government misconduct, including illegal wiretapping and a break-in by White House operatives targeting Ellsberg. Russo remained an antiwar and civil-liberties activist for the rest of his life.',
                'race' => 'White',
                'gender' => 'Male',
                'death_date' => '2008-08-06',
                'state' => 'California',
                'ideologies' => ['Anti-war', 'Whistleblower', 'Civil liberties'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'California',
                    'charges' => 'Civil contempt for refusing to testify before a federal grand jury investigating the Pentagon Papers leak',
                    'convicted' => 'Civil contempt (no underlying conviction; later indicted with Ellsberg, case dismissed 1973 for government misconduct)',
                    'sentence' => 'Jailed 47 days for civil contempt in 1971',
                    'imprisoned_for_days' => 47,
                ]],
            ],
            [
                'name' => 'Leslie Bacon',
                'first_name' => 'Leslie',
                'last_name' => 'Bacon',
                'description' => 'Leslie Bacon was a 19-year-old antiwar activist who became one of the most notorious examples of grand jury and material-witness abuse during the Nixon era. In April 1971 she was arrested in Washington, D.C. as a material witness, secretly flown across the country to Seattle, and held largely incommunicado in connection with a federal grand jury investigating the March 1, 1971 bombing of the U.S. Capitol and an alleged plot to bomb a New York bank. When she refused to cooperate with the grand jury, she was jailed for roughly four weeks before being released on bond. Bacon was never charged with any crime. Her treatment — arrest as a material witness, secret cross-country transport, and jailing for refusing a grand jury fishing expedition into the antiwar movement — became a widely cited symbol of the Nixon-era use of grand juries to harass dissenters.',
                'race' => 'White',
                'gender' => 'Female',
                'ideologies' => ['Anti-war'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Washington',
                    'charges' => 'Held as a material witness and jailed for civil contempt for refusing to cooperate with a federal grand jury investigating the 1971 U.S. Capitol bombing',
                    'convicted' => 'Never charged with any crime',
                    'sentence' => 'Jailed roughly four weeks as a recalcitrant material witness (1971)',
                    'imprisoned_for_days' => 28,
                ]],
            ],
            [
                'name' => 'Jogues Egan',
                'first_name' => 'Jogues',
                'last_name' => 'Egan',
                'aka' => 'Sister Jogues Egan',
                'description' => 'Sister Jogues Egan was a Roman Catholic nun and educator jailed for civil contempt in 1971 for refusing to testify before the federal grand jury in Harrisburg, Pennsylvania that investigated the Catholic antiwar left — the so-called East Coast Conspiracy to Save Lives, the alleged plot associated with Daniel and Philip Berrigan to raid draft boards and symbolically kidnap national security adviser Henry Kissinger. Egan refused to answer the grand jury questions on the ground that they were derived from illegal government wiretapping and electronic surveillance. Held in contempt by the U.S. District Court for the Middle District of Pennsylvania and ordered jailed until she testified or the grand jury expired, she carried her challenge to the Supreme Court, which in Gelbard v. United States (1972) upheld the right of grand jury witnesses to refuse to answer questions based on unlawful surveillance. Her resistance, alongside fellow Catholic activist Anne Walsh, made her a prominent figure of Catholic antiwar grand jury resistance.',
                'race' => 'White',
                'gender' => 'Female',
                'state' => 'Pennsylvania',
                'ideologies' => ['Anti-war', 'Catholic left', 'Civil liberties'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Pennsylvania',
                    'charges' => 'Civil contempt for refusing to testify before the Harrisburg federal grand jury investigating the Catholic antiwar movement',
                    'convicted' => 'Civil contempt (no underlying criminal charge)',
                    'sentence' => 'Jailed for civil contempt in 1971',
                ]],
            ],
            [
                'name' => 'Anne Walsh',
                'first_name' => 'Anne',
                'last_name' => 'Walsh',
                'aka' => 'Anne Elizabeth Walsh',
                'description' => 'Anne Elizabeth Walsh was a Catholic antiwar activist jailed for civil contempt in 1971, alongside Sister Jogues Egan, for refusing to testify before the federal grand jury in Harrisburg, Pennsylvania investigating the Catholic left and the alleged East Coast Conspiracy to Save Lives associated with the Berrigan brothers. Like Egan, Walsh refused to answer questions she argued were the product of illegal government wiretapping. The two women were held in contempt by the U.S. District Court for the Middle District of Pennsylvania, and their challenge was decided together with companion cases by the Supreme Court in Gelbard v. United States (1972), which affirmed that grand jury witnesses may refuse to answer questions derived from unlawful electronic surveillance.',
                'race' => 'White',
                'gender' => 'Female',
                'state' => 'Pennsylvania',
                'ideologies' => ['Anti-war', 'Catholic left', 'Civil liberties'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Pennsylvania',
                    'charges' => 'Civil contempt for refusing to testify before the Harrisburg federal grand jury investigating the Catholic antiwar movement',
                    'convicted' => 'Civil contempt (no underlying criminal charge)',
                    'sentence' => 'Jailed for civil contempt in 1971',
                ]],
            ],
        ];

        $added = 0;
        $skipped = 0;
        foreach ($prisoners as $p) {
            $this->line("\n— {$p['name']} —");
            $code = Artisan::call('prisoner:add', ['json' => json_encode($p, JSON_UNESCAPED_UNICODE)]);
            $this->line(trim(Artisan::output()));
            if ($code === self::SUCCESS) {
                $added++;
            } else {
                $skipped++;
            }
        }

        $this->info("\nDone — added {$added}, skipped {$skipped} (already present).");

        return self::SUCCESS;
    }
}
