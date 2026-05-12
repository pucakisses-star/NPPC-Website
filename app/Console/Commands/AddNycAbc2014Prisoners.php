<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Imports political prisoners surfaced from the NYC ABC November 2014
 * PP/POW Listing booklet (Edition 9.7). Idempotent — prisoner:add dedups
 * against existing records by name.
 */
final class AddNycAbc2014Prisoners extends Command {
    protected $signature = 'archive:add-nyc-abc-2014-prisoners';
    protected $description = 'Add prisoners from the NYC ABC November 2014 PP/POW listing';

    public function handle(): int {
        $path = database_path('data/nycabc-2014-prisoners.json');
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
