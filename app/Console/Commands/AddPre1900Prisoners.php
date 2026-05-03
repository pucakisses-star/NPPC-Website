<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddPre1900Prisoners extends Command
{
    protected $signature = 'prisoners:add-pre-1900';
    protected $description = 'Add pre-1900 historical political prisoners: John Brown and his raiders (1859), the Haymarket Martyrs (1886-87), and Clement Vallandigham (1863).';

    public function handle(): int
    {
        $created = 0;
        $skipped = 0;

        $charlesTown = Institution::firstOrCreate(
            ['name' => 'Charles Town Jail (Jefferson County)'],
            ['city' => 'Charles Town', 'state' => 'Virginia']
        );

        $cookCountyJail = Institution::firstOrCreate(
            ['name' => 'Cook County Jail'],
            ['city' => 'Chicago', 'state' => 'Illinois']
        );

        $joliet = Institution::firstOrCreate(
            ['name' => 'Illinois State Penitentiary (Joliet)'],
            ['city' => 'Joliet', 'state' => 'Illinois']
        );

        $kemper = Institution::firstOrCreate(
            ['name' => 'Kemper Barracks military prison'],
            ['city' => 'Cincinnati', 'state' => 'Ohio']
        );

        // ─────────────────────────────────────────────────────────
        // John Brown and the Harpers Ferry raiders, 1859–1860
        // ─────────────────────────────────────────────────────────

        $harpersFerry = "On October 16-18, 1859, the abolitionist John Brown led 21 men in an attempted raid on the federal armory at Harpers Ferry, Virginia (now West Virginia), aiming to arm enslaved people and ignite a general uprising against slavery. The raid was suppressed within 36 hours by U.S. Marines under Robert E. Lee. Ten raiders were killed, seven were captured, and five escaped. The captured raiders were tried by the Commonwealth of Virginia for treason against the state, murder, and conspiring with slaves to rebel — all crimes for which they were sentenced to death by hanging at Charles Town. Their executions made them, in the months before the Civil War, the most consequential political prisoners in American history.";

        $brownRaiders = [
            [
                'name'         => 'John Brown',
                'first_name'   => 'John',
                'last_name'    => 'Brown',
                'birthdate'    => '1800-05-09',
                'death_date'   => '1859-12-02',
                'gender'       => 'Male',
                'state'        => 'New York',
                'description'  => "John Brown was an American abolitionist whose conviction that slavery could only be ended by armed force placed him decades ahead of mainstream antislavery politics. After leading the antislavery militia in the Bleeding Kansas conflict of 1856, he spent three years planning the raid on Harpers Ferry as the opening act of a war of liberation. After the raid failed he was tried in Charles Town in October 1859, convicted of treason against Virginia, murder, and conspiring with enslaved people to rebel, and hanged on December 2, 1859. His final written statement read, in full: 'I, John Brown, am now quite certain that the crimes of this guilty land will never be purged away but with blood.' His execution was widely regarded, even by Brown's critics, as one of the proximate causes of the Civil War.",
                'arrest'       => '1859-10-18',
                'release'      => null,
                'death_in_custody' => '1859-12-02',
                'sentence'     => 'Death by hanging — executed December 2, 1859',
            ],
            [
                'name'         => 'Aaron Stevens',
                'first_name'   => 'Aaron',
                'middle_name'  => 'Dwight',
                'last_name'    => 'Stevens',
                'birthdate'    => '1831-03-15',
                'death_date'   => '1860-03-16',
                'gender'       => 'Male',
                'state'        => 'Connecticut',
                'description'  => "Aaron Dwight Stevens was a Mexican–American War veteran and one of John Brown's most experienced lieutenants. He had previously served a death sentence (commuted) at Fort Leavenworth for striking a superior officer who was abusing a Mexican civilian, and escaped to join Brown in Kansas. Severely wounded during the raid, he was captured, tried separately because his case had been transferred to federal court, and ultimately hanged at Charles Town on March 16, 1860 alongside Albert Hazlett.",
                'arrest'       => '1859-10-18',
                'death_in_custody' => '1860-03-16',
                'sentence'     => 'Death by hanging — executed March 16, 1860',
            ],
            [
                'name'         => 'John E. Cook',
                'first_name'   => 'John',
                'middle_name'  => 'Edwin',
                'last_name'    => 'Cook',
                'birthdate'    => '1830-05-02',
                'death_date'   => '1859-12-16',
                'gender'       => 'Male',
                'state'        => 'Connecticut',
                'description'  => "John Edwin Cook was a Yale-educated abolitionist who served as Brown's principal advance scout, living in Harpers Ferry under cover for more than a year before the raid. He escaped in the immediate aftermath but was captured in Pennsylvania, returned to Virginia, and hanged on December 16, 1859.",
                'arrest'       => '1859-10-26',
                'death_in_custody' => '1859-12-16',
                'sentence'     => 'Death by hanging — executed December 16, 1859',
            ],
            [
                'name'         => 'John Anthony Copeland Jr.',
                'first_name'   => 'John',
                'middle_name'  => 'Anthony',
                'last_name'    => 'Copeland Jr.',
                'birthdate'    => '1834-08-15',
                'death_date'   => '1859-12-16',
                'gender'       => 'Male',
                'race'         => 'Black',
                'state'        => 'Ohio',
                'description'  => "John Anthony Copeland Jr. was a free Black abolitionist from Oberlin, Ohio, where he had been involved in the 1858 Oberlin–Wellington Rescue of a recaptured fugitive. He joined Brown's force as one of five Black raiders. Captured at the rifle works during the raid, he was tried separately under Virginia law (which denied Black defendants the same procedural protections as white defendants) and hanged on the morning of December 16, 1859. His final letter to his family is one of the most widely cited statements of antislavery resolve from the era.",
                'arrest'       => '1859-10-17',
                'death_in_custody' => '1859-12-16',
                'sentence'     => 'Death by hanging — executed December 16, 1859',
            ],
            [
                'name'         => 'Shields Green',
                'first_name'   => 'Shields',
                'last_name'    => 'Green',
                'birthdate'    => '1836-01-01', // approximate, exact unknown
                'death_date'   => '1859-12-16',
                'gender'       => 'Male',
                'race'         => 'Black',
                'state'        => 'New York',
                'description'  => "Shields Green was a fugitive from slavery in South Carolina who had escaped to the North and become close to Frederick Douglass. When Brown met with Douglass shortly before the raid to make a final plea for Douglass to join, Douglass declined; Green answered, 'I b'lieve I'll go with the old man,' and traveled with Brown to Harpers Ferry. He was captured during the raid, tried under Virginia law for treason, and hanged on the morning of December 16, 1859.",
                'arrest'       => '1859-10-18',
                'death_in_custody' => '1859-12-16',
                'sentence'     => 'Death by hanging — executed December 16, 1859',
            ],
            [
                'name'         => 'Edwin Coppoc',
                'first_name'   => 'Edwin',
                'last_name'    => 'Coppoc',
                'birthdate'    => '1835-06-30',
                'death_date'   => '1859-12-16',
                'gender'       => 'Male',
                'state'        => 'Iowa',
                'description'  => "Edwin Coppoc was a young Quaker from Springdale, Iowa, who joined Brown's force after meeting him there in 1857. He was captured inside the engine house, tried for treason and murder, and hanged in the afternoon of December 16, 1859, after attempting an escape from the Charles Town jail with John Cook the night before.",
                'arrest'       => '1859-10-18',
                'death_in_custody' => '1859-12-16',
                'sentence'     => 'Death by hanging — executed December 16, 1859',
            ],
            [
                'name'         => 'Albert Hazlett',
                'first_name'   => 'Albert',
                'last_name'    => 'Hazlett',
                'birthdate'    => '1837-09-21',
                'death_date'   => '1860-03-16',
                'gender'       => 'Male',
                'state'        => 'Pennsylvania',
                'description'  => "Albert Hazlett was a Pennsylvania-born veteran of Brown's Kansas militia who escaped Harpers Ferry but was captured in Pennsylvania days later. He used the alias William Harrison and refused to admit his identity, but was extradited to Virginia, convicted, and hanged at Charles Town on March 16, 1860, alongside Aaron Stevens.",
                'arrest'       => '1859-10-22',
                'death_in_custody' => '1860-03-16',
                'sentence'     => 'Death by hanging — executed March 16, 1860',
            ],
        ];

        foreach ($brownRaiders as $r) {
            DB::transaction(function () use ($r, $charlesTown, $harpersFerry, &$created, &$skipped) {
                if (Prisoner::where('name', $r['name'])->exists()) {
                    $this->warn("Skipping {$r['name']} — already exists.");
                    $skipped++;
                    return;
                }

                $prisoner = Prisoner::create(array_filter([
                    'name'        => $r['name'],
                    'first_name'  => $r['first_name'],
                    'middle_name' => $r['middle_name'] ?? null,
                    'last_name'   => $r['last_name'],
                    'birthdate'   => $r['birthdate'],
                    'death_date'  => $r['death_date'],
                    'gender'      => $r['gender'],
                    'race'        => $r['race'] ?? null,
                    'state'       => $r['state'],
                    'description' => $r['description']."\n\n".$harpersFerry,
                    'era'         => 'Antebellum',
                    'ideologies'  => ['Abolitionist'],
                    'in_custody'  => false,
                    'released'    => false,
                ], fn ($v) => $v !== null));

                PrisonerCase::create([
                    'prisoner_id'           => $prisoner->id,
                    'institution_id'        => $charlesTown->id,
                    'charges'               => 'Treason against the Commonwealth of Virginia; murder; conspiring with enslaved people to rebel',
                    'arrest_date'           => $r['arrest'],
                    'death_in_custody_date' => $r['death_in_custody'],
                    'convicted'             => 'Yes — Commonwealth of Virginia, 1859',
                    'sentence'              => $r['sentence'],
                ]);

                $this->info("Added {$prisoner->name}");
                $created++;
            });
        }

        // ─────────────────────────────────────────────────────────
        // The Haymarket Martyrs, 1886–1887
        // ─────────────────────────────────────────────────────────

        $haymarket = "On May 4, 1886, a rally in Chicago's Haymarket Square in support of an eight-hour workday and against the police killing of striking workers at the McCormick Reaper plant the previous day was nearing its end when an unknown assailant threw a bomb into a column of police advancing to disperse the crowd. The explosion and the police gunfire that followed killed seven officers and at least four civilians. Eight Chicago anarchist organizers were arrested and tried together for the bombing, although no evidence ever connected most of them to the device or the bomb-thrower. The 1886 trial, presided over by Judge Joseph Gary, was widely condemned at the time and since as a political prosecution. Four were hanged on November 11, 1887; one died in his cell the night before the executions. Three years later, on June 26, 1893, Illinois Governor John Peter Altgeld pardoned the three surviving prisoners and issued a long public statement excoriating the trial as a miscarriage of justice. The case helped establish May 1 as International Workers' Day around the world.";

        $haymarketDefendants = [
            [
                'name'         => 'August Spies',
                'first_name'   => 'August',
                'last_name'    => 'Spies',
                'birthdate'    => '1855-12-10',
                'death_date'   => '1887-11-11',
                'description'  => "August Spies was a German-born anarchist labor organizer and editor of the Chicago Arbeiter-Zeitung, the leading German-language anarchist newspaper in the United States. He had spoken at the McCormick rally on May 3 and convened the Haymarket meeting the following day. Hanged on November 11, 1887, his last words from the gallows — 'There will come a time when our silence will be more powerful than the voices you strangle today' — became a labor-movement touchstone.",
                'institution'  => 'cook',
                'sentence'     => 'Death by hanging — executed November 11, 1887',
                'death'        => '1887-11-11',
            ],
            [
                'name'         => 'Albert Parsons',
                'first_name'   => 'Albert',
                'middle_name'  => 'Richard',
                'last_name'    => 'Parsons',
                'birthdate'    => '1848-06-20',
                'death_date'   => '1887-11-11',
                'description'  => "Albert Parsons was a former Confederate soldier turned radical labor organizer and editor of The Alarm, an English-language anarchist paper in Chicago. Although he had left town immediately after the Haymarket meeting, he voluntarily surrendered to stand trial with his comrades. He was hanged on November 11, 1887. His widow Lucy Parsons would go on to become one of the most influential anarchist organizers in American history.",
                'institution'  => 'cook',
                'sentence'     => 'Death by hanging — executed November 11, 1887',
                'death'        => '1887-11-11',
            ],
            [
                'name'         => 'Adolph Fischer',
                'first_name'   => 'Adolph',
                'last_name'    => 'Fischer',
                'birthdate'    => '1858-01-01',
                'death_date'   => '1887-11-11',
                'description'  => "Adolph Fischer was a German-born typesetter at the Arbeiter-Zeitung and a member of the International Working People's Association. He had printed the handbill calling for the May 4 Haymarket rally. Hanged on November 11, 1887.",
                'institution'  => 'cook',
                'sentence'     => 'Death by hanging — executed November 11, 1887',
                'death'        => '1887-11-11',
            ],
            [
                'name'         => 'George Engel',
                'first_name'   => 'George',
                'last_name'    => 'Engel',
                'birthdate'    => '1836-04-15',
                'death_date'   => '1887-11-11',
                'description'  => "George Engel was a German-born printer and one of the older Chicago anarchists. He was not even at Haymarket Square on May 4 — he was at home playing cards — but was nonetheless tried and hanged on November 11, 1887.",
                'institution'  => 'cook',
                'sentence'     => 'Death by hanging — executed November 11, 1887',
                'death'        => '1887-11-11',
            ],
            [
                'name'         => 'Louis Lingg',
                'first_name'   => 'Louis',
                'last_name'    => 'Lingg',
                'birthdate'    => '1864-09-09',
                'death_date'   => '1887-11-10',
                'description'  => "Louis Lingg was a 22-year-old German-born carpenter and the youngest of the Haymarket defendants. He had not been at the rally and prosecutors did not allege he was — they argued only that he had built bombs. On the night of November 10, 1887, the eve of his scheduled execution, he killed himself in his cell with a smuggled blasting cap, denying the state his execution.",
                'institution'  => 'cook',
                'sentence'     => 'Death by hanging — died by suicide in his cell November 10, 1887',
                'death'        => '1887-11-10',
            ],
            [
                'name'         => 'Samuel Fielden',
                'first_name'   => 'Samuel',
                'last_name'    => 'Fielden',
                'birthdate'    => '1847-02-25',
                'death_date'   => '1922-02-07',
                'description'  => "Samuel Fielden was an English-born textile worker and Methodist lay preacher who became a leading figure in Chicago's anarchist movement. He was speaking from the wagon at Haymarket Square when the bomb was thrown. Sentenced to death, he and Michael Schwab petitioned the governor for mercy and had their sentences commuted to life imprisonment in 1887. Pardoned by Illinois Governor John Peter Altgeld on June 26, 1893, he lived another 29 years.",
                'institution'  => 'joliet',
                'sentence'     => 'Death sentence commuted to life in 1887; fully pardoned by Governor Altgeld on June 26, 1893 (held approximately 6 years)',
                'release'      => '1893-06-26',
            ],
            [
                'name'         => 'Michael Schwab',
                'first_name'   => 'Michael',
                'last_name'    => 'Schwab',
                'birthdate'    => '1853-08-09',
                'death_date'   => '1898-06-29',
                'description'  => "Michael Schwab was a German-born bookbinder and assistant editor of the Arbeiter-Zeitung. He had been at a separate union meeting on the evening of the bombing and prosecutors did not place him in the square. Sentenced to death, he and Samuel Fielden petitioned the governor for mercy and had their sentences commuted to life imprisonment in 1887. Pardoned by Governor Altgeld on June 26, 1893.",
                'institution'  => 'joliet',
                'sentence'     => 'Death sentence commuted to life in 1887; fully pardoned by Governor Altgeld on June 26, 1893 (held approximately 6 years)',
                'release'      => '1893-06-26',
            ],
            [
                'name'         => 'Oscar Neebe',
                'first_name'   => 'Oscar',
                'middle_name'  => 'William',
                'last_name'    => 'Neebe',
                'birthdate'    => '1850-07-12',
                'death_date'   => '1916-04-22',
                'description'  => "Oscar Neebe was a yeast salesman and labor organizer who had played no role in the Haymarket meeting at all. The jury, unable to find him guilty of the bombing, convicted him only of being an anarchist and sentenced him to fifteen years. Pardoned by Governor Altgeld on June 26, 1893, after serving roughly six years at Joliet.",
                'institution'  => 'joliet',
                'sentence'     => '15 years; pardoned by Governor Altgeld on June 26, 1893 (held approximately 6 years)',
                'release'      => '1893-06-26',
            ],
        ];

        foreach ($haymarketDefendants as $d) {
            DB::transaction(function () use ($d, $cookCountyJail, $joliet, $haymarket, &$created, &$skipped) {
                if (Prisoner::where('name', $d['name'])->exists()) {
                    $this->warn("Skipping {$d['name']} — already exists.");
                    $skipped++;
                    return;
                }

                $prisoner = Prisoner::create(array_filter([
                    'name'        => $d['name'],
                    'first_name'  => $d['first_name'],
                    'middle_name' => $d['middle_name'] ?? null,
                    'last_name'   => $d['last_name'],
                    'birthdate'   => $d['birthdate'],
                    'death_date'  => $d['death_date'],
                    'gender'      => 'Male',
                    'state'       => 'Illinois',
                    'description' => $d['description']."\n\n".$haymarket,
                    'era'         => '1880s',
                    'ideologies'  => ['Anarchist', 'Labor'],
                    'affiliation' => ['International Working People\'s Association'],
                    'in_custody'  => false,
                    'released'    => isset($d['release']),
                ], fn ($v) => $v !== null));

                $institutionId = $d['institution'] === 'joliet' ? $joliet->id : $cookCountyJail->id;

                PrisonerCase::create(array_filter([
                    'prisoner_id'           => $prisoner->id,
                    'institution_id'        => $institutionId,
                    'charges'               => 'Conspiracy to commit murder (Haymarket bombing prosecution)',
                    'arrest_date'           => '1886-05-05',
                    'release_date'          => $d['release'] ?? null,
                    'death_in_custody_date' => $d['death'] ?? null,
                    'convicted'             => 'Yes — Cook County jury verdict, August 20, 1886; trial widely condemned as a political prosecution and the surviving defendants formally pardoned by Governor John Peter Altgeld on June 26, 1893',
                    'sentence'              => $d['sentence'],
                    'judge'                 => 'Joseph E. Gary',
                ], fn ($v) => $v !== null));

                $this->info("Added {$prisoner->name}");
                $created++;
            });
        }

        // ─────────────────────────────────────────────────────────
        // Clement Vallandigham, 1863
        // ─────────────────────────────────────────────────────────

        DB::transaction(function () use ($kemper, &$created, &$skipped) {
            if (Prisoner::where('name', 'Clement Vallandigham')->exists()) {
                $this->warn("Skipping Clement Vallandigham — already exists.");
                $skipped++;
                return;
            }

            $prisoner = Prisoner::create([
                'name'        => 'Clement Vallandigham',
                'first_name'  => 'Clement',
                'middle_name' => 'Laird',
                'last_name'   => 'Vallandigham',
                'birthdate'   => '1820-07-29',
                'death_date'  => '1871-06-17',
                'gender'      => 'Male',
                'state'       => 'Ohio',
                'era'         => '1860s',
                'ideologies'  => ['Anti-war'],
                'affiliation' => ['Democratic Party (Copperhead faction)'],
                'in_custody'  => false,
                'released'    => true,
                'description' => "Clement Laird Vallandigham was a former U.S. Representative from Ohio and the leader of the antiwar 'Copperhead' faction of the Democratic Party during the Civil War. On May 1, 1863, while campaigning for governor of Ohio, he gave a speech in Mount Vernon denouncing the war and the Lincoln administration. Four days later, he was arrested in his Dayton home in the middle of the night by a company of soldiers acting under General Ambrose Burnside's General Order No. 38, which had criminalized 'expressed or implied' sympathy for the enemy.\n\nHe was tried by a military commission rather than a civilian court, despite the fact that the civilian courts in Ohio were fully open and functioning, and was sentenced to confinement in a military prison for the duration of the war. President Lincoln, wary of making him a martyr, instead ordered him expelled through the lines into the Confederacy on May 19, 1863. Vallandigham eventually made his way to Canada, where he continued to campaign for governor in absentia (losing badly), and returned to Ohio in 1864 to a quiet welcome that the administration declined to enforce.\n\nThe Supreme Court declined to hear his appeal in Ex parte Vallandigham (1864), holding that it had no jurisdiction over military commissions — a ruling the Court would substantially walk back two years later in Ex parte Milligan. Vallandigham's case remains the leading 19th-century American precedent on the limits of executive war power and the prosecution of antiwar speech.",
            ]);

            PrisonerCase::create([
                'prisoner_id'    => $prisoner->id,
                'institution_id' => $kemper->id,
                'charges'        => "Violation of General Order No. 38 — declaring sympathies for the enemy and uttering disloyal sentiments and opinions with the object of weakening the power of the government in its efforts to suppress an unlawful rebellion",
                'arrest_date'    => '1863-05-05',
                'release_date'   => '1863-06-02',
                'convicted'      => 'Yes — military commission verdict, May 1863',
                'sentence'       => "Confinement in a U.S. military prison for the duration of the war; sentence commuted by President Lincoln on May 19, 1863 to banishment beyond Union lines into the Confederacy",
            ]);

            $this->info("Added {$prisoner->name}");
            $created++;
        });

        $this->info("\nDone. Created {$created}, skipped {$skipped}.");

        return self::SUCCESS;
    }
}
