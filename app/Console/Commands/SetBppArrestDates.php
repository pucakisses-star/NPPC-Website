<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Backfills arrest dates on the Black Panther newspaper-era cases that were
 * first added without one (their exact dates were unverified at the time).
 *
 * Documented: the New Haven headquarters raid (May 22, 1969) that took Kimbro,
 * Edwards and the women; Loretta Luckes (May 28, 1969); and the Monterey County
 * grand-jury indictment of the Soledad Brothers (Feb 14, 1970 — Drumgo and
 * Clutchette were already imprisoned, so this is the charge date, not a street
 * arrest). McLucas (Salt Lake City) and Sams (Toronto) are documented only to
 * the month, so their dates are approximate and marked below.
 *
 * Idempotent: only writes a date that differs from what's stored, and matches
 * the (single) case of each named prisoner.
 */
final class SetBppArrestDates extends Command
{
    protected $signature = 'prisoners:set-bpp-arrest-dates';

    protected $description = 'Backfill arrest dates on Black Panther-era prisoner cases added without one';

    public function handle(): int
    {
        $dates = [
            // New Haven BPP headquarters raid, May 22, 1969 (documented)
            'Warren Kimbro' => '1969-05-22',
            'Frances Carter' => '1969-05-22',
            'Margaret Hudgins' => '1969-05-22',
            'Rose Marie Smith' => '1969-05-22',
            'George Edwards' => '1969-05-22',
            // Arrested while visiting New Haven, May 28, 1969 (documented)
            'Loretta Luckes' => '1969-05-28',
            // Approximate — Salt Lake City, about a month after the May 21 killing
            'Lonnie McLucas' => '1969-06-21',
            // Approximate — captured in Toronto, August 1969
            'George Sams Jr.' => '1969-08-15',
            // Monterey County grand-jury indictment for the Mills murder; both
            // were already imprisoned at Soledad (charge date, not a new arrest)
            'Fleeta Drumgo' => '1970-02-14',
            'John Clutchette' => '1970-02-14',
        ];

        $set = 0;
        $already = 0;
        $missing = 0;

        foreach ($dates as $name => $date) {
            $prisoner = Prisoner::withUnderReview()->where('name', $name)->first();
            if (! $prisoner) {
                $this->warn("Not found: {$name}");
                $missing++;

                continue;
            }
            $case = $prisoner->cases()->first();
            if (! $case) {
                $this->warn("No case: {$name}");
                $missing++;

                continue;
            }
            if ($case->arrest_date && $case->arrest_date->format('Y-m-d') === $date) {
                $this->line("Already set: {$name} ({$date})");
                $already++;

                continue;
            }
            $case->arrest_date = $date;
            $case->save();
            $this->info("Set {$name} -> {$date}");
            $set++;
        }

        $this->info("\nDone. set={$set} already={$already} missing={$missing}");

        return self::SUCCESS;
    }
}
