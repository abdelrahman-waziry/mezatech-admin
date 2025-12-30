<?php

namespace App\Filament\Resources\TradeInJourneys\Pages;

use App\Models\TradeInJourney;
use App\Services\TradeInAnalyticsService;
use BackedEnum;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

use App\Filament\Resources\TradeInJourneys\TradeInJourneyResource;

class TradeInJourneyAnalytics extends Page
{
    protected static string $resource = TradeInJourneyResource::class;
    protected string $view = 'filament.trade-in-journeys.analytics';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected ?string $heading = "Trade-In Analytics";

    protected ?TradeInAnalyticsService $analyticsService = null;

    public function getViewData(): array
    {
        return [
            'statusBreakdown' => $this->getStatusBreakdown(),
            'priceByCondition' => $this->getPriceByCondition(),
            'requestCounts' => $this->getAnalyticsService()->requestCounts('hour'),
            'avgResponseByEndpoint' => $this->getAnalyticsService()->avgResponseTimePerEndpoint(),
            'successFailure' => $this->getAnalyticsService()->successFailureRates(),
        ];
    }

    protected function getStatusBreakdown(): array
    {
        return TradeInJourney::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status')
            ->toArray();
    }

    protected function getPriceByCondition(): array
    {
        return TradeInJourney::query()
            ->select('condition_rating', DB::raw('avg(estimated_price) as avg_price'))
            ->groupBy('condition_rating')
            ->orderBy('condition_rating')
            ->get()
            ->map(fn ($row) => [
                'rating' => $row->condition_rating,
                'avg_price' => (float) $row->avg_price,
            ])
            ->toArray();
    }

    protected function getAnalyticsService(): TradeInAnalyticsService
    {
        return $this->analyticsService ??= new TradeInAnalyticsService();
    }
}

