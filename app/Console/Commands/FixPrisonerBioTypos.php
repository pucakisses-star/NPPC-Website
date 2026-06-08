<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Fixes specific typos found in prisoner bio descriptions during the June 2026
 * text audit. The descriptions live in the database (not seeded from the repo),
 * so this applies targeted, idempotent string replacements -- it only changes a
 * record when the exact typo text is still present, and is safe to re-run.
 */
class FixPrisonerBioTypos extends Command {
    protected $signature = 'prisoners:fix-bio-typos';
    protected $description = 'Fix known typos in prisoner bio descriptions (idempotent)';

    public function handle(): int {
        // [slug, exact text to replace, replacement]
        $fixes = [
            ['joseph-bowen',   'others measures to seperate him', 'other measures to separate him'],
            ['joshua-schulte', 'additional offense ontop of',     'additional offense on top of'],
        ];

        $fixed = 0;
        foreach ($fixes as [$slug, $from, $to]) {
            $prisoner = Prisoner::where('slug', $slug)->first();
            if (! $prisoner) {
                $this->warn("Not found: {$slug}");
                continue;
            }

            $desc = (string) $prisoner->description;
            if (str_contains($desc, $from)) {
                $prisoner->description = str_replace($from, $to, $desc);
                $prisoner->save();
                $this->info("Fixed: {$slug}");
                $fixed++;
            } else {
                $this->line("No change ({$slug}): text not present (already fixed?).");
            }
        }

        $this->info("Done. {$fixed} bio(s) updated.");

        return self::SUCCESS;
    }
}
