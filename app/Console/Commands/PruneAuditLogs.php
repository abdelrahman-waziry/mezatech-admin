<?php

namespace App\Console\Commands;

use App\Enums\Audit\AuditAction;
use App\Models\AuditLog;
use App\Models\AuditSession;
use App\Services\AuditLogService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PruneAuditLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:prune {--days= : Override the default retention days} {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune old audit logs based on the retention policy';

    /**
     * Execute the console command.
     */
    public function handle(AuditLogService $auditService): int
    {
        $days = $this->option('days') ?? config('audit.retention_days', 365);
        
        if ($days <= 0) {
            $this->info('Audit log pruning is disabled (retention_days = 0).');
            return self::SUCCESS;
        }

        $cutoffDate = Carbon::now()->subDays((int) $days);

        $logCount = AuditLog::where('created_at', '<', $cutoffDate)->count();
        $sessionCount = AuditSession::where('created_at', '<', $cutoffDate)->count();

        if ($logCount === 0 && $sessionCount === 0) {
            $this->info('No old audit logs or sessions found to prune.');
            return self::SUCCESS;
        }

        if (!$this->option('force')) {
            $this->warn("Found {$logCount} audit logs and {$sessionCount} sessions older than {$days} days.");
            if (!$this->confirm('Are you sure you want to permanently delete these records?')) {
                $this->info('Pruning aborted.');
                return self::SUCCESS;
            }
        }

        $this->info("Pruning records older than {$cutoffDate->toIso8601String()}...");

        // Prune logs
        $logsDeleted = AuditLog::where('created_at', '<', $cutoffDate)->delete();
        
        // Prune sessions
        $sessionsDeleted = AuditSession::where('created_at', '<', $cutoffDate)->delete();

        // Meta-audit: log that pruning occurred
        try {
            $auditService->log(AuditAction::PRUNE_AUDIT_LOGS, [
                'success' => true,
                'resource' => 'System',
                'metadata' => [
                    'retention_days' => $days,
                    'cutoff_date' => $cutoffDate->toIso8601String(),
                    'logs_deleted' => $logsDeleted,
                    'sessions_deleted' => $sessionsDeleted,
                    'executed_by' => 'console',
                ],
            ]);
        } catch (\Throwable $e) {
            $this->error("Failed to log pruning action: {$e->getMessage()}");
        }

        $this->info("Successfully pruned {$logsDeleted} logs and {$sessionsDeleted} sessions.");

        return self::SUCCESS;
    }
}
