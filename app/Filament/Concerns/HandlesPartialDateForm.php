<?php

namespace App\Filament\Concerns;

use App\Filament\Forms\PartialDate;
use Carbon\Carbon;

/**
 * Bridges the precision-driven date field built by {@see PartialDate} with the
 * stored full date + `date_precision` JSON. Mix into a Create/Edit page (or
 * relation manager) and list the date columns via partialDateFields().
 *
 *   - splitPartialDates()   — record attributes -> the `{field}__date|month|year`
 *                             field matching the stored precision (form fill)
 *   - combinePartialDates() — the field matching `{field}__precision` -> date
 *                             column + precision (save)
 *
 * The stored date is always a full Y-M-D (month/day default to 01 for the coarser
 * precisions) so all date logic keeps working. "day" precision is the default and
 * is not stored (absent precision renders as a full date).
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
            $prec = $precision[$field] ?? 'day';
            $data["{$field}__precision"] = $prec;
            $data["{$field}__date"] = null;
            $data["{$field}__month"] = null;
            $data["{$field}__year"] = null;

            if (empty($data[$field])) {
                continue;
            }

            $c = Carbon::parse($data[$field]);
            match ($prec) {
                'circa', 'year' => $data["{$field}__year"] = (int) $c->year,
                'month' => $data["{$field}__month"] = $c->format('Y-m'),
                default => $data["{$field}__date"] = $c->format('Y-m-d'),
            };
        }

        return $data;
    }

    protected function combinePartialDates(array $data): array
    {
        $precision = is_array($data['date_precision'] ?? null) ? $data['date_precision'] : [];

        foreach ($this->partialDateFields() as $field) {
            $prec = $data["{$field}__precision"] ?? 'day';
            $prec = in_array($prec, ['circa', 'year', 'month', 'day'], true) ? $prec : 'day';
            $date = $data["{$field}__date"] ?? null;
            $month = $data["{$field}__month"] ?? null;
            $year = $data["{$field}__year"] ?? null;
            unset(
                $data["{$field}__date"], $data["{$field}__month"],
                $data["{$field}__year"], $data["{$field}__precision"],
            );

            $stored = match (true) {
                in_array($prec, ['year', 'circa'], true) && $year => sprintf('%04d-01-01', (int) $year),
                $prec === 'month' && $month => Carbon::parse($month.'-01')->format('Y-m-d'),
                $prec === 'day' && $date => Carbon::parse($date)->format('Y-m-d'),
                default => null,
            };

            if (! $stored) {
                $data[$field] = null;
                unset($precision[$field]);

                continue;
            }

            $data[$field] = $stored;

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
