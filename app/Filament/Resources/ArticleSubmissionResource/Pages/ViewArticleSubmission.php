<?php

namespace App\Filament\Resources\ArticleSubmissionResource\Pages;

use App\Filament\Resources\ArticleSubmissionResource;
use Filament\Resources\Pages\ViewRecord;

class ViewArticleSubmission extends ViewRecord {
    protected static string $resource = ArticleSubmissionResource::class;

    protected function mutateFormDataBeforeFill(array $data): array {
        // Auto-mark as read when viewed
        if ($this->record->status === 'new') {
            $this->record->update(['status' => 'read']);
        }

        return $data;
    }
}
