<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Imports 5 inter-war / early Cold War political prisoners surfaced from
 * Robert Justin Goldstein (ed.), "Little 'Red Scares': Anti-Communism
 * and Political Repression in the United States, 1921–1946" (Ashgate, 2014).
 *
 * Only includes prisoners who actually served jail time:
 *   - Morris U. Schappes (NY Rapp-Coudert perjury, 13 months Rikers, 1943-45)
 *   - Ward H. Rodgers (Arkansas STFU criminal anarchy, 6 months 1935)
 *   - Benjamin Gitlow (NY criminal anarchy, ~3 yrs Sing Sing, Gitlow v. NY 1925)
 *   - Edward K. Barsky (JAFRC HUAC contempt, 6 mo FCI Petersburg, 1950)
 *   - Helen R. Bryan (JAFRC HUAC contempt, 3 mo FRW Alderson, 1950)
 */
final class AddLittleRedScaresPrisoners extends Command {
    protected $signature = 'archive:add-little-red-scares-prisoners';
    protected $description = 'Add inter-war / early Cold War prisoners from Goldstein, Little Red Scares (2014)';

    public function handle(): int {
        $path = database_path('data/little-red-scares-prisoners.json');
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

        $this->info("\nDone. Added={$added} Skipped={$skipped}");

        return self::SUCCESS;
    }
}
