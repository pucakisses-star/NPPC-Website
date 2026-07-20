<?php

namespace App\Filament\Resources\PrisonerLetterSubmissionResource\Pages;

use App\Filament\Resources\PrisonerLetterSubmissionResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPrisonerLetterSubmission extends ViewRecord {
    protected static string $resource = PrisonerLetterSubmissionResource::class;

    protected function mutateFormDataBeforeFill(array $data): array {
        // Auto-mark as read when viewed
        if ($this->record->status === 'new') {
            $this->record->update(['status' => 'read']);
        }

        return $data;
    }
}
