<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddOlderEcoPrisoners extends Command
{
    protected $signature = 'prisoners:add-older-eco';
    protected $description = 'Add William "Avalon" Rodgers and Tre Arrow — earlier-era ELF/animal-rights prisoners not yet in the database.';

    public function handle(): int
    {
        $created = 0;
        $skipped = 0;

        $maricopa = Institution::firstOrCreate(
            ['name' => 'Maricopa County Jail (4th Avenue)'],
            ['city' => 'Phoenix', 'state' => 'Arizona']
        );

        $sheridan = Institution::firstOrCreate(
            ['name' => 'FCI Sheridan'],
            ['city' => 'Sheridan', 'state' => 'Oregon']
        );

        $defendants = [];

        $defendants[] = [
            'data' => [
                'name'           => 'William C. Rodgers',
                'first_name'     => 'William',
                'middle_name'    => 'Courtney',
                'last_name'      => 'Rodgers',
                'aka'            => 'Avalon; Bill Rodgers',
                'gender'         => 'Male',
                'birthdate'      => '1965-01-01',
                'death_date'     => '2005-12-21',
                'state'          => 'Arizona',
                'era'            => '2000s',
                'ideologies'     => ['Animal rights', 'Environmental', 'Anarchist'],
                'affiliation'    => ['Earth Liberation Front', 'Animal Liberation Front', 'Catalyst Infoshop (Prescott, AZ)'],
                'in_custody'     => false,
                'released'       => true, // died in custody Dec 21, 2005 — see death_in_custody_date
                'awaiting_trial' => false,
                'description'    => "William Courtney Rodgers, known throughout the radical ecology and animal liberation movements as Avalon, was an environmental and animal rights activist and the co-proprietor of the Catalyst Infoshop, a long-running anarchist bookstore and community space in Prescott, Arizona. He was a participant in the small Eugene-area Earth Liberation Front cell that became known to investigators as 'The Family,' which between 1996 and 2001 carried out approximately twenty arsons and acts of sabotage in five western states targeting timber operations, ski resorts, federal animal-research facilities, meat-industry sites, and the Vail ski resort expansion that the cell argued threatened lynx habitat. The action federal prosecutors charged Rodgers with personally setting was the June 1998 fire at the U.S. Department of Agriculture's Animal and Plant Health Inspection Service / National Wildlife Research Center in Olympia, Washington — an action explicitly framed by its participants as a defense of wild animals against federal predator-control programs.\n\nOn December 7, 2005, FBI agents arrested him in Prescott as part of Operation Backfire, the multi-agency federal investigation that ultimately indicted thirteen activists in connection with the Eugene cell's actions. He was held at the Maricopa County 4th Avenue Jail in Phoenix awaiting transfer to Oregon. Two weeks after his arrest, on December 21, 2005, he was found dead in his cell. The county sheriff ruled the death a suicide by suffocation with a plastic bag.\n\nHe left a note that became one of the most widely circulated movement texts of the post-9/11 Green Scare era: 'Certain human cultures have been waging war against the Earth for millennia. I chose to fight on the side of bears, mountain lions, skunks, bats, saguaros, cliff rose, and all things wild. I am just the most recent casualty in that war. But tonight I have made a jail break — I am returning home, to the Earth, to the place of my origins.' His death is commemorated annually within the radical ecology and animal-liberation movements on December 21 as Avalon Day.",
            ],
            'case' => [
                'institution_id'        => $maricopa->id,
                'charges'               => 'One federal count of arson — June 1998 fire at the USDA National Wildlife Research Center in Olympia, Washington (Earth Liberation Front action defending wild animals against federal predator-control programs)',
                'arrest_date'           => '2005-12-07',
                'death_in_custody_date' => '2005-12-21',
                'convicted'             => 'Indicted but never tried — died in pretrial custody at the Maricopa County Jail on December 21, 2005, two weeks after arrest',
                'sentence'              => 'Faced potential 30+ years in federal prison; held in pretrial detention; died in custody',
            ],
        ];

        $defendants[] = [
            'data' => [
                'name'           => 'Tre Arrow',
                'first_name'     => 'Tre',
                'last_name'      => 'Arrow',
                'aka'            => 'Michael James Scarpitti',
                'gender'         => 'Male',
                'birthdate'      => '1974-01-01',
                'death_date'     => null,
                'state'          => 'Oregon',
                'era'            => '2000s',
                'ideologies'     => ['Environmental', 'Animal rights', 'Anarchist'],
                'affiliation'    => ['Earth Liberation Front', 'Pacific Green Party'],
                'in_custody'     => false,
                'released'       => true,
                'awaiting_trial' => false,
                'description'    => "Tre Arrow, born Michael James Scarpitti in 1974, became one of the most publicly visible figures of Pacific Northwest forest defense in the early 2000s. After dropping out of Florida State University and moving to Oregon in 1996, he announced he had to 'put my own body between the chain saws and the ancient forest.' In July 2000 he scaled the U.S. Forest Service building in downtown Portland and lived for eleven days on a nine-inch ledge to protest the planned logging of the Eagle Creek timber sale on the Mount Hood National Forest — an action that drew national press coverage and helped halt the sale.\n\nIn 2000 he ran for the U.S. House of Representatives in Oregon's 3rd Congressional District as the Pacific Green Party candidate, placing third behind Earl Blumenauer.\n\nOn June 1, 2001, three logging trucks belonging to the Ross Construction Company were burned in Estacada, Oregon, in an action claimed by the Earth Liberation Front in protest of a planned timber cut on Mount Hood. A second arson, of cement mixers belonging to a road-building company working on a Forest Service road project, followed weeks later. Federal prosecutors charged Tre Arrow as one of four participants. Three other activists pleaded out and cooperated; he refused. He fled to Canada in 2002 and lived underground there for nearly four years until his arrest in Victoria, British Columbia in March 2004 for shoplifting. He fought extradition through the Canadian courts before being returned to Oregon on February 29, 2008.\n\nOn June 3, 2008, he pleaded guilty in U.S. District Court in Portland to two counts of arson and was sentenced on August 12, 2008 to 78 months — six and a half years — in federal prison. He was held primarily at FCI Sheridan in Oregon. He was released to a halfway house in 2009 and finished his federal supervision in subsequent years. He has since returned to public environmental advocacy.",
            ],
            'case' => [
                'institution_id' => $sheridan->id,
                'charges'        => 'Two counts of arson — June 1, 2001 Ross Construction logging-truck arsons in Estacada, Oregon and the related cement-mixer arson, both claimed at the time by the Earth Liberation Front',
                'arrest_date'    => '2004-03-13',
                'incarceration_date' => '2008-08-12',
                'release_date'   => '2014-08-12',
                'convicted'      => 'Yes — pleaded guilty in U.S. District Court for the District of Oregon, June 3, 2008',
                'sentence'       => '78 months (6.5 years) in federal prison; released to a halfway house in 2009',
            ],
        ];

        foreach ($defendants as $entry) {
            DB::transaction(function () use ($entry, &$created, &$skipped) {
                $name = $entry['data']['name'];
                if (Prisoner::where('name', $name)->exists()) {
                    $this->warn("Skipping {$name} — already exists.");
                    $skipped++;
                    return;
                }

                $prisoner = Prisoner::create($entry['data']);
                PrisonerCase::create(array_merge(['prisoner_id' => $prisoner->id], $entry['case']));

                $this->info("Added {$prisoner->name}");
                $created++;
            });
        }

        $this->info("\nDone. Created {$created}, skipped {$skipped}.");

        return self::SUCCESS;
    }
}
