<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds 4 prisoners surfaced from the NMAC 1980 Political Prisoners
 * report: the Charlotte Three (Jim Grant, T.J. Reddy, Charles Parker)
 * and Attica Brother John Boncore Hill / Dacajewiah.
 */
final class AddCharlotteThreeAtticaHill extends Command {
    protected $signature = 'archive:add-charlotte-three-attica-hill';
    protected $description = 'Add Charlotte Three + John Hill (Attica) from NMAC 1980 report';

    public function handle(): int {
        $path = database_path('data/charlotte-three-attica-hill.json');
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
