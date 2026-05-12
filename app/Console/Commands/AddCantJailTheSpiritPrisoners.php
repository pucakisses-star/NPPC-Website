<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Imports prisoners surfaced from "Can't Jail the Spirit: Political
 * Prisoners in the U.S." (Committee to End the Marion Lockdown, 1985).
 *
 * Coverage:
 *   - Native American: Standing Deer (Robert Wilson)
 *   - New Afrikan / BLA: Albert Nuh Washington, Johnny Imani Harris,
 *     Richard Mafundi Lake, Ramona Africa
 *   - Puerto Rican Nationalist FALN/Macheteros (17): Edwin Cortes,
 *     Elizam Escobar, Oscar Lopez Rivera, Ida Luz Rodriguez, Alejandrina
 *     Torres, Ricardo Jimenez, Dylcia Pagan, Carlos Alberto Torres,
 *     Haydee Beltran Torres, Alberto Rodriguez, Adolfo Matos, Luis Rosa,
 *     Alicia Rodriguez, Carmen Valentin, Dora Garcia, Jaime Delgado,
 *     Filiberto Ojeda Rios
 *   - Ohio 7 / United Freedom Front: Patricia Gros Levasseur, Raymond
 *     Luc Levasseur, Barbara Curzi-Laaman, Jaan Karl Laaman, Carol
 *     Manning, Tom Manning, Richard Charles Williams
 *   - Resistance Conspiracy / Brink's-era: Alan Berkman, Timothy Blunk
 *   - George Jackson Brigade / NW armed: Larry Giddings, Edward Mead
 *   - Irish republican: Joseph Patrick Doherty
 *   - Plowshares anti-nuclear: George Michael Ostensen
 */
final class AddCantJailTheSpiritPrisoners extends Command {
    protected $signature = 'archive:add-cant-jail-the-spirit-prisoners';
    protected $description = 'Add prisoners from CEML, Can\'t Jail the Spirit (1985)';

    public function handle(): int {
        $path = database_path('data/cant-jail-the-spirit-prisoners.json');
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

        $this->info("\nDone. Added={$added} Skipped={$skipped} (skipped = duplicates by name)");

        return self::SUCCESS;
    }
}
