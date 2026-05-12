<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds the three named Coxey's Army defendants jailed for the May 1,
 * 1894 march on the U.S. Capitol: Jacob S. Coxey, Carl Browne, and
 * Christopher Columbus Jones. All three convicted of carrying banners
 * and walking on Capitol grass; sentenced May 21, 1894 to 20 days in
 * the D.C. jail; released June 10, 1894.
 *
 * Lewis C. Fry of Fry's Army is intentionally not included — there is
 * no public-source confirmation that he served a custodial sentence.
 *
 * Idempotent — prisoner:add dedups by name.
 */
final class AddCoxeysArmyPrisoners extends Command {
    protected $signature = 'archive:add-coxeys-army-prisoners';
    protected $description = 'Add Coxey, Browne, and Jones (Coxey\'s Army May 1894 Capitol arrests)';

    public function handle(): int {
        $path = database_path('data/coxeys-army-prisoners.json');
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
