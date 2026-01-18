<?php

namespace App\Filament\Resources\VariantFeatures;

use App\Filament\Resources\VariantFeatures\Pages\ListVariantFeatures;
use App\Filament\Resources\VariantFeatures\Tables\VariantFeaturesTable;
use App\Models\VariantFeature;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class VariantFeatureResource extends Resource
{
    protected static ?string $model = VariantFeature::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static string|UnitEnum|null $navigationGroup = "Pricing Management";
    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return VariantFeaturesTable::configure($table);
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
            'index' => ListVariantFeatures::route('/'),
        ];
    }
}
