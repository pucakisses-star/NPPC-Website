<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerResource\Pages;
use App\Models\Partner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PartnerResource extends Resource {
    protected static ?string $model = Partner::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Content';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('logo')
                    ->image()
                    ->disk('public')
                    ->directory('partners'),
                Forms\Components\TextInput::make('url')
                    ->label('Website URL')
                    ->url()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->helperText('Short description shown on the partners page'),
                Forms\Components\TextInput::make('category')
                    ->maxLength(100)
                    ->placeholder('e.g. Legal, Advocacy, Research')
                    ->helperText('Optional category for grouping partners'),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('published')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->disk('public')
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('published')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
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
            'index'  => Pages\ListPartners::route('/'),
            'create' => Pages\CreatePartner::route('/create'),
            'edit'   => Pages\EditPartner::route('/{record}/edit'),
        ];
    }
}
