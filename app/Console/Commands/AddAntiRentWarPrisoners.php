<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Imports four leading Anti-Rent War (1839-1845) prisoners convicted
 * in the wake of the August 7, 1845 killing of Delaware County
 * Undersheriff Osman N. Steele at the Moses Earle farm in Andes, NY:
 * Smith A. Boughton ("Big Thunder"), John Van Steenburgh, Edward
 * O'Connor, and Moses Earle. All four served time at Clinton State
 * Prison and were pardoned by Governor John Young in 1847.
 *
 * Idempotent — prisoner:add dedups by name.
 */
final class AddAntiRentWarPrisoners extends Command {
    protected $signature = 'archive:add-anti-rent-war-prisoners';
    protected $description = 'Add four leading Anti-Rent War (1845) prisoners';

    public function handle(): int {
        $path = database_path('data/anti-rent-war-prisoners.json');
        $payloads = json_decode(file_get_contents($path), true);
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
        $this->info("\nDone. Added={$added} Skipped={$skipped}");

        return self::SUCCESS;
    }
}
