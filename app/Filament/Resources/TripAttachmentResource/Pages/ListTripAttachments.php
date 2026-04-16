<?php

namespace App\Filament\Resources\TripAttachmentResource\Pages;

use App\Filament\Resources\TripAttachmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTripAttachments extends ListRecords {
    protected static string $resource = TripAttachmentResource::class;

    protected function getHeaderActions(): array {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
