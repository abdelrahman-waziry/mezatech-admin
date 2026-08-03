<?php

namespace App\Filament\Resources\HardwareReports\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class HardwareReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('diagnostic_device_id')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('timestamp'),
                TextInput::make('overall_status')
                    ->default(null),
                TextInput::make('battery_health')
                    ->default(null),
                TextInput::make('cycle_count')
                    ->numeric()
                    ->default(null),
                Textarea::make('summary')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('battery_data')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('display_data')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('components_data')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
