<?php

namespace App\Filament\Resources\CosmeticReports\Pages;

use App\Filament\Resources\CosmeticReports\CosmeticReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCosmeticReports extends ListRecords
{
    protected static string $resource = CosmeticReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
