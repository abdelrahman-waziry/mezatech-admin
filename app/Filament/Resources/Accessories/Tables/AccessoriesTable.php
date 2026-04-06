<?php

namespace App\Filament\Resources\Accessories\Tables;

use App\Models\Accessory;
use App\Models\AccessoryCategory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AccessoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('brand')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('item_code')
                    ->label('Item Code')
                    ->sortable()
                    ->searchable(),
                TextInputColumn::make('name')
                    ->label('Product')
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
                TextColumn::make('notes')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
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
