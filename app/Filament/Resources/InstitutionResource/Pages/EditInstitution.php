<?php

namespace App\Filament\Resources\InstitutionResource\Pages;

use App\Filament\Concerns\AutosavesOnBlur;
use App\Filament\Resources\InstitutionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInstitution extends EditRecord {
    use AutosavesOnBlur;

    protected static string $resource = InstitutionResource::class;

    protected function getHeaderActions(): array {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
