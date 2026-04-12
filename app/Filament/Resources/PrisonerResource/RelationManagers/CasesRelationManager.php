<?php

namespace App\Filament\Resources\PrisonerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CasesRelationManager extends RelationManager {
    protected static string $relationship = 'cases';
    protected static ?string $recordTitleAttribute = 'charges';

    public function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\Select::make('institution_id')
                    ->relationship('institution', 'name')
                    ->nullable()
                    ->searchable()
                    ->preload(),
                Forms\Components\Textarea::make('charges')
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('arrest_date'),
                Forms\Components\TextInput::make('indicted')
                    ->maxLength(255),
                Forms\Components\TextInput::make('convicted')
                    ->maxLength(255),
                Forms\Components\TextInput::make('plead')
                    ->maxLength(255),
                Forms\Components\DatePicker::make('sentenced_date'),
                Forms\Components\DatePicker::make('incarceration_date'),
                Forms\Components\DatePicker::make('release_date'),
                Forms\Components\DatePicker::make('death_in_custody_date'),
                Forms\Components\DatePicker::make('in_exile_since'),
                Forms\Components\DatePicker::make('end_of_exile'),
                Forms\Components\TextInput::make('prosecutor')
                    ->maxLength(255),
                Forms\Components\TextInput::make('judge')
                    ->maxLength(255),
                Forms\Components\Textarea::make('sentence'),
                Forms\Components\TextInput::make('imprisoned_for_days')
                    ->numeric()
                    ->disabled()
                    ->helperText('Auto-calculated from incarceration and release dates'),
                Forms\Components\TextInput::make('in_exile_for_days')
                    ->numeric(),
            ]);
    }

    public function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('institution.name'),
                Tables\Columns\TextColumn::make('charges')
                    ->limit(40),
                Tables\Columns\TextColumn::make('arrest_date')
                    ->date(),
                Tables\Columns\TextColumn::make('release_date')
                    ->date(),
                Tables\Columns\TextColumn::make('sentence')
                    ->limit(30),
            ])
            ->filters([])
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
