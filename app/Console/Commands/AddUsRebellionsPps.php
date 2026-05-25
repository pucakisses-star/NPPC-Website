<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds 25 PPs surfaced from Wikipedia's "List of rebellions in the
 * United States" deep crawl across less-covered rebellions:
 *
 *   - NC Regulator Movement 1771: Benjamin Merrill, James Few
 *   - Pointe Coupée Conspiracy (Spanish LA, 1795): Antoine Sarrasin,
 *     Jean-Baptiste
 *   - 1811 German Coast Uprising (LA, largest US slave revolt):
 *     Kook, Quamana, Harry Kenner
 *   - Denmark Vesey Conspiracy (SC, 1822): Peter Poyas, Gullah
 *     Jack, Monday Gell, Rolla Bennett, Ned Bennett
 *   - Christiana Resistance 1851: Castner Hanway, Elijah Lewis,
 *     Joseph Scarlett
 *   - Jerry Rescue 1851: Enoch Reed, Ira Cobb
 *   - Anthony Burns rendition 1854: Thomas Wentworth Higginson,
 *     Martin Stowell, Theodore Parker, Wendell Phillips
 *   - Boston Five anti-Vietnam draft resistance 1968: Benjamin
 *     Spock, Mitchell Goodman, Michael Ferber, Marcus Raskin
 *
 * Era values per project decade-string convention.
 */
final class AddUsRebellionsPps extends Command {
    protected $signature = 'archive:add-us-rebellions-pps';
    protected $description = 'Add 25 historical PPs from the US rebellions crawl';

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

        // === NC Regulator Movement 1771 ===
        $regulatorDesc = 'Carolina Regulator and participant in the colonial-era armed uprising of western North Carolina farmers (1768-71) against corrupt British colonial sheriffs, illegal court fees, and unfair tax collection. After Governor William Tryon\'s militia defeated the Regulators at the Battle of Alamance on May 16, 1771, six Regulators were summarily tried for treason and hanged.';
        $out[] = [
            'name' => 'Benjamin Merrill',
            'first_name' => 'Benjamin', 'last_name' => 'Merrill',
            'description' => $regulatorDesc.' Merrill was a Regulator captain who commanded approximately 300 men. He was sentenced to be hanged, drawn, and quartered — a sentence imposed at Hillsborough, NC on June 19, 1771.',
            'state' => 'North Carolina', 'race' => 'White', 'gender' => 'Male',
            'death_date' => '1771-06-19',
            'ideologies' => ['Anti-corruption', 'Colonial-era populist'],
            'affiliation' => ['Carolina Regulators'],
            'era' => '1770s', 'in_custody' => false, 'released' => false,
            'cases' => [[
                'institution_state' => 'North Carolina',
                'charges' => 'Treason against the British Crown (Regulator Movement).',
                'arrest_date' => '1771-05-16', 'sentenced_date' => '1771-06-19',
                'death_in_custody_date' => '1771-06-19',
                'convicted' => 'Yes.',
                'sentence' => 'Death — hanged, drawn, and quartered at Hillsborough, NC June 19, 1771.',
            ]],
        ];
        $out[] = [
            'name' => 'James Few',
            'first_name' => 'James', 'last_name' => 'Few',
            'description' => $regulatorDesc.' Few wounded Governor Tryon during the Battle of Alamance. He was summarily tried for treason and hanged the day after the battle (May 17, 1771).',
            'state' => 'North Carolina', 'race' => 'White', 'gender' => 'Male',
            'death_date' => '1771-05-17',
            'ideologies' => ['Anti-corruption', 'Colonial-era populist'],
            'affiliation' => ['Carolina Regulators'],
            'era' => '1770s', 'in_custody' => false, 'released' => false,
            'cases' => [[
                'institution_state' => 'North Carolina',
                'charges' => 'Treason against the British Crown.',
                'arrest_date' => '1771-05-16', 'sentenced_date' => '1771-05-17',
                'death_in_custody_date' => '1771-05-17',
                'convicted' => 'Yes — summary trial.',
                'sentence' => 'Death; hanged May 17, 1771.',
            ]],
        ];

