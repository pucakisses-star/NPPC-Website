<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuizResultResource\Pages;
use App\Models\QuizResult;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuizResultResource extends Resource {
    protected static ?string $model = QuizResult::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Submissions';
    protected static ?string $navigationLabel = 'Quiz Results';
    protected static ?string $modelLabel = 'Quiz Result';
    protected static ?int $navigationSort = 320;

    public static function getNavigationBadge(): ?string {
        $count = QuizResult::where('created_at', '>=', now()->subDays(7))->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string {
        return 'info';
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Taken')
                    ->dateTime('M j, Y g:ia')
                    ->sortable(),
                Tables\Columns\TextColumn::make('profile')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'The Dissent Defender'     => 'info',
                        'The Prisoner’s Ally'      => 'danger',
                        'The Due Process Guardian' => 'warning',
                        'The Resister'             => 'success',
                        default                    => 'gray',
                    }),
                Tables\Columns\TextColumn::make('engagement_tier')
                    ->label('Engagement')
                    ->description(fn (QuizResult $record): string => $record->engagement_score.' / 30'),
                Tables\Columns\TextColumn::make('knowledge_pct')
                    ->label('Knowledge')
                    ->formatStateUsing(fn (QuizResult $record): string => $record->knowledge_pct.'% ('.$record->knowledge_correct.'/'.$record->knowledge_total.')')
                    ->sortable(),
                Tables\Columns\TextColumn::make('perception_avg_error')
                    ->label('Guess error')
                    ->formatStateUsing(fn (?int $state): string => $state === null ? '—' : "±{$state} pts")
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('profile')
                    ->options([
                        'The Dissent Defender'     => 'The Dissent Defender',
                        'The Prisoner’s Ally'      => 'The Prisoner’s Ally',
                        'The Due Process Guardian' => 'The Due Process Guardian',
                        'The Resister'             => 'The Resister',
                    ]),
                Tables\Filters\SelectFilter::make('engagement_tier')
                    ->label('Engagement tier')
                    ->options([
                        'Witness'   => 'Witness',
                        'Supporter' => 'Supporter',
                        'Advocate'  => 'Advocate',
                        'Organizer' => 'Organizer',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Result')
                    ->schema([
                        Infolists\Components\TextEntry::make('profile')->badge(),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Taken')
                            ->dateTime('F j, Y \a\t g:ia'),
                        Infolists\Components\TextEntry::make('engagement_tier')
                            ->label('Engagement')
                            ->formatStateUsing(fn (QuizResult $record): string => $record->engagement_tier.' ('.$record->engagement_score.' / 30)'),
                        Infolists\Components\TextEntry::make('knowledge_tier')
                            ->label('Knowledge')
                            ->formatStateUsing(fn (QuizResult $record): string => $record->knowledge_tier.' — '.$record->knowledge_pct.'% ('.$record->knowledge_correct.' of '.$record->knowledge_total.')'),
                        Infolists\Components\TextEntry::make('perception_avg_error')
                            ->label('The Numbers — average guess error')
                            ->formatStateUsing(fn (?int $state): string => $state === null ? 'Skipped' : "Within {$state} points of the real figures"),
                    ])
                    ->columns(2),
                Infolists\Components\Section::make('Values dimensions')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('values_scores')
                            ->label('Score per dimension (0–100)')
                            ->keyLabel('Dimension')
                            ->valueLabel('Score'),
                    ]),
                Infolists\Components\Section::make('Raw answers')
                    ->collapsed()
                    ->schema([
                        Infolists\Components\TextEntry::make('answers')
                            ->label('Answer indexes per part')
                            ->formatStateUsing(fn (QuizResult $record): string => json_encode($record->answers, JSON_PRETTY_PRINT))
                            ->fontFamily('mono')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListQuizResults::route('/'),
            'view'  => Pages\ViewQuizResult::route('/{record}'),
        ];
    }
}
