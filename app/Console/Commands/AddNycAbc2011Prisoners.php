<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Imports political prisoners surfaced from the NYC ABC April 2011
 * PP/POW Listing (5th Edition). Idempotent — prisoner:add dedups.
 */
final class AddNycAbc2011Prisoners extends Command {
    protected $signature = 'archive:add-nyc-abc-2011-prisoners';
    protected $description = 'Add prisoners from the NYC ABC April 2011 (5th edition) listing';

    public function handle(): int {
        $path = database_path('data/nycabc-2011-prisoners.json');
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
