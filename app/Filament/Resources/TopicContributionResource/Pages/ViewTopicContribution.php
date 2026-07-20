<?php

namespace App\Filament\Resources\TopicContributionResource\Pages;

use App\Filament\Resources\TopicContributionResource;
use Filament\Resources\Pages\ViewRecord;

class ViewTopicContribution extends ViewRecord {
    protected static string $resource = TopicContributionResource::class;

    protected function mutateFormDataBeforeFill(array $data): array {
        // Auto-mark as read when viewed
        if ($this->record->status === 'new') {
            $this->record->update(['status' => 'read']);
        }

        return $data;
    }
}
