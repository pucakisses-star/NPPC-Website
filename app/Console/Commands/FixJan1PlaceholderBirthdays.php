<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Fixes the ~365 prisoner records whose birthday was stored as January 1 — a
 * legacy "unknown date of birth" placeholder. Research of every one of those
 * names found that NOT ONE was actually born on January 1.
 *
 * Two actions:
 *   1. For the people whose real, full birth date was found and is
 *      well-sourced, set the correct date (day precision).
 *   2. For every remaining record still displaying a full-precision January 1
 *      (i.e. a placeholder), drop it to YEAR precision so the site shows just
 *      the year of birth instead of a fabricated "January 1". The stored year
 *      is kept; only the fake month/day is hidden.
 *
 * Records that already carry month/year precision (e.g. the Cop City batch,
 * which legitimately displays "January 1996") are left untouched. Idempotent.
 */
class FixJan1PlaceholderBirthdays extends Command
{
    protected $signature = 'prisoners:fix-jan1-birthdays';

    protected $description = 'Correct researched birth dates and downgrade the rest of the January-1 placeholder birthdays to year-only precision';

    /** Name => [year, month, day] — confidently sourced full birth dates (none fall on Jan 1). */
    private const FULL_DATES = [
        'Adolfo Matos Antogiorgi' => [1950, 9, 18],
        'Alexander H. Macmillan' => [1877, 6, 2],
        'Alison Turnbull Hopkins' => [1880, 5, 20],
        'Anne Symens-Bucher' => [1957, 9, 17],
        'Arnold Johnson' => [1904, 9, 23],
        'Ben Dobbs' => [1912, 2, 23],
        'Ben Gold' => [1898, 9, 8],
        'David Hartsough' => [1940, 5, 2],
        'Edward Allen Mead' => [1941, 11, 6],
        'Elmer Smith' => [1888, 1, 22],
        'Faraz Martin Talab' => [1994, 8, 12],
        'Felice Cohen-Joppa' => [1959, 6, 26],
        'Guy Chichester' => [1935, 2, 11],
        'Harold Studley Gray' => [1894, 2, 23],
        'Harvey Franklin Wasserman' => [1945, 12, 31],
        'Haywood Patterson' => [1912, 12, 12],
        'Howard Wilbur Moore' => [1889, 2, 9],
        'Iya Fulani Sunni-Ali' => [1948, 3, 6],
        'Jack Stachel' => [1900, 1, 18],
        'James Burmeister' => [1984, 7, 23],
        'Jeffrey Sterling' => [1967, 12, 28],
        'John M. Fife' => [1940, 1, 16],
        'Joseph Austin Gaskins' => [2000, 9, 26],
        'LeRoy Nathaniel Bundy' => [1873, 4, 14],
        'Lewis Hayden' => [1811, 12, 2],
        'Loretta Starvus Stack' => [1913, 5, 2],
        'Louis Weinstock' => [1903, 5, 14],
        'Louise Olivereau' => [1884, 4, 9],
        'Marjorie Bradford Melville' => [1929, 8, 19],
        'Martha Hennessy' => [1955, 7, 11],
        'Matilda Hall Gardner' => [1871, 12, 31],
        'Max Geldman' => [1905, 5, 8],
        'Paul Kabat' => [1932, 1, 16],
        'Rev. Paul Kabat' => [1932, 1, 16],
        'Pete O\'Neal' => [1940, 7, 27],
        'Peter DeMott' => [1947, 1, 6],
        'Ramona Africa' => [1955, 6, 8],
        'Randy Kehler' => [1944, 7, 16],
        'Rose Chernin' => [1901, 9, 14],
        'Stephen Funk' => [1982, 6, 15],
        'Thomas A. Drake' => [1957, 4, 22],
        'Walter Bond' => [1976, 4, 16],
        'Walter Lee Irvin' => [1927, 5, 8],
        'Wilhelm von Brincken' => [1881, 5, 27],
    ];

    public function handle(): int
    {
        DB::transaction(function () {
            $corrected = 0;

            // 1. Set researched full birth dates (day precision).
            foreach (self::FULL_DATES as $name => [$y, $m, $d]) {
                $matches = Prisoner::withoutGlobalScopes()->where('name', $name)->get();

                if ($matches->isEmpty()) {
                    $this->warn("Not found (skipped): {$name}");

                    continue;
                }

                if ($matches->count() > 1) {
                    $this->warn("Multiple records named {$name}; setting all {$matches->count()}.");
                }

                foreach ($matches as $p) {
                    $p->setPartialDate('birthdate', $y, $m, $d);
                    $p->save();
                    $corrected++;
                    $this->info("Corrected {$p->name}: ".$p->formatPartialDate('birthdate'));
                }
            }

            // 2. Downgrade every remaining full-precision January-1 placeholder
            //    to year-only precision (keep the stored year, hide the fake
            //    month/day). Records already at month/year precision are skipped.
            $downgraded = 0;
            $placeholders = Prisoner::withoutGlobalScopes()
                ->whereMonth('birthdate', 1)
                ->whereDay('birthdate', 1)
                ->get();

            foreach ($placeholders as $p) {
                if ($p->datePrecisionFor('birthdate') !== 'day') {
                    continue; // already year/month precision — displays fine
                }

                $year = $p->birthdate->year;
                $p->setPartialDate('birthdate', $year, null, null);
                $p->save();
                $downgraded++;
            }

            $this->info("Done. {$corrected} full dates set; {$downgraded} placeholders reduced to year-only.");
        });

        return self::SUCCESS;
    }
}
