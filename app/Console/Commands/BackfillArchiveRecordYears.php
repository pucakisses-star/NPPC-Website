<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * For ArchiveRecord rows that have an exact `date` but no `year`,
 * copy the year out of the date so the /archive-records year facet
 * surfaces them. Pass --force to also overwrite an existing `year`
 * that disagrees with the date.
 */
final class BackfillArchiveRecordYears extends Command {
    protected $signature = 'archive:backfill-record-years {--apply : Write the changes (dry-run by default)} {--force : Also overwrite an existing year that disagrees with date}';
    protected $description = 'Backfill ArchiveRecord.year from ArchiveRecord.date';

    public function handle(): int {
        $apply = (bool) $this->option('apply');
        $force = (bool) $this->option('force');

        $rows = ArchiveRecord::whereNotNull('date')->get(['id', 'title', 'date', 'year']);

        $filled = 0;
        $overwritten = 0;
        $skipped = 0;
        foreach ($rows as $r) {
            $dateYear = (int) $r->date->format('Y');
            if ($r->year === null) {
                $this->line(str_pad('FILL', 12).$r->title.'  ('.$r->date->toDateString().' -> '.$dateYear.')');
                if ($apply) {
                    $r->year = $dateYear;
                    $r->save();
                }
                $filled++;
            } elseif ((int) $r->year !== $dateYear) {
                if ($force) {
                    $this->warn(str_pad('OVERWRITE', 12).$r->title.'  (year='.$r->year.' -> '.$dateYear.' from date '.$r->date->toDateString().')');
                    if ($apply) {
                        $r->year = $dateYear;
                        $r->save();
                    }
                    $overwritten++;
                } else {
                    $this->line(str_pad('SKIP', 12).$r->title.'  (year='.$r->year.' ≠ date '.$r->date->toDateString().'; use --force to overwrite)');
                    $skipped++;
                }
            }
        }

        $this->info("\nFilled:      {$filled}");
        $this->info("Overwritten: {$overwritten}");
        $this->info("Skipped:     {$skipped}");
        if (! $apply) {
            $this->info('(re-run with --apply to write changes)');
        }

        return self::SUCCESS;
    }
}
