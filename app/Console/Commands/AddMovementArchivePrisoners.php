<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds political prisoners surfaced from a Rainbow Coalition / Puerto Rican
 * solidarity movement archive finding aid:
 *
 *   - Puerto Rican independence: William "Guillermo" Morales (FALN; in exile in
 *     Cuba), Carlos Feliciano (MIRA), Eduardo "Pancho" Cruz, Juan Otero
 *   - Rainbow Coalition: José "Cha Cha" Jiménez (Young Lords, Chicago),
 *     Pablo "Yoruba" Guzmán (Young Lords, NY), Tom Dostou & Chuck Armsbury
 *     (Patriot Party), Bobby Rush (Black Panther Party)
 *
 * Charges/sentences are drawn from the archival materials and established
 * history; the obscure cases (Cruz, Otero, Dostou) are stored as modest records
 * with sparse fields noted. Idempotent — prisoner:add refuses duplicates by name.
 */
final class AddMovementArchivePrisoners extends Command
{
    protected $signature = 'prisoners:add-movement-archive';

    protected $description = 'Add Rainbow Coalition / Puerto Rican movement prisoners from the archive finding aid';

    public function handle(): int
    {
        $path = database_path('data/movement-archive-prisoners.json');
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
            $name = $payload['name'] ?? '(unnamed)';
            $exit = $this->call('prisoner:add', ['json' => json_encode($payload)]);
            if ($exit === self::SUCCESS) {
                $this->info("ADD: {$name}");
                $added++;
            } else {
                $skipped++;
            }
        }

        $this->info("\nDone. added={$added} skipped={$skipped}");

        return self::SUCCESS;
    }
}
