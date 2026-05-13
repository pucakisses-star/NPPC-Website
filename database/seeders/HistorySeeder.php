<?php

namespace Database\Seeders;

use App\Models\HistoryEra;
use App\Models\HistoryTopic;
use Illuminate\Database\Seeder;

class HistorySeeder extends Seeder {
    public function run(): void {
        $eras = [
            [
                'title'         => 'Criminalizing Dissent in a New Republic',
                'nav_label'     => '1700s',
                'slug'          => '1700s',
                'tag_line'      => 'The 18th Century',
                'heading'       => 'Criminalizing Dissent in a New Republic',
                'description'   => 'Before the United States was a decade old, the government passed laws making it a crime to criticize those in power — establishing a precedent that would define centuries of political repression.',
                'bg_class'      => 'vbg-1700',
                'caption_era'   => '1700s',
                'caption_label' => 'The birth of political repression in America',
                'sort_order'    => 1,
                'topics'        => [
                    [
                        'title'         => 'The Sedition Act',
                        'date_label'    => '1798',
                        'summary'       => 'Just seven years after the ratification of the Bill of Rights, President John Adams signed the Alien and Sedition Acts into law. The Sedition Act made it a federal crime to publish "false, scandalous, and malicious" statements against the government or its officials. At least 25 people were arrested and 10 convicted — nearly all of them newspaper editors and political opponents of the Federalist Party. Among them was Congressman Matthew Lyon of Vermont, who was sentenced to four months in prison and a $1,000 fine for criticizing President Adams in print. Lyon won re-election while still incarcerated, a powerful early symbol of democratic resistance to political repression. The Act expired in 1801 under Thomas Jefferson, who pardoned all those convicted, but its precedent — that the federal government could imprison its critics — would echo through American history.',
                        'bg_class'      => 'vbg-sedition',
                        'caption_era'   => '1798',
                        'caption_label' => 'The Sedition Act',
                        'sort_order'    => 1,
                    ],
                ],
            ],
            [
                'title'         => 'Abolition, War & the Struggle for Freedom',
                'nav_label'     => '1800s',
                'slug'          => '1800s',
                'tag_line'      => 'The 19th Century',
                'heading'       => 'Abolition, War & the Struggle for Freedom',
                'description'   => 'The fight to end slavery and the upheaval of the Civil War produced some of America\'s earliest and most consequential political prisoners.',
                'bg_class'      => 'vbg-1800',
                'caption_era'   => '1800s',
                'caption_label' => 'Abolition and the Civil War',
                'sort_order'    => 2,
                'topics'        => [
                    [
                        'title'         => 'The Anti-Rent War',
                        'date_label'    => '1839 – 1845',
                        'summary'       => 'In the Hudson Valley of New York, tenant farmers rose up against the patroon system — a Dutch feudal-style land tenure that bound tens of thousands of farmers to the Van Rensselaer, Livingston, and other manor lords with perpetual leases collected in livestock, labor, and cash. Disguised in calico-cloth masks and sheepskin face paint as "Calico Indians," the tenants resisted sheriffs serving distress warrants, intercepted rent-collection parties, and built a region-wide insurgency across Columbia, Rensselaer, Albany, and Delaware Counties. On August 7, 1845 at the Moses Earle farm in Andes, Delaware County, hundreds of Calico Indians surrounded and shot dead Undersheriff Osman N. Steele, who had ridden out to enforce a rent-distress sale. Four leaders — physician Smith A. Boughton ("Big Thunder"), and Calico Indians John Van Steenburgh, Edward O\'Connor, and the elderly tenant Moses Earle himself — were tried at Hudson and Delhi in 1845 before Justices John W. Edmonds and Amasa J. Parker. Boughton received life for robbery (taking a sheriff\'s rent paper); Van Steenburgh and O\'Connor were sentenced to be hanged for Steele\'s murder; Earle was convicted of manslaughter. All four served at Clinton State Prison at Dannemora. After Anti-Rent voting blocs delivered the 1846 gubernatorial election to John Young, the new governor pardoned every Anti-Rent prisoner in 1847. The patroon leasehold system was effectively dismantled by the new state constitution adopted that same year.',
                        'bg_class'      => 'vbg-antirent',
                        'caption_era'   => '1839',
                        'caption_label' => 'The Anti-Rent War',
                        'sort_order'    => 1,
                    ],
                    [
                        'title'         => 'The Abolition Movement',
                        'date_label'    => '1830s – 1860s',
                        'summary'       => 'As the movement to end slavery intensified, Southern states and the federal government responded by criminalizing anti-slavery activism. It became illegal in much of the South to distribute abolitionist literature, teach enslaved people to read, or publicly advocate for emancipation. Abolitionists were arrested, imprisoned, and subjected to mob violence. Elijah Lovejoy, an abolitionist newspaper editor, was murdered by a pro-slavery mob in Illinois in 1837. John Brown was executed in 1859 after leading a raid on the federal armoury at Harpers Ferry. His trial and execution made him a martyr and deepened the divide that would lead to war. Across the South, free Black people and fugitive slaves who resisted their condition were treated as criminals for asserting their basic humanity.',
                        'bg_class'      => 'vbg-abolition',
                        'caption_era'   => '1830s',
                        'caption_label' => 'The Abolition Movement',
                        'sort_order'    => 2,
                    ],
                    [
                        'title'         => 'The Civil War',
                        'date_label'    => '1861 – 1865',
                        'summary'       => 'During the Civil War, President Abraham Lincoln suspended the writ of habeas corpus, allowing the government to arrest and hold civilians without charge or trial. An estimated 13,000 to 15,000 political prisoners were detained, including journalists, elected officials, draft resisters, and Confederate sympathizers. Clement Vallandigham, a sitting Ohio congressman, was arrested and tried by a military tribunal for delivering a speech criticizing the war. The suspension of habeas corpus set a significant constitutional precedent for executive authority during wartime and demonstrated how quickly civil liberties could be curtailed when the government perceived a threat to national security.',
                        'bg_class'      => 'vbg-civilwar',
                        'caption_era'   => '1861',
                        'caption_label' => 'The Civil War',
                        'sort_order'    => 3,
                    ],
                ],
            ],
            [
                'title'         => 'Labor, Suffrage & the Red Scare',
                'nav_label'     => 'Early 1900s',
                'slug'          => 'early-1900s',
                'tag_line'      => 'Early 20th Century',
                'heading'       => 'Labor, Suffrage & the Red Scare',
                'description'   => 'The rise of labour organising, the women\'s suffrage movement, and the first wave of anti-communist repression brought mass political imprisonment to the United States.',
                'bg_class'      => 'vbg-1900',
                'caption_era'   => '1900s',
                'caption_label' => 'Labor, suffrage, and the Red Scare',
                'sort_order'    => 3,
                'topics'        => [
                    [
                        'title'         => 'The Labor Movement',
                        'date_label'    => '1900s – 1920s',
                        'summary'       => 'The early twentieth century saw brutal repression of the American labour movement. The Industrial Workers of the World (IWW) were a primary target. In 1917, federal agents raided IWW offices across the country, and over 100 leaders were convicted under the Espionage Act for opposing World War I and organising strikes. Joe Hill, a labour songwriter and organiser, was executed by firing squad in Utah in 1915 on disputed murder charges. Big Bill Haywood and other leaders faced lengthy prison sentences. State governments used criminal syndicalism laws to prosecute union organisers, and private companies employed armed strikebreakers. The Ludlow Massacre of 1914, where National Guard troops killed striking coal miners and their families in Colorado, exemplified the violent suppression of labour activism.',
                        'bg_class'      => 'vbg-labor',
                        'caption_era'   => '1900s',
                        'caption_label' => 'The Labor Movement',
                        'sort_order'    => 1,
                    ],
                    [
                        'title'         => 'Suffragism',
                        'date_label'    => '1910s',
                        'summary'       => 'Women who picketed the White House demanding the right to vote were arrested, imprisoned, and subjected to brutal treatment. In 1917, members of the National Woman\'s Party began a sustained campaign of picketing. Police arrested over 200 women on charges of "obstructing traffic." Those who refused to pay fines were sent to the Occoquan Workhouse in Virginia. On the Night of Terror in November 1917, guards beat, choked, and brutalised imprisoned suffragists. When Alice Paul and other leaders went on hunger strikes, prison officials ordered force-feeding. Public outrage over their treatment ultimately helped build support for the 19th Amendment, which granted women the right to vote in 1920.',
                        'bg_class'      => 'vbg-suffrage',
                        'caption_era'   => '1910s',
                        'caption_label' => 'Suffragism',
                        'sort_order'    => 2,
                    ],
                    [
                        'title'         => 'The First Red Scare',
                        'date_label'    => '1919 – 1920',
                        'summary'       => 'Following the Russian Revolution, Attorney General A. Mitchell Palmer launched a sweeping campaign against suspected radicals. The Palmer Raids of 1919–1920 saw federal agents arrest over 10,000 people, often without warrants, in coordinated raids targeting immigrant communities, labour organisers, and suspected communists. Hundreds of foreign-born residents were deported, including the prominent anarchists Emma Goldman and Alexander Berkman. Many detainees were held in overcrowded facilities without access to legal counsel. The raids represented the largest mass violation of civil liberties in American history up to that point.',
                        'bg_class'      => 'vbg-redscare',
                        'caption_era'   => '1919',
                        'caption_label' => 'The First Red Scare',
                        'sort_order'    => 3,
                    ],
                    [
                        'title'         => 'World War I',
                        'date_label'    => '1917 – 1918',
                        'summary'       => 'The Espionage Act of 1917 and the Sedition Act of 1918 criminalised opposition to the war effort. Over 2,000 people were prosecuted, including Socialist Party leader Eugene V. Debs, sentenced to ten years for an anti-war speech. Debs ran for president from prison in 1920, receiving nearly one million votes. Hundreds of conscientious objectors were court-martialled and imprisoned, with some subjected to physical abuse and solitary confinement. The Espionage Act remains in effect today and has been used in the twenty-first century to prosecute whistleblowers.',
                        'bg_class'      => 'vbg-ww1',
                        'caption_era'   => '1917',
                        'caption_label' => 'World War I',
                        'sort_order'    => 4,
                    ],
                ],
            ],
            [
                'title'         => 'McCarthyism, Civil Rights & Vietnam',
                'nav_label'     => 'Mid 1900s',
                'slug'          => 'mid-1900s',
                'tag_line'      => 'Mid 20th Century',
                'heading'       => 'McCarthyism, Civil Rights & Vietnam',
                'description'   => 'The Cold War, the struggle for racial justice, and opposition to the Vietnam War produced waves of political imprisonment that reshaped American society.',
                'bg_class'      => 'vbg-mid1900',
                'caption_era'   => '1940–70s',
                'caption_label' => 'McCarthyism, civil rights, and Vietnam',
                'sort_order'    => 4,
                'topics'        => [
                    [
                        'title'         => 'World War II',
                        'date_label'    => '1939 – 1945',
                        'summary'       => 'The most sweeping act of political imprisonment during World War II was the forced incarceration of approximately 120,000 Japanese Americans, two-thirds of whom were U.S. citizens. Following Executive Order 9066 in February 1942, Japanese Americans on the West Coast were forcibly removed from their homes and confined in internment camps. The government presented no evidence of disloyalty. Resisters like Fred Korematsu, Minoru Yasui, and Gordon Hirabayashi were arrested and convicted. It took decades for the government to acknowledge the injustice; reparations were not authorised until 1988.',
                        'bg_class'      => 'vbg-ww2',
                        'caption_era'   => '1942',
                        'caption_label' => 'World War II',
                        'sort_order'    => 1,
                    ],
                    [
                        'title'         => 'McCarthyism',
                        'date_label'    => '1947 – 1957',
                        'summary'       => 'Senator Joseph McCarthy\'s anti-communist crusade created a climate of repression that pervaded American life. The House Un-American Activities Committee investigated suspected communists in government, academia, and entertainment. The Hollywood Ten were imprisoned for refusing to testify. Julius and Ethel Rosenberg were executed in 1953 on espionage charges in a case that remains deeply controversial. Thousands lost their jobs through loyalty oaths, blacklists, and guilt-by-association investigations. The Smith Act was used to prosecute Communist Party leaders. McCarthyism demonstrated how fear could be weaponised to suppress political dissent across an entire society.',
                        'bg_class'      => 'vbg-mccarthy',
                        'caption_era'   => '1950s',
                        'caption_label' => 'McCarthyism',
                        'sort_order'    => 2,
                    ],
                    [
                        'title'         => 'The Civil Rights Movement',
                        'date_label'    => '1955 – 1968',
                        'summary'       => 'The movement for racial justice produced political prisoners on a massive scale. Across the South, tens of thousands of activists were arrested for sit-ins, Freedom Rides, marches, and voter registration drives. Dr. Martin Luther King Jr. was arrested 29 times. His "Letter from Birmingham Jail" became one of the defining documents of the movement. Freedom Riders were imprisoned in Mississippi\'s Parchman Farm penitentiary. Fannie Lou Hamer was arrested and savagely beaten for registering Black voters. In Birmingham, thousands of schoolchildren were arrested during the 1963 Children\'s Crusade. The systematic use of arrest and imprisonment exposed the deep entanglement of the criminal justice system with racial oppression.',
                        'bg_class'      => 'vbg-civilrights',
                        'caption_era'   => '1960s',
                        'caption_label' => 'The Civil Rights Movement',
                        'sort_order'    => 3,
                    ],
                    [
                        'title'         => 'The Vietnam War',
                        'date_label'    => '1964 – 1975',
                        'summary'       => 'Opposition to the Vietnam War led to the imprisonment of thousands. Over 200,000 men were formally accused of violating draft laws, and approximately 8,750 were convicted. The "Chicago Seven" were prosecuted for conspiracy and inciting a riot at the 1968 Democratic Convention. Muhammad Ali was stripped of his boxing title and convicted of draft evasion in 1967; his conviction was unanimously overturned by the Supreme Court. Daniel and Philip Berrigan, Catholic priests who destroyed draft files, were imprisoned and became symbols of the anti-war movement.',
                        'bg_class'      => 'vbg-vietnam',
                        'caption_era'   => '1960s',
                        'caption_label' => 'The Vietnam War',
                        'sort_order'    => 4,
                    ],
                ],
            ],
            [
                'title'         => 'COINTELPRO & Independence Movements',
                'nav_label'     => 'Late 1900s',
                'slug'          => 'late-1900s',
                'tag_line'      => 'Late 20th Century',
                'heading'       => 'COINTELPRO & Independence Movements',
                'description'   => 'The FBI\'s covert war on domestic political movements and the ongoing struggle for Puerto Rican independence produced political prisoners who remain incarcerated — or only recently freed — to this day.',
                'bg_class'      => 'vbg-late1900',
                'caption_era'   => '1970–90s',
                'caption_label' => 'COINTELPRO and independence movements',
                'sort_order'    => 5,
                'topics'        => [
                    [
                        'title'         => 'COINTELPRO',
                        'date_label'    => '1956 – 1971',
                        'summary'       => "COINTELPRO — the FBI's Counter Intelligence Program — was a covert and often illegal campaign to surveil, infiltrate, discredit, and disrupt domestic political organisations. Originally targeting the Communist Party USA, the programme expanded to include the Civil Rights Movement, the Black Panther Party, the American Indian Movement, and other groups. Hoover authorised tactics including wiretapping, blackmail, and forged correspondence. Fred Hampton was assassinated in a 1969 raid carried out by Chicago police in coordination with the FBI. Geronimo Pratt was framed for murder and spent 27 years in prison before his conviction was overturned. Leonard Peltier, convicted in connection with the deaths of two FBI agents at Pine Ridge in 1975, spent nearly 50 years in prison before his release in 2025. COINTELPRO's full extent was only revealed after activists broke into an FBI office in 1971 and leaked the files.",
                        'bg_class'      => 'vbg-cointelpro',
                        'caption_era'   => '1956',
                        'caption_label' => 'COINTELPRO',
                        'sort_order'    => 1,
                    ],
                    [
                        'title'         => 'Puerto Rican Independence Movement',
                        'date_label'    => '1950s – 1990s',
                        'summary'       => "The struggle for Puerto Rican independence produced some of the longest-held political prisoners in the Western Hemisphere. In 1950, members of the Puerto Rican Nationalist Party staged an armed insurrection and an attack on Blair House. Lolita Lebrón led a 1954 attack on the U.S. House of Representatives as an act of protest against colonial rule; she served 25 years. Oscar López Rivera, a Vietnam veteran and advocate for independence, was sentenced to 55 years on charges of seditious conspiracy. He spent 36 years in prison — including 12 in solitary — before President Obama commuted his sentence in 2017. His case drew international attention to the question of political prisoners in the United States.",
                        'bg_class'      => 'vbg-puertorico',
                        'caption_era'   => '1950s',
                        'caption_label' => 'Puerto Rican Independence Movement',
                        'sort_order'    => 2,
                    ],
                ],
            ],
            [
                'title'         => 'Terror, Technology & New Movements',
                'nav_label'     => '2000s',
                'slug'          => '2000s',
                'tag_line'      => 'The 21st Century',
                'heading'       => 'Terror, Technology & New Movements',
                'description'   => 'The post-9/11 era brought sweeping new powers of surveillance and prosecution, while movements for environmental justice, digital freedom, economic equality, and Black lives continued the long American tradition of dissent — and its consequences.',
                'bg_class'      => 'vbg-2000',
                'caption_era'   => '2000s',
                'caption_label' => 'Terror, technology, and new movements',
                'sort_order'    => 6,
                'topics'        => [
                    [
                        'title'         => 'The War on Terror',
                        'date_label'    => '2001 – Present',
                        'summary'       => 'Following September 11, 2001, the USA PATRIOT Act granted sweeping authority to monitor communications, freeze assets, and detain non-citizens. Guantánamo Bay held 780 detainees — many without charge — and many were subjected to what international bodies characterised as torture. Material support statutes were used to prosecute humanitarian workers and charity organisers. Muslim communities faced widespread surveillance. The legal architecture created during the War on Terror fundamentally altered the relationship between civil liberties and national security.',
                        'bg_class'      => 'vbg-terror',
                        'caption_era'   => '2001',
                        'caption_label' => 'The War on Terror',
                        'sort_order'    => 1,
                    ],
                    [
                        'title'         => 'The Green Scare',
                        'date_label'    => '2004 – 2011',
                        'summary'       => 'In the mid-2000s, the FBI declared environmental and animal rights extremism the leading domestic terrorism threat. "Operation Backfire" led to dozens of arrests for property destruction that harmed no person. The Animal Enterprise Terrorism Act of 2006 created enhanced penalties even for peaceful protesters. Activists like Daniel McGowan were held in secretive Communications Management Units that severely limited contact with the outside world. Critics coined the term "Green Scare" as a reference to earlier waves of political repression.',
                        'bg_class'      => 'vbg-green',
                        'caption_era'   => '2004',
                        'caption_label' => 'The Green Scare',
                        'sort_order'    => 2,
                    ],
                    [
                        'title'         => 'Anonymous',
                        'date_label'    => '2008 – 2013',
                        'summary'       => 'The hacktivist collective Anonymous emerged as a new form of political dissent. Members carried out attacks against government agencies and corporations they deemed oppressive. The government responded with aggressive prosecutions under the Computer Fraud and Abuse Act. Jeremy Hammond received 10 years for hacking Stratfor. Chelsea Manning was sentenced to 35 years for disclosing classified documents to WikiLeaks — one of the longest sentences ever for a leak to the press. Obama commuted Manning\'s sentence in 2017. The cases raised questions about digital dissent and the treatment of whistleblowers as enemies of the state.',
                        'bg_class'      => 'vbg-anon',
                        'caption_era'   => '2008',
                        'caption_label' => 'Anonymous',
                        'sort_order'    => 3,
                    ],
                    [
                        'title'         => 'Occupy Wall Street',
                        'date_label'    => '2011 – 2012',
                        'summary'       => 'The Occupy movement challenged economic inequality and corporate influence on governance. Over 7,700 protesters were arrested nationwide. Police used mass arrest tactics, kettling, pepper spray, and rubber bullets. More than 700 were arrested in a single incident on the Brooklyn Bridge. Journalists covering the protests were also targeted. Internal documents later revealed coordinated surveillance by the FBI, the Department of Homeland Security, and local law enforcement through Joint Terrorism Task Forces.',
                        'bg_class'      => 'vbg-occupy',
                        'caption_era'   => '2011',
                        'caption_label' => 'Occupy Wall Street',
                        'sort_order'    => 4,
                    ],
                    [
                        'title'         => 'Black Lives Matter',
                        'date_label'    => '2013 – Present',
                        'summary'       => 'The movement for Black lives sparked the largest wave of protest in American history. In the summer of 2020, an estimated 15 to 26 million people participated in demonstrations. More than 14,000 were arrested. Federal authorities used unmarked vehicles, agents in camouflage, tear gas, and rubber bullets. Protest leaders faced surveillance, infiltration, and targeted prosecution. Several states introduced anti-protest laws increasing penalties for demonstrations. The movement drew direct comparisons to the civil rights era, and the government\'s response underscored that the tension between political dissent and state power remains one of the most unresolved questions in American democracy.',
                        'bg_class'      => 'vbg-blm',
                        'caption_era'   => '2013',
                        'caption_label' => 'Black Lives Matter',
                        'sort_order'    => 5,
                    ],
                ],
            ],
        ];

        foreach ($eras as $eraData) {
            $topics = $eraData['topics'];
            unset($eraData['topics']);

            $era = HistoryEra::create($eraData);

            foreach ($topics as $topicData) {
                $topicData['history_era_id'] = $era->id;
                HistoryTopic::create($topicData);
            }
        }
    }
}
