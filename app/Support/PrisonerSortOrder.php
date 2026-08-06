<?php

namespace App\Support;

use App\Models\Prisoner;
use Illuminate\Support\Facades\DB;

/**
 * Places a prisoner into the curated sort_order sequence.
 *
 * sort_order is a global, gap-free-ish sequence with no reconstructible
 * formula: it trends newest-era-first but is interleaved (463 era runs across
 * 8,600 records), and within it records cluster by cohort — the six people
 * arrested together on 2023-01-11 sit on consecutive numbers. It is a curated
 * order, so a new record must be INSERTED next to its peers, never appended
 * at a formula's idea of the right place.
 *
 * A record left at the column default of 0 sorts in front of the entire
 * database on every listing that orders by sort_order, which is how prisoners
 * added without an explicit sort_order were surfacing first on /database.
 *
 * Placement tiers, first match wins:
 *
 *   1. cohort-date  after the last record of the same era sharing an exact
 *                   arrest date — people arrested together stay together
 *                   (the Tougaloo Nine land beside the Tougaloo Nine).
 *   2. cohort-year  after the last record of the same era arrested in the
 *                   same year — catches co-defendants whose rows carry
 *                   different dates of the same episode (an arrest vs. a
 *                   rearrest).
 *   3. era-end      after the era's last record in the sequence, which is
 *                   where that era's most recent additions live.
 *   4. global-end   after everything, when the record has no era and none
 *                   can be derived.
 *
 * The insertion shifts every later record up by one, preserving the curated
 * order exactly. Writes go through the query builder: no model events (a
 * sort-order shift must not trigger 8,000 imprisoned_for_days recomputes)
 * and no touched timestamps.
 */
final class PrisonerSortOrder
{
    /**
     * Era derived from the record's own case dates — only when every dated
     * case falls in a single decade, so nothing is guessed across decades.
     */
    public static function deriveEra(Prisoner $prisoner): ?string
    {
        $decades = [];

        foreach ($prisoner->cases as $case) {
            foreach (['arrest_date', 'incarceration_date', 'release_date', 'sentenced_date'] as $field) {
                if ($case->{$field}) {
                    $decades[intdiv((int) $case->{$field}->format('Y'), 10) * 10] = true;
                }
            }
        }

        return count($decades) === 1 ? array_key_first($decades).'s' : null;
    }

    /**
     * Insert the prisoner into the sequence and return how, for reporting:
     * ['method' => ..., 'after' => sort_order it now follows, 'sort_order' => its new value].
     *
     * $era is passed rather than read from the model so a caller that derived
     * it can decide separately whether to persist it.
     */
    public static function place(Prisoner $prisoner, ?string $era): array
    {
        $anchor = null;
        $method = 'global-end';

        $arrestDates = $prisoner->cases
            ->pluck('arrest_date')
            ->filter()
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->unique()
            ->values();

        if ($era && $arrestDates->isNotEmpty()) {
            // whereDate/whereYear, not raw DATE()/YEAR(): the production
            // connection is SQLite (despite what older docs say), which has
            // no YEAR() function. Laravel's date-based wheres compile to the
            // right SQL per grammar — strftime on SQLite, DATE/YEAR on MySQL.
            $anchor = self::peerQuery($prisoner, $era)
                ->where(function ($q) use ($arrestDates) {
                    foreach ($arrestDates as $d) {
                        $q->orWhereDate('prisoner_cases.arrest_date', $d);
                    }
                })
                ->max('prisoners.sort_order');
            $method = 'cohort-date';

            if ($anchor === null) {
                $years = $arrestDates->map(fn ($d) => (int) substr($d, 0, 4))->unique()->values();
                $anchor = self::peerQuery($prisoner, $era)
                    ->where(function ($q) use ($years) {
                        foreach ($years as $y) {
                            $q->orWhereYear('prisoner_cases.arrest_date', $y);
                        }
                    })
                    ->max('prisoners.sort_order');
                $method = 'cohort-year';
            }
        }

        if ($anchor === null && $era) {
            $anchor = DB::table('prisoners')
                ->where('era', $era)->where('sort_order', '>', 0)
                ->where('id', '!=', $prisoner->id)
                ->max('sort_order');
            $method = 'era-end';
        }

        if ($anchor === null) {
            $anchor = (int) DB::table('prisoners')->where('sort_order', '>', 0)->max('sort_order');
            $method = 'global-end';
        }

        DB::table('prisoners')->where('sort_order', '>', $anchor)->increment('sort_order');
        DB::table('prisoners')->where('id', $prisoner->id)->update(['sort_order' => $anchor + 1]);
        $prisoner->refresh();

        return ['method' => $method, 'after' => (int) $anchor, 'sort_order' => $anchor + 1];
    }

    /** Peers of the same era with case rows, for the cohort tiers. */
    private static function peerQuery(Prisoner $prisoner, string $era)
    {
        return DB::table('prisoners')
            ->join('prisoner_cases', 'prisoner_cases.prisoner_id', '=', 'prisoners.id')
            ->where('prisoners.era', $era)
            ->where('prisoners.sort_order', '>', 0)
            ->where('prisoners.id', '!=', $prisoner->id)
            ->whereNotNull('prisoner_cases.arrest_date');
    }
}
