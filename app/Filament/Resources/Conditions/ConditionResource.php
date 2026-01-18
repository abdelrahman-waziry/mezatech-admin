<?php

namespace App\Filament\Resources\Conditions;

use App\Filament\Resources\Conditions\Pages\CreateCondition;
use App\Filament\Resources\Conditions\Pages\EditCondition;
use App\Filament\Resources\Conditions\Pages\ListConditions;
use App\Filament\Resources\Conditions\Schemas\ConditionForm;
use App\Filament\Resources\Conditions\Tables\ConditionsTable;
use App\Models\Condition;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ConditionResource extends Resource
{
    protected static ?string $model = Condition::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static string|UnitEnum|null $navigationGroup = "Pricing Management";
    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return ConditionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConditionsTable::configure($table);
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
            'index' => ListConditions::route('/'),
            'create' => CreateCondition::route('/create'),
            'edit' => EditCondition::route('/{record}/edit'),
        ];
    }
}
