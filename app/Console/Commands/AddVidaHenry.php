<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds First Sergeant Vida Henry, the leader of the 24th Infantry soldiers'
 * uprising during the Houston riot of 1917 (the Camp Logan mutiny). Unlike the
 * soldiers already recorded from that event, Henry was never captured or tried:
 * he died the night of the mutiny. Complements prisoners:add-houston-riot-soldiers.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddVidaHenry extends Command
{
    protected $signature = 'prisoners:add-vida-henry';

    protected $description = 'Add First Sergeant Vida Henry, leader of the 1917 Houston riot (Camp Logan mutiny)';

    public function handle(): int
    {
        $people = [
            [
                'payload' => [
                    'name' => 'Vida Henry',
                    'first_name' => 'Vida',
                    'last_name' => 'Henry',
                    'aka' => 'First Sergeant Vida Henry',
                    'description' => "First Sergeant Vida Henry, a nineteen-year veteran of the all-Black 24th Infantry Regiment and a leader in Company I, took command of the soldiers' uprising during the Houston riot of 1917 (the Camp Logan mutiny). On the night of 23 August 1917 — after Houston police enforcing Jim Crow had beaten and arrested men of the regiment — Henry formed a column of about 150 soldiers and led them on a march into the city. As the march broke up outside the San Felipe district, he shook hands with the remaining men, told them he intended to take his own life, and was found dead the next morning. Never captured or tried, he was not among the 118 soldiers court-martialed — of whom 19 were executed and 63 imprisoned for life — whose 110 convictions the U.S. Army set aside in 2023.",
                    'state' => 'Texas',
                    'gender' => 'Male',
                    'race' => 'Black',
                    'death_date' => '1917-08-23',
                    'affiliation' => ['24th Infantry Regiment'],
                    'era' => '1910s',
                    'in_custody' => false,
                    'released' => false,
                    'cases' => [[
                        'charges' => "Led the 24th Infantry soldiers' march on Houston during the 23 August 1917 riot (Camp Logan mutiny).",
                        'convicted' => 'Never tried — died the night of the mutiny, 23 August 1917',
                        'sentence' => 'Was never captured or court-martialed; found dead the morning after the uprising.',
                    ]],
                ],
                'dates' => [],
            ],
        ];

        $added = 0;
        foreach ($people as $person) {
            $payload = $person['payload'];
            $inCustody = $payload['in_custody'] ?? false;
            $released = $payload['released'] ?? ! $inCustody;
            $payload['in_custody'] = $inCustody;
            $payload['released'] = $released;

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
            $prisoner->in_custody = $inCustody;
            $prisoner->released = $released;
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
