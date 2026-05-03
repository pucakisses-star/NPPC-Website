<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

class RecalculatePrisonerAges extends Command
{
    protected $signature = 'prisoners:recalculate-ages';
    protected $description = 'Touch every prisoner so the stored age column gets recomputed by the saving hook.';

    public function handle(): int
    {
        $updated = 0;
        $skipped = 0;

        Prisoner::query()->chunkById(200, function ($prisoners) use (&$updated, &$skipped) {
            foreach ($prisoners as $p) {
                if (! $p->birthdate) {
                    $skipped++;
                    continue;
                }
                $p->save();
                $updated++;
            }
        });

        $this->info("Recalculated ages for {$updated} prisoner(s); skipped {$skipped} with no birthdate set.");

        return self::SUCCESS;
    }
}