        // === Pointe Coupée Conspiracy 1795 ===
        $pointeDesc = 'Enslaved leader of the 1795 Pointe Coupée Conspiracy in Spanish Louisiana — a planned uprising inspired by the Haitian Revolution and the abolitionist French Convention. The plot was discovered before launch; Spanish authorities executed approximately 28 conspirators, displaying their heads on poles along the Mississippi River as a warning.';
        foreach (['Antoine Sarrasin' => ['Antoine', 'Sarrasin'], 'Jean-Baptiste' => ['Jean-Baptiste', '']] as $name => [$first, $last]) {
            $p = [
                'name' => $name,
                'first_name' => $first,
                'description' => $pointeDesc.' One of the principal leaders of the conspiracy; hanged and decapitated 1795.',
                'state' => 'Louisiana', 'race' => 'Black', 'gender' => 'Male',
                'death_date' => '1795-06-01',
                'ideologies' => ['Anti-slavery', 'Haitian-inspired Black freedom'],
                'affiliation' => ['Pointe Coupée Conspiracy'],
                'era' => '1790s', 'in_custody' => false, 'released' => false,
                'cases' => [[
                    'institution_state' => 'Louisiana',
                    'charges' => 'Conspiracy to revolt (Pointe Coupée 1795).',
                    'arrest_date' => '1795-04-01',
                    'death_in_custody_date' => '1795-06-01',
                    'convicted' => 'Yes — by Spanish colonial tribunal.',
                    'sentence' => 'Death; hanged and decapitated; head displayed publicly.',
                ]],
            ];
            if ($last) $p['last_name'] = $last;
            $out[] = $p;
        }

        // === 1811 German Coast Uprising ===
        $germanDesc = 'Leader of the January 1811 German Coast Uprising in Louisiana — the largest slave revolt in U.S. history, in which roughly 500 enslaved people marched on New Orleans burning plantations and seizing weapons. White militia and federal troops killed approximately 95 insurgents and executed roughly 44 captured leaders; their heads were placed on poles along the River Road as warnings.';
        $germanData = [
            'Kook' => 'Akan-born enslaved insurgent who killed planter François Trépagnier with an axe during the uprising.',
            'Quamana' => 'Akan-born co-commander alongside Kook and Charles Deslondes.',
            'Harry Kenner' => 'Enslaved carpenter who recruited the English-speaking faction of insurgents.',
        ];
        foreach ($germanData as $name => $bio) {
            $parts = explode(' ', $name);
            $p = [
                'name' => $name,
                'first_name' => $parts[0],
                'description' => $germanDesc.' '.$bio,
                'state' => 'Louisiana', 'race' => 'Black', 'gender' => 'Male',
                'death_date' => '1811-01-15',
                'ideologies' => ['Anti-slavery', 'Black freedom', 'Akan resistance'],
                'affiliation' => ['1811 German Coast Uprising'],
                'era' => '1810s', 'in_custody' => false, 'released' => false,
                'cases' => [[
                    'institution_state' => 'Louisiana',
                    'charges' => 'Insurrection (1811 German Coast Uprising).',
                    'arrest_date' => '1811-01-10',
                    'death_in_custody_date' => '1811-01-15',
                    'convicted' => 'Yes — by Destrehan tribunal.',
                    'sentence' => 'Death; executed; head displayed on River Road.',
                ]],
            ];
            if (count($parts) > 1) $p['last_name'] = $parts[1];
            $out[] = $p;
        }

        // === Denmark Vesey Conspiracy 1822 ===
        $veseyDesc = 'Lieutenant in the 1822 Denmark Vesey Conspiracy in Charleston, South Carolina — a planned mass uprising of enslaved people inspired by Haiti, which intended to seize the city\'s arsenal, free Charleston\'s enslaved population, and sail for Haiti. The plot was betrayed before launch. South Carolina prosecuted 131 enslaved people; 35 were hanged, 31 were exiled, and 27 acquitted.';
        $veseyData = [
            'Peter Poyas' => ['Literate ship carpenter and Vesey\'s top lieutenant. Hanged June 1822 alongside Vesey; refused to confess despite torture.', 'hanged'],
            'Gullah Jack' => ['Angolan-born obeah (spiritual practitioner) who gave the conspirators spiritual protection charms; key recruiter of the African-born faction. Hanged July 12, 1822.', 'hanged'],
            'Monday Gell' => ['Harness-maker who ran the conspiracy\'s communication network with plantations on the city outskirts. Sentenced to death; sentence commuted to transportation out of South Carolina after his testimony.', 'transported'],
            'Rolla Bennett' => ['Enslaved by Governor Thomas Bennett Jr. Trusted lieutenant in the conspiracy. Hanged July 2, 1822.', 'hanged'],
            'Ned Bennett' => ['Enslaved by Governor Bennett. Co-conspirator with his brother Rolla. Hanged July 2, 1822.', 'hanged'],
        ];
        foreach ($veseyData as $name => [$bio, $fate]) {
            $parts = explode(' ', $name);
            $p = [
                'name' => $name,
                'first_name' => $parts[0],
                'description' => $veseyDesc.' '.$bio,
                'state' => 'South Carolina', 'race' => 'Black', 'gender' => 'Male',
                'ideologies' => ['Anti-slavery', 'Black freedom'],
                'affiliation' => ['Denmark Vesey Conspiracy'],
                'era' => '1820s', 'in_custody' => false, 'released' => $fate !== 'hanged',
                'cases' => [[
                    'institution_state' => 'South Carolina',
                    'charges' => 'Conspiracy to incite slave insurrection (Denmark Vesey Conspiracy, 1822).',
                    'arrest_date' => '1822-06-18',
                    'convicted' => 'Yes — by Charleston Court of Magistrates and Freeholders.',
                    'sentence' => $fate === 'hanged' ? 'Death by hanging.' : 'Death sentence commuted to transportation out of South Carolina.',
                ]],
            ];
            if ($fate === 'hanged') $p['death_date'] = '1822-07-12';
            if (count($parts) > 1) $p['last_name'] = $parts[1];
            $out[] = $p;
        }

