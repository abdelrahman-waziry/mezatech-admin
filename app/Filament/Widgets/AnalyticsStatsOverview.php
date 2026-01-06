<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AnalyticsStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalRequests = AnalyticsRequest::count();
        $avgDuration = AnalyticsRequest::avg('duration_ms');
        $errorRate = AnalyticsRequest::where('status', '>=', 400)->count() / max($totalRequests, 1) * 100;

        return [
            Stat::make('Total Requests', number_format($totalRequests))
                ->description('All time')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Avg Response Time', number_format($avgDuration, 2) . 'ms')
                ->description('Average latency')
                ->color('info'),
            Stat::make('Error Rate', number_format($errorRate, 2) . '%')
                ->description('Requests with 4xx/5xx status')
                ->color($errorRate > 1 ? 'danger' : 'success'),
        ];
    }
}
