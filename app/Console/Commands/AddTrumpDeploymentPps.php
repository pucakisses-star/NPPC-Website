<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds 7 PPs surfaced from the deep crawl of Wikipedia's "Domestic
 * military deployments by the second Trump administration" article.
 *
 *   - Rasha Alawieh — Brown Med faculty deported to Lebanon in
 *     defiance of a federal judge's stay order
 *   - Alfredo "Lelo" Juarez Zeferino — Familias Unidas por la
 *     Justicia farmworker union co-founder; ICE-detained
 *   - David Huerta — SEIU California president; felony conspiracy
 *     for blocking ICE vehicle in LA, June 2025
 *   - Christian Damian Cerno-Camacho — US citizen Walmart worker
 *     prosecuted for alleged punching of CBP agent during the LA
 *     deployment
 *   - Sean Dunn — DOJ paralegal prosecuted for throwing a Subway
 *     sandwich at a CBP agent during the DC federal takeover;
 *     jury acquittal
 *   - Kat Abughazaleh — federally charged after Sept 2025 Broadview
 *     ICE center confrontation (Chicago); all charges dismissed for
 *     grand-jury misconduct
 *   - Julio Cesar Sosa-Celis — Venezuelan national shot in the leg
 *     by ICE during Operation Metro Surge (Minneapolis, Jan 2026);
 *     charges dismissed with prejudice
 *
 * Era = "2020s" per project decade-string convention.
 */
