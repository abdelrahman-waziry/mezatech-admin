<?php

namespace App\Services;

use App\Enums\Audit\AuditAction;
use App\Enums\Audit\AuditCategory;
use App\Jobs\ProcessAuditLog;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditLogService
{
    protected UserAgentParserService $uaParser;

    public function __construct(UserAgentParserService $uaParser)
    {
        $this->uaParser = $uaParser;
    }

    /**
     * Log an audit event.
     */
    public function log(
        AuditAction $action,
        array $context = [],
        ?Request $request = null
    ): void {
        if (!config('audit.enabled', true)) {
            return;
        }

        try {
            $request = $request ?? request();
            $user = Auth::user();
            $uaInfo = $this->uaParser->parse($request->userAgent());

            $data = array_merge([
                // User Information
                'user_id' => $user?->id,
                'user_name' => $user?->name,
                'user_email' => $user?->email,
                'user_role' => $user ? $this->getUserRole($user) : null,
                'session_id' => $request->session()?->getId(),

                // Action
                'action' => $action->value,
                'category' => $action->category()->value,

                // Device Information
                'browser' => $uaInfo['browser'],
                'browser_version' => $uaInfo['browser_version'],
                'operating_system' => $uaInfo['operating_system'],
                'device_type' => $uaInfo['device_type'],
                'user_agent' => $request->userAgent(),

                // Network
                'ip_address' => $request->ip(),

                // Request
                'request_url' => $request->fullUrl(),
                'http_method' => $request->method(),

                // Defaults
                'success' => true,
            ], $context);

            if (config('audit.async', true)) {
                ProcessAuditLog::dispatch($data)->onQueue(config('audit.queue', 'default'));
            } else {
                $this->processSync($data);
            }
        } catch (\Throwable $e) {
            // Never let audit logging break the application
            Log::error('Audit logging failed', [
                'action' => $action->value,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Log a resource change with before/after values.
     */
    public function logResourceChange(
        AuditAction $action,
        string $resource,
        string|int|null $resourceId = null,
        ?array $previousValue = null,
        ?array $newValue = null,
        array $context = [],
        ?Request $request = null
    ): void {
        $this->log($action, array_merge([
            'resource' => $resource,
            'resource_id' => $resourceId ? (string) $resourceId : null,
            'previous_value' => $previousValue,
            'new_value' => $newValue,
        ], $context), $request);
    }

    /**
     * Log an authentication event.
     */
    public function logAuthentication(
        AuditAction $action,
        ?string $email = null,
        bool $success = true,
        ?string $errorMessage = null,
        array $context = [],
        ?Request $request = null
    ): void {
        $this->log($action, array_merge([
            'user_email' => $email ?? Auth::user()?->email,
            'success' => $success,
            'error_message' => $errorMessage,
            'resource' => 'User',
        ], $context), $request);
    }

    /**
     * Log a suspicious activity.
     */
    public function logSuspicious(
        AuditAction $action,
        string $reason,
        array $context = [],
        ?Request $request = null
    ): void {
        $this->log($action, array_merge([
            'is_suspicious' => true,
            'suspicious_reason' => $reason,
        ], $context), $request);
    }

    /**
     * Log an unauthorized access attempt.
     */
    public function logUnauthorizedAccess(
        ?Request $request = null,
        ?string $reason = null
    ): void {
        $request = $request ?? request();
        $user = Auth::user();

        $this->log(AuditAction::UNAUTHORIZED_ACCESS, [
            'success' => false,
            'error_message' => $reason ?? 'Unauthorized access attempt',
            'resource' => $request->path(),
            'is_suspicious' => true,
            'suspicious_reason' => 'Attempted access to restricted endpoint',
            'metadata' => [
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'user_agent' => $request->userAgent(),
                'reason' => $reason ?? 'Insufficient permissions',
            ],
        ], $request);
    }

    /**
     * Process an audit log synchronously.
     */
    protected function processSync(array $data): void
    {
        try {
            $geoIp = app(GeoIpService::class);
            $detector = app(SuspiciousActivityDetector::class);

            // GeoIP lookup
            $geoData = $geoIp->lookup($data['ip_address'] ?? null);
            $data = array_merge($data, $geoData);

            // Suspicious activity detection
            if (empty($data['is_suspicious'])) {
                $suspiciousResult = $detector->analyze($data);
                $data['is_suspicious'] = $suspiciousResult['is_suspicious'];
                $data['suspicious_reason'] = $suspiciousResult['reason'];
            }

            AuditLog::create($data);
        } catch (\Throwable $e) {
            Log::error('Sync audit log processing failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get the primary role name for a user.
     */
    protected function getUserRole($user): ?string
    {
        if (method_exists($user, 'getRoleNames')) {
            $roles = $user->getRoleNames();
            return $roles->first();
        }

        return null;
    }
}
