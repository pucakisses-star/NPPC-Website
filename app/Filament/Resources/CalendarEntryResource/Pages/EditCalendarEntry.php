<?php

namespace App\Filament\Resources\CalendarEntryResource\Pages;

use App\Filament\Concerns\AutosavesOnBlur;
use App\Filament\Resources\CalendarEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCalendarEntry extends EditRecord {
    use AutosavesOnBlur;

    protected static string $resource = CalendarEntryResource::class;

    protected function getHeaderActions(): array {
        return [Actions\DeleteAction::make()];
    }
}
