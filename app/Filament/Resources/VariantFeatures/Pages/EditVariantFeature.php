<?php

namespace App\Filament\Resources\VariantFeatures\Pages;

use App\Filament\Resources\VariantFeatures\VariantFeatureResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVariantFeature extends EditRecord
{
    protected static string $resource = VariantFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
