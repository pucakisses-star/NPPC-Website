<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArchiveRecordResource\Pages;
use App\Models\ArchiveRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ArchiveRecordResource extends Resource {
    protected static ?string $model = ArchiveRecord::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Content';
    protected static ?string $navigationLabel = 'Archive Records';
    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Section::make('Record')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('description')
                        ->rows(4)
                        ->columnSpanFull(),
                    Forms\Components\Select::make('record_type')
                        ->options([
                            'document' => 'Document',
                            'audio' => 'Audio',
                            'video' => 'Video',
                        ])
                        ->default('document')
                        ->required(),
                    Forms\Components\Select::make('source_format')
                        ->options([
                            'periodical' => 'Periodical',
                            'monograph' => 'Monograph',
                            'mp3' => 'mp3',
                            'flyer' => 'Flyer',
                            'article' => 'Article',
                            'book' => 'Book',
                            'pamphlet' => 'Pamphlet',
                            'video' => 'Video',
                            'other' => 'Other',
                        ])
                        ->searchable(),
                ]),

            Forms\Components\Section::make('Metadata')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('collection')
                        ->datalist(fn () => ArchiveRecord::query()
                            ->whereNotNull('collection')
                            ->distinct()
                            ->pluck('collection')
                            ->all())
                        ->helperText('Free-form collection name. Existing values shown as suggestions.'),
                    Forms\Components\TextInput::make('publisher'),
                    Forms\Components\TextInput::make('authors')
                        ->helperText('Comma-separated for multiple'),
                    Forms\Components\TextInput::make('volume'),
                    Forms\Components\TextInput::make('year')
                        ->numeric()
                        ->minValue(1800)
                        ->maxValue(2100),
                    Forms\Components\DatePicker::make('date')
                        ->label('Publication date')
                        ->native(false),
                    Forms\Components\TagsInput::make('subjects')
                        ->placeholder('Add subject and press Enter')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Files')
                ->columns(2)
                ->schema([
                    Forms\Components\FileUpload::make('file')
                        ->disk('public')
                        ->directory('archive-records/files')
                        ->acceptedFileTypes(['application/pdf', 'audio/mpeg', 'audio/mp3', 'audio/wav', 'video/mp4', 'video/webm', 'image/jpeg', 'image/png'])
                        ->maxSize(102400)
                        ->helperText('PDF, audio, or video (≤100 MB)'),
                    Forms\Components\FileUpload::make('thumbnail')
                        ->image()
                        ->disk('public')
                        ->directory('archive-records/thumbnails')
                        ->imageEditor()
                        ->helperText('Cover/preview image'),
                ]),

            Forms\Components\Section::make('Publishing')
                ->columns(3)
                ->schema([
                    Forms\Components\Toggle::make('published')->default(true),
                    Forms\Components\Toggle::make('is_digitized')
                        ->label('Digitized')
                        ->helperText('Leave on if the file is uploaded; turn off for citation-only records')
                        ->default(true),
                    Forms\Components\TextInput::make('sort_order')
                        ->numeric()
                        ->default(0),
                ]),
        ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->disk('public')
                    ->label(''),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(60)
                    ->wrap(),
                Tables\Columns\TextColumn::make('record_type')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('collection')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('year')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_digitized')
                    ->label('Digitized')
                    ->boolean(),
                Tables\Columns\IconColumn::make('published')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('record_type')
                    ->options([
                        'document' => 'Document',
                        'audio' => 'Audio',
                        'video' => 'Video',
                    ]),
                Tables\Filters\SelectFilter::make('collection')
                    ->options(fn () => ArchiveRecord::query()
                        ->whereNotNull('collection')
                        ->distinct()
                        ->orderBy('collection')
                        ->pluck('collection', 'collection')
                        ->all()),
                Tables\Filters\TernaryFilter::make('published'),
                Tables\Filters\TernaryFilter::make('is_digitized')->label('Digitized'),
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
            'index' => Pages\ListArchiveRecords::route('/'),
            'create' => Pages\CreateArchiveRecord::route('/create'),
            'edit' => Pages\EditArchiveRecord::route('/{record}/edit'),
        ];
    }
}
