<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds three well-documented political prisoners held at the United States
 * Penitentiary, Leavenworth, who were missing from the database — the IWW
 * leader Vincent St. John (imprisoned after the 1918 Chicago Espionage Act
 * trial) and the Communist Party leaders Gus Hall and Gil Green (imprisoned
 * under the Smith Act in the 1950s).
 *
 * The database already holds the wider Leavenworth cohorts: the ~100 IWW
 * Chicago-trial Wobblies (SetLeavenworthInmateNumbers), the WWI conscientious
 * objectors (the Hofer brothers, Philip Grosser, Carl Haessler), the Mexican
 * anarchists Ricardo Flores Magón and Librado Rivera, and Puerto Rican
 * Nationalists — so this fills the gap left by three of the most prominent
 * individual leaders.
 *
 * Facts and Leavenworth terms verified against public sources. Idempotent:
 * prisoner:add refuses duplicates by name and the variant-name guard skips
 * anyone already recorded.
 */
final class AddLeavenworthPoliticalPrisoners extends Command
{
    protected $signature = 'prisoners:add-leavenworth-political-prisoners';

    protected $description = 'Add three Leavenworth political prisoners missing from the database (Vincent St. John, Gus Hall, Gil Green)';

    public function handle(): int
    {
        $people = [
            [
                'payload' => [
                    'name' => 'Vincent St. John',
                    'first_name' => 'Vincent',
                    'last_name' => 'St. John',
                    'aka' => 'The Saint',
                    'description' => "Vincent St. John, a hardrock miner known across the labor movement as \"The Saint,\" was general secretary-treasurer of the Industrial Workers of the World in its most militant years. In the government's wartime campaign against the union he was one of 101 Wobblies tried in the 1918 Chicago Espionage Act case — the longest criminal trial in American history to that point — and, though he had left the IWW's leadership before the war, was convicted with Big Bill Haywood and the rest. Stunned by his twenty-year sentence, he served about two and a half years at the United States Penitentiary, Leavenworth before a commutation won his release. He died in 1929.",
                    'gender' => 'Male',
                    'death_date' => '1929-06-20',
                    'ideologies' => ['Industrial unionism', 'Syndicalism'],
                    'affiliation' => ['Industrial Workers of the World'],
                    'era' => '1910s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Convicted in the 1918 Chicago mass trial of the IWW under the Espionage Act of 1917.',
                        'convicted' => 'Convicted, 1918; sentenced to 20 years',
                        'sentence' => 'Served about two and a half years at USP Leavenworth before his sentence was commuted.',
                        'institution_name' => 'United States Penitentiary, Leavenworth',
                        'institution_city' => 'Leavenworth',
                        'institution_state' => 'Kansas',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1918, null, null], 'release_date' => [1922, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Gus Hall',
                    'first_name' => 'Gus',
                    'last_name' => 'Hall',
                    'description' => "Gus Hall, the Minnesota Iron Range labor organizer who would lead the Communist Party USA for four decades, was one of the eleven party leaders convicted in the 1949 Foley Square trial under the Smith Act for \"conspiring to advocate\" the overthrow of the government. When the Supreme Court upheld the convictions in 1951 he jumped bail and went underground; captured in Mexico City that October, he received three additional years and served about five and a half years at the United States Penitentiary, Leavenworth before his release. He ran for president four times and led the party until 2000.",
                    'state' => 'New York',
                    'gender' => 'Male',
                    'ideologies' => ['Communism'],
                    'affiliation' => ['Communist Party USA'],
                    'era' => '1950s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Convicted in the 1949 Foley Square trial under the Smith Act, then given additional time for jumping bail in 1951.',
                        'convicted' => 'Convicted under the Smith Act, 1949; upheld in Dennis v. United States (1951)',
                        'sentence' => 'Five-year sentence plus three years for bail-jumping; served about five and a half years at USP Leavenworth.',
                        'institution_name' => 'United States Penitentiary, Leavenworth',
                        'institution_city' => 'Leavenworth',
                        'institution_state' => 'Kansas',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1951, 10, null], 'release_date' => [1957, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Gil Green',
                    'first_name' => 'Gil',
                    'last_name' => 'Green',
                    'description' => "Gil Green, a Chicago-born Communist who had led the Young Communist League, was among the party leaders convicted in the 1949 Foley Square Smith Act trial. When the convictions were upheld in 1951 he was one of four defendants who went underground rather than report to prison; he lived as a fugitive for nearly five years before surrendering in February 1956. He then served his term at the United States Penitentiary, Leavenworth until his release in July 1961, and later recounted the years in hiding in his memoir Cold War Fugitive.",
                    'state' => 'Illinois',
                    'gender' => 'Male',
                    'ideologies' => ['Communism'],
                    'affiliation' => ['Communist Party USA', 'Young Communist League'],
                    'era' => '1950s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Convicted in the 1949 Foley Square trial under the Smith Act; additional time for jumping bail in 1951.',
                        'convicted' => 'Convicted under the Smith Act, 1949',
                        'sentence' => 'Went underground in 1951, surrendered in February 1956, and served at USP Leavenworth until July 1961.',
                        'institution_name' => 'United States Penitentiary, Leavenworth',
                        'institution_city' => 'Leavenworth',
                        'institution_state' => 'Kansas',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1956, 2, 27], 'release_date' => [1961, 7, 29]],
            ],
        ];

        $added = 0;
        foreach ($people as $person) {
            $payload = $person['payload'];
            $payload['in_custody'] = false;
            if (! array_key_exists('released', $payload)) {
                $payload['released'] = true;
            }

            // Guard against variant-name duplicates: skip anyone whose first AND
            // last name both already appear in a record.
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

            // Backfill dates on the first case with honest precision.
            $case = $prisoner->cases()->first();
            if ($case && ! empty($person['dates'])) {
                foreach ($person['dates'] as $field => [$y, $m, $d]) {
                    $case->setPartialDate($field, $y, $m, $d);
                }
                $case->save();
            }
            $added++;
        }

        $this->info("\nDone. Processed {$added} Leavenworth political prisoner(s).");

        return self::SUCCESS;
    }
}
