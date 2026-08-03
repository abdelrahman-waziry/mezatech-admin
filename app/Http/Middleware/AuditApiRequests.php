<?php

namespace App\Http\Middleware;

use App\Enums\Audit\AuditAction;
use App\Models\ApiRequest;
use App\Services\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditApiRequests
{
    public function __construct(
        protected AuditLogService $auditService
    ) {}

    /**
     * Handle an incoming request.
     *
     * Logs every API request as an audit event and also persists
     * to the existing api_requests table for backward compatibility.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);
        $response = $next($request);
        $duration = (int) ((microtime(true) - $start) * 1000);

        // Preserve existing ApiRequest logging
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

        // Audit logging (only for authenticated requests on sensitive endpoints)
        if (Auth::check() && $this->isSensitiveEndpoint($request)) {
            try {
                $this->auditService->log(AuditAction::API_REQUEST, [
                    'resource' => $request->path(),
                    'response_status' => $response->getStatusCode(),
                    'execution_time_ms' => $duration,
                    'success' => $response->getStatusCode() < 400,
                    'error_message' => $response->getStatusCode() >= 400
                        ? "HTTP {$response->getStatusCode()}" : null,
                    'metadata' => [
                        'query_params' => $request->query(),
                    ],
                ], $request);
            } catch (\Throwable $e) {
                Log::error('Failed to create audit log for API request', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $response;
    }

    /**
     * Determine if the endpoint is sensitive enough to audit.
     * Avoids logging every single request (health checks, etc.)
     */
    protected function isSensitiveEndpoint(Request $request): bool
    {
        $sensitivePatterns = [
            'admin/',
            'brands',
            'conditions',
            'products',
            'variants',
            'parts',
            'files',
            'trade-in',
            'repair',
            'accessor',
            'analytics',
        ];

        $path = $request->path();

        foreach ($sensitivePatterns as $pattern) {
            if (str_contains($path, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
