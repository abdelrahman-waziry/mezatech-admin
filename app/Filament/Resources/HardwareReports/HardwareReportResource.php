<?php

namespace App\Filament\Resources\HardwareReports;

use App\Filament\Resources\HardwareReports\Pages\CreateHardwareReport;
use App\Filament\Resources\HardwareReports\Pages\EditHardwareReport;
use App\Filament\Resources\HardwareReports\Pages\ListHardwareReports;
use App\Filament\Resources\HardwareReports\Schemas\HardwareReportForm;
use App\Filament\Resources\HardwareReports\Tables\HardwareReportsTable;
use App\Models\HardwareReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HardwareReportResource extends Resource
{
    protected static ?string $model = HardwareReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static \UnitEnum|string|null $navigationGroup = 'IMTI Assessments';

    public static function form(Schema $schema): Schema
    {
        return HardwareReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HardwareReportsTable::configure($table);
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
            'index' => ListHardwareReports::route('/'),
            'create' => CreateHardwareReport::route('/create'),
            'edit' => EditHardwareReport::route('/{record}/edit'),
        ];
    }
}
