<?php

namespace App\Filament\Forms;

use App\Filament\Concerns\HandlesPartialDateForm;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;

/**
 * The standard calendar date picker plus a small "Show as" selector so a date
 * can be recorded imprecisely — Full date, Month & year, or Year only. Pick a
 * date in the calendar as normal; the selector controls how much of it is shown
 * publicly (and the stored date is normalised to the 1st of the month/year for
 * the coarser precisions).
 *
 * State lives in `{field}__date` (the picked date) and `{field}__precision`; the
 * owning page must use {@see HandlesPartialDateForm} to combine/split these with
 * the stored date column + `date_precision`.
 */
class PartialDate
{
    public static function make(string $field, string $label): Group
    {
        return Group::make([
            DatePicker::make("{$field}__date")
                ->label($label)
                ->columnSpan(2),
            Select::make("{$field}__precision")
                ->label('Show as')
                ->options([
                    'day' => 'Full date',
                    'month' => 'Month & year',
                    'year' => 'Year only',
                ])
                ->default('day')
                ->selectablePlaceholder(false)
                ->columnSpan(1),
        ])
            ->columns(3)
            ->columnSpanFull();
    }
}
