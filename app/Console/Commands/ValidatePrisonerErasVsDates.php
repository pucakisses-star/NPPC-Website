<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sanity-check the era vs. birth/death dates for prisoners whose
 * Wikipedia-sourced data might have landed on the wrong person.
 *
 * Rules (any one fires a "wrong-person" verdict):
 *   - birth_year > era_end_year - 12 (would be too young — under 12 at
 *     the very end of the era they're tagged with)
 *   - death_year < era_start_year (already dead when the era started)
 *
 * On a wrong-person verdict, clears birthdate + death_date + photo
 * (deletes the file from public storage). Era / case data is left
 * intact since those came from the original imports, not Wikipedia.
 *
 * Skips prisoners whose era isn't a decade like "1910s" — we can't
 * compute a range without it.
 */
final class ValidatePrisonerErasVsDates extends Command {
    protected $signature = 'archive:validate-prisoner-eras-vs-dates {--dry-run} {--limit=0}';
    protected $description = 'Clear obviously-wrong Wikipedia-sourced birthdate/death/photo when they conflict with the era';

    public function handle(): int {
        $dry = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');

        $query = Prisoner::query()
            ->whereNotNull('era')
            ->where(function ($q) {
                $q->whereNotNull('birthdate')->orWhereNotNull('death_date');
            });
        if ($limit > 0) {
            $query->limit($limit);
        }
        $prisoners = $query->get();
        $this->info("Checking {$prisoners->count()} prisoners with era + dates…");

        $cleared = 0;
        $kept = 0;
        $skippedNoEra = 0;

        foreach ($prisoners as $p) {
            if (! preg_match('/^(\\d{4})s$/', (string) $p->era, $m)) {
                $skippedNoEra++;

                continue;
            }
            $eraStart = (int) $m[1];
            // Pre-1900 eras like "1700s" / "1800s" denote centuries in the NPPC
            // dataset (Alien & Sedition Act, abolitionists, Haymarket, etc.).
            // Eras from 1900s onward denote decades.
            $eraEnd = $eraStart < 1900 ? $eraStart + 99 : $eraStart + 9;
            $tooYoungThreshold = $eraEnd - 12;

            $birthYear = $p->birthdate ? (int) substr((string) $p->birthdate, 0, 4) : null;
            $deathYear = $p->death_date ? (int) substr((string) $p->death_date, 0, 4) : null;

            $wrong = false;
            $reasons = [];

            if ($birthYear !== null && $birthYear > $tooYoungThreshold) {
                $wrong = true;
                $reasons[] = "born {$birthYear}, era ends {$eraEnd} (would be ".($eraEnd - $birthYear)." at era end)";
            }
            if ($deathYear !== null && $deathYear < $eraStart) {
                $wrong = true;
                $reasons[] = "died {$deathYear} before era starts {$eraStart}";
            }

            if (! $wrong) {
                $kept++;

                continue;
            }

            $reasonStr = implode('; ', $reasons);
            $this->warn("CLEAR {$p->name} (era {$p->era}): {$reasonStr}");

            if (! $dry) {
                if ($p->photo) {
                    Storage::disk('public')->delete($p->photo);
                }
                $p->birthdate = null;
                $p->death_date = null;
                $p->photo = null;
                $p->save();
            }
            $cleared++;
        }

        $verb = $dry ? 'would clear' : 'cleared';
        $this->info("\nDone. {$verb}={$cleared} kept={$kept} skipped_no_era={$skippedNoEra} ".($dry ? '(DRY RUN)' : ''));

        return self::SUCCESS;
    }
}
