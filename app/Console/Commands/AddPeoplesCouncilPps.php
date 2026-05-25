<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds 9 PPs from the People's Council of America for Democracy and
 * the Terms of Peace (1917-1919) and its allied milieu — the broad
 * anti-WWI socialist / pacifist / labor coalition that was the most
 * prominent target of the Espionage and Sedition Acts after the IWW
 * itself.
 *
 *   - 2 People's Council leaders directly prosecuted/repressed for
 *     their role in the organization: Scott Nearing (Espionage Act
 *     indictment, acquitted), Rabbi Judah L. Magnes (forced
 *     resignation from Temple Emanu-El)
 *   - 2 Socialist Party speakers convicted under the Espionage Act
 *     for People's-Council-adjacent anti-war agitation: Rose Pastor
 *     Stokes (10 years, overturned), Kate Richards O'Hare (5 years,
 *     served 14 months)
 *   - 4 Masses defendants whose 1917-18 prosecution arose from the
 *     same anti-conscription movement: Floyd Dell, Art Young, Merrill
 *     Rogers, Josephine Bell (already covered narratively in the
 *     existing Max Eastman / John Reed entries; here as standalones)
 *   - Emily Greene Balch — Wellesley economics professor and AUAM /
 *     Women's Peace Party leader, fired by Wellesley in 1918 for her
 *     pacifism; later 1946 Nobel Peace Prize laureate
 *
 * Roger Baldwin (NCLB / AUAM) already in the DB via AddIWWAndAnarchist
 * Prisoners and skipped automatically by prisoner:add idempotency.
 */
final class AddPeoplesCouncilPps extends Command {
    protected $signature = 'archive:add-peoples-council-pps';
    protected $description = 'Add 9 PPs from People\'s Council of America / anti-WWI movement (1917-19)';

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

        // === People's Council leadership ===
        $out[] = [
            'name' => 'Scott Nearing',
            'first_name' => 'Scott',
            'last_name' => 'Nearing',
            'description' => 'Socialist economist, anti-WWI organizer, and chair of the People\'s Council of America for Democracy and the Terms of Peace (1917). Fired from the Wharton School (1915) and then from the University of Toledo (1917) for his pacifist and socialist views, becoming a touchstone case of academic-freedom suppression during the war. Indicted in 1918 under the Espionage Act of 1917 over his American Socialist Society pamphlet "The Great Madness" — which argued the war served Wall Street profit. Tried alongside the publishing society in New York in February 1919; the jury acquitted Nearing personally but convicted the corporation, the only known case of a publisher being convicted while its author was acquitted. Spent the rest of his long life as a homesteader, peace activist, and author of "Living the Good Life."',
            'state' => 'New York',
            'race' => 'White',
            'gender' => 'Male',
            'birthdate' => '1883-08-06',
            'death_date' => '1983-08-24',
            'ideologies' => ['Socialist', 'Pacifist', 'Anti-imperialist'],
            'affiliation' => ['People\'s Council of America', 'American Socialist Society'],
            'era' => '1910s',
            'in_custody' => false,
            'released' => true,
            'cases' => [[
                'institution_state' => 'New York',
                'charges' => 'Espionage Act of 1917 — conspiracy to obstruct military recruitment via the pamphlet "The Great Madness."',
                'arrest_date' => '1918-03-19',
                'sentenced_date' => '1919-02-26',
                'convicted' => 'No — jury acquitted Nearing personally; convicted the American Socialist Society as publisher.',
                'sentence' => 'Acquitted. Earlier fired from Univ. of Toledo (1917) and Wharton (1915) for political views.',
            ]],
        ];

        $out[] = [
            'name' => 'Judah Leon Magnes',
            'aka' => 'Rabbi Judah Magnes',
            'first_name' => 'Judah',
            'middle_name' => 'Leon',
            'last_name' => 'Magnes',
            'description' => 'Reform rabbi of Temple Emanu-El (New York City) and chair of the executive committee of the People\'s Council of America during 1917. His high-profile pacifist opposition to the war and his prominent role in the May 30-31, 1917 Madison Square Garden mass meeting of the People\'s Council triggered a sustained pressure campaign by the trustees of Emanu-El that forced him out of the country\'s wealthiest pulpit. After the war Magnes emigrated to Mandate Palestine, where in 1925 he co-founded and served as the first president of the Hebrew University of Jerusalem; he became a leading binationalist Zionist arguing for a single Jewish-Arab state.',
            'state' => 'New York',
            'race' => 'White',
            'gender' => 'Male',
            'birthdate' => '1877-07-05',
            'death_date' => '1948-10-27',
            'ideologies' => ['Pacifist', 'Reform Judaism', 'Binationalist Zionism'],
            'affiliation' => ['People\'s Council of America', 'Temple Emanu-El', 'Hebrew University of Jerusalem'],
            'era' => '1910s',
            'in_custody' => false,
            'released' => true,
            'in_exile' => true,
            'cases' => [[
                'institution_state' => 'New York',
                'charges' => 'No criminal charges — forced resignation from Temple Emanu-El over People\'s Council leadership.',
                'arrest_date' => '1917-05-31',
                'sentence' => 'Forced from Temple Emanu-El pulpit over anti-war activism; permanent emigration to Mandate Palestine 1922.',
            ]],
        ];

