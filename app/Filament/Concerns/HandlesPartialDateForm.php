<?php

namespace App\Filament\Concerns;

use App\Filament\Forms\PartialDate;
use Carbon\Carbon;

/**
 * Bridges the calendar picker + "Show as" precision selector built by
 * {@see PartialDate} with the stored full date + `date_precision` JSON. Mix into
 * a Create/Edit page (or relation manager) and list the date columns via
 * partialDateFields().
 *
 *   - splitPartialDates()   — record attributes -> `{field}__date` + `{field}__precision` (form fill)
 *   - combinePartialDates() — those two fields    -> date column + precision (save)
 *
 * The picked date is normalised to the 1st of the month/year for the coarser
 * precisions ("month", "year") so it matches the chosen precision and keeps all
 * date logic working; a blank picker clears the date. "day" precision is the
 * default and is not stored (absent precision renders as a full date).
 */
trait HandlesPartialDateForm
{
    /** Date columns rendered as partial-date fields. Override per resource. */
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
            $value = $data[$field] ?? null;
            $data["{$field}__date"] = $value ? Carbon::parse($value)->format('Y-m-d') : null;
            $data["{$field}__precision"] = $precision[$field] ?? 'day';
        }

        return $data;
    }

    protected function combinePartialDates(array $data): array
    {
        $precision = is_array($data['date_precision'] ?? null) ? $data['date_precision'] : [];

        foreach ($this->partialDateFields() as $field) {
            $date = $data["{$field}__date"] ?? null;
            $prec = $data["{$field}__precision"] ?? 'day';
            unset($data["{$field}__date"], $data["{$field}__precision"]);

            if (! $date) {
                $data[$field] = null;
                unset($precision[$field]);

                continue;
            }

            $prec = in_array($prec, ['year', 'month', 'day'], true) ? $prec : 'day';
            $c = Carbon::parse($date);
            $month = $prec === 'year' ? 1 : (int) $c->month;
            $day = $prec === 'day' ? (int) $c->day : 1;

            $data[$field] = sprintf('%04d-%02d-%02d', (int) $c->year, $month, $day);

            if ($prec === 'day') {
                unset($precision[$field]);
            } else {
                $precision[$field] = $prec;
            }
        }

        $data['date_precision'] = $precision ?: null;

        return $data;
    }
}
