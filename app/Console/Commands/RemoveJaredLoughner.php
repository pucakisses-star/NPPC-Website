<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Removes Jared Lee Loughner from the database. He is not a political
 * prisoner — he was convicted of the 2011 Tucson shooting that killed
 * six people and wounded Rep. Gabby Giffords, almost certainly added
 * by accident during one of the Goldstein-era bulk imports.
 */
final class RemoveJaredLoughner extends Command {
    protected $signature = 'archive:remove-jared-loughner';
    protected $description = 'Delete the Jared Lee Loughner prisoner record (not a political prisoner)';

    public function handle(): int {
        $p = Prisoner::where('name', 'like', '%Jared%Loughner%')->first();
        if (! $p) {
            $this->info('No Jared Loughner record found.');

            return self::SUCCESS;
        }
        $name = $p->name;
        $p->cases()->delete();
        $p->delete();
        $this->info("Deleted: {$name}");

        return self::SUCCESS;
    }
}
