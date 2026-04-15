<?php

namespace App\Filament\Resources\RepairCategories\Pages;

use App\Filament\Resources\RepairCategories\RepairCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageRepairCategories extends ManageRecords
{
    protected static string $resource = RepairCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
