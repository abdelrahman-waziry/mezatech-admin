<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnalyticsRequestResource\Pages;
use App\Models\AnalyticsRequest;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;


class AnalyticsRequestResource extends Resource
{
    protected static ?string $model = AnalyticsRequest::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-server';
    protected static string | \UnitEnum | null $navigationGroup = 'Analytics';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Read-only resource
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Timestamp'),
                Tables\Columns\TextColumn::make('method')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('endpoint')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 500 => 'danger',
                        $state >= 400 => 'warning',
                        $state >= 200 => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_ms')
                    ->numeric()
                    ->sortable()
                    ->label('Duration (ms)'),
                Tables\Columns\TextColumn::make('app_source')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('device_os')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('request_id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from'),
                        \Filament\Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                // ViewAction removed due to compatibility issue
            ])
            ->bulkActions([
                // No delete
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnalyticsRequests::route('/'),
        ];
    }
    
    public static function canCreate(): bool
    {
        return false;
    }
}
