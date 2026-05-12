<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Marks every prisoner with era='1910s' as released and not in custody.
 * These are the WWI Espionage Act prisoners imported from Kohn (1994);
 * all are long deceased, so "released" is the correct historical state.
 */
final class Release1910sPrisoners extends Command {
    protected $signature = 'archive:release-1910s-prisoners';
    protected $description = 'Set released=true / in_custody=false on every era=1910s prisoner';

    public function handle(): int {
        $updated = Prisoner::where('era', '1910s')->update([
            'in_custody' => false,
            'released' => true,
        ]);

        $this->info("Updated {$updated} prisoners with era=1910s.");

        return self::SUCCESS;
    }
}
