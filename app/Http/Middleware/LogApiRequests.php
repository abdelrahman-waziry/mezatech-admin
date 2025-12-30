<?php

namespace App\Http\Middleware;

use App\Models\ApiRequest;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);
        $response = $next($request);
        $duration = (int) (($micro = microtime(true) - $start) * 1000);

        try {
            ApiRequest::create([
                'method' => $request->method(),
                'endpoint' => $request->path(),
                'status_code' => (string) $response->getStatusCode(),
                'error_type' => $response->getStatusCode() >= 400 ? $response->getStatusText() : null,
                'response_time_ms' => $duration,
                'recorded_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to log API request', ['error' => $e->getMessage()]);
        }

        return $response;
    }
}

