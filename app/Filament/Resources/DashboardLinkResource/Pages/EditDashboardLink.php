<?php

namespace App\Filament\Resources\DashboardLinkResource\Pages;

use App\Filament\Concerns\AutosavesOnBlur;
use App\Filament\Resources\DashboardLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDashboardLink extends EditRecord {
    use AutosavesOnBlur;

    protected static string $resource = DashboardLinkResource::class;

    protected function getHeaderActions(): array {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
