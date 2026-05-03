<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddAnimalRightsAdvocates extends Command
{
    protected $signature = 'prisoners:add-animal-rights-trio';
    protected $description = 'Add Daniel Andreas San Diego and Camille Marino — US animal-rights advocates who served prison time but are not yet in the database.';

    public function handle(): int
    {
        $created = 0;
        $skipped = 0;

        $ukJail = Institution::firstOrCreate(
            ['name' => 'HMP Wandsworth (UK pretrial detention pending US extradition)'],
            ['city' => 'London', 'state' => 'England, United Kingdom']
        );

        $detroit = Institution::firstOrCreate(
            ['name' => 'Federal Detention Center, Detroit'],
            ['city' => 'Detroit', 'state' => 'Michigan']
        );

        $entries = [
            // ── Daniel Andreas San Diego ──────────────────────────────
            [
                'data' => [
                    'name'           => 'Daniel Andreas San Diego',
                    'first_name'     => 'Daniel',
                    'middle_name'    => 'Andreas',
                    'last_name'      => 'San Diego',
                    'birthdate'      => '1978-02-09',
                    'death_date'     => null,
                    'gender'         => 'Male',
                    'state'          => 'California',
                    'era'            => '2020s',
                    'ideologies'     => ['Animal rights', 'Vegan', 'Straight edge', 'Environmental'],
                    'affiliation'    => ['Animal Liberation Brigade', 'Earth Liberation Front'],
                    'in_custody'     => true,
                    'released'       => false,
                    'awaiting_trial' => true,
                    'description' => "Daniel Andreas San Diego is a straight-edge vegan animal-liberation activist from the San Francisco Bay Area who, beginning in 2003, was the first domestic-terrorism suspect ever placed on the FBI's Most Wanted Terrorists list — a designation usually reserved for international jihadist suspects.\n\nFederal prosecutors charged him with planting two pipe bombs that exploded in the early morning hours of August 28, 2003 at the Emeryville, California campus of Chiron Corporation, a biotechnology company that had used Huntingdon Life Sciences as a contract research organization, and a third nail-bomb that exploded a month later, on September 26, 2003, at the Pleasanton headquarters of Shaklee Corporation. No one was killed in either attack. The Animal Liberation Brigade, a small ALF-aligned cell, claimed responsibility.\n\nOn October 9, 2003, San Diego was photographed boarding a Bay Area Rapid Transit train and disappeared. He spent the next 21 years on the run, evading capture across multiple continents. The FBI placed him on the Most Wanted Terrorists list in 2009 with a \$250,000 reward, later increased to \$1 million.\n\nOn November 25, 2024, after a long surveillance operation by the UK's National Crime Agency, he was arrested in the village of Maenan, near Llanrwst in Conwy, North Wales, where he had been living quietly under the alias 'Danny Stephen Webb' on a forged Irish passport. He was held at HMP Wandsworth pending an extradition hearing. On February 6, 2026, Judge Samuel Goozee of Westminster Magistrates' Court ruled that he could be extradited to the United States, leaving the final decision to the UK Home Secretary. As of this writing he remains in UK pretrial detention awaiting the final extradition order.",
                ],
                'case' => [
                    'institution_id' => $ukJail->id,
                    'charges'        => "U.S. federal charges (Northern District of California): use of a destructive device (18 U.S.C. § 924(c)); two counts of malicious destruction of property by means of an explosive (18 U.S.C. § 844(i)); use of a destructive device during a crime of violence — for the August 28, 2003 bombings at Chiron Corporation in Emeryville and the September 26, 2003 nail-bombing at Shaklee Corporation in Pleasanton, claimed at the time by the Animal Liberation Brigade",
                    'arrest_date'    => '2024-11-25',
                    'incarceration_date' => '2024-11-25',
                    'release_date'   => null,
                    'convicted'      => "Not yet — held in UK pretrial detention since November 25, 2024. UK extradition proceedings: Westminster Magistrates' Court judge Samuel Goozee ruled February 6, 2026 that extradition to the U.S. could proceed; Home Secretary's final decision pending",
                    'sentence'       => 'No sentence yet — awaiting US extradition. If convicted in the U.S. on all charges he could face a mandatory minimum of 30 years',
                    'judge'          => 'Samuel Goozee (Westminster Magistrates\' Court, UK extradition)',
                ],
            ],

            // ── Camille Marino ─────────────────────────────────────────
            [
                'data' => [
                    'name'           => 'Camille Marino',
                    'first_name'     => 'Camille',
                    'last_name'      => 'Marino',
                    'birthdate'      => '1962-01-01',
                    'death_date'     => null,
                    'gender'         => 'Female',
                    'state'          => 'Florida',
                    'era'            => '2010s',
                    'ideologies'     => ['Animal rights', 'Anti-vivisection'],
                    'affiliation'    => ['Negotiation Is Over (NIO)', 'Eleventh Hour for Animals'],
                    'in_custody'     => false,
                    'released'       => true,
                    'awaiting_trial' => false,
                    'description' => "Camille Marino is an American animal-rights organizer and the founder of Negotiation Is Over (NIO), a campaigning network founded in 2009 that argued openly for confrontational and intimidating tactics against named individual researchers conducting animal experimentation — including the publication of identifying information about specific scientists. Her work made her one of the most polarizing figures of the late-2000s/early-2010s anti-vivisection movement; many in the animal-rights movement criticized her tactics as counterproductive while others defended them as a necessary escalation against industries protected by the Animal Enterprise Terrorism Act.\n\nIn 2012, in connection with a sustained campaign against Wayne State University researcher Donal O'Leary, she was arrested for posting messages naming and addressing O'Leary in violation of an existing protective order. She pleaded guilty in Michigan state court to unlawful posting of a message with aggravating circumstances and to trespassing. She was sentenced to six months and served her time in Detroit, completing the sentence in early 2013. While incarcerated she also won a separate civil-rights case against the University of Florida, which had banned her from campus over her organizing.\n\nAfter release she returned to Florida and continued writing and organizing, founding the successor project Eleventh Hour for Animals.",
                ],
                'case' => [
                    'institution_id' => $detroit->id,
                    'charges'        => 'Unlawful posting of a message with aggravating circumstances; trespassing (Michigan state court) — arising from a NIO campaign against Wayne State University vivisection researcher Donal O\'Leary in violation of a protective order',
                    'arrest_date'    => '2012-07-13',
                    'incarceration_date' => '2012-09-01',
                    'release_date'   => '2013-03-01',
                    'convicted'      => 'Yes — guilty plea, Michigan state court, 2012',
                    'sentence'       => 'Six months in custody; served sentence in Detroit through early 2013',
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
