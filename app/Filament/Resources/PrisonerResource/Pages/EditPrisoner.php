<?php

namespace App\Filament\Resources\PrisonerResource\Pages;

use App\Filament\Concerns\AutosavesOnBlur;
use App\Filament\Concerns\HandlesPartialDateForm;
use App\Filament\Resources\PrisonerResource;
use App\Models\Prisoner;
use Filament\Actions;
use Filament\Notifications\Notification;
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
            Actions\Action::make('moveToTop')
                ->label('Move to top')
                ->icon('heroicon-o-arrow-up-circle')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Move to the top of the list?')
                ->modalDescription('This entry becomes Sort #1. Everything currently above it shifts down one place; the rest of the order is untouched.')
                ->modalSubmitActionLabel('Move to top')
                ->action(function (): void {
                    $moved = PrisonerResource::moveToTop($this->record);

                    // This page autosaves on blur, and Sort # is a field on the
                    // form. Without pulling the new value back in, the box would
                    // still hold the old number and the next blur would write it
                    // straight back over the move. fillForm() rather than the
                    // narrower refreshFormData(): it is what EditAnnualReport
                    // already uses for the same job, so it is known to work on
                    // this Filament version, and on a page that saves every
                    // field on blur there is nothing in-progress to lose.
                    $this->fillForm();

                    Notification::make()
                        ->title($moved ? 'Moved to the top' : 'Already at the top')
                        ->success()
                        ->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
