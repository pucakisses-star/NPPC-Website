<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * New Masses mining, batch 6 — 1938.
 *
 * 1938 is the first year past the ILD Labor Defender's run, but New Masses had
 * turned heavily to the Spanish Civil War, the Moscow trials, and the Dies
 * Committee, so domestic class-war prisoner reportage is sparse and the
 * standing cases (Mooney & Billings, Scottsboro, Ciambrelli, Charles Bock,
 * Alf White, Krumbein, the 1930 hunger-march leaders, the Dreiser committee)
 * are all already in the database. Those are skipped.
 *
 * This adds the genuinely-new US class-war prisoners of 1938: the Vermilion
 * County (Westville/Danville), Illinois miners jailed under the state
 * criminal-syndicalism law in a vigilante frame-up; Jeff Burkitt, jailed six
 * months in the Jersey City free-speech fight against Mayor Hague; and the
 * four Fort Lauderdale, Florida Communists seized, jailed and beaten in a
 * Klan-linked police raid.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddNewMasses1938Cases extends Command
{
    protected $signature = 'prisoners:add-new-masses-1938';

    protected $description = 'Add the genuinely-new US class-war prisoners surfaced mining New Masses 1938 (the Vermilion County IL miners, the Jersey City free-speech fight, and the Fort Lauderdale FL raid)';

    public function handle(): int
    {
        $people = [];
        $mk = function (array $p, array $dates = []) use (&$people) {
            $people[] = ['payload' => $p, 'dates' => $dates];
        };

        // ── VERMILION COUNTY, ILLINOIS — CRIMINAL-SYNDICALISM FRAME-UP ──
        $ilBase = "was a Progressive miner of Westville, Vermilion County, Illinois, jailed in early 1938 and charged under the Illinois criminal-syndicalism law after a vigilante campaign by the American Legion and the 'American Patriotic Club' against the striking miners of the Danville coalfields. The International Labor Defense fought the case as a frame-up, and it was referred to the La Follette Civil Liberties Committee.";
        foreach ([
            ['John Sloan', 'John', 'Sloan', " (This is the Illinois miner, not the Ashcan School painter of the same name.) First held for 'inciting to riot,' John Sloan {$ilBase}"],
            ['Ernest Giuliani', 'Ernest', 'Giuliani', "Ernest Giuliani {$ilBase} His surname appears variously as Giuliani/Guiliani in the sources."],
            ['Frank Suchaczewski', 'Frank', 'Suchaczewski', "Frank Suchaczewski {$ilBase}"],
        ] as [$name, $first, $last, $bio]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => $bio,
                'state' => 'Illinois', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['Progressive Miners of America'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged under the Illinois criminal-syndicalism law in a vigilante frame-up of striking miners.',
                    'convicted' => 'Jailed, 1938',
                    'sentence' => 'Jailed awaiting trial.',
                    'institution_city' => 'Danville', 'institution_state' => 'Illinois',
                ]],
            ], ['arrest_date' => [1938, 1, null]]);
        }

        // ── NEW JERSEY — JERSEY CITY FREE-SPEECH FIGHT (Hague) ──────────
        $mk([
            'name' => 'Jeff Burkitt', 'first_name' => 'Jeff', 'last_name' => 'Burkitt',
            'description' => "Jeff Burkitt was jailed for six months for attempting to speak in Journal Square, Jersey City, New Jersey, in defiance of Mayor Frank Hague's ban on public assemblies — part of the notorious 1938 Jersey City free-speech fight against the Hague machine, which also saw Norman Thomas and Congressman Jerry O'Connell forcibly expelled from the city.",
            'state' => 'New Jersey', 'gender' => 'Male',
            'ideologies' => ['Civil liberties'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Jailed for attempting to speak in defiance of Mayor Hague's assembly ban.",
                'convicted' => 'Convicted, 1938',
                'sentence' => 'Six months.',
                'institution_city' => 'Jersey City', 'institution_state' => 'New Jersey',
            ]],
        ], ['arrest_date' => [1938, null, null]]);

        // ── FLORIDA — FORT LAUDERDALE POLICE RAID ───────────────────────
        $mk([
            'name' => 'David Butler', 'first_name' => 'David', 'last_name' => 'Butler',
            'description' => "David Butler was a 17-year-old Black Communist in Fort Lauderdale, Broward County, Florida, arrested on a trumped-up 'vagrancy' charge in a 1938 raid in which about fifteen Klan-affiliated police broke up a Communist Party meeting. Jailed and beaten with fists and blackjacks by Sheriff Walter Clark and a deputy to coerce testimony against his comrades, he was forced to repudiate his ILD lawyer and plead guilty, then fled town leaving a signed note exposing the coercion.",
            'state' => 'Florida', 'gender' => 'Male', 'race' => 'Black',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Arrested on a 'vagrancy' charge in the raid on a Communist meeting.",
                'convicted' => 'Jailed, 1938',
                'sentence' => 'Jailed and beaten to coerce a guilty plea.',
                'institution_city' => 'Fort Lauderdale', 'institution_state' => 'Florida',
            ]],
        ], ['arrest_date' => [1938, null, null]]);

        $mk([
            'name' => 'Rose Jackson', 'first_name' => 'Rose', 'last_name' => 'Jackson',
            'description' => "Rose Jackson was a 38-year-old white Communist Party organizer in Fort Lauderdale, Florida, arrested in the 1938 raid on a party meeting, handcuffed and marched through town to jail. Released without charge, she was re-arrested and jailed on a 'vagrancy' charge under $250 bond after issuing leaflets protesting the raid; the Fort Lauderdale defendants were eventually freed when the charges were dismissed.",
            'state' => 'Florida', 'gender' => 'Female',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Arrested in the raid, then re-jailed on a 'vagrancy' charge for protest leaflets.",
                'convicted' => 'Jailed, 1938',
                'sentence' => 'Held under $250 bond; charges later dismissed.',
                'institution_city' => 'Fort Lauderdale', 'institution_state' => 'Florida',
            ]],
        ], ['arrest_date' => [1938, null, null]]);

        $mk([
            'name' => 'Arthur Jackson', 'first_name' => 'Arthur', 'last_name' => 'Jackson',
            'description' => "Arthur Jackson, a white Communist and the husband of Rose Jackson, was arrested and jailed in the 1938 Fort Lauderdale, Florida raid on a party meeting. He was released without charges after a few days — a move intended to isolate the Black defendants who were held and beaten.",
            'state' => 'Florida', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested in the raid on a Communist meeting.',
                'convicted' => 'Jailed, 1938',
                'sentence' => 'Held a few days; released.',
                'institution_city' => 'Fort Lauderdale', 'institution_state' => 'Florida',
            ]],
        ], ['arrest_date' => [1938, null, null]]);

        $mk([
            'name' => 'Bob Davis', 'first_name' => 'Bob', 'last_name' => 'Davis',
            'description' => "Bob Davis was a Black Communist of Fort Lauderdale, Florida, in whose house the party meeting was held when it was raided by police in 1938. Jailed on the mass 'vagrancy' charge, he was taken from his cell at 3 a.m. and beaten by deputies. (This is a local Florida man, not the New York Communist Ben Davis.)",
            'state' => 'Florida', 'gender' => 'Male', 'race' => 'Black',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Jailed on the mass 'vagrancy' charge after the raid on his house.",
                'convicted' => 'Jailed, 1938',
                'sentence' => 'Jailed and beaten in custody.',
                'institution_city' => 'Fort Lauderdale', 'institution_state' => 'Florida',
            ]],
        ], ['arrest_date' => [1938, null, null]]);

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

        $this->info("\nDone. Processed {$added} of ".count($people)." New Masses 1938 prisoner(s).");

        return self::SUCCESS;
    }
}
