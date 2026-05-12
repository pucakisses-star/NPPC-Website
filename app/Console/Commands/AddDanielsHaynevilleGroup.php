<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds Jonathan Daniels and 9 of the protesters arrested with him in
 * Fort Deposit, Alabama on August 14, 1965 and held in the Lowndes
 * County Jail in Hayneville for six days before being released without
 * bond on August 20, 1965 — the day special deputy Tom Coleman shot
 * Daniels dead and wounded Father Richard Morrisroe at Varner's Cash
 * Store.
 *
 * Loads database/data/daniels-hayneville-group.json (10 entries) and
 * calls the existing idempotent prisoner:add for each.
 */
final class AddDanielsHaynevilleGroup extends Command {
    protected $signature = 'archive:add-daniels-hayneville-group';
    protected $description = 'Add Jonathan Daniels + co-detainees from the Fort Deposit/Hayneville 1965 jailing';

    public function handle(): int {
        $path = database_path('data/daniels-hayneville-group.json');
        if (! is_file($path)) {
            $this->error("Data file not found: {$path}");

            return self::FAILURE;
        }

        $payloads = json_decode(file_get_contents($path), true);
        if (! is_array($payloads)) {
            $this->error('Could not parse JSON.');

            return self::FAILURE;
        }

        $added = 0;
        $skipped = 0;
        foreach ($payloads as $payload) {
            $name = $payload['name'];
            $exit = $this->call('prisoner:add', ['json' => json_encode($payload)]);
            if ($exit === self::SUCCESS) {
                $this->info("ADD: {$name}");
                $added++;
            } else {
                $skipped++;
            }
        }

        $this->info("\nDone. Added={$added} Skipped={$skipped} (skipped = duplicates by name)");

        return self::SUCCESS;
    }
}
