<?php

namespace App\Filament\Resources\TradeInJourneys\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TradeInJourneysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('user_id')
                    ->label('User')
                    ->sortable(),
                TextColumn::make('device_name')
                    ->label('Device'),
                TextColumn::make('device_serial')
                    ->label('Serial'),
                TextColumn::make('status'),
                TextColumn::make('is_functioning')
                    ->label('Power')
                    ->formatStateUsing(fn ($state) => $state ? 'Running' : 'Dead'),
                TextColumn::make('condition_rating')
                    ->label('Condition')
                    ->sortable(),
                TextColumn::make('estimated_price')
                    ->label('Estimated Price')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('logged_at')
                    ->label('Logged At')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}

