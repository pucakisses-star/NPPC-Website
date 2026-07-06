<?php

namespace App\Models\Concerns;

use Carbon\Carbon;

/**
 * Partial / imprecise dates. A date column always holds a full Y-M-D (missing
 * parts default to 01) so all date logic keeps working; the companion
 * `date_precision` JSON column remembers, per field, how much the user actually
 * entered — 'year', 'month' or 'day' — so display can show only those parts.
 *
 * Models using this trait should cast `date_precision` to 'array' and may list
 * their partial-date columns via partialDateFields() (used by the admin forms).
 */
trait HasPartialDates
{
    /** Date columns that support partial entry. Override per model. */
    public function partialDateFields(): array
    {
        return [];
    }

    /** 'year' | 'month' | 'day'. Anything unknown (incl. legacy rows) is full 'day'. */
    public function datePrecisionFor(string $field): string
    {
        $p = $this->date_precision[$field] ?? null;

        return in_array($p, ['year', 'month', 'day'], true) ? $p : 'day';
    }

    /** ['year'=>?int,'month'=>?int,'day'=>?int] with parts the user didn't enter as null. */
    public function partialDateParts(string $field): array
    {
        $value = $this->{$field};
        if (! $value) {
            return ['year' => null, 'month' => null, 'day' => null];
        }

        $c = $value instanceof Carbon ? $value : Carbon::parse($value);
        $precision = $this->datePrecisionFor($field);

        return [
            'year' => (int) $c->year,
            'month' => $precision === 'year' ? null : (int) $c->month,
            'day' => $precision === 'day' ? (int) $c->day : null,
        ];
    }

    /**
     * Human display honouring precision: "1919", "June 1919" or "Jun 1, 1919".
     * Pass a custom day-level format via $dayFormat (default 'M j, Y').
     */
    public function formatPartialDate(string $field, string $dayFormat = 'M j, Y'): ?string
    {
        $value = $this->{$field};
        if (! $value) {
            return null;
        }

        $c = $value instanceof Carbon ? $value : Carbon::parse($value);

        return match ($this->datePrecisionFor($field)) {
            'year' => $c->format('Y'),
            'month' => $c->format('F Y'),
            default => $c->format($dayFormat),
        };
    }

    /**
     * ISO display truncated to precision: "1919", "1919-06" or "1919-06-15"
     * (null when unset). Used by the JSON API so the front end never shows a
     * defaulted "-01" day/month the data doesn't actually have.
     */
    public function partialDateIso(string $field): ?string
    {
        $value = $this->{$field};
        if (! $value) {
            return null;
        }

        $c = $value instanceof Carbon ? $value : Carbon::parse($value);

        return match ($this->datePrecisionFor($field)) {
            'year' => $c->format('Y'),
            'month' => $c->format('Y-m'),
            default => $c->format('Y-m-d'),
        };
    }

    /**
     * Set a date from its parts, defaulting missing month/day to 1 and recording
     * the precision. A null/empty year clears the date entirely.
     */
    public function setPartialDate(string $field, ?int $year, ?int $month = null, ?int $day = null): void
    {
        $month = $month ?: null;
        $day = $day ?: null;

        if (! $year) {
            $this->{$field} = null;
            $this->setDatePrecision($field, null);

            return;
        }

        $this->{$field} = sprintf('%04d-%02d-%02d', $year, $month ?: 1, $day ?: 1);
        $this->setDatePrecision($field, $day ? 'day' : ($month ? 'month' : 'year'));
    }

    /** Copy a field's precision onto another (used when one date is derived from another). */
    public function mirrorDatePrecision(string $from, string $to): void
    {
        $this->setDatePrecision($to, $this->date_precision[$from] ?? null);
    }

    protected function setDatePrecision(string $field, ?string $precision): void
    {
        $map = $this->date_precision ?? [];

        if ($precision === null) {
            unset($map[$field]);
        } else {
            $map[$field] = $precision;
        }

        $this->date_precision = $map ?: null;
    }
}
