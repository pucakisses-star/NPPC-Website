<?php

namespace App\Filament\Concerns;

use App\Filament\Forms\PartialDate;
use Carbon\Carbon;

/**
 * Bridges the single MM/DD/YYYY field built by {@see PartialDate} with the stored
 * full date + `date_precision` JSON. Mix into a Create/Edit page (or relation
 * manager) and list the date columns via partialDateFields().
 *
 *   - splitPartialDates()   — record attributes -> `{field}__partial` string (form fill)
 *   - combinePartialDates() — `{field}__partial` string -> date column + precision (save)
 *
 * The user may type just a year ("1971"), a month and year ("03/1971") or a full
 * date ("03/14/1971"). Missing month/day default to 1; a blank year clears the
 * date. The day is clamped to the month length so an impossible date (Feb 31)
 * can't be saved.
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
            $data["{$field}__partial"] = $this->formatPartialInput(
                $data[$field] ?? null,
                $precision[$field] ?? 'day',
            );
        }

        return $data;
    }

    protected function combinePartialDates(array $data): array
    {
        $precision = is_array($data['date_precision'] ?? null) ? $data['date_precision'] : [];

        foreach ($this->partialDateFields() as $field) {
            $raw = $data["{$field}__partial"] ?? null;
            unset($data["{$field}__partial"]);

            [$y, $m, $d] = $this->parsePartialInput((string) $raw);

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

    /** Parse a typed MM/DD/YYYY (or partial) string into [year, month, day]. */
    private function parsePartialInput(string $input): array
    {
        $parts = preg_split('/[^0-9]+/', trim($input)) ?: [];
        $parts = array_values(array_filter($parts, fn ($p) => $p !== ''));
        $n = count($parts);

        return match (true) {
            $n === 0 => [null, null, null],
            $n === 1 => [(int) $parts[0], null, null],            // YYYY
            $n === 2 => [(int) $parts[1], (int) $parts[0], null],  // MM / YYYY
            default => [(int) $parts[2], (int) $parts[0], (int) $parts[1]], // MM / DD / YYYY
        };
    }

    /** Format a stored date + precision back into the MM/DD/YYYY field string. */
    private function formatPartialInput($value, string $precision): ?string
    {
        if (! $value) {
            return null;
        }

        $c = $value instanceof Carbon ? $value : Carbon::parse($value);

        return match ($precision) {
            'year' => $c->format('Y'),
            'month' => $c->format('m/Y'),
            default => $c->format('m/d/Y'),
        };
    }
}
