<?php

namespace App\Filament\Resources\DiagnosticDevices\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DiagnosticDeviceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
            ]);
    }
}
