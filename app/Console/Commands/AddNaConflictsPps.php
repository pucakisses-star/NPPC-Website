<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds 19 historical political prisoners surfaced from the deep
 * crawl of Wikipedia's "List of conflicts in North America" article.
 * Spans 1786-1907 across:
 *
 *   - Early US settler rebellions: Shays' Rebellion (2 hanged),
 *     Whiskey Rebellion (2 pardoned by Washington), Fries's
 *     Rebellion (3, pardoned by Adams)
 *   - John Brown's Raid: Edwin Coppock (hanged)
 *   - Mexican-American War: 4 San Patricio Battalion defectors
 *     (Irish/German US Army soldiers who fought for Mexico)
 *   - Indigenous resistance POWs held by US: Geronimo (23 yrs at
 *     Fort Sill), Black Hawk, Manuelito, Crazy Horse (bayoneted
 *     during arrest at Fort Robinson)
 *   - Philippine-American War: Macario Sakay (hanged), Emilio
 *     Aguinaldo (captured), Vicente Lukbán
 *   - Labor war: Hugh Dempsey (KOL, Homestead 1892)
 *
 * Era values per project decade-string convention. Pre-1800 dates
 * get era "1780s" / "1790s"; for Indigenous and Filipino leaders
 * captured by US, era is the decade of capture.
 *
 * Idempotent — prisoner:add refuses duplicates by name.
 */
final class AddNaConflictsPps extends Command {
    protected $signature = 'archive:add-na-conflicts-pps';
    protected $description = 'Add 19 historical PPs from North America conflicts crawl';

    public function handle(): int {
        $added = 0; $skipped = 0;

        foreach ($this->prisoners() as $payload) {
            $exit = $this->call('prisoner:add', ['json' => json_encode($payload)]);
            if ($exit === self::SUCCESS) {
                $this->info('ADD: '.$payload['name']);
                $added++;
            } else {
                $skipped++;
            }
        }

        $this->info("Done — added {$added}, skipped {$skipped}.");
        return self::SUCCESS;
    }

