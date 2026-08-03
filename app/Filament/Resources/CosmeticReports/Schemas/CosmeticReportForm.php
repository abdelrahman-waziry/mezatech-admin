<?php

namespace App\Filament\Resources\CosmeticReports\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CosmeticReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('diagnostic_device_id')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('timestamp'),
                TextInput::make('grade')
                    ->default(null),
                TextInput::make('overall_score')
                    ->numeric()
                    ->default(null),
                TextInput::make('total_defects')
                    ->numeric()
                    ->default(null),
                TextInput::make('color')
                    ->default(null),
                TextInput::make('label')
                    ->default(null),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('defect_summary')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('image_scores')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('images')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
