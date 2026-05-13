<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds 11 U.S. political prisoners surfaced from phillyabc.org's
 * letter-writing roster who are not already in NPPC:
 *
 *   Jamil Abdullah Al-Amin (H. Rap Brown)
 *   Jessica Reznicek (DAPL sabotage)
 *   Dan Baker (FL antifascist veteran)
 *   Urooj Rahman (NYPD Molotov, NY)
 *   Lore-Elisabeth Blumenthal (Philadelphia)
 *   Anthony "Ant" Smith (Philadelphia)
 *   Brian "Peppy" DiPippa (Pittsburgh)
 *   Malik Muhammad (Portland OR)
 *   Fran Thompson (Missouri)
 *   Jesse "Tall Can" Cannon (San Diego)
 *   Calla Walsh (Merrimack 4)
 *
 * Idempotent — prisoner:add dedups by name. International prisoners
 * (Aldama in Mexico, Shone in UK, Abdallah in France) and prisoners
 * already in NPPC (Ronald Reed, Kojo Sababu, Alvaro Hernandez, Alex
 * Stokes) were filtered out from Philly ABC's roster before import.
 */
final class AddPhillyAbcPrisoners extends Command {
    protected $signature = 'archive:add-philly-abc-prisoners';
    protected $description = 'Add prisoners surfaced from phillyabc.org letter-writing roster';

    public function handle(): int {
        $path = database_path('data/philly-abc-prisoners.json');
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
