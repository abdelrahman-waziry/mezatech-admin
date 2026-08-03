<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Audit Logging Enabled
    |--------------------------------------------------------------------------
    |
    | Master toggle for the audit logging system. When disabled, no audit
    | records will be created. Useful for development or testing.
    |
    */

    'enabled' => env('AUDIT_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Async Processing
    |--------------------------------------------------------------------------
    |
    | When enabled, audit logs are dispatched to a queue job for async
    | processing. This ensures minimal impact on request latency.
    | Set to false for synchronous logging (useful for debugging).
    |
    */

    'async' => env('AUDIT_ASYNC', true),

    /*
    |--------------------------------------------------------------------------
    | Queue Name
    |--------------------------------------------------------------------------
    |
    | The queue name to use for async audit log processing.
    |
    */

    'queue' => env('AUDIT_QUEUE', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Retention Policy
    |--------------------------------------------------------------------------
    |
    | Number of days to retain audit logs before they are eligible for
    | pruning. Set to 0 to disable automatic pruning.
    |
    */

    'retention_days' => env('AUDIT_RETENTION_DAYS', 365),

    /*
    |--------------------------------------------------------------------------
    | GeoIP Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for IP geolocation lookups. Uses ip-api.com free tier
    | with result caching to avoid rate limits.
    |
    */

    'geoip' => [
        'enabled' => env('AUDIT_GEOIP_ENABLED', true),
        'cache_ttl' => env('AUDIT_GEOIP_CACHE_TTL', 86400), // 24 hours
        'api_url' => 'http://ip-api.com/json/',
        'timeout' => 3, // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Suspicious Activity Detection
    |--------------------------------------------------------------------------
    |
    | Thresholds for flagging suspicious activity. Adjust these values
    | based on your expected traffic patterns.
    |
    */

    'suspicious' => [
        // Failed login attempts threshold
        'failed_login_count' => env('AUDIT_FAILED_LOGIN_COUNT', 5),
        'failed_login_window_minutes' => env('AUDIT_FAILED_LOGIN_WINDOW', 10),

        // Rapid requests threshold
        'rapid_request_count' => env('AUDIT_RAPID_REQUEST_COUNT', 50),
        'rapid_request_window_minutes' => env('AUDIT_RAPID_REQUEST_WINDOW', 1),

        // Excessive exports threshold
        'excessive_export_count' => env('AUDIT_EXCESSIVE_EXPORT_COUNT', 10),
        'excessive_export_window_minutes' => env('AUDIT_EXCESSIVE_EXPORT_WINDOW', 60),

        // Bulk modifications threshold
        'bulk_modification_count' => env('AUDIT_BULK_MODIFICATION_COUNT', 20),
        'bulk_modification_window_minutes' => env('AUDIT_BULK_MODIFICATION_WINDOW', 5),

        // New country/device detection
        'detect_new_country' => true,
        'detect_new_device' => true,

        // Concurrent sessions threshold
        'max_concurrent_sessions' => env('AUDIT_MAX_CONCURRENT_SESSIONS', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | Super Admin Role
    |--------------------------------------------------------------------------
    |
    | The role name that identifies Super Admin users. Must match the
    | Filament Shield configuration.
    |
    */

    'super_admin_role' => env('AUDIT_SUPER_ADMIN_ROLE', 'super_admin'),

];
