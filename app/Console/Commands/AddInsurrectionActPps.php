<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds 10 PPs surfaced from the Wikipedia "Insurrection Act of 1807"
 * deep crawl. The article catalogs presidential invocations of the
 * Act across 1808-2025; most invocations either yielded defendants
 * already in the DB or no in-scope named defendants at all
 * (Nullification Crisis, Selma, Ole Miss, Katrina).
 *
 * The productive incidents were labor-strike injunction prosecutions:
 *
 *   - 1894 Pullman Strike (Cleveland invocation): 5 ARU directors
 *     jailed with Debs at the Woodstock jail
 *   - 1899 Coeur d'Alene mining war (McKinley): Paul Corcoran of
 *     the WFM, 17 yrs (overturned)
 *   - 1907 Goldfield NV strike: (Preston / Smith already in)
 *   - 1914 Colorado Coalfield War / Ludlow (Wilson): Louis
 *     Zancanelli, life (overturned)
 *   - 1932 Bonus Army (Hoover): James W. Ford, CPUSA VP candidate
 *   - 1943 Detroit race riot (FDR): Charles Lyons, Aaron Fox —
 *     Black defendants railroaded post-deployment
 */
final class AddInsurrectionActPps extends Command {
    protected $signature = 'archive:add-insurrection-act-pps';
    protected $description = 'Add 10 PPs from Insurrection Act invocation crawl';

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

        // === 1894 Pullman Strike — 5 ARU directors jailed with Debs ===
        $pullmanDesc = 'American Railway Union director and Eugene V. Debs co-defendant in the federal contempt prosecution following the 1894 Pullman Strike, when President Cleveland invoked the Insurrection Act and deployed federal troops to break the boycott of Pullman cars. All ARU directors were enjoined under Olney\'s sweeping federal injunction from any further communication that might continue the strike; when they continued to coordinate the union\'s defense, they were convicted of criminal contempt and jailed for three months at Woodstock (McHenry County) jail alongside Debs.';
        $pullmanCase = [
            'institution_name' => 'Woodstock Jail (McHenry County)',
            'institution_state' => 'Illinois',
            'charges' => 'Criminal contempt of federal injunction (in re Debs); conspiracy to obstruct mail and interstate commerce.',
            'arrest_date' => '1894-07-17',
            'sentenced_date' => '1894-12-14',
            'release_date' => '1895-11-22',
            'convicted' => 'Yes.',
            'sentence' => '3 months in Woodstock Jail (some served additional injunction time).',
        ];
        $pullmanBase = [
            'state' => 'Illinois',
            'race' => 'White',
            'gender' => 'Male',
            'ideologies' => ['Labor', 'Socialist'],
            'affiliation' => ['American Railway Union'],
            'era' => '1890s',
            'in_custody' => false,
            'released' => true,
        ];
        foreach ([
            'George W. Howard' => 'Vice President of the ARU and Debs\'s closest lieutenant.',
            'Sylvester Keliher' => 'Secretary-Treasurer of the ARU.',
            'Louis W. Rogers' => 'ARU director and editor of the Railway Times.',
            'Martin J. Elliott' => 'ARU director.',
            'Roy M. Goodwin' => 'ARU director.',
        ] as $name => $bio) {
            $parts = preg_split('/\s+/', $name);
            $p = [
                'name' => $name,
                'first_name' => $parts[0],
                'last_name' => end($parts),
                'description' => $pullmanDesc.' '.$bio,
                'cases' => [$pullmanCase],
            ] + $pullmanBase;
            if (count($parts) === 3) $p['middle_name'] = $parts[1];
            $out[] = $p;
        }

        // === Paul Corcoran (1899 Coeur d'Alene) ===
        $out[] = [
            'name' => 'Paul Corcoran',
            'first_name' => 'Paul',
            'last_name' => 'Corcoran',
            'description' => 'Financial secretary of the Burke Miners\' Union (Western Federation of Miners) in the Coeur d\'Alene mining district of Idaho. After the April 29, 1899 dynamiting of the Bunker Hill mill by union miners, President McKinley invoked the Insurrection Act, sent federal troops, and the state established a notorious "bullpen" prison camp in which roughly 1,000 miners were held without charge for months. Corcoran was charged with the second-degree murder of non-union miner James Cheyne despite not being at the scene of the explosion; convicted by a packed jury and sentenced to 17 years at hard labor. Released on appeal.',
            'state' => 'Idaho',
            'race' => 'White',
            'gender' => 'Male',
            'ideologies' => ['Labor', 'WFM'],
            'affiliation' => ['Western Federation of Miners'],
            'era' => '1890s',
            'in_custody' => false,
            'released' => true,
            'cases' => [[
                'institution_name' => 'Idaho State Penitentiary',
                'institution_state' => 'Idaho',
                'charges' => 'Second-degree murder (death of non-union miner James Cheyne during Bunker Hill mill dynamiting).',
                'arrest_date' => '1899-05-01',
                'sentenced_date' => '1899-09-15',
                'convicted' => 'Yes — later released on appeal.',
                'sentence' => '17 years hard labor; released on appeal.',
            ]],
        ];

