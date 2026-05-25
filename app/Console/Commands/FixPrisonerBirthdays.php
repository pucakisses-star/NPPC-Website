<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Repairs the 12 birthday mismatches / missing-dates surfaced by
 * prisoners:audit-birthdays against the canonical ABCF / Jericho /
 * NYC ABC lists. Birth years are preserved where the DB already had
 * one; otherwise a reasonable canonical year is set.
 *
 * Dry-run by default; --apply writes.
 */
final class FixPrisonerBirthdays extends Command {
    protected $signature = 'prisoners:fix-birthdays {--apply : Actually write the corrected dates}';
    protected $description = 'Fix the 12 birthday mismatches + missing-dates found by prisoners:audit-birthdays';

    /**
     * [name-or-aka, canonical-month-day, fallback-year]
     */
    private array $fixes = [
        ['Jeremy Hammond',          '01-03', 1985],
        ['Lynne Stewart',           '01-06', 1939],
        ['Veronza Bowers',          '02-04', 1946],
        ['Kamau Sadiki',            '02-19', 1953],
        ['Oso Blanco',              '02-26', 1967],
        ['Delbert Orr Africa',      '04-07', 1946],
        ['Romaine "Chip" Fitzgerald', '04-11', 1949],
        ['Janet Holloway Africa',   '04-13', 1951],
        ['Janine Phillips Africa',  '04-25', 1956],
        ['Larry Hoover',            '11-03', 1950],
        ['Alvaro Luna Hernandez',   '05-12', 1952],
        ['Edward Goodman Africa',   '10-21', 1949],
    ];

    public function handle(): int {
        $apply = (bool) $this->option('apply');
        $changes = []; $skipped = [];

        foreach ($this->fixes as [$name, $mmdd, $fallbackYear]) {
            $prisoner = Prisoner::query()
                ->where('name', $name)
                ->orWhere('aka', $name)
                ->first();

            if (! $prisoner) {
                $skipped[] = [$name, 'NOT FOUND'];
                continue;
            }

            $existingYear = null;
            if ($prisoner->birthdate) {
                try {
                    $existingYear = Carbon::parse($prisoner->birthdate)->year;
                } catch (\Throwable $e) {}
            }

            $year = $existingYear ?: $fallbackYear;
            $newDate = sprintf('%04d-%s', $year, $mmdd);

            if ($prisoner->birthdate === $newDate) {
                $skipped[] = [$name, 'already correct'];
                continue;
            }

            $changes[] = [$name, $prisoner->birthdate, $newDate, $prisoner];
        }

        $this->info('Planned changes ('.count($changes).'):');
        foreach ($changes as [$name, $old, $new, $_]) {
            $this->line(sprintf('  %s  :  %s  →  %s', $name, $old ?: '(null)', $new));
        }

        if ($skipped) {
            $this->newLine();
            $this->warn('Skipped ('.count($skipped).'):');
            foreach ($skipped as [$name, $reason]) {
                $this->line('  '.$name.'  — '.$reason);
            }
        }

        if (! $apply) {
            $this->newLine();
            $this->info('(dry-run; re-run with --apply to write)');
            return self::SUCCESS;
        }

        foreach ($changes as [$name, $old, $new, $prisoner]) {
            $prisoner->birthdate = $new;
            $prisoner->save();
        }
        $this->newLine();
        $this->info('Applied '.count($changes).' updates.');
        return self::SUCCESS;
    }
}
