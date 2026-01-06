<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ConditionDistributionChart extends ChartWidget
{
    protected ?string $heading = 'Device Condition Distribution';
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $data = AnalyticsEvent::query()
            ->whereNotNull('condition')
            ->select('condition', DB::raw('COUNT(*) as count'))
            ->groupBy('condition')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Conditions',
                    'data' => $data->pluck('count'),
                    'backgroundColor' => ['#4BC0C0', '#FFCE56', '#FF6384', '#9966FF'],
                ],
            ],
            'labels' => $data->pluck('condition'),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