    /** @return array<int, array<string, mixed>> */
    private function prisoners(): array {
        return [
            // === Shays' Rebellion ===
            [
                'name' => 'John Bly',
                'first_name' => 'John',
                'last_name' => 'Bly',
                'description' => 'Berkshire County (Massachusetts) participant in Shays\' Rebellion — the 1786-87 western Massachusetts uprising of indebted Revolutionary War veterans and farmers against state foreclosures, regressive taxes, and creditor courts. After the state militia broke the rebels at the Springfield Armory, Bly and Charles Rose were convicted of rebellion and looting. Despite a broad amnesty granted by Governor John Hancock, Bly and Rose were hanged at Lenox on December 6, 1787 — the only two participants executed.',
                'state' => 'Massachusetts',
                'gender' => 'Male',
                'death_date' => '1787-12-06',
                'ideologies' => ['Anti-creditor', 'Veterans\' organizing'],
                'affiliation' => ['Shays\' Rebellion'],
                'era' => '1780s',
                'in_custody' => false,
                'released' => false,
                'cases' => [[
                    'institution_state' => 'Massachusetts',
                    'charges' => 'Rebellion and looting (Shays\' Rebellion).',
                    'arrest_date' => '1787-02-04',
                    'death_in_custody_date' => '1787-12-06',
                    'convicted' => 'Yes.',
                    'sentence' => 'Death; hanged at Lenox, MA, December 6, 1787.',
                ]],
            ],
            [
                'name' => 'Charles Rose',
                'first_name' => 'Charles',
                'last_name' => 'Rose',
                'description' => 'Berkshire County participant in Shays\' Rebellion (1786-87). Convicted of rebellion and hanged alongside John Bly at Lenox, MA on December 6, 1787 — one of only two participants executed despite Governor Hancock\'s broad amnesty.',
                'state' => 'Massachusetts',
                'gender' => 'Male',
                'death_date' => '1787-12-06',
                'ideologies' => ['Anti-creditor', 'Veterans\' organizing'],
                'affiliation' => ['Shays\' Rebellion'],
                'era' => '1780s',
                'in_custody' => false,
                'released' => false,
                'cases' => [[
                    'institution_state' => 'Massachusetts',
                    'charges' => 'Rebellion (Shays\' Rebellion).',
                    'arrest_date' => '1787-02-04',
                    'death_in_custody_date' => '1787-12-06',
                    'convicted' => 'Yes.',
                    'sentence' => 'Death; hanged at Lenox, MA, December 6, 1787.',
                ]],
            ],

            // === Whiskey Rebellion ===
            [
                'name' => 'Philip Wigle',
                'first_name' => 'Philip',
                'last_name' => 'Wigle',
                'description' => 'Western Pennsylvania farmer-distiller and Whiskey Rebellion (1791-94) participant who beat federal tax collector Robert Johnson and burned his home in protest of Alexander Hamilton\'s excise tax on whiskey. One of only two men convicted of treason (the rebellion produced the first treason convictions in the new United States). Sentenced to hang; pardoned by President George Washington on November 2, 1795.',
                'state' => 'Pennsylvania',
                'gender' => 'Male',
                'ideologies' => ['Anti-tax', 'Frontier farmer'],
                'affiliation' => ['Whiskey Rebellion'],
                'era' => '1790s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Pennsylvania',
                    'charges' => 'Treason against the United States.',
                    'arrest_date' => '1794-11-13',
                    'sentenced_date' => '1795-05-25',
                    'release_date' => '1795-11-02',
                    'convicted' => 'Yes — pardoned by Washington November 2, 1795.',
                    'sentence' => 'Death; pardoned.',
                ]],
            ],
            [
                'name' => 'John Mitchell',
                'first_name' => 'John',
                'last_name' => 'Mitchell',
                'description' => 'Western Pennsylvania Whiskey Rebellion participant convicted of robbing the U.S. mail in 1794 — at the urging of insurgent leader David Bradford — as part of the broader resistance to Alexander Hamilton\'s excise tax. One of only two men convicted of treason in the rebellion. Sentenced to hang; pardoned by President Washington November 2, 1795.',
                'state' => 'Pennsylvania',
                'gender' => 'Male',
                'ideologies' => ['Anti-tax', 'Frontier farmer'],
                'affiliation' => ['Whiskey Rebellion'],
                'era' => '1790s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Pennsylvania',
                    'charges' => 'Treason against the United States (mail robbery in furtherance of the Whiskey Rebellion).',
                    'arrest_date' => '1794-11-13',
                    'sentenced_date' => '1795-05-25',
                    'release_date' => '1795-11-02',
                    'convicted' => 'Yes — pardoned by Washington November 2, 1795.',
                    'sentence' => 'Death; pardoned.',
                ]],
            ],

            // === Fries's Rebellion ===
            [
                'name' => 'John Fries',
                'first_name' => 'John',
                'last_name' => 'Fries',
                'description' => 'Pennsylvania German auctioneer and Revolutionary War militia veteran who led an armed party of Bucks County farmers to free tax resisters held at Bethlehem in March 1799 — the central act of Fries\'s Rebellion against the federal Direct Tax of 1798. Convicted of treason at his second trial in 1800; sentenced to hang. Pardoned by President John Adams just before the scheduled execution.',
                'state' => 'Pennsylvania',
                'gender' => 'Male',
                'birthdate' => '1750-01-01',
                'death_date' => '1818-02-01',
                'ideologies' => ['Anti-tax', 'Frontier farmer'],
                'affiliation' => ['Fries\'s Rebellion'],
                'era' => '1790s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Pennsylvania',
                    'charges' => 'Treason against the United States.',
                    'arrest_date' => '1799-04-04',
                    'sentenced_date' => '1800-04-30',
                    'release_date' => '1800-05-21',
                    'convicted' => 'Yes — pardoned by Adams May 21, 1800.',
                    'sentence' => 'Death; pardoned by President Adams.',
                ]],
            ],
            [
                'name' => 'Frederick Heaney',
                'first_name' => 'Frederick',
                'last_name' => 'Heaney',
                'description' => 'Pennsylvania German farmer convicted of treason at the 1800 Fries\'s Rebellion trials. Sentenced to hang; pardoned by President John Adams alongside John Fries.',
                'state' => 'Pennsylvania',
                'gender' => 'Male',
                'ideologies' => ['Anti-tax'],
                'affiliation' => ['Fries\'s Rebellion'],
                'era' => '1790s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Pennsylvania',
                    'charges' => 'Treason against the United States (Fries\'s Rebellion).',
                    'arrest_date' => '1799-04-04',
                    'sentenced_date' => '1800-04-30',
                    'release_date' => '1800-05-21',
                    'convicted' => 'Yes — pardoned by Adams May 21, 1800.',
                    'sentence' => 'Death; pardoned.',
                ]],
            ],
            [
                'name' => 'John Getman',
                'first_name' => 'John',
                'last_name' => 'Getman',
                'description' => 'Pennsylvania German farmer convicted of treason at the 1800 Fries\'s Rebellion trials. Sentenced to hang; pardoned by President John Adams alongside Fries and Heaney.',
                'state' => 'Pennsylvania',
                'gender' => 'Male',
                'ideologies' => ['Anti-tax'],
                'affiliation' => ['Fries\'s Rebellion'],
                'era' => '1790s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Pennsylvania',
                    'charges' => 'Treason against the United States (Fries\'s Rebellion).',
                    'arrest_date' => '1799-04-04',
                    'sentenced_date' => '1800-04-30',
                    'release_date' => '1800-05-21',
                    'convicted' => 'Yes — pardoned by Adams May 21, 1800.',
                    'sentence' => 'Death; pardoned.',
                ]],
            ],

            // === John Brown's Raid ===
            [
                'name' => 'Edwin Coppock',
                'first_name' => 'Edwin',
                'last_name' => 'Coppock',
                'description' => 'White abolitionist raider with John Brown\'s Harpers Ferry raid in October 1859. Coppock fatally shot Harpers Ferry mayor Fontaine Beckham during the raid; captured along with Brown; convicted of treason, murder, and inciting slave insurrection. Hanged at Charles Town, Virginia on December 16, 1859 — two weeks after Brown.',
                'state' => 'Iowa',
                'race' => 'White',
                'gender' => 'Male',
                'birthdate' => '1835-06-30',
                'death_date' => '1859-12-16',
                'ideologies' => ['Abolitionist'],
                'affiliation' => ['John Brown\'s Provisional Army'],
                'era' => '1850s',
                'in_custody' => false,
                'released' => false,
                'cases' => [[
                    'institution_name' => 'Charles Town Jail',
                    'institution_state' => 'Virginia',
                    'charges' => 'Treason against the Commonwealth of Virginia; murder; conspiring with slaves to rebel.',
                    'arrest_date' => '1859-10-18',
                    'sentenced_date' => '1859-11-02',
                    'death_in_custody_date' => '1859-12-16',
                    'convicted' => 'Yes.',
                    'sentence' => 'Death; hanged at Charles Town, VA December 16, 1859.',
                ]],
            ],

            // === Mexican-American War / San Patricio Battalion ===
            [
                'name' => 'John Riley',
                'first_name' => 'John',
                'last_name' => 'Riley',
                'description' => 'Irish-born U.S. Army soldier who defected before the Mexican-American War and founded the San Patricio Battalion (Batallón de San Patricio) — a unit of mostly Irish, German, and other Catholic U.S. Army defectors who fought for the Mexican Republic against U.S. invasion. Captured after the Battle of Churubusco in August 1847. Court-martialed; because his desertion technically pre-dated the formal declaration of war, he escaped the death sentence. Sentenced instead to 50 lashes, branding with the letter "D" on his cheek, and an iron yoke around his neck. Released by Mexico after the war and lived out his days in Veracruz.',
                'state' => 'New York',
                'race' => 'White',
                'gender' => 'Male',
                'birthdate' => '1817-02-01',
                'death_date' => '1850-08-31',
                'ideologies' => ['Catholic solidarity with Mexico', 'Anti-Yankee imperialism'],
                'affiliation' => ['San Patricio Battalion / Batallón de San Patricio'],
                'era' => '1840s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Mexico',
                    'charges' => 'Desertion (court-martialed by U.S. Army occupying forces in Mexico, 1847).',
                    'arrest_date' => '1847-08-20',
                    'sentenced_date' => '1847-09-09',
                    'convicted' => 'Yes.',
                    'sentence' => '50 lashes; branded with "D" on cheek; iron yoke around neck; escaped death only because his desertion pre-dated formal declaration of war.',
                ]],
            ],
            [
                'name' => 'Patrick Dalton',
                'first_name' => 'Patrick',
                'last_name' => 'Dalton',
                'description' => 'Irish-born former U.S. Army sergeant who defected and rose to captain in the San Patricio Battalion fighting for Mexico in the Mexican-American War. Captured after the Battle of Churubusco. Court-martialed and hanged at San Ángel, Mexico on September 10, 1847.',
                'race' => 'White',
                'gender' => 'Male',
                'death_date' => '1847-09-10',
                'ideologies' => ['Catholic solidarity with Mexico', 'Anti-Yankee imperialism'],
                'affiliation' => ['San Patricio Battalion'],
                'era' => '1840s',
                'in_custody' => false,
                'released' => false,
                'cases' => [[
                    'institution_state' => 'Mexico',
                    'charges' => 'Desertion in time of war (U.S. Army court-martial in occupied Mexico).',
                    'sentenced_date' => '1847-09-09',
                    'death_in_custody_date' => '1847-09-10',
                    'convicted' => 'Yes.',
                    'sentence' => 'Death; hanged at San Ángel, September 10, 1847.',
                ]],
            ],
            [
                'name' => 'Francis O\'Connor',
                'first_name' => 'Francis',
                'last_name' => 'O\'Connor',
                'description' => 'San Patricio Battalion sergeant. Wounded so severely at Churubusco that both his legs were amputated before his court-martial. Despite his condition, U.S. authorities still hanged him at Chapultepec on September 13, 1847.',
                'race' => 'White',
                'gender' => 'Male',
                'death_date' => '1847-09-13',
                'ideologies' => ['Catholic solidarity with Mexico'],
                'affiliation' => ['San Patricio Battalion'],
                'era' => '1840s',
                'in_custody' => false,
                'released' => false,
                'cases' => [[
                    'institution_state' => 'Mexico',
                    'charges' => 'Desertion in time of war.',
                    'sentenced_date' => '1847-09-12',
                    'death_in_custody_date' => '1847-09-13',
                    'convicted' => 'Yes.',
                    'sentence' => 'Death; hanged at Chapultepec September 13, 1847 with both legs amputated.',
                ]],
            ],
            [
                'name' => 'Santiago O\'Leary',
                'first_name' => 'Santiago',
                'last_name' => 'O\'Leary',
                'description' => 'San Patricio Battalion captain wounded and captured at the Battle of Churubusco in August 1847; court-martialed by U.S. occupying forces.',
                'race' => 'White',
                'gender' => 'Male',
                'ideologies' => ['Catholic solidarity with Mexico'],
                'affiliation' => ['San Patricio Battalion'],
                'era' => '1840s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Mexico',
                    'charges' => 'Desertion in time of war.',
                    'sentenced_date' => '1847-09-01',
                    'convicted' => 'Yes.',
                    'sentence' => 'Court-martial sentence not fully documented.',
                ]],
            ],

            // === Indigenous resistance POWs ===
            [
                'name' => 'Geronimo',
                'aka' => 'Goyaałé',
                'first_name' => 'Geronimo',
                'description' => 'Chiricahua Apache leader of the last major Indigenous armed resistance to U.S. expansion in the Southwest. Surrendered to General Nelson Miles on September 4, 1886 after years of guerrilla resistance to forced relocation. Imprisoned by the U.S. Army for 23 years as a prisoner of war — first at Fort Pickens (Florida), then Mount Vernon Barracks (Alabama), then Fort Sill (Oklahoma) where he died on February 17, 1909. Never permitted to return to his Arizona homeland.',
                'state' => 'Oklahoma',
                'race' => 'Indigenous',
                'gender' => 'Male',
                'birthdate' => '1829-06-16',
                'death_date' => '1909-02-17',
                'ideologies' => ['Chiricahua Apache sovereignty', 'Anti-colonial resistance'],
                'affiliation' => ['Chiricahua Apache'],
                'era' => '1880s',
                'in_custody' => false,
                'released' => false,
                'cases' => [[
                    'institution_name' => 'Fort Pickens / Mount Vernon Barracks / Fort Sill',
                    'institution_state' => 'Oklahoma',
                    'charges' => 'Held as prisoner of war by the U.S. Army following surrender.',
                    'arrest_date' => '1886-09-04',
                    'death_in_custody_date' => '1909-02-17',
                    'sentence' => '23 years as a U.S. Army prisoner of war; died in custody at Fort Sill, OK February 17, 1909.',
                ]],
            ],
            [
                'name' => 'Black Hawk',
                'aka' => 'Ma-ka-tai-me-she-kia-kiak',
                'first_name' => 'Black',
                'last_name' => 'Hawk',
                'description' => 'Sauk war leader who led the 1832 Black Hawk War — the last major armed Indigenous resistance east of the Mississippi — against the forced removal of his band from their ancestral lands in present-day Illinois. Surrendered August 1832; held for eight months at Jefferson Barracks and Fortress Monroe as a U.S. Army prisoner. Released and toured eastern U.S. cities; dictated his autobiography "Life of Ma-ka-tai-me-she-kia-kiak" in 1833 — the first Indigenous autobiography published in the United States.',
                'state' => 'Iowa',
                'race' => 'Indigenous',
                'gender' => 'Male',
                'birthdate' => '1767-01-01',
                'death_date' => '1838-10-03',
                'ideologies' => ['Sauk sovereignty', 'Anti-removal'],
                'affiliation' => ['Sauk Nation'],
                'era' => '1830s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Jefferson Barracks / Fortress Monroe',
                    'institution_state' => 'Missouri',
                    'charges' => 'Held as prisoner of war following surrender (Black Hawk War, August 1832).',
                    'arrest_date' => '1832-08-27',
                    'release_date' => '1833-06-04',
                    'sentence' => '~8 months as a U.S. Army prisoner of war.',
                ]],
            ],
            [
                'name' => 'Manuelito',
                'aka' => 'Hastiin Ch\'il Haajiní',
                'first_name' => 'Manuelito',
                'description' => 'Navajo (Diné) war leader who led armed resistance to Kit Carson\'s 1863-66 scorched-earth campaign against the Navajo and the subsequent forced "Long Walk" deportation to the Bosque Redondo concentration camp at Fort Sumner, New Mexico. One of the last Navajo leaders to surrender (September 1866); held at Bosque Redondo until the 1868 Treaty of Bosque Redondo (which Manuelito signed) permitted the Navajo to return home. He continued as a principal chief of the Navajo until his death in 1893.',
                'state' => 'New Mexico',
                'race' => 'Indigenous',
                'gender' => 'Male',
                'birthdate' => '1818-01-01',
                'death_date' => '1893-01-01',
                'ideologies' => ['Diné sovereignty', 'Anti-removal'],
                'affiliation' => ['Navajo Nation'],
                'era' => '1860s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Bosque Redondo (Fort Sumner)',
                    'institution_state' => 'New Mexico',
                    'charges' => 'Held with the Navajo Nation at Bosque Redondo concentration camp following surrender.',
                    'arrest_date' => '1866-09-01',
                    'release_date' => '1868-06-01',
                    'sentence' => '~2 years at Bosque Redondo concentration camp until the 1868 Treaty permitted Navajo return to their homeland.',
                ]],
            ],
            [
                'name' => 'Crazy Horse',
                'aka' => 'Tȟašúŋke Witkó',
                'first_name' => 'Crazy',
                'last_name' => 'Horse',
                'description' => 'Oglala Lakota war leader, principal Lakota commander at the Battle of the Little Bighorn (June 25, 1876) where U.S. Lt. Col. George Custer\'s 7th Cavalry was destroyed. After a harsh winter and U.S. Army campaigns of attrition, Crazy Horse surrendered with his band at Fort Robinson, Nebraska on May 5, 1877. He was bayoneted to death by an Army guard while being taken into a guardhouse for what authorities claimed was protective custody on September 5, 1877. His killing is widely understood as deliberate political assassination.',
                'state' => 'South Dakota',
                'race' => 'Indigenous',
                'gender' => 'Male',
                'death_date' => '1877-09-05',
                'ideologies' => ['Oglala Lakota sovereignty', 'Anti-colonial resistance'],
                'affiliation' => ['Oglala Lakota'],
                'era' => '1870s',
                'in_custody' => false,
                'released' => false,
                'cases' => [[
                    'institution_name' => 'Fort Robinson',
                    'institution_state' => 'Nebraska',
                    'charges' => 'Held by U.S. Army after surrender; killed during forcible detention.',
                    'arrest_date' => '1877-05-05',
                    'death_in_custody_date' => '1877-09-05',
                    'sentence' => 'Bayoneted to death by an Army guard at Fort Robinson on September 5, 1877.',
                ]],
            ],

            // === Philippine-American War ===
            [
                'name' => 'Macario Sakay',
                'first_name' => 'Macario',
                'last_name' => 'Sakay',
                'description' => 'Filipino general and president of the Tagalog Republic — the continuation of the Philippine Republic\'s armed resistance to the U.S. occupation after Emilio Aguinaldo\'s 1901 capture. Operated guerrilla campaigns from the mountains of Luzon for years. Lured down from the mountains in July 1906 with promises of amnesty and Philippine Assembly participation, then arrested. Convicted by the U.S. colonial authorities under the Brigandage Act of 1902 — which reclassified armed political resistance as banditry — and hanged at Bilibid Prison in Manila on September 13, 1907.',
                'state' => 'Philippines',
                'race' => 'Asian',
                'gender' => 'Male',
                'birthdate' => '1870-03-01',
                'death_date' => '1907-09-13',
                'ideologies' => ['Philippine independence', 'Anti-colonial'],
                'affiliation' => ['Tagalog Republic', 'Philippine Republic'],
                'era' => '1900s',
                'in_custody' => false,
                'released' => false,
                'cases' => [[
                    'institution_name' => 'Bilibid Prison (Manila)',
                    'institution_state' => 'Philippines',
                    'charges' => 'Brigandage Act of 1902 (the U.S. colonial law reclassifying armed political resistance as banditry).',
                    'arrest_date' => '1906-07-17',
                    'sentenced_date' => '1907-08-01',
                    'death_in_custody_date' => '1907-09-13',
                    'convicted' => 'Yes — by U.S. colonial tribunal.',
                    'sentence' => 'Death; hanged at Bilibid Prison, Manila, September 13, 1907.',
                ]],
            ],
            [
                'name' => 'Emilio Aguinaldo',
                'first_name' => 'Emilio',
                'last_name' => 'Aguinaldo',
                'description' => 'First President of the Philippine Republic and commander-in-chief of Filipino resistance to U.S. occupation during the Philippine-American War (1899-1902). Captured by a U.S. force led by General Frederick Funston at his hideout in Palanan, Isabela on March 23, 1901 — the operation used Macabebe Scouts disguised as reinforcements. Held in Malacañang Palace until coerced into swearing an oath of allegiance to the United States on April 1, 1901. Lived under U.S. colonial surveillance for the rest of his political life.',
                'state' => 'Philippines',
                'race' => 'Asian',
                'gender' => 'Male',
                'birthdate' => '1869-03-22',
                'death_date' => '1964-02-06',
                'ideologies' => ['Philippine independence', 'Anti-colonial'],
                'affiliation' => ['Philippine Republic'],
                'era' => '1900s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Malacañang Palace (Manila)',
                    'institution_state' => 'Philippines',
                    'charges' => 'Held by U.S. forces after capture of the Philippine Republic\'s commander-in-chief.',
                    'arrest_date' => '1901-03-23',
                    'release_date' => '1901-04-01',
                    'sentence' => 'Coerced into U.S. oath of allegiance April 1, 1901; held under U.S. surveillance.',
                ]],
            ],
            [
                'name' => 'Vicente Lukbán',
                'first_name' => 'Vicente',
                'last_name' => 'Lukbán',
                'description' => 'Filipino general who led the resistance to the U.S. occupation on the island of Samar during the Philippine-American War — including the September 28, 1901 Balangiga ambush that killed 48 U.S. soldiers. The U.S. response, led by General Jacob H. Smith, was a near-genocidal "Howling Wilderness" campaign that killed thousands of Samareños. Lukbán was captured by U.S. forces on February 18, 1902; held until the end of the war. Later served in the Philippine Assembly.',
                'state' => 'Philippines',
                'race' => 'Asian',
                'gender' => 'Male',
                'birthdate' => '1860-02-11',
                'death_date' => '1916-11-16',
                'ideologies' => ['Philippine independence', 'Anti-colonial'],
                'affiliation' => ['Philippine Republic'],
                'era' => '1900s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Philippines',
                    'charges' => 'Held by U.S. forces as captured commander of Samar resistance.',
                    'arrest_date' => '1902-02-18',
                    'release_date' => '1902-07-04',
                    'sentence' => 'Held until the end of the war.',
                ]],
            ],

            // === Labor / Homestead 1892 ===
            [
                'name' => 'Hugh Dempsey',
                'first_name' => 'Hugh',
                'last_name' => 'Dempsey',
                'description' => 'Knights of Labor District Master Workman convicted in 1892 of conspiracy to poison strikebreakers at the Homestead Steel Works during the great Homestead Strike. The conviction rested almost entirely on testimony from an informer who later recanted. Sentenced to seven years in the Western Penitentiary of Pennsylvania.',
                'state' => 'Pennsylvania',
                'race' => 'White',
                'gender' => 'Male',
                'ideologies' => ['Labor', 'Knights of Labor'],
                'affiliation' => ['Knights of Labor'],
                'era' => '1890s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Western Penitentiary of Pennsylvania',
                    'institution_state' => 'Pennsylvania',
                    'charges' => 'Conspiracy to poison strikebreakers (Homestead Strike, 1892).',
                    'arrest_date' => '1892-07-21',
                    'sentenced_date' => '1892-11-15',
                    'convicted' => 'Yes — on testimony later recanted by the informer.',
                    'sentence' => '7 years at Western Penitentiary.',
                ]],
            ],
        ];
    }
}
