<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Fills in Joseph P. Kamp's exact birth and death dates (May 3, 1900, Yonkers,
 * NY – June 7, 1993, Jupiter, FL) and records the release date for his 1950
 * contempt-of-Congress term.
 *
 * Sources disagree on the start date — the American Jewish Year Book gives a
 * four-month sentence beginning June 9, 1950 (implying release ~Oct 9); the
 * New York Times reports he was jailed June 16, 1950 (implying release ~Oct 16).
 * His case already records incarceration on June 16, so the release is stored at
 * month precision (October 1950) rather than asserting a disputed exact day.
 * Idempotent.
 */
final class FillJosephKamp extends Command
{
    protected $signature = 'prisoners:fill-joseph-kamp';

    protected $description = 'Set Joseph P. Kamp\'s birth/death dates and his 1950 contempt release (Oct 1950)';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'Joseph P. Kamp')->first();
        if (! $prisoner) {
            $this->error('Joseph P. Kamp not found.');

            return self::FAILURE;
        }

        $prisoner->setPartialDate('birthdate', 1900, 5, 3);
        $prisoner->setPartialDate('death_date', 1993, 6, 7);
        $prisoner->save();
        $this->info('Set dates: b. May 3, 1900, d. June 7, 1993.');

        $case = $prisoner->cases()->first();
        if ($case) {
            $case->sentence = 'Four months in federal prison, beginning June 16, 1950; released about October 1950. (Sources differ on the start date — the American Jewish Year Book gives June 9 and the New York Times June 16 — so his release fell in mid-October 1950, on or about October 9–16.)';
            $case->setPartialDate('release_date', 1950, 10);
            $case->save();
            $this->info('Set release: October 1950 (month precision).');
        } else {
            $this->warn('No case found for Joseph P. Kamp — dates set, release skipped.');
        }

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
