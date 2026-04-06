<?php

namespace App\Filament\Resources\RepairPrices;

use App\Filament\Resources\RepairPrices\Pages\CreateRepairPrice;
use App\Filament\Resources\RepairPrices\Pages\EditRepairPrice;
use App\Filament\Resources\RepairPrices\Pages\ListRepairPrices;
use App\Filament\Resources\RepairPrices\Schemas\RepairPriceForm;
use App\Filament\Resources\RepairPrices\Tables\RepairPricesTable;
use App\Models\RepairPrice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RepairPriceResource extends Resource
{
    protected static ?string $model = RepairPrice::class;

    protected static string|\UnitEnum|null $navigationGroup = 'FixTech Pricing';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'Repair Prices';

    protected static ?string $recordTitleAttribute = 'model';

    public static function form(Schema $schema): Schema
    {
        return RepairPriceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RepairPricesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRepairPrices::route('/'),
            'create' => CreateRepairPrice::route('/create'),
            'edit' => EditRepairPrice::route('/{record}/edit'),
        ];
    }
}
