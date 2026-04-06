<?php

namespace App\Filament\Resources\Accessories\Schemas;

use App\Models\AccessoryCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AccessoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Classification')
                    ->schema([
                        Select::make('accessory_category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable(),
                    ]),

                Section::make('Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('brand')
                            ->nullable(),
                        TextInput::make('item_code')
                            ->label('Item Code')
                            ->nullable(),
                        TextInput::make('name')
                            ->label('Product Name')
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->columnSpanFull()
                            ->nullable(),
                    ]),

                Section::make('Pricing')
                    ->columns(2)
                    ->schema([
                        TextInput::make('price')
                            ->numeric()
                            ->required()
                            ->prefix('EGP')
                            ->live()
                            ->afterStateUpdated(function ($state, $get, $set) {
                                $discount = (float) $get('discount') ?: 0;
                                $price = (float) $state ?: 0;
                                $set('price_after_discount', $price * (1 - ($discount / 100)));
                            }),
                        TextInput::make('discount')
                            ->numeric()
                            ->suffix('%')
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(function ($state, $get, $set) {
                                $price = (float) $get('price') ?: 0;
                                $discount = (float) $state ?: 0;
                                $set('price_after_discount', $price * (1 - ($discount / 100)));
                            }),
                        TextInput::make('price_after_discount')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->prefix('EGP')
                            ->hint('Auto-calculated'),
                    ]),
            ]);
    }
}
