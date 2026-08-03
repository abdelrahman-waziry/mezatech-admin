<?php

namespace App\Jobs;

use App\Models\AuditLog;
use App\Services\GeoIpService;
use App\Services\SuspiciousActivityDetector;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAuditLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected array $logData
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        GeoIpService $geoIpService,
        SuspiciousActivityDetector $detector
    ): void {
        try {
            // Perform GeoIP lookup
            $geoData = $geoIpService->lookup($this->logData['ip_address'] ?? null);
            $this->logData = array_merge($this->logData, $geoData);

            // Run suspicious activity detection (only if not already flagged)
            if (empty($this->logData['is_suspicious'])) {
                $suspiciousResult = $detector->analyze($this->logData);
                $this->logData['is_suspicious'] = $suspiciousResult['is_suspicious'];
                $this->logData['suspicious_reason'] = $suspiciousResult['reason'];
            }

            // Persist the audit log record
            AuditLog::create($this->logData);
        } catch (\Throwable $e) {
            Log::error('Failed to process audit log', [
                'action' => $this->logData['action'] ?? 'unknown',
                'error' => $e->getMessage(),
                'data' => array_diff_key($this->logData, array_flip([
                    'previous_value', 'new_value', 'metadata',
                ])),
            ]);

            throw $e; // Let the queue retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical('Audit log processing permanently failed', [
            'action' => $this->logData['action'] ?? 'unknown',
            'user_id' => $this->logData['user_id'] ?? null,
            'error' => $exception->getMessage(),
        ]);
    }
}
