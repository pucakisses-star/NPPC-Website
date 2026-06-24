<?php

namespace App\Filament\Concerns;

use App\Filament\Forms\PartialDate;
use Carbon\Carbon;

/**
 * Bridges the Year/Month/Day form fields built by {@see PartialDate}
 * with the stored full date + `date_precision` JSON. Mix into a Create/Edit page
 * (or relation manager) and list the date columns via partialDateFields().
 *
 *   - splitPartialDates()   — record attributes  -> `{field}__y/__m/__d` (form fill)
 *   - combinePartialDates() — `{field}__y/__m/__d` -> date column + precision (save)
 *
 * Missing month/day default to 1; a blank year clears the date. The day is
 * clamped to the month length so an impossible date (e.g. Feb 31) can't be saved.
 */
trait HandlesPartialDateForm
{
    /** Date columns rendered as Year/Month/Day groups. Override per resource. */
    protected function partialDateFields(): array
    {
        return [];
    }

    protected function splitPartialDates(array $data): array
    {
        $precision = $data['date_precision'] ?? [];
        if (! is_array($precision)) {
            $precision = json_decode((string) $precision, true) ?: [];
        }

        foreach ($this->partialDateFields() as $field) {
            $prec = $precision[$field] ?? 'day';
            $y = $m = $d = null;

            if (! empty($data[$field])) {
                $c = Carbon::parse($data[$field]);
                $y = (int) $c->year;
                $m = $prec === 'year' ? null : (int) $c->month;
                $d = $prec === 'day' ? (int) $c->day : null;
            }

            $data["{$field}__y"] = $y;
            $data["{$field}__m"] = $m;
            $data["{$field}__d"] = $d;
        }

        return $data;
    }

    protected function combinePartialDates(array $data): array
    {
        $precision = is_array($data['date_precision'] ?? null) ? $data['date_precision'] : [];

        foreach ($this->partialDateFields() as $field) {
            $y = $data["{$field}__y"] ?? null;
            $m = $data["{$field}__m"] ?? null;
            $d = $data["{$field}__d"] ?? null;
            unset($data["{$field}__y"], $data["{$field}__m"], $data["{$field}__d"]);

            if (! $y) {
                $data[$field] = null;
                unset($precision[$field]);

                continue;
            }

            $month = (int) ($m ?: 1);
            $maxDay = (int) Carbon::create((int) $y, $month, 1)->daysInMonth;
            $day = $d ? min((int) $d, $maxDay) : 1;

            $data[$field] = sprintf('%04d-%02d-%02d', (int) $y, $month, $day);
            $precision[$field] = $d ? 'day' : ($m ? 'month' : 'year');
        }

        $data['date_precision'] = $precision ?: null;

        return $data;
    }
}
