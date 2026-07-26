<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * "Tamil Eelam" describes an organization/cause (the Liberation Tigers of
 * Tamil Eelam and the wider Tamil Eelam movement), not a political ideology,
 * so it belongs in each prisoner's `affiliation` array rather than
 * `ideologies`. This command finds every prisoner carrying a Tamil-Eelam
 * value under ideologies, removes it there, and adds it to affiliation
 * (preserving the exact stored label and de-duplicating).
 *
 * Matches any ideology entry containing "eelam" (case-insensitive) so close
 * variants ("Tamil Eelam", "Tamil Eelam nationalism", ...) are all relocated.
 * Idempotent: once no ideology contains "eelam", re-runs change nothing.
 */
final class MoveTamilEelamToAffiliation extends Command
{
    protected $signature = 'prisoners:move-tamil-eelam-affiliation {--dry-run : Show planned changes without writing}';

    protected $description = 'Move the "Tamil Eelam" value from prisoners\' ideologies into their affiliation';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        // Narrow with a JSON LIKE, then filter precisely in PHP so this works
        // on both MySQL and SQLite.
        $prisoners = Prisoner::withoutGlobalScopes()
            ->where('ideologies', 'like', '%eelam%')
            ->get();

        $changed = 0;
        foreach ($prisoners as $p) {
            $ideologies = is_array($p->ideologies) ? $p->ideologies : [];
            $affiliation = is_array($p->affiliation) ? $p->affiliation : [];

            $moved = array_values(array_filter(
                $ideologies,
                fn ($v) => str_contains(strtolower((string) $v), 'eelam'),
            ));

            if (empty($moved)) {
                continue;
            }

            $newIdeologies = array_values(array_filter(
                $ideologies,
                fn ($v) => ! str_contains(strtolower((string) $v), 'eelam'),
            ));

            $newAffiliation = array_values(array_unique(array_merge($affiliation, $moved)));

            $this->line(
                "  {$p->name}: move [".implode(', ', $moved).'] '
                ."ideologies -> affiliation"
            );

            if (! $dry) {
                $p->ideologies = $newIdeologies;
                $p->affiliation = $newAffiliation;
                $p->save();
            }

            $changed++;
        }

        $this->newLine();
        if ($dry) {
            $this->warn("Dry run — no changes written. {$changed} record(s) would change.");
        } else {
            $this->info("Done. Updated {$changed} record(s).");
        }

        return self::SUCCESS;
    }
}
