<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Corrects Richard Williams (United Freedom Front): he was captured in
 * Cleveland on November 4, 1984 — his 37th birthday — so his date of birth
 * is November 4, 1947, and his arrest/incarceration both date to Nov 4, 1984.
 * (An existing record wrongly listed the April 1985 capture of his
 * co-defendant Thomas Manning.)
 *
 * Two duplicate records exist for him (richard-charles-williams and
 * richard-williams); this updates both so the dates are correct regardless
 * of which is viewed. Idempotent.
 */
final class UpdateRichardWilliams extends Command
{
    protected $signature = 'prisoners:update-richard-williams';

    protected $description = 'Set Richard Williams (UFF) DOB and Nov 4 1984 capture date';

    private const DOB = '1947-11-04';

    private const CAPTURE = '1984-11-04';

    public function handle(): int
    {
        $found = 0;

        foreach (['richard-charles-williams', 'richard-williams'] as $slug) {
            $prisoner = Prisoner::withUnderReview()->where('slug', $slug)->first();
            if (! $prisoner) {
                $this->warn("Not found: {$slug}");

                continue;
            }

            $prisoner->birthdate = self::DOB;
            $prisoner->save();

            $case = $prisoner->cases()->first();
            if ($case) {
                $case->arrest_date = self::CAPTURE;
                $case->incarceration_date = self::CAPTURE;
                $case->save();
                $this->info("Updated {$slug}: DOB ".self::DOB.', arrest/incarceration '.self::CAPTURE);
            } else {
                $this->info("Updated {$slug}: DOB ".self::DOB.' (no case to date)');
            }
            $found++;
        }

        $this->info("\nDone. Updated {$found} record(s).");

        return self::SUCCESS;
    }
}
