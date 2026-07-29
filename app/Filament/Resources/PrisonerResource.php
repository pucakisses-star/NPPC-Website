<?php

namespace App\Filament\Resources;

use App\Filament\Forms\PartialDate;
use App\Filament\Resources\PrisonerResource\Pages;
use App\Filament\Resources\PrisonerResource\RelationManagers;
use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\Scopes\NotUnderReviewScope;
use App\Support\AccentInsensitiveSearch;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PrisonerResource extends Resource
{
    protected static ?string $model = Prisoner::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'Prisoner Database';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identity')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('first_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('middle_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('aka')
                            ->label('AKA')
                            ->maxLength(255),
                        Forms\Components\Select::make('gender')
                            ->options([
                                'Male' => 'Male',
                                'Female' => 'Female',
                                'Other' => 'Other',
                            ]),
                        Forms\Components\TextInput::make('race')
                            ->maxLength(255),
                        PartialDate::make('birthdate', 'Date of birth'),
                        PartialDate::make('death_date', 'Date of death'),
                        Forms\Components\Placeholder::make('age_display')
                            ->label('Age')
                            ->content(fn (Forms\Get $get, ?Prisoner $record): string => self::ageDisplay($get, $record))
                            ->helperText('Calculated from the date of birth above (and the date of death if set), and updated as you edit them.'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Photo & Description')
                    ->schema([
                        Forms\Components\FileUpload::make('photo')
                            ->image()
                            ->disk('public')
                            ->directory('prisoners'),
                        Forms\Components\Textarea::make('description')
                            ->label('Short Description')
                            ->helperText('Brief summary shown in listings and search results.')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Page Content')
                    ->description('Rich text content displayed on the prisoner\'s public page. Use this for detailed case information, embedded PDFs, images, and other media.')
                    ->schema([
                        TiptapEditor::make('body')
                            ->label('')
                            ->profile('default')
                            ->disk('public')
                            ->directory('prisoners/content')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Political Information')
                    ->schema([
                        Forms\Components\TagsInput::make('ideologies'),
                        Forms\Components\TagsInput::make('affiliation'),
                        Forms\Components\TextInput::make('era')
                            ->maxLength(255),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('in_custody'),
                        Forms\Components\Toggle::make('released'),
                        Forms\Components\Toggle::make('in_exile'),
                        Forms\Components\Toggle::make('currently_in_exile'),
                        Forms\Components\Toggle::make('imprisoned_or_exiled'),
                        Forms\Components\Toggle::make('awaiting_trial'),
                        Forms\Components\Toggle::make('under_review')
                            ->label('Under review (hide from public site)')
                            ->helperText('When on, this prisoner is hidden from the public database, search, map, and calendar. Still visible in the admin.'),
                        Forms\Components\Toggle::make('minor_case')
                            ->label('Minor case')
                            ->helperText('Flag brief or minor detentions (short holds, dismissed charges, pretrial-only) so they can be filtered.'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Incarceration Details')
                    ->schema([
                        Forms\Components\Placeholder::make('years_in_prison_display')
                            ->label('Years in Prison')
                            ->content(fn ($record) => $record
                                ? (count($record->years_in_prison) ? implode(', ', $record->years_in_prison) : '—')
                                : '—')
                            ->helperText('Auto-calculated from each case\'s incarceration and release dates.'),
                        Forms\Components\TextInput::make('inmate_number')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Location')
                    ->schema([
                        Forms\Components\TextInput::make('state')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('address'),
                        Forms\Components\TextInput::make('lat')
                            ->label('Latitude')
                            ->numeric(),
                        Forms\Components\TextInput::make('lng')
                            ->label('Longitude')
                            ->numeric(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Social & Web')
                    ->schema([
                        Forms\Components\TextInput::make('website')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('twitter')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('facebook')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('instagram')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
            ]);
    }

    /**
     * The age shown beside the life dates, computed from what is CURRENTLY in
     * the form rather than from the stored `age` column.
     *
     * The old field was a disabled TextInput bound to that column, which made
     * it a mirror of a number written the last time the record was saved: type
     * a birthdate of October 1992 into a record whose stored age was 41 and the
     * box went on saying 41 until you saved and reloaded. The label promised it
     * was auto-calculated, so the stale value read as a calculation error.
     *
     * A stored age with no birthdate behind it is NOT stale and is still shown:
     * plenty of records get an age from a source that never gave a birth date
     * (a BOP inmate lookup, say), and that number is the only age information
     * there is. It is labelled as recorded so the two cases cannot be confused.
     */
    private static function ageDisplay(Forms\Get $get, ?Prisoner $record): string
    {
        $birth = self::dateFromPartialState($get, 'birthdate');

        if (! $birth) {
            $stored = $record?->getAttributes()['age'] ?? null;

            return $stored === null || $stored === ''
                ? '—'
                : $stored.'  (as recorded — no date of birth to calculate from)';
        }

        $death = self::dateFromPartialState($get, 'death_date');
        $years = (int) $birth->diffInYears($death ?? Carbon::now(), false);

        if ($years < 0) {
            return 'the date of death precedes the date of birth — one of them is wrong';
        }

        return $years > 120 ? '—' : (string) $years;
    }

    /** Read one PartialDate field out of the live form state. */
    private static function dateFromPartialState(Forms\Get $get, string $field): ?Carbon
    {
        $precision = $get("{$field}__precision") ?: 'day';

        if (in_array($precision, ['year', 'circa'], true)) {
            $year = $get("{$field}__year");
            $raw = $year ? sprintf('%04d-01-01', (int) $year) : null;
        } elseif ($precision === 'month') {
            $month = $get("{$field}__month");
            $raw = $month ? $month.'-01' : null;
        } else {
            $raw = $get("{$field}__date") ?: null;
        }

        if (! $raw) {
            return null;
        }

        try {
            return Carbon::parse($raw);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Sort #')
                    ->sortable()
                    ->alignCenter()
                    ->color('gray'),
                Tables\Columns\ImageColumn::make('photo')
                    ->circular()
                    ->disk('public')
                    ->size(50),
                Tables\Columns\TextColumn::make('name')
                    ->description(fn (Prisoner $record): ?string => $record->aka ? "AKA: {$record->aka}" : null)
                    // Accent-insensitive: "Maria Cueto" finds "María Cueto".
                    ->searchable(
                        ['name', 'first_name', 'last_name', 'aka'],
                        query: fn (Builder $query, string $search): Builder => AccentInsensitiveSearch::allWords(
                            $query,
                            ['name', 'first_name', 'middle_name', 'last_name', 'aka'],
                            $search,
                        ),
                    )
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\IconColumn::make('under_review')
                    ->label('Review')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye-slash')
                    ->falseIcon('')
                    ->trueColor('warning')
                    ->tooltip(fn ($state) => $state ? 'Under review — hidden from public site' : null)
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('inmate_number')
                    ->label('ID #')
                    ->prefix('#')
                    ->searchable(),
                Tables\Columns\TextColumn::make('age')
                    ->suffix(fn (Prisoner $record): string => $record->death_date ? ' (Deceased)' : '')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gender')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Male' => 'info',
                        'Female' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('race')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('era')
                    ->sortable(),
                Tables\Columns\TextColumn::make('state')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->state(function (Prisoner $record): string {
                        $statuses = [];
                        if ($record->in_custody) {
                            $statuses[] = 'In Custody';
                        }
                        if ($record->in_exile || $record->currently_in_exile) {
                            $statuses[] = 'In Exile';
                        }
                        if ($record->released) {
                            $statuses[] = 'Released';
                        }
                        if ($record->awaiting_trial) {
                            $statuses[] = 'Awaiting Trial';
                        }

                        return implode(', ', $statuses) ?: '-';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'Custody') => 'danger',
                        str_contains($state, 'Exile') => 'warning',
                        str_contains($state, 'Released') => 'success',
                        str_contains($state, 'Awaiting') => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('years_in_prison')
                    ->label('Years in Prison')
                    ->alignCenter()
                    ->wrap()
                    ->getStateUsing(function ($record) {
                        $years = $record->years_in_prison;
                        if (! $years) {
                            return '—';
                        }
                        if (count($years) === 1) {
                            return (string) $years[0];
                        }
                        // Compress consecutive runs into ranges (2002–2008)
                        $ranges = [];
                        $start = $prev = $years[0];
                        foreach (array_slice($years, 1) as $y) {
                            if ($y === $prev + 1) {
                                $prev = $y;

                                continue;
                            }
                            $ranges[] = $start === $prev ? (string) $start : "{$start}–{$prev}";
                            $start = $prev = $y;
                        }
                        $ranges[] = $start === $prev ? (string) $start : "{$start}–{$prev}";

                        return implode(', ', $ranges);
                    }),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->reorderRecordsTriggerAction(
                fn (Tables\Actions\Action $action, bool $isReordering) => $action
                    ->button()
                    ->color($isReordering ? 'success' : 'gray')
                    ->label($isReordering ? 'Done reordering' : 'Reorder list'),
            )
            ->paginatedWhileReordering()
            ->paginationPageOptions([25, 50, 100, 250, 'all'])
            ->defaultPaginationPageOption(50)
            ->filters([
                Tables\Filters\TernaryFilter::make('under_review')
                    ->label('Under review')
                    ->placeholder('All prisoners')
                    ->trueLabel('Under review only')
                    ->falseLabel('Public only')
                    ->default(false),
                // Status filter group matching Airtable's button filters
                Tables\Filters\Filter::make('imprisoned_or_exiled')
                    ->label('In Custody or Exiled')
                    ->query(fn (Builder $query): Builder => $query->where(function ($q) {
                        // "Currently in custody" or "currently in exile" only -
                        // do not include in_exile=true (which means "was ever
                        // in exile" - a historical fact that includes deceased
                        // formerly-exiled prisoners like Bill Haywood).
                        $q->where('in_custody', true)->orWhere('currently_in_exile', true);
                    })),
                Tables\Filters\TernaryFilter::make('in_custody')
                    ->label('In Custody'),
                Tables\Filters\TernaryFilter::make('in_exile')
                    ->label('In Exile'),
                Tables\Filters\TernaryFilter::make('released')
                    ->label('Released'),
                Tables\Filters\TernaryFilter::make('awaiting_trial')
                    ->label('Awaiting Trial'),
                Tables\Filters\TernaryFilter::make('minor_case')
                    ->label('Minor Case'),

                // Dropdown filters matching Airtable's multi-select filters
                Tables\Filters\SelectFilter::make('gender')
                    ->options(fn (): array => Prisoner::query()
                        ->whereNotNull('gender')
                        ->distinct()
                        ->pluck('gender', 'gender')
                        ->toArray()),
                Tables\Filters\SelectFilter::make('race')
                    ->options(fn (): array => Prisoner::query()
                        ->whereNotNull('race')
                        ->distinct()
                        ->pluck('race', 'race')
                        ->toArray()),
                Tables\Filters\SelectFilter::make('era')
                    ->options(fn (): array => Prisoner::query()
                        ->whereNotNull('era')
                        ->distinct()
                        ->orderByDesc('era')
                        ->pluck('era', 'era')
                        ->toArray()),
                Tables\Filters\SelectFilter::make('state')
                    ->options(fn (): array => Prisoner::query()
                        ->whereNotNull('state')
                        ->distinct()
                        ->pluck('state', 'state')
                        ->toArray()),
                Tables\Filters\Filter::make('ideology')
                    ->form([
                        Forms\Components\TextInput::make('ideology')
                            ->label('Ideology (contains)'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['ideology'],
                            fn (Builder $query, $value): Builder => $query->whereJsonContains('ideologies', $value),
                        );
                    }),
                Tables\Filters\Filter::make('affiliation_filter')
                    ->form([
                        Forms\Components\TextInput::make('affiliation')
                            ->label('Affiliation (contains)'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['affiliation'],
                            fn (Builder $query, $value): Builder => $query->whereJsonContains('affiliation', $value),
                        );
                    }),
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('moveToPosition')
                    ->label('Move to position…')
                    ->icon('heroicon-o-arrows-up-down')
                    ->form([
                        Forms\Components\TextInput::make('position')
                            ->label('New position (Sort #)')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->helperText('The selected entries are inserted as one block starting at this position, keeping their current relative order. Everything else renumbers around them.'),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        // Selected block, preserving its current relative order.
                        $selected = $records->sortBy('sort_order')->pluck('id')->all();

                        // Full sequence (under-review included) minus the block,
                        // block spliced in so its first entry lands at the
                        // requested Sort #, then renumber 1..N.
                        $ids = Prisoner::withoutGlobalScopes()
                            ->whereNotIn('id', $selected)
                            ->orderBy('sort_order')
                            ->orderBy('name')
                            ->pluck('id')
                            ->all();
                        $at = max(0, min((int) $data['position'] - 1, count($ids)));
                        array_splice($ids, $at, 0, $selected);

                        $current = Prisoner::withoutGlobalScopes()->pluck('sort_order', 'id');
                        DB::transaction(function () use ($ids, $current) {
                            foreach ($ids as $index => $id) {
                                if ((int) ($current[$id] ?? -1) !== $index + 1) {
                                    Prisoner::withoutGlobalScopes()->whereKey($id)->update(['sort_order' => $index + 1]);
                                }
                            }
                        });

                        Cache::forget(PrisonerApiController::cacheKey());
                    })
                    ->deselectRecordsAfterCompletion()
                    ->successNotificationTitle('Entries moved'),
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\ImageEntry::make('photo')
                                ->circular()
                                ->disk('public')
                                ->size(120)
                                ->grow(false),
                            Infolists\Components\Group::make([
                                Infolists\Components\TextEntry::make('name')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->weight('bold'),
                                Infolists\Components\TextEntry::make('aka')
                                    ->label('AKA')
                                    ->visible(fn (Prisoner $record): bool => (bool) $record->aka),
                                Infolists\Components\TextEntry::make('inmate_number')
                                    ->label('Inmate #')
                                    ->prefix('#')
                                    ->visible(fn (Prisoner $record): bool => (bool) $record->inmate_number),
                            ]),
                        ]),
                    ]),

                Infolists\Components\Section::make('Personal Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('age'),
                        Infolists\Components\TextEntry::make('gender'),
                        Infolists\Components\TextEntry::make('race'),
                        Infolists\Components\TextEntry::make('birthdate')
                            ->date(),
                        Infolists\Components\TextEntry::make('death_date')
                            ->date()
                            ->visible(fn (Prisoner $record): bool => (bool) $record->death_date),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Description')
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->hiddenLabel()
                            ->prose(),
                    ])
                    ->visible(fn (Prisoner $record): bool => (bool) $record->description),

                Infolists\Components\Section::make('Political Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('ideologies')
                            ->badge(),
                        Infolists\Components\TextEntry::make('affiliation')
                            ->badge(),
                        Infolists\Components\TextEntry::make('era'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Status')
                    ->schema([
                        Infolists\Components\IconEntry::make('in_custody')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('released')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('in_exile')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('currently_in_exile')
                            ->boolean(),
                        Infolists\Components\IconEntry::make('awaiting_trial')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('years_in_prison')
                            ->label('Years in Prison')
                            ->getStateUsing(fn ($record) => count($record->years_in_prison)
                                ? implode(', ', $record->years_in_prison)
                                : '—'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Location')
                    ->schema([
                        Infolists\Components\TextEntry::make('state'),
                        Infolists\Components\TextEntry::make('address'),
                    ])
                    ->columns(2)
                    ->visible(fn (Prisoner $record): bool => (bool) $record->state || (bool) $record->address),

                Infolists\Components\Section::make('Social & Web')
                    ->schema([
                        Infolists\Components\TextEntry::make('website')
                            ->url()
                            ->openUrlInNewTab(),
                        Infolists\Components\TextEntry::make('twitter')
                            ->url(fn (Prisoner $record): ?string => $record->twitter ? "https://x.com/{$record->twitter}" : null)
                            ->openUrlInNewTab(),
                        Infolists\Components\TextEntry::make('facebook')
                            ->url()
                            ->openUrlInNewTab(),
                        Infolists\Components\TextEntry::make('instagram')
                            ->url(fn (Prisoner $record): ?string => $record->instagram ? "https://instagram.com/{$record->instagram}" : null)
                            ->openUrlInNewTab(),
                    ])
                    ->columns(2)
                    ->visible(fn (Prisoner $record): bool => (bool) $record->website || (bool) $record->twitter || (bool) $record->facebook || (bool) $record->instagram),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CasesRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Admin sees ALL prisoners — including those marked under_review.
        return parent::getEloquentQuery()
            ->withoutGlobalScope(NotUnderReviewScope::class);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrisoners::route('/'),
            'create' => Pages\CreatePrisoner::route('/create'),
            'edit' => Pages\EditPrisoner::route('/{record}/edit'),
        ];
    }
}