        // === Espionage Act speakers in the People's Council orbit ===
        $out[] = [
            'name' => 'Rose Pastor Stokes',
            'first_name' => 'Rose',
            'middle_name' => 'Pastor',
            'last_name' => 'Stokes',
            'description' => 'Polish-Jewish immigrant cigar-roller turned Socialist Party agitator, journalist, and (after her 1905 marriage to millionaire Socialist J.G. Phelps Stokes) the era\'s most celebrated "Cinderella" of the radical left. Spoke alongside the People\'s Council in 1917. Convicted in March 1918 under the Espionage Act of 1917 for a letter to the Kansas City Star reading "No government which is for the profiteers can also be for the people, and I am for the people, while the government is for the profiteers." Sentenced by Judge Arba Van Valkenburgh to 10 years in federal prison. The 8th Circuit reversed the conviction in 1920 (Stokes v. United States) on the ground of an erroneous jury instruction; the government dropped the case in 1921. Co-founded the Communist Party USA in 1919.',
            'state' => 'Missouri',
            'race' => 'White',
            'gender' => 'Female',
            'birthdate' => '1879-07-18',
            'death_date' => '1933-06-20',
            'ideologies' => ['Socialist', 'Communist', 'Anti-war'],
            'affiliation' => ['Socialist Party of America', 'Communist Party USA', 'People\'s Council of America'],
            'era' => '1910s',
            'in_custody' => false,
            'released' => true,
            'cases' => [[
                'institution_state' => 'Missouri',
                'charges' => 'Espionage Act of 1917 — letter to the Kansas City Star opposing the war.',
                'arrest_date' => '1918-03-23',
                'sentenced_date' => '1918-06-01',
                'convicted' => 'Yes — overturned on appeal by the 8th Circuit (1920); government dropped case 1921.',
                'sentence' => '10 years federal prison; reversed on appeal; case dropped.',
            ]],
        ];

        $out[] = [
            'name' => 'Kate Richards O\'Hare',
            'first_name' => 'Kate',
            'last_name' => 'O\'Hare',
            'description' => 'Socialist Party of America agitator, editor of the National Rip-Saw, and one of the most-traveled anti-war speakers on the American left in 1916-17. After delivering an anti-war speech in the small town of Bowman, North Dakota on July 17, 1917, in which she said "any woman who gave up her son to be made a soldier was a brood sow," she was indicted under the Espionage Act and convicted in December 1917 by an all-male jury. Sentenced by Judge Martin Wade to 5 years at the Missouri State Penitentiary in Jefferson City — the longest sentence imposed on any woman under the Espionage Act. Served 14 months (April 1919 — May 1920) before President Wilson commuted her sentence in response to the campaign led by Crystal Eastman, Lillian Wald, and her own husband. Devoted the rest of her life to prison reform.',
            'state' => 'North Dakota',
            'race' => 'White',
            'gender' => 'Female',
            'birthdate' => '1876-03-26',
            'death_date' => '1948-01-10',
            'ideologies' => ['Socialist', 'Anti-war', 'Prison abolition'],
            'affiliation' => ['Socialist Party of America', 'National Rip-Saw'],
            'era' => '1910s',
            'in_custody' => false,
            'released' => true,
            'cases' => [[
                'institution_name' => 'Missouri State Penitentiary',
                'institution_state' => 'Missouri',
                'charges' => 'Espionage Act of 1917 — anti-war speech in Bowman, ND, July 17, 1917.',
                'arrest_date' => '1917-07-17',
                'sentenced_date' => '1917-12-14',
                'incarceration_date' => '1919-04-15',
                'release_date' => '1920-05-30',
                'convicted' => 'Yes — sentence commuted by President Wilson 1920.',
                'sentence' => '5 years at Missouri State Penitentiary; served 14 months before Wilson commutation.',
            ]],
        ];

