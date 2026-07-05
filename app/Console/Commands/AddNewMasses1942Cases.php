<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * New Masses mining, batch 10 — 1942.
 *
 * 1942 is a low-yield year: with the US now in the war and the CPUSA backing
 * the war effort, New Masses turns to the second front, war production, and the
 * anti-fascist struggle abroad. The domestic class-war prisoners it names are
 * mostly already in the database — Earl Browder (freed by Roosevelt in May
 * 1942), Pedro Albizu Campos, the King–Ramsay–Conner (Point Lobos) defendants,
 * Morris Schappes, Anita Whitney, William Schneiderman, and the Oklahoma City
 * criminal-syndicalism defendants — and are skipped. Odell Waller, the year's
 * peak case, was electrocuted July 2, 1942 (a martyr, not a living prisoner),
 * and Stanley Nowak's denaturalization indictment carried no incarceration.
 *
 * This adds the genuinely-new US class-war prisoners of 1942: three racial
 * "rape"-frame death-row cases New Masses fought as legal lynchings — Festus
 * Coleman of San Francisco, William Wellman of North Carolina, and the three
 * Black soldiers condemned at Alexandria, Louisiana — plus the Puerto Rican
 * nationalist Juan Antonio Corretjer, released from Atlanta in 1942 after a
 * six-year sedition term.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddNewMasses1942Cases extends Command
{
    protected $signature = 'prisoners:add-new-masses-1942';

    protected $description = 'Add the genuinely-new US class-war prisoners surfaced mining New Masses 1942 (Festus Coleman, William Wellman, the three Alexandria LA soldiers, and Juan Antonio Corretjer)';

    public function handle(): int
    {
        $people = [];
        $mk = function (array $p, array $dates = []) use (&$people) {
            $people[] = ['payload' => $p, 'dates' => $dates];
        };

        // ── CALIFORNIA — FESTUS COLEMAN FRAME-UP ────────────────────────
        $mk([
            'name' => 'Festus Coleman', 'first_name' => 'Festus', 'last_name' => 'Coleman',
            'description' => "Festus Coleman was a young Black San Franciscan sentenced to sixty-five years in prison in 1942 on a trumped-up charge of 'rape and robbery.' New Masses and the International Labor Defense fought the case as a frame-up: Coleman was seized after an altercation with an army officer he had stumbled upon in a park, and a defense campaign for his freedom ran through the war years.",
            'state' => 'California', 'gender' => 'Male', 'race' => 'Black',
            'ideologies' => ['Civil rights'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Convicted on a trumped-up 'rape and robbery' charge after a fight with an army officer.",
                'convicted' => 'Convicted, 1942',
                'sentence' => 'Sixty-five years.',
                'institution_city' => 'San Francisco', 'institution_state' => 'California',
            ]],
        ], ['incarceration_date' => [1942, null, null]]);

        // ── NORTH CAROLINA — WILLIAM WELLMAN FRAME-UP ───────────────────
        $mk([
            'name' => 'William Wellman', 'first_name' => 'William', 'last_name' => 'Wellman',
            'description' => "William Wellman was a Black laborer sentenced to hang in North Carolina in 1942 on the familiar 'rape' charge, which New Masses reported as a legal lynching — Wellman had been working on a government construction job some four hundred miles away at the time of the alleged crime. After thousands of protest letters, Governor J. M. Broughton granted him a sixty-day reprieve in December 1942.",
            'state' => 'North Carolina', 'gender' => 'Male', 'race' => 'Black',
            'ideologies' => ['Civil rights'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Sentenced to death on a 'rape' charge despite working 400 miles away at the time.",
                'convicted' => 'Convicted, 1942',
                'sentence' => 'Death; a 60-day reprieve granted after mass protest.',
                'institution_state' => 'North Carolina',
            ]],
        ], ['incarceration_date' => [1942, null, null]]);

        // ── LOUISIANA — ALEXANDRIA SOLDIERS DEATH-ROW FRAME-UP ──────────
        $laBase = "was one of three Black soldiers convicted by a civil court in Alexandria, Louisiana in 1942 on a 'rape' charge — the complainant a white woman — and scheduled to die on October 30, 1942. New Masses reported the case as a wartime legal lynching of servicemen and joined the protest campaign for a stay.";
        foreach ([
            ['Richard Adams', 'Richard', 'Adams'],
            ['John W. Bordenave', 'John', 'Bordenave'],
            ['Lawrence Mitchell', 'Lawrence', 'Mitchell'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} {$laBase}",
                'state' => 'Louisiana', 'gender' => 'Male', 'race' => 'Black',
                'ideologies' => ['Civil rights'],
                'era' => '1940s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => "Convicted by a civil court on a 'rape' charge and condemned to death.",
                    'convicted' => 'Convicted, 1942',
                    'sentence' => 'Death; scheduled for October 30, 1942.',
                    'institution_city' => 'Alexandria', 'institution_state' => 'Louisiana',
                ]],
            ], ['incarceration_date' => [1942, null, null]]);
        }

        // ── PUERTO RICO — NATIONALIST PARTY ─────────────────────────────
        $mk([
            'name' => 'Juan Antonio Corretjer', 'first_name' => 'Juan', 'last_name' => 'Corretjer',
            'description' => "Juan Antonio Corretjer was a Puerto Rican poet and nationalist leader, a close associate of Pedro Albizu Campos, imprisoned in the federal penitentiary at Atlanta as an aftermath of the US repression of the Nationalist Party following the 1935–36 events. New Masses noted his release in 1942 after a six-year term and carried his article 'Trumpet of Lares.'",
            'state' => 'Puerto Rico', 'gender' => 'Male',
            'ideologies' => ['Puerto Rican independence', 'Nationalism'],
            'affiliation' => ['Nationalist Party of Puerto Rico'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Imprisoned in the federal repression of the Puerto Rican Nationalist Party.',
                'convicted' => 'Convicted, 1936',
                'sentence' => 'Six-year term; released 1942.',
                'institution_name' => 'United States Penitentiary, Atlanta',
                'institution_city' => 'Atlanta', 'institution_state' => 'Georgia',
            ]],
        ], ['incarceration_date' => [1936, null, null], 'release_date' => [1942, null, null]]);

        // ── INSERT ───────────────────────────────────────────────────────
        $added = 0;
        foreach ($people as $person) {
            $payload = $person['payload'];
            $payload['in_custody'] = false;
            if (! array_key_exists('released', $payload)) {
                $payload['released'] = true;
            }

            $existing = Prisoner::withoutGlobalScopes()
                ->where('name', 'like', '%'.$payload['first_name'].'%')
                ->where('name', 'like', '%'.$payload['last_name'].'%')
                ->first();
            if ($existing) {
                $this->line("  already in database as \"{$existing->name}\" — skipping {$payload['name']}.");

                continue;
            }

            $this->call('prisoner:add', ['json' => json_encode($payload)]);

            $prisoner = Prisoner::withoutGlobalScopes()->where('name', $payload['name'])->first();
            if (! $prisoner) {
                continue;
            }
            $prisoner->in_custody = false;
            $prisoner->released = $payload['released'];
            $prisoner->save();

            $case = $prisoner->cases()->first();
            if ($case && ! empty($person['dates'])) {
                foreach ($person['dates'] as $field => [$y, $m, $d]) {
                    $case->setPartialDate($field, $y, $m, $d);
                }
                $case->save();
            }
            $added++;
        }

        $this->info("\nDone. Processed {$added} of ".count($people)." New Masses 1942 prisoner(s).");

        return self::SUCCESS;
    }
}
