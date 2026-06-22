<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Political prisoners from the Jericho Movement's "Freed Political Prisoners"
 * list that were not already in the database. Most of that list (Sundiata Acoli,
 * Herman Bell, Jalil Bottom, Veronza Bowers, Ruchell Magee, Leonard Peltier,
 * David Gilbert, Patrice Lumumba Ford, Jeremy Hammond, Eric King, Tarek Mehanna,
 * Rebecca Rubin, most of the MOVE 9, etc.) is already recorded; added here are:
 *
 *  - Kazi Toure (United Freedom Front; first modern seditious-conspiracy
 *    conviction), Michael "Rattler" Markus (Standing Rock #NoDAPL), Michael Davis
 *    Africa / Mike Africa Sr. (MOVE 9), and the two "Williamsburg Four" brothers
 *    Dawud Abdur Rahman and Salih Ali Abdullah (the latter deceased — also on
 *    the Jericho ancestors list).
 *
 * Georges Ibrahim Abdallah (a Lebanese prisoner held in France) is on the list
 * but falls outside this database's U.S. scope and is not included.
 *
 * Idempotent and variant-aware: skips a record if ANY of its name variants is
 * already present, so it will not duplicate anyone already on production.
 */
final class AddJerichoFreedPrisoners extends Command
{
    protected $signature = 'prisoners:add-jericho-freed';

    protected $description = 'Add missing political prisoners from the Jericho Movement freed-prisoners list';

    public function handle(): int
    {
        $added = 0;
        $skipped = 0;

        foreach ($this->records() as $r) {
            $terms = $r['match'] ?? [$r['name']];
            unset($r['match']);

            $exists = false;
            foreach ($terms as $t) {
                if (Prisoner::withUnderReview()->where('name', 'like', '%'.$t.'%')->exists()) {
                    $exists = true;
                    break;
                }
            }
            if ($exists) {
                $this->warn("Exists, skipping: {$r['name']}");
                $skipped++;

                continue;
            }

            DB::transaction(function () use ($r) {
                $cases = $r['cases'] ?? [];
                unset($r['cases']);
                $prisoner = Prisoner::create($r);
                foreach ($cases as $c) {
                    if (! empty($c['institution_name'])) {
                        $inst = Institution::firstOrCreate(
                            ['name' => $c['institution_name']],
                            ['city' => $c['institution_city'] ?? null, 'state' => $c['institution_state'] ?? null],
                        );
                        $c['institution_id'] = $inst->id;
                    }
                    unset($c['institution_name'], $c['institution_city'], $c['institution_state']);
                    $c['prisoner_id'] = $prisoner->id;
                    PrisonerCase::create($c);
                }
            });

            $this->info("Added: {$r['name']}");
            $added++;
        }

        $this->info("\nDone. Added={$added} Skipped={$skipped}");

        return self::SUCCESS;
    }

    private function records(): array
    {
        // The "Williamsburg Four" (1973 Brooklyn hostage crisis) shared scaffold.
        $williamsburg = function (string $name, string $first, string $last, array $match, string $extra, bool $released): array {
            return [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'match' => $match,
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'New York',
                'era' => '1970s',
                'ideologies' => ['Black liberation', 'Islam'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => $released,
                'awaiting_trial' => false,
                'description' => "{$name} was one of the \"Williamsburg Four\" — four members of a Black Muslim group in Williamsburg, Brooklyn who, on January 19, 1973, entered John and Al's Sporting Goods store seeking weapons. A shootout erupted in which New York City police officer Stephen Gilroy was killed, touching off a 47-hour police siege before the four surrendered on January 21, 1973. They were convicted of Officer Gilroy's murder and sentenced to 25 years to life. The four — and the Jericho Movement, which counts them as political prisoners — maintained that Gilroy was in fact killed by police crossfire.{$extra}",
                'cases' => [[
                    'institution_state' => 'New York',
                    'charges' => 'Murder of NYPD Officer Stephen Gilroy (the January 19-21, 1973 Williamsburg, Brooklyn sporting-goods-store shootout and standoff)',
                    'convicted' => 'Yes — sentenced to 25 years to life; the defendants maintained the officer was killed by police crossfire',
                    'sentence' => '25 years to life',
                ]],
            ];
        };

        return [
            [
                'name' => 'Kazi Toure',
                'first_name' => 'Kazi',
                'last_name' => 'Toure',
                'aka' => 'Christopher King',
                'match' => ['Kazi Toure'],
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Massachusetts',
                'era' => '1980s',
                'ideologies' => ['Anti-imperialism', 'Black liberation'],
                'affiliation' => ['United Freedom Front'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Kazi Toure (Christopher King) was a member of the United Freedom Front — the small clandestine group sometimes called the "Ohio 7" — and the first person convicted of seditious conspiracy in the modern era. He was imprisoned for his part in a campaign of bombings against corporate and military targets carried out in opposition to apartheid South Africa and U.S. intervention in Central America, serving about ten years before his release on October 1, 1991. Recruited by Safiya Bukhari and Herman Ferguson, he became a coordinator of the Jericho Movement to free political prisoners.',
                'cases' => [[
                    'charges' => 'Seditious conspiracy (United Freedom Front) — the first conviction under the statute in the modern era',
                    'convicted' => 'Yes — served about ten years; released October 1, 1991',
                    'release_date' => '1991-10-01',
                ]],
            ],
            [
                'name' => 'Michael Markus',
                'first_name' => 'Michael',
                'last_name' => 'Markus',
                'aka' => 'Rattler',
                'match' => ['Rattler', 'Michael Markus'],
                'gender' => 'Male',
                'state' => 'North Dakota',
                'era' => '2010s',
                'ideologies' => ['Indigenous sovereignty', 'Environmental justice'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Michael "Rattler" Markus was a water protector (Akicita) at the Standing Rock #NoDAPL encampments resisting the Dakota Access Pipeline. He took a non-cooperating plea to a federal "civil disorder" charge — in exchange for prosecutors dropping a more serious "use of fire to commit a felony" count — for his role in fires set at police barricades during the raid on the resistance camps, and was sentenced on September 28, 2018 to 36 months in federal prison. He was released from federal supervision on November 2, 2022.',
                'cases' => [[
                    'charges' => 'Civil disorder (the #NoDAPL water-protector resistance at Standing Rock, 2016)',
                    'convicted' => 'Yes — pleaded guilty; sentenced to 36 months in federal prison',
                    'sentence' => '36 months (federal)',
                ]],
            ],
            [
                'name' => 'Michael Davis Africa',
                'first_name' => 'Michael',
                'last_name' => 'Africa',
                'aka' => 'Mike Africa Sr.',
                'match' => ['Michael Davis Africa', 'Mike Africa Sr'],
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Pennsylvania',
                'era' => '1970s',
                'ideologies' => ['Black liberation', 'MOVE'],
                'affiliation' => ['MOVE'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Michael Davis Africa, known as Mike Africa Sr., was one of the MOVE 9 — members of the Philadelphia MOVE organization convicted of third-degree murder in the death of police officer James Ramp during the August 8, 1978 police assault on MOVE\'s house in Powelton Village. He and the others were sentenced to 30 to 100 years. His wife, Debbie Sims Africa, also of the MOVE 9, was pregnant at the time and gave birth in jail to their son, Mike Africa Jr. After 40 years in prison, Mike Africa Sr. was released on parole in October 2018.',
                'cases' => [[
                    'institution_state' => 'Pennsylvania',
                    'charges' => 'Third-degree murder of Philadelphia police officer James Ramp (the August 8, 1978 police assault on the MOVE house)',
                    'convicted' => 'Yes — sentenced to 30 to 100 years; paroled October 2018 after 40 years',
                    'sentence' => '30 to 100 years',
                ]],
            ],
            $williamsburg('Dawud Abdur Rahman', 'Dawud', 'Rahman',
                ['Dawud Abdur Rahman', 'Dawud A. Rahman'],
                ' Dawud Abdur Rahman (Dawud A. Rahman) was paroled in October 2017 after more than 40 years in prison.',
                true),
            $williamsburg('Salih Ali Abdullah', 'Salih', 'Abdullah',
                ['Salih Ali Abdullah'],
                ' Salih Ali Abdullah served decades in prison and has since died; he is also memorialized on the Jericho Movement\'s ancestors list.',
                false),
        ];
    }
}
