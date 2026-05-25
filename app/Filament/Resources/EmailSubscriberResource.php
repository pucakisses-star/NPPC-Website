<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailSubscriberResource\Pages;
use App\Models\EmailSubscriber;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmailSubscriberResource extends Resource {
    protected static ?string $model = EmailSubscriber::class;
    protected static ?string $navigationIcon = 'heroicon-o-envelope-open';
    protected static ?string $navigationGroup = 'Submissions';
    protected static ?string $navigationLabel = 'Email Subscribers';
    protected static ?string $modelLabel = 'Subscriber';
    protected static ?int $navigationSort = 302;

    public static function getNavigationBadge(): ?string {
        $count = EmailSubscriber::where('status', 'active')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string {
        return 'success';
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'       => 'success',
                        'unsubscribed' => 'gray',
                        default        => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Subscribed')
                    ->dateTime('M j, Y g:ia')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active'       => 'Active',
                        'unsubscribed' => 'Unsubscribed',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('unsubscribe')
                    ->label('Unsubscribe')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->visible(fn (EmailSubscriber $record): bool => $record->status === 'active')
                    ->requiresConfirmation()
                    ->action(fn (EmailSubscriber $record) => $record->update(['status' => 'unsubscribed'])),
                Tables\Actions\Action::make('resubscribe')
                    ->label('Reactivate')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (EmailSubscriber $record): bool => $record->status === 'unsubscribed')
                    ->action(fn (EmailSubscriber $record) => $record->update(['status' => 'active'])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('exportSelectedCsv')
                    ->label('Export Selected (CSV)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->deselectRecordsAfterCompletion()
                    ->action(function ($records) {
                        $filename = 'email-subscribers-selected-'.now()->format('Y-m-d-His').'.csv';
                        return response()->streamDownload(function () use ($records) {
                            $out = fopen('php://output', 'w');
                            fputcsv($out, ['email', 'status', 'subscribed_at']);
                            foreach ($records as $row) {
                                fputcsv($out, [
                                    $row->email,
                                    $row->status,
                                    optional($row->created_at)->toIso8601String(),
                                ]);
                            }
                            fclose($out);
                        }, $filename, [
                            'Content-Type' => 'text/csv',
                        ]);
                    }),
                Tables\Actions\BulkAction::make('unsubscribeSelected')
                    ->label('Mark Unsubscribed')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->action(fn ($records) => $records->each->update(['status' => 'unsubscribed'])),
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListEmailSubscribers::route('/'),
        ];
    }
}
