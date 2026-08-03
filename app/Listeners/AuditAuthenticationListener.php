<?php

namespace App\Listeners;

use App\Enums\Audit\AuditAction;
use App\Models\AuditSession;
use App\Services\AuditLogService;
use App\Services\GeoIpService;
use App\Services\UserAgentParserService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Log;

class AuditAuthenticationListener
{
    public function __construct(
        protected AuditLogService $auditService,
        protected UserAgentParserService $uaParser
    ) {}

    /**
     * Handle the Login event.
     */
    public function handleLogin(Login $event): void
    {
        try {
            $request = request();
            $user = $event->user;

            // Log the login audit event
            $this->auditService->logAuthentication(
                AuditAction::LOGIN,
                $user->email,
                true,
                null,
                [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'resource_id' => (string) $user->id,
                    'metadata' => [
                        'guard' => $event->guard ?? 'web',
                        'remember' => $event->remember ?? false,
                    ],
                ],
                $request
            );

            // Create audit session
            $uaInfo = $this->uaParser->parse($request->userAgent());
            $geoInfo = app(GeoIpService::class)->lookup($request->ip());

            AuditSession::create([
                'user_id' => $user->id,
                'session_id' => $request->session()?->getId(),
                'login_at' => now(),
                'auth_method' => 'password',
                'device_type' => $uaInfo['device_type'],
                'browser' => $uaInfo['browser'],
                'browser_version' => $uaInfo['browser_version'],
                'operating_system' => $uaInfo['operating_system'],
                'platform' => $uaInfo['platform'],
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'country' => $geoInfo['country'],
                'city' => $geoInfo['city'],
                'timezone' => $geoInfo['timezone'],
                'isp' => $geoInfo['isp'],
                'is_active' => true,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to log login event', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle the Logout event.
     */
    public function handleLogout(Logout $event): void
    {
        try {
            $request = request();
            $user = $event->user;

            if (!$user) {
                return;
            }

            // Log the logout audit event
            $this->auditService->logAuthentication(
                AuditAction::LOGOUT,
                $user->email,
                true,
                null,
                [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'resource_id' => (string) $user->id,
                ],
                $request
            );

            // End active sessions for this user
            $sessionId = $request->session()?->getId();
            $query = AuditSession::where('user_id', $user->id)->where('is_active', true);

            if ($sessionId) {
                $query->where('session_id', $sessionId);
            }

            $query->get()->each(function (AuditSession $session) {
                $session->endSession('logout');
            });
        } catch (\Throwable $e) {
            Log::error('Failed to log logout event', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle the Failed login event.
     */
    public function handleFailed(Failed $event): void
    {
        try {
            $request = request();
            $email = $event->credentials['email'] ?? 'unknown';

            $this->auditService->logAuthentication(
                AuditAction::FAILED_LOGIN,
                $email,
                false,
                'Invalid credentials',
                [
                    'user_id' => $event->user?->id,
                    'user_name' => $event->user?->name,
                    'metadata' => [
                        'guard' => $event->guard ?? 'web',
                        'attempted_email' => $email,
                    ],
                ],
                $request
            );
        } catch (\Throwable $e) {
            Log::error('Failed to log failed login event', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle the Lockout event.
     */
    public function handleLockout(Lockout $event): void
    {
        try {
            $request = $event->request;

            $this->auditService->logAuthentication(
                AuditAction::ACCOUNT_LOCK,
                $request->input('email', 'unknown'),
                false,
                'Account locked due to too many login attempts',
                [
                    'is_suspicious' => true,
                    'suspicious_reason' => 'Account locked due to excessive failed login attempts',
                    'metadata' => [
                        'attempted_email' => $request->input('email'),
                    ],
                ],
                $request
            );
        } catch (\Throwable $e) {
            Log::error('Failed to log lockout event', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @return array<string, string>
     */
    public function subscribe(): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
            Failed::class => 'handleFailed',
            Lockout::class => 'handleLockout',
        ];
    }
}
