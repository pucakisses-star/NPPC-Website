<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleSubmissionResource\Pages;
use App\Models\FormSubmission;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ArticleSubmissionResource extends Resource {
    protected static ?string $model = FormSubmission::class;
    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $navigationGroup = 'Submissions';
    protected static ?string $navigationLabel = 'Article Submissions';
    protected static ?string $modelLabel = 'Article Submission';
    protected static ?int $navigationSort = 332;

    public static function getEloquentQuery(): Builder {
        return parent::getEloquentQuery()->where('form_type', 'article-submission');
    }

    public static function getNavigationBadge(): ?string {
        $count = FormSubmission::where('form_type', 'article-submission')->where('status', 'new')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string {
        return 'danger';
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new'      => 'danger',
                        'read'     => 'info',
                        'archived' => 'gray',
                        default    => 'gray',
                    }),
                Tables\Columns\TextColumn::make('data.title')
                    ->label('Title')
                    ->limit(40),
                Tables\Columns\TextColumn::make('data.submitter_email')
                    ->label('Email'),
                Tables\Columns\TextColumn::make('data.website')
                    ->label('Source link')
                    ->limit(30),
                Tables\Columns\TextColumn::make('data.description')
                    ->label('Description')
                    ->limit(40),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M j, Y g:ia')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'new'      => 'New',
                        'read'     => 'Read',
                        'archived' => 'Archived',
                    ])
                    ->default('new'),
            ])
            ->actions([
                Tables\Actions\Action::make('markRead')
                    ->label('Mark Read')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn (FormSubmission $record): bool => $record->status === 'new')
                    ->action(fn (FormSubmission $record) => $record->update(['status' => 'read'])),
                Tables\Actions\Action::make('archive')
                    ->label('Archive')
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    ->visible(fn (FormSubmission $record): bool => $record->status !== 'archived')
                    ->requiresConfirmation()
                    ->action(fn (FormSubmission $record) => $record->update(['status' => 'archived'])),
                Tables\Actions\Action::make('restore')
                    ->label('Restore')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->visible(fn (FormSubmission $record): bool => $record->status === 'archived')
                    ->action(fn (FormSubmission $record) => $record->update(['status' => 'new'])),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('markAllRead')
                    ->label('Mark as Read')
                    ->icon('heroicon-o-eye')
                    ->action(fn ($records) => $records->each(fn ($r) => $r->update(['status' => 'read']))),
                Tables\Actions\BulkAction::make('archiveAll')
                    ->label('Archive')
                    ->icon('heroicon-o-archive-box')
                    ->requiresConfirmation()
                    ->action(fn ($records) => $records->each(fn ($r) => $r->update(['status' => 'archived']))),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Submission Details')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('data')
                            ->label('Submitted fields')
                            ->keyLabel('Field')
                            ->valueLabel('Value')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Submitted')
                            ->dateTime('F j, Y \a\t g:ia'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'new'      => 'danger',
                                'read'     => 'info',
                                'archived' => 'gray',
                                default    => 'gray',
                            }),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListArticleSubmissions::route('/'),
            'view'  => Pages\ViewArticleSubmission::route('/{record}'),
        ];
    }
}
