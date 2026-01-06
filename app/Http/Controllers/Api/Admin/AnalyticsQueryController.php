<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\AnalyticsRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsQueryController extends Controller
{
    public function performance(Request $request): JsonResponse
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $groupBy = $request->input('group_by', 'day'); // endpoint | day | hour

        $query = AnalyticsRequest::query();

        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        $selects = [
            DB::raw('COUNT(*) as request_count'),
            DB::raw('AVG(duration_ms) as avg_response_time'),
            DB::raw('SUM(case when status >= 200 and status < 300 then 1 else 0 end) / COUNT(*) * 100 as success_rate'),
            DB::raw('SUM(case when status >= 500 then 1 else 0 end) / COUNT(*) * 100 as failure_rate'),
        ];
        
        $groupByClause = [];

        if ($groupBy === 'endpoint') {
            $selects[] = 'endpoint';
            $groupByClause[] = 'endpoint';
        } elseif ($groupBy === 'hour') {
            $selects[] = DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00") as date');
            $groupByClause[] = DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00")');
        } else { // day
            $selects[] = DB::raw('DATE(created_at) as date');
            $groupByClause[] = DB::raw('DATE(created_at)');
        }

        $result = $query->select($selects)
            ->groupBy($groupByClause)
            ->get();

        return response()->json($result);
    }

    public function traffic(Request $request): JsonResponse
    {
        $interval = $request->input('interval', 'day'); // hour | day | month
        
        $groupByFormat = match ($interval) {
            'hour' => '%Y-%m-%d %H:00:00',
            'month' => '%Y-%m-01 00:00:00',
            default => '%Y-%m-%d 00:00:00', // day
        };

        $result = AnalyticsRequest::query()
            ->select([
                DB::raw("DATE_FORMAT(created_at, '$groupByFormat') as date"),
                DB::raw('COUNT(*) as count')
            ])
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '$groupByFormat')"))
            ->orderBy('date')
            ->get();

        return response()->json($result);
    }

    public function topEndpoints(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);

        $result = AnalyticsRequest::query()
            ->select('endpoint', DB::raw('COUNT(*) as count'))
            ->groupBy('endpoint')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();

        return response()->json($result);
    }

    public function tradeinsSummary(): JsonResponse
    {
        $totalTradeins = AnalyticsEvent::where('event_name', 'tradein_started')->count();
        $completedTradeins = AnalyticsEvent::where('event_name', 'tradein_completed')->count();
        $requoteRequests = AnalyticsEvent::where('event_name', 'requote_requested')->count();

        return response()->json([
            'total_tradeins' => $totalTradeins,
            'completed_tradeins' => $completedTradeins,
            'requote_requests' => $requoteRequests,
        ]);
    }

    public function tradeinsDemand(Request $request): JsonResponse
    {
        $groupBy = $request->input('group_by', 'model'); // model | brand

        $result = AnalyticsEvent::query()
            ->whereIn('event_name', ['tradein_started', 'quote_viewed'])
            ->select($groupBy, DB::raw('COUNT(*) as demand_score'))
            ->groupBy($groupBy)
            ->orderByDesc('demand_score')
            ->get();

        return response()->json($result);
    }

    public function tradeinsConditions(): JsonResponse
    {
        $result = AnalyticsEvent::query()
            ->whereNotNull('condition')
            ->select('condition', DB::raw('COUNT(*) as count'))
            ->groupBy('condition')
            ->get();

        return response()->json($result);
    }

    public function geography(Request $request): JsonResponse
    {
        $level = $request->input('level', 'city'); // city | area | district
        $validLevels = ['city', 'area', 'district'];
        
        if (!in_array($level, $validLevels)) {
             $level = 'city';
        }

        $result = AnalyticsEvent::query()
            ->whereNotNull($level)
            ->select($level, DB::raw('COUNT(*) as count'))
            ->groupBy($level)
            ->orderByDesc('count')
            ->get();

        return response()->json($result);
    }

    public function devices(): JsonResponse
    {
        $result = AnalyticsEvent::query()
            ->select('device_model', DB::raw('COUNT(*) as count'))
            ->groupBy('device_model')
            ->orderByDesc('count')
            ->get();

        return response()->json($result);
    }
}
