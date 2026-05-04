<?php

namespace App\Console\Commands;

use App\Models\Topic;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SeedTopics extends Command
{
    protected $signature = 'topics:seed';
    protected $description = 'Seed the Topics explorer with hierarchical topics covering movements, eras, repressive tools, and prisoner categories.';

    public function handle(): int
    {
        $tree = [
            // ─── MOVEMENTS ─────────────────────────────────────────
            [
                'title' => 'Movements',
                'body'  => "The political prisoners documented in this database came out of organized social movements. From abolitionism in the 1850s to Palestine solidarity in the 2020s, the United States has consistently used its criminal-legal system to neutralize the people who built and led those movements. Browse by movement to see how repression has tracked organizing across two centuries.",
                'children' => [
                    ['title' => 'Black Liberation',
                     'body'  => "From the Black Panther Party and the Black Liberation Army through the New Afrikan Independence Movement, MOVE, and the contemporary Movement for Black Lives, Black liberation organizing in the United States has produced more long-term political prisoners than any other movement. Many entered prison as the direct target of the FBI's COINTELPRO program (1956–1971), and several remain incarcerated today on convictions shaped by that program."],
                    ['title' => 'Indigenous Sovereignty',
                     'body'  => "Indigenous resistance to U.S. settler colonialism has produced political prisoners from the Wounded Knee occupation (1973), the Pine Ridge shootout (1975), and contemporary land defense at Standing Rock and Line 3. Leonard Peltier, the longest-serving Indigenous political prisoner in U.S. history, was finally released to home confinement in February 2025 after 49 years in federal custody."],
                    ['title' => 'Puerto Rican Independence',
                     'body'  => "Since the 1950 Jayuya Uprising, Puerto Rican independentistas have faced sustained federal prosecution — from the Nationalist Party prisoners of the 1950s (Lolita Lebrón, Rafael Cancel Miranda, Andrés Figueroa Cordero, Irvin Flores Rodríguez, Oscar Collazo) through the FALN prisoners of the 1980s (Oscar López Rivera, Carlos Alberto Torres, Juan Segarra Palmer). The campaign to free them has been one of the longest-running prisoner-support efforts in U.S. history."],
                    ['title' => 'Anti-Nuclear Resistance',
                     'body'  => "From the founding of the Plowshares movement in 1980 — when Daniel and Philip Berrigan, Anne Montgomery, and five others entered the GE King of Prussia plant and hammered on Mark 12A nuclear-warhead nose cones — through the Trident, Disarm Now, and Transform Now Plowshares actions, U.S. anti-nuclear activists have served thousands of cumulative years in federal prison for symbolic disarmament actions at nuclear weapons facilities."],
                    ['title' => 'Anti-War Resistance',
                     'body'  => "U.S. anti-war prisoners include World War I draft resisters and Espionage Act defendants (Eugene V. Debs, Bill Haywood, A. Philip Randolph), Vietnam-era resisters (the Catonsville Nine, the Camden 28, Father Daniel Berrigan), Iraq-era military resisters (Ehren Watada, Camilo Mejía, Stephen Funk, Kimberly Rivera), drone-protest line-crossers (Brian Terrell, Kathy Kelly), and contemporary Palestine-solidarity organizers."],
                    ['title' => 'Environmental & Animal Liberation',
                     'body'  => "The post-2000 prosecutions of Earth Liberation Front (ELF) and Animal Liberation Front (ALF) defendants — known collectively as the 'Green Scare' — produced some of the longest sentences ever imposed for property-destruction offenses with no human casualties. The 2006 Animal Enterprise Terrorism Act (AETA) federalized ALF/ELF cases as terrorism. Marius Mason, Daniel McGowan, Jessica Reznicek, and others were sentenced under this framework."],
                    ['title' => 'Anti-Fascism',
                     'body'  => "Anti-fascist organizers face escalating federal and state prosecution — from the Tinley Park Five and Gainesville antifa cases through the post-Charlottesville prosecutions and the 2025 Stop Cop City RICO indictments in Atlanta. The category overlaps heavily with Black Lives Matter prosecutions, with the same activists often charged under both frameworks."],
                    ['title' => 'Palestine Solidarity',
                     'body'  => "Beginning in October 2023 and accelerating after January 2025, U.S. authorities have detained, prosecuted, and deported pro-Palestinian organizers under a new application of immigration and counter-terrorism authorities. International students (Mahmoud Khalil, Rumeysa Öztürk, Badar Khan Suri, Momodou Taal, Ranjani Srinivasan) have been the most visible targets, alongside long-standing Palestine-solidarity defendants like Rasmea Odeh and the Holy Land Foundation Five."],
                    ['title' => 'Labor & Industrial Unionism',
                     'body'  => "From the 1886 Haymarket prosecutions through the 1917 IWW mass trial in Chicago and the Sacco-Vanzetti and Tom Mooney frame-ups, U.S. labor organizers have been imprisoned under sedition, syndicalism, and conspiracy statutes specifically designed to neutralize their organizations. The legal architecture built to crush the IWW remains in force."],
                    ['title' => 'Prison Abolition',
                     'body'  => "Prison-abolitionist political prisoners include those imprisoned for organizing inside (Sean Swain, Kevin 'Rashid' Johnson, the Pendleton 2, Joe-Joe Bowen) and those imprisoned for defending people inside (the Free Alabama Movement, the August 21 / 9 movements). Many are serving sentences that were directly extended in retaliation for hunger strikes, work strikes, and writing from inside."],
                    ['title' => 'LGBTQ+ Liberation',
                     'body'  => "LGBTQ+ political prisoners include those imprisoned for queer self-defense (Luke O'Donovan), trans organizers prosecuted under enhanced terrorism statutes (Marius Mason), and ACT UP / AIDS Coalition civil-disobedience defendants. Trans prisoners face systematic mistreatment within both BOP and state custody, including being held in facilities incongruent with their gender identity."],
                ],
            ],

            // ─── ERAS ──────────────────────────────────────────────
            [
                'title' => 'Eras',
                'body'  => "Repression in the United States arrives in waves, and each wave shapes a generation of political prisoners. The period that produced a person's case usually predicts the framework used to prosecute them — the same conspiracy and seditious-libel doctrine that imprisoned Wobblies in 1918 was reapplied to Black Panthers in 1969, Plowshares activists in 1981, ALF defendants in 2006, and Stop Cop City defendants in 2024.",
                'children' => [
                    ['title' => 'The First Red Scare (1917–1920)',
                     'body'  => "Triggered by U.S. entry into World War I and the Russian Revolution, the First Red Scare federalized political prosecution through the Espionage Act of 1917 and the Sedition Act of 1918. The 1917 IWW mass trial in Chicago, the prosecutions of Eugene V. Debs and Victor L. Berger, the November 1919–January 1920 Palmer Raids, and the deportation of Emma Goldman and Alexander Berkman to Soviet Russia all came out of this period."],
                    ['title' => 'McCarthyism (1947–1957)',
                     'body'  => "The Smith Act of 1940 was used to prosecute Communist Party USA leadership in waves of trials beginning in 1949. The Rosenberg execution (1953), the imprisonment of W.E.B. Du Bois, Paul Robeson's passport revocation, and the Hollywood Ten contempt-of-Congress prosecutions defined the era. Several Smith Act prisoners served time at the U.S. Penitentiary in Terre Haute, Indiana."],
                    ['title' => 'COINTELPRO (1956–1971)',
                     'body'  => "The FBI's Counterintelligence Program targeted civil-rights organizations, the Black Panther Party, the American Indian Movement, the Puerto Rican independence movement, and the New Left through illegal surveillance, infiltration, manufactured evidence, and coordinated prosecutions. Many of the convictions COINTELPRO produced are still being served fifty years later — including those of Mumia Abu-Jamal, Sundiata Acoli, Veronza Bowers, and Ed Poindexter."],
                    ['title' => 'The Vietnam War Era (1964–1975)',
                     'body'  => "The Vietnam War produced a generation of draft resisters, military deserters, and civil-disobedience defendants. The Catonsville Nine (1968), the Camden 28 (1971), the Pentagon Papers prosecutions of Daniel Ellsberg and Anthony Russo (1971–1973), the Chicago Eight conspiracy trial (1968–1970), and the Wilmington Ten (1971) all date to this period."],
                    ['title' => 'The Reagan Era (1981–1989)',
                     'body'  => "The Reagan years saw the federal terrorism prosecutions of FALN and Macheteros members, the 1981 Brink's robbery prosecutions of Black Liberation Army and Weather Underground veterans (Mutulu Shakur, Marilyn Buck, David Gilbert), the founding of SOA Watch and the Plowshares trials, and the 1985 Philadelphia police bombing of MOVE."],
                    ['title' => 'The War on Terror (2001–)',
                     'body'  => "Post-9/11 federal counterterrorism prosecutions produced new tools — the PATRIOT Act, material support statutes, the Special Administrative Measures (SAMs), and the Communications Management Units. Most defendants prosecuted under this framework were Muslim, and many were caught in FBI sting operations that critics characterize as entrapment. The Holy Land Foundation Five, the Newburgh Four, the Liberty City Seven, and the Lackawanna Six all came out of this period."],
                    ['title' => 'The Green Scare (2005–2010)',
                     'body'  => "Operation Backfire (2005–2006) targeted the Earth Liberation Front and Animal Liberation Front through cooperator-driven federal prosecutions. The 2006 Animal Enterprise Terrorism Act (AETA) federalized animal-rights organizing as terrorism. Daniel McGowan, Marius Mason, Jeff Luers, Tre Arrow, and the AETA Four came out of this period."],
                    ['title' => 'The George Floyd Uprising (2020)',
                     'body'  => "The summer-2020 Black Lives Matter protests following the police murder of George Floyd produced hundreds of state and federal prosecutions for arson, riot, civil disorder, and federal explosives offenses. Many defendants are still serving multi-year sentences (José Felan, Margaret Channon, Montez Lee, David Elmakayes, Brandon Wolfe, Matthew White, Ellie Brett)."],
                    ['title' => 'The Stop Cop City Era (2022–)',
                     'body'  => "The campaign against the Atlanta Public Safety Training Center ('Cop City') produced the most aggressive use of state RICO and domestic-terrorism statutes against environmental and racial-justice organizers in U.S. history. Tortuguita was killed by Georgia state troopers on January 18, 2023; the 61 RICO defendants face decades-long sentences for forest-defense and bail-fund organizing."],
                    ['title' => 'The Trump-Era Crackdown on Palestine Solidarity (2024–)',
                     'body'  => "Beginning with the campus encampment crackdown in spring 2024 and accelerating after January 2025, U.S. authorities have used immigration enforcement, visa revocation, and ICE administrative detention to remove pro-Palestinian organizers — without filing criminal charges in most cases. Mahmoud Khalil, Rumeysa Öztürk, Badar Khan Suri, Momodou Taal, Ranjani Srinivasan, and Lelo (Alfredo Juarez) are among those caught up in this campaign."],
                ],
            ],

            // ─── REPRESSIVE TOOLS ──────────────────────────────────
            [
                'title' => 'Repressive Tools',
                'body'  => "The legal and administrative machinery used to imprison political dissidents has evolved over two centuries, but its components recur with surprising consistency. Conspiracy doctrine, sedition and seditious-libel statutes, immigration removal, racketeering and 'enterprise' statutes, and administrative detention are the recurring tools — often deployed against new targets using language and statutory architecture inherited from earlier waves of repression.",
                'children' => [
                    ['title' => 'The Espionage Act of 1917',
                     'body'  => "Signed by Woodrow Wilson during World War I, the Espionage Act criminalizes 'willfully' obstructing recruitment or causing 'insubordination, disloyalty, mutiny, or refusal of duty' in the armed forces. It was used to imprison Eugene V. Debs (1918), the IWW leadership (1918), and ~2,000 anti-war organizers in 1917–1919. Since 2008 it has been the primary statute used against whistleblowers — Chelsea Manning, Edward Snowden, Reality Winner, Daniel Hale, Jeffrey Sterling, John Kiriakou, and Joshua Schulte."],
                    ['title' => 'The Sedition Act of 1918',
                     'body'  => "Amended the Espionage Act to also prohibit 'disloyal, profane, scurrilous, or abusive' language about the United States government, the flag, or the armed forces. It was used to imprison hundreds of socialists, anarchists, and labor organizers between 1918 and its repeal in 1920. The architectural precedent it set — criminalizing speech alone, not just specific acts — was invoked again by COINTELPRO and the post-9/11 material-support statutes."],
                    ['title' => 'The Smith Act (1940)',
                     'body'  => "Made it a federal crime to advocate the overthrow of the U.S. government or to organize or be a member of any group advocating such overthrow. It was used in the late-1940s and 1950s to prosecute the entire leadership of the Communist Party USA, sending dozens of organizers to federal prison. Its reach was finally narrowed by the Supreme Court in Yates v. United States (1957) and Scales v. United States (1961), but it has never been repealed."],
                    ['title' => 'COINTELPRO',
                     'body'  => "The FBI's Counterintelligence Program (1956–1971) was an internal initiative that used illegal wiretaps, infiltration, agents provocateurs, manufactured evidence, and coordinated prosecutions to neutralize political movements. After it was exposed in the 1971 Media, PA office break-in and the 1975 Church Committee hearings, the FBI publicly disavowed it — but the convictions it produced were never reopened. Many are still being served."],
                    ['title' => 'The Animal Enterprise Terrorism Act (AETA, 2006)',
                     'body'  => "Federal statute that turned property damage and protest activity targeting animal-research facilities and factory farms into federal terrorism offenses. It expanded the older Animal Enterprise Protection Act of 1992 to include actions causing 'economic damage' — including secondary protests at executives' homes and the publication of corporate addresses. The AETA Four, Marius Mason, Daniel McGowan, and others were prosecuted under this framework."],
                    ['title' => 'The PATRIOT Act (2001)',
                     'body'  => "Passed six weeks after September 11, 2001, the PATRIOT Act expanded federal authorities to surveil, detain, and prosecute people in connection with 'terrorism' broadly defined. Its material-support statute (18 U.S.C. § 2339A and § 2339B) has been the central legal tool in post-9/11 political-prisoner prosecutions, including the Holy Land Foundation Five, the Newburgh Four, and the Lackawanna Six."],
                    ['title' => 'Special Administrative Measures (SAMs)',
                     'body'  => "Administrative orders issued by the U.S. Attorney General that severely restrict a federal prisoner's communication with the outside world — limiting mail, phone calls, and visits. SAMs were imposed on Lynne Stewart, Daniel Hale, the Holy Land Foundation prisoners, and others. They are renewable annually and have been imposed on prisoners for over a decade."],
                    ['title' => 'Communications Management Units (CMUs)',
                     'body'  => "Special federal prison housing units, currently at FCI Terre Haute (Indiana) and FCI Marion (Illinois), where prisoners' contact with the outside world is severely restricted. Mail is read and copied before delivery; phone calls are limited to a few minutes per week and conducted in English. CMUs were created in 2006 without public notice or rulemaking and disproportionately house Muslim and political prisoners."],
                    ['title' => 'ADX Florence Supermax',
                     'body'  => "The U.S. Penitentiary Administrative Maximum Facility in Florence, Colorado is the federal supermax — the highest-security prison in the federal system. Prisoners are held in single cells for 22–23 hours per day with extreme communication restrictions. Political prisoners held at ADX have included H. Rap Brown / Jamil al-Amin, Russell Maroon Shoatz, and Ramzi Yousef."],
                    ['title' => 'Solitary Confinement',
                     'body'  => "The use of long-term solitary confinement — what the United Nations classifies as torture beyond 15 days — is endemic in U.S. prisons and has been used as a particular tool against political organizers inside. Albert Woodfox spent 43 years in solitary at Angola; Russell 'Maroon' Shoatz spent 22; the Pelican Bay hunger strikes (2011, 2013) drew national attention to California's use of indefinite solitary."],
                    ['title' => 'The Federal Terrorism Enhancement (USSG §3A1.4)',
                     'body'  => "A federal sentencing-guideline enhancement that, when applied, raises a defendant's offense level by 12 points and treats them as having a Category VI criminal history regardless of actual record. It has been routinely applied to AETA defendants, Stop Cop City defendants, and Plowshares-era environmental defendants — producing sentences several times longer than non-political defendants convicted of similar conduct."],
                    ['title' => 'Immigration Enforcement as Political Tool',
                     'body'  => "ICE detention and deportation have been used since at least the 1919 Palmer Raids — which deported 249 anarchists including Emma Goldman to Soviet Russia — as a workaround for criminal-prosecution requirements. The 2024–2025 detentions of pro-Palestinian student organizers under visa-revocation authority (Mahmoud Khalil, Rumeysa Öztürk, Badar Khan Suri) follow this pattern, allowing the government to remove organizers without proving any criminal offense."],
                ],
            ],

            // ─── CATEGORIES OF PRISONERS ───────────────────────────
            [
                'title' => 'Categories',
                'body'  => "Cross-cutting groupings that don't map neatly to a single movement or era. Some prisoners belong to several of these at once — a whistleblower may also be a war resister, a political prisoner in exile may also be a queer self-defense defendant.",
                'children' => [
                    ['title' => 'Whistleblowers',
                     'body'  => "Government employees and contractors who disclosed classified or otherwise restricted information in the public interest and were prosecuted, primarily under the Espionage Act. Includes Daniel Ellsberg, Anthony Russo, Chelsea Manning, Edward Snowden, Reality Winner, Daniel Hale, Jeffrey Sterling, John Kiriakou, Joshua Schulte, and Charles Littlejohn."],
                    ['title' => 'Conscientious Objectors & Military Resisters',
                     'body'  => "Service members who refused deployment, deserted, or otherwise resisted participation in U.S. wars. From World War I draft resisters and Vietnam-era objectors through Iraq War resisters Ehren Watada, Camilo Mejía, Stephen Funk, Kimberly Rivera, Jeremy Hinzman, and Chelsea Manning. Many faced prosecution by court-martial under the Uniform Code of Military Justice rather than civilian criminal law."],
                    ['title' => 'Civil Disobedience & Plowshares',
                     'body'  => "Prisoners convicted of nonviolent direct action — line-crossings, lock-downs, banner drops, symbolic disarmament. Includes the Catonsville Nine, the Camden 28, the Plowshares Eight (1980) and every subsequent Plowshares action, the SOA Watch line-crossers, the Trident Ploughshares defendants, and contemporary climate-defense actions."],
                    ['title' => 'Political Prisoners in Exile',
                     'body'  => "U.S. nationals (or permanent residents) who fled the United States to escape prosecution and were granted political asylum elsewhere. Includes Assata Shakur (Cuba, since 1984), Ishmael LaBeet / Ismail Ali (Cuba, since 1985), William Lee Brent (Cuba, 1969–2006), Edward Snowden (Russia, since 2013), John Anthony Robles (Russia, since 1995), John Dougan (Russia, since 2015), Barry Cooper (Mexico, since 2011), Gavin Seim (Mexico, since 2017), and Bill Haywood (Soviet Russia, 1921–1928)."],
                    ['title' => 'Death-Row Political Prisoners',
                     'body'  => "Political prisoners who were sentenced to death and either had the sentence overturned, commuted, or carried out. Includes Mumia Abu-Jamal (death sentence 1982; commuted to life in 2011), Sacco and Vanzetti (executed 1927), the Rosenbergs (executed 1953), Joe Hill (executed 1915), and Albert Parsons / August Spies / Adolph Fischer / George Engel (Haymarket martyrs, executed 1887)."],
                    ['title' => 'Hunger Strikers',
                     'body'  => "Political prisoners who used hunger strikes as a tool of resistance from inside. Includes the Pelican Bay strikes (2011, 2013), the 1981 Irish hunger strikes' U.S. solidarity strikes by Plowshares prisoners, Joe-Joe Bowen's solitary-confinement strikes, the August 21 / Free Alabama Movement strikes (2018), and the 2025 strikes by Stop Cop City and Palestine-solidarity defendants."],
                    ['title' => 'Wrongfully Convicted / COINTELPRO Frame-Ups',
                     'body'  => "Political prisoners whose convictions rested on evidence later shown to have been fabricated, coerced, or hidden by the FBI or local police as part of programs of political repression. Includes Geronimo ji Jaga Pratt (released 1997 after 27 years; conviction overturned), Romaine 'Chip' Fitzgerald, the New York 21, the Panther 21, and many of the long-term Black Panther political prisoners still incarcerated today."],
                    ['title' => 'Recently Released Political Prisoners',
                     'body'  => "Political prisoners released within the past decade through clemency, parole, sentence completion, or compassionate release. Includes Leonard Peltier (released to home confinement February 2025), Russell Maroon Shoatz (released October 2021, died December 2021), Mutulu Shakur (compassionate release December 2022, died July 2023), Sundiata Acoli (parole December 2022), Romaine 'Chip' Fitzgerald (parole 2023), Oscar López Rivera (commutation by Obama 2017), Chelsea Manning (commutation by Obama 2017)."],
                    ['title' => 'Elder Political Prisoners',
                     'body'  => "Political prisoners who have served thirty, forty, or more than fifty years. Many are now in their seventies and eighties, with serious chronic-health needs that prison medical care fails to meet. Includes Mumia Abu-Jamal (44+ years), Veronza Bowers (50+), Joe-Joe Bowen, Ed Poindexter, the Fountain Valley 5 (53+ years), Omar Askia Ali / Edward Sistrunk (55+ years), and Larry Hoover."],
                ],
            ],
        ];

        $createdRoot = 0;
        $createdChild = 0;
        $skipped = 0;
        $rootSort = 0;

        foreach ($tree as $rootDef) {
            $rootSort += 10;
            $rootSlug = Str::slug($rootDef['title']);

            $root = Topic::where('slug', $rootSlug)->first();
            if ($root) {
                $this->line("Root exists: {$rootDef['title']}");
                $skipped++;
            } else {
                $root = Topic::create([
                    'title'      => $rootDef['title'],
                    'body'       => $rootDef['body'],
                    'parent_id'  => null,
                    'sort_order' => $rootSort,
                    'published'  => true,
                ]);
                $this->info("Added root: {$root->title}");
                $createdRoot++;
            }

            $childSort = 0;
            foreach ($rootDef['children'] as $childDef) {
                $childSort += 10;
                $childSlug = Str::slug($childDef['title']);

                if (Topic::where('slug', $childSlug)->exists()) {
                    $this->line("  Child exists: {$childDef['title']}");
                    $skipped++;
                    continue;
                }

                Topic::create([
                    'title'      => $childDef['title'],
                    'body'       => $childDef['body'],
                    'parent_id'  => $root->id,
                    'sort_order' => $childSort,
                    'published'  => true,
                ]);
                $this->info("  Added child: {$childDef['title']}");
                $createdChild++;
            }
        }

        $this->info("\nDone. Roots created: {$createdRoot}, children created: {$createdChild}, skipped: {$skipped}.");

        return self::SUCCESS;
    }
}
