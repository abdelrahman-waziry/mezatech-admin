<?php

namespace App\Filament\Resources\RepairPrices\Pages;

use App\Filament\Actions\ImportFixtechRepairs;
use App\Filament\Resources\RepairPrices\RepairPriceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRepairPrices extends ListRecords
{
    protected static string $resource = RepairPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportFixtechRepairs::make(),
            CreateAction::make(),
        ];
    }
}
