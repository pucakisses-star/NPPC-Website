<?php

namespace App\Console\Commands;

use App\Models\Topic;
use Illuminate\Console\Command;

/**
 * Fills in the Topics explorer's empty sections: every Movements or Eras
 * sub-topic that currently has no children of its own (Black Lives Matter and
 * Environmental Justice under Movements, and all twenty Eras) gets 4-5 nested
 * sub-topic pages covering the key prosecutions, trials, and prisoner
 * histories of that movement or era — matching the third-level nav the
 * Organizations section and the other Movements already have.
 *
 * Idempotent: parents are looked up by slug (falling back to title) and
 * skipped with a warning when absent; each child is firstOrCreate'd by
 * (parent_id, title), so re-running never duplicates. Existing children keep
 * their admin-edited body; published and sort_order are (re)applied on every
 * run. Explicit slugs are used unless already taken elsewhere, in which case
 * the HasSlug trait generates a unique one.
 */
final class AddMissingSubtopics extends Command
{
    protected $signature = 'topics:add-missing-subtopics';

    protected $description = 'Add nested sub-topics under Movements/Eras topics that have none';

    public function handle(): int
    {
        $added = 0;
        $updated = 0;
        $skippedParents = 0;

        foreach ($this->data() as $parentSlug => $group) {
            $parent = Topic::where('slug', $parentSlug)->first()
                ?? Topic::where('title', $group['title'])->whereNotNull('parent_id')->first();

            if (! $parent) {
                $this->warn("Parent topic not found, skipping: {$parentSlug} ({$group['title']})");
                $skippedParents++;

                continue;
            }

            foreach (array_values($group['children']) as $i => $child) {
                $sortOrder = ($i + 1) * 10;

                // Only pass the preferred slug when it is free — otherwise let
                // HasSlug derive a unique one from the title on create.
                $slugTaken = Topic::where('slug', $child['slug'])->exists();

                $topic = Topic::firstOrCreate(
                    ['parent_id' => $parent->id, 'title' => $child['title']],
                    array_filter([
                        'slug' => $slugTaken ? null : $child['slug'],
                        'body' => $child['body'],
                        'published' => true,
                        'sort_order' => $sortOrder,
                    ], fn ($v) => $v !== null),
                );

                if ($topic->wasRecentlyCreated) {
                    $added++;
                    $this->info("  added: {$parent->title} → {$child['title']}");
                } else {
                    // Re-run: keep any admin-edited body, but make sure the
                    // page is published and ordered. Backfill an empty body.
                    $topic->published = true;
                    $topic->sort_order = $sortOrder;
                    if (blank($topic->body)) {
                        $topic->body = $child['body'];
                    }
                    if ($topic->isDirty()) {
                        $topic->save();
                        $updated++;
                        $this->line("  updated: {$parent->title} → {$child['title']}");
                    }
                }
            }

            $this->info("{$parent->title}: ".count($group['children']).' sub-topics ensured.');
        }

        $this->info("\nDone. Added: {$added}, updated: {$updated}, parents skipped: {$skippedParents}.");

        return self::SUCCESS;
    }

