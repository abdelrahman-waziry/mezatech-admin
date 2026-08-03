<?php

namespace App\Http\Middleware;

use App\Enums\Audit\AuditAction;
use App\Services\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditFilamentActivity
{
    public function __construct(
        protected AuditLogService $auditService
    ) {}

    /**
     * Handle an incoming request.
     *
     * Logs Filament panel navigation and page views as audit events.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log GET requests to Filament pages (not Livewire updates)
        if (
            !Auth::check()
            || $request->method() !== 'GET'
            || $request->ajax()
            || $request->expectsJson()
            || str_contains($request->path(), 'livewire')
        ) {
            return $response;
        }

        try {
            $action = $this->resolveAction($request);

            if ($action) {
                $this->auditService->log($action, [
                    'resource' => $request->path(),
                    'response_status' => $response->getStatusCode(),
                    'success' => $response->getStatusCode() < 400,
                ], $request);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to log Filament activity', [
                'error' => $e->getMessage(),
            ]);
        }

        return $response;
    }

    /**
     * Resolve the audit action from the request path.
     */
    protected function resolveAction(Request $request): ?AuditAction
    {
        $path = $request->path();

        // Map Filament paths to audit actions
        if (str_contains($path, 'admin') && !str_contains($path, 'admin/')) {
            return AuditAction::VIEW_DASHBOARD;
        }

        if (str_contains($path, 'price-list') || str_contains($path, 'price-navigator')) {
            return AuditAction::VIEW_PRICES;
        }

        if (str_contains($path, 'analytics') || str_contains($path, 'analytics-')) {
            return AuditAction::VIEW_ANALYTICS;
        }

        if (str_contains($path, 'users')) {
            return AuditAction::VIEW_CUSTOMER_INFO;
        }

        if (str_contains($path, 'products') || str_contains($path, 'variants')) {
            return AuditAction::VIEW_PRODUCT_INFO;
        }

        if (str_contains($path, 'trade-in')) {
            return AuditAction::VIEW_REPORT;
        }

        if (str_contains($path, 'audit-logs')) {
            return AuditAction::VIEW_AUDIT_LOGS;
        }

        // Don't log unknown Filament pages to avoid noise
        return null;
    }
}
