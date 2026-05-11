<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * One-shot: rename the existing imported Kohn era from
 * "World War I (Espionage & Sedition Acts)" to "1910s".
 */
final class RenameKohnEra extends Command {
    protected $signature = 'archive:rename-kohn-era';
    protected $description = 'Rename Kohn prisoners\' era from "World War I (Espionage & Sedition Acts)" to "1910s"';

    public function handle(): int {
        $count = Prisoner::where('era', 'World War I (Espionage & Sedition Acts)')
            ->update(['era' => '1910s']);

        $this->info("Renamed era on {$count} prisoners to '1910s'.");

        return self::SUCCESS;
    }
}
