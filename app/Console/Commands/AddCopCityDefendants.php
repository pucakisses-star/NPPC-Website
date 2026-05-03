<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Stop Cop City / Defend the Atlanta Forest defendants who served
 * meaningful jail time. The 61-defendant Georgia RICO case is still
 * pretrial as of writing, so this is restricted to the subset who were
 * held in custody for more than a day or so before posting bond — not
 * the broader group who were arrested and released the same day.
 *
 * Tortuguita (Manuel Esteban Paez Terán) is intentionally excluded;
 * per the editorial line established earlier, killed-not-imprisoned
 * cases are not added.
 */
class AddCopCityDefendants extends Command
{
    protected $signature = 'prisoners:add-cop-city';
    protected $description = 'Add Stop Cop City defendants who served meaningful pretrial detention.';

    public function handle(): int
    {
        $created = 0;
        $skipped = 0;

        $dekalb = Institution::firstOrCreate(
            ['name' => 'DeKalb County Jail (Stop Cop City pretrial detention)'],
            ['city' => 'Decatur', 'state' => 'Georgia']
        );

        $iceFolkston = Institution::firstOrCreate(
            ['name' => 'Folkston ICE Processing Center'],
            ['city' => 'Folkston', 'state' => 'Georgia']
        );

        $atlanta = Institution::firstOrCreate(
            ['name' => 'Atlanta City Detention Center'],
            ['city' => 'Atlanta', 'state' => 'Georgia']
        );

        $forestFraming = "On January 18, 2023, during a multi-agency raid on the Stop Cop City forest encampment in the South River Forest near Atlanta, Georgia State Patrol officers shot and killed 26-year-old forest defender Manuel Esteban Paez Terán (Tortuguita). The killing — the first known police killing of an environmental activist on U.S. soil during a protest — accelerated and radicalized the multi-year movement against the construction of the Atlanta Public Safety Training Center, a 171-acre, \$90+ million police and fire training facility in the South River Forest known to opponents as 'Cop City.'\n\nGeorgia and DeKalb County prosecutors responded with the most aggressive use of state-level domestic-terrorism and RICO statutes against a U.S. protest movement in modern history: 42 forest defenders were charged under Georgia's domestic-terrorism statute (originally enacted in 2017 in response to white-supremacist mass shootings) for activities ranging from camping in the forest to attending a March 2023 music festival a mile from the construction site. On August 29, 2023, an Atlanta grand jury returned a single 109-page RICO indictment against 61 of these defendants — the largest RICO indictment of a U.S. social movement in decades. Three Atlanta Solidarity Fund leaders were also indicted on 15 counts each of money laundering and charity fraud (charges later dropped in September 2024). The RICO case has remained largely stalled in pretrial proceedings; this database entry is for one of the small number of defendants who were denied bond or otherwise held in custody for meaningful periods before posting bail.";

        $entries = [
            [
                'data' => [
                    'name'           => 'Victor Puertas',
                    'first_name'     => 'Victor',
                    'last_name'      => 'Puertas',
                    'birthdate'      => '1985-01-01', // approximate
                    'death_date'     => null,
                    'gender'         => 'Male',
                    'race'           => 'Native American',
                    'state'          => 'Georgia',
                    'era'            => '2020s',
                    'ideologies'     => ['Environmental', 'Indigenous sovereignty', 'Anti-police', 'Forest defender'],
                    'affiliation'    => ['Stop Cop City', 'Defend the Atlanta Forest'],
                    'in_custody'     => false,
                    'released'       => true,
                    'awaiting_trial' => true,
                    'description'    => "Victor Puertas is an Indigenous forest defender from the Andean region of South America who became one of the longest-detained Stop Cop City pretrial prisoners. He was arrested on March 5, 2023 during the Georgia State Patrol's mass raid on the music festival in the Weelaunee Forest, where approximately 350 protesters had gathered as part of a Defend the Atlanta Forest week of action. He was held at the DeKalb County Jail for three months without bond on a charge of domestic terrorism, then transferred to ICE custody at the Stewart Detention Center and the Folkston ICE Processing Center, where he was held for an additional eight months in immigration detention before finally being released. His total time in custody for the action came to approximately 11 months — the longest pretrial detention of any Stop Cop City defendant. He was indicted in the August 2023 RICO sweep along with the other 60 defendants. The RICO case against him remains pending.\n\n".$forestFraming,
                ],
                'case' => [
                    'institution_id'     => $dekalb->id,
                    'charges'            => "Domestic terrorism (Georgia Code § 16-11-220); racketeering / RICO (Georgia Code § 16-14-4) — for participation in the March 5, 2023 Defend the Atlanta Forest week of action and attendance at the Weelaunee music festival. Plus parallel ICE immigration removal proceedings",
                    'arrest_date'        => '2023-03-05',
                    'incarceration_date' => '2023-03-05',
                    'release_date'       => '2024-02-01',
                    'convicted'          => 'Not yet — RICO case pending. Released after approximately 11 months of pretrial / ICE detention',
                    'sentence'           => 'Approximately 11 months in custody: 3 months at DeKalb County Jail without bond plus 8 months in ICE detention at Stewart and Folkston ICE Processing Center; longest pretrial detention of any Stop Cop City defendant',
                ],
            ],
            [
                'data' => [
                    'name'           => 'Luke Harper',
                    'first_name'     => 'Luke',
                    'last_name'      => 'Harper',
                    'birthdate'      => '1996-01-01', // age 27 in March 2023
                    'death_date'     => null,
                    'gender'         => 'Male',
                    'state'          => 'Florida',
                    'era'            => '2020s',
                    'ideologies'     => ['Environmental', 'Anti-police', 'Forest defender'],
                    'affiliation'    => ['Stop Cop City', 'Defend the Atlanta Forest'],
                    'in_custody'     => false,
                    'released'       => true,
                    'awaiting_trial' => true,
                    'description'    => "Luke Harper, age 27 of Lake Worth, Florida at the time of arrest, was one of 23 people arrested by Georgia State Patrol and the Atlanta Police Department during the March 5, 2023 raid on the Stop Cop City music festival in the South River (Weelaunee) Forest. He had posted Instagram video of himself at the action shortly before the arrests; that video was used by prosecutors to argue against bond. He was charged with domestic terrorism under Georgia's § 16-11-220 statute and was denied pretrial release at his preliminary hearing in May 2023, while a co-defendant who had not posted social media was granted a \$25,000 bond on the same charges. He was held at the DeKalb County Jail through the summer of 2023. He was subsequently indicted in the August 2023 Stop Cop City RICO sweep along with 60 other defendants. The RICO case against him remains pending.\n\n".$forestFraming,
                ],
                'case' => [
                    'institution_id'     => $dekalb->id,
                    'charges'            => "Domestic terrorism (Georgia Code § 16-11-220); racketeering / RICO (Georgia Code § 16-14-4) — for participation in the March 5, 2023 Defend the Atlanta Forest action at the Weelaunee music festival",
                    'arrest_date'        => '2023-03-05',
                    'incarceration_date' => '2023-03-05',
                    'release_date'       => '2023-09-01',
                    'convicted'          => 'Not yet — RICO case pending',
                    'sentence'           => 'Several months pretrial detention at DeKalb County Jail after being denied bond at his May 2023 preliminary hearing — bond denial cited his Instagram posts about the action',
                ],
            ],
            [
                'data' => [
                    'name'           => 'Priscilla Grim',
                    'first_name'     => 'Priscilla',
                    'last_name'      => 'Grim',
                    'birthdate'      => '1975-01-01', // age 48 in 2023
                    'death_date'     => null,
                    'gender'         => 'Female',
                    'state'          => 'New York',
                    'era'            => '2020s',
                    'ideologies'     => ['Environmental', 'Anti-police', 'Anti-capitalist'],
                    'affiliation'    => ['Stop Cop City', 'Occupy Wall Street (formerly)'],
                    'in_custody'     => false,
                    'released'       => true,
                    'awaiting_trial' => true,
                    'description'    => "Priscilla Grim, age 48 of Brooklyn, New York, was a longtime media organizer with Occupy Wall Street in 2011-2012 and later a contributor to the National Lawyers Guild's mass-defense work. She traveled to Atlanta to support the Stop Cop City week of action in March 2023 and was arrested at the music festival on March 5, 2023. She was charged under Georgia's § 16-11-220 domestic-terrorism statute and held at the DeKalb County Jail for several weeks before bond was set and she could return to New York. She was subsequently indicted in the August 2023 RICO indictment along with 60 other defendants and now faces felony racketeering charges in addition to the original domestic-terrorism count. She has lost her job and reported severe financial and personal hardship as a result of the years-long pretrial proceedings. The RICO case against her remains pending.\n\n".$forestFraming,
                ],
                'case' => [
                    'institution_id'     => $dekalb->id,
                    'charges'            => "Domestic terrorism (Georgia Code § 16-11-220); racketeering / RICO (Georgia Code § 16-14-4) — for participation in the March 5, 2023 Defend the Atlanta Forest action at the Weelaunee music festival",
                    'arrest_date'        => '2023-03-05',
                    'incarceration_date' => '2023-03-05',
                    'release_date'       => '2023-04-15',
                    'convicted'          => 'Not yet — RICO case pending',
                    'sentence'           => 'Several weeks pretrial detention at DeKalb County Jail before bond was set and she was permitted to return to New York; current RICO prosecution ongoing',
                ],
            ],
            [
                'data' => [
                    'name'           => 'Marlon Kautz',
                    'first_name'     => 'Marlon',
                    'middle_name'    => 'Scott',
                    'last_name'      => 'Kautz',
                    'birthdate'      => '1985-01-01',
                    'death_date'     => null,
                    'gender'         => 'Male',
                    'state'          => 'Georgia',
                    'era'            => '2020s',
                    'ideologies'     => ['Mutual aid', 'Bail fund', 'Anti-police'],
                    'affiliation'    => ['Atlanta Solidarity Fund', 'Network for Strong Communities'],
                    'in_custody'     => false,
                    'released'       => true,
                    'awaiting_trial' => true,
                    'description'    => "Marlon Scott Kautz is the chief financial officer of the Network for Strong Communities, the nonprofit that operates the Atlanta Solidarity Fund — a bail fund founded in 2020 that has provided bail money and helped find attorneys for Atlanta-area protesters since the George Floyd Uprising. The Solidarity Fund had bailed out a number of Stop Cop City defendants in the months following the January 2023 police killing of Tortuguita.\n\nOn the morning of May 31, 2023, the Atlanta Police Department and Georgia Bureau of Investigation conducted a militarized SWAT raid on the home Kautz shared with co-organizers Adele MacLean and Savannah Patterson, arresting all three on charges of money laundering and charity fraud. The 15 counts of money laundering against each were based on routine bail-fund disbursements and small reimbursements (including \$93.04 for 'camping supplies' and \$12.52 for 'forest kitchen materials'). They were held at the Atlanta City Detention Center for two days before bond was set and they were released. They were subsequently named among the 61 RICO defendants in the August 2023 indictment. The 15 counts of money laundering against each were quietly dismissed by Georgia prosecutors in September 2024; the RICO charges remain pending.\n\n".$forestFraming,
                ],
                'case' => [
                    'institution_id'     => $atlanta->id,
                    'charges'            => "15 counts of money laundering (later dismissed September 2024); charity fraud; racketeering / RICO (Georgia Code § 16-14-4) — for his role as CFO of the Network for Strong Communities and the Atlanta Solidarity Fund",
                    'arrest_date'        => '2023-05-31',
                    'incarceration_date' => '2023-05-31',
                    'release_date'       => '2023-06-02',
                    'convicted'          => 'Not yet — money laundering charges dismissed September 2024; RICO prosecution pending',
                    'sentence'           => 'Approximately 2 days at Atlanta City Detention Center before release on bond. Continues to face RICO charges',
                ],
            ],
            [
                'data' => [
                    'name'           => 'Adele MacLean',
                    'first_name'     => 'Adele',
                    'last_name'      => 'MacLean',
                    'birthdate'      => '1985-01-01',
                    'death_date'     => null,
                    'gender'         => 'Female',
                    'state'          => 'Georgia',
                    'era'            => '2020s',
                    'ideologies'     => ['Mutual aid', 'Bail fund', 'Anti-police'],
                    'affiliation'    => ['Atlanta Solidarity Fund', 'Network for Strong Communities'],
                    'in_custody'     => false,
                    'released'       => true,
                    'awaiting_trial' => true,
                    'description'    => "Adele MacLean is the chief executive officer of the Network for Strong Communities, the nonprofit that operates the Atlanta Solidarity Fund. She was arrested with her co-organizers Marlon Kautz and Savannah Patterson during the May 31, 2023 SWAT raid on their shared home and charged with 15 counts of money laundering and charity fraud over the Solidarity Fund's bail-fund operations. She was held at the Atlanta City Detention Center for approximately two days before bond was set and she was released. She was subsequently named in the August 2023 Stop Cop City RICO indictment. Georgia prosecutors dismissed the money laundering counts in September 2024; the RICO prosecution remains pending.\n\n".$forestFraming,
                ],
                'case' => [
                    'institution_id'     => $atlanta->id,
                    'charges'            => "15 counts of money laundering (dismissed September 2024); charity fraud; racketeering / RICO — for her role as CEO of the Network for Strong Communities and the Atlanta Solidarity Fund",
                    'arrest_date'        => '2023-05-31',
                    'incarceration_date' => '2023-05-31',
                    'release_date'       => '2023-06-02',
                    'convicted'          => 'Not yet — money laundering charges dismissed; RICO prosecution pending',
                    'sentence'           => 'Approximately 2 days at Atlanta City Detention Center before release on bond. Continues to face RICO charges',
                ],
            ],
            [
                'data' => [
                    'name'           => 'Savannah Patterson',
                    'first_name'     => 'Savannah',
                    'last_name'      => 'Patterson',
                    'birthdate'      => '1985-01-01',
                    'death_date'     => null,
                    'gender'         => 'Female',
                    'state'          => 'Georgia',
                    'era'            => '2020s',
                    'ideologies'     => ['Mutual aid', 'Bail fund', 'Anti-police'],
                    'affiliation'    => ['Atlanta Solidarity Fund', 'Network for Strong Communities'],
                    'in_custody'     => false,
                    'released'       => true,
                    'awaiting_trial' => true,
                    'description'    => "Savannah Patterson is the secretary of the Network for Strong Communities, the nonprofit that operates the Atlanta Solidarity Fund. She was arrested with her co-organizers Marlon Kautz and Adele MacLean during the May 31, 2023 SWAT raid on their shared home and charged with 15 counts of money laundering and charity fraud over the Solidarity Fund's bail-fund operations. She was held at the Atlanta City Detention Center for approximately two days before bond was set and she was released. She was subsequently named in the August 2023 Stop Cop City RICO indictment. The money laundering counts were dismissed in September 2024; the RICO prosecution remains pending.\n\n".$forestFraming,
                ],
                'case' => [
                    'institution_id'     => $atlanta->id,
                    'charges'            => "15 counts of money laundering (dismissed September 2024); charity fraud; racketeering / RICO — for her role as secretary of the Network for Strong Communities and the Atlanta Solidarity Fund",
                    'arrest_date'        => '2023-05-31',
                    'incarceration_date' => '2023-05-31',
                    'release_date'       => '2023-06-02',
                    'convicted'          => 'Not yet — money laundering charges dismissed; RICO prosecution pending',
                    'sentence'           => 'Approximately 2 days at Atlanta City Detention Center before release on bond. Continues to face RICO charges',
                ],
            ],
        ];

        foreach ($entries as $entry) {
            DB::transaction(function () use ($entry, &$created, &$skipped) {
                $name = $entry['data']['name'];
                if (Prisoner::where('name', $name)->exists()) {
                    $this->warn("  skipped: {$name} already exists");
                    $skipped++;
                    return;
                }

                $prisoner = Prisoner::create($entry['data']);
                PrisonerCase::create(array_merge(['prisoner_id' => $prisoner->id], $entry['case']));

                $this->info("  added {$prisoner->name}");
                $created++;
            });
        }

        $this->info("\nDone. {$created} created, {$skipped} skipped.");

        return self::SUCCESS;
    }
}
