<?php

namespace App\Http\Middleware;

use App\Enums\Audit\AuditAction;
use App\Services\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function __construct(
        protected AuditLogService $auditService
    ) {}

    /**
     * Handle an incoming request.
     *
     * Ensures the authenticated user has the super_admin role.
     * Logs unauthorized access attempts as security events.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $superAdminRole = config('audit.super_admin_role', 'super_admin');

        if (!$user || !$user->hasRole($superAdminRole)) {
            // Log the unauthorized access attempt
            try {
                $this->auditService->logUnauthorizedAccess($request, sprintf(
                    'User %s attempted to access Super Admin endpoint: %s %s',
                    $user ? $user->email : 'unauthenticated',
                    $request->method(),
                    $request->path()
                ));
            } catch (\Throwable $e) {
                Log::error('Failed to log unauthorized access', [
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'message' => 'Forbidden. Super Admin access required.',
            ], 403);
        }

        return $next($request);
    }
}
