<?php

namespace App\Services;

use App\Enums\Audit\AuditAction;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

class SuspiciousActivityDetector
{
    /**
     * Analyze an audit log entry for suspicious activity.
     *
     * @return array{is_suspicious: bool, reason: string|null}
     */
    public function analyze(array $logData): array
    {
        $checks = [
            $this->checkFailedLogins($logData),
            $this->checkNewCountry($logData),
            $this->checkNewDevice($logData),
            $this->checkRapidRequests($logData),
            $this->checkExcessiveExports($logData),
            $this->checkBulkModifications($logData),
            $this->checkConcurrentSessions($logData),
            $this->checkUnauthorizedAccess($logData),
        ];

        foreach ($checks as $result) {
            if ($result['is_suspicious']) {
                return $result;
            }
        }

        return ['is_suspicious' => false, 'reason' => null];
    }

    /**
     * Check for multiple failed login attempts.
     */
    protected function checkFailedLogins(array $data): array
    {
        if (($data['action'] ?? '') !== AuditAction::FAILED_LOGIN->value) {
            return ['is_suspicious' => false, 'reason' => null];
        }

        $threshold = config('audit.suspicious.failed_login_count', 5);
        $window = config('audit.suspicious.failed_login_window_minutes', 10);

        $identifier = $data['ip_address'] ?? $data['user_email'] ?? null;
        if (!$identifier) {
            return ['is_suspicious' => false, 'reason' => null];
        }

        $count = AuditLog::where('action', AuditAction::FAILED_LOGIN->value)
            ->where(function ($query) use ($data) {
                $query->where('ip_address', $data['ip_address'] ?? '')
                    ->orWhere('user_email', $data['user_email'] ?? '');
            })
            ->where('created_at', '>=', now()->subMinutes($window))
            ->count();

        if ($count >= $threshold - 1) { // -1 because current entry isn't persisted yet
            return [
                'is_suspicious' => true,
                'reason' => "Multiple failed login attempts ({$count}+ in {$window} minutes)",
            ];
        }

        return ['is_suspicious' => false, 'reason' => null];
    }

    /**
     * Check for access from a new country.
     */
    protected function checkNewCountry(array $data): array
    {
        if (!config('audit.suspicious.detect_new_country', true)) {
            return ['is_suspicious' => false, 'reason' => null];
        }

        $userId = $data['user_id'] ?? null;
        $country = $data['country'] ?? null;

        if (!$userId || !$country) {
            return ['is_suspicious' => false, 'reason' => null];
        }

        // Only flag for login events
        if (($data['action'] ?? '') !== AuditAction::LOGIN->value) {
            return ['is_suspicious' => false, 'reason' => null];
        }

        $knownCountries = AuditLog::where('user_id', $userId)
            ->where('action', AuditAction::LOGIN->value)
            ->where('success', true)
            ->whereNotNull('country')
            ->distinct()
            ->pluck('country')
            ->toArray();

        if (!empty($knownCountries) && !in_array($country, $knownCountries)) {
            return [
                'is_suspicious' => true,
                'reason' => "Login from new country: {$country} (known: " . implode(', ', $knownCountries) . ")",
            ];
        }

        return ['is_suspicious' => false, 'reason' => null];
    }

    /**
     * Check for access from a new device.
     */
    protected function checkNewDevice(array $data): array
    {
        if (!config('audit.suspicious.detect_new_device', true)) {
            return ['is_suspicious' => false, 'reason' => null];
        }

        $userId = $data['user_id'] ?? null;
        $userAgent = $data['user_agent'] ?? null;

        if (!$userId || !$userAgent) {
            return ['is_suspicious' => false, 'reason' => null];
        }

        // Only flag for login events
        if (($data['action'] ?? '') !== AuditAction::LOGIN->value) {
            return ['is_suspicious' => false, 'reason' => null];
        }

        // Create a device fingerprint from browser + OS + device type
        $fingerprint = implode('|', array_filter([
            $data['browser'] ?? null,
            $data['operating_system'] ?? null,
            $data['device_type'] ?? null,
        ]));

        if (empty($fingerprint)) {
            return ['is_suspicious' => false, 'reason' => null];
        }

        $hasExistingLogins = AuditLog::where('user_id', $userId)
            ->where('action', AuditAction::LOGIN->value)
            ->where('success', true)
            ->exists();

        if (!$hasExistingLogins) {
            return ['is_suspicious' => false, 'reason' => null];
        }

        $knownDevice = AuditLog::where('user_id', $userId)
            ->where('action', AuditAction::LOGIN->value)
            ->where('success', true)
            ->where('browser', $data['browser'] ?? '')
            ->where('operating_system', $data['operating_system'] ?? '')
            ->where('device_type', $data['device_type'] ?? '')
            ->exists();

        if (!$knownDevice) {
            return [
                'is_suspicious' => true,
                'reason' => "Login from new device: {$fingerprint}",
            ];
        }

        return ['is_suspicious' => false, 'reason' => null];
    }

