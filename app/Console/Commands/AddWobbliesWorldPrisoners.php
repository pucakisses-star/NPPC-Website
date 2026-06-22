<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds U.S.-imprisoned Wobblies (and allied PLM / Ghadar radicals jailed by the
 * U.S. state) surfaced from "Wobblies of the World: A Global History of the IWW"
 * (ed. Cole, Struthers, Zimmer, 2017): the WWI Espionage Act / Leavenworth
 * defendants (Ben Fletcher, Ralph Chaplin, George Andreytchine, Manuel Rey, the
 * Flores Magón brothers, Librado Rivera, Taraknath Das), the 1913 Wheatland
 * hop-riot case (Ford and Suhr), New York criminal-anarchy cases (Paivio,
 * Alonen), and Pacific Northwest free-speech fighters (Frenette, Rowan).
 *
 * Charges and sentences are drawn from the book; bios and dates are filled from
 * established history where well documented and omitted where not. Idempotent —
 * prisoner:add refuses duplicates by name.
 */
final class AddWobbliesWorldPrisoners extends Command
{
    protected $signature = 'prisoners:add-wobblies-world';

    protected $description = 'Add U.S.-imprisoned Wobblies surfaced from "Wobblies of the World"';

    public function handle(): int
    {
        $path = database_path('data/wobblies-world-prisoners.json');
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
