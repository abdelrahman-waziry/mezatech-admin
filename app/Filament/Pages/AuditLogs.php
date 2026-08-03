<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AuditStatsOverview;
use App\Models\AuditLog;
use App\Services\AuditLogService;
use Filament\Pages\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class AuditLogs extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-shield-exclamation';

    protected string $view = 'filament.pages.audit-logs';

    protected static \UnitEnum|string|null $navigationGroup = 'Audit Logs';

    protected static ?string $title = 'Audit Logs & Activity';

    protected static ?int $navigationSort = 100;

    // Filter state (bound via Livewire)
    public string $search = '';
    public string $category = '';
    public string $is_suspicious = '';
    public int $page = 1;
    public int $perPage = 50;

    // Listeners for Livewire events
    protected $listeners = ['refreshLogs' => '$refresh', 'deleteLog' => 'deleteLog'];

    /**
     * Check if the authenticated user can access this page.
     * Only Super Admins are allowed.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(config('audit.super_admin_role', 'super_admin')) ?? false;
    }

    /**
     * Register widgets to display on this page.
     */
    protected function getHeaderWidgets(): array
    {
        return [
            AuditStatsOverview::class,
        ];
    }

    /**
     * Fetch paginated audit logs based on current filter state.
     */
    public function getLogsProperty(): LengthAwarePaginator
    {
        $query = AuditLog::query()->with('user:id,name,email');

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('user_email', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('resource', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('uuid', $search);
            });
        }

        if ($this->category) {
            $query->where('category', $this->category);
        }

        if ($this->is_suspicious !== '') {
            $query->where('is_suspicious', filter_var($this->is_suspicious, FILTER_VALIDATE_BOOLEAN));
        }

        return $query->orderByDesc('created_at')->paginate($this->perPage, ['*'], 'page', $this->page);
    }

    /**
     * Called when any filter changes — reset to page 1.
     */
    public function updatedSearch(): void
    {
        $this->page = 1;
    }

    public function updatedCategory(): void
    {
        $this->page = 1;
    }

    public function updatedIsSuspicious(): void
    {
        $this->page = 1;
    }

    public function nextPage(): void
    {
        $logs = $this->logs;
        if ($this->page < $logs->lastPage()) {
            $this->page++;
        }
    }

    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    /**
     * Delete a single audit log record and meta-audit the deletion.
     */
    public function deleteLog(string $uuid): void
    {
        $log = AuditLog::where('uuid', $uuid)->firstOrFail();
        $logArray = $log->toArray();
        $log->delete();

        app(AuditLogService::class)->log(\App\Enums\Audit\AuditAction::DELETE_AUDIT_LOG, [
            'resource'         => 'AuditLog',
            'resource_id'      => $uuid,
            'previous_value'   => $logArray,
            'is_suspicious'    => true,
            'suspicious_reason' => 'Audit log record was manually deleted',
        ], request());

        $this->dispatch('log-deleted');
    }

    /**
     * Provide view data.
     */
    protected function getViewData(): array
    {
        $logs = $this->logs;

        return [
            'logs'        => $logs->items(),
            'currentPage' => $logs->currentPage(),
            'lastPage'    => $logs->lastPage(),
            'total'       => $logs->total(),
            'from'        => $logs->firstItem() ?? 0,
            'to'          => $logs->lastItem() ?? 0,
        ];
    }
}
