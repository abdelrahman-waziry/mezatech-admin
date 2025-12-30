<?php

namespace App\Filament\Resources\Conditions\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ConditionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Condition Name')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->maxLength(1000),

                TextInput::make('price_modifier')
                    ->label('Price Modifier')
                    ->numeric()
                    ->step(0.01)
                    ->default(1.0)
                    ->helperText('Multiplier applied to base repair price'),
            ]);
    }
}
