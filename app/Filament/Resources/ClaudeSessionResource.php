<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClaudeSessionResource\Pages;
use App\Models\ClaudeSession;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClaudeSessionResource extends Resource {
    protected static ?string $model = ClaudeSession::class;
    protected static ?string $navigationIcon = 'heroicon-o-command-line';
    protected static ?string $navigationGroup = 'Developer Tools';
    protected static ?string $navigationLabel = 'Claude Code';
    protected static ?string $modelLabel = 'Claude Session';
    protected static ?int $navigationSort = 200;

    // Hidden from navigation and access for now
    protected static bool $shouldRegisterNavigation = false;

    public static function canViewAny(): bool {
        return false;
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('prompt')
                    ->limit(60)
                    ->searchable()
                    ->tooltip(fn (ClaudeSession $record): string => $record->prompt),
                Tables\Columns\TextColumn::make('status_label')
                    ->label('Status')
                    ->badge()
                    ->color(fn (ClaudeSession $record): string => $record->status_color),
                Tables\Columns\TextColumn::make('branch_name')
                    ->label('Branch')
                    ->fontFamily('mono')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('files_changed')
                    ->label('Files')
                    ->state(fn (ClaudeSession $record): string => $record->files_changed ? count($record->files_changed).' files' : '-'),
                Tables\Columns\TextColumn::make('created_by')
                    ->label('By'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M j, g:ia')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Open')
                    ->icon('heroicon-o-eye')
                    ->url(fn (ClaudeSession $record): string => static::getUrl('view', ['record' => $record])),
            ])
            ->poll('5s');
    }

    public static function getPages(): array {
        return [
            'index'  => Pages\ListClaudeSessions::route('/'),
            'create' => Pages\CreateClaudeSession::route('/create'),
            'view'   => Pages\ViewClaudeSession::route('/{record}'),
        ];
    }
}
