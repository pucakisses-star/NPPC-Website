<?php

namespace App\Filament\Resources\PrisonerResource\Pages;

use App\Filament\Concerns\HandlesPartialDateForm;
use App\Filament\Resources\PrisonerResource;
use App\Models\Prisoner;
use Filament\Resources\Pages\CreateRecord;

class CreatePrisoner extends CreateRecord
{
    use HandlesPartialDateForm;

    protected static string $resource = PrisonerResource::class;

    protected function partialDateFields(): array
    {
        return (new Prisoner)->partialDateFields();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->combinePartialDates($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
