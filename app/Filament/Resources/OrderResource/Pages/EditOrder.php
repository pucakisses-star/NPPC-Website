<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Concerns\AutosavesOnBlur;
use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord {
    use AutosavesOnBlur;

    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array {
        return [Actions\DeleteAction::make()];
    }
}
