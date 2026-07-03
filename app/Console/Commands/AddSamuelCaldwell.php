<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds Samuel R. Caldwell, the first person in the United States arrested and
 * convicted for selling marijuana under the Marihuana Tax Act of 1937, held at
 * USP Leavenworth. Complements the other Leavenworth prisoners in the database.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddSamuelCaldwell extends Command
{
    protected $signature = 'prisoners:add-samuel-caldwell';

    protected $description = 'Add Samuel R. Caldwell, first person convicted under the 1937 Marihuana Tax Act';

    public function handle(): int
    {
        $people = [
            [
                'payload' => [
                    'name' => 'Samuel R. Caldwell',
                    'first_name' => 'Samuel',
                    'last_name' => 'Caldwell',
                    'description' => "Samuel R. Caldwell was the first person in the United States arrested and convicted for selling marijuana under the Marihuana Tax Act of 1937. Arrested in Denver within a day of the act taking effect, he was sentenced to four years at the United States Penitentiary, Leavenworth, and released from custody in 1940 after serving three years. His case is often cited as the opening prosecution of federal marijuana prohibition.",
                    'state' => 'Colorado',
                    'gender' => 'Male',
                    'era' => '1930s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Convicted of selling marijuana under the Marihuana Tax Act of 1937 — the first such federal conviction in the United States.',
                        'convicted' => 'Convicted, 1937',
                        'sentence' => 'Four years at USP Leavenworth; released in 1940 after serving three years.',
                        'institution_name' => 'United States Penitentiary, Leavenworth',
                        'institution_city' => 'Leavenworth',
                        'institution_state' => 'Kansas',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1937, 10, null], 'release_date' => [1940, null, null]],
            ],
        ];

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

        $this->info("\nDone. Processed {$added} prisoner(s).");

        return self::SUCCESS;
    }
}
