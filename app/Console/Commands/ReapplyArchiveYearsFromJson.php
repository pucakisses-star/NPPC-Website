<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Re-applies `year` (and `date`) from every database/data JSON
 * import file onto matching ArchiveRecord rows. Use to recover
 * rows that lost their year during partial imports.
 *
 * Walks every *.json under database/data/, looks for entries with
 * a `slug` + `year` (or `date`), and sets the matching DB row's
 * year if it's currently null. Pass --force to overwrite.
 */
final class ReapplyArchiveYearsFromJson extends Command {
    protected $signature = 'archive:reapply-years-from-json {--apply : Write changes (dry-run by default)} {--force : Overwrite existing year values}';
    protected $description = 'Re-sync ArchiveRecord.year from source JSON files in database/data/';

    public function handle(): int {
        $apply = (bool) $this->option('apply');
        $force = (bool) $this->option('force');

        $files = glob(database_path('data/*.json'));
        $this->info('Scanning '.count($files).' JSON files in database/data/');

        $set = 0;
        $skipped = 0;
        $miss = 0;
        $touched = [];

        foreach ($files as $f) {
            $data = json_decode(file_get_contents($f), true);
            if (! is_array($data)) {
                continue;
            }
            // Some files are wrapped objects (e.g., {prisoners:[...]}). Skip if not a plain list.
            if (! isset($data[0])) {
                continue;
            }
            foreach ($data as $entry) {
                if (empty($entry['slug'])) {
                    continue;
                }
                $year = $entry['year'] ?? null;
                if (! $year && ! empty($entry['date'])) {
                    $maybe = (int) substr((string) $entry['date'], 0, 4);
                    if ($maybe >= 1850 && $maybe <= 2030 && $maybe !== 1900) {
                        $year = $maybe;
                    }
                }
                if (! $year) {
                    continue;
                }
                $r = ArchiveRecord::where('slug', $entry['slug'])->first();
                if (! $r) {
                    $miss++;

                    continue;
                }
                if (! $force && $r->year && (int) $r->year === (int) $year) {
                    $skipped++;

                    continue;
                }
                if (! $force && $r->year) {
                    $skipped++;

                    continue;
                }
                $old = $r->year;
                $touched[] = '#'.$r->id.'  '.$entry['slug'].'  ('.basename($f).')  '.($old ?: '∅').' → '.$year;
                if ($apply) {
                    $r->year = $year;
                    $r->save();
                }
                $set++;
            }
        }

        foreach ($touched as $t) {
            $this->info('SET  '.$t);
        }

        $this->info("\nWould set: {$set}    Skipped (already had year): {$skipped}    Slug missing in DB: {$miss}");
        if (! $apply) {
            $this->info('(dry-run; re-run with --apply to write)');
        }

        return self::SUCCESS;
    }
}
