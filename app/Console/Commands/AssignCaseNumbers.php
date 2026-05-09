<?php

namespace App\Console\Commands;

use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Assign a unique sequential case_number to every PrisonerCase row,
 * starting at 0. Default order is chronological by the earliest of
 * (incarceration_date, arrest_date, sentenced_date, release_date),
 * with ties broken by case id so the assignment is deterministic.
 *
 * Re-running the command will renumber from 0 again unless
 * --append-only is used (in which case existing numbers are kept and
 * only nulls are filled in starting at max+1).
 */
class AssignCaseNumbers extends Command
{
    protected $signature   = 'prisoners:assign-case-numbers {--dry-run : Print plan without writing} {--append-only : Keep existing case_numbers; assign only to rows where it is null}';
    protected $description = 'Assign unique case_number (0..N-1) to every prisoner case in chronological order.';

    public function handle(): int
    {
        $dryRun     = (bool) $this->option('dry-run');
        $appendOnly = (bool) $this->option('append-only');

        $cases = PrisonerCase::query()->orderBy('id')->get([
            'id', 'case_number',
            'arrest_date', 'incarceration_date', 'sentenced_date', 'release_date',
        ]);

        $this->info("Total cases: {$cases->count()}");

        if ($appendOnly) {
            $start = (int) (PrisonerCase::max('case_number') ?? -1) + 1;
            $unassigned = $cases->filter(fn ($c) => $c->case_number === null)->values();
            $sorted = $this->chronologicalSort($unassigned);

            $this->info("Append-only: assigning case_number to {$sorted->count()} rows starting at {$start}.");
            $written = 0;
            foreach ($sorted as $i => $c) {
                $newNum = $start + $i;
                if ($dryRun) continue;
                DB::table('prisoner_cases')->where('id', $c->id)->update(['case_number' => $newNum]);
                $written++;
            }
            $this->info($dryRun ? "Dry run — no changes written." : "Done. Updated {$written} rows.");
            return self::SUCCESS;
        }

        // Full renumber: clear, then assign 0..N-1 in chronological order.
        $sorted = $this->chronologicalSort($cases);
        $this->info("Renumbering all rows 0..{$cases->count()} (this is a full reset).");

        if ($dryRun) {
            foreach ($sorted->take(20) as $i => $c) {
                $year = $this->anchorYear($c) ?: '----';
                $this->line(sprintf("  %5d  %s  case_id=%s", $i, $year, $c->id));
            }
            $this->warn("Dry run — no changes written.");
            return self::SUCCESS;
        }

        DB::transaction(function () use ($sorted) {
            // Two-pass to avoid colliding on the unique index while we
            // shuffle existing values around: stage into a high range
            // first, then drop them down to the final values.
            $offset = 1_000_000_000;
            foreach ($sorted as $i => $c) {
                DB::table('prisoner_cases')->where('id', $c->id)->update(['case_number' => $offset + $i]);
            }
            foreach ($sorted as $i => $c) {
                DB::table('prisoner_cases')->where('id', $c->id)->update(['case_number' => $i]);
            }
        });

        $this->info("Done. Assigned case_number 0..{" . ($sorted->count() - 1) . "} to {$sorted->count()} cases.");
        return self::SUCCESS;
    }

    private function chronologicalSort($cases)
    {
        return $cases->sortBy(function ($c) {
            $year = $this->anchorYear($c);
            return sprintf('%04d-%s', $year ?: 9999, (string) $c->id);
        })->values();
    }

    private function anchorYear($c): int
    {
        $year = 0;
        foreach (['incarceration_date', 'arrest_date', 'sentenced_date', 'release_date'] as $field) {
            $d = $c->$field ?? null;
            if (! $d) continue;
            $y = is_string($d) ? (int) substr($d, 0, 4) : (int) $d->format('Y');
            if ($y < 1500 || $y > 2100) continue;
            if ($year === 0 || $y < $year) $year = $y;
        }
        return $year;
    }
}
