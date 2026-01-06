<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Analytics\StoreAnalyticsEventRequest;
use App\Http\Requests\Analytics\StoreAnalyticsRequestRequest;
use App\Models\AnalyticsEvent;
use App\Models\AnalyticsRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class AnalyticsIngestionController extends Controller
{
    public function storeRequest(StoreAnalyticsRequestRequest $request): JsonResponse
    {
        $validated = $request->validated();

        AnalyticsRequest::create([
            'request_id' => $validated['request_id'],
            'endpoint' => $validated['endpoint'],
            'method' => $validated['method'],
            'status' => $validated['response']['status'],
            'duration_ms' => $validated['response']['duration_ms'],
            'error_type' => $validated['response']['error_type'] ?? null,
            'app_source' => $validated['app_source'],
            'app_version' => $validated['app_version'],
            'device_os' => $validated['device']['os'],
            'device_model' => $validated['device']['model'],
            'device_network' => $validated['device']['network'],
            'created_at' => Carbon::parse($validated['timestamp']),
        ]);

        return response()->json(['message' => 'Logged successfully'], 201);
    }

    public function storeEvent(StoreAnalyticsEventRequest $request): JsonResponse
    {
        $validated = $request->validated();

        AnalyticsEvent::create([
            'event_name' => $validated['event_name'],
            'user_id' => $validated['user_id'],
            'brand' => $validated['context']['brand'] ?? '',
            'model' => $validated['context']['model'] ?? '',
            'condition' => $validated['context']['condition'] ?? null,
            'quoted_price' => $validated['context']['quoted_price'] ?? null,
            'country' => $validated['location']['country'],
            'city' => $validated['location']['city'],
            'area' => $validated['location']['area'] ?? null,
            'district' => $validated['location']['district'] ?? null,
            'device_brand' => $validated['device']['brand'],
            'device_model' => $validated['device']['model'],
            'device_os_version' => $validated['device']['os_version'],
            'created_at' => Carbon::parse($validated['timestamp']),
        ]);

        return response()->json(['message' => 'Event logged successfully'], 201);
    }
}
