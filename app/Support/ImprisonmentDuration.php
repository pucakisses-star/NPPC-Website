<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

final class ImprisonmentDuration
{
    /**
     * Break a span of $days that began on $start into calendar-accurate
     * [years, months, days].
     *
     * Anchoring to the real start date and letting Carbon walk the calendar
     * (instead of dividing by fixed 365-day years and 30-day months) means a
     * span such as May 13 → Dec 13 reads as exactly 7 months, not the
     * "7 months 4 days" you get from 214 ÷ 30. When no start date is known we
     * anchor at today minus the span, so the result is still real calendar
     * units rather than a 30-day-month fiction.
     *
     * @param  CarbonInterface|string|null  $start
     * @return array{years:int,months:int,days:int}
     */
    public static function breakdown($start, int $days): array
    {
        if ($days <= 0) {
            return ['years' => 0, 'months' => 0, 'days' => 0];
        }

        $start = $start ? Carbon::parse($start)->startOfDay() : Carbon::today()->subDays($days);
        $diff = $start->diff($start->copy()->addDays($days));

        return ['years' => $diff->y, 'months' => $diff->m, 'days' => $diff->d];
    }
}
