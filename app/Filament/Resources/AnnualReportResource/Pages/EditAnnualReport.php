<?php

namespace App\Filament\Resources\AnnualReportResource\Pages;

use App\Filament\Concerns\AutosavesOnBlur;
use App\Filament\Resources\AnnualReportResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditAnnualReport extends EditRecord {
    use AutosavesOnBlur;

    protected static string $resource = AnnualReportResource::class;

    protected function getHeaderActions(): array {
        return [
            Actions\Action::make('generateCover')
                ->label('Generate Cover from PDF')
                ->icon('heroicon-o-photo')
                ->color('info')
                ->visible(fn () => $this->record->file && extension_loaded('imagick'))
                ->requiresConfirmation()
                ->modalDescription('This will generate a cover image from the first page of the PDF. Any existing cover image will be replaced.')
                ->action(function () {
                    try {
                        $pdfPath = Storage::disk('public')->path($this->record->file);

                        if (! file_exists($pdfPath)) {
                            Notification::make()->title('PDF file not found')->danger()->send();

                            return;
                        }

                        $imagick = new \Imagick();
                        $imagick->setResolution(200, 200);
                        $imagick->readImage($pdfPath.'[0]');
                        $imagick->setImageFormat('jpg');
                        $imagick->setImageCompressionQuality(85);

                        $filename = 'annual-reports/images/'.pathinfo($this->record->file, PATHINFO_FILENAME).'.jpg';
                        Storage::disk('public')->put($filename, $imagick->getImageBlob());
                        $imagick->clear();
                        $imagick->destroy();

                        $this->record->update(['image' => $filename]);
                        $this->fillForm();

                        Notification::make()->title('Cover image generated successfully')->success()->send();
                    } catch (\Exception $e) {
                        Notification::make()->title('Failed to generate cover')->body($e->getMessage())->danger()->send();
                    }
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
