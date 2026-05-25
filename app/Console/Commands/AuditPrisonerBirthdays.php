<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Audits the prisoner-birthday roster against the canonical lists
 * maintained by ABCF, Jericho Movement, and NYC ABC. For each
 * canonical entry, reports:
 *
 *   - MATCH        — found in DB with the same month/day
 *   - DATE MISMATCH — found in DB, birthdate differs from canonical
 *   - MISSING DATE — found in DB but birthdate is null
 *   - NOT FOUND    — not in DB at all
 *
 * Run on prod with:  php artisan prisoners:audit-birthdays
 */
final class AuditPrisonerBirthdays extends Command {
    protected $signature = 'prisoners:audit-birthdays';
    protected $description = 'Cross-check prisoner birthdays against ABCF / Jericho / NYC ABC canonical lists';

    /**
     * Canonical roster compiled from:
     *   abcf.net/blog/upcoming-political-prisoner-birthdays(/-2)
     *   thejerichomovement.com/political-prisoners-birthdays
     *   nycabc.wordpress.com/pppow-birthday-calendar/
     *
     * Format: month-day => [canonical name, name aliases to match in DB]
     */
    private array $roster = [
        // January
        ['01-03', 'Jeremy Hammond', []],
        ['01-06', 'Lynne Stewart', []],
        ['01-09', 'Abdul Azeez', []],
        ['01-14', 'Sundiata Acoli', ['Clark Squire']],
        ['01-15', 'Joseph "Joe Joe" Bowen', ['Joseph Bowen', 'Joe Joe Bowen', 'Joe-Joe Bowen']],
        ['01-26', 'Marius Mason', ['Marie Mason']],
        // February
        ['02-04', 'Veronza Bowers', ['Veronza Bowers Jr.']],
        ['02-19', 'Kamau Sadiki', ['Freddie Hilton']],
        ['02-19', 'Albert Woodfox', []],
        ['02-20', 'Abdullah Malik Ka\'bah', ['Jeff Fort']],
        ['02-26', 'Alexander Contompasis', []],
        ['02-26', 'Oso Blanco', ['Byron Chubbuck', 'Byron Shane Chubbuck']],
        // March
        ['03-02', 'Aafia Siddiqui', ['Dr. Aafia Siddiqui']],
        ['03-05', 'Joyce Powell', []],
        ['03-17', 'Ruchell Cinque Magee', ['Ruchell Magee']],
        // April
        ['04-07', 'Delbert Orr Africa', ['Delbert Africa']],
        ['04-11', 'Romaine "Chip" Fitzgerald', ['Romaine Fitzgerald', 'Chip Fitzgerald']],
        ['04-13', 'Janet Holloway Africa', []],
        ['04-17', 'Charles Sims Africa', []],
        ['04-18', 'Rebecca Rubin', []],
        ['04-24', 'Mumia Abu-Jamal', []],
        ['04-25', 'Janine Phillips Africa', ['Janine Africa']],
        // May
        ['05-12', 'Alvaro Luna Hernandez', ['Alvaro Luna Hernández']],
        ['05-27', 'Kojo Bomani Sababu', ['Grailing Brown', 'Kojo Bomani Sabubu']],
        // June
        ['06-28', 'Thomas Manning', []],
        // August
        ['08-03', 'Bill Dunne', []],
        ['08-08', 'Mutulu Shakur', []],
        ['08-23', 'Russell Maroon Shoatz', ['Russell Shoats', 'Russell Maroon Shoats']],
        // September
        ['09-12', 'Leonard Peltier', []], // NYC ABC says 9/9; Jericho says 9/12
        ['09-15', 'Maumin Khabir', ['Melvin Mayes']],
        // October
        ['10-04', 'Jamil Abdullah Al-Amin', ['Jamil al-Amin', 'H. Rap Brown']],
        ['10-06', 'David Gilbert', []],
        ['10-18', 'Jalil Muntaqim', ['Anthony Jalil Bottom']],
        ['10-21', 'Edward Goodman Africa', []],
        // November
        ['11-01', 'Ed Poindexter', []],
        ['11-03', 'Larry Hoover', []],
        // December
        ['12-12', 'Zolo Azania', []],
        ['12-15', 'Fred "Muhammad" Burton', ['Fred Burton', 'Muhammad Burton']],
    ];

    public function handle(): int {
        $match = []; $mismatch = []; $missingDate = []; $notFound = [];

        foreach ($this->roster as [$mmdd, $name, $aliases]) {
            $names = array_merge([$name], $aliases);
            $prisoner = null;
            foreach ($names as $candidate) {
                $prisoner = Prisoner::query()
                    ->where('name', $candidate)
                    ->orWhere('aka', $candidate)
                    ->first();
                if ($prisoner) break;
            }

            if (! $prisoner) {
                $notFound[] = [$mmdd, $name];
                continue;
            }

            if (! $prisoner->birthdate) {
                $missingDate[] = [$mmdd, $name, $prisoner->id];
                continue;
            }

            $dbMmdd = Carbon::parse($prisoner->birthdate)->format('m-d');
            if ($dbMmdd === $mmdd) {
                $match[] = [$mmdd, $name];
            } else {
                $mismatch[] = [$mmdd, $name, $dbMmdd, $prisoner->id];
            }
        }

        $this->info('=== MATCH ('.count($match).') ===');
        foreach ($match as [$mmdd, $name]) {
            $this->line("  {$mmdd}  {$name}");
        }

        $this->newLine();
        $this->warn('=== DATE MISMATCH ('.count($mismatch).') ===');
        foreach ($mismatch as [$mmdd, $name, $db, $id]) {
            $this->line("  canonical {$mmdd}  vs DB {$db}  — {$name}  (#{$id})");
        }

        $this->newLine();
        $this->warn('=== MISSING DATE — in DB but no birthdate set ('.count($missingDate).') ===');
        foreach ($missingDate as [$mmdd, $name, $id]) {
            $this->line("  should be {$mmdd}  — {$name}  (#{$id})");
        }

        $this->newLine();
        $this->error('=== NOT FOUND in DB ('.count($notFound).') ===');
        foreach ($notFound as [$mmdd, $name]) {
            $this->line("  {$mmdd}  {$name}");
        }

        $this->newLine();
        $this->info('Total canonical entries: '.count($this->roster));
        $this->info(sprintf('Match: %d  |  Mismatch: %d  |  Missing date: %d  |  Not found: %d',
            count($match), count($mismatch), count($missingDate), count($notFound)));

        return self::SUCCESS;
    }
}
