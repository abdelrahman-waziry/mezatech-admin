<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnalyticsEventResource\Pages;
use App\Models\AnalyticsEvent;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;


class AnalyticsEventResource extends Resource
{
    protected static ?string $model = AnalyticsEvent::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';
    protected static string | \UnitEnum | null $navigationGroup = 'Analytics';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
               // Read-only
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
                Tables\Columns\TextColumn::make('event_name')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->searchable()
                    ->toggleable(),
                 Tables\Columns\TextColumn::make('brand')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('model')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quoted_price')
                    ->money(currency: 'EGP') // Assuming SAR or similar, adjustable
                    ->sortable()
                    ->toggleable(),
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
            'index' => Pages\ListAnalyticsEvents::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
