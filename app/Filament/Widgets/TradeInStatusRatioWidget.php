<?php

namespace App\Filament\Widgets;

use App\Models\TradeInRequest;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TradeInStatusRatioWidget extends ChartWidget
{
    protected ?string $heading = 'Trade-In Acceptance vs Rejection';

    protected ?string $description = 'Ratio of accepted, rejected, and pending trade-in requests';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'half';

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $statuses = TradeInRequest::query()
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        $accepted = $statuses['accepted'] ?? 0;
        $rejected = $statuses['rejected'] ?? 0;
        $pending = $statuses['pending'] ?? 0;
        $confirmed = $statuses['confirmed'] ?? 0;
        $total = $accepted + $rejected + $pending + $confirmed;

        // Build description with percentages
        if ($total > 0) {
            $this->heading = 'Trade-In Acceptance vs Rejection';
        }

        return [
            'labels' => [
                "Accepted ({$accepted})",
                "Rejected ({$rejected})",
                "Pending ({$pending})",
                "Confirmed ({$confirmed})",
            ],
            'datasets' => [
                [
                    'label' => 'Trade-In Requests',
                    'data' => [$accepted, $rejected, $pending, $confirmed],
                    'backgroundColor' => [
                        '#22c55e', // green for accepted
                        '#ef4444', // red for rejected
                        '#f59e0b', // amber for pending
                        '#3b82f6', // blue for confirmed
                    ],
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                    'hoverOffset' => 8,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'padding' => 16,
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                    ],
                ],
            ],
            'cutout' => '60%',
        ];
    }
}
