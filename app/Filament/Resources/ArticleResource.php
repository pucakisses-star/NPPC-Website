<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Models\Article;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ArticleResource extends Resource {
    protected static ?string $model = Article::class;
    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $navigationGroup = 'Content';
    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('articles'),
                Forms\Components\TextInput::make('image_caption')
                    ->maxLength(500)
                    ->label('Image Caption')
                    ->placeholder('e.g. Photo by John Doe / Reuters')
                    ->helperText('Credit or caption displayed below the article image.'),
                \FilamentTiptapEditor\TiptapEditor::make('body')
                    ->required()
                    ->profile('default')
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('published_at'),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'title')
                    ->nullable()
                    ->preload()
                    ->searchable(),
                Forms\Components\Select::make('author_id')
                    ->relationship('author', 'name')
                    ->nullable()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('about'),
                        Forms\Components\FileUpload::make('avatar')
                            ->image()
                            ->disk('public')
                            ->directory('authors'),
                    ]),
                Forms\Components\SpatieTagsInput::make('tags'),
                Forms\Components\Repeater::make('citations_json')
                    ->label('Citations')
                    ->schema([
                        Forms\Components\TextInput::make('title'),
                        Forms\Components\MarkdownEditor::make('content'),
                    ])
                    ->columnSpanFull()
                    ->defaultItems(0)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->title),
                Tables\Columns\TextColumn::make('category.title')
                    ->sortable(),
                Tables\Columns\TextColumn::make('author.name'),
                Tables\Columns\TextColumn::make('published_at')
                    ->date()
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
            'index'  => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit'   => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
