<?php

namespace App\Filament\Widgets;

use App\Models\AuditLog;
use App\Models\AuditSession;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AuditStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = now()->startOfDay();
        $sevenDaysAgo = now()->subDays(6)->startOfDay();

        // Total Events
        $totalEvents = AuditLog::count();
        $totalEventsChart = $this->getTrendData(AuditLog::query(), $sevenDaysAgo);
        
        // Failed Logins
        $failedLogins = AuditLog::where('action', 'failed_login')->where('created_at', '>=', $today)->count();
        $failedLoginsChart = $this->getTrendData(AuditLog::where('action', 'failed_login'), $sevenDaysAgo);
            
        // Security Alerts
        $securityAlerts = AuditLog::where('is_suspicious', true)->where('created_at', '>=', $today)->count();
        $securityAlertsChart = $this->getTrendData(AuditLog::where('is_suspicious', true), $sevenDaysAgo);
            
        // Active Sessions
        $activeSessions = AuditSession::where('is_active', true)->count();
        $activeSessionsChart = $this->getTrendData(AuditSession::where('is_active', true), $sevenDaysAgo);
        
        // Exports
        $exportActions = ['export_prices', 'export_report', 'export_file', 'download_pricing_sheet'];
        $exportsToday = AuditLog::whereIn('action', $exportActions)->where('created_at', '>=', $today)->count();
        $exportsChart = $this->getTrendData(AuditLog::whereIn('action', $exportActions), $sevenDaysAgo);
            
        // Price Modifications
        $priceActions = ['create_price', 'update_price', 'delete_price', 'import_pricing_sheet'];
        $priceModifications = AuditLog::whereIn('action', $priceActions)->where('created_at', '>=', $today)->count();
        $priceChart = $this->getTrendData(AuditLog::whereIn('action', $priceActions), $sevenDaysAgo);

        return [
            Stat::make('Total Audit Events', number_format($totalEvents))
                ->description('All recorded events')
                ->descriptionIcon('heroicon-m-server')
                ->chart($totalEventsChart)
                ->color('primary'),

            Stat::make('Failed Logins Today', $failedLogins)
                ->description('Since midnight')
                ->descriptionIcon('heroicon-m-x-circle')
                ->chart($failedLoginsChart)
                ->color($failedLogins > 0 ? 'warning' : 'success'),

            Stat::make('Security Alerts Today', $securityAlerts)
                ->description('Suspicious activity')
                ->descriptionIcon('heroicon-m-shield-exclamation')
                ->chart($securityAlertsChart)
                ->color($securityAlerts > 0 ? 'danger' : 'success'),

            Stat::make('Active Sessions', $activeSessions)
                ->description('Users currently logged in')
                ->descriptionIcon('heroicon-m-users')
                ->chart($activeSessionsChart)
                ->color('info'),

            Stat::make('Exports Today', $exportsToday)
                ->description('Data downloaded')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->chart($exportsChart)
                ->color('gray'),

            Stat::make('Price Modifications Today', $priceModifications)
                ->description('Creates, updates, deletions')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->chart($priceChart)
                ->color('success'),
        ];
    }
    
    protected function getTrendData($query, $startDate): array
    {
        $trend = [];
        $queryClone = clone $query;
        
        // Use a simple loop for the 7-day sparklines
        for ($i = 0; $i <= 6; $i++) {
            $date = $startDate->copy()->addDays($i);
            $q = clone $queryClone;
            $trend[] = $q->whereDate('created_at', $date->format('Y-m-d'))->count();
        }
        
        // If all zeroes, provide a flatline so the chart still renders nicely
        if (array_sum($trend) === 0) {
            return [0, 0, 0, 0, 0, 0, 0];
        }
        
        return $trend;
    }
    
    public static function canView(): bool
    {
        return auth()->user()?->hasRole(config('audit.super_admin_role', 'super_admin')) ?? false;
    }
}
