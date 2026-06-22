<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds political prisoners surfaced by OCR-reading all 26 issues of Breakthrough,
 * the journal of the Prairie Fire Organizing Committee (1977–1995), and
 * cross-referencing every name against the existing database. These are the
 * anti-imperialist prisoners the journal championed who were not yet recorded:
 *
 *  - FALN / Los Macheteros (Puerto Rican independence; mostly Clinton's 1999
 *    clemency): Oscar López Rivera, Alejandrina Torres, Elizam Escobar, Carmen
 *    Valentín, Carlos Alberto Torres, Ricardo Jiménez, Luis Rosa, Adolfo Matos,
 *    Alicia & Ida Luz Rodríguez, Dylcia Pagán, Edwin Cortés, Juan Segarra Palmer,
 *    Alberto Rodríguez.
 *  - Resistance Conspiracy / May 19th / Brink's: David Gilbert, Alan Berkman,
 *    Susan Rosenberg, Judith Clark, Laura Whitehorn, Linda Evans, Timothy Blunk,
 *    Kathy Boudin, Silvia Baraldini.
 *  - Ohio 7 / United Freedom Front: Raymond Levasseur, Thomas & Carol Manning,
 *    Jaan Laaman.
 *  - BLA / Black liberation: Herman Bell, Albert "Nuh" Washington, Mutulu Shakur,
 *    Mark Cook, Haki Malik Abdullah.
 *  - Native / Chicano: Standing Deer, Rita Silk-Nauni, Richard Mafundi Lake,
 *    Pedro Archuleta, Ricardo Romero.
 *
 * Dates were verified per person and omitted where only a year was known.
 * Idempotent — prisoner:add refuses duplicates by name, so re-running only adds
 * those still missing.
 */
final class AddBreakthroughPrisoners extends Command
{
    protected $signature = 'prisoners:add-breakthrough';

    protected $description = 'Add political prisoners surfaced from OCR of the Breakthrough (PFOC) journal';

    public function handle(): int
    {
        $path = database_path('data/breakthrough-prisoners.json');
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
