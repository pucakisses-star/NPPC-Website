<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Backfills exact arrest, conviction, and release-related dates and
 * factual context for Claude Daniel Marks and Donna Jean Willmott —
 * the two May 19th Communist Organization / Resistance Conspiracy
 * defendants who spent 7+ years on the FBI's Ten Most Wanted Fugitives
 * list before surrendering together in Chicago on December 6, 1994.
 *
 * Both were charged with conspiring to break FALN co-founder Oscar
 * López Rivera out of USP Leavenworth.
 *
 * Idempotent — updates the first PrisonerCase on each existing record,
 * filling in missing fields; does not blank populated values.
 */
final class UpdateMarksWillmottDates extends Command {
    protected $signature = 'archive:update-marks-willmott-dates {--dry-run : Preview without saving}';
    protected $description = 'Backfill verified arrest / surrender / sentence details on Claude Marks and Donna Willmott';

    public function handle(): int {
        $dryRun = (bool) $this->option('dry-run');

        $records = [
            'claude-marks' => [
                'aka'      => 'Claude Daniel Marks',
                'description_append' => "\n\nMarks was named to the FBI's Ten Most Wanted Fugitives list on May 22, 1987 — fugitive #411. After more than seven years underground he surrendered with his partner Donna Jean Willmott in Chicago, Illinois on December 6, 1994. Both were charged with conspiring to use explosives to break FALN co-founder Oscar López Rivera out of USP Leavenworth in Kansas. Both pleaded guilty.",
                'case' => [
                    'institution_name' => 'Federal Bureau of Prisons',
                    'charges'          => 'Conspiracy to use explosives to free Puerto Rican independentista Oscar López Rivera from USP Leavenworth (1985 indictment); related explosives and weapons offenses',
                    'arrest_date'      => '1994-12-06',
                    'incarceration_date' => '1994-12-06',
                    'convicted'        => 'Yes — pleaded guilty after surrender',
                    'sentence'         => '5 years federal imprisonment',
                ],
            ],
            'donna-willmott' => [
                'aka'      => 'Donna Jean Willmott',
                'description_append' => "\n\nWillmott was added to the FBI's Ten Most Wanted Fugitives list shortly after her partner Claude Daniel Marks — fugitive #412. After more than seven years underground she surrendered with Marks in Chicago, Illinois on December 6, 1994. Both were charged with conspiring to use explosives to break FALN co-founder Oscar López Rivera out of USP Leavenworth. She pleaded guilty.",
                'case' => [
                    'institution_name' => 'Federal Bureau of Prisons',
                    'charges'          => 'Conspiracy to use explosives to free Puerto Rican independentista Oscar López Rivera from USP Leavenworth (1985 indictment); related explosives and weapons offenses',
                    'arrest_date'      => '1994-12-06',
                    'incarceration_date' => '1994-12-06',
                    'convicted'        => 'Yes — pleaded guilty after surrender',
                    'sentence'         => '4 years federal imprisonment',
                ],
            ],
        ];

        $touched = 0;
        $skipped = 0;

        foreach ($records as $slug => $updates) {
            // bypass under_review global scope just in case
            $prisoner = Prisoner::withUnderReview()->with('cases')->where('slug', $slug)->first();
            if (! $prisoner) {
                $this->warn("Prisoner not found: {$slug}");
                $skipped++;

                continue;
            }

            // patch aka if missing
            if (empty($prisoner->aka) && ! empty($updates['aka'])) {
                $this->line(($dryRun ? '[dry-run] ' : '')."{$slug}  aka: (null) -> {$updates['aka']}");
                if (! $dryRun) {
                    $prisoner->aka = $updates['aka'];
                }
            }

            // append narrative context if not already present
            $needle = 'Ten Most Wanted Fugitives list';
            $description = $prisoner->description ?? '';
            if (! str_contains($description, $needle) && ! empty($updates['description_append'])) {
                $this->line(($dryRun ? '[dry-run] ' : '')."{$slug}  description: append FBI Most-Wanted + surrender details");
                if (! $dryRun) {
                    $prisoner->description = rtrim($description).$updates['description_append'];
                }
            }

            if (! $dryRun && $prisoner->isDirty()) {
                $prisoner->save();
            }

            // update or create first case
            $case = $prisoner->cases->first();
            if (! $case) {
                $this->warn("  {$slug}: no PrisonerCase row — creating one.");
                if (! $dryRun) {
                    $case = $prisoner->cases()->create($updates['case']);
                    $touched++;
                }

                continue;
            }
            $changes = [];
            foreach ($updates['case'] as $field => $value) {
                $current = $case->{$field}?->toDateString() ?? $case->{$field};
                if ($current === $value) {
                    continue;
                }
                if (! empty($current) && ! in_array($field, ['arrest_date', 'incarceration_date', 'sentence', 'charges', 'convicted'])) {
                    // never overwrite an existing non-empty release_date / judge / institution_state
                    continue;
                }
                $changes[$field] = ['from' => $current, 'to' => $value];
                if (! $dryRun) {
                    $case->{$field} = $value;
                }
            }
            foreach ($changes as $f => $diff) {
                $this->line(($dryRun ? '[dry-run] ' : '')."{$slug}  case.{$f}: ".($diff['from'] ?? '(null)').' -> '.$diff['to']);
            }
            if (! $dryRun && $case->isDirty()) {
                $case->save();
            }
            $touched++;
        }

        $this->info("\nDone. Updated={$touched} Skipped={$skipped}".($dryRun ? ' (DRY RUN)' : ''));

        return self::SUCCESS;
    }
}
