<?php

namespace App\Filament\Widgets\PriceList;

use App\Models\Accessory;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class AccessoryTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Accessory Pricing Guide';

    public function table(Table $table): Table
    {
        return $table
            ->query(Accessory::query())
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                TextColumn::make('brand')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price_after_discount')
                    ->label('Final Price')
                    ->money('EGP')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('accessory_category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),
                SelectFilter::make('brand')
                    ->options(function () {
                        return Accessory::distinct()
                            ->whereNotNull('brand')
                            ->pluck('brand', 'brand')
                            ->toArray();
                    }),
            ]);
    }
}
