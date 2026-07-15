<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * California farm-labor political prisoners surfaced from Sam Kushner's "Long
 * Road to Delano" (International Publishers, 1975). Adds only those not already
 * in the database — most of the figures in the book (the Wheatland pair Ford
 * and Suhr, the Sacramento CAWIU defendants Chambers, Decker, Wilson, Crane,
 * Conklin, Hougardy, Cutler, Lorine Norman, the Imperial Valley defendants,
 * William Z. Foster, Cesar Chavez, Elaine Black) are already present.
 *
 * New here: Norman Mini (Sacramento CAWIU trial, 1935), Karl Yoneda and Louis
 * Yamamoto (Communist farm/waterfront organizers), and Jack Jones (IWW,
 * Missoula 1909 free-speech fight). Details are stated conservatively where the
 * source is brief. Idempotent — skips any name already present.
 */
class AddFarmLaborPrisoners extends Command
{
    protected $signature = 'prisoners:add-farm-labor-prisoners';

    protected $description = 'Add farm-labor political prisoners from Kushner\'s Long Road to Delano (Mini, Yoneda, Yamamoto, Jones)';

    public function handle(): int
    {
        foreach ($this->people() as $p) {
            DB::transaction(function () use ($p) {
                if (Prisoner::where('name', $p['name'])->exists()) {
                    $this->warn('Skipped (already exists): '.$p['name']);

                    return;
                }

                $prisoner = Prisoner::create(array_merge([
                    'name' => $p['name'],
                    'in_custody' => false,
                    'released' => true,
                    'in_exile' => false,
                    'awaiting_trial' => false,
                ], $p['fields']));

                $case = $p['case'];
                if (isset($case['institution'])) {
                    $inst = Institution::firstOrCreate(
                        ['name' => $case['institution']['name']],
                        ['city' => $case['institution']['city'] ?? null, 'state' => $case['institution']['state'] ?? null]
                    );
                    $case['institution_id'] = $inst->id;
                    unset($case['institution']);
                }
                PrisonerCase::create(array_merge(['prisoner_id' => $prisoner->id], $case));

                $this->info('Added: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
            });
        }

        return self::SUCCESS;
    }

    private function people(): array
    {
        return [
            [
                'name' => 'Norman Mini',
                'fields' => [
                    'first_name' => 'Norman', 'last_name' => 'Mini',
                    'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'era' => '1930s',
                    'ideologies' => ['Communism', 'Labor organizing'],
                    'affiliation' => ['Cannery and Agricultural Workers Industrial Union'],
                    'description' => 'Norman Mini was a defendant in the 1935 Sacramento criminal-syndicalism trial of the leaders of the Cannery and Agricultural Workers Industrial Union (CAWIU), the Communist-led farm-labor union that had led the great California field strikes of 1933. Prosecuted under the California Criminal Syndicalism Act — the growers\' chief legal weapon against farm-worker organizing — he was convicted and sentenced to three years, the lightest of the terms handed down (co-defendants Pat Chambers, Caroline Decker, Martin Wilson, Jack Crane, and Nora Conklin each received five years, and Albert Hougardy three and a half).',
                ],
                'case' => [
                    'charges' => 'Criminal syndicalism (California Criminal Syndicalism Act) — as a leader of the Cannery and Agricultural Workers Industrial Union, in the 1935 Sacramento trial aimed at smashing the farm-workers\' union.',
                    'convicted' => 'Yes — convicted in the 1935 Sacramento criminal-syndicalism trial.',
                    'sentence' => 'Three years.',
                ],
            ],
            [
                'name' => 'Karl Yoneda',
                'fields' => [
                    'first_name' => 'Karl', 'last_name' => 'Yoneda',
                    'aka' => 'Karl Goso Yoneda',
                    'gender' => 'Male', 'race' => 'Asian', 'state' => 'California', 'era' => '1920s',
                    'ideologies' => ['Communism', 'Labor organizing'],
                    'affiliation' => ['Communist Party USA'],
                    'description' => 'Karl Yoneda was a Japanese-American Communist and labor organizer active on the California waterfront and in the fields. In 1929, protesting the visit of a Japanese naval training ship to San Pedro harbor, he was arrested with fellow Communist Tetsuji Horiuchi and held for three days on suspicion of violating California\'s criminal-syndicalism law. He went on to organize agricultural and longshore workers through the 1930s and was repeatedly arrested; though a Communist opponent of Japanese militarism, he was later incarcerated with his family at the Manzanar concentration camp during the wartime internment of Japanese Americans, from which he volunteered for the U.S. Army. He was the husband of the labor-defense organizer Elaine Black Yoneda.',
                ],
                'case' => [
                    'charges' => 'Suspicion of violating California\'s criminal-syndicalism law — arrested in 1929 while protesting the visit of a Japanese naval training ship to San Pedro harbor, together with the Communist organizer Tetsuji Horiuchi.',
                    'convicted' => 'Held three days on suspicion and released without charge (by which time the naval ship had left port).',
                    'sentence' => 'Held three days in 1929.',
                ],
            ],
            [
                'name' => 'Louis Yamamoto',
                'fields' => [
                    'first_name' => 'Louis', 'last_name' => 'Yamamoto',
                    'gender' => 'Male', 'race' => 'Asian', 'state' => 'California', 'era' => '1930s',
                    'ideologies' => ['Communism', 'Labor organizing'],
                    'description' => 'Louis Yamamoto was a Japanese-American Communist organizing farm workers in California\'s Central Valley during the agricultural strikes of the early 1930s. He was arrested and held on high bail at the Stockton jail. The International Labor Defense organizer Elaine Black Yoneda recalled that to reach the court and post his bail she and the union leader Pat Chambers had to bluff their way past an armed vigilante roadblock by claiming they were on their way to the Justice of the Peace to be married.',
                ],
                'case' => [
                    'institution' => ['name' => 'Stockton Jail', 'city' => 'Stockton', 'state' => 'California'],
                    'charges' => 'Arrested and held on high bail while organizing farm workers as a Communist during the early-1930s California agricultural strikes.',
                    'convicted' => 'Held on high bail at the Stockton jail; freed when the International Labor Defense posted bail.',
                ],
            ],
            [
                'name' => 'Jack Jones',
                'fields' => [
                    'first_name' => 'Jack', 'last_name' => 'Jones',
                    'gender' => 'Male', 'race' => 'White', 'state' => 'Montana', 'era' => '1900s',
                    'ideologies' => ['Labor organizing'],
                    'affiliation' => ['Industrial Workers of the World'],
                    'description' => 'Jack Jones was an Industrial Workers of the World (Wobbly) organizer and, at the time, the husband of Elizabeth Gurley Flynn. During the IWW\'s Missoula, Montana free-speech fight in the summer of 1909 — a template for the free-speech battles the Wobblies would later wage across the western agricultural towns — he was arrested and beaten in jail while he and Flynn organized the town\'s migratory workers.',
                ],
                'case' => [
                    'charges' => 'Arrested in the Industrial Workers of the World free-speech fight at Missoula, Montana in the summer of 1909, while organizing migratory workers.',
                    'convicted' => 'Arrested and beaten in jail during the free-speech fight; the disposition is not documented in the source.',
                    'arrest_date' => null,
                ],
            ],
        ];
    }
}
