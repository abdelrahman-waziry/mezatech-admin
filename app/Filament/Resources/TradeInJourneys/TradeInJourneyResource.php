<?php

namespace App\Filament\Resources\TradeInJourneys;

use App\Filament\Resources\TradeInJourneys\Pages\ListTradeInJourneys;
use App\Filament\Resources\TradeInJourneys\Tables\TradeInJourneysTable;
use App\Filament\Resources\TradeInJourneys\Schemas\TradeInJourneyForm;
use App\Models\TradeInJourney;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TradeInJourneyResource extends Resource
{
    protected static ?string $model = TradeInJourney::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|UnitEnum|null $navigationGroup = 'Activity Logs';

    public static function form(Schema $schema): Schema
    {
        return TradeInJourneyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TradeInJourneysTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTradeInJourneys::route('/'),
            'analytics' => Pages\TradeInJourneyAnalytics::route('/analytics'),
        ];
    }
}

