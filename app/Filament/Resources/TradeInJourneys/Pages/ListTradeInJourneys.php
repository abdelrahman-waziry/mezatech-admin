<?php

namespace App\Filament\Resources\TradeInJourneys\Pages;

use App\Filament\Resources\TradeInJourneys\TradeInJourneyResource;
use Filament\Resources\Pages\ListRecords;

use Filament\Actions\Action;

class ListTradeInJourneys extends ListRecords
{
    protected static string $resource = TradeInJourneyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('analytics')
                ->label('Analytics')
                ->icon('heroicon-o-chart-bar')
                ->url($this->getResource()::getUrl('analytics')),
        ];
    }
}

