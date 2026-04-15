<?php

namespace App\Filament\Resources\RepairSubcategories\Pages;

use App\Filament\Resources\RepairSubcategories\RepairSubcategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageRepairSubcategories extends ManageRecords
{
    protected static string $resource = RepairSubcategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
