<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Clears the era field on every prisoner whose era is not a numeric decade
 * label (e.g. "1910s", "1980s"). Categories like "Black Liberation",
 * "Anti-War", "Anti-Imperialist", etc. become NULL.
 */
final class ClearNonNumericEras extends Command {
    protected $signature = 'archive:clear-non-numeric-eras {--dry : preview only, do not write}';
    protected $description = 'Set era=NULL on prisoners whose era is not a decade label like 1910s';

    public function handle(): int {
        $dry = (bool) $this->option('dry');

        // Decade pattern: 4 digits + "s" (e.g. 1700s, 1900s, 2010s).
        $pattern = '/^\d{4}s$/';

        $byEra = [];
        $cleared = 0;
        $kept = 0;
        $alreadyNull = 0;

        Prisoner::query()->chunk(200, function ($batch) use (&$byEra, &$cleared, &$kept, &$alreadyNull, $dry, $pattern) {
            foreach ($batch as $prisoner) {
                $era = trim((string) $prisoner->era);

                if ($era === '') {
                    $alreadyNull++;

                    continue;
                }

                if (preg_match($pattern, $era)) {
                    $kept++;

                    continue;
                }

                $byEra[$era] = ($byEra[$era] ?? 0) + 1;

                if (! $dry) {
                    $prisoner->era = null;
                    $prisoner->save();
                }
                $cleared++;
            }
        });

        $verb = $dry ? 'would clear' : 'cleared';
        $this->info("{$verb}: {$cleared}");
        $this->info("kept (numeric decade): {$kept}");
        $this->info("already null: {$alreadyNull}");

        if ($byEra) {
            $this->line("\nEras cleared (count):");
            arsort($byEra);
            foreach ($byEra as $era => $n) {
                $this->line("  {$n}\t{$era}");
            }
        }

        return self::SUCCESS;
    }
}
