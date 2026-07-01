<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Folds legacy prisoner era values into the canonical set: "1850s" and "1860s"
 * become "1800s", and "Contemporary" becomes "2020s". Iterates and saves each
 * affected record so the /api/prisoners cache invalidates. Idempotent.
 */
final class FoldLegacyEras extends Command
{
    protected $signature = 'prisoners:fold-legacy-eras';

    protected $description = 'Fold 1850s/1860s eras into 1800s and Contemporary into 2020s';

    /** old era value => new era value */
    private const MAP = [
        '1850s' => '1800s',
        '1860s' => '1800s',
        'Contemporary' => '2020s',
    ];

    public function handle(): int
    {
        $total = 0;

        foreach (self::MAP as $from => $to) {
            $prisoners = Prisoner::withoutGlobalScopes()->where('era', $from)->get();
            foreach ($prisoners as $prisoner) {
                $prisoner->era = $to;
                $prisoner->save();
                $total++;
            }
            $this->info("{$from} -> {$to}: {$prisoners->count()} prisoner(s)");
        }

        $this->info("\nDone. Normalized the era on {$total} prisoner(s).");

        return self::SUCCESS;
    }
}
