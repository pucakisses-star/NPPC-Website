<?php

namespace App\Filament\Resources\PrisonerCaseResource\Pages;

use App\Filament\Concerns\HandlesPartialDateForm;
use App\Filament\Resources\PrisonerCaseResource;
use App\Models\PrisonerCase;
use Filament\Resources\Pages\CreateRecord;

class CreatePrisonerCase extends CreateRecord
{
    use HandlesPartialDateForm;

    protected static string $resource = PrisonerCaseResource::class;

    protected function partialDateFields(): array
    {
        return (new PrisonerCase)->partialDateFields();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->combinePartialDates($data);
    }
}
