<?php

namespace App\Filament\Resources\TradeInRequests;

use App\Filament\Resources\TradeInRequests\Pages\ListTradeInRequests;
use App\Filament\Resources\TradeInRequests\Tables\TradeInRequestsTable;
use App\Filament\Resources\TradeInRequests\Schemas\TradeInRequestForm;
use App\Models\TradeInRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TradeInRequestResource extends Resource
{
    protected static ?string $model = TradeInRequest::class;

    protected static ?string $modelLabel = 'Request';
    protected static ?string $pluralModelLabel = 'Requests';
    protected static ?string $navigationLabel = 'Trade-in requests';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|UnitEnum|null $navigationGroup = 'Activity Logs';

    public static function form(Schema $schema): Schema
    {
        return TradeInRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TradeInRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTradeInRequests::route('/'),
            'edit' => Pages\EditTradeInRequest::route('/{record}/edit'),
        ];
    }
}

