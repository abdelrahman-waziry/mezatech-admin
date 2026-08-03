<?php

namespace App\Filament\Resources\CosmeticReports;

use App\Filament\Resources\CosmeticReports\Pages\CreateCosmeticReport;
use App\Filament\Resources\CosmeticReports\Pages\EditCosmeticReport;
use App\Filament\Resources\CosmeticReports\Pages\ListCosmeticReports;
use App\Filament\Resources\CosmeticReports\Schemas\CosmeticReportForm;
use App\Filament\Resources\CosmeticReports\Tables\CosmeticReportsTable;
use App\Models\CosmeticReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CosmeticReportResource extends Resource
{
    protected static ?string $model = CosmeticReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static \UnitEnum|string|null $navigationGroup = 'IMTI Assessments';

    public static function form(Schema $schema): Schema
    {
        return CosmeticReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CosmeticReportsTable::configure($table);
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
            'index' => ListCosmeticReports::route('/'),
            'create' => CreateCosmeticReport::route('/create'),
            'edit' => EditCosmeticReport::route('/{record}/edit'),
        ];
    }
}