    /**
     * Check for rapid repeated requests.
     */
    protected function checkRapidRequests(array $data): array
    {
        $userId = $data['user_id'] ?? null;
        if (!$userId) {
            return ['is_suspicious' => false, 'reason' => null];
        }

        $threshold = config('audit.suspicious.rapid_request_count', 50);
        $window = config('audit.suspicious.rapid_request_window_minutes', 1);

        $count = AuditLog::where('user_id', $userId)
            ->where('created_at', '>=', now()->subMinutes($window))
            ->count();

        if ($count >= $threshold) {
            return [
                'is_suspicious' => true,
                'reason' => "Rapid repeated requests ({$count}+ in {$window} minute(s))",
            ];
        }

        return ['is_suspicious' => false, 'reason' => null];
    }

    /**
     * Check for excessive exports.
     */
    protected function checkExcessiveExports(array $data): array
    {
        $action = $data['action'] ?? '';
        $exportActions = [
            AuditAction::EXPORT_PRICES->value,
            AuditAction::EXPORT_REPORT->value,
            AuditAction::EXPORT_FILE->value,
            AuditAction::EXPORT_AUDIT_LOGS->value,
            AuditAction::DOWNLOAD_PRICING_SHEET->value,
            AuditAction::DOWNLOAD_FILE->value,
        ];

        if (!in_array($action, $exportActions)) {
            return ['is_suspicious' => false, 'reason' => null];
        }

        $userId = $data['user_id'] ?? null;
        if (!$userId) {
            return ['is_suspicious' => false, 'reason' => null];
        }

        $threshold = config('audit.suspicious.excessive_export_count', 10);
        $window = config('audit.suspicious.excessive_export_window_minutes', 60);

        $count = AuditLog::where('user_id', $userId)
            ->whereIn('action', $exportActions)
            ->where('created_at', '>=', now()->subMinutes($window))
            ->count();

        if ($count >= $threshold) {
            return [
                'is_suspicious' => true,
                'reason' => "Excessive exports ({$count}+ in {$window} minutes)",
            ];
        }

        return ['is_suspicious' => false, 'reason' => null];
    }

    /**
     * Check for bulk modifications.
     */
    protected function checkBulkModifications(array $data): array
    {
        $action = $data['action'] ?? '';
        $modifyActions = [
            AuditAction::CREATE_PRICE->value,
            AuditAction::UPDATE_PRICE->value,
            AuditAction::DELETE_PRICE->value,
            AuditAction::CREATE_USER->value,
            AuditAction::EDIT_USER->value,
            AuditAction::DELETE_USER->value,
            AuditAction::CHANGE_ROLE->value,
        ];

        if (!in_array($action, $modifyActions)) {
            return ['is_suspicious' => false, 'reason' => null];
        }

        $userId = $data['user_id'] ?? null;
        if (!$userId) {
            return ['is_suspicious' => false, 'reason' => null];
        }

        $threshold = config('audit.suspicious.bulk_modification_count', 20);
        $window = config('audit.suspicious.bulk_modification_window_minutes', 5);

        $count = AuditLog::where('user_id', $userId)
            ->whereIn('action', $modifyActions)
            ->where('created_at', '>=', now()->subMinutes($window))
            ->count();

        if ($count >= $threshold) {
            return [
                'is_suspicious' => true,
                'reason' => "Bulk modifications ({$count}+ in {$window} minutes)",
            ];
        }

        return ['is_suspicious' => false, 'reason' => null];
    }

    /**
     * Check for multiple concurrent sessions.
     */
    protected function checkConcurrentSessions(array $data): array
    {
        if (($data['action'] ?? '') !== AuditAction::LOGIN->value) {
            return ['is_suspicious' => false, 'reason' => null];
        }

        $userId = $data['user_id'] ?? null;
        if (!$userId) {
            return ['is_suspicious' => false, 'reason' => null];
        }

        $maxSessions = config('audit.suspicious.max_concurrent_sessions', 3);

        $activeSessions = \App\Models\AuditSession::where('user_id', $userId)
            ->where('is_active', true)
            ->count();

        if ($activeSessions >= $maxSessions) {
            return [
                'is_suspicious' => true,
                'reason' => "Multiple concurrent sessions ({$activeSessions} active, max {$maxSessions})",
            ];
        }

        return ['is_suspicious' => false, 'reason' => null];
    }

    /**
     * Check for unauthorized access attempts.
     */
    protected function checkUnauthorizedAccess(array $data): array
    {
        if (($data['action'] ?? '') === AuditAction::UNAUTHORIZED_ACCESS->value) {
            return [
                'is_suspicious' => true,
                'reason' => 'Attempted access to restricted endpoint',
            ];
        }

        if (($data['action'] ?? '') === AuditAction::PRIVILEGE_ESCALATION->value) {
            return [
                'is_suspicious' => true,
                'reason' => 'Privilege escalation attempt detected',
            ];
        }

        return ['is_suspicious' => false, 'reason' => null];
    }
}
