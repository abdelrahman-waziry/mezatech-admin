<?php

namespace App\Filament\Resources\VariantFeatures\Tables;

use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VariantFeaturesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product_name')
                    ->label('Product')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('variant_name')
                    ->label('Variant')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('feature_name')
                    ->label('Feature')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('feature_value')
                    ->label('Value')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label('Product')
                    ->options(fn () => Product::query()->orderBy('name')->pluck('name', 'id')->toArray())
                    ->placeholder('Select a product'),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
