<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds the nine New York 8+ federal RICO defendants (Black liberation
 * organizers arrested in the Oct 17-18 1984 FBI/NYPD JTTF raid in NYC
 * and tried in 1985 before Judge Kevin Thomas Duffy in SDNY): Coltrane
 * Chimurenga, Omowale Clay, Viola Plummer, Roger Wareham, Yvette
 * Kelley, Ruth Carter, Lateefah Carter, Robert Taylor, and Jose Rios.
 *
 * All nine were held without bail in federal custody (MCC New York)
 * under the just-enacted 1984 Bail Reform Act before being released
 * pending trial in early 1985 — meeting the project's "actually
 * served jail time" criterion. All were acquitted of every RICO and
 * conspiracy count in August 1985; only minor uncontested
 * weapons/false-ID counts produced convictions.
 *
 * Idempotent — prisoner:add dedups by name.
 */
final class AddNy8PlusPrisoners extends Command {
    protected $signature = 'archive:add-ny8-plus-prisoners';
    protected $description = 'Add the New York 8+ federal RICO defendants';

    public function handle(): int {
        $path = database_path('data/ny8-plus-prisoners.json');
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
