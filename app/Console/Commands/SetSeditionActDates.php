<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Backfills incarceration_date and release_date on the first
 * PrisonerCase of each well-documented Sedition Act / common-law
 * libel prisoner. Sets only the fields we have confident historical
 * dates for; leaves uncertain fields alone.
 *
 * Idempotent — only writes when the target field is currently null
 * or differs from the value we want to set, and never blanks out
 * anything that's already populated.
 */
final class SetSeditionActDates extends Command {
    protected $signature = 'archive:set-sedition-act-dates {--dry-run : Preview without saving}';
    protected $description = 'Backfill arrest / incarceration / release dates on Sedition Act prisoners';

    public function handle(): int {
        $dryRun = (bool) $this->option('dry-run');

        // [slug => [field => value, ...]]
        $updates = [
            // ---- High confidence ----
            'matthew-lyon' => [
                'incarceration_date' => '1798-10-09',
                'release_date'       => '1799-02-09',
            ],
            'thomas-cooper' => [
                'incarceration_date' => '1800-04-26',
                'release_date'       => '1800-10-26',
            ],
            'james-thompson-callender' => [
                'incarceration_date' => '1800-06-03',
                'release_date'       => '1801-03-16',
            ],
            'anthony-haswell' => [
                'incarceration_date' => '1800-05-09',
                'release_date'       => '1800-07-09',
            ],
            'charles-holt' => [
                'incarceration_date' => '1800-04-17',
                'release_date'       => '1800-07-17',
            ],
            'abijah-adams' => [
                'incarceration_date' => '1799-02-19',
                'release_date'       => '1799-03-21',
            ],
            // ---- Medium confidence — set only what we know ----
            'david-brown' => [
                // Arrested in Dedham, MA in November 1798 over the liberty
                // pole. Pleaded guilty and sentenced June 1799 in Salem
                // (Justice Samuel Chase) to 18 months + $480 fine. Held
                // continuously because he could not pay the fine. Released
                // March 12, 1801 by Jefferson's general pardon.
                'arrest_date'        => '1798-11-01',
                'incarceration_date' => '1799-06-08',
                'release_date'       => '1801-03-12',
            ],
            'david-frothingham' => [
                // Tried Nov 21, 1799 in NY Court of General Sessions on
                // Hamilton's common-law libel complaint (not federal
                // Sedition Act). Sentenced to 4 months + $100 fine; held
                // beyond his sentence because he could not pay. Died in
                // the NYC Bridewell in 1800 — exact death date not
                // confidently known, so release_date intentionally left
                // blank.
                'incarceration_date' => '1799-11-21',
            ],
        ];

        $touched = 0;
        $skipped = 0;

        foreach ($updates as $slug => $fields) {
            $prisoner = Prisoner::with('cases')->where('slug', $slug)->first();
            if (! $prisoner) {
                $this->warn("Prisoner not found: {$slug}");
                $skipped++;

                continue;
            }
            $case = $prisoner->cases->first();
            if (! $case) {
                $this->warn("Prisoner {$prisoner->name} has no PrisonerCase — skipping.");
                $skipped++;

                continue;
            }

            $changes = [];
            foreach ($fields as $field => $value) {
                $current = $case->{$field}?->toDateString() ?? $case->{$field};
                if ($current === $value) {
                    continue;
                }
                $changes[$field] = ['from' => $current, 'to' => $value];
                if (! $dryRun) {
                    $case->{$field} = $value;
                }
            }

            if (empty($changes)) {
                $this->line("unchanged: {$prisoner->name}");

                continue;
            }

            foreach ($changes as $f => $diff) {
                $this->line(($dryRun ? '[dry-run] ' : '')."{$prisoner->name}  {$f}: ".($diff['from'] ?? '(null)').' -> '.$diff['to']);
            }
            if (! $dryRun) {
                $case->save();
            }
            $touched++;
        }

        $this->info("\nDone. Updated={$touched} Skipped={$skipped}".($dryRun ? ' (DRY RUN)' : ''));

        return self::SUCCESS;
    }
}
