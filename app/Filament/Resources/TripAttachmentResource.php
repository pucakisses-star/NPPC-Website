<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TripAttachmentResource\Pages;
use App\Models\TripAttachment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TripAttachmentResource extends Resource {
    protected static ?string $model = TripAttachment::class;
    protected static ?string $navigationIcon = 'heroicon-o-paper-clip';
    protected static ?string $navigationGroup = 'Rachel London Trip';
    protected static ?string $navigationLabel = 'Attachments';
    protected static ?string $modelLabel = 'Attachment';
    protected static ?int $navigationSort = 300;

    public static function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\Select::make('day')
                    ->options([
                        1 => 'Day 1 - Monday, April 27th',
                        2 => 'Day 2 - Tuesday, April 28th',
                        3 => 'Day 3 - Wednesday, April 29th',
                        4 => 'Day 4 - Thursday, April 30th (Birthday!)',
                        5 => 'Day 5 - Friday, May 1st',
                        6 => 'Day 6 - Saturday, May 2nd',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('label')
                    ->placeholder('e.g. Moco Museum Tickets, Hotel Confirmation')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('file_path')
                    ->label('File')
                    ->disk('public')
                    ->directory('trip-attachments')
                    ->acceptedFileTypes(['image/*', 'application/pdf'])
                    ->required(),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('day')
                    ->sortable()
                    ->formatStateUsing(fn (int $state): string => "Day {$state}"),
                Tables\Columns\TextColumn::make('label')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('file_path')
                    ->disk('public')
                    ->label('Preview')
                    ->circular(false)
                    ->height(40),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M j, g:ia')
                    ->sortable(),
            ])
            ->defaultSort('day')
            ->filters([
                Tables\Filters\SelectFilter::make('day')
                    ->options([
                        1 => 'Day 1', 2 => 'Day 2', 3 => 'Day 3',
                        4 => 'Day 4', 5 => 'Day 5', 6 => 'Day 6',
                    ]),
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

    public static function getPages(): array {
        return [
            'index'  => Pages\ListTripAttachments::route('/'),
            'create' => Pages\CreateTripAttachment::route('/create'),
            'edit'   => Pages\EditTripAttachment::route('/{record}/edit'),
        ];
    }
}
