<?php

namespace App\Filament\Resources\EmailSubscriberResource\Pages;

use App\Filament\Resources\EmailSubscriberResource;
use App\Models\EmailSubscriber;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListEmailSubscribers extends ListRecords {
    protected static string $resource = EmailSubscriberResource::class;

    protected function getHeaderActions(): array {
        return [
            Actions\Action::make('exportActiveCsv')
                ->label('Export Active (CSV)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn () => $this->exportCsv('active')),
            Actions\Action::make('exportAllCsv')
                ->label('Export All (CSV)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn () => $this->exportCsv('all')),
        ];
    }

    /**
     * Stream a CSV download of subscriber rows.
     *
     * @param string $scope 'active' | 'unsubscribed' | 'all'
     */
    private function exportCsv(string $scope = 'active'): StreamedResponse {
        $filename = 'email-subscribers-'.$scope.'-'.now()->format('Y-m-d').'.csv';

        $query = EmailSubscriber::query()->orderBy('created_at');
        if ($scope === 'active' || $scope === 'unsubscribed') {
            $query->where('status', $scope);
        }

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['email', 'status', 'subscribed_at']);
            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $row) {
                    fputcsv($out, [
                        $row->email,
                        $row->status,
                        optional($row->created_at)->toIso8601String(),
                    ]);
                }
            });
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
