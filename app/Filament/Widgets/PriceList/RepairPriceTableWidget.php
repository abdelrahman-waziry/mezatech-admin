<?php

namespace App\Filament\Widgets\PriceList;

use App\Models\RepairPrice;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class RepairPriceTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Repair Pricing Guide';

    public function table(Table $table): Table
    {
        return $table
            ->query(RepairPrice::query())
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('subcategory.category.name')
                    ->label('Category')
                    ->sortable(),
                TextColumn::make('model')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price_after_discount')
                    ->label('Final Price')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('warranty')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('is_etisalat_offer')
                    ->label('Etisalat')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('repair_category')
                    ->label('Category')
                    ->relationship('subcategory.category', 'name'),
            ]);
    }
}
