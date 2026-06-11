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
 * were all already present. These eight are the genuine gaps, all confirmed
 * resisters who never cooperated:
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
 *
 * NOTE: the other five Lexington Six members (Cohee, Junkin, Hands, Link,
 * Seymour) were jailed but ultimately testified, so they are deliberately NOT
 * added here pending an editorial decision.
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
                'name' => 'Norberto Cintrón Fiallo',
                'first_name' => 'Norberto',
                'last_name' => 'Cintrón Fiallo',
                'aka' => 'Norberto A. Cintrón Fiallo',
                'description' => 'Norberto Cintrón Fiallo was a Puerto Rican independence militant and a leader of the Armed Forces of Popular Resistance (Fuerzas Armadas de Resistencia Popular, FARP). In the early 1980s he was subpoenaed by federal grand juries in New York investigating the Puerto Rican independence movement and was ordered to provide hair samples and testimony. Refusing to recognize the authority of the grand jury, he declined to cooperate. A first grand jury impaneled in 1980 was dissolved without holding him in contempt; a second then jailed him for civil contempt. He served roughly eighteen months in prison, from 1984 to 1985, before a judge ordered his release. A longtime organizer for Puerto Rican independence and a board member of the anti-repression committee CUCRE, Cintrón Fiallo remained active in the movement until his death in 2024.',
                'race' => 'Latino',
                'gender' => 'Male',
                'state' => 'Puerto Rico',
                'ideologies' => ['Puerto Rican independence', 'Anti-imperialism', 'Revolutionary nationalism'],
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
