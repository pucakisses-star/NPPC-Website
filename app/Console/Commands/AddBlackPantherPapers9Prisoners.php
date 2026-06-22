<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Ninth batch from reading The Black Panther — drawn from the December 25, 1971
 * issue:
 *
 *  - Roderick Kirby — a Wilmington, N.C. man convicted of assaulting an officer
 *    after he intervened to stop police harassing a 12-year-old, amid white-
 *    supremacist ("Rights of White People") mobilization in the city.
 *  - Clarence Jones — a New Orleans Black Panther arrested on a revived, shaky
 *    armed-robbery warrant the day before a rally.
 *  - The Winston-Salem "Long family" raid: Benny Long and 15-year-old Marvin
 *    Wilson, jailed after police forced their way into the family's home.
 *
 * (The "Adjustment Center Six" referenced in the same issue is another name for
 * the San Quentin Six, already recorded. Romaine Fitzgerald and Vincent Robinson
 * are already in the database.) Idempotent: skips any name already present.
 */
final class AddBlackPantherPapers9Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-9';

    protected $description = 'Add Black Panther newspaper prisoners from the Dec 25 1971 issue, batch 9';

    public function handle(): int
    {
        $added = 0;
        $skipped = 0;

        foreach ($this->records() as $r) {
            if (Prisoner::withUnderReview()->where('name', $r['name'])->exists()) {
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
        // The Winston-Salem "Long family" raid (shared case).
        $longRaid = function (string $name, string $first, string $last, string $descExtra): array {
            return [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'North Carolina',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "{$name} was jailed in Winston-Salem, North Carolina after city police, led by Officer R.A. Spillman, forced their way into the Long family home and beat its occupants with nightsticks.{$descExtra} Benny Long, his mother, and Marvin Wilson were taken to jail and charged with disorderly conduct, cursing and abusing police officers, and assault upon police officers; Benny (whose back was badly injured) and Marvin both needed hospital treatment. Documented in The Black Panther (December 25, 1971).",
                'cases' => [[
                    'institution_state' => 'North Carolina',
                    'charges' => 'Disorderly conduct, cursing and abusing police officers, and assault upon police officers (after Winston-Salem police forced their way into the Long family home)',
                ]],
            ];
        };

        return [
            [
                'name' => 'Roderick Kirby',
                'first_name' => 'Roderick',
                'last_name' => 'Kirby',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'North Carolina',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Roderick Kirby was a member of the Black community in Wilmington, North Carolina who, seeing police harassing a 12-year-old boy outside a school game, asked the officers why they were intimidating the youth. The police turned on Kirby; he defended himself and the boy, and about a dozen officers beat and arrested him, then claimed he had injured one of them. The confrontation came amid the mobilization of a white-supremacist paramilitary group, the "Rights of White People" (led by Leroy Gibson), and the North Carolina State Police against Wilmington\'s Black community. Kirby was convicted of assaulting an officer and sentenced to prison. Documented in The Black Panther (December 25, 1971).',
                'cases' => [[
                    'institution_state' => 'North Carolina',
                    'charges' => 'Assault on a police officer (after he intervened to stop police harassing a 12-year-old boy in Wilmington, N.C.)',
                    'convicted' => 'Yes — convicted of assaulting an officer and sentenced to prison',
                ]],
            ],
            [
                'name' => 'Clarence Jones',
                'first_name' => 'Clarence',
                'last_name' => 'Jones',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Louisiana',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['Black Panther Party'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Clarence Jones was a member of the Louisiana State Chapter of the Black Panther Party in New Orleans who, with Vincent Robinson, was well known for regularly delivering the Black Panther newspaper door to door. On November 3, 1971 — the day before a rally — New Orleans police arrested him on a warrant stemming from an old armed-robbery charge so weak that Judge Alcock had earlier set it aside; The Black Panther described it as part of the daily harassment of party members. Documented in The Black Panther (December 25, 1971).',
                'cases' => [[
                    'institution_state' => 'Louisiana',
                    'charges' => 'Armed robbery (a year-old charge, previously found too weak by Judge Alcock, revived as a warrant) — New Orleans',
                    'arrest_date' => '1971-11-03',
                ]],
            ],

            // ---- Winston-Salem "Long family" raid ----
            $longRaid('Benny Long', 'Benny', 'Long', ' Benny Long was knocked to the floor and beaten, suffering a badly injured back.'),
            $longRaid('Marvin Wilson', 'Marvin', 'Wilson', ' Marvin Wilson, fifteen years old and a friend of the family, had been in the kitchen with Mrs. Long when the police attacked.'),
        ];
    }
}
