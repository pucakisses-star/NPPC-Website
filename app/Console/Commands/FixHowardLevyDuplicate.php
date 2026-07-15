<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Consolidates the two duplicate entries for Captain Howard Levy — the Army
 * doctor court-martialed in 1967 for refusing to train Green Beret medics and
 * imprisoned 26 months at Fort Leavenworth (Parker v. Levy). The "howard-levy"
 * record is the fuller one (case record, ideology, Parker v. Levy); the
 * "captain-howard-levy" duplicate only adds his birth date. Ports the birth
 * date over and deletes the duplicate. Idempotent.
 */
final class FixHowardLevyDuplicate extends Command
{
    protected $signature = 'prisoners:fix-howard-levy-duplicate';

    protected $description = 'Merge the duplicate Howard Levy entries into one (keep howard-levy, delete captain-howard-levy)';

    public function handle(): int
    {
        $keep = Prisoner::withUnderReview()->where('slug', 'howard-levy')->first();
        $dup = Prisoner::withUnderReview()->where('slug', 'captain-howard-levy')->first();

        if (! $keep) {
            $this->warn('Primary record "howard-levy" not found — nothing to do.');

            return self::SUCCESS;
        }

        // Port the birth date from the duplicate (or a known value) if missing.
        if (empty($keep->birthdate)) {
            $keep->setPartialDate('birthdate', 1937, 4, 10);
            $keep->save();
            $this->info('Set Howard Levy birthdate: 1937-04-10.');
        }

        if ($dup) {
            // Preserve a photo only if the keeper somehow lacks one.
            if (empty($keep->photo) && ! empty($dup->photo)) {
                $keep->photo = $dup->photo;
                $keep->save();
                $this->info('Ported photo from the duplicate.');
            }
            $dup->cases()->delete();
            $dup->delete();
            $this->info('Deleted duplicate "captain-howard-levy".');
        } else {
            $this->info('Duplicate "captain-howard-levy" already gone.');
        }

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
