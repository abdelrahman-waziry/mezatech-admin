<?php

namespace App\Filament\Widgets;

use App\Models\TradeInRequest;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class MostTradedVariantsWidget extends ChartWidget
{
    protected ?string $heading = 'Most Traded-In Variants';

    protected ?string $description = 'Top 10 variants by trade-in request count';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'half';

    protected ?string $maxHeight = '350px';

    protected function getData(): array
    {
        $data = TradeInRequest::query()
            ->select('variant_id', DB::raw('COUNT(*) as count'))
            ->whereNotNull('variant_id')
            ->groupBy('variant_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        if ($data->isEmpty()) {
            return [
                'labels' => ['No data yet'],
                'datasets' => [
                    [
                        'label' => 'Trade-In Requests',
                        'data' => [0],
                        'backgroundColor' => ['#e5e7eb'],
                    ],
                ],
            ];
        }

        // Resolve variant names via API (cached)
        $labels = $data->map(function ($item) {
            return Cache::remember('variant_name_' . $item->variant_id, 3600, function () use ($item) {
                try {
                    $token = app(\App\Services\ApiTokenService::class)->getToken();
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                        'Accept' => 'application/json',
                    ])->timeout(2)->get('https://bestrepairegypt.com/v1/variants/' . $item->variant_id);

                    if ($response->successful()) {
                        return $response->json()['name'] ?? 'ID: ' . $item->variant_id;
                    }
                } catch (\Exception $e) {
                    // Fallback
                }
                return 'ID: ' . $item->variant_id;
            });
        });

        $colors = [
            '#6366f1', '#8b5cf6', '#a78bfa', '#c084fc',
            '#e879f9', '#f472b6', '#fb7185', '#f87171',
            '#fbbf24', '#34d399',
        ];

        return [
            'labels' => $labels->toArray(),
            'datasets' => [
                [
                    'label' => 'Trade-In Requests',
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => array_slice($colors, 0, $data->count()),
                    'borderColor' => '#ffffff',
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }
}
