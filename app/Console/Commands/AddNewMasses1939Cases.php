<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * New Masses mining, batch 7 — 1939.
 *
 * 1939 is dominated by the fall of Republican Spain, the Nazi-Soviet Pact, the
 * Tom Mooney pardon, the Harry Bridges deportation hearings, and the Dies
 * Committee. The standing cases (Mooney, Billings, McNamara, Herndon,
 * Scottsboro, Albizu Campos, Amter, Browder, Sam Darcy) are all already in the
 * database, and the Bridges/Pritchett/Strecker cases are foreign-born
 * deportation matters — all skipped.
 *
 * This adds the genuinely-new US class-war prisoners of 1939: a young Alabama
 * UCAPAWA organizer framed for highway robbery; the Sioux City, Iowa Communist
 * "six" jailed under the state criminal-syndicalism law; two California CIO
 * farm-strike leaders (Marysville and Madera); and the Hollywood IATSE
 * rank-and-file leader Jeff Kibre.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddNewMasses1939Cases extends Command
{
    protected $signature = 'prisoners:add-new-masses-1939';

    protected $description = 'Add the genuinely-new US class-war prisoners surfaced mining New Masses 1939 (Willie Joe Hart, the Sioux City IA Communist six, two California CIO farm-strike leaders, and Hollywood\'s Jeff Kibre)';

    public function handle(): int
    {
        $people = [];
        $mk = function (array $p, array $dates = []) use (&$people) {
            $people[] = ['payload' => $p, 'dates' => $dates];
        };

        // ── ALABAMA — UCAPAWA FRAME-UP ──────────────────────────────────
        $mk([
            'name' => 'Willie Joe Hart', 'first_name' => 'Willie', 'last_name' => 'Hart',
            'description' => "Willie Joe Hart was a seventeen-year-old Black volunteer organizer for the United Cannery, Agricultural, Packing & Allied Workers of America (UCAPAWA-CIO) in Tallapoosa County, Alabama. In October 1938 he was arrested by Sheriff Corprew on a fabricated highway-robbery charge and, after what the defense called a farcical trial, sentenced to fifteen years — a frame-up meant to break the union's drive against landlord control of WPA relief jobs.",
            'state' => 'Alabama', 'gender' => 'Male', 'race' => 'Black',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['United Cannery, Agricultural, Packing & Allied Workers of America'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Framed on a highway-robbery charge to break the UCAPAWA relief-jobs drive.',
                'convicted' => 'Convicted, 1938',
                'sentence' => 'Fifteen years.',
                'institution_city' => 'Dadeville', 'institution_state' => 'Alabama',
            ]],
        ], ['incarceration_date' => [1938, 10, null]]);

        // ── IOWA — SIOUX CITY CRIMINAL-SYNDICALISM "SIX" (Sept 1939) ─────
        $iaBase = "was one of the 'Sioux City six' — Communists seized in Sioux City, Iowa in September 1939 and charged under the Iowa criminal-syndicalism law (inciting hostility against the government and belonging to an organization advocating its overthrow by force). Held on 'open charges' after pre-dawn raids, they were released on $500 bond each, with the International Labor Defense taking up the defense.";
        foreach ([
            ['Ted Baer', 'Ted', 'Baer', "Ted Baer was the Communist Party section organizer in Sioux City, Iowa, whose home was raided at 2:30 a.m. and who was jailed with a suitcase of literature. He {$iaBase}"],
            ['Bob Carson', 'Bob', 'Carson', "Bob Carson was state secretary of the Communist Party of Iowa, taken in the criminal-syndicalism sweep just after speaking at a meeting in the Jackson Hotel. He {$iaBase}"],
            ['Carl Martin', 'Carl', 'Martin', "Carl Martin {$iaBase} He wrote the letter recounting the arrests that reached New Masses."],
            ['Wilbur Howard', 'Wilbur', 'Howard', "Wilbur Howard {$iaBase}"],
        ] as [$name, $first, $last, $bio]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => $bio,
                'state' => 'Iowa', 'gender' => 'Male',
                'ideologies' => ['Communism'],
                'affiliation' => ['Communist Party'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged under the Iowa criminal-syndicalism law in the Sioux City raids.',
                    'convicted' => 'Arrested, 1939',
                    'sentence' => 'Held; released on $500 bond.',
                    'institution_city' => 'Sioux City', 'institution_state' => 'Iowa',
                ]],
            ], ['arrest_date' => [1939, 9, null]]);
        }

        // ── CALIFORNIA — CIO FARM STRIKES ───────────────────────────────
        $mk([
            'name' => 'Luke Hinman', 'first_name' => 'Luke', 'last_name' => 'Hinman',
            'description' => "Luke Hinman was a CIO organizer for the United Cannery, Agricultural, Packing and Allied Workers of America (UCAPAWA) who came to Marysville, California to aid the 1939 Earl Fruit Company strike. He was among roughly seventeen strikers arrested under the Yuba County anti-picketing ordinance — part of Sheriff McCoy's wave of about ninety-seven arrests that 'practically put an entire strike in jail.'",
            'state' => 'California', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['United Cannery, Agricultural, Packing & Allied Workers of America'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested under the Yuba County anti-picketing ordinance in the Marysville fruit strike.',
                'convicted' => 'Arrested, 1939',
                'sentence' => 'Held.',
                'institution_city' => 'Marysville', 'institution_state' => 'California',
            ]],
        ], ['arrest_date' => [1939, null, null]]);

        $mk([
            'name' => 'Carl Patterson', 'first_name' => 'Carl', 'last_name' => 'Patterson',
            'description' => "Carl Patterson was the chief leader of the UCAPAWA cotton pickers' strike in California's San Joaquin Valley in October 1939. He was jailed by Madera County authorities, who seized strike funds addressed to him as a pretext for the arrest; some twenty-one Madera cotton strikers were held in the county jail for about two weeks during the same crackdown.",
            'state' => 'California', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['United Cannery, Agricultural, Packing & Allied Workers of America'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Jailed in the Madera County crackdown on the UCAPAWA cotton strike.",
                'convicted' => 'Arrested, 1939',
                'sentence' => 'Held in the county jail.',
                'institution_city' => 'Madera', 'institution_state' => 'California',
            ]],
        ], ['arrest_date' => [1939, 10, null]]);

        // ── CALIFORNIA — HOLLYWOOD IATSE ────────────────────────────────
        $mk([
            'name' => 'Jeff Kibre', 'first_name' => 'Jeff', 'last_name' => 'Kibre',
            'description' => "Jeff Kibre led the rank-and-file reform movement in IATSE Local 37, the Hollywood studio-workers' union, against the mob-linked Bioff–Browne leadership. In 1939 he was arrested in Los Angeles on suspicion of criminal syndicalism, with police citing a copy of S. J. Perelman's 'Strictly from Hunger' found among his books as supposed evidence; he filed a $250,000 false-arrest and libel suit.",
            'state' => 'California', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['IATSE Local 37'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested in Los Angeles on suspicion of criminal syndicalism.',
                'convicted' => 'Arrested, 1939',
                'sentence' => 'Held; sued for false arrest.',
                'institution_city' => 'Los Angeles', 'institution_state' => 'California',
            ]],
        ], ['arrest_date' => [1939, null, null]]);

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

        $this->info("\nDone. Processed {$added} of ".count($people)." New Masses 1939 prisoner(s).");

        return self::SUCCESS;
    }
}
