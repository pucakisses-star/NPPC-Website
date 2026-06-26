<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds Timothy Blunk's federal register number (09429-050) to his existing
 * record. Only the inmate_number field is touched — his bio and other fields
 * are left as-is. Idempotent.
 */
final class SetBlunkInmateNumber extends Command
{
    protected $signature = 'prisoners:set-blunk-inmate-number';

    protected $description = 'Set Timothy Blunk\'s federal register number (09429-050)';

    public function handle(): int
    {
        $p = Prisoner::withoutGlobalScopes()
            ->where('slug', 'timothy-blunk')
            ->orWhere('name', 'like', '%Blunk%')
            ->first();

        if (! $p) {
            $this->warn('Timothy Blunk not found — nothing to update.');

            return self::SUCCESS;
        }

        $p->inmate_number = '09429-050';
        $p->save();
        $this->info("Set inmate number on {$p->name}: #{$p->inmate_number}");

        return self::SUCCESS;
    }
}
