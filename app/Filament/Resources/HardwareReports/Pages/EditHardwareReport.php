<?php

namespace App\Filament\Resources\HardwareReports\Pages;

use App\Filament\Resources\HardwareReports\HardwareReportResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHardwareReport extends EditRecord
{
    protected static string $resource = HardwareReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