        // === Christiana Resistance 1851 ===
        $christianaDesc = 'Defendant in the federal treason prosecution following the September 11, 1851 Christiana Resistance — an armed defense by free Black and formerly-enslaved residents of Christiana, Pennsylvania against slave-catcher Edward Gorsuch, who was killed in the confrontation. The Fillmore administration charged 38 men with treason in what was then the largest federal treason prosecution in U.S. history.';
        foreach ([
            'Castner Hanway' => ['White Quaker miller who refused U.S. Marshal Henry Kline\'s order to assist in capturing the fugitives. The lead test case for the government\'s treason theory.', 'acquitted in 15 minutes'],
            'Elijah Lewis' => ['White Quaker shopkeeper who similarly refused to assist the marshal.', 'charges dropped after Hanway acquittal'],
            'Joseph Scarlett' => ['White Quaker neighbor of the Black community who refused to help the marshal.', 'charges dropped'],
        ] as $name => [$bio, $outcome]) {
            $parts = explode(' ', $name);
            $out[] = [
                'name' => $name,
                'first_name' => $parts[0], 'last_name' => $parts[1],
                'description' => $christianaDesc.' '.$bio.' Outcome: '.$outcome.'.',
                'state' => 'Pennsylvania', 'race' => 'White', 'gender' => 'Male',
                'ideologies' => ['Abolitionist', 'Quaker'],
                'affiliation' => ['Christiana Resistance'],
                'era' => '1850s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'institution_state' => 'Pennsylvania',
                    'charges' => 'Treason against the United States (Christiana Resistance, September 11, 1851).',
                    'arrest_date' => '1851-09-15', 'sentenced_date' => '1851-12-12',
                    'convicted' => str_starts_with($outcome, 'acquitted') ? 'No — acquitted in 15 minutes.' : 'No — charges dropped after Hanway acquittal.',
                    'sentence' => $outcome === 'acquitted in 15 minutes' ? 'Acquitted.' : 'Charges dropped.',
                ]],
            ];
        }

        // === Jerry Rescue 1851 ===
        $jerryDesc = 'Defendant in the federal prosecutions following the October 1, 1851 Jerry Rescue in Syracuse, NY — when a multiracial mob of abolitionists broke fugitive William "Jerry" Henry out of federal custody during his Fugitive Slave Act extradition hearing and helped him escape to Canada. Twenty-six men were indicted; only Reed was convicted before the case effectively collapsed under jury nullification.';
        $out[] = [
            'name' => 'Enoch Reed',
            'first_name' => 'Enoch', 'last_name' => 'Reed',
            'description' => $jerryDesc.' Black participant — the only Jerry Rescue defendant convicted under the Fugitive Slave Act (1853, for resisting a federal officer). Died before his appeal could be heard.',
            'state' => 'New York', 'race' => 'Black', 'gender' => 'Male',
            'ideologies' => ['Abolitionist'],
            'affiliation' => ['Syracuse Vigilance Committee', 'Jerry Rescue'],
            'era' => '1850s', 'in_custody' => false, 'released' => false,
            'cases' => [[
                'institution_state' => 'New York',
                'charges' => 'Resisting a federal officer (Jerry Rescue, October 1, 1851).',
                'arrest_date' => '1851-10-10', 'sentenced_date' => '1853-06-01',
                'convicted' => 'Yes — died before appeal.',
                'sentence' => 'Conviction stood; died before appeal.',
            ]],
        ];
        $out[] = [
            'name' => 'Ira Cobb',
            'first_name' => 'Ira', 'last_name' => 'Cobb',
            'description' => $jerryDesc.' Vigilance committee planner of the rescue; tried in 1853 but the case ended in a hung jury.',
            'state' => 'New York', 'race' => 'White', 'gender' => 'Male',
            'ideologies' => ['Abolitionist'],
            'affiliation' => ['Syracuse Vigilance Committee', 'Jerry Rescue'],
            'era' => '1850s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'institution_state' => 'New York',
                'charges' => 'Resisting a federal officer (Jerry Rescue).',
                'arrest_date' => '1851-10-10', 'sentenced_date' => '1853-06-01',
                'convicted' => 'No — hung jury.',
                'sentence' => 'Hung jury; charges effectively dropped.',
            ]],
        ];

        // === Anthony Burns rendition / Boston Slave Riot 1854 ===
        $burnsDesc = 'Boston abolitionist indicted in connection with the May 26, 1854 storming of the Boston Court House by an axe-wielding mob attempting to free Anthony Burns from Fugitive Slave Act rendition. U.S. Marshal James Batchelder was killed in the assault. The Pierce administration brought federal charges against the leadership of the Boston Vigilance Committee.';
        $burnsData = [
            'Thomas Wentworth Higginson' => 'Unitarian minister and abolitionist who led the axe-wielding attempt to storm the Boston Court House. Indicted for obstructing Burns\'s rendition; charges ultimately dismissed (nolle prosequi).',
            'Martin Stowell' => 'Worcester abolitionist; indicted on assault and murder charges for the Batchelder killing. Tried but the jury hung; charges dropped.',
            'Theodore Parker' => 'Boston Unitarian minister and Transcendentalist; gave the inflammatory Faneuil Hall speech the night before the courthouse assault. Indicted for obstructing the rendition; the indictment was quashed before trial.',
            'Wendell Phillips' => 'Boston abolitionist orator and Vigilance Committee leader; indicted alongside Parker for obstructing the rendition. Charges dropped (nolle prosequi).',
        ];
        foreach ($burnsData as $name => $bio) {
            $parts = explode(' ', $name);
            $out[] = [
                'name' => $name,
                'first_name' => $parts[0],
                'middle_name' => count($parts) === 3 ? $parts[1] : null,
                'last_name' => end($parts),
                'description' => $burnsDesc.' '.$bio,
                'state' => 'Massachusetts', 'race' => 'White', 'gender' => 'Male',
                'ideologies' => ['Abolitionist'],
                'affiliation' => ['Boston Vigilance Committee'],
                'era' => '1850s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'institution_state' => 'Massachusetts',
                    'charges' => 'Obstructing the rendition of a fugitive from labor; related charges (Boston Slave Riot, May 26, 1854).',
                    'arrest_date' => '1854-06-07', 'sentenced_date' => '1855-04-03',
                    'convicted' => 'No — charges dismissed.',
                    'sentence' => 'Charges dismissed.',
                ]],
            ];
        }

        // === Boston Five 1968 ===
        $bostonFiveDesc = 'One of the "Boston Five" defendants in United States v. Spock (1968) — the federal conspiracy prosecution of five prominent antiwar leaders for "conspiring to counsel, aid, and abet draft refusal" through public statements like "A Call to Resist Illegitimate Authority" and the October 1967 draft-card return ceremonies. The First Circuit Court of Appeals overturned all four convictions in 1969.';
        $bostonFiveData = [
            'Benjamin Spock' => ['World-famous pediatrician and author of "The Common Sense Book of Baby and Child Care." Convicted; sentenced to 2 years; conviction overturned on appeal.', '2yrs overturned'],
            'Mitchell Goodman' => ['Novelist and co-organizer of the October 1967 nationwide draft-card turn-in. Convicted; 2 years; overturned.', '2yrs overturned'],
            'Michael Ferber' => ['Harvard graduate student and the youngest of the Boston Five. Convicted; 2 years; overturned.', '2yrs overturned'],
            'Marcus Raskin' => ['Co-founder of the Institute for Policy Studies (IPS). The only Boston Five defendant acquitted at the original trial.', 'acquitted'],
        ];
        foreach ($bostonFiveData as $name => [$bio, $sentence]) {
            $parts = explode(' ', $name);
            $out[] = [
                'name' => $name,
                'first_name' => $parts[0], 'last_name' => end($parts),
                'description' => $bostonFiveDesc.' '.$bio,
                'state' => 'Massachusetts', 'race' => 'White', 'gender' => 'Male',
                'ideologies' => ['Anti-Vietnam War', 'Draft resistance'],
                'affiliation' => ['Boston Five'],
                'era' => '1960s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'institution_state' => 'Massachusetts',
                    'charges' => 'Conspiracy to counsel, aid, and abet refusal of service in the armed forces.',
                    'arrest_date' => '1968-01-05', 'sentenced_date' => '1968-07-10',
                    'convicted' => $sentence === 'acquitted' ? 'No — acquitted.' : 'Yes — overturned on appeal 1969.',
                    'sentence' => $sentence === 'acquitted' ? 'Acquitted.' : '2 years; conviction overturned by First Circuit 1969.',
                ]],
            ];
        }

        return $out;
    }
}
