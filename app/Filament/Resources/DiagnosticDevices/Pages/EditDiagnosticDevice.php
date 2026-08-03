<?php

namespace App\Filament\Resources\DiagnosticDevices\Pages;

use App\Filament\Resources\DiagnosticDevices\DiagnosticDeviceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDiagnosticDevice extends EditRecord
{
    protected static string $resource = DiagnosticDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