final class AddTrumpDeploymentPps extends Command {
    protected $signature = 'archive:add-trump-deployment-pps';
    protected $description = 'Add 7 PPs from second-Trump-admin domestic deployments crawl';

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
        return [
            [
                'name' => 'Rasha Alawieh',
                'first_name' => 'Rasha',
                'last_name' => 'Alawieh',
                'description' => 'Lebanese assistant professor at Brown University\'s Warren Alpert Medical School (H-1B visa holder). Detained at Boston Logan airport on March 14, 2025 returning from a family visit to Lebanon. CBP agents found photos and videos of Hassan Nasrallah and other Hezbollah-related figures on her phone. Deported to Lebanon on March 16, 2025 in direct defiance of an order from U.S. District Judge Leo T. Sorokin requiring 48 hours\' notice before any removal. The defiance became one of the early test cases of the second Trump administration\'s willingness to ignore federal court orders in immigration enforcement.',
                'state' => 'Rhode Island',
                'race' => 'Arab',
                'gender' => 'Female',
                'ideologies' => ['Pro-Palestine', 'Pro-Lebanese resistance'],
                'era' => '2020s',
                'in_custody' => false,
                'released' => false,
                'in_exile' => true,
                'cases' => [[
                    'institution_state' => 'Massachusetts',
                    'charges' => 'Immigration detention; no criminal charges. Visa revoked over phone images.',
                    'arrest_date' => '2025-03-14',
                    'release_date' => '2025-03-16',
                    'convicted' => 'No — administrative detention only.',
                    'sentence' => 'Deported to Lebanon March 16, 2025 in defiance of federal court stay.',
                ]],
            ],
            [
                'name' => 'Alfredo Juarez Zeferino',
                'aka' => 'Lelo',
                'first_name' => 'Alfredo',
                'last_name' => 'Zeferino',
                'description' => 'Co-founder of Familias Unidas por la Justicia, the Washington state berry-farmworker union. Mixtec migrant farmworker from Oaxaca who came to the U.S. as a child. ICE-detained March 25, 2025 in Sedro-Woolley, Washington — the same day his union held a press conference about ICE raids of farmworker communities. Held at the Northwest ICE Processing Center (Tacoma); voluntary departure to Mexico July 14, 2025 to avoid further prolonged detention.',
                'state' => 'Washington',
                'race' => 'Indigenous',
                'gender' => 'Male',
                'ideologies' => ['Farmworker organizing', 'Migrant rights'],
                'affiliation' => ['Familias Unidas por la Justicia', 'Community to Community Development'],
                'era' => '2020s',
                'in_custody' => false,
                'released' => false,
                'in_exile' => true,
                'cases' => [[
                    'institution_name' => 'Northwest ICE Processing Center (Tacoma)',
                    'institution_state' => 'Washington',
                    'charges' => 'ICE detention (no criminal charges); held while removal proceedings continued.',
                    'arrest_date' => '2025-03-25',
                    'release_date' => '2025-07-14',
                    'convicted' => 'No — immigration detention only.',
                    'sentence' => 'Voluntary departure to Mexico after ~4 months in ICE custody.',
                ]],
            ],
            [
                'name' => 'David Huerta',
                'first_name' => 'David',
                'last_name' => 'Huerta',
                'description' => 'President of SEIU California and Service Employees International Union United Service Workers West, the union representing 750,000 California workers. Arrested June 6, 2025 in Los Angeles while blocking an ICE vehicle during the multi-day immigration raids that triggered the deployment of 4,000 California National Guard troops and 700 U.S. Marines to LA. Federally charged with felony conspiracy to impede an officer; Huerta sustained injuries to his head during the arrest. Released June 9, 2025 on $50,000 bond. The Huerta arrest became a defining moment of the 2025 LA deployment.',
                'state' => 'California',
                'race' => 'Latinx',
                'gender' => 'Male',
                'ideologies' => ['Labor', 'Migrant rights'],
                'affiliation' => ['Service Employees International Union (SEIU)'],
                'era' => '2020s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'California',
                    'charges' => 'Felony conspiracy to impede a federal officer (18 U.S.C. §372).',
                    'arrest_date' => '2025-06-06',
                    'release_date' => '2025-06-09',
                    'sentence' => 'Released on $50,000 bond; case pending.',
                ]],
            ],
            [
                'name' => 'Christian Damian Cerno-Camacho',
                'first_name' => 'Christian',
                'middle_name' => 'Damian',
                'last_name' => 'Cerno-Camacho',
                'description' => '20-year-old U.S. citizen and Walmart employee arrested June 17, 2025 in Pico Rivera, California during the federal deployment to the Los Angeles area. Federal prosecutors charged him with assaulting a CBP agent during the protests against the immigration raids; bystander video circulated publicly disputed the government\'s account.',
                'state' => 'California',
                'race' => 'Latinx',
                'gender' => 'Male',
                'ideologies' => ['Anti-ICE', 'Migrant solidarity'],
                'era' => '2020s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'California',
                    'charges' => 'Assault on a federal officer (CBP).',
                    'arrest_date' => '2025-06-17',
                    'sentence' => 'Case pending as of 2026.',
                ]],
            ],
            [
                'name' => 'Sean Dunn',
                'first_name' => 'Sean',
                'last_name' => 'Dunn',
                'description' => 'U.S. Department of Justice paralegal (fired after arrest) who, during the August 2025 federal deployment to Washington, DC under Executive Order 14333, was charged after throwing a Subway sandwich at a CBP agent at a downtown federal protective formation. The DOJ initially sought a felony assault indictment; the DC grand jury declined twice, forcing prosecutors to refile as a misdemeanor. Jury acquitted in November 2025. The "Subway sandwich" trial became a national symbol of the DC federal takeover.',
                'state' => 'District of Columbia',
                'race' => 'White',
                'gender' => 'Male',
                'ideologies' => ['Anti-deployment protest'],
                'era' => '2020s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'District of Columbia',
                    'charges' => 'Assault on a federal officer (grand jury twice declined felony; refiled as misdemeanor assault).',
                    'arrest_date' => '2025-08-12',
                    'sentenced_date' => '2025-11-14',
                    'convicted' => 'No — jury acquittal.',
                    'sentence' => 'Acquitted.',
                ]],
            ],
            [
                'name' => 'Kat Abughazaleh',
                'first_name' => 'Kat',
                'last_name' => 'Abughazaleh',
                'description' => 'Journalist, media analyst, and Democratic political candidate. Federally charged along with five co-defendants after a September 26, 2025 anti-ICE confrontation at the Broadview ICE Processing Center outside Chicago during Operation Midway Blitz — the Trump administration\'s federal deployment to Chicago. All charges were dismissed with prejudice in May 2026 after the disclosure of grand-jury misconduct by federal prosecutors.',
                'state' => 'Illinois',
                'race' => 'Arab',
                'gender' => 'Female',
                'ideologies' => ['Anti-ICE', 'Pro-Palestine'],
                'era' => '2020s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Illinois',
                    'charges' => 'Federal obstruction of officers; civil disorder.',
                    'arrest_date' => '2025-09-26',
                    'sentenced_date' => '2026-05-15',
                    'convicted' => 'No — all charges dismissed with prejudice in May 2026 over grand-jury misconduct.',
                    'sentence' => 'Charges dismissed with prejudice.',
                ]],
            ],
            [
                'name' => 'Julio Cesar Sosa-Celis',
                'first_name' => 'Julio',
                'middle_name' => 'Cesar',
                'last_name' => 'Sosa-Celis',
                'description' => 'Venezuelan national shot in the leg by ICE Agent Castro on January 14, 2026 during Operation Metro Surge — the Trump administration\'s federal deployment to Minneapolis. Initially charged with assault on a federal officer; charges were later dismissed with prejudice. Agent Castro was placed under criminal investigation for the shooting.',
                'state' => 'Minnesota',
                'race' => 'Latinx',
                'gender' => 'Male',
                'ideologies' => ['Migrant solidarity'],
                'era' => '2020s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Minnesota',
                    'charges' => 'Assault on a federal officer (ICE agent who shot him).',
                    'arrest_date' => '2026-01-14',
                    'sentenced_date' => '2026-04-01',
                    'convicted' => 'No — charges dismissed with prejudice; shooting agent under criminal probe.',
                    'sentence' => 'Charges dismissed with prejudice.',
                ]],
            ],
        ];
    }
}
