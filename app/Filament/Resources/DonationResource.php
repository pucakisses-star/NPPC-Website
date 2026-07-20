<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DonationResource\Pages;
use App\Models\Donation;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DonationResource extends Resource {
    protected static ?string $model = Donation::class;
    protected static ?string $navigationIcon = 'heroicon-o-heart';
    protected static ?string $navigationGroup = 'Submissions';
    protected static ?string $navigationLabel = 'Donations';
    protected static ?int $navigationSort = 310;

    public static function getNavigationBadge(): ?string {
        $count = Donation::where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string {
        return 'success';
    }

    public static function table(Table $table): Table {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M j, Y g:ia')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->formatStateUsing(fn (Donation $record): string => '$'.number_format($record->amount / 100, 2).' '.strtoupper($record->currency))
                    ->sortable(),
                Tables\Columns\TextColumn::make('mode')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'subscription' ? 'Recurring' : 'One-time')
                    ->color(fn (string $state): string => $state === 'subscription' ? 'info' : 'gray'),
                Tables\Columns\TextColumn::make('donor_name')
                    ->label('Donor')
                    ->searchable()
                    ->default('—'),
                Tables\Columns\TextColumn::make('donor_email')
                    ->label('Email')
                    ->searchable()
                    ->default('—'),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid'    => 'success',
                        'unpaid'  => 'danger',
                        default   => 'gray',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('mode')
                    ->label('Type')
                    ->options([
                        'payment'      => 'One-time',
                        'subscription' => 'Recurring',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'paid'   => 'Paid',
                        'unpaid' => 'Unpaid',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Donation')
                    ->schema([
                        Infolists\Components\TextEntry::make('amount')
                            ->formatStateUsing(fn (Donation $record): string => '$'.number_format($record->amount / 100, 2).' '.strtoupper($record->currency)),
                        Infolists\Components\TextEntry::make('mode')
                            ->label('Type')
                            ->formatStateUsing(fn (string $state): string => $state === 'subscription' ? 'Recurring' : 'One-time'),
                        Infolists\Components\TextEntry::make('donor_name')->label('Donor'),
                        Infolists\Components\TextEntry::make('donor_email')->label('Email'),
                        Infolists\Components\TextEntry::make('payment_status')->badge(),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Date')
                            ->dateTime('F j, Y \a\t g:ia'),
                        Infolists\Components\TextEntry::make('stripe_session_id')
                            ->label('Stripe session')
                            ->fontFamily('mono')
                            ->copyable()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListDonations::route('/'),
            'view'  => Pages\ViewDonation::route('/{record}'),
        ];
    }
}
