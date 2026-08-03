<?php

namespace App\Filament\Resources\DiagnosticDevices\Pages;

use App\Filament\Resources\DiagnosticDevices\DiagnosticDeviceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDiagnosticDevices extends ListRecords
{
    protected static string $resource = DiagnosticDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
