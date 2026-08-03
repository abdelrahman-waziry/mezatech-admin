<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\AuditSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    /**
     * Get paginated audit logs with search and filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::query()
            ->with('user:id,name,email');

        // Apply filters
        $this->applyFilters($query, $request);

        $perPage = $request->integer('per_page', 50);
        
        $logs = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json($logs);
    }

    /**
     * Get a single audit log entry.
     */
    public function show(string $uuid): JsonResponse
    {
        $log = AuditLog::with('user:id,name,email')->where('uuid', $uuid)->firstOrFail();

        return response()->json($log);
    }

    /**
     * Get dashboard summary statistics.
     */
    public function summary(): JsonResponse
    {
        $today = now()->startOfDay();

        $totalEvents = AuditLog::count();
        
        $failedLogins = AuditLog::where('action', 'failed_login')
            ->where('created_at', '>=', $today)
            ->count();
            
        $securityAlerts = AuditLog::where('is_suspicious', true)
            ->where('created_at', '>=', $today)
            ->count();
            
        $activeSessions = AuditSession::where('is_active', true)->count();
        
        $exportsToday = AuditLog::whereIn('action', ['export_prices', 'export_report', 'export_file', 'download_pricing_sheet'])
            ->where('created_at', '>=', $today)
            ->count();
            
        $priceModifications = AuditLog::whereIn('action', ['create_price', 'update_price', 'delete_price', 'import_pricing_sheet'])
            ->where('created_at', '>=', $today)
            ->count();

        return response()->json([
            'total_events' => $totalEvents,
            'failed_logins_today' => $failedLogins,
            'security_alerts_today' => $securityAlerts,
            'active_sessions' => $activeSessions,
            'exports_today' => $exportsToday,
            'price_modifications_today' => $priceModifications,
        ]);
    }

    /**
     * Export audit logs to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = AuditLog::query();
        $this->applyFilters($query, $request);
        
        // Log the export action itself
        app(\App\Services\AuditLogService::class)->log(\App\Enums\Audit\AuditAction::EXPORT_AUDIT_LOGS, [
            'resource' => 'AuditLogs',
            'metadata' => ['filters' => $request->all()],
        ], $request);

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($handle, [
                'ID', 'Date & Time', 'User ID', 'User Name', 'Role', 'Action', 
                'Category', 'Resource', 'Resource ID', 'Status', 'IP Address', 
                'Location', 'Device', 'Is Suspicious', 'Reason'
            ]);

            $query->orderByDesc('created_at')->chunk(1000, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->uuid,
                        $log->created_at->toIso8601String(),
                        $log->user_id,
                        $log->user_name ?? 'System',
                        $log->user_role,
                        $log->action,
                        $log->category,
                        $log->resource,
                        $log->resource_id,
                        $log->success ? 'Success' : 'Failed',
                        $log->ip_address,
                        $log->location,
                        $log->device_info,
                        $log->is_suspicious ? 'Yes' : 'No',
                        $log->suspicious_reason ?? $log->error_message
                    ]);
                }
            });

            fclose($handle);
        }, 'audit-logs-export-' . now()->format('Y-m-d-His') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Delete an audit log (requires confirmation).
     */
    public function destroy(string $uuid, Request $request): JsonResponse
    {
        $log = AuditLog::where('uuid', $uuid)->firstOrFail();
        
        $logArray = $log->toArray();
        $log->delete();

        // Meta-audit: log the deletion
        app(\App\Services\AuditLogService::class)->log(\App\Enums\Audit\AuditAction::DELETE_AUDIT_LOG, [
            'resource' => 'AuditLog',
            'resource_id' => $uuid,
            'previous_value' => $logArray,
            'is_suspicious' => true, // Flag deletions as inherently suspicious
            'suspicious_reason' => 'Audit log record was manually deleted',
        ], $request);

        return response()->json(['message' => 'Audit log deleted successfully']);
    }

    /**
     * Apply common filters to the query.
     */
    protected function applyFilters($query, Request $request): void
    {
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('is_suspicious')) {
            $query->where('is_suspicious', filter_var($request->is_suspicious, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('status')) {
            $query->where('success', filter_var($request->status, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', "%{$request->ip_address}%");
        }

        // Global search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('user_email', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('resource', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('uuid', $search);
            });
        }
    }
}
