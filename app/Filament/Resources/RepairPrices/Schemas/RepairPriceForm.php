<?php

namespace App\Filament\Resources\RepairPrices\Schemas;

use App\Models\RepairCategory;
use App\Models\RepairSubcategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RepairPriceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Classification')
                    ->columns(2)
                    ->schema([
                        Select::make('repair_category_id')
                            ->label('Category')
                            ->options(RepairCategory::pluck('name', 'id'))
                            ->reactive()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (Select $component, $record) {
                                if ($record && $record->repair_subcategory_id) {
                                    $component->state($record->subcategory->repair_category_id);
                                }
                            })
                            ->afterStateUpdated(fn ($set) => $set('repair_subcategory_id', null)),

                        Select::make('repair_subcategory_id')
                            ->label('Subcategory')
                            ->options(function ($get) {
                                $categoryId = $get('repair_category_id');
                                if (!$categoryId) {
                                    return [];
                                }
                                return RepairSubcategory::where('repair_category_id', $categoryId)->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable(),
                    ]),

                Section::make('Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('product_number')
                            ->nullable(),
                        TextInput::make('model')
                            ->required(),
                        TextInput::make('warranty')
                            ->nullable(),
                        TextInput::make('sla')
                            ->label('SLA')
                            ->nullable(),
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
                        Toggle::make('is_etisalat_offer')
                            ->label('Etisalat Offer')
                            ->default(false),
                    ]),
            ]);
    }
}
