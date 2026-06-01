<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DashboardLinkResource\Pages;
use App\Models\DashboardLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DashboardLinkResource extends Resource {
    protected static ?string $model = DashboardLink::class;
    protected static ?string $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationGroup = 'Content';
    protected static ?string $navigationLabel = 'Dashboard Links';
    protected static ?string $modelLabel = 'dashboard link';
    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Headline shown in the dashboard ticker and newswire.'),
                Forms\Components\TextInput::make('url')
                    ->label('Link URL')
                    ->required()
                    ->url()
                    ->maxLength(2048)
                    ->helperText('Where the item links to. Opens in a new tab.'),
                Forms\Components\TextInput::make('source')
                    ->maxLength(120)
                    ->helperText('Optional label shown as a tag, e.g. BBC or Reuters.'),
                Forms\Components\DateTimePicker::make('published_at')
                    ->label('Published at')
                    ->helperText('Set this (now or a past time) for the item to appear. Items show newest first.'),
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->title),
                Tables\Columns\TextColumn::make('source')
                    ->sortable(),
                Tables\Columns\TextColumn::make('url')
                    ->limit(40),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array {
        return [
            'index'  => Pages\ListDashboardLinks::route('/'),
            'create' => Pages\CreateDashboardLink::route('/create'),
            'edit'   => Pages\EditDashboardLink::route('/{record}/edit'),
        ];
    }
}
