<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource {
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Content';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('category')
                    ->label('Primary category')
                    ->placeholder('e.g. Apparel, Accessories, Books')
                    ->maxLength(100),
                Forms\Components\TagsInput::make('categories')
                    ->label('Also appears under')
                    ->suggestions(['Apparel', 'Accessories', 'Pins', 'Stickers', 'Magnets', 'Bundles', 'Books', 'Zines'])
                    ->helperText('Extra categories this product is filtered under, beyond the primary one (e.g. a bumper sticker can be Stickers and Accessories).'),
                Forms\Components\TextInput::make('purchase_url')
                    ->label('Purchase URL')
                    ->url()
                    ->placeholder('https://...')
                    ->helperText('External link where the product can be purchased'),
                Forms\Components\FileUpload::make('image')
                    ->label('Primary image')
                    ->image()
                    ->disk('public')
                    ->directory('products'),
                Forms\Components\FileUpload::make('gallery')
                    ->label('Additional images')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->disk('public')
                    ->directory('products')
                    ->helperText('Extra photos shown as thumbnails on the product page (drag to reorder).'),
                Forms\Components\Textarea::make('description')
                    ->rows(3),
                Forms\Components\Toggle::make('featured')
                    ->helperText('Featured products appear in the hero section'),
                Forms\Components\Toggle::make('published')
                    ->default(true),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge(),
                Tables\Columns\IconColumn::make('featured')
                    ->boolean(),
                Tables\Columns\IconColumn::make('published')
                    ->boolean(),
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
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
