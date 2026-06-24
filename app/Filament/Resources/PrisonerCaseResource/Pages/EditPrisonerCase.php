<?php

namespace App\Filament\Resources\PrisonerCaseResource\Pages;

use App\Filament\Concerns\AutosavesOnBlur;
use App\Filament\Concerns\HandlesPartialDateForm;
use App\Filament\Resources\PrisonerCaseResource;
use App\Models\PrisonerCase;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPrisonerCase extends EditRecord
{
    use AutosavesOnBlur;
    use HandlesPartialDateForm;

    protected static string $resource = PrisonerCaseResource::class;

    protected function partialDateFields(): array
    {
        return (new PrisonerCase)->partialDateFields();
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->splitPartialDates($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->combinePartialDates($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