        // === Louis Zancanelli (1914 Colorado Coalfield War) ===
        $out[] = [
            'name' => 'Louis Zancanelli',
            'first_name' => 'Louis',
            'last_name' => 'Zancanelli',
            'description' => 'Italian-American striking coal miner during the 1913-14 Colorado Coalfield War — a year of armed conflict between United Mine Workers strikers and Rockefeller\'s Colorado Fuel and Iron Company that culminated in the April 20, 1914 Ludlow Massacre (where company guards and National Guard killed at least 21 strikers and family members, including 11 children). After President Wilson invoked the Insurrection Act, Colorado prosecutors charged Zancanelli with first-degree murder for the killing of Baldwin-Felts detective George Belcher on the streets of Trinidad. Tried before the pro-operator Judge Hilliard with a packed jury; convicted and sentenced to life imprisonment. Conviction was reversed by the Colorado Supreme Court in 1917.',
            'state' => 'Colorado',
            'race' => 'White',
            'gender' => 'Male',
            'ideologies' => ['Labor', 'UMWA'],
            'affiliation' => ['United Mine Workers of America'],
            'era' => '1910s',
            'in_custody' => false,
            'released' => true,
            'cases' => [[
                'institution_name' => 'Colorado State Penitentiary',
                'institution_state' => 'Colorado',
                'charges' => 'First-degree murder (death of Baldwin-Felts detective George Belcher, Colorado Coalfield War).',
                'arrest_date' => '1914-05-01',
                'sentenced_date' => '1914-09-01',
                'convicted' => 'Yes — reversed by Colorado Supreme Court 1917.',
                'sentence' => 'Life imprisonment; overturned 1917.',
            ]],
        ];

        // === James W. Ford (1932 Bonus Army aftermath) ===
        $out[] = [
            'name' => 'James W. Ford',
            'first_name' => 'James',
            'middle_name' => 'W.',
            'last_name' => 'Ford',
            'description' => 'African-American Communist Party USA organizer and the first Black candidate for U.S. Vice President (CPUSA ticket, 1932, 1936, 1940). After President Hoover invoked the Insurrection Act and General MacArthur cleared the Bonus Army encampment in late July 1932, Ford led a Communist-organized regrouping of bonus marchers in Virginia. He was arrested with 42 others when local authorities raided their meeting. Continued CPUSA organizing for decades.',
            'state' => 'New York',
            'race' => 'Black',
            'gender' => 'Male',
            'birthdate' => '1893-12-22',
            'death_date' => '1957-06-21',
            'ideologies' => ['Communist', 'Black freedom', 'Anti-poverty'],
            'affiliation' => ['Communist Party USA'],
            'era' => '1930s',
            'in_custody' => false,
            'released' => true,
            'cases' => [[
                'institution_state' => 'Virginia',
                'charges' => 'Detained at CPUSA-organized Bonus Army regrouping meeting following federal eviction.',
                'arrest_date' => '1932-08-01',
                'sentence' => 'Detained with 42 others; outcome unspecified.',
            ]],
        ];

        // === 1943 Detroit Race Riot — Black defendants railroaded ===
        $detroitDesc = 'Black defendant convicted in the racially asymmetric prosecutions following the June 1943 Detroit race riot — three days of white-mob violence against Black neighborhoods that killed 25 Black residents and 9 whites. President Roosevelt invoked the Insurrection Act and deployed federal troops to restore order. Of the 1,800 arrests that followed, 85% were of Black residents; nearly all rioting and assault prosecutions targeted Black defendants while white attackers were largely uncharged.';
        $out[] = [
            'name' => 'Charles Lyons',
            'first_name' => 'Charles',
            'last_name' => 'Lyons',
            'description' => $detroitDesc.' Lyons was convicted of "rioting" in a trial where more than 20 Black witnesses testified for the defense but two of his three Black jurors were excused. Sentenced to 4-5 years.',
            'state' => 'Michigan',
            'race' => 'Black',
            'gender' => 'Male',
            'ideologies' => ['Anti-racist self-defense'],
            'affiliation' => ['1943 Detroit Race Riot defendants'],
            'era' => '1940s',
            'in_custody' => false,
            'released' => true,
            'cases' => [[
                'institution_state' => 'Michigan',
                'charges' => 'Rioting (1943 Detroit race riot).',
                'arrest_date' => '1943-06-21',
                'sentenced_date' => '1943-10-01',
                'convicted' => 'Yes.',
                'sentence' => '4-5 years.',
            ]],
        ];
        $out[] = [
            'name' => 'Aaron Fox',
            'first_name' => 'Aaron',
            'last_name' => 'Fox',
            'description' => $detroitDesc.' Fox, age 17, was convicted of a riot-related murder he could not have committed; sentenced to 25 years. Cleared and released after a two-year legal fight by his defense team that exposed the prosecution\'s frame-up.',
            'state' => 'Michigan',
            'race' => 'Black',
            'gender' => 'Male',
            'ideologies' => ['Anti-racist self-defense'],
            'affiliation' => ['1943 Detroit Race Riot defendants'],
            'era' => '1940s',
            'in_custody' => false,
            'released' => true,
            'cases' => [[
                'institution_state' => 'Michigan',
                'charges' => 'Murder (1943 Detroit race riot).',
                'arrest_date' => '1943-06-21',
                'sentenced_date' => '1943-10-01',
                'release_date' => '1945-10-01',
                'convicted' => 'Yes — vacated after ~2 years.',
                'sentence' => '25 years; vacated after ~2 years.',
            ]],
        ];

        return $out;
    }
}