    /**
     * Parent slug => ['title' => fallback lookup title, 'children' => [...]].
     * Bodies are trusted rich text rendered with {!! !!} on the topic page.
     *
     * @return array<string, array{title: string, children: list<array{slug: string, title: string, body: string}>}>
     */
    private function data(): array
    {
        return [
            'black-lives-matter' => [
                'title' => 'Black Lives Matter',
                'children' => [
                    [
                        'slug' => 'blm-decade-of-prosecutions',
                        'title' => 'From Ferguson to Minneapolis: A Decade of Protest Prosecutions (2014–2023)',
                        'body' => <<<'HTML'
<p>Black Lives Matter emerged in 2013 as a hashtag and became, after the killing of Michael Brown in Ferguson in August 2014, the largest protest movement in American history. Each of its major waves — Ferguson in 2014, Baltimore in 2015, the summer of 2016, and the George Floyd uprising of 2020, when as many as 26 million people took part in demonstrations — was met with mass arrest, curfew prosecutions, and a steadily escalating legal response that moved from municipal charges toward state and federal felonies.</p><p>The pattern that defined the decade was selective severity: the overwhelming majority of arrestees saw their charges dropped, while a small number of defendants — often young, Black, and already known to police — received multi-year sentences meant to stand in for the crowd. Joshua Williams in Missouri, Jasmine Richards in Pasadena, and the federal arson defendants of 2020 became the movement's political prisoners, held up by supporters as proof that the state punished the uprising through exemplary cases rather than provable individual guilt.</p>
HTML,
                    ],
                    [
                        'slug' => 'blm-black-identity-extremist-program',
                        'title' => 'The "Black Identity Extremist" Program (2017–2019)',
                        'body' => <<<'HTML'
<p>In August 2017 the FBI's counterterrorism division circulated an intelligence assessment inventing a new threat category: "Black Identity Extremists," defined as people likely to commit violence against police in response to perceived racism. Civil-liberties groups and members of Congress immediately recognized the echo of COINTELPRO's "Black Nationalist Hate Groups" program — a designation built around ideology and race rather than conduct, used to justify surveillance of activists who had committed no crime.</p><p>Its best-known casualty was Rakem Balogun of Dallas, arrested in a pre-dawn raid in December 2017 after FBI agents monitored his Facebook posts criticizing police. Held five months without bail on a firearms count that collapsed in court, Balogun was released in May 2018 with no conviction — widely described as the first person jailed under the BIE designation. The FBI formally retired the term in 2019 after congressional pressure, folding it into a broader "racially motivated violent extremism" category whose criteria remain classified.</p>
HTML,
                    ],
                    [
                        'slug' => 'blm-mckesson-v-doe',
                        'title' => 'Mckesson v. Doe & the Legal War on Organizers (2016–2024)',
                        'body' => <<<'HTML'
<p>After a police officer was injured by an unknown rock-thrower at a July 2016 protest in Baton Rouge over the killing of Alton Sterling, the officer sued not the thrower but DeRay Mckesson, the movement figure who had helped lead the march. The theory — that an organizer is personally liable for the independent criminal act of anyone present — struck at the foundation of NAACP v. Claiborne Hardware, the 1982 Supreme Court decision that had protected civil-rights boycott leaders from exactly this tactic.</p><p>The Fifth Circuit allowed the suit to proceed on a "negligent protest" theory, and in 2024 the Supreme Court declined to intervene, leaving organizers in Louisiana, Texas, and Mississippi exposed to ruinous personal liability for leading demonstrations. Paired with the felony charges brought against protest leaders in the same years, the case marked the revival of a pre-civil-rights-era strategy: making the act of organizing itself too legally dangerous to attempt.</p>
HTML,
                    ],
                    [
                        'slug' => 'blm-surveillance-apparatus',
                        'title' => 'The Surveillance of the Movement (2015–)',
                        'body' => <<<'HTML'
<p>Documents pried loose by FOIA litigation showed that from Ferguson onward the movement was tracked as a security threat rather than protected speech. DHS mapped Black Lives Matter gatherings — including vigils and a funk-music festival — in real time; the FBI opened assessments on organizers; and police departments purchased social-media monitoring tools such as Geofeedia, Dataminr, and ShadowDragon that flagged hashtags like #BlackLivesMatter for intelligence units. Cell-site simulators and aerial surveillance were deployed over Baltimore and Minneapolis during the uprisings.</p><p>The surveillance mattered because it fed prosecutions: social-media posts became the basis for federal charges in 2020, and monitoring dossiers surfaced in the bail arguments used to hold protesters pretrial. Activists who had never been charged with a crime discovered they carried threat-assessment files — the infrastructure of suspicion that produced the "Black Identity Extremist" designation and, veterans of the era argued, reproduced COINTELPRO with better software.</p>
HTML,
                    ],
                ],
            ],

            'environmental-justice' => [
                'title' => 'Environmental Justice',
                'children' => [
                    [
                        'slug' => 'env-justice-reznicek-terrorism-enhancement',
                        'title' => 'Jessica Reznicek & the Terrorism Enhancement (2016–2021)',
                        'body' => <<<'HTML'
<p>Jessica Reznicek, a Catholic Worker from Des Moines, publicly admitted in 2017 that she and Ruby Montoya had burned construction equipment and pierced empty sections of the Dakota Access Pipeline with welding torches — property sabotage, carried out, they said, to protect water, that injured no one. Reznicek pleaded guilty to a single count of conspiracy to damage an energy facility. At sentencing in June 2021, the court applied a federal terrorism enhancement, treating the pipeline as government property and her acts as calculated to influence government conduct, and imposed eight years in prison plus millions in restitution.</p><p>The Eighth Circuit affirmed in 2022, and Montoya was sentenced to six years. The case became the environmental-justice movement's clearest evidence that "terrorism" in federal law had become a label for politically motivated property damage: advocates noted that the enhancement roughly doubled Reznicek's sentence for conduct that, absent politics, would have been ordinary vandalism. "Free Jessica Reznicek" campaigns made her one of the most widely recognized U.S. political prisoners of the decade.</p>
HTML,
                    ],
                    [
                        'slug' => 'env-justice-critical-infrastructure-laws',
                        'title' => 'The Critical-Infrastructure Laws (2017–)',
                        'body' => <<<'HTML'
<p>Within months of the Standing Rock camps being cleared, Oklahoma enacted the first "critical infrastructure protection" law — felony penalties for trespass near pipelines, and organizational liability for groups that "conspire" with trespassers. The template, promoted by the American Legislative Exchange Council with energy-industry backing, spread to more than twenty states. Louisiana's 2018 version was applied almost immediately to kayakers and landowner-invited protesters at the Bayou Bridge Pipeline, who found themselves facing five-year felonies for standing in a swamp.</p><p>The laws' purpose, critics argued, was not security but pre-emption: converting the misdemeanor civil disobedience that had powered Standing Rock into prison-time felonies before the next fight began, and threatening the nonprofits that might support it. Constitutional challenges, including White Hat v. Landry in Louisiana, chipped at the vaguest provisions, but the statutes largely survived — a standing warning that in much of the country, the criminal code around fossil-fuel infrastructure had been rewritten specifically for protesters.</p>
HTML,
                    ],
                    [
                        'slug' => 'env-justice-line-3-arrests',
                        'title' => 'The Line 3 Arrests (2021)',
                        'body' => <<<'HTML'
<p>The fight over Enbridge's Line 3 tar-sands pipeline across northern Minnesota produced roughly a thousand arrests in the summer and fall of 2021 — Anishinaabe water protectors defending wild-rice watersheds and treaty territory alongside thousands of allies. Prosecutors in rural counties charged lockdown protesters with felony theft and "obstruction of legal process," and some faced the state's new gross-misdemeanor trespass-on-critical-infrastructure offense.</p><p>What set Line 3 apart was the financing: a permit condition required Enbridge to fund a state-managed escrow account that reimbursed local police for pipeline-related enforcement, ultimately paying sheriffs' offices millions of dollars for surveillance, riot gear, and overtime spent policing the company's opponents. Civil-liberties groups called it a privately funded police force; most of the serious charges were eventually reduced or dismissed, but the arrests achieved their operational purpose — the pipeline entered service in October 2021.</p>
HTML,
                    ],
                    [
                        'slug' => 'env-justice-appalachian-blockades',
                        'title' => 'Appalachia: The Mountain Valley Pipeline Blockades (2018–2024)',
                        'body' => <<<'HTML'
<p>Resistance to the Mountain Valley Pipeline through West Virginia and Virginia took the form of the longest blockades in American environmental history: tree-sits and monopod perches on steep Appalachian ridgelines, including the Yellow Finch tree-sits near Elliston, Virginia, which held the easement for 932 days until extracted in March 2021. The final two sitters were jailed on contempt, and courts imposed escalating daily fines designed to make aerial resistance financially impossible.</p><p>Judges hearing the cases increasingly treated the protests as attacks on a federally sanctioned project rather than trespass against a private company — a shift completed in 2023 when Congress ordered the pipeline's completion by statute. Dozens of blockaders, from college students to a 64-year-old grandmother who locked herself to construction equipment, served jail time or probation. The campaign did not stop the pipeline, which entered service in 2024, but it delayed it for years and trained a generation of Appalachian land defenders in the legal costs of direct action.</p>
HTML,
                    ],
                ],
            ],

            'civil-rights-black-power' => [
                'title' => 'Civil Rights & Black Power',
                'children' => [
                    [
                        'slug' => 'crbp-montgomery-to-birmingham',
                        'title' => 'Montgomery to Birmingham: Jail as Witness (1955–1963)',
                        'body' => <<<'HTML'
<p>The Southern freedom movement made imprisonment itself a political instrument. From Rosa Parks's arrest in December 1955 through the Montgomery bus boycott — whose leaders, including Martin Luther King Jr., were indicted en masse under an old anti-boycott statute — activists accepted jail as the price and the proof of their witness. King would be arrested some thirty times; his April 1963 confinement in Birmingham, for marching in defiance of a state-court injunction, produced the "Letter from Birmingham Jail," the era's most enduring defense of civil disobedience against unjust law.</p><p>Birmingham also showed the system's capacity for mass incarceration of the movement: during the May 1963 Children's Crusade, thousands of schoolchildren were arrested and held in improvised pens, filling the jails exactly as the campaign intended. The convictions of that spring followed the movement for years — the Supreme Court upheld King's contempt sentence in Walker v. City of Birmingham (1967), sending him back to jail in Birmingham in the last year of his life.</p>
HTML,
                    ],
                    [
                        'slug' => 'crbp-freedom-riders-parchman',
                        'title' => 'The Freedom Riders at Parchman (1961)',
                        'body' => <<<'HTML'
<p>When the Freedom Rides reached Jackson, Mississippi in the summer of 1961, the state adopted a strategy of mass imprisonment: more than 300 riders were arrested for "breach of the peace" as they entered white waiting rooms, and rather than pay fines, most chose jail. Mississippi transferred them to the maximum-security unit of Parchman Farm, the state penitentiary — a working plantation notorious for brutality — where they were held in death-row cells, stripped of mattresses for singing freedom songs, and subjected to wrist-breaker cuffs and cattle prods.</p><p>Parchman was meant to break the Rides; it did the opposite, becoming the movement's shared crucible. Riders including John Lewis, Stokely Carmichael, and James Farmer later described the prison as the place where a generation of organizers was forged. The convictions were eventually reversed, and the Interstate Commerce Commission's September 1961 desegregation order vindicated the campaign — but the riders had already demonstrated that the movement could absorb any number of political prisoners the South cared to make.</p>
HTML,
                    ],
                    [
                        'slug' => 'crbp-jail-no-bail',
                        'title' => '"Jail, No Bail": Rock Hill, Albany & Selma (1961–1965)',
                        'body' => <<<'HTML'
<p>In February 1961, nine students in Rock Hill, South Carolina refused to pay fines for a lunch-counter sit-in and served thirty days of hard labor instead — the "jail, no bail" tactic that transformed arrest from a deterrent into a weapon. Filling the jails shifted the financial burden of segregation onto the jailers and dramatized the moral stakes; SNCC adopted the tactic movement-wide, and it defined the mass campaigns that followed.</p><p>In Albany, Georgia (1961–62), more than a thousand demonstrators were jailed, dispersed by Sheriff Laurie Pritchett across surrounding county jails to blunt the tactic's force. In Selma, Alabama, the 1963–65 voting-rights campaign produced thousands more arrests — including hundreds of schoolteachers and children marched to holding camps — before the violence of Bloody Sunday forced the Voting Rights Act through Congress. The movement's ledger from these campaigns runs to tens of thousands of political arrests, most of them never individually recorded.</p>
HTML,
                    ],
                    [
                        'slug' => 'crbp-wilmington-ten-charlotte-three',
                        'title' => 'The Wilmington Ten & the Charlotte Three (1971–1980)',
                        'body' => <<<'HTML'
<p>The Wilmington Ten — nine young Black men and a white anti-poverty worker, led by 24-year-old organizer Ben Chavis — were convicted in 1972 of arson and conspiracy after racial violence surrounding school desegregation in Wilmington, North Carolina, and sentenced to a combined 282 years. The case rested on three witnesses who all later recanted, saying prosecutors had bribed and pressured them; Amnesty International declared the Ten political prisoners, among the first U.S. cases it ever adopted.</p><p>A federal appeals court overturned the convictions in 1980, citing prosecutorial misconduct, and in December 2012 North Carolina's governor issued pardons of innocence after the prosecutor's notes surfaced showing he had screened jurors for Klan sympathy. The companion Charlotte Three case — Black activists convicted of burning a riding stable on similar paid-informant testimony — followed the same arc of conviction, international protest, and eventual clemency, together documenting how North Carolina used criminal courts against the Black freedom movement's local leadership.</p>
HTML,
                    ],
                    [
                        'slug' => 'crbp-black-power-prosecutions',
                        'title' => 'H. Rap Brown & the Black Power Prosecutions (1966–1972)',
                        'body' => <<<'HTML'
<p>As the movement's center of gravity shifted from civil rights to Black Power, prosecution shifted with it — from local breach-of-peace charges to state and federal cases aimed at leadership. SNCC chairman H. Rap Brown was charged with inciting riot and arson after a 1967 speech in Cambridge, Maryland; Congress responded to him personally with the 1968 federal anti-riot act, known in Washington as the "Rap Brown law," which criminalized crossing state lines with intent to incite a riot and would later be used against the Chicago Eight.</p><p>The pattern repeated across the country: RAM organizers in Philadelphia and New York arrested on conspiracy charges built by informants in 1967, SNCC workers jailed for draft resistance and "criminal anarchy," and Black Power spokesmen held on charge after charge until organizations exhausted themselves posting bail. By 1972, when Brown began a five-year robbery sentence after being wounded and captured in New York, most of the generation's leadership had passed through prison — the bridge between the civil-rights jailings and the COINTELPRO-era frame-ups that followed.</p>
HTML,
                    ],
                ],
            ],

            'abolitionism-the-slave-power-1850-1861' => [
                'title' => 'Abolitionism & the Slave Power (1850–1861)',
                'children' => [
                    [
                        'slug' => 'abolition-era-rescue-trials',
                        'title' => 'The Fugitive Slave Rescue Trials (1851–1859)',
                        'body' => <<<'HTML'
<p>The Fugitive Slave Act of 1850 made it a federal crime to aid an escaped slave or obstruct a rendition, and Northern juries were soon asked to imprison their neighbors for acts of open conscience. In Boston, crowds led by Black abolitionists freed Shadrach Minkins from a federal courtroom in February 1851; in Syracuse that October, a biracial crowd broke William "Jerry" Henry out of custody in the celebrated Jerry Rescue. Federal prosecutors indicted the rescuers in both cities and secured almost nothing — juries acquitted or hung, and only one Syracuse defendant, Enoch Reed, a Black laborer, was convicted, dying while his appeal was pending.</p><p>The climactic case came from Ohio: after the 1858 Oberlin–Wellington Rescue, thirty-seven men were indicted, and rescuers Simeon Bushnell and Charles Langston chose jail over recognizing the law's legitimacy. Langston's speech at sentencing became an abolitionist classic, and the spectacle of respected citizens imprisoned as political offenders did as much as any pamphlet to convince the North that the Slave Power had captured the federal courts.</p>
HTML,
                    ],
                    [
                        'slug' => 'abolition-era-christiana-treason-trial',
                        'title' => 'The Christiana Treason Trial (1851)',
                        'body' => <<<'HTML'
<p>In September 1851, a Maryland slaveholder named Edward Gorsuch was killed when he attempted to seize four escaped men at the Christiana, Pennsylvania farmhouse of William Parker, a Black underground-railroad leader whose armed self-defense network had resolved to resist renditions by force. The Fillmore administration, determined to make an example, charged thirty-eight men — most of them Black farmers, plus white miller Castner Hanway, who had refused a federal marshal's order to assist — with treason against the United States: the largest mass treason indictment in American history.</p><p>The theory that refusing to help catch a fugitive amounted to levying war collapsed at the first trial. Defended by Thaddeus Stevens, Hanway was acquitted in fifteen minutes in December 1851, and the government abandoned the remaining cases. The defendants had spent months in Moyamensing Prison, but the verdict established that Northern juries would not treat resistance to the Fugitive Slave Act as treason — while Parker and the men he protected escaped to Canada, beyond the law's reach.</p>
HTML,
                    ],
                    [
                        'slug' => 'abolition-era-imprisoned-conductors',
                        'title' => 'The Imprisoned Conductors: Torrey, Fairbank & Walker (1844–1864)',
                        'body' => <<<'HTML'
<p>Before the war, the clearest American political prisoners were the underground railroad's captured conductors, punished under slave-state law for helping human beings escape. Charles Torrey, a Massachusetts minister credited with aiding some 400 escapes, was convicted in Maryland in 1844 and died of tuberculosis in the state penitentiary in 1846, refused a pardon; his funeral made him abolition's martyr. Jonathan Walker, a Massachusetts sea captain caught carrying seven fugitives by boat from Florida, was pilloried and branded on the hand with "S.S." — slave stealer — a mark John Greenleaf Whittier's verse turned into a badge of honor.</p><p>Calvin Fairbank suffered longest: convicted in Kentucky in 1845 for helping Lewis Hayden's family escape, pardoned in 1849, then seized again in 1851, he served a combined seventeen years in the Kentucky penitentiary, where by his own count he was flogged more than a thousand times before his 1864 release. Their sentences established a template that would recur throughout American history — the state punishing as theft or treason what the movement understood as rescue.</p>
HTML,
                    ],
                    [
                        'slug' => 'abolition-era-harpers-ferry-prisoners',
                        'title' => 'John Brown & the Harpers Ferry Prisoners (1859–1860)',
                        'body' => <<<'HTML'
<p>After the October 1859 raid on the federal arsenal at Harpers Ferry, Virginia tried John Brown for treason against a state of which he was not a citizen, murder, and inciting slave insurrection — a proceeding conducted in six days while Brown lay wounded on a cot in the courtroom. His conduct in prison and his speech to the court transformed a failed raid into the era's defining political trial; he was hanged at Charlestown on December 2, 1859, with church bells tolling across the North.</p><p>Six of his men followed him to the gallows: John Copeland and Shields Green, the raid's Black prisoners, and Edwin Coppoc and John Cook in December 1859, then Aaron Stevens and Albert Hazlett in March 1860. Copeland's letters from jail — "I am dying for freedom, I could not die for a better cause" — circulated as widely as Brown's own. The executions, mourned in the North and celebrated in the South, hardened both sections; within eighteen months the questions the Charlestown jail had contained were being settled by armies.</p>
HTML,
                    ],
                ],
            ],

            'the-haymarket-affair-the-anti-anarchist-era-1886-1903' => [
                'title' => 'The Haymarket Affair & the Anti-Anarchist Era (1886–1903)',
                'children' => [
                    [
                        'slug' => 'haymarket-era-trial-of-the-eight',
                        'title' => 'The Trial of the Chicago Eight (1886–1887)',
                        'body' => <<<'HTML'
<p>When a bomb killed seven policemen at a Haymarket Square labor rally on May 4, 1886, Chicago answered with the classic political trial: eight anarchists — August Spies, Albert Parsons, Adolph Fischer, George Engel, Louis Lingg, Samuel Fielden, Michael Schwab, and Oscar Neebe — were convicted of murder although the bomb-thrower was never identified and most were not present when it exploded. The prosecution's theory was that their speeches and newspapers had inspired the unknown killer; the jury was openly biased, and Judge Joseph Gary's rulings made the trial a referendum on anarchism itself.</p><p>Seven were sentenced to death. On November 11, 1887 — "Black Friday" to the labor movement — Spies, Parsons, Fischer, and Engel were hanged; Lingg had died in his cell the day before. Spies's last words from the scaffold, "there will come a time when our silence will be more powerful than the voices you strangle today," became the international labor movement's epitaph for the case, and May Day itself was established in the executed men's memory.</p>
HTML,
                    ],
                    [
                        'slug' => 'haymarket-era-altgeld-pardons',
                        'title' => 'The Altgeld Pardons (1893)',
                        'body' => <<<'HTML'
<p>Three of the Haymarket defendants — Samuel Fielden, Michael Schwab, and Oscar Neebe — escaped the gallows and remained in Joliet penitentiary while a clemency movement gathered signatures from lawyers, editors, and trade unionists across the country. In June 1893, Illinois's new governor, John Peter Altgeld, did not merely commute their sentences: he issued an absolute pardon accompanied by an 18,000-word message dissecting the case, finding the jury packed, the judge prejudiced, and the evidence insufficient — a state governor declaring, in effect, that Illinois had hanged four men for their opinions.</p><p>The pardon destroyed Altgeld politically; the press rechristened him "John Pardon Altgeld" and an anarchist sympathizer, and he never held office again after 1896. But his message remains the most thorough official acknowledgment of a political frame-up in nineteenth-century America, and it made the Haymarket case a permanent reference point — cited in every subsequent American amnesty campaign, from Mooney and Billings to the Wilmington Ten.</p>
HTML,
                    ],
                    [
                        'slug' => 'haymarket-era-berkman-homestead',
                        'title' => 'Alexander Berkman & the Homestead Attentat (1892–1906)',
                        'body' => <<<'HTML'
<p>During the 1892 Homestead steel strike, after Pinkerton guards and striking workers fought a pitched battle that left ten dead, the young anarchist Alexander Berkman walked into the Pittsburgh office of Carnegie Steel chairman Henry Clay Frick and shot and stabbed him in an attempted <em>attentat</em> — propaganda of the deed meant to avenge the strikers. Frick survived; Berkman, who refused counsel and defended the act politically, was sentenced to twenty-two years, a term stacked from multiple charges that Pennsylvania law would ordinarily have merged.</p><p>He served fourteen years, much of it in solitary confinement in Western Penitentiary, and emerged in 1906 to write <em>Prison Memoirs of an Anarchist</em> — one of the enduring American accounts of long imprisonment, and a founding text of the prisoner-support tradition. Berkman's later career traced the era's arc: editor of Emma Goldman's <em>Mother Earth</em>, imprisoned again in 1917 for opposing the draft, and deported to Russia on the Buford in 1919.</p>
HTML,
                    ],
                    [
                        'slug' => 'haymarket-era-goldman-blackwells-island',
                        'title' => "Emma Goldman's Blackwell's Island Sentence (1893–1894)",
                        'body' => <<<'HTML'
<p>In the depression summer of 1893, Emma Goldman told a Union Square rally of the unemployed that a starving man had a right to demand bread — and if denied, to take it. New York charged her with "inciting to riot" for a riot that never occurred; a detective's disputed rendering of her words was the chief evidence, and she was sentenced to a year on Blackwell's Island. She was twenty-four, and the trial made her the most famous anarchist in America.</p><p>Goldman used the penitentiary as she would use every institution — studying nursing in the prison hospital, an experience that gave her a profession and deepened her radicalism. The 1893 case opened a quarter-century of legal siege: repeated arrests for lectures on free speech and birth control, two weeks' imprisonment in 1916 for distributing contraception information, two years in Missouri State Penitentiary for organizing against the World War I draft, and finally deportation in 1919 — the state never finding a charge that silenced her, only a boat.</p>
HTML,
                    ],
                    [
                        'slug' => 'haymarket-era-anarchist-exclusion',
                        'title' => 'The Criminal Anarchy Laws & the Turner Case (1901–1904)',
                        'body' => <<<'HTML'
<p>The assassination of President McKinley by Leon Czolgosz in September 1901 — a man with only glancing contact with the anarchist movement — triggered the first American statutes criminalizing a political doctrine by name. New York's 1902 criminal anarchy law made advocating the overthrow of government by force a felony, punishing speech itself; New Jersey, Wisconsin, and Washington followed. Anarchists were mobbed, meeting halls closed, and Johann Most was imprisoned yet again merely for republishing a decades-old article.</p><p>Congress joined in with the Immigration Act of 1903, the Anarchist Exclusion Act — the first federal law since 1798 to bar immigrants for their beliefs. Its test case was John Turner, a mild English trade-union organizer arrested at a New York lecture in October 1903 and held at Ellis Island for deportation. The Free Speech League fought the case to the Supreme Court, which upheld the statute in 1904, establishing that noncitizens could be expelled for ideology alone — the legal foundation on which the Palmer raids, the Buford deportations, and every subsequent ideological-deportation campaign would be built.</p>
HTML,
                    ],
                ],
            ],

            'the-first-red-scare-1917-1920' => [
                'title' => 'The First Red Scare (1917–1920)',
                'children' => [
                    [
                        'slug' => 'red-scare-debs-espionage-act',
                        'title' => 'Debs & the Espionage Act Prosecutions (1917–1921)',
                        'body' => <<<'HTML'
<p>The Espionage Act of 1917 and the Sedition Act amendments of 1918 converted opposition to the World War into a federal crime, and roughly two thousand Americans were prosecuted for speeches, pamphlets, and private remarks. The emblematic case was Eugene V. Debs, the Socialist Party's four-time presidential candidate, convicted for a June 1918 speech in Canton, Ohio in which he told his audience they were "fit for something better than slavery and cannon fodder." Sentenced to ten years, he told the court: "while there is a lower class, I am in it… while there is a soul in prison, I am not free."</p><p>From Atlanta Penitentiary, as Convict No. 9653, Debs received nearly a million votes for president in 1920. Around him stood a movement of prisoners: Kate Richards O'Hare, sentenced to five years for an antiwar lecture; Rose Pastor Stokes, ten years for a letter to a newspaper; and hundreds of lesser-known socialists and pacifists. The Supreme Court upheld the convictions unanimously, but the amnesty campaign that followed built the modern American civil-liberties movement, and President Harding commuted Debs's sentence on Christmas Day 1921.</p>
HTML,
                    ],
                    [
                        'slug' => 'red-scare-iww-mass-trials',
                        'title' => 'The IWW Mass Trials (1917–1919)',
                        'body' => <<<'HTML'
<p>In September 1917, federal agents simultaneously raided every significant IWW office in the country, seizing five tons of records, and grand juries indicted virtually the union's entire leadership for conspiracy to obstruct the war. The Chicago trial of 1918 was the largest criminal trial in American history to that point: after five months, the jury took less than an hour to convict all defendants — Big Bill Haywood and Ralph Chaplin among roughly a hundred men — and Judge Kenesaw Mountain Landis imposed sentences of up to twenty years.</p><p>Companion mass trials at Sacramento — where silent defendants refused to recognize the court — and Wichita filled Leavenworth with what the union called its "class-war prisoners," more than a hundred at the peak. The trials were explicitly aimed at an organization rather than acts: the evidence was overwhelmingly songs, editorials, and stump speeches. Haywood jumped bail to Soviet Russia in 1921; the last Wobblies left Leavenworth only with the general amnesty of December 1923, by which time the union they led had been broken.</p>
HTML,
                    ],
                    [
                        'slug' => 'red-scare-palmer-raids-buford',
                        'title' => 'The Palmer Raids & the Buford Deportations (1919–1920)',
                        'body' => <<<'HTML'
<p>Attorney General A. Mitchell Palmer, with a young J. Edgar Hoover directing the Justice Department's new Radical Division, answered the 1919 bombings with the largest mass roundup in American history: coordinated raids in November 1919 and January 1920 swept up thousands of immigrant radicals in dozens of cities — estimates run from three to ten thousand — most seized without warrants, held incommunicado, and beaten in overcrowded detention. Since mere membership in a proscribed organization was a deportable offense for noncitizens, the government needed no criminal trials at all.</p><p>On December 21, 1919, the army transport Buford — the press called it the "Soviet Ark" — sailed from New York with 249 deportees, Emma Goldman and Alexander Berkman the most famous among them. The excesses provoked the founding of the ACLU and a devastating report by twelve prominent lawyers documenting the illegality; Assistant Labor Secretary Louis Post canceled thousands of deportation orders and survived impeachment for it. Palmer's predicted May Day 1920 uprising never came, and the panic collapsed — but the files Hoover built in 1919 became the foundation of fifty years of FBI political surveillance.</p>
HTML,
                    ],
                    [
                        'slug' => 'red-scare-criminal-syndicalism',
                        'title' => 'Criminal Syndicalism: Whitney, Gitlow & the State Cases (1917–1927)',
                        'body' => <<<'HTML'
<p>While federal prosecutors used the Espionage Act, the states built their own machinery: between 1917 and 1920 some two dozen enacted criminal syndicalism or criminal anarchy laws punishing the advocacy of industrial or political change by force. California's statute alone produced over five hundred arrests and more than a hundred San Quentin sentences, overwhelmingly of IWW members whose crime was carrying a red card. Charlotte Anita Whitney — a 52-year-old suffragist and philanthropist convicted for attending a Communist Labor Party convention — became the era's cause célèbre.</p><p>The Supreme Court upheld both New York's conviction of Benjamin Gitlow (1925) and Whitney's (1927), but the cases produced the dissents that eventually became law: Holmes in Gitlow, and Brandeis's concurrence in Whitney — "the fitting remedy for evil counsels is good ones" — the most quoted defense of free speech in American jurisprudence. Whitney was pardoned in 1927 by the governor who cited Brandeis directly; the syndicalism convictions were finally repudiated in Brandenburg v. Ohio (1969), four decades after the prisoners had served their time.</p>
HTML,
                    ],
                    [
                        'slug' => 'red-scare-sacco-vanzetti',
                        'title' => 'Sacco & Vanzetti (1920–1927)',
                        'body' => <<<'HTML'
<p>Nicola Sacco and Bartolomeo Vanzetti, Italian immigrant anarchists of the militant Galleanist circle, were arrested in May 1920 — at the height of the deportation panic — and convicted the following year of a payroll robbery and double murder in South Braintree, Massachusetts. The evidence was contested at every point; the atmosphere was not. Judge Webster Thayer, who privately called the defendants "anarchistic bastards," presided over both trial and the motions for a new trial, denying them all even after a convicted gunman confessed to the crime.</p><p>Seven years of appeals made the case the world's measure of American justice: protests filled capitals from Buenos Aires to Paris, and figures from Einstein to future justice Felix Frankfurter pleaded for review. A commission chaired by Harvard's president blessed the verdict, and the two men were electrocuted on August 23, 1927. Vanzetti's final statement — "that last moment belongs to us, that agony is our triumph" — entered the canon of prison literature; fifty years later Governor Michael Dukakis proclaimed that their trial had been permeated by prejudice and that any stigma should be forever removed from their names.</p>
HTML,
                    ],
                ],
            ],

            'world-war-ii-japanese-incarceration-the-first-smith-act-trials-1941-1945' => [
                'title' => 'World War II: Japanese Incarceration & the First Smith Act Trials (1941–1945)',
                'children' => [
                    [
                        'slug' => 'wwii-executive-order-9066',
                        'title' => 'Executive Order 9066 & the Camps (1942–1946)',
                        'body' => <<<'HTML'
<p>Executive Order 9066, signed by President Roosevelt on February 19, 1942, authorized the military to exclude "any or all persons" from the West Coast — in practice, the removal and incarceration of some 120,000 people of Japanese ancestry, two-thirds of them American citizens, without charge, hearing, or individual suspicion. Families were given days to dispose of homes and businesses, held in converted racetracks and fairgrounds, then shipped to ten War Relocation Authority camps in desert and swampland, behind barbed wire and under gun towers, for the better part of four years.</p><p>The government's own intelligence had found no basis for mass removal, a fact its lawyers concealed from the Supreme Court. Within the camps, dissenters — those who answered the loyalty questionnaire "no-no," protested conditions, or resisted the draft — were segregated at Tule Lake or imprisoned again in Justice Department internment camps, prisoners within the prison. Congress formally apologized in the Civil Liberties Act of 1988, acknowledging that the incarceration arose from "race prejudice, war hysteria, and a failure of political leadership" — the largest single act of political imprisonment in American history.</p>
HTML,
                    ],
                    [
                        'slug' => 'wwii-supreme-court-test-cases',
                        'title' => 'The Test Cases: Hirabayashi, Yasui, Korematsu & Endo (1942–1944)',
                        'body' => <<<'HTML'
<p>Four young Nisei carried the constitutional challenge. Minoru Yasui, a lawyer, walked a Portland street after curfew to force his own arrest and spent nine months in solitary confinement; Gordon Hirabayashi, a Quaker student, refused both curfew and removal as a matter of conscience; Fred Korematsu simply declined to report and was convicted of remaining in his own home town; Mitsuye Endo, a state employee of unquestioned loyalty, petitioned for habeas corpus from inside the camps. The Supreme Court upheld the curfew in 1943 and, in Korematsu v. United States (1944), the exclusion itself — over Justice Jackson's warning that the ruling lay about "like a loaded weapon."</p><p>Ex parte Endo, decided the same day as Korematsu, held that the government could not detain a concededly loyal citizen, forcing the camps' closure. Four decades later, historians found the wartime solicitor general had suppressed intelligence reports contradicting the military's claims; federal courts vacated the convictions of Korematsu (1983), Yasui, and Hirabayashi (1987) on writs of coram nobis. All three received the Presidential Medal of Freedom, and in 2018 the Supreme Court declared that "Korematsu was gravely wrong the day it was decided."</p>
HTML,
                    ],
                    [
                        'slug' => 'wwii-heart-mountain-resisters',
                        'title' => 'The Heart Mountain Draft Resisters (1944–1947)',
                        'body' => <<<'HTML'
<p>When the government began drafting young men out of the camps in 1944 while their families remained imprisoned, the Fair Play Committee at Wyoming's Heart Mountain camp declared it would comply the day the Constitution was restored to them — and not before. Sixty-three Heart Mountain resisters were convicted in the largest mass trial in Wyoming history, sentenced to three years, and sent to federal prison at McNeil Island and Leavenworth; roughly three hundred Nisei from all ten camps ultimately resisted the draft on the same grounds.</p><p>Seven Fair Play Committee leaders, along with journalist James Omura, who had defended the resisters in print, were tried separately for conspiracy; the leaders' convictions were overturned on appeal in late 1945, and Omura was acquitted — one of the era's few press-freedom victories. President Truman pardoned all the Nisei draft resisters in December 1947, but the men bore the stigma of disloyalty inside the Japanese American community for decades, until the redress movement of the 1980s reframed their stand as constitutional conscience of the highest order.</p>
HTML,
                    ],
                    [
                        'slug' => 'wwii-minneapolis-smith-act-trial',
                        'title' => 'The Minneapolis Smith Act Trial (1941)',
                        'body' => <<<'HTML'
<p>The Smith Act of 1940 — the first peacetime federal sedition law since 1798 — made it a crime to advocate overthowing the government or to belong to a group that did. Its first use came in 1941 not against fascists but against the Socialist Workers Party and the leadership of Minneapolis Teamsters Local 544, the militant local that had won the 1934 general strikes: twenty-nine were indicted after Local 544 broke with Teamster president Daniel Tobin, a Roosevelt ally who had asked the White House for help against his rivals.</p><p>Eighteen defendants, including SWP leader James P. Cannon and strike organizer Vincent Raymond Dunne, were convicted in December 1941 of advocating revolutionary doctrine — the evidence consisting almost entirely of Marxist classics and party resolutions — and served twelve to sixteen months in Sandstone and Danbury. The ACLU condemned the prosecution, and the Communist Party's applause for it would return upon them cruelly: the same statute, and the precedent set in Minneapolis, sent the CP's own leadership to prison at Foley Square eight years later.</p>
HTML,
                    ],
                    [
                        'slug' => 'wwii-conscientious-objector-strikes',
                        'title' => 'The Imprisoned Conscientious Objectors (1940–1946)',
                        'body' => <<<'HTML'
<p>Some six thousand conscientious objectors went to federal prison during the Second World War — most of them Jehovah's Witnesses denied ministerial exemptions, alongside secular pacifists, radical Christians, and men who refused even the alternative-service camps as conscription by another name. At the peak, one in six federal prisoners was a draft resister. Among them were Bayard Rustin, who served over two years in Ashland and organized against its Jim Crow seating from inside, and David Dellinger and the Union Theological Seminary students who had refused to register at all.</p><p>The CO prisoners turned the penitentiaries into laboratories of nonviolent resistance: hunger strikes at Lewisburg and Danbury against mail censorship and racial segregation — the 135-day Danbury strike of 1943 forced the first desegregation of a federal prison dining hall — and work strikes that prefigured the tactics of the civil-rights movement. The generation that emerged from these cellblocks — Rustin, Dellinger, Jim Peck, George Houser — went on to organize the first freedom rides and the antiwar movements of the following thirty years.</p>
HTML,
                    ],
                ],
            ],

            'mccarthyism-1947-1957' => [
                'title' => 'McCarthyism (1947–1957)',
                'children' => [
                    [
                        'slug' => 'mccarthyism-hollywood-ten',
                        'title' => 'The Hollywood Ten (1947–1950)',
                        'body' => <<<'HTML'
<p>In October 1947 the House Committee on Un-American Activities summoned screenwriters and directors to answer for communism in the film industry. Ten — among them Dalton Trumbo, John Howard Lawson, Ring Lardner Jr., and Albert Maltz — refused on First Amendment grounds to answer whether they were or had ever been Communists, arguing that Congress had no power to inquire into belief. All ten were cited for contempt of Congress, convicted, and, when the Supreme Court declined review in 1950, served sentences of six months to a year.</p><p>Trumbo and Lawson entered the same federal prison at Ashland, Kentucky where, in a celebrated irony, Trumbo encountered J. Parnell Thomas, the HUAC chairman who had presided over the hearings, himself imprisoned for payroll fraud. The studios' Waldorf Statement blacklisted the Ten the week of the contempt citations, inaugurating an industry purge that lasted more than a decade and rested on hundreds of careers; Trumbo, writing under fronts, won two Academy Awards he could not claim. The Ten established the template of the era: prison not for acts, but for refusing to name beliefs and associates.</p>
HTML,
                    ],
                    [
                        'slug' => 'mccarthyism-foley-square-trial',
                        'title' => 'The Foley Square Smith Act Trial (1949–1951)',
                        'body' => <<<'HTML'
<p>The government's central blow against the Communist Party was the 1949 trial of its eleven-member national board at Foley Square in Manhattan — nine months before Judge Harold Medina, the longest federal criminal trial to that date. The charge was not espionage, sabotage, or any overt act, but conspiring to advocate the overthrow of the government: the evidence was the party's existence, its classics, and the testimony of FBI informants. All eleven, including general secretary Eugene Dennis, Henry Winston, and Gus Hall, were convicted; Medina then jailed the defense lawyers themselves for contempt, a warning to the bar that defending Communists carried its own price.</p><p>The Supreme Court upheld the convictions in Dennis v. United States (1951), with Justices Black and Douglas dissenting — Black predicting that "in calmer times" the decision would be seen for what it was. Four defendants forfeited bail and went underground; Robert Thompson, a decorated war hero, and Henry Winston, who went blind in prison after a tumor went untreated, paid most heavily. Winston, his sentence commuted in 1961, remarked: "They have robbed me of my sight, but not my vision."</p>
HTML,
                    ],
                    [
                        'slug' => 'mccarthyism-second-string-yates',
                        'title' => 'The Second-String Prosecutions & Yates (1951–1957)',
                        'body' => <<<'HTML'
<p>After Dennis, the Justice Department indicted the Communist Party's entire secondary leadership — more than 140 state and regional officers in waves of "second-string" Smith Act trials from New York to Los Angeles, Honolulu to Puerto Rico. Elizabeth Gurley Flynn, the sixty-one-year-old veteran of the Lawrence and Paterson strikes, was convicted in 1953 and served over two years at Alderson, writing a prison memoir; Claudia Jones, the Trinidad-born organizer, was imprisoned and then deported. Alongside the trials ran the Taft-Hartley non-Communist affidavits, denaturalization drives, and the McCarran Act's registration machinery — an interlocking system that made party membership effectively a status crime.</p><p>The machine stopped in 1957. In Yates v. United States, the Supreme Court distinguished the advocacy of abstract doctrine from incitement to action, reversing or ordering acquittals for fourteen California defendants and setting a standard under which virtually no Smith Act case could be won. The remaining prosecutions collapsed, and no American has been imprisoned under the act's advocacy clauses since — though by then the party the trials targeted had been reduced to a shell honeycombed with informants.</p>
HTML,
                    ],
                    [
                        'slug' => 'mccarthyism-contempt-of-congress',
                        'title' => "Contempt of Congress: The Committee's Prisoners (1946–1961)",
                        'body' => <<<'HTML'
<p>The investigating committees sent their own stream of prisoners to jail — witnesses whose offense was refusing to inform. The board of the Joint Anti-Fascist Refugee Committee, including Dr. Edward Barsky and the novelist Howard Fast, served three-month to six-month terms in 1950 for refusing to surrender the names of donors who had aided refugees from Franco's Spain. Screenwriters, professors, union officers, and clergy followed, as HUAC perfected the exposure hearing: the point was not legislation but the ritual demand for names, with prison for those who stood on the First Amendment rather than the Fifth.</p><p>The last of the committee's prisoners came at the era's end: Carl Braden, the Louisville civil-rights activist, and Frank Wilkinson, organizer of the campaign to abolish HUAC itself, each served nine months to a year after the Supreme Court upheld their convictions 5–4 in 1961, and Willard Uphaus, a sixty-nine-year-old pacifist educator, spent a year in a New Hampshire jail for refusing to name his summer camp's guests. Their cases — and the national defense campaigns around them — helped turn public opinion against the committees, which lost their power in the following decade.</p>
HTML,
                    ],
                    [
                        'slug' => 'mccarthyism-rosenbergs-sobell',
                        'title' => 'The Rosenbergs & Morton Sobell (1950–1969)',
                        'body' => <<<'HTML'
<p>The atomic spy case sat at the center of the era's fears. Julius and Ethel Rosenberg were convicted in 1951 of conspiracy to commit espionage for the Soviet Union on the testimony of Ethel's brother, David Greenglass; Judge Irving Kaufman, blaming them for the Korean War itself, imposed death sentences that shocked much of the world. A global clemency campaign — the Pope, Einstein, and millions of petitioners — failed, and the couple were electrocuted at Sing Sing on June 19, 1953, leaving two orphaned sons and a case that has never left American political memory.</p><p>Their co-defendant Morton Sobell, kidnapped back from Mexico to stand trial, received thirty years and served more than seventeen, including five at Alcatraz, while his wife Helen led the long campaign for his release. Decades later the documentary record complicated every side's certainties: Soviet cables and Greenglass's recantation indicated Julius had indeed run an espionage ring, while Ethel's conviction rested on testimony her brother admitted fabricating, and Sobell — who confessed in 2008 — had passed military, not atomic, secrets. The executions, though, remain what contemporaries called them: the Cold War's most irrevocable act of political justice.</p>
HTML,
                    ],
                ],
            ],

            'cointelpro-1956-1971' => [
                'title' => 'COINTELPRO (1956–1971)',
                'children' => [
                    [
                        'slug' => 'cointelpro-era-the-program',
                        'title' => 'The Program: Disruption as Policy (1956–1971)',
                        'body' => <<<'HTML'
<p>COINTELPRO — the FBI's Counterintelligence Program — began in August 1956 as a covert campaign to "disrupt" the Communist Party and expanded, program by program, to the Socialist Workers Party, the Klan, "Black Nationalist Hate Groups," and the New Left. Over fifteen years the Bureau ran more than two thousand documented operations: forged letters designed to break marriages and provoke gang wars, anonymous tips to employers and landlords, planted news stories, snitch-jacketing of loyal activists as informants, and the systematic use of criminal prosecution as a disruption tool.</p><p>The program's own directives stated the goal plainly — to "expose, disrupt, misdirect, discredit, or otherwise neutralize" movements, and to "prevent the rise of a messiah" who could unify Black America. For the prisoner-support movement, COINTELPRO is the indispensable context: many of the era's longest-serving political prisoners were convicted in trials shaped by informants, manufactured evidence, and suppressed exculpatory files, discoverable only years later through FOIA litigation and the Church Committee's investigations.</p>
HTML,
                    ],
                    [
                        'slug' => 'cointelpro-era-media-burglary',
                        'title' => 'The Media Burglary & the Church Committee (1971–1976)',
                        'body' => <<<'HTML'
<p>On the night of March 8, 1971, eight antiwar activists calling themselves the Citizens' Commission to Investigate the FBI picked the lock of the Bureau's resident agency in Media, Pennsylvania and removed every file in the office. The documents they mailed to journalists — including the first page ever seen bearing the word COINTELPRO — proved that the FBI's mission against dissent was surveillance and disruption, not law enforcement: agents were instructed to intensify "the paranoia endemic in these circles" and leave activists convinced there was "an FBI agent behind every mailbox."</p><p>None of the burglars was ever caught; five revealed themselves only in 2014. NBC reporter Carl Stern's FOIA suits forced out the fuller COINTELPRO record, and the Senate's Church Committee (1975–76) documented the program's scope, concluding the Bureau had conducted "a sophisticated vigilante operation" against lawful political activity. The exposure ended the program on paper, produced the FBI guidelines regime, and gave hundreds of defendants and prisoners the documentary basis for reopening their cases.</p>
HTML,
                    ],
                    [
                        'slug' => 'cointelpro-era-panther-21',
                        'title' => 'The Panther 21 (1969–1971)',
                        'body' => <<<'HTML'
<p>In April 1969, New York indicted twenty-one members of the city's Black Panther chapter — effectively its entire leadership — on charges of conspiring to bomb police stations, department stores, and the Bronx Botanical Garden. Bail was set at $100,000 each, imprisoning the chapter for two years before trial; the case was built by undercover officers of the NYPD's BOSS unit who had helped found the chapter. The defendants' collective autobiography, "Look for Me in the Whirlwind," and Afeni Shakur's pro se defense from the Women's House of Detention made the case an international cause.</p><p>On May 13, 1971, after the longest political trial in New York history, the jury acquitted all defendants on all 156 counts in less than an hour of deliberation — a repudiation so complete it embarrassed the prosecution into silence. But the operation had succeeded as disruption: the chapter was bankrupted and split, the two years of pretrial detention were never compensated, and several of the Twenty-One were pursued into new cases for years afterward.</p>
HTML,
                    ],
                    [
                        'slug' => 'cointelpro-era-hampton-chicago-raid',
                        'title' => 'Fred Hampton & the Chicago Raid (1969–1982)',
                        'body' => <<<'HTML'
<p>Fred Hampton, the 21-year-old chairman of the Illinois Black Panther Party, had built the original Rainbow Coalition and a free-breakfast program that made him, in the FBI's own assessment, precisely the kind of unifying figure COINTELPRO existed to stop. He was already a political prisoner in the ordinary sense — serving time on an absurd conviction for allegedly stealing $71 worth of ice cream bars — when the Bureau's informant William O'Neal, his chief of security, supplied a floor plan of his apartment marking where he slept.</p><p>Before dawn on December 4, 1969, Chicago police attached to the state's attorney's office fired nearly a hundred rounds into the apartment, killing Hampton in his bed — likely drugged with barbiturates — and Mark Clark, and wounding four others; one shot may have come from the Panthers' side. The survivors were charged with attempted murder while the raiders were exonerated. Thirteen years of civil litigation ended in 1982 with a $1.85 million settlement and judicial findings of a cover-up spanning the FBI, the prosecutor, and the police — the clearest documented case of a political assassination carried out under color of American law enforcement.</p>
HTML,
                    ],
                    [
                        'slug' => 'cointelpro-era-frameups-pratt-bin-wahad',
                        'title' => 'The Frame-Ups: Geronimo Pratt & Dhoruba bin Wahad (1970–1997)',
                        'body' => <<<'HTML'
<p>The program's most durable products were convictions. Geronimo ji-Jaga Pratt, the decorated Vietnam veteran who led the Los Angeles Panthers, was convicted in 1972 of a 1968 Santa Monica murder; the FBI possessed wiretap evidence placing him four hundred miles away in Oakland and concealed it, while the key witness hid that he was an informant for both the Bureau and the LAPD. Pratt served twenty-seven years — eight of them in solitary — before a judge vacated the conviction in 1997; the government later paid $4.5 million rather than defend the case.</p><p>Dhoruba bin Wahad of the Panther 21 was convicted in 1973 of machine-gunning two officers guarding a district attorney's home, after two trials ended without verdict; nineteen years later, FOIA litigation produced over 300,000 pages showing the case had been a COINTELPRO/NEWKILL operation and that the star witness's recantations were suppressed. His conviction was vacated in 1990. The two cases became the movement's proof text: that some prisoners called "cop killers" and "terrorists" were, on the government's own documents, targets first and defendants second.</p>
HTML,
                    ],
                ],
            ],

            'the-vietnam-war-era-1964-1975' => [
                'title' => 'The Vietnam War Era (1964–1975)',
                'children' => [
                    [
                        'slug' => 'vietnam-era-draft-resisters',
                        'title' => 'The Draft Resisters (1964–1974)',
                        'body' => <<<'HTML'
<p>Resistance to the Vietnam draft produced criminal referrals against more than 200,000 men; roughly 25,000 were indicted, nearly 9,000 convicted, and over 3,000 imprisoned, with sentences commonly running two to five years. The Resistance, founded in 1967, made refusal public and collective — draft cards returned by the thousand, induction centers blockaded — and its leaders went to prison deliberately: David Harris was seized from the house he shared with Joan Baez and served fifteen months. Muhammad Ali, the era's most famous resister, was convicted in 1967, stripped of his title, and spared prison only when the Supreme Court reversed in 1971.</p><p>The resisters' distribution told its own story: Jehovah's Witnesses and poor and Black defendants — who lacked the deferments that sheltered the middle class — filled the cellblocks, and Black Muslims prosecuted for refusing induction followed the path Elijah Muhammad had walked in the 1940s. President Carter's 1977 pardon of draft offenders closed the ledger, but the movement's veterans counted the prison terms as the war's forgotten American casualties — and its proof that conscience could be organized on a mass scale.</p>
HTML,
                    ],
                    [
                        'slug' => 'vietnam-era-catonsville-draft-board-raids',
                        'title' => 'The Catonsville Nine & the Draft Board Raids (1967–1973)',
                        'body' => <<<'HTML'
<p>On May 17, 1968, nine Catholics led by the priest brothers Daniel and Philip Berrigan removed 378 draft files from the board office in Catonsville, Maryland and burned them in the parking lot with homemade napalm, praying as they waited for arrest. "Our apologies, good friends," Daniel wrote, "for the fracture of good order, the burning of paper instead of children." The trial that followed — dramatized in Daniel's play "The Trial of the Catonsville Nine" — and sentences of two to three and a half years launched the "ultra-resistance": dozens of raids by the Milwaukee Fourteen, the DC Nine, and others that destroyed tens of thousands of draft records.</p><p>Daniel Berrigan went underground rather than report to prison, appearing at rallies while the FBI hunted him for four months until his capture on Block Island in August 1970 — the first priest on the Bureau's most-wanted list. The campaign culminated in acquittal: the Camden 28, whose 1971 raid had been enabled by an FBI informant, were found not guilty across the board in 1973, a jury nullification verdict Supreme Court Justice William Brennan later called a great moment in the history of trial by jury.</p>
HTML,
                    ],
                    [
                        'slug' => 'vietnam-era-chicago-conspiracy-trial',
                        'title' => 'The Chicago Conspiracy Trial (1969–1972)',
                        'body' => <<<'HTML'
<p>The Nixon Justice Department's showpiece prosecution charged eight movement figures — Abbie Hoffman, Jerry Rubin, Dave Dellinger, Tom Hayden, Rennie Davis, John Froines, Lee Weiner, and Black Panther chairman Bobby Seale — with conspiring to incite the riots at the 1968 Democratic Convention, under the new federal anti-riot "Rap Brown law." The five-month trial before Judge Julius Hoffman became the era's defining courtroom theater, its indelible image the judge's order that Seale, who had demanded the right to defend himself, be bound and gagged in his chair before being severed from the case with a four-year contempt sentence.</p><p>The jury acquitted all defendants of conspiracy and convicted five of crossing state lines to incite riot; Judge Hoffman added contempt sentences against every defendant and both lawyers. In 1972 the Seventh Circuit reversed the convictions, citing the judge's open hostility and the FBI's surveillance of defense lawyers, and most of the contempts fell with them. The government had spent three years and made no charge stick — but the trial had meanwhile advertised, to a generation on both sides, that political dissent in America was being tried as crime.</p>
HTML,
                    ],
                    [
                        'slug' => 'vietnam-era-gi-resistance',
                        'title' => 'GI Resistance: The Fort Hood Three & the Presidio 27 (1966–1970)',
                        'body' => <<<'HTML'
<p>The war produced political prisoners inside the military itself. The Fort Hood Three — Privates James Johnson, Dennis Mora, and David Samas — publicly refused deployment to Vietnam in 1966, calling the war "immoral, illegal and unjust," and were court-martialed and sentenced to three to five years' hard labor in Leavenworth. Captain Howard Levy, an Army dermatologist, refused to train Green Beret medics and drew three years; his case put the Nuremberg defense before American military courts. Stockades filled with resisters, and the coffeehouse movement and underground GI press that supported them became prosecution targets in their own right.</p><p>The emblematic case came at the San Francisco Presidio in October 1968, when twenty-seven stockade prisoners sat down and sang "We Shall Overcome" to protest the shotgun killing of a fellow prisoner and the stockade's conditions. The Army charged them with mutiny — a capital offense — and initial sentences ran as high as sixteen years. Public outrage forced drastic reductions on review, and the Presidio 27, most of them working-class draftees who had gone AWOL rather than fight, became the emblem of a military justice system punishing dissent as insurrection.</p>
HTML,
                    ],
                    [
                        'slug' => 'vietnam-era-pentagon-papers',
                        'title' => 'Ellsberg, Russo & the Pentagon Papers (1971–1973)',
                        'body' => <<<'HTML'
<p>Daniel Ellsberg, a RAND analyst who had helped write the Defense Department's secret history of the war, copied its seven thousand pages with colleague Anthony Russo and gave them to the press in 1971. The government's attempt at prior restraint failed within weeks in New York Times Co. v. United States, so it turned to the criminal law: Ellsberg and Russo were indicted under the Espionage Act — the first use of the 1917 statute against a leak to the American public — facing over a hundred years combined. Russo had already served forty-seven days in jail for refusing to testify against Ellsberg before a grand jury.</p><p>The trial ended in May 1973 when Judge Matthew Byrne dismissed all charges with prejudice, the misconduct having become unignorable: the White House "plumbers" had burglarized Ellsberg's psychiatrist's office, the CIA had prepared a psychological profile, wiretap records had vanished, and Byrne himself had been dangled the FBI directorship mid-trial. The dismissal left the central question unresolved — whether the Espionage Act may constitutionally be used against whistleblowers — a question that returned, with fewer happy endings, in the prosecutions of Manning, Kiriakou, Winner, and Hale a generation later.</p>
HTML,
                    ],
                ],
            ],

            'the-american-indian-movement-wounded-knee-1973-1977' => [
                'title' => 'The American Indian Movement & Wounded Knee (1973–1977)',
                'children' => [
                    [
                        'slug' => 'wounded-knee-era-custer-courthouse',
                        'title' => 'Custer & the Courthouse Cases (1973)',
                        'body' => <<<'HTML'
<p>Weeks before Wounded Knee, in February 1973, AIM members and Oglala families confronted authorities at the Custer County courthouse in South Dakota after prosecutors charged the white killer of Wesley Bad Heart Bull, a young Lakota man stabbed to death outside a bar, with only second-degree manslaughter. Police in riot gear met the delegation on the courthouse steps; in the melee that followed, the courthouse annex and chamber-of-commerce building burned, and dozens were arrested — including Wesley's mother, Sarah Bad Heart Bull, who had come seeking justice for her son.</p><p>The dispositions became the movement's shorthand for South Dakota justice: Sarah Bad Heart Bull was convicted of riot-related charges and served months in prison, while her son's killer served no prison time at all, acquitted after arguing self-defense. The Custer prosecutions, running alongside the Wounded Knee cases, helped convince a generation of Lakota people that the courts themselves were instruments of the occupation AIM had risen against.</p>
HTML,
                    ],
                    [
                        'slug' => 'wounded-knee-era-leadership-trial',
                        'title' => 'The Occupation & the Leadership Trial (1973–1974)',
                        'body' => <<<'HTML'
<p>For seventy-one days in the spring of 1973, AIM and Oglala Lakota traditionals held the hamlet of Wounded Knee on Pine Ridge — site of the 1890 massacre — against federal marshals, the FBI, and armored personnel carriers, demanding hearings on the Fort Laramie Treaty and an end to the Wilson tribal government's violence. Two defenders, Frank Clearwater and Buddy Lamont, were killed by government fire before the stand-down on May 8. The occupation produced one of the largest federal prosecutions in American history: over 500 arrests and some 185 indictments.</p><p>The government tried AIM leaders Dennis Banks and Russell Means first, in St. Paul, on charges carrying decades. After eight and a half months, Judge Fred Nichol dismissed the entire case for governmental misconduct — an altered witness statement, concealed wiretaps and informants (including one inside the defense committee), and an FBI he found had "stooped to a new low." The dismissal of the leadership case, and the collapse of most that followed, established the Wounded Knee prosecutions as an exercise in what the defense called "lawfare": punishment by process rather than verdict.</p>
HTML,
                    ],
                    [
                        'slug' => 'wounded-knee-era-non-leadership-cases',
                        'title' => 'The Non-Leadership Trials (1973–1976)',
                        'body' => <<<'HTML'
<p>Behind the celebrated leadership case ran the grinding machinery of the "non-leadership" prosecutions: hundreds of defendants — occupation participants, supply runners, and people arrested at roadblocks — cycled through federal courts in South Dakota, Nebraska, and Iowa for three years. The Wounded Knee Legal Defense/Offense Committee, staffed by volunteer lawyers and legal workers, matched the government motion for motion, and the results were unprecedented: of the roughly 185 indictments, barely fifteen produced convictions, a failure rate documenting how little criminal conduct underlay the mass charging.</p><p>The strategy's cost was the point. Defendants spent years traveling to hearings, posting bond, and living under indictment; AIM's organizing capacity was consumed by its own defense; and the movement's resources drained into courtrooms while the Wilson regime's violence on Pine Ridge went unprosecuted. The committee's veterans carried the model — political defense organized as a movement institution — into the Peltier, Skyhorse-Mohawk, and grand-jury-resistance fights that followed.</p>
HTML,
                    ],
                    [
                        'slug' => 'wounded-knee-era-resmurs-peltier',
                        'title' => 'Pine Ridge, RESMURS & Leonard Peltier (1975–2025)',
                        'body' => <<<'HTML'
<p>The years after Wounded Knee were Pine Ridge's "reign of terror": dozens of AIM members and traditionals killed while the tribal chairman's GOON squads operated with impunity. Into that war zone came the June 26, 1975 firefight at the Jumping Bull ranch in Oglala, in which FBI agents Jack Coler and Ronald Williams and AIM member Joe Stuntz were killed. The RESMURS investigation charged three AIM members: Bob Robideau and Dino Butler were acquitted at Cedar Rapids in 1976 by a jury that accepted they had acted amid an atmosphere of terror the government itself had fed.</p><p>Leonard Peltier, extradited from Canada on an affidavit its signer — Myrtle Poor Bear, who had never met him — later repudiated, was tried separately under different rulings and convicted in 1977 of two life terms. The case against him eroded for decades: ballistics reports withheld at trial, the prosecution's theory shifting on appeal to "aiding and abetting," and appellate judges who upheld the verdict while writing openly of their discomfort. Amnesty International, the UN, and the Pope's emissaries sought clemency through nearly fifty years of imprisonment, until President Biden commuted the sentence to home confinement in January 2025 — an end to America's longest-running political-prisoner case, without the exoneration his movement sought.</p>
HTML,
                    ],
                    [
                        'slug' => 'wounded-knee-era-skyhorse-mohawk',
                        'title' => 'The Skyhorse–Mohawk Case (1974–1978)',
                        'body' => <<<'HTML'
<p>Paul Skyhorse Durant and Richard Mohawk Billings, two AIM activists working at the movement's Los Angeles camp, were charged in 1974 with the torture-murder of a taxi driver, George Aird — a killing to which three other camp residents, who received immunity or light dispositions, were directly linked by the physical evidence. Prosecutors nonetheless built their case against the two most politically identified defendants, and the pair spent years in pretrial custody while the proceedings, moved to Los Angeles, became one of the longest criminal trials in California history.</p><p>The jury acquitted both men in May 1978, jurors saying afterward that the case should never have been brought. By then the prosecution had served a function no verdict could undo: AIM's California organization had been bankrupted and stigmatized — headlines about "AIM murders" ran for four years — and movement lawyers came to cite Skyhorse-Mohawk alongside the Wounded Knee dismissals as the era's clearest example of prosecution as counterinsurgency, where acquittal arrives only after the organization is destroyed.</p>
HTML,
                    ],
                ],
            ],

            'the-reagan-era-1981-1989' => [
                'title' => 'The Reagan Era (1981–1989)',
                'children' => [
                    [
                        'slug' => 'reagan-era-plowshares-prosecutions',
                        'title' => 'The Plowshares Prosecutions (1980–1989)',
                        'body' => <<<'HTML'
<p>On September 9, 1980, eight Catholic activists including Daniel and Philip Berrigan entered the General Electric plant at King of Prussia, Pennsylvania, hammered on Mark 12A missile nose cones, and poured their own blood on documents — the first Plowshares action, taking Isaiah's injunction to beat swords into plowshares literally. Convicted of burglary and criminal mischief and sentenced to up to ten years, the Plowshares Eight spent a decade in appeals before being resentenced in 1990 to time served; by then the action had seeded a movement.</p><p>Through the Reagan buildup, dozens of Plowshares groups hammered on bombers, submarines, and missile silos and accepted the consequences in court, where judges routinely barred any defense of necessity or international law. Sentences escalated sharply for silo actions in the Midwest: Helen Woodson and Fr. Carl Kabat drew eighteen-year terms for the 1984 Silo Pruning Hooks action — punishment for symbolic disarmament that exceeded many sentences for violent crime, and that made the Plowshares prisoners a fixture of the era's political-prisoner rolls.</p>
HTML,
                    ],
                    [
                        'slug' => 'reagan-era-sanctuary-trials',
                        'title' => 'The Sanctuary Movement Trials (1984–1986)',
                        'body' => <<<'HTML'
<p>As Salvadorans and Guatemalans fleeing U.S.-backed military regimes were denied asylum at rates above ninety-seven percent, hundreds of congregations declared themselves sanctuaries and moved refugees along a new underground railroad. The government answered with Operation Sombrero: paid informants wearing wires into Bible-study meetings and worship services, and a 1985 indictment of sixteen sanctuary workers in Arizona — priests, nuns, a Presbyterian minister, and lay volunteers.</p><p>At the 1985–86 Tucson trial the judge barred every defense that mattered — refugee law, religious motive, conditions in El Salvador — and eight defendants including Rev. John Fife and Fr. Anthony Clark were convicted of smuggling and harboring; all received probation, the government having concluded that imprisoning clergy would be a public-relations disaster. In Texas, Stacey Merkt and Jack Elder of Casa Óscar Romero had already been prosecuted — Merkt served jail time and became an Amnesty International prisoner of conscience. The trials failed as deterrence: sanctuary congregations doubled during the prosecution, and the movement's litigation ultimately forced the government to reopen tens of thousands of asylum cases.</p>
HTML,
                    ],
                    [
                        'slug' => 'reagan-era-resistance-conspiracy-lexington',
                        'title' => 'The Resistance Conspiracy Case & the Lexington HSU (1984–1990)',
                        'body' => <<<'HTML'
<p>The decade's armed-struggle prosecutions culminated in the Resistance Conspiracy case: six radicals — Susan Rosenberg, Marilyn Buck, Linda Evans, Laura Whitehorn, Alan Berkman, and Tim Blunk — charged with conspiracy in a series of symbolic bombings of government buildings, including the 1983 Capitol bombing after the Grenada invasion, in which no one was injured. Several were already serving extraordinary sentences: Rosenberg and Blunk had received fifty-eight years for possession of explosives — many times the average federal murder sentence — and Evans forty years for firearms purchases.</p><p>The women's confinement became a scandal of its own. In 1986 the Bureau of Prisons opened a High Security Unit sixty feet underground at Lexington, Kentucky — small-group isolation, constant lighting and camera surveillance, and conditions Amnesty International condemned as deliberately designed for political prisoners. A federal judge found in 1988 that assignment to the unit had been based on the women's political beliefs, and the unit was closed. The case's prisoners were among the last of the era released: Whitehorn in 1999, Rosenberg and Evans by presidential commutation in January 2001, Buck — after decades and a terminal illness — weeks before her death in 2010.</p>
HTML,
                    ],
                    [
                        'slug' => 'reagan-era-ohio-7-sedition-trial',
                        'title' => 'The Ohio 7 Sedition Trial (1985–1989)',
                        'body' => <<<'HTML'
<p>The United Freedom Front — a clandestine group of working-class radicals led by Vietnam veteran Raymond Luc Levasseur — spent a decade underground bombing courthouses and corporate offices tied to apartheid South Africa and Central America policy, actions timed to avoid casualties. Captured in 1984–85 after one of the largest manhunts in FBI history, the "Ohio 7" received long sentences for the bombings themselves; Levasseur drew forty-five years, and Tom Manning a consecutive life term for the killing of a New Jersey trooper in a traffic-stop shootout.</p><p>The government then reached for the rarest charge in the federal code: seditious conspiracy. The 1989 trial of Levasseur and others at Springfield, Massachusetts sought to criminalize the group's politics wholesale — and failed completely, ending in acquittals and a hung jury after jurors balked at punishing the same conduct twice under an ideological label. The verdict, following the failed 1988 Fort Smith sedition trial of white supremacists, effectively retired seditious conspiracy for a generation. The prisoners served on: Levasseur until 2004, Manning until his death in federal prison in 2019.</p>
HTML,
                    ],
                    [
                        'slug' => 'reagan-era-grand-jury-resisters',
                        'title' => 'The Grand Jury Resisters (1981–1990)',
                        'body' => <<<'HTML'
<p>The era's least visible political prisoners committed no crime at all. Federal grand juries investigating the Puerto Rican independence movement, the sanctuary networks, and clandestine organizations subpoenaed activists en masse; those who refused on principle to testify about their movements were jailed for civil contempt — imprisonment without charge, trial, or conviction, renewable subpoena by subpoena. Puerto Rican independentistas made non-collaboration a doctrine: Julio, Andrés, and Ricardo Rosado, Maria Cueto, and others served months and in some cases years across the late 1970s and 1980s solely for their silence.</p><p>Because civil contempt is nominally coercive rather than punitive, the resisters occupied a legal void — no sentence to appeal, no parole date, release only upon submission or the grand jury's expiration. Movement lawyers documented how the device functioned as internment: activists cycled from subpoena to cellblock and back, organizations were mapped through who refused, and support committees learned to treat grand-jury resistance as a form of political imprisonment in its own right. The tradition they built — refuse, organize, serve the time — passed directly to the resisters of the Green Scare and Standing Rock decades later.</p>
HTML,
                    ],
                ],
            ],

            'the-anti-globalization-movement-1999-2001' => [
                'title' => 'The Anti-Globalization Movement (1999–2001)',
                'children' => [
                    [
                        'slug' => 'antiglob-battle-of-seattle',
                        'title' => 'The Battle of Seattle (1999–2007)',
                        'body' => <<<'HTML'
<p>On November 30, 1999, tens of thousands of unionists, environmentalists, and direct-action affinity groups shut down the opening of the World Trade Organization's ministerial in Seattle. The city declared a civil emergency, established a fifty-block "no-protest zone," and over the following days police swept up roughly six hundred people — the overwhelming majority arrested not for property destruction but for standing in the wrong part of downtown, many while complying with dispersal orders or simply shopping.</p><p>The prosecutions evaporated on contact with courtrooms: nearly all charges were dismissed, and juries acquitted most of the handful tried. The aftermath ran longer than the trials — in 2007 a federal jury found the mass arrests unconstitutional, and Seattle paid about one million dollars to a class of arrestees. But "Seattle" had already entered police doctrine as the disaster to be prevented, and the decade of preemptive raids, mass kettles, and militarized summit policing that followed was designed explicitly so that no city would be "another Seattle."</p>
HTML,
                    ],
                    [
                        'slug' => 'antiglob-a16-washington',
                        'title' => 'A16: The IMF & World Bank Protests (2000)',
                        'body' => <<<'HTML'
<p>Five months after Seattle, the movement converged on Washington for the April 2000 meetings of the IMF and World Bank. Police adopted the new preemptive playbook: the day before the main actions, fire marshals raided and closed the Convergence Center — the movement's organizing hub — seizing banners and puppets as "fire hazards," and a marching column of hundreds was trapped and arrested en masse near the Justice Department before any protest occurred. Roughly 1,300 people were arrested across the weekend, including legal observers and journalists.</p><p>Arrestees practicing jail solidarity — refusing to give names to force collective negotiation — were held for days in conditions that produced years of litigation. Nearly all charges were dropped or resolved with token fines, and the District of Columbia eventually paid millions across settlements for A16 and its successor mass arrests, with the 2002 Pershing Park kettle alone costing $8.25 million. The pattern was now fixed: unconstitutional mass arrest as crowd management, paid for by taxpayers years later, with the immediate goal — clearing the streets during the summit — achieved every time.</p>
HTML,
                    ],
                    [
                        'slug' => 'antiglob-r2k-philadelphia',
                        'title' => 'R2K: The Philadelphia RNC Prosecutions (2000–2004)',
                        'body' => <<<'HTML'
<p>At the Republican National Convention in Philadelphia in August 2000, police raided the puppet-making warehouse on the word of state-police infiltrators — arresting seventy-five people for assembling street theater — and swept up more than four hundred others in the streets. Prosecutors then did something new: they charged organizers as ringleaders and asked for bail designed to incapacitate. Ruckus Society director John Sellers was held on $1 million bail on misdemeanor charges; organizer Kate Sorensen faced $1 million as well; young activist Camilo Viveiros was charged with assaulting the police commissioner and faced decades in prison.</p><p>The cases collapsed at a rate exceeding ninety-five percent — Viveiros was acquitted in 2004 after eyewitnesses contradicted the police account — and civil suits followed. But the R2K prosecutions marked the tactical frontier the next two decades would inhabit: infiltration of art spaces, conspiracy theories built on organizing itself, ruinous bail as pretrial punishment, and charges calibrated to consume a movement's resources regardless of verdict — the direct ancestor of the J20 prosecution seventeen years later.</p>
HTML,
                    ],
                    [
                        'slug' => 'antiglob-miami-model',
                        'title' => 'The Miami Model (2003)',
                        'body' => <<<'HTML'
<p>The era's policing doctrine reached its finished form at the Free Trade Area of the Americas summit in Miami in November 2003. Armored, unbadged officers from forty coordinated agencies — funded by an $8.5 million earmark tucked into the Iraq war appropriation — met permitted marches with rubber bullets, tasers, and pepper spray, embedded police provocateurs in the crowds, and arrested hundreds, including elderly retirees from the AFL-CIO march. Police chief John Timoney, architect of the Philadelphia RNC response, gave the approach its name: the Miami Model.</p><p>Nearly every criminal case failed — a county judge who witnessed the streets said he had seen "no less than twenty felonies committed by police officers" — and the city paid out settlements for years. Miami closed the anti-globalization cycle that Seattle had opened: the movement's summit-hopping phase ended, but the model outlived it, reappearing at the 2004 New York RNC (1,800 arrests, a $18 million settlement), at Occupy's evictions, and in the federal response of 2020 — the durable institutional legacy of four years of trade-summit protest.</p>
HTML,
                    ],
                ],
            ],

            'the-war-on-terror-2001' => [
                'title' => 'The War on Terror (2001–)',
                'children' => [
                    [
                        'slug' => 'war-on-terror-post-911-sweeps',
                        'title' => 'The Post-9/11 Sweeps (2001–2003)',
                        'body' => <<<'HTML'
<p>In the weeks after September 11, the Justice Department detained more than 1,200 noncitizens — overwhelmingly Muslim men from South Asia and the Middle East — on immigration violations, material-witness warrants, and no charge at all, holding many under a blanket "hold until cleared" policy while the FBI worked through its leads. Names were kept secret, hearings closed, and at Brooklyn's Metropolitan Detention Center detainees were slammed into walls, kept in twenty-three-hour lockdown under constant lighting, and denied counsel for weeks.</p><p>The Justice Department's own Inspector General documented the abuse in a scathing 2003 report and confirmed the central fact: none of the sweep's detainees was convicted of a terrorism offense connected to the attacks. The episode — mass preventive detention organized by nationality and faith — set the war on terror's domestic template, and the Supreme Court later closed the courthouse door on the survivors, holding in Ziglar v. Abbasi (2017) that the officials who designed the policy could not be sued.</p>
HTML,
                    ],
                    [
                        'slug' => 'war-on-terror-material-support',
                        'title' => 'The Material-Support Prosecutions (2001–)',
                        'body' => <<<'HTML'
<p>The material-support statutes — which criminalize providing "support" to designated organizations regardless of intent — became the war on terror's workhorse, and their reach extended far beyond weapons or money. The Holy Land Foundation, once the largest Muslim charity in America, was destroyed for funding zakat committees in Palestine; after a first trial produced no convictions, a 2008 retrial ended with sentences of up to sixty-five years for its leaders, Shukri Abu Baker and Ghassan Elashi, for charity the government conceded fed real orphans. Professor Sami Al-Arian was acquitted or hung on every count in 2005, yet remained imprisoned and under indictment for years before being deported in 2015.</p><p>The statute also reached lawyers: veteran civil-rights attorney Lynne Stewart was convicted in 2005 of passing her imprisoned client's statement to the press, and when an appeals court demanded a harsher sentence, the 70-year-old was resentenced to ten years — dying in 2017 shortly after a compassionate release. In Holder v. Humanitarian Law Project (2010), the Supreme Court confirmed the design: even training a designated group in nonviolence and human-rights law is a crime, making "material support" the broadest speech-and-association offense in modern American law.</p>
HTML,
                    ],
                    [
                        'slug' => 'war-on-terror-entrapment-cases',
                        'title' => 'Preemptive Prosecution & the Entrapment Cases (2004–2023)',
                        'body' => <<<'HTML'
<p>After 2004 the FBI shifted to "preemptive prosecution": informant-driven stings in which the Bureau's own operatives — often paid six figures or working off criminal and immigration troubles — supplied the plan, the money, the rhetoric, and the fake weapons. Studies of the era's hundreds of terrorism prosecutions found that nearly half relied on informants and that in the highest-profile plots, the government itself was the plot's engine. Marginal, impoverished, and sometimes mentally ill defendants received decades under terrorism enhancements for conspiracies that could never have occurred without the sting.</p><p>The Newburgh Four became the emblem: four Black men from a struggling New York town, recruited by informant Shahed Hussain with offers of $250,000, convicted in 2010 of a synagogue-bombing plot the trial judge said the government had "manufactured," adding that the FBI "made them terrorists." In 2023 the same judge ordered three of them released on compassionate grounds, calling the operation a stain on the justice system. The Fort Dix Five, the Liberty City Seven, and dozens of similar cases fill the support lists of Muslim political prisoners — men serving sentences for the government's imagination of their intentions.</p>
HTML,
                    ],
                    [
                        'slug' => 'war-on-terror-guantanamo',
                        'title' => 'Guantánamo & Indefinite Detention (2002–)',
                        'body' => <<<'HTML'
<p>The naval base at Guantánamo Bay was chosen in January 2002 precisely because the administration believed no court's writ ran there. Some 780 Muslim men and boys passed through its cages and cellblocks — the vast majority never charged with anything, many sold to U.S. forces for bounties — subjected to an interrogation regime of stress positions, isolation, and "enhanced" techniques that the Senate later documented as torture. The Supreme Court forced open the courthouse door in Rasul (2004) and Boumediene (2008), affirming the detainees' right to habeas corpus, but review proved no guarantee of release.</p><p>Two decades on, the prison still holds men cleared for transfer by every relevant agency yet detained year after year — indefinite detention without trial as settled American practice, at a cost of more than $500 million a year at its peak. The military commissions have produced a handful of convictions, several later overturned, while the 9/11 case itself remains mired in pretrial litigation over torture. For the political-prisoner tradition, Guantánamo is the war on terror's purest artifact: imprisonment defined not by verdict but by category.</p>
HTML,
                    ],
                    [
                        'slug' => 'war-on-terror-leak-prosecutions',
                        'title' => 'The Whistleblower Prosecutions (2010–2024)',
                        'body' => <<<'HTML'
<p>The Espionage Act, used three times against leakers in the ninety years after 1917, became routine after 2009: the Obama administration brought more leak prosecutions than all predecessors combined, and its successors continued the practice. Chelsea Manning was sentenced in 2013 to thirty-five years — the longest leak sentence in American history — for giving WikiLeaks the Iraq and Afghan war logs and the "Collateral Murder" video; she endured pretrial isolation a UN rapporteur called cruel and inhuman before President Obama commuted the sentence in 2017, and was jailed again in 2019–20 for refusing to testify to a grand jury.</p><p>The docket that followed reads as a roll of prisoners of conscience: John Kiriakou, the CIA officer who confirmed the torture program, thirty months — while no torturer served a day; Jeffrey Sterling, three and a half years; Reality Winner, sixty-three months for one document on Russian election interference; Daniel Hale, forty-five months for revealing the drone program's civilian toll. Because the Act permits no public-interest defense, none could tell a jury why they acted. The Assange case extended the theory to publication itself before ending in a 2024 plea, leaving the question of whether journalism can be espionage formally unresolved — and the whistleblowers' sentences standing as the era's warning.</p>
HTML,
                    ],
                ],
            ],

            'the-green-scare-2005-2010' => [
                'title' => 'The Green Scare (2005–2010)',
                'children' => [
                    [
                        'slug' => 'green-scare-operation-backfire',
                        'title' => 'Operation Backfire (2005–2007)',
                        'body' => <<<'HTML'
<p>In December 2005 the FBI rolled up "the Family," the Earth Liberation Front cell responsible for a string of 1990s and early-2000s arsons against timber companies, wild-horse corrals, and the Vail ski expansion — some $40 million in property damage that had injured no one. The break came through Jake Ferguson, a heroin-addicted participant turned wired informant, and the Bureau leveraged the first arrests into a cascade of cooperation deals. Bill Rodgers, accused as a cell organizer, died by suicide in his Arizona jail cell within weeks of arrest.</p><p>The government branded the case "the No. 1 domestic terrorism priority" — language previously reserved for lethal violence — and sought terrorism sentencing enhancements for arson. Daniel McGowan, who refused to inform on others, received seven years with the enhancement and was assigned to a Communication Management Unit; non-cooperating defendants Jonathan Paul, Nathan Block, and Joanna Zacher drew similar treatment, while cooperators received a fraction. Operation Backfire fixed the Green Scare's rules: property destruction as "terrorism," cooperation as the price of leniency, and the informant as the movement's central vulnerability.</p>
HTML,
                    ],
                    [
                        'slug' => 'green-scare-shac-7-aeta',
                        'title' => 'The SHAC 7 & the Animal Enterprise Terrorism Act (2004–2006)',
                        'body' => <<<'HTML'
<p>Stop Huntingdon Animal Cruelty ran one of the most effective pressure campaigns in movement history, driving investors and suppliers away from the animal-testing giant Huntingdon Life Sciences — largely through a website that reported on protests and posted targets' information. In 2004 the government indicted six organizers and the campaign itself under the Animal Enterprise Protection Act, charging them not with any act of property destruction but with running the website and giving speeches: conspiracy by publication.</p><p>Convicted in 2006, the SHAC 7 received sentences of up to six years — Kevin Kjonaas, Lauren Gazzola, and Jacob Conroy the longest — for what their lawyers argued, unsuccessfully, was First Amendment activity from beginning to end. Congress responded to industry lobbying the same year by expanding the statute into the Animal Enterprise Terrorism Act, criminalizing campaigns that cause businesses "loss of profits" and formally attaching the word "terrorism" to animal-rights advocacy. The case remains the movement's starkest precedent: activists imprisoned as terrorists for a website, while the conduct they publicized was lawful protest.</p>
HTML,
                    ],
                    [
                        'slug' => 'green-scare-eric-mcdavid',
                        'title' => 'Eric McDavid & the Informant "Anna" (2006–2015)',
                        'body' => <<<'HTML'
<p>Eric McDavid was convicted in 2007 of a single count of conspiring to damage federal property — a "plot" with no target finally agreed, no explosives that worked, and no action taken — and sentenced to nearly twenty years, the longest Green Scare sentence imposed. The group's engine was "Anna," an 18-to-20-year-old paid FBI informant who had recruited the participants, driven them, housed them in a cabin the Bureau wired for sound, bought the supplies with FBI money, and pushed the reluctant McDavid — who was romantically fixated on her, a fixation her handlers documented and exploited — to keep going.</p><p>McDavid's jury was never shown the full record. In 2014, FOIA litigation surfaced roughly 2,500 pages the government had withheld, including correspondence between McDavid and Anna that went to the heart of his entrapment defense. Rather than retry the case, prosecutors agreed to his release in January 2015 after nearly nine years in prison, on a plea to a lesser charge. The case became the movement's canonical entrapment story — proof that the era's "eco-terrorism" plots could be government products from recruitment to arrest.</p>
HTML,
                    ],
                    [
                        'slug' => 'green-scare-marius-mason',
                        'title' => 'Marius Mason (2008–2024)',
                        'body' => <<<'HTML'
<p>Marius Mason, an Indiana-born environmental and labor activist, pleaded guilty in 2008 to a 1999 arson at a Michigan State University genetic-engineering lab and to related property actions carried out with the ELF — crimes against property that harmed no one, committed years earlier. Turned in by his ex-husband, who cooperated after his own arrest, and refusing to inform on others, Mason was sentenced in 2009 to nearly twenty-two years with a terrorism enhancement — the longest sentence of any Green Scare defendant, and far beyond the guidelines range the parties had contemplated.</p><p>In prison Mason came out as a transgender man, becoming the first federal prisoner to win access to gender-affirming care in a men's-designated facility only after years of advocacy, much of it from the Carswell administrative unit in Texas — a high-restriction women's unit with conditions likened to the shuttered Lexington HSU. An international June 11th solidarity day carried his case for over a decade until his release from prison in 2024. Mason's sentence remains the movement's benchmark for the Green Scare's asymmetry: twenty-two years for property, while corporate destruction of ecosystems is regulated, fined, and legal.</p>
HTML,
                    ],
                    [
                        'slug' => 'green-scare-communication-management-units',
                        'title' => 'The Communication Management Units (2006–)',
                        'body' => <<<'HTML'
<p>In 2006 and 2008 the Bureau of Prisons quietly opened self-contained isolation units at Terre Haute, Indiana and Marion, Illinois — Communication Management Units, where prisoners' calls, visits, and letters are drastically restricted, monitored live, and barred from all physical contact with family, some seeing children only through glass for years. Created without the legally required public rulemaking, the units held populations that were roughly two-thirds Muslim — the war on terror's defendants — leavened with political cases: Daniel McGowan of Operation Backfire and animal-rights organizer Andrew Stepanian both did time there, and prisoners called the units "Little Guantánamo."</p><p>Documents produced in the Center for Constitutional Rights' lawsuit Aref v. Lynch showed prisoners designated for the units on the basis of their "anti-government" and "extremist" beliefs, protected speech in prison newsletters among the cited grounds; McGowan was returned to a CMU after publishing an article about the units themselves. The litigation won procedural review but left the units standing — a permanent architecture, born in the Green Scare years, for segregating prisoners by ideology and severing them from the movements that support them.</p>
HTML,
                    ],
                ],
            ],

            'occupy-wall-street-2011-2012' => [
                'title' => 'Occupy Wall Street (2011–2012)',
                'children' => [
                    [
                        'slug' => 'occupy-brooklyn-bridge-mass-arrest',
                        'title' => 'The Brooklyn Bridge Mass Arrest (2011)',
                        'body' => <<<'HTML'
<p>Two weeks into the occupation of Zuccotti Park, on October 1, 2011, a march of thousands set out across the Brooklyn Bridge. Police appeared to lead the column onto the vehicle roadway, then stopped it mid-span, unrolled orange netting behind the crowd, and arrested more than 700 people — the largest single mass arrest in the movement's history and among the largest in American protest history, processed for hours in plastic cuffs on a bridge in the rain.</p><p>Nearly all charges were dismissed, and the kettle spawned years of federal litigation over whether marchers had been deliberately trapped; the city ultimately paid settlements rather than test the videos before juries. As with Seattle and A16, the arrests functioned regardless of legal outcome — they defined Occupy's relationship to the NYPD, previewed the eviction to come, and added seven hundred names to the decade's ledger of Americans arrested for walking in the wrong lane of a public bridge.</p>
HTML,
                    ],
                    [
                        'slug' => 'occupy-cecily-mcmillan',
                        'title' => 'Cecily McMillan (2012–2014)',
                        'body' => <<<'HTML'
<p>At the six-month anniversary demonstration in Zuccotti Park in March 2012, organizer Cecily McMillan elbowed the officer who had seized her from behind by the breast, leaving her bruised; she was beaten in the ensuing arrest and suffered a seizure on the pavement. New York charged her — not the officer — with felony assault. At trial the judge excluded most evidence of the officer's documented history of misconduct, and in May 2014 a jury convicted her, though a majority of jurors, learning afterward what sentence she faced, wrote the court urging no prison at all.</p><p>McMillan was sentenced to ninety days on Rikers Island and served fifty-eight — the only Occupy Wall Street defendant imprisoned for protest-related assault, in a movement whose own injuries at police hands produced no prosecutions. She used the platform her case created to testify about conditions facing the women jailed with her at Rikers. Supporters from Pussy Riot to the National Lawyers Guild treated the case as the movement's political trial: the state's need, after evicting the park, for one conviction that recast the policed as the aggressor.</p>
HTML,
                    ],
                    [
                        'slug' => 'occupy-nato-3',
                        'title' => 'The NATO 3 (2012–2014)',
                        'body' => <<<'HTML'
<p>Days before the 2012 NATO summit in Chicago, police raided a Bridgeport apartment and arrested out-of-town Occupy activists Brian Church, Jared Chase, and Brent Betterly, announcing they had foiled a terrorist plot involving Molotov cocktails. The case was the first prosecution under Illinois's post-9/11 state terrorism statute — and it was built by two undercover Chicago police officers, "Mo" and "Gloves," who had embedded in the protest scene, supplied encouragement, and were present when beer bottles became "weapons."</p><p>At trial in 2014 the jury acquitted all three of every terrorism count, convicting only on mob-action and possession charges; jurors and even the sentencing judge scoffed at the terrorism framing, one juror calling the defendants "goofs" rather than terrorists. Sentences of five to eight years still followed. The NATO 3 case marked the arrival of the Green Scare's architecture at street protest: infiltration weeks in advance, terrorism statutes deployed for headline value before a summit, and defendants held on millions in bail while the label — never proven — did its political work.</p>
HTML,
                    ],
                    [
                        'slug' => 'occupy-cleveland-4',
                        'title' => 'The Cleveland 4 (2012)',
                        'body' => <<<'HTML'
<p>On the eve of May Day 2012, the FBI announced it had thwarted an anarchist plot to blow up a Cleveland-area bridge. The five accused were young, unstable, and mostly homeless men from Occupy Cleveland's fringe; the plot's engine was Shaquille Azir, a paid informant with a long fraud record, who had recruited them, employed them, driven them, supplied alcohol, and connected them to an undercover agent selling inert C-4. The Bureau controlled the "explosives" at every moment; the public was never in danger from anyone but the government's own scenario.</p><p>Douglas Wright, Brandon Baxter, and Connor Stevens pleaded guilty and — after prosecutors invoked the terrorism enhancement — received sentences of eight to eleven and a half years, with Joshua Stafford convicted at trial and sentenced to ten. Occupy's national network adopted the four as political prisoners, and their case, paired with the NATO 3, completed the lesson of 2012: the sting playbook refined on Muslim communities after 9/11 had been turned on the domestic left, converting the movement's most vulnerable hangers-on into its terrorism statistics.</p>
HTML,
                    ],
                ],
            ],

            'ferguson-the-movement-for-black-lives-2014-2016' => [
                'title' => 'Ferguson & the Movement for Black Lives (2014–2016)',
                'children' => [
                    [
                        'slug' => 'ferguson-august-uprising',
                        'title' => 'The August Uprising & the Mass Arrests (2014)',
                        'body' => <<<'HTML'
<p>The killing of eighteen-year-old Michael Brown by Officer Darren Wilson on August 9, 2014 brought Ferguson, Missouri into open revolt — and brought armored vehicles, snipers, and tear gas into an American suburb in images that changed the national vocabulary. Police arrested hundreds over the following weeks under a shifting regime of curfews, "keep moving" orders, and a five-second standing rule later held unconstitutional; journalists including the Washington Post's Wesley Lowery and the Huffington Post's Ryan Reilly were arrested in a McDonald's for reporting.</p><p>A second wave followed the November grand-jury announcement declining to indict Wilson. Most charges were eventually dropped, and federal courts enjoined the police tactics one by one, but the Justice Department's 2015 investigation supplied the deeper indictment: Ferguson's courts and police had operated for years as a revenue machine that jailed its Black residents over traffic debt — a municipal system of imprisonment for poverty that the uprising, and the arrests meant to contain it, had finally exposed.</p>
HTML,
                    ],
                    [
                        'slug' => 'ferguson-joshua-williams',
                        'title' => 'Joshua Williams (2014– )',
                        'body' => <<<'HTML'
<p>Josh Williams was nineteen, one of the Ferguson movement's youngest and most visible front-line protesters, when he was arrested in December 2014 for setting a fire at a QuikTrip convenience store during protests in Berkeley, Missouri over the police killing of Antonio Martin. He confessed, pleaded guilty to arson and burglary charges for a fire that hurt no one, and was sentenced to eight years in Missouri state prison — a term activists immediately measured against the probation and suspended sentences routinely given for comparable property offenses without politics attached.</p><p>Williams became the Ferguson uprising's political prisoner: his letters circulated through movement networks, national figures campaigned for his parole through repeated denials, and his case anchored the argument that the state had answered a rebellion against police violence by making an example of a teenager. That an internationally known movement produced its longest sentence for a QuikTrip fire — while the officer whose killing sparked the uprising faced no charges — became, for the movement, the era's arithmetic in miniature.</p>
HTML,
                    ],
                    [
                        'slug' => 'ferguson-jasmine-richards',
                        'title' => 'Jasmine Richards & "Felony Lynching" (2015–2016)',
                        'body' => <<<'HTML'
<p>Jasmine Richards (later Jasmine Abdullah), founder of Black Lives Matter's Pasadena chapter, was convicted in June 2016 of "attempted lynching" — California's statutory term, dating to 1933, for taking a person from police custody — after she tried to pull a young Black woman away from officers at a peace march the previous summer. Governor Jerry Brown had signed a bill striking the word "lynching" from the statute in 2015, after an earlier BLM case drew attention to it, but the offense itself remained, and prosecutors deployed it against the movement's local leadership.</p><p>Richards served roughly a month of a ninety-day sentence, but the case's significance outran its length: she is widely described as the first Black person convicted under the California lynching statute, in a prosecution that turned an anti-mob-violence law born of white lynch mobs against a Black woman organizing against police violence. Movement lawyers cited the case alongside Joshua Williams's as evidence of a deliberate strategy — decapitating chapters by criminalizing their founders — and "felony lynching" entered the movement's lexicon as shorthand for the legal system's capacity for historical irony.</p>
HTML,
                    ],
                    [
                        'slug' => 'ferguson-baltimore-uprising',
                        'title' => 'The Baltimore Uprising (2015)',
                        'body' => <<<'HTML'
<p>Freddie Gray died in April 2015 of a spine injury suffered in a Baltimore police van, and the city rose. During the uprising and the week-long curfew that followed, police arrested nearly five hundred people, holding many for days without charge as courts and jails jammed; public defenders documented arrestees released en masse when no paperwork could be produced. The emblematic defendant was eighteen-year-old Allen Bullock, photographed smashing a police car with a traffic cone: he turned himself in on his parents' advice and was held on $500,000 bail — higher than that set for any of the six officers charged in Gray's death.</p><p>The prosecutions of those officers produced no convictions; the charges were dropped by mid-2016. Bullock, facing life-maximum riot counts, eventually received a suspended sentence and probation after years under the case's weight. Baltimore condensed the era's asymmetry into a single ledger — a dead man, zero officers punished, and the heaviest pretrial detention reserved for a teenager with a traffic cone — and the movement's bail funds, which had cut their teeth in Ferguson, became permanent infrastructure there.</p>
HTML,
                    ],
                ],
            ],

            'standing-rock-the-nodapl-water-protectors-2016-2017' => [
                'title' => 'Standing Rock & the #NoDAPL Water Protectors (2016–2017)',
                'children' => [
                    [
                        'slug' => 'standing-rock-era-mass-arrests',
                        'title' => 'The Mass Arrests & the Treaty Camp Raid (2016–2017)',
                        'body' => <<<'HTML'
<p>The camps that grew along the Cannonball River in 2016 — called by the Standing Rock Sioux to stop the Dakota Access Pipeline's crossing upstream of their water supply — drew thousands from hundreds of Indigenous nations, the largest such gathering in a century. North Dakota's response produced more than 800 criminal cases. The largest single day came on October 27, 2016, when police and National Guard in armored vehicles cleared the 1851 Treaty Camp erected in the pipeline's path, arresting 141 people; arrestees were numbered on their forearms in marker and held in chain-link enclosures they compared to dog kennels.</p><p>The prosecutions strained a rural court system for years: the state charged riot and conspiracy offenses wholesale, judges dismissed cases by the score for lack of evidence, and the acquittal rate at trial ran high. Water protectors with felony convictions — and those who traveled home with open cases across state lines — carried the campaign's legal weight long after the camps were cleared in February 2017 and oil began to flow.</p>
HTML,
                    ],
                    [
                        'slug' => 'standing-rock-era-red-fawn-fallis',
                        'title' => 'Red Fawn Fallis (2016–2020)',
                        'body' => <<<'HTML'
<p>Red Fawn Fallis, an Oglala Lakota organizer known in the camps as a medic and peacemaker, was tackled by officers during the October 27, 2016 Treaty Camp raid; prosecutors alleged a revolver fired twice into the dirt during the struggle. The gun belonged to Heath Harmon — a paid FBI informant who had infiltrated the camps, become Fallis's boyfriend, and by his own account kept the weapon in their shared trailer. The federal case built on that foundation made her the campaign's central political prisoner.</p><p>Facing charges carrying decades and barred from a jury instruction on the informant's role, Fallis pleaded guilty in 2018 to civil disorder and firearm possession and was sentenced to fifty-seven months. Her case traveled internationally — raised by Indigenous delegations at the UN — as the distillation of Standing Rock's policing: an FBI informant's gun, an Indigenous woman's body under a pile of officers, and the only long federal sentence of the movement landing on a medic while the pipeline's private security force, whose dogs had bitten protectors on camera, faced nothing.</p>
HTML,
                    ],
                    [
                        'slug' => 'standing-rock-era-little-feather-rattler',
                        'title' => 'Little Feather & Rattler: The Federal Cases (2017–2020)',
                        'body' => <<<'HTML'
<p>Alongside the state's mass prosecutions, federal prosecutors selected a handful of water protectors for felony civil-disorder and arson-related charges arising from the October 27 raid — among them Michael "Little Feather" Giron, a Chumash man, and Michael "Rattler" Markus, a Lakota Marine Corps veteran, accused in connection with barricade fires set as police advanced on the Treaty Camp. Held without bail and facing fifteen-year maximums with the government signaling terrorism-adjacent theories, both accepted non-cooperating plea agreements — refusing to testify about anyone else — and received thirty-six months each in 2018.</p><p>The water-protector legal collective treated the two men, along with Red Fawn Fallis and fellow federal defendants, as prisoners of the movement, organizing commissary support and letter-writing through their releases in 2020. Their cases defined the federal strategy at Standing Rock: pick a few Indigenous defendants for exemplary punishment, price the risk of trial beyond reach, and let plea agreements write the official history of a day the government itself had turned violent.</p>
HTML,
                    ],
                    [
                        'slug' => 'standing-rock-era-tigerswan',
                        'title' => 'TigerSwan & the Counterinsurgency Campaign (2016–2017)',
                        'body' => <<<'HTML'
<p>Documents leaked to The Intercept in 2017 revealed that Energy Transfer Partners had hired TigerSwan — a private military contractor founded by Delta Force veterans of Iraq and Afghanistan — to run the corporate side of the Standing Rock response as a literal counterinsurgency. Internal situation reports described water protectors as "jihadist" insurgents, proposed exploiting divisions in the camps, ran infiltrators and aerial surveillance, and fed intelligence to law enforcement through fusion-center channels; North Dakota regulators later found the firm had operated in the state without a license.</p><p>The private war matched the public one: the water-cannon operation in sub-freezing temperatures in November 2016, less-lethal munitions that maimed protectors including Sophia Wilansky and Vanessa Dundon, and a seamless information loop between the company and prosecutors. Standing Rock thus became the reference case for the privatization of protest policing — a corporation's military contractor helping to generate the criminal cases against the corporation's opponents — and the template invoked when the same fusion of private security and public prosecution reappeared at Line 3 and Cop City.</p>
HTML,
                    ],
                ],
            ],

            'j20-the-inauguration-day-prosecutions-2017' => [
                'title' => 'J20: The Inauguration Day Prosecutions (2017)',
                'children' => [
                    [
                        'slug' => 'j20-kettle-12th-and-l',
                        'title' => 'The Kettle at 12th & L (January 20, 2017)',
                        'body' => <<<'HTML'
<p>During the anti-capitalist march on the morning of Donald Trump's first inauguration, a minority of participants broke windows along the route through downtown Washington. Rather than arrest individuals, D.C. police boxed the entire march — and everyone swept along with it — into a kettle at 12th and L Streets, holding more than 230 people for hours without food, water, or bathrooms before mass arrest. Journalists, legal observers, medics, and bystanders were taken along with marchers; detainees reported pepper spray, rough handling, and invasive manual searches in processing.</p><p>The kettle itself was familiar from the summit-protest era; what followed was not. Instead of the usual pattern — mass arrest, dropped charges, eventual settlement — federal prosecutors in the U.S. Attorney's office chose to charge virtually everyone in the net with identical felonies, on the theory that presence at the march made each person liable for every window broken by anyone. The District later paid $1.6 million to settle arrestees' civil claims, but by then the criminal cases had already made J20 the era's defining test of collective liability for protest.</p>
HTML,
                    ],
                    [
                        'slug' => 'j20-felony-riot-indictments',
                        'title' => 'The Felony Riot Indictments (2017)',
                        'body' => <<<'HTML'
<p>A grand jury indicted more than 200 of the J20 arrestees on felony rioting counts, and a superseding indictment in April 2017 stacked charges — inciting riot, conspiracy to riot, and multiple property-destruction felonies — until most defendants faced theoretical maximums above seventy years in prison. The government conceded it could not say who broke what; its theory was that wearing black, chanting, or simply marching constituted joining a conspiracy whose every act was chargeable to all. Prosecutors also sought sweeping digital dragnets, subpoenaing data on visitors to a protest website and the contents of organizers' social-media accounts.</p><p>The strategy was unprecedented in scale for an American protest case: not exemplary prosecution of a few, but an attempt to convict an entire demonstration. Defendants organized a collective-defense agreement — hundreds refusing individual plea deals that required testimony against others — betting that solidarity could beat a stacked indictment. Only one defendant, Dane Powell, took an early plea and served prison time; the rest held the line into trial.</p>
HTML,
                    ],
                    [
                        'slug' => 'j20-first-trial-acquittals',
                        'title' => 'The First Trial & the Acquittals (2017)',
                        'body' => <<<'HTML'
<p>The government chose what it considered its strongest hand for the first trial in November 2017: six defendants including a journalist, a medic, and a legal observer, tried on the full felony slate. Prosecutors showed hours of video proving windows had been broken by someone — and virtually nothing connecting these defendants to any act beyond presence. The centerpiece evidence included undercover video from Project Veritas, the right-wing sting outfit, whose selective editing the defense exposed on cross-examination.</p><p>On December 21, 2017, the jury acquitted all six defendants on all counts — a total repudiation, jurors telling reporters the government had never shown individual guilt. The verdict cracked the prosecution's premise: if the state could not convict its best cases, the collective-liability theory could not survive contact with juries. Weeks later prosecutors dropped charges against 129 defendants in a single filing, retreating to a "core" group they still insisted they would convict.</p>
HTML,
                    ],
                    [
                        'slug' => 'j20-collapse-of-the-prosecution',
                        'title' => 'The Collapse of the Prosecution (2018)',
                        'body' => <<<'HTML'
<p>The endgame arrived through prosecutorial misconduct. In the spring 2018 trials, defense lawyers established that the government had withheld dozens of Project Veritas recordings — including material undercutting its own conspiracy narrative — and edited the video it did disclose. The trial judge found Brady violations, sanctioned the prosecution by dismissing conspiracy counts, and the second trial group walked free without a single conviction. On July 6, 2018, the U.S. Attorney's office dismissed the remaining thirty-nine cases, ending the largest protest prosecution in modern American history with a conviction rate, at trial, of zero.</p><p>J20's legacy runs in both directions. The defense victory — built on the no-cooperation pact, movement fundraising, and eighteen months of collective discipline — became the modern handbook for mass-defendant solidarity. But the prosecution had also demonstrated a new state capability: charge everyone, demand decades, and let the process punish regardless of verdict. Defendants spent a year and a half under felony indictment, losing jobs and housing; the office that brought the case answered to no one. Both lessons were carried directly into the George Floyd era.</p>
HTML,
                    ],
                ],
            ],

            'the-george-floyd-uprising-2020' => [
                'title' => 'The George Floyd Uprising (2020)',
                'children' => [
                    [
                        'slug' => 'floyd-uprising-federal-dragnet',
                        'title' => 'The Federal Dragnet (2020–2021)',
                        'body' => <<<'HTML'
<p>The uprising after George Floyd's murder on May 25, 2020 — the broadest protest wave in American history, with participation estimated between 15 and 26 million — met the most aggressive federal response ever mounted against domestic protest. Attorney General William Barr directed U.S. Attorneys and the Joint Terrorism Task Forces to prioritize protest cases, publicly framing the unrest as the work of "antifa" agitators; within months the Justice Department had charged over 300 defendants federally, converting conduct normally handled in state court — arson, curfew violations, gun counts — into federal cases carrying mandatory minimums.</p><p>Researchers who tracked the docket found the "outside agitator" theory empty: defendants were overwhelmingly local, young, and without organizational ties, and prosecutions clustered in districts whose U.S. Attorneys embraced the directive. Federal charging also served pretrial ends — defendants were detained as flight risks and "threats to the community" for offenses their states would have bailed. The dragnet established the George Floyd cases as a distinct cohort of political prisoners, sentenced under a policy explicitly announced as a response to a protest movement.</p>
HTML,
                    ],
                    [
                        'slug' => 'floyd-uprising-rahman-mattis',
                        'title' => 'Rahman & Mattis (2020–2022)',
                        'body' => <<<'HTML'
<p>Urooj Rahman and Colinford Mattis — two young Brooklyn attorneys, a human-rights lawyer and a corporate associate — were arrested in the first hours of New York's protests for tossing a Molotov cocktail into an empty, already-damaged NYPD cruiser, injuring no one. The government's response announced the era: federal charges carrying a 45-year mandatory minimum exposure, pretrial detention fought all the way to the Second Circuit, and prosecutors' insistence on a terrorism-adjacent framing for an act of property damage committed amid a city-wide uprising.</p><p>The case's second act was equally telling. After the change of administrations, the Justice Department permitted a replea to a single count; sentencing judges, weighing two previously spotless lives of public service against the night's recklessness, imposed roughly a year — Mattis twelve months and a day, Rahman fifteen months. For the movement's lawyers the case carried a deliberate message to their profession: the first federal defendants of the uprising were attorneys, chosen, many believed, to warn the bar itself away from the streets.</p>
HTML,
                    ],
                    [
                        'slug' => 'floyd-uprising-minneapolis-arson-sentences',
                        'title' => 'The Minneapolis Arson Sentences (2020–2022)',
                        'body' => <<<'HTML'
<p>The burning of the Minneapolis Third Precinct on May 28, 2020 — the uprising's most iconic act — produced the era's heaviest sentences. Federal arson carries a five-year mandatory minimum, and the men convicted for the precinct fire and the burning of nearby businesses received terms of roughly three to ten years: Dylan Robinson, who was eighteen at the fire, drew four years; Branden Wolfe forty-one months; and Montez Lee ten years for a pawnshop fire in which the remains of Oscar Lee Stewart were later found — a death the government did not charge as homicide but cited at sentencing.</p><p>The Lee case produced a rare artifact: a prosecutor's sentencing memorandum acknowledging that rioting defendants motivated by "a genuine desire to see the police reformed" differ from ordinary arsonists — even as the office sought, and received, a decade. Movement support networks adopted the uprising's imprisoned as political prisoners of the George Floyd rebellion, arguing that the state had answered the largest protest against police violence in its history by imprisoning its participants for years while the system they burned against was reformed hardly at all.</p>
HTML,
                    ],
                    [
                        'slug' => 'floyd-uprising-portland-diligent-valor',
                        'title' => 'Portland: Operation Diligent Valor (2020)',
                        'body' => <<<'HTML'
<p>Portland's nightly protests at the Mark O. Hatfield federal courthouse ran for months, and in July 2020 the administration escalated: Operation Diligent Valor deployed DHS tactical teams — Border Patrol's BORTAC among them — who blanketed neighborhoods in tear gas and, in the episode that defined the operation, seized protesters off streets into unmarked rental vans without identifying themselves. Oregon's governor and Portland's mayor demanded the federal force withdraw; the courthouse deployment instead became the model the president threatened to extend to other "Democrat cities."</p><p>More than a hundred protesters were charged federally in Oregon that summer — civil disorder, assault on officers for shining lasers or throwing water bottles, failure to obey — and prosecutors offered dozens of unusual "deferred resolution" agreements once the political moment passed, with a large share of cases ultimately dismissed. The operation's legacy was less its convictions than its precedent: a federal paramilitary response to local protest over local objection, held together by charges that mostly evaporated — force first, law afterward.</p>
HTML,
                    ],
                ],
            ],

            'the-stop-cop-city-era-2022' => [
                'title' => 'The Stop Cop City Era (2022–)',
                'children' => [
                    [
                        'slug' => 'cop-city-domestic-terrorism-arrests',
                        'title' => 'The Domestic-Terrorism Arrests (2022–2023)',
                        'body' => <<<'HTML'
<p>The movement to stop Atlanta's planned police training center — "Cop City" — and defend the Weelaunee Forest produced the first mass use of a state domestic-terrorism statute against protest in American history. Beginning in December 2022, Georgia charged forest defenders under its domestic-terrorism law, which had been expanded in 2017 to cover property crimes; by mid-2023, forty-two people faced the charge, which carries up to thirty-five years. Arrest warrants cited evidence as thin as mud on shoes, camping in the forest, and possession of a legal-support hotline number written on skin.</p><p>The most sweeping single action came on March 5, 2023, when police raided a music festival in the forest a mile from any property destruction and charged twenty-three attendees — selected, defense lawyers showed, largely for being from out of state — with domestic terrorism. Two years on, the charges had produced no terrorism convictions, but they had done their pretrial work: denied bond or held on ankle monitors, barred from the forest, defendants learned that in Georgia the word "terrorist" now attached to sitting in a tree.</p>
HTML,
                    ],
                    [
                        'slug' => 'cop-city-tortuguita',
                        'title' => 'The Killing of Tortuguita (2023)',
                        'body' => <<<'HTML'
<p>On January 18, 2023, during a multi-agency raid to clear the forest encampments, Georgia State Patrol troopers shot and killed Manuel "Tortuguita" Paez Terán, a 26-year-old Indigenous Venezuelan environmental activist, in their tent. The state said Tortuguita fired first, wounding a trooper; there was no body-camera footage of the shooting itself. The family's independent autopsy found some fifty-seven gunshot wounds and concluded Tortuguita was likely sitting cross-legged with hands raised; the county autopsy documented gunshot residue findings the family's experts said were consistent with that position.</p><p>An investigation controlled by the state ended in October 2023 with a special prosecutor declining all charges against the troopers. Tortuguita became the American environmental movement's first activist killed by police during a forest defense — a death that internationalized the Stop Cop City struggle, drew condemnation from UN human-rights experts, and hardened the movement's conviction that the domestic-terrorism prosecutions and the raid that killed their friend were parts of a single policy: the forest would be cleared, whatever the law had to be made to say.</p>
HTML,
                    ],
                    [
                        'slug' => 'cop-city-rico-61',
                        'title' => 'The RICO 61 (2023–)',
                        'body' => <<<'HTML'
<p>In September 2023, Georgia's attorney general unveiled a 109-page indictment charging sixty-one people under the state's racketeering statute — treating the entire Stop Cop City movement, from tree-sitters to a bail fund to activists who distributed flyers, as a criminal enterprise dating its "conspiracy" to the George Floyd protests of 2020. The indictment cited zine-writing, mutual aid, camping, and reimbursing protesters for glue and food as overt acts; three of the defendants were the legal-observer and bail-fund organizers already charged in the May raid, and legal scholars across the spectrum called it the broadest criminalization of movement infrastructure ever attempted by a state.</p><p>The prosecution moved slowly and roughly: arraignments stretched into 2024, the elected Fulton County district attorney had already stepped aside from the forest cases, and the case's premise — that opposing a police facility is itself racketeering — awaits the appellate courts. For the movement, the RICO 61 completed a trilogy begun with the terrorism arrests and the bail-fund raid: charge the action, then the support structure, then the movement as such. Its defendants remain the era's largest single cohort of American protest prosecutions.</p>
HTML,
                    ],
                    [
                        'slug' => 'cop-city-bail-fund-raid',
                        'title' => 'The Bail-Fund Raid (2023)',
                        'body' => <<<'HTML'
<p>On May 31, 2023, a SWAT team raided an Atlanta house and arrested three organizers of the Atlanta Solidarity Fund — Marlon Kautz, Adele MacLean, and Savannah Patterson — charging them with money laundering and charity fraud for the ordinary operations of a bail fund: posting bond and paying legal expenses for arrested protesters. The warrants cited reimbursements for camping supplies, gasoline, and forest-kitchen food as evidence of a criminal enterprise. A skeptical judge granted bond over the state's objection, remarking that the evidence presented was thin.</p><p>Bail funds had bonded out civil-rights workers since the 1960s without their operators being treated as launderers; the arrests — condemned by more than forty legal organizations and cited by the White House press corps as a First Amendment alarm — marked a new front in protest prosecution: criminalizing the support infrastructure that makes exercising rights survivable. The three were subsequently folded into the RICO indictment. For movements nationwide the message was unmistakable, and the case has become the standing citation for why prisoner support, jail support, and legal defense are themselves now contested political terrain.</p>
HTML,
                    ],
                ],
            ],

            'the-trump-era-crackdown-on-palestine-solidarity-2024' => [
                'title' => 'The Trump-Era Crackdown on Palestine Solidarity (2024–)',
                'children' => [
                    [
                        'slug' => 'palestine-crackdown-encampment-arrests',
                        'title' => 'The Campus Encampments & Mass Arrests (2024)',
                        'body' => <<<'HTML'
<p>The Gaza solidarity encampments of spring 2024 — beginning at Columbia and spreading to well over a hundred campuses — produced upwards of 3,100 arrests in a matter of weeks, the largest wave of student political arrests since 1970. Universities summoned police onto their own campuses at a scale not seen since the Vietnam era: the NYPD's clearance of Columbia's Hamilton Hall on April 30, riot-line sweeps at UCLA that followed a night of unpoliced mob attack on the encampment, and mass zip-tie processions at Emory, UT Austin, Dartmouth, and dozens more, professors and legal observers among those taken.</p><p>Most criminal charges were dropped or reduced within months — Manhattan prosecutors dismissed the bulk of the Columbia cases — but the universities' internal machinery continued where prosecutors stopped: suspensions, expulsions, withheld degrees, and eviction from housing, punishments imposed without the criminal law's protections. The encampment spring established the pattern the following years hardened: the criminal cases would mostly fail, while the administrative and immigration systems — which need not prove anything to a jury — did the movement's lasting punishment.</p>
HTML,
                    ],
                    [
                        'slug' => 'palestine-crackdown-student-detentions',
                        'title' => 'The Student Detentions: Khalil, Öztürk & Mahdawi (2025)',
                        'body' => <<<'HTML'
<p>In March 2025 the federal government began seizing non-citizen students and scholars for their Palestine advocacy. Mahmoud Khalil — a Columbia graduate and encampment negotiator, a lawful permanent resident married to a U.S. citizen — was arrested by ICE in his university housing and flown to a Louisiana detention center, the administration invoking a rarely used Cold War-era provision letting the Secretary of State deport anyone whose presence he deems adverse to foreign policy. Days later, masked plainclothes agents took Tufts doctoral student Rümeysa Öztürk off a Somerville street — video of the arrest circulated worldwide — her sole identified offense an op-ed she had co-authored; Columbia's Mohsen Mahdawi was arrested at his own citizenship interview.</p><p>The courts pushed back through the spring: federal judges ordered Mahdawi released in April, Öztürk in May, and Khalil in June 2025, finding the detentions raised grave First Amendment problems — Khalil had been held over three months, missing his son's birth. But the releases were bail, not vindication: the deportation cases ground on, and the message to every non-citizen in the movement had been delivered by the arrests themselves — the modern Turner and Buford logic, applied to student visas and green cards.</p>
HTML,
                    ],
                    [
                        'slug' => 'palestine-crackdown-ideological-deportation',
                        'title' => 'The Ideological-Deportation Policy & the Courts (2025–)',
                        'body' => <<<'HTML'
<p>The student seizures were policy, not improvisation. Executive orders issued in January 2025 directed agencies to deport non-citizen "Hamas sympathizers," and the State Department paired visa revocations — Secretary Rubio claimed hundreds, saying "we gave you a visa to study, not to become a social activist" — with an AI-assisted "Catch and Revoke" screening of students' social media. The chosen instruments were the foreign-policy deportation ground of the immigration code and the secretary's revocation power: provisions that require no crime, no charge, and historically almost no use, resurrected as a general-purpose tool against a political movement.</p><p>The constitutional test came fast. University associations sued, and in the fall of 2025 a federal judge in Boston — a Reagan appointee — ruled after trial that the administration had run an unconstitutional campaign of "ideological deportation" aimed at chilling pro-Palestinian speech, in one of the most sweeping First Amendment rebukes ever delivered to a sitting administration. Appeals ensure the question will run for years; in the meantime, immigration detention has functioned as what the movement's lawyers call it — political imprisonment through the one legal system where the First Amendment's protection remains contested.</p>
HTML,
                    ],
                    [
                        'slug' => 'palestine-crackdown-designations-funding',
                        'title' => 'Designations, Funding Probes & the State Cases (2024–)',
                        'body' => <<<'HTML'
<p>Beyond the campuses, the crackdown ran through the financial and state-law machinery built by earlier eras. In October 2024 the Treasury Department designated the Samidoun Palestinian Prisoner Solidarity Network — the main international support organization for Palestinian prisoners — as a "sham charity" funding terrorism, freezing its U.S. operations and warning donors; congressional committees demanded universities' and nonprofits' funding records, and legislation advanced to let the executive strip any nonprofit's tax status as "terrorist-supporting." The material-support framework perfected on the Holy Land Five two decades earlier stood ready behind the subpoenas.</p><p>States supplied their own prosecutions: Michigan's attorney general brought felony cases against University of Michigan encampment protesters — only to drop them in May 2025 amid recusal controversy — while Texas, Florida, and others charged demonstrators under riot and trespass statutes and governors ordered expulsions by decree. Individual defendants — from students charged over sit-ins to activists prosecuted for property actions against weapons manufacturers — populate the era's support lists. Taken together, the designations, funding investigations, and scattered state felonies mark the same strategy the movement has faced since 1903: when speech cannot be convicted, punish the infrastructure that carries it.</p>
HTML,
                    ],
                ],
            ],
        ];
    }
}
