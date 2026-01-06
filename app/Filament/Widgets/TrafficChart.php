<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsRequest;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class TrafficChart extends ChartWidget
{
    protected ?string $heading = 'Traffic Over Time';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = \Flowframe\Trend\Trend::model(AnalyticsRequest::class)
            ->between(
                start: now()->subDays(30),
                end: now(),
            )
            ->perDay()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Requests',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