        // === The Masses defendants (Nov 1917 indictment; two hung juries 1918) ===
        $massesDesc = 'Indicted in November 1917 along with editor Max Eastman, John Reed, and the staff of The Masses — the radical New York socialist literary magazine — under the Espionage Act of 1917 for conspiracy to obstruct military recruitment. The Post Office had already revoked the magazine\'s second-class mailing privileges, effectively shutting it down. The first trial (April 1918) and second trial (October 1918) both ended in hung juries; the government declined a third prosecution and the charges were dismissed. The Masses re-emerged as The Liberator under Eastman\'s editorship.';
        $massesCase = [
            'institution_state' => 'New York',
            'charges' => 'Espionage Act of 1917 — conspiracy to obstruct military recruitment (The Masses indictment).',
            'arrest_date' => '1917-11-19',
            'sentenced_date' => '1918-10-05',
            'convicted' => 'No — two hung juries (April 1918, October 1918); charges dismissed.',
            'sentence' => 'Two hung juries; charges dismissed.',
        ];
        $massesBase = [
            'state' => 'New York',
            'race' => 'White',
            'ideologies' => ['Socialist', 'Anti-war', 'Bohemian'],
            'affiliation' => ['The Masses'],
            'era' => '1910s',
            'in_custody' => false,
            'released' => true,
        ];
        $out[] = [
            'name' => 'Floyd Dell',
            'first_name' => 'Floyd',
            'last_name' => 'Dell',
            'description' => 'Greenwich Village novelist, Chicago Renaissance critic, and managing editor of The Masses (1914-17). '.$massesDesc.' Author of "Moon-Calf" (1920) and a leading figure of the post-WWI bohemian left.',
            'gender' => 'Male',
            'birthdate' => '1887-06-28',
            'death_date' => '1969-07-23',
            'cases' => [$massesCase],
        ] + $massesBase;
        $out[] = [
            'name' => 'Art Young',
            'aka' => 'Arthur Henry Young',
            'first_name' => 'Arthur',
            'middle_name' => 'Henry',
            'last_name' => 'Young',
            'description' => 'Wisconsin-born socialist cartoonist for Puck, Life, The Masses, and later The Liberator and The New Masses. '.$massesDesc.' His famous anti-war cartoon "Having Their Fling" — depicting an editor, capitalist, politician, and minister dancing to a chord struck by the Devil — was specifically singled out in the prosecution.',
            'gender' => 'Male',
            'birthdate' => '1866-01-14',
            'death_date' => '1943-12-29',
            'cases' => [$massesCase],
        ] + $massesBase;
        $out[] = [
            'name' => 'Merrill Rogers',
            'first_name' => 'Merrill',
            'last_name' => 'Rogers',
            'description' => 'Business manager of The Masses. '.$massesDesc.' Famously, during the trial Rogers would stand up and lead the courtroom in the national anthem whenever the band on the street outside struck up — repeatedly halting proceedings and infuriating Judge Augustus Hand.',
            'gender' => 'Male',
            'cases' => [$massesCase],
        ] + $massesBase;
        $out[] = [
            'name' => 'Josephine Bell',
            'first_name' => 'Josephine',
            'last_name' => 'Bell',
            'description' => 'Poet and Masses contributor. '.$massesDesc.' Bell was indicted specifically over her poem "A Tribute" — a sympathetic verse about jailed anarchist anti-conscription organizers Alexander Berkman and Emma Goldman published in The Masses; the indictment quoted her poem as proof of conspiracy to obstruct the draft. Charges dismissed against her with the rest of the defendants.',
            'gender' => 'Female',
            'cases' => [$massesCase],
        ] + $massesBase;

        // === Emily Greene Balch — Wellesley firing ===
        $out[] = [
            'name' => 'Emily Greene Balch',
            'first_name' => 'Emily',
            'middle_name' => 'Greene',
            'last_name' => 'Balch',
            'description' => 'Economist, sociologist, and pacifist; professor at Wellesley College (1896-1918). A founder of the Women\'s International League for Peace and Freedom (WILPF) and a delegate to the 1915 International Congress of Women at The Hague. After publicly opposing U.S. entry into WWI and joining the People\'s Council of America and American Union Against Militarism, she was denied reappointment at Wellesley in 1918 over her pacifism — one of the highest-profile academic firings of the war. Became international secretary of WILPF (1919-22) and devoted the next four decades to peace organizing, refugee work, and disarmament. Awarded the 1946 Nobel Peace Prize jointly with John R. Mott — the second American woman to receive it after Jane Addams.',
            'state' => 'Massachusetts',
            'race' => 'White',
            'gender' => 'Female',
            'birthdate' => '1867-01-08',
            'death_date' => '1961-01-09',
            'ideologies' => ['Pacifist', 'Socialist', 'Feminist'],
            'affiliation' => ['Women\'s International League for Peace and Freedom', 'People\'s Council of America', 'American Union Against Militarism'],
            'era' => '1910s',
            'in_custody' => false,
            'released' => true,
            'cases' => [[
                'institution_state' => 'Massachusetts',
                'charges' => 'No criminal charges — Wellesley College trustees denied reappointment over her People\'s Council / WILPF pacifism.',
                'arrest_date' => '1918-04-01',
                'sentence' => 'Fired from Wellesley faculty for anti-war activism; later 1946 Nobel Peace Prize laureate.',
            ]],
        ];

        return $out;
    }
}
