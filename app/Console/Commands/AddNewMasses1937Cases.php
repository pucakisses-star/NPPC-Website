<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * New Masses mining, batch 5 — 1937.
 *
 * 1937 was the ILD Labor Defender's final year and is the most redundant of
 * the weekly volumes: the marquee cases are all already in the database
 * (Mooney & Billings, Herndon, the Scottsboro defendants, the Gallup NM
 * miners, the King-Ramsay-Conner and Modesto maritime frame-ups, McNamara,
 * Ned Cobb, the Harlan "Battle of Evarts" lifers, John Catchings, Ernest
 * Mullins, Florence Blaylock, Lorenzo Puentes, Pedro Albizu Campos, and the
 * first two Ponce Massacre Nationalists — Julio Pinto Gandía and Lorenzo
 * Piñero). All skipped.
 *
 * This adds the genuinely-new US class-war prisoners of 1937: the local
 * "Little Steel" strike arrests that the Labor Defender did not individually
 * record (the Youngstown, Ohio SWOC men and the Chicago/Johnstown organizers
 * John Riffe and Andy Ogando), the St. Louis dye-workers' union officers
 * beaten and jailed by the police "bombing squad," and the eight additional
 * Ponce Massacre murder-trial defendants held in the La Princesa fortress
 * beyond the two already recorded.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddNewMasses1937Cases extends Command
{
    protected $signature = 'prisoners:add-new-masses-1937';

    protected $description = 'Add the genuinely-new US class-war prisoners surfaced mining New Masses 1937 (the Youngstown Little Steel arrests, SWOC organizers, the St. Louis dye-workers, and eight more Ponce Massacre defendants)';

    public function handle(): int
    {
        $people = [];
        $mk = function (array $p, array $dates = []) use (&$people) {
            $people[] = ['payload' => $p, 'dates' => $dates];
        };

        // ── LITTLE STEEL — YOUNGSTOWN, OHIO (SWOC/CIO, June 1937) ───────
        $mk([
            'name' => 'Robert Burke', 'first_name' => 'Robert', 'last_name' => 'Burke',
            'description' => "Robert \"Bob\" Burke was a former Republic Steel worker, SWOC organizer and ex-American Student Union leader who was framed during the 1937 Little Steel strike in Youngstown, Ohio for the June 9 shooting of a strikebreaker at the Market Street Bridge. Arrested on a picket's sworn testimony and charged with 'shooting with intent to kill,' he was held under $1,500 bail; the union held that the shot actually came from a Republic Steel office window.",
            'state' => 'Ohio', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Steel Workers Organizing Committee'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Framed for 'shooting with intent to kill' in the Youngstown Little Steel strike.",
                'convicted' => 'Arrested, 1937',
                'sentence' => 'Held under $1,500 bail.',
                'institution_city' => 'Youngstown', 'institution_state' => 'Ohio',
            ]],
        ], ['arrest_date' => [1937, 6, null]]);

        $mk([
            'name' => 'Shorty Stevenson', 'first_name' => 'Shorty', 'last_name' => 'Stevenson',
            'description' => "Shorty Stevenson was a Steel Workers' Organizing Committee leader arrested with roughly 200 strikers on June 22, 1937 in the Youngstown, Ohio Little Steel strike. He was jailed for allegedly carrying a gun he 'had never seen until the sheriff offered it in evidence' and released on $5,000 bail.",
            'state' => 'Ohio', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Steel Workers Organizing Committee'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed on a planted-gun charge in the Youngstown Little Steel strike.',
                'convicted' => 'Arrested, 1937',
                'sentence' => 'Held on $5,000 bail.',
                'institution_city' => 'Youngstown', 'institution_state' => 'Ohio',
            ]],
        ], ['arrest_date' => [1937, 6, 22]]);

        $mk([
            'name' => 'Smiley Chatok', 'first_name' => 'Smiley', 'last_name' => 'Chatok',
            'description' => "Smiley Chatok was a striker in the 1937 Youngstown, Ohio Little Steel strike who was held about forty hours in custody for carrying a pocket-knife he had owned for years — one of the pretextual arrests used to break the strike.",
            'state' => 'Ohio', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Steel Workers Organizing Committee'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Held on a pretextual pocket-knife charge in the Little Steel strike.',
                'convicted' => 'Arrested, 1937',
                'sentence' => 'Held about forty hours.',
                'institution_city' => 'Youngstown', 'institution_state' => 'Ohio',
            ]],
        ], ['arrest_date' => [1937, 6, null]]);

        $mk([
            'name' => 'Arthur Connelly', 'first_name' => 'Arthur', 'last_name' => 'Connelly',
            'description' => "Arthur Connelly was a disabled man who volunteered as a Steel Workers' Organizing Committee secretary and was jailed during a sheriff's-deputy raid on the CIO strike headquarters on Poland Avenue in Youngstown, Ohio during the 1937 Little Steel strike.",
            'state' => 'Ohio', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Steel Workers Organizing Committee'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed in a raid on CIO strike headquarters.',
                'convicted' => 'Arrested, 1937',
                'sentence' => 'Jailed.',
                'institution_city' => 'Youngstown', 'institution_state' => 'Ohio',
            ]],
        ], ['arrest_date' => [1937, null, null]]);

        // ── SWOC ORGANIZERS — CHICAGO & JOHNSTOWN ───────────────────────
        $mk([
            'name' => 'John Riffe', 'first_name' => 'John', 'last_name' => 'Riffe',
            'description' => "John Riffe was a field director for the Steel Workers' Organizing Committee (SWOC, CIO) during the 1937 Little Steel strike. On May 26, 1937 — the first day of the Republic Steel walkout in South Chicago — police broke up a mass picket line and arrested Riffe with roughly forty strikers. He later became a prominent national CIO figure.",
            'state' => 'Illinois', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Steel Workers Organizing Committee'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested breaking up the Republic Steel picket line, South Chicago.',
                'convicted' => 'Arrested, 1937',
                'sentence' => 'Held.',
                'institution_city' => 'Chicago', 'institution_state' => 'Illinois',
            ]],
        ], ['arrest_date' => [1937, 5, 26]]);

        $mk([
            'name' => 'Andy Ogando', 'first_name' => 'Andy', 'last_name' => 'Ogando',
            'description' => "Andy Ogando was a union steelworker arrested during the 1937 Little Steel strike against Bethlehem Steel's Cambria works in Johnstown, Pennsylvania. After a police attack on picketing strikers, Mayor Daniel J. Shields sentenced Ogando to 90 days in jail or a $100 fine — an instance of the municipal repression deployed for the steel companies.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Steel Workers Organizing Committee'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Sentenced after a police attack on Little Steel pickets at Johnstown.',
                'convicted' => 'Convicted, 1937',
                'sentence' => '90 days in jail or a $100 fine.',
                'institution_city' => 'Johnstown', 'institution_state' => 'Pennsylvania',
            ]],
        ], ['arrest_date' => [1937, null, null]]);

        // ── ST. LOUIS DYE-WORKERS (Local 20, Aug 1937) ──────────────────
        $mk([
            'name' => 'Matthew A. McLoughlin', 'first_name' => 'Matthew', 'last_name' => 'McLoughlin',
            'description' => "Matthew A. McLoughlin was secretary-treasurer of Cleaning & Dye House Workers' Union Local 20 (AFL) in St. Louis. On August 27, 1937 he was arrested and beaten by the St. Louis police 'bombing squad' with rubber hoses and paddles in an attempt to extort a confession about strike window-smashing; he was held about eighteen hours and hospitalized.",
            'state' => 'Missouri', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ["Cleaning & Dye House Workers' Union Local 20"],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested and beaten by the police bombing squad during a dye-workers strike.',
                'convicted' => 'Arrested, 1937',
                'sentence' => 'Held about eighteen hours; hospitalized.',
                'institution_city' => 'St. Louis', 'institution_state' => 'Missouri',
            ]],
        ], ['arrest_date' => [1937, 8, 27]]);

        $mk([
            'name' => 'Ted Graham', 'first_name' => 'Ted', 'last_name' => 'Graham',
            'description' => "Ted Graham was the business agent of Cleaning & Dye House Workers' Union Local 20 in St. Louis, arrested alongside Matthew McLoughlin in the August 1937 police roundup of the union's officers during a dye-workers' strike.",
            'state' => 'Missouri', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ["Cleaning & Dye House Workers' Union Local 20"],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Arrested in the roundup of the dye-workers' union officers.",
                'convicted' => 'Arrested, 1937',
                'sentence' => 'Held.',
                'institution_city' => 'St. Louis', 'institution_state' => 'Missouri',
            ]],
        ], ['arrest_date' => [1937, 8, null]]);

        $mk([
            'name' => 'Allen Flory', 'first_name' => 'Allen', 'last_name' => 'Flory',
            'description' => "Allen Flory was president of Cleaning & Dye House Workers' Union Local 20 in St. Louis, arrested with Matthew McLoughlin and Ted Graham in the August 1937 police action against the striking union's leadership.",
            'state' => 'Missouri', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ["Cleaning & Dye House Workers' Union Local 20"],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Arrested in the police action against the dye-workers' union leadership.",
                'convicted' => 'Arrested, 1937',
                'sentence' => 'Held.',
                'institution_city' => 'St. Louis', 'institution_state' => 'Missouri',
            ]],
        ], ['arrest_date' => [1937, 8, null]]);

        // ── PONCE MASSACRE MURDER-TRIAL DEFENDANTS (La Princesa, PR) ────
        $ponceBase = "was one of the Puerto Rican Nationalist Party members charged with the murder of two policemen killed during the police massacre of Nationalist marchers at Ponce on 21 March 1937 (Palm Sunday), in which police killed some nineteen unarmed civilians and wounded about two hundred. He was imprisoned roughly six months in the La Princesa fortress in San Juan under the Ponce Massacre murder indictment, though most defendants were not even present at the shooting; the mass prosecution under Gov. Blanton Winship's colonial administration was widely condemned as political repression of the independence movement.";
        foreach ([
            ['Plinio Graciani', 'Plinio', 'Graciani'],
            ['Tomás López de Victoria', 'Tomás', 'López de Victoria'],
            ['Casimiro Berenguer', 'Casimiro', 'Berenguer'],
            ['Martín González Ruiz', 'Martín', 'González Ruiz'],
            ['Elifaz Escobar', 'Elifaz', 'Escobar'],
            ['Luis Ángel Correa', 'Luis Ángel', 'Correa'],
            ['Santiago González', 'Santiago', 'González'],
            ['Luis Castro Quesada', 'Luis', 'Castro Quesada'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} {$ponceBase}",
                'state' => 'Puerto Rico', 'gender' => 'Male',
                'ideologies' => ['Nationalism', 'Anti-imperialism'],
                'affiliation' => ['Puerto Rican Nationalist Party'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged with murder in the mass prosecution following the Ponce Massacre.',
                    'convicted' => 'Held / on trial, 1937',
                    'sentence' => 'Imprisoned about six months in the La Princesa fortress.',
                    'institution_name' => 'La Princesa',
                    'institution_city' => 'San Juan', 'institution_state' => 'Puerto Rico',
                ]],
            ], ['arrest_date' => [1937, 3, 21]]);
        }

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

        $this->info("\nDone. Processed {$added} of ".count($people)." New Masses 1937 prisoner(s).");

        return self::SUCCESS;
    }
}
