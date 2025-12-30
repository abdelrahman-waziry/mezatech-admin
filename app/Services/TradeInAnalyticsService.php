<?php

namespace App\Services;

use App\Models\ApiRequest;
use App\Models\TradeInJourney;
use Illuminate\Support\Facades\DB;

class TradeInAnalyticsService
{
    public function requestCounts(string $period = 'hour'): array
    {
        $interval = match ($period) {
            'minute' => 'DATE_FORMAT(recorded_at, "%Y-%m-%d %H:%i")',
            'day' => 'DATE(recorded_at)',
            default => 'DATE_FORMAT(recorded_at, "%Y-%m-%d %H")',
        };

        return ApiRequest::query()
            ->select(DB::raw("$interval AS bucket"), DB::raw('count(*) as total'))
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->pluck('total', 'bucket')
            ->toArray();
    }

    public function avgResponseTimePerEndpoint(): array
    {
        return ApiRequest::query()
            ->select('endpoint', DB::raw('avg(response_time_ms) as avg'))
            ->groupBy('endpoint')
            ->orderByDesc('avg')
            ->take(10)
            ->get()
            ->map(fn ($row) => [
                'endpoint' => $row->endpoint,
                'avg' => round($row->avg, 2),
            ])
            ->toArray();
    }

    public function successFailureRates(): array
    {
        return ApiRequest::query()
            ->select(DB::raw('IF(status_code LIKE "2%", "success","failure") as outcome'), DB::raw('count(*) as total'))
            ->groupBy('outcome')
            ->pluck('total','outcome')
            ->toArray();
    }

    public function demandScorePerVariant(): array
    {
        return TradeInJourney::query()
            ->select('variant_id', DB::raw('count(*) as quoted'))
            ->whereNotNull('variant_id')
            ->groupBy('variant_id')
            ->orderByDesc('quoted')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function locationBreakdown(): array
    {
        return TradeInJourney::query()
            ->select('city', DB::raw('count(*) as total'))
            ->groupBy('city')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->pluck('total', 'city')
            ->toArray();
    }
}

