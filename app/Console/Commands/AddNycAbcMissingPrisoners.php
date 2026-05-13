<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Adds the high-confidence NOT-FOUND entries from the NYC ABC
 * birthday calendar — researched, with verified birth years and
 * accurate in_custody/released/death_date status. Reads
 * database/data/nycabc-missing-prisoners.json and forwards each
 * entry to `prisoner:add`, which handles dedup, institution
 * creation, and case wiring.
 */
final class AddNycAbcMissingPrisoners extends Command {
    protected $signature = 'archive:add-nycabc-missing';
    protected $description = 'Add the researched NYC ABC NOT-FOUND prisoners with real birthdates and status';

    public function handle(): int {
        $path = database_path('data/nycabc-missing-prisoners.json');
        $entries = json_decode(file_get_contents($path), true);

        $added = 0;
        $skipped = 0;
        foreach ($entries as $entry) {
            $name = $entry['name'];
            $this->line("\n— {$name} —");
            $code = Artisan::call('prisoner:add', ['json' => json_encode($entry)]);
            $this->line(trim(Artisan::output()));
            if ($code === self::SUCCESS) {
                $added++;
            } else {
                $skipped++;
            }
        }

        $this->info("\nAdded: {$added}    Skipped: {$skipped}");

        return self::SUCCESS;
    }
}
