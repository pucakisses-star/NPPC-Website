<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Fill in missing birthdates from the Jericho Movement's political-prisoner
 * birthdays. The Jericho birthdays page lists only month-day, so the full birth
 * YEAR for each was taken from that prisoner's Jericho profile ("Birthday: Month
 * Day, Year") and cross-checked against the month-day on the birthdays list;
 * three well-documented dates (Albert Woodfox, Aafia Siddiqui, Jeff Fort) come
 * from their verified public biographies. Entries whose two Jericho sources
 * disagreed (Delbert Africa, Charles Sims Africa) or that gave no year were left
 * out rather than guessed.
 *
 * A second batch covers the current/freed prisoners on the same Jericho
 * birthdays page whose profiles supplied a verifiable year that matched the
 * list's month-day: Abdul Azeez (Virgin Islands 5), Marius Mason, Joy Powell,
 * Alvaro Luna Hernandez, Kojo Bomani Sababu and Fred Burton. (Abdul Azeez's
 * profile birthday — Jan. 9, 1947 — is used here, not the August 13, 1973
 * Fountain Valley conviction date.)
 *
 * Sets birthdate ONLY where a matching prisoner currently has none (never
 * overwrites), is variant-aware on the name, and skips prisoners not present.
 */
final class SetJerichoBirthdays extends Command
{
    protected $signature = 'prisoners:set-jericho-birthdays {--overwrite : Replace existing birthdates too}';

    protected $description = 'Fill missing prisoner birthdates from the Jericho Movement birthdays/profiles';

    /** @var array<array{0:string[],1:string}> [name-match fragments, YYYY-MM-DD] */
    private array $map = [
        [['Abdul Azeez', 'Abdul Aziz'], '1947-01-09'],
        [['Sundiata Acoli', 'Acoli'], '1937-01-14'],
        [['Joseph Bowen'], '1948-01-15'],
        [['Marius Mason', 'Marie Mason'], '1962-01-26'],
        [['Veronza'], '1946-02-04'],
        [['Albert Woodfox', 'Woodfox'], '1947-02-19'],
        [['Kamau Sadiki', 'Freddie Hilton'], '1953-02-19'],
        [['Jeff Fort', 'Abdullah Malik'], '1947-02-20'],
        [['Oso Blanco', 'Byron Chubbuck', 'Chubbuck'], '1967-02-26'],
        [['Aafia Siddiqui', 'Siddiqui'], '1972-03-02'],
        [['Joy Powell'], '1962-03-05'],
        [['Ruchell Magee'], '1939-03-17'],
        [['Romaine Fitzgerald', 'Romaine'], '1949-04-11'],
        [['Janet Holloway'], '1951-04-13'],
        [['Mumia', 'Abu-Jamal'], '1954-04-24'],
        [['Alvaro Luna Hernandez', 'Alvaro Luna Hernández', 'Xinachtli'], '1952-05-12'],
        [['Kojo Bomani Sababu', 'Grailing Brown'], '1953-05-27'],
        [['Thomas Manning'], '1946-06-28'],
        [['Bill Dunne', 'William Dunne'], '1954-08-03'],
        [['Mutulu Shakur', 'Mutulu'], '1950-08-08'],
        [['Shoatz'], '1943-08-23'],
        [['Leonard Peltier'], '1944-09-12'],
        [['Jamil Al-Amin', 'Al-Amin'], '1943-10-04'],
        [['David Gilbert'], '1944-10-06'],
        [['Anthony Bottom', 'Jalil'], '1951-10-18'],
        [['Edward Goodman'], '1949-10-21'],
        [['Poindexter'], '1944-11-01'],
        [['Larry Hoover'], '1950-11-30'],
        [['Fred Burton', 'Muhammad Burton'], '1946-12-15'],
    ];

    public function handle(): int
    {
        $overwrite = (bool) $this->option('overwrite');
        $set = 0;
        $had = 0;
        $missing = 0;

        foreach ($this->map as [$fragments, $dob]) {
            $prisoner = null;
            foreach ($fragments as $frag) {
                $prisoner = Prisoner::withUnderReview()->where('name', 'like', '%'.$frag.'%')->first();
                if ($prisoner) {
                    break;
                }
            }

            if (! $prisoner) {
                $this->warn('Not found, skipping: '.$fragments[0]);
                $missing++;

                continue;
            }

            if ($prisoner->birthdate && ! $overwrite) {
                $this->line("  Has birthdate, skipping: {$prisoner->name}");
                $had++;

                continue;
            }

            $prisoner->birthdate = $dob;
            $prisoner->save();
            $this->info("  {$prisoner->name} ← {$dob}");
            $set++;
        }

        $this->info("\nDone. Birthdates set={$set}  Already had={$had}  Not found={$missing}");

        return self::SUCCESS;
    }
}
