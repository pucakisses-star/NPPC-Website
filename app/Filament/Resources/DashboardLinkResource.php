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
                Forms\Components\Fieldset::make('Map location (optional)')
                    ->schema([
                        Forms\Components\TextInput::make('location_label')
                            ->label('Location label')
                            ->maxLength(255)
                            ->helperText('Place name shown in the marker popup, e.g. Newark, NJ.'),
                        Forms\Components\TextInput::make('lat')
                            ->label('Latitude')
                            ->numeric()
                            ->minValue(-90)->maxValue(90)
                            ->helperText('Set both latitude and longitude to plot this as an event marker on the map.'),
                        Forms\Components\TextInput::make('lng')
                            ->label('Longitude')
                            ->numeric()
                            ->minValue(-180)->maxValue(180),
                    ])->columns(3),
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
                Tables\Columns\TextColumn::make('location_label')
                    ->label('Location')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('on_map')
                    ->label('On map')
                    ->boolean()
                    ->state(fn ($record) => $record->lat !== null && $record->lng !== null)
                    ->toggleable(),
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
