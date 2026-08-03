<?php

namespace App\Filament\Resources\CosmeticReports\Pages;

use App\Filament\Resources\CosmeticReports\CosmeticReportResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCosmeticReport extends EditRecord
{
    protected static string $resource = CosmeticReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
