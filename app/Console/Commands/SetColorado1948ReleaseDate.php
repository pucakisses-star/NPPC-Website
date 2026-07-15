<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Sets the bail-release date for the five Colorado Communists jailed for
 * contempt of a federal grand jury in Denver in 1948 (Rogers/Wertheimer/Blau/
 * Bary/Kleinbord v. United States, 179 F.2d 559). They were released on bail on
 * Thursday, November 4, 1948 by U.S. Supreme Court Justice Wiley B. Rutledge.
 *
 * Sources: a UPI news photo dated Nov 5, 1948 ("released from county jail on
 * bail Thursday," showing Bary and Kleinbord) and The Colorado Statesman,
 * Dec 11, 1948, which reports the five "were released … five weeks ago" by
 * Justice Rutledge. The exact arrest/jailing date is NOT documented in the
 * available sources and is therefore left unset (no duration is asserted).
 *
 * Only sets the release date where it is currently empty; idempotent.
 */
class SetColorado1948ReleaseDate extends Command
{
    protected $signature = 'prisoners:set-colorado-1948-release-date';

    protected $description = 'Set the Nov 4, 1948 bail-release date on the five Colorado Communist grand-jury contempt defendants';

    private const RELEASE = '1948-11-04';

    public function handle(): int
    {
        $names = ['Arthur Bary', 'Paul Kleinbord', 'Irving Blau', 'Nancy Wertheimer', 'Jane Rogers'];

        DB::transaction(function () use ($names) {
            foreach ($names as $name) {
                $prisoner = Prisoner::withUnderReview()->where('name', $name)->first();
                if (! $prisoner) {
                    $this->warn("Not found, skipping: {$name}");

                    continue;
                }

                $case = $prisoner->cases()->first();
                if (! $case) {
                    $this->warn("No case on record, skipping: {$name}");

                    continue;
                }

                if ($case->release_date) {
                    $this->line("Already has a release date, leaving: {$name} ({$case->release_date->format('Y-m-d')})");

                    continue;
                }

                $case->release_date = self::RELEASE;
                $case->save();
                $this->info("Set release date {$name}: ".self::RELEASE);
            }
        });

        return self::SUCCESS;
    }
}
