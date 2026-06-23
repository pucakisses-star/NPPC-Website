<?php

namespace App\Filament\Resources\PrisonerResource\Pages;

use App\Filament\Concerns\AutosavesOnBlur;
use App\Filament\Resources\PrisonerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPrisoner extends EditRecord {
    use AutosavesOnBlur;

    protected static string $resource = PrisonerResource::class;

    protected function getHeaderActions(): array {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
