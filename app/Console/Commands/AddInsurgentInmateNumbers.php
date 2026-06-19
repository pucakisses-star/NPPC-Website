<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Fills in inmate numbers documented in THE INSURGENT (1986–89) "Write to the
 * POWs" directories for existing prisoner records that lack one. Most records
 * already carry a number; these are the gaps found by checking each against
 * the live site. Strictly non-destructive: a number is set ONLY when the
 * record's inmate_number is currently empty. Idempotent.
 */
final class AddInsurgentInmateNumbers extends Command
{
    protected $signature = 'prisoners:add-insurgent-inmate-numbers';

    protected $description = 'Fill missing inmate numbers from The Insurgent (only where empty)';

    /** slug => inmate number documented in The Insurgent */
    private const NUMBERS = [
        'carmen-valentin' => '88974-024',       // FALN / Puerto Rican POW
        'alberto-rodriguez-2' => '92150-024',   // FALN (1983 Chicago arrests)
        'carlos-ayes-suarez' => '03176-069',    // Los Macheteros / Hartford
        'julio-veras' => '00799-069',           // Puerto Rican political prisoner
        'dhoruba-bin-wahad' => '72-A-0639',     // BLA / NY (DIN)
        'mujahid-farid' => '79-A-0362',         // New Afrikan / NY (DIN)
        'elmer-geronimo-pratt' => 'B-40319',    // BPP/BLA (CA, San Quentin)
    ];

    public function handle(): int
    {
        $filled = 0;
        $skipped = 0;
        $missing = 0;

        foreach (self::NUMBERS as $slug => $number) {
            $p = Prisoner::withUnderReview()->where('slug', $slug)->first();
            if (! $p) {
                $this->warn("Not found: {$slug}");
                $missing++;

                continue;
            }
            if (! empty($p->inmate_number)) {
                $this->line("Already has a number, skipping: {$slug} ({$p->inmate_number})");
                $skipped++;

                continue;
            }
            $p->inmate_number = $number;
            $p->save();
            $this->info("Set {$slug} -> {$number}");
            $filled++;
        }

        $this->info("\nDone. Filled={$filled} Skipped(existing)={$skipped} NotFound={$missing}");

        return self::SUCCESS;
    }
}
