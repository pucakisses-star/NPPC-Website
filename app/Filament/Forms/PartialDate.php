<?php

namespace App\Filament\Forms;

use App\Filament\Concerns\HandlesPartialDateForm;
use Filament\Forms\Components\TextInput;

/**
 * A single date field shaped like MM/DD/YYYY that accepts a partial date — a year
 * alone ("1971"), a month and year ("03/1971"), or a full date ("03/14/1971").
 * State lives in `{field}__partial`; the owning page must use
 * {@see HandlesPartialDateForm} to parse/format it against the stored date column
 * + `date_precision`.
 */
class PartialDate
{
    public static function make(string $field, string $label): TextInput
    {
        return TextInput::make("{$field}__partial")
            ->label($label)
            ->placeholder('MM/DD/YYYY')
            ->helperText('Enter a full date, or leave parts off — e.g. 1971, 03/1971, or 03/14/1971.')
            ->maxLength(20);
    }
}
