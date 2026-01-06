<?php

namespace App\Filament\Resources\AnalyticsRequestResource\Pages;

use App\Filament\Resources\AnalyticsRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListAnalyticsRequests extends ListRecords
{
    protected static string $resource = AnalyticsRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
