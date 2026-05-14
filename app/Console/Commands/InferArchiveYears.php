<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Infers a publication `year` for ArchiveRecord rows that don't
 * have one, by scanning (in priority order):
 *
 *   1. `date` column (most reliable — exact date)
 *   2. File path / URL — most reliable for our scanned-PDF corpus
 *      because FA / NYC ABC / Chronicling America paths typically
 *      embed the date (e.g. "510.iww.gdc.1919.pdf",
 *      "1886050501/0001.pdf")
 *   3. Slug — often contains the year ("fag-c25-libertad-...-1985")
 *   4. Title — last resort, may mention a historical year that
 *      isn't the publication year
 *
 * Skips rows that already have `year` set. Pass --apply to write.
 * Pass --include-title to also consider titles (more aggressive
 * but riskier — disabled by default).
 */
final class InferArchiveYears extends Command {
    protected $signature = 'archive:infer-years {--apply : Write the inferred years (dry-run by default)} {--include-title : Also infer from title text} {--force : Overwrite existing year values}';
    protected $description = 'Infer ArchiveRecord.year from file path / slug / title where empty';

    public function handle(): int {
        $apply = (bool) $this->option('apply');
        $includeTitle = (bool) $this->option('include-title');
        $force = (bool) $this->option('force');

        $query = ArchiveRecord::query();
        if (! $force) {
            $query->whereNull('year');
        }
        $rows = $query->get(['id', 'slug', 'title', 'date', 'file', 'description', 'year']);

        $this->info('Candidates (rows '.($force ? 'rescanned' : 'with null year').'): '.$rows->count());

        $set = 0;
        $skipped = 0;
        $multi = 0;
        $miss = 0;

        foreach ($rows as $r) {
            $year = $this->inferYear($r, $includeTitle);
            if ($year === null) {
                $miss++;

                continue;
            }
            $existing = $r->year ? (int) $r->year : null;
            if (! $force && $existing !== null) {
                $skipped++;

                continue;
            }
            if ($force && $existing === $year) {
                $skipped++;

                continue;
            }

            $changed = $existing === null ? 'SET' : 'CHANGE';
            $this->info(str_pad($changed, 8).$year.'  '.($existing !== null ? '(was '.$existing.')  ' : '').'#'.$r->id.'  '.$r->slug.'  — '.\Illuminate\Support\Str::limit($r->title, 80));
            if ($apply) {
                $r->year = $year;
                $r->save();
            }
            $set++;
        }

        $this->info("\nWould set: {$set}    Already-had-year (skipped): {$skipped}    No year found: {$miss}");
        if (! $apply) {
            $this->info('(dry-run; re-run with --apply to write)');
        }

        return self::SUCCESS;
    }

    private function inferYear(ArchiveRecord $r, bool $includeTitle): ?int {
        // 1. From `date` — skip the FA "1900-01-01" sentinel
        if (! empty($r->date)) {
            $y = is_string($r->date) ? (int) substr($r->date, 0, 4) : (int) $r->date->format('Y');
            if ($this->validYear($y) && $y !== 1900) {
                return $y;
            }
        }
        // 2. From file path — most reliable for scanned PDFs
        if (! empty($r->file)) {
            $y = $this->scanForYear((string) $r->file);
            if ($y !== null) {
                return $y;
            }
        }
        // 3. From slug
        if (! empty($r->slug)) {
            $y = $this->scanForYear((string) $r->slug);
            if ($y !== null) {
                return $y;
            }
        }
        // 4. From title — always (was opt-in, now default; --include-title kept for back-compat)
        if (! empty($r->title)) {
            $y = $this->scanForYear((string) $r->title);
            if ($y !== null) {
                return $y;
            }
        }
        // 5. From description — last resort; many FA records embed the
        //    issue date in the description rather than path or title.
        if (! empty($r->description)) {
            $y = $this->scanForYear((string) $r->description);
            if ($y !== null) {
                return $y;
            }
        }

        return null;
    }

    /**
     * Scan a string for a year. Returns the LATEST plausible year
     * found (latest because file paths often contain both scan year
     * and original-document year — and the more-recent year is more
     * likely to be a real publication date than the older one).
     */
    private function scanForYear(string $s): ?int {
        $years = [];

        // Match 4-digit years in plausible range (1850-2030).
        if (preg_match_all('/\b(18[5-9]\d|19\d{2}|20[0-2]\d)\b/', $s, $matches)) {
            foreach ($matches[1] as $m) {
                $y = (int) $m;
                if ($this->validYear($y)) {
                    $years[] = $y;
                }
            }
        }
        // Match 2-digit year suffixes preceded by a month abbreviation
        // (e.g., "may.78" or "march.77" common in FA filenames).
        $months = '(?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)\w*';
        if (preg_match_all('/'.$months.'\.(\d{2})\b/i', $s, $m2)) {
            foreach ($m2[1] as $y2) {
                $y2 = (int) $y2;
                $y = $y2 >= 30 ? 1900 + $y2 : 2000 + $y2;
                if ($this->validYear($y)) {
                    $years[] = $y;
                }
            }
        }
        // Match YYYY-MM-DD or YYYYMMDD timestamps in path
        if (preg_match_all('/\b(18[5-9]\d|19\d{2}|20[0-2]\d)[\-_.]?(0[1-9]|1[0-2])[\-_.]?(0[1-9]|[12]\d|3[01])\b/', $s, $m3)) {
            foreach ($m3[1] as $y) {
                $y = (int) $y;
                if ($this->validYear($y)) {
                    $years[] = $y;
                }
            }
        }
        if (empty($years)) {
            return null;
        }
        // Prefer the most common year, then the latest; this lets a
        // path with "1985.05.18" still pick 1985 over a passing "1968"
        // reference.
        $counts = array_count_values($years);
        arsort($counts);
        $top = max($counts);
        $candidates = array_keys(array_filter($counts, fn ($c) => $c === $top));
        sort($candidates);

        return end($candidates);
    }

    private function validYear(int $y): bool {
        return $y >= 1850 && $y <= 2030;
    }
}
