<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Cross-checks the 84 entries on NYC ABC's PP/POW Birthday Calendar
 * against our DB. By default it just reports the status for each entry
 * (no writes); pass --apply to set Prisoner.birthdate using a 1900
 * sentinel year when (a) we have a match by name and (b) the existing
 * birthdate is empty. The /birthdays page already sorts by month + day,
 * so the sentinel year doesn't affect calendar display — but pass
 * --apply only if you're comfortable with that approach. Pass
 * --strict-year to skip apply entirely for rows where the NYC ABC feed
 * lacks a known year.
 */
final class AuditNycAbcBirthdays extends Command {
    protected $signature = 'archive:audit-nycabc-birthdays {--apply : Write birthdates for matched-but-empty prisoners using year=1900 sentinel} {--strict-year : Refuse to set a 1900 sentinel — only update if NYC ABC feed has a known year} {--fix-placeholders : For CONFLICT rows whose existing date ends in -01-01, overwrite month-day with the NYC ABC value (preserving year)} {--investigate : For NOT-FOUND rows, run broader candidate searches (last name only, fuzzy) and print matches}';
    protected $description = 'Cross-check NYC ABC PP/POW Birthday Calendar entries against NPPC';

    public function handle(): int {
        $apply = (bool) $this->option('apply');
        $strict = (bool) $this->option('strict-year');
        $fixPlaceholders = (bool) $this->option('fix-placeholders');
        $investigate = (bool) $this->option('investigate');
        $entries = json_decode(file_get_contents(database_path('data/nycabc-birthdays.json')), true);

        $found = 0;
        $missing = 0;
        $alreadyHas = 0;
        $updated = 0;
        $ambiguous = 0;

        foreach ($entries as $entry) {
            $name = trim((string) $entry['name']);
            $month = (int) $entry['month'];
            $day = (int) $entry['day'];
            $year = $entry['year'] ?? null;

            $candidates = $this->lookup($name);
            if ($candidates->isEmpty()) {
                $this->line(str_pad('NOT FOUND', 12).$name);
                $missing++;
                if ($investigate) {
                    $this->printCandidates($name);
                }

                continue;
            }
            if ($candidates->count() > 1) {
                $this->warn(str_pad('AMBIGUOUS', 12).$name.' — matches '.$candidates->count().' prisoners: '.$candidates->pluck('name')->implode(', '));
                $ambiguous++;

                continue;
            }
            $prisoner = $candidates->first();
            $found++;

            if (! empty($prisoner->birthdate)) {
                $existing = $prisoner->birthdate->format('m-d');
                $proposed = sprintf('%02d-%02d', $month, $day);
                if ($existing === $proposed) {
                    $alreadyHas++;

                    continue;
                }
                $this->warn(str_pad('CONFLICT', 12).$name.'  (existing='.$prisoner->birthdate->toDateString().'  nycabc='.$proposed.')');
                if ($fixPlaceholders && str_ends_with($existing, '-01-01')) {
                    $existingYear = (int) $prisoner->birthdate->format('Y');
                    $newDate = sprintf('%04d-%02d-%02d', $existingYear, $month, $day);
                    if (! $apply) {
                        $this->line(str_pad('WOULD-FIX', 12).$name.'  -> '.$newDate.' (placeholder)');
                    } else {
                        $prisoner->birthdate = $newDate;
                        $prisoner->save();
                        $this->info(str_pad('FIXED', 12).$name.'  -> '.$newDate.' (placeholder)');
                        $updated++;
                    }
                }

                continue;
            }

            // Empty birthdate — eligible for backfill
            if ($strict && empty($year)) {
                $this->line(str_pad('NEEDS-YEAR', 12).$name.'  (no year in nycabc feed; --strict-year so skipping)');

                continue;
            }
            $useYear = $year ?? 1900;
            $proposed = sprintf('%04d-%02d-%02d', $useYear, $month, $day);

            if (! $apply) {
                $this->line(str_pad('WOULD-FILL', 12).$name.'  -> '.$proposed);

                continue;
            }
            $prisoner->birthdate = $proposed;
            $prisoner->save();
            $this->info(str_pad('FILLED', 12).$name.'  -> '.$proposed);
            $updated++;
        }

        $this->info("\nSummary:");
        $this->info("  Matched (DB):   {$found}");
        $this->info("  Already had:    {$alreadyHas}");
        $this->info("  Ambiguous:      {$ambiguous}");
        $this->info("  Not found:      {$missing}");
        if ($apply) {
            $this->info("  Updated:        {$updated}");
        } else {
            $this->info('  (re-run with --apply to write changes)');
        }

        return self::SUCCESS;
    }

    /** Return a small collection of Prisoner candidates matching this name. */
    private function lookup(string $name): \Illuminate\Database\Eloquent\Collection {
        $base = Prisoner::withUnderReview();
        $hits = (clone $base)->where('name', $name)->get();
        if ($hits->isNotEmpty()) {
            return $hits;
        }
        $hits = (clone $base)->where('name', 'like', $name)->get();
        if ($hits->isNotEmpty()) {
            return $hits;
        }
        $hits = (clone $base)->where('aka', 'like', "%{$name}%")->get();
        if ($hits->isNotEmpty()) {
            return $hits;
        }
        $parts = preg_split('/\s+/', $name);
        $last = end($parts);
        $first = $parts[0];
        if ($last && $first && $last !== $first) {
            $hits = (clone $base)
                ->where('name', 'like', "%{$first}%{$last}%")
                ->get();

            return $hits;
        }

        return new \Illuminate\Database\Eloquent\Collection;
    }

    /** For NOT-FOUND names, surface possible candidates by last name / alias / fuzzy parts. */
    private function printCandidates(string $name): void {
        $clean = preg_replace('/\(.*?\)/', '', $name);
        $clean = trim(preg_replace('/\s+/', ' ', $clean));
        $parts = preg_split('/\s+/', $clean);
        if (count($parts) < 1) {
            return;
        }
        $last = end($parts);
        $first = $parts[0];

        $base = Prisoner::withUnderReview();
        $hits = (clone $base)
            ->where(function ($q) use ($last, $first, $name) {
                $q->where('name', 'like', "%{$last}%")
                    ->orWhere('aka', 'like', "%{$last}%")
                    ->orWhere('name', 'like', "%{$first}%")
                    ->orWhere('aka', 'like', "%{$name}%");
            })
            ->limit(8)
            ->get(['name', 'slug', 'aka']);

        if ($hits->isEmpty()) {
            $this->line(str_pad('', 12).'  (no candidates)');

            return;
        }
        foreach ($hits as $h) {
            $aka = $h->aka ? ' [aka '.$h->aka.']' : '';
            $this->line(str_pad('', 12).'  ? '.$h->name.$aka.'  ('.$h->slug.')');
        }
    }
}
