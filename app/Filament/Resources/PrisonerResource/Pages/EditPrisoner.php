<?php

namespace App\Filament\Resources\PrisonerResource\Pages;

use App\Filament\Concerns\AutosavesOnBlur;
use App\Filament\Concerns\HandlesPartialDateForm;
use App\Filament\Resources\PrisonerResource;
use App\Models\Prisoner;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPrisoner extends EditRecord
{
    use AutosavesOnBlur;
    use HandlesPartialDateForm;

    protected static string $resource = PrisonerResource::class;

    protected function partialDateFields(): array
    {
        return (new Prisoner)->partialDateFields();
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
