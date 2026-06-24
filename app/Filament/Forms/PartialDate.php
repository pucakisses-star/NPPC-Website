<?php

namespace App\Filament\Forms;

use App\Filament\Concerns\HandlesPartialDateForm;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

/**
 * A "partial date" entry: Year / Month / Day, where Month and Day may be left
 * blank to record an imprecise date (just a year, or a month + year). Renders as
 * three fields whose state lives under `{field}__y/__m/__d`; the owning page must
 * use {@see HandlesPartialDateForm} to combine/split them
 * with the stored date column + `date_precision`.
 */
class PartialDate
{
    public static function make(string $field, string $label): Fieldset
    {
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];
        $days = array_combine(range(1, 31), range(1, 31));

        return Fieldset::make($label)
            ->schema([
                TextInput::make("{$field}__y")
                    ->label('Year')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(2100)
                    ->placeholder('e.g. 1971')
                    ->helperText('Leave month and/or day blank for a partial date'),
                Select::make("{$field}__m")
                    ->label('Month')
                    ->options($months)
                    ->placeholder('—'),
                Select::make("{$field}__d")
                    ->label('Day')
                    ->options($days)
                    ->placeholder('—'),
            ])
            ->columns(3)
            ->columnSpanFull();
    }
}
