<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-time cleanup for the 1954 Connecticut Smith Act cluster.
 *
 * The original ng-1954-a.json roster carried three OCR-garbled / spurious
 * names that were deployed before they could be verified against the court
 * record (United States v. Silverman, 248 F.2d 671, 2d Cir. 1957):
 *
 *   Joseph Dinan   -> should be Joseph Dimow
 *   Jacob Golding  -> should be Jacob Goldring
 *   Sidney Resnek  -> not a defendant at all; the fifth real defendant is
 *                     Robert Champion Ekins (CP Connecticut state secretary)
 *
 * Because prisoners:add-ng-1954 is idempotent (skips by exact name), it will
 * happily insert the corrected names but cannot remove the stale rows. This
 * command deletes the affected prisoners (and their cases) so that a follow-up
 * `php artisan prisoners:add-ng-1954` re-inserts the whole Connecticut cluster
 * from the corrected roster. Simon Silverman is included because his name was
 * already right but his bio was refreshed (alias "Sid Taylor" + case citation),
 * and the idempotent loader would otherwise skip him. Safe to run more than
 * once — it only touches these exact names and re-adding restores them.
 */
class FixConnecticut1954Names extends Command
{
    protected $signature = 'prisoners:fix-connecticut-1954';

    protected $description = 'Remove stale OCR-garbled 1954 Connecticut Smith Act names so the corrected records can be re-added';

    /**
     * Names currently in the DB to delete so the corrected cluster re-inserts:
     * the first three are wrong (replaced by Dimow / Goldring / Ekins); Simon
     * Silverman is deleted only to refresh his bio (his name is unchanged).
     */
    private const STALE_NAMES = ['Joseph Dinan', 'Jacob Golding', 'Sidney Resnek', 'Simon Silverman'];

    public function handle(): int
    {
        $deleted = 0;

        foreach (self::STALE_NAMES as $name) {
            $prisoner = Prisoner::withUnderReview()->where('name', $name)->first();

            if (! $prisoner) {
                $this->warn("Not present (already clean): {$name}");
                continue;
            }

            DB::transaction(function () use ($prisoner) {
                $prisoner->cases()->delete();
                $prisoner->delete();
            });

            $this->info("Deleted stale record: {$name}");
            $deleted++;
        }

        $this->info("\nDone. Deleted={$deleted}. Now run: php artisan prisoners:add-ng-1954");

        return self::SUCCESS;
    }
}
