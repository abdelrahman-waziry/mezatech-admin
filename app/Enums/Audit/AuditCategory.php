<?php

namespace App\Enums\Audit;

enum AuditCategory: string
{
    case AUTHENTICATION = 'authentication';
    case USER_MANAGEMENT = 'user_management';
    case PRICING = 'pricing';
    case DASHBOARD = 'dashboard';
    case ADMINISTRATION = 'administration';
    case FILE_OPERATIONS = 'file_operations';
    case API_ACTIVITY = 'api_activity';
    case SECURITY = 'security';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::AUTHENTICATION => 'Authentication',
            self::USER_MANAGEMENT => 'User Management',
            self::PRICING => 'Pricing',
            self::DASHBOARD => 'Dashboard',
            self::ADMINISTRATION => 'Administration',
            self::FILE_OPERATIONS => 'File Operations',
            self::API_ACTIVITY => 'API Activity',
            self::SECURITY => 'Security',
        };
    }

    /**
     * Get the icon for display.
     */
    public function icon(): string
    {
        return match ($this) {
            self::AUTHENTICATION => 'heroicon-o-key',
            self::USER_MANAGEMENT => 'heroicon-o-users',
            self::PRICING => 'heroicon-o-currency-dollar',
            self::DASHBOARD => 'heroicon-o-chart-bar',
            self::ADMINISTRATION => 'heroicon-o-cog-6-tooth',
            self::FILE_OPERATIONS => 'heroicon-o-document',
            self::API_ACTIVITY => 'heroicon-o-server',
            self::SECURITY => 'heroicon-o-shield-exclamation',
        };
    }

    /**
     * Get the color for badges.
     */
    public function color(): string
    {
        return match ($this) {
            self::AUTHENTICATION => 'info',
            self::USER_MANAGEMENT => 'primary',
            self::PRICING => 'success',
            self::DASHBOARD => 'gray',
            self::ADMINISTRATION => 'warning',
            self::FILE_OPERATIONS => 'primary',
            self::API_ACTIVITY => 'gray',
            self::SECURITY => 'danger',
        };
    }
}
