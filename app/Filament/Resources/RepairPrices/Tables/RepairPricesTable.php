<?php

namespace App\Filament\Resources\RepairPrices\Tables;

use App\Models\RepairCategory;
use App\Models\RepairSubcategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RepairPricesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subcategory.category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('subcategory.name')
                    ->label('Subcategory')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('product_number')
                    ->sortable()
                    ->searchable(),
                TextInputColumn::make('model')
                    ->sortable()
                    ->searchable()
                    ->rules(['required', 'max:255']),
                TextInputColumn::make('price')
                    ->sortable()
                    ->rules(['required', 'numeric', 'min:0']),
                TextColumn::make('discount')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('price_after_discount')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('warranty')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sla')
                    ->label('SLA')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('is_etisalat_offer')
                    ->label('Etisalat Offer')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('repair_category')
                    ->label('Category')
                    ->relationship('subcategory.category', 'name'),
                SelectFilter::make('repair_subcategory_id')
                    ->label('Subcategory')
                    ->relationship('subcategory', 'name'),
                TernaryFilter::make('is_etisalat_offer')
                    ->label('Etisalat Offer'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
