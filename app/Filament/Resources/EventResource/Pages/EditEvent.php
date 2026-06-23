<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Concerns\AutosavesOnBlur;
use App\Filament\Resources\EventResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEvent extends EditRecord {
    use AutosavesOnBlur;

    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array {
        return [Actions\DeleteAction::make()];
    }
}
