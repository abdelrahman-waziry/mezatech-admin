<?php

namespace App\Filament\Resources\TradeInRequests\Pages;

use App\Filament\Resources\TradeInRequests\TradeInRequestResource;
use Filament\Resources\Pages\ListRecords;

use Filament\Actions\Action;

class ListTradeInRequests extends ListRecords
{
    protected static string $resource = TradeInRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

