<?php

namespace App\Filament\Resources\HardwareReports\Pages;

use App\Filament\Resources\HardwareReports\HardwareReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHardwareReports extends ListRecords
{
    protected static string $resource = HardwareReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
