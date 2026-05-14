<?php

namespace App\Filament\Resources\HistoryEraResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TopicsRelationManager extends RelationManager {
    protected static string $relationship = 'topics';
    protected static ?string $recordTitleAttribute = 'title';

    public function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('date_label')
                    ->required()
                    ->maxLength(100)
                    ->helperText('Date range shown above the title (e.g. "1798" or "1830s – 1860s")'),
                Forms\Components\Textarea::make('summary')
                    ->required()
                    ->rows(8)
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('history')
                    ->helperText('Optional image displayed in the visual panel'),
                Forms\Components\TextInput::make('bg_class')
                    ->default('vbg-1700')
                    ->maxLength(50),
                Forms\Components\TextInput::make('caption_era')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('caption_label')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn () => null),
                Tables\Columns\TextColumn::make('date_label')
                    ->label('Date'),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
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
}
