<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Converts the remaining thematic era labels on prisoner records
 * (e.g. "Anti-nuclear movement", "Green Scare", "Antebellum", "Modern")
 * into year decades, using the researched mapping in
 * database/data/era-decades.json.
 *
 * Each decade was determined from the person's actual case dates — anchored
 * on arrest/conviction, NOT release — with manual correction for figures the
 * raw dates would misdate: long-imprisoned prisoners released decades later
 * (Herman Wallace, Marilyn Buck) and fugitives arrested long after their
 * actions (the Green Scare / Operation Backfire cohort). Idempotent.
 */
class NormalizeErasToDecades extends Command
{
    protected $signature = 'prisoners:normalize-eras-to-decades {--dry-run : Show changes without writing}';

    protected $description = 'Convert thematic prisoner era labels into year decades (from era-decades.json)';

    public function handle(): int
    {
        $file = database_path('data/era-decades.json');
        if (! file_exists($file)) {
            $this->error('era-decades.json not found.');

            return self::FAILURE;
        }

        $map = json_decode(file_get_contents($file), true);
        if (! is_array($map)) {
            $this->error("Could not parse {$file}");

            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');
        $updated = 0;
        $unchanged = 0;
        $missing = 0;

        foreach ($map as $name => $decade) {
            $prisoner = Prisoner::withUnderReview()->where('name', $name)->first();
            if (! $prisoner) {
                $this->warn("Not in DB: {$name}");
                $missing++;

                continue;
            }

            if ($prisoner->era === $decade) {
                $unchanged++;

                continue;
            }

            $this->line(sprintf('%s %-26s %-22s -> %s', $dry ? '[dry]' : '  ok', $name, $prisoner->era ?? '(null)', $decade));

            if (! $dry) {
                $prisoner->era = $decade;
                $prisoner->save();
            }
            $updated++;
        }

        $this->info("\n".($dry ? 'Would update' : 'Updated')." {$updated} | unchanged {$unchanged} | not in DB {$missing}");

        return self::SUCCESS;
    }
}
