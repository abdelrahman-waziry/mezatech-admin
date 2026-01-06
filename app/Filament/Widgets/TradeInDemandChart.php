<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TradeInDemandChart extends ChartWidget
{
    protected ?string $heading = 'Trade-In Demand per Model';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = AnalyticsEvent::query()
            ->whereIn('event_name', ['tradein_started', 'quote_viewed'])
            ->select('model', DB::raw('COUNT(*) as count'))
            ->groupBy('model')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Demand',
                    'data' => $data->pluck('count'),
                    'backgroundColor' => '#36A2EB',
                ],
            ],
            'labels' => $data->pluck('model'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
