<?php

namespace App\Filament\Resources\Parts\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Part Name')
                    ->required()
                    ->maxLength(255),

                Select::make('product_id')
                    ->label('Product')
                    ->options(fn () => Product::all()->pluck('name', 'id')->toArray())
                    ->required()
                    ->searchable()
                    ->preload(),

                TextInput::make('price')
                    ->label('Price')
                    ->numeric()
                    ->required()
                    ->inputMode('decimal'),

                TextInput::make('type')
                    ->label('Type ID')
                    ->numeric()
                    ->inputMode('numeric'),

                Textarea::make('info')
                    ->label('Metadata (JSON)')
                    ->rows(3)
                    ->helperText('Optional JSON object describing extra pricing tiers'),
            ]);
    }
}
