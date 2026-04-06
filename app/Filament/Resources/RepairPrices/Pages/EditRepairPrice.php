<?php

namespace App\Filament\Resources\RepairPrices\Pages;

use App\Filament\Resources\RepairPrices\RepairPriceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRepairPrice extends EditRecord
{
    protected static string $resource = RepairPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
