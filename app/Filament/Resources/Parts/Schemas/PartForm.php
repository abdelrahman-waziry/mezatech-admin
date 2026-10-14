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

                Select::make('type')
                    ->label('Part Type')
                    ->options([
                        0 => 'Functional device (0)',
                        1 => 'Repaired before (1)',
                        2 => 'Battery (2)',
                        3 => 'Screen (3)',
                        4 => 'Back frame (4)',
                        5 => 'Charging port (5)',
                        6 => 'Volume / silencer button (6)',
                        7 => 'Speaker 1 (7)',
                        8 => 'Speaker 2 (8)',
                        9 => 'Microphone 1 (9)',
                        10 => 'Microphone 2 (10)',
                        11 => 'Microphone 3 (11)',
                        12 => 'Back camera (12)',
                        13 => 'Front camera (13)',
                        14 => 'Face ID / fingerprint / home button (14)',
                        15 => 'Keyboard (15)',
                        16 => 'Camera flash (16)',
                        17 => 'Proximity sensor (17)',
                        18 => 'True Tone (18)',
                        19 => 'Vibration (19)',
                        20 => 'USB port (20)',
                        21 => 'AUX (21)',
                        22 => 'SIM / network (22)',
                        23 => 'Wi-Fi / Bluetooth (23)',
                        24 => 'Wireless charging (24)',
                        25 => 'Tax SIM 1 (25)',
                        26 => 'Tax SIM 2 (26)',
                        27 => 'Lighting sensor (27)',
                        28 => 'Box (28)',
                        29 => 'Top frame (29)',
                        30 => 'Bottom frame (30)',
                        31 => 'Left frame side (31)',
                        32 => 'Right frame side (32)',
                        33 => 'Charger (33)',
                        34 => 'Cable (34)',
                    ])
                    ->required()
                    ->searchable()
                    ->helperText('This numeric ID is sent to the Best Repair API.'),

                Textarea::make('info')
                    ->label('Metadata (JSON)')
                    ->rows(3)
                    ->helperText('Optional JSON object describing extra pricing tiers'),
            ]);
    }
}
