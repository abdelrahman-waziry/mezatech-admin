<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Brand;
use App\Models\Tag;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Product Name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('sku')
                    ->label('SKU')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('e.g., IPH15-128GB')
                    ->helperText('Unique Stock Keeping Unit identifier'),

                Select::make('brand_id')
                    ->label('Brand')
                    ->options(fn () => Brand::all()->pluck('name', 'id')->toArray())
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('condition')
                    ->label('Condition')
                    ->options([
                        0 => 'Brand New',
                        1 => 'Like New',
                        2 => 'Good',
                        3 => 'Fair',
                    ])
                    ->required(),

                TextInput::make('minimum_buying_price')
                    ->label('Minimum Buying Price')
                    ->numeric()
                    ->required()
                    ->inputMode('decimal'),

                TextInput::make('waste_price')
                    ->label('Waste Price')
                    ->numeric()
                    ->required()
                    ->inputMode('decimal'),

                Textarea::make('features_summary')
                    ->label('Notes/Specifications')
                    ->rows(3)
                    ->maxLength(1000),

                Select::make('tags_summary')
                    ->label('Tags')
                    ->multiple()
                    ->options(fn () => Tag::query()->orderBy('name')->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->hint('Select tags to attach to this product')
                    ->afterStateHydrated(function (Select $component, $state) {
                        if (is_array($state)) {
                            return;
                        }

                        if (! filled($state)) {
                            $component->state([]);
                            return;
                        }

                        $names = array_filter(array_map('trim', explode(',', (string) $state)));
                        if (! count($names)) {
                            $component->state([]);
                            return;
                        }

                        $ids = Tag::whereIn('name', $names)
                            ->pluck('id')
                            ->map(fn ($id) => (int) $id)
                            ->all();

                        $component->state($ids);
                    })
                    ->dehydrateStateUsing(function ($state) {
                        if (! is_array($state)) {
                            return $state;
                        }

                        $trimmed = array_filter(array_map(fn ($value) => trim((string) $value), $state));

                        return implode(',', $trimmed);
                    }),
            ]);
    }
}
