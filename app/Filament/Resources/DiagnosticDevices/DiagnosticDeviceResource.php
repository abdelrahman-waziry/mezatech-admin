<?php

namespace App\Filament\Resources\DiagnosticDevices;

use App\Filament\Resources\DiagnosticDevices\Pages\CreateDiagnosticDevice;
use App\Filament\Resources\DiagnosticDevices\Pages\EditDiagnosticDevice;
use App\Filament\Resources\DiagnosticDevices\Pages\ListDiagnosticDevices;
use App\Filament\Resources\DiagnosticDevices\Schemas\DiagnosticDeviceForm;
use App\Filament\Resources\DiagnosticDevices\Tables\DiagnosticDevicesTable;
use App\Models\DiagnosticDevice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DiagnosticDeviceResource extends Resource
{
    protected static ?string $model = DiagnosticDevice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static \UnitEnum|string|null $navigationGroup = 'IMTI Assessments';

    public static function form(Schema $schema): Schema
    {
        return DiagnosticDeviceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DiagnosticDevicesTable::configure($table);
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
            'index' => ListDiagnosticDevices::route('/'),
            'create' => CreateDiagnosticDevice::route('/create'),
            'edit' => EditDiagnosticDevice::route('/{record}/edit'),
        ];
    }
}
