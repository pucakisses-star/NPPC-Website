<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class OrderResource extends Resource {
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Content';
    protected static ?string $recordTitleAttribute = 'reference';

    public static function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\Section::make('Order')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('reference')->disabled(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending'   => 'Pending',
                                'paid'      => 'Paid',
                                'fulfilled' => 'Fulfilled',
                                'cancelled' => 'Cancelled',
                                'failed'    => 'Failed',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('total')->disabled()->prefix('$'),
                        Forms\Components\TextInput::make('payment_status')->disabled(),
                    ]),
                Forms\Components\Section::make('Customer')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')->disabled(),
                        Forms\Components\TextInput::make('customer_email')->disabled(),
                        Forms\Components\Textarea::make('shipping_address')->disabled()->rows(4)->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Items')
                    ->schema([
                        Forms\Components\Placeholder::make('items_summary')
                            ->hiddenLabel()
                            ->content(function ($record) {
                                if (! $record) {
                                    return '—';
                                }
                                $rows = $record->items->map(function ($i) {
                                    $size = $i->size ? " ({$i->size})" : '';

                                    return e($i->quantity.' × '.$i->name.$size).' — <strong>$'.number_format($i->line_total, 2).'</strong>';
                                })->implode('<br>');

                                return new HtmlString($rows ?: '—');
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('customer_email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items'),
                Tables\Columns\TextColumn::make('total')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid'      => 'success',
                        'fulfilled' => 'success',
                        'pending'   => 'warning',
                        'cancelled' => 'gray',
                        'failed'    => 'danger',
                        default     => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'   => 'Pending',
                        'paid'      => 'Paid',
                        'fulfilled' => 'Fulfilled',
                        'cancelled' => 'Cancelled',
                        'failed'    => 'Failed',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit'  => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
