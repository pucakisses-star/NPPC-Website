<?php

namespace App\Filament\Resources\DashboardLinkResource\Pages;

use App\Filament\Resources\DashboardLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDashboardLinks extends ListRecords {
    protected static string $resource = DashboardLinkResource::class;

    protected function getHeaderActions(): array {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
