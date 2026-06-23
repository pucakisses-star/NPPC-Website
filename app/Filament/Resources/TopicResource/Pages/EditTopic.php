<?php

namespace App\Filament\Resources\TopicResource\Pages;

use App\Filament\Concerns\AutosavesOnBlur;
use App\Filament\Resources\TopicResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTopic extends EditRecord {
    use AutosavesOnBlur;

    protected static string $resource = TopicResource::class;

    protected function getHeaderActions(): array {
        return [Actions\DeleteAction::make()];
    }
}
