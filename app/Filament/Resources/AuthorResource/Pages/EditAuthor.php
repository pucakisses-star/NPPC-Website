<?php

namespace App\Filament\Resources\AuthorResource\Pages;

use App\Filament\Concerns\AutosavesOnBlur;
use App\Filament\Resources\AuthorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAuthor extends EditRecord {
    use AutosavesOnBlur;

    protected static string $resource = AuthorResource::class;

    protected function getHeaderActions(): array {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
