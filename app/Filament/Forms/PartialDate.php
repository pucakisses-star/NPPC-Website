<?php

namespace App\Filament\Forms;

use App\Filament\Concerns\HandlesPartialDateForm;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;

/**
 * A date field that can be entered imprecisely. A "Precision" selector chooses
 * how much of the date you want to record, and the input next to it adapts so it
 * only ever asks for the parts that precision needs — which means the browser
 * never complains about an "incomplete" date:
 *
 *   - Full date     -> the normal calendar date picker (MM/DD/YYYY)
 *   - Month & year  -> a month picker (MM/YYYY, no day)
 *   - Year only     -> a plain year box
 *
 * State lives in `{field}__date` / `{field}__month` / `{field}__year` (only the
 * one matching the precision is used) plus `{field}__precision`; the owning page
 * must use {@see HandlesPartialDateForm} to combine/split these with the stored
 * date column + `date_precision`.
 */
class PartialDate
{
    public static function make(string $field, string $label): Group
    {
        $prec = "{$field}__precision";

        return Group::make([
            // The three inputs are live(onBlur) so anything derived from the
            // date -- the age beside a prisoner's life dates, for one --
            // recalculates when you leave the field instead of showing the
            // last-saved value until the next reload.
            DatePicker::make("{$field}__date")
                ->label($label)
                ->live(onBlur: true)
                ->visible(fn (Get $get) => ($get($prec) ?? 'day') === 'day')
                ->columnSpan(2),
            TextInput::make("{$field}__month")
                ->label($label)
                ->type('month')
                ->live(onBlur: true)
                ->visible(fn (Get $get) => $get($prec) === 'month')
                ->columnSpan(2),
            TextInput::make("{$field}__year")
                ->label($label)
                ->numeric()
                ->minValue(1)
                ->maxValue(2100)
                ->placeholder('e.g. 1971')
                ->live(onBlur: true)
                ->visible(fn (Get $get) => $get($prec) === 'year')
                ->columnSpan(2),
            Select::make($prec)
                ->label('Precision')
                ->options([
                    'day' => 'Full date',
                    'month' => 'Month & year',
                    'year' => 'Year only',
                ])
                ->default('day')
                ->selectablePlaceholder(false)
                ->live()
                ->columnSpan(1),
        ])
            ->columns(3)
            ->columnSpanFull();
    }
}
