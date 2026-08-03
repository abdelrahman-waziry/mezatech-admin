<?php

namespace App\Enums\Audit;

enum AuditAction: string
{
    // Authentication
    case LOGIN = 'login';
    case LOGOUT = 'logout';
    case FAILED_LOGIN = 'failed_login';
    case PASSWORD_RESET = 'password_reset';
    case PASSWORD_CHANGE = 'password_change';
    case MFA_VERIFICATION = 'mfa_verification';
    case ACCOUNT_LOCK = 'account_lock';
    case SESSION_EXPIRED = 'session_expired';

    // User Management
    case CREATE_USER = 'create_user';
    case EDIT_USER = 'edit_user';
    case DELETE_USER = 'delete_user';
    case ENABLE_USER = 'enable_user';
    case DISABLE_USER = 'disable_user';
    case CHANGE_ROLE = 'change_role';
    case RESET_USER_PASSWORD = 'reset_user_password';

    // Pricing
    case VIEW_PRICES = 'view_prices';
    case SEARCH_PRICES = 'search_prices';
    case FILTER_PRICES = 'filter_prices';
    case EXPORT_PRICES = 'export_prices';
    case DOWNLOAD_PRICING_SHEET = 'download_pricing_sheet';
    case IMPORT_PRICING_SHEET = 'import_pricing_sheet';
    case UPLOAD_PRICING_SHEET = 'upload_pricing_sheet';
    case CREATE_PRICE = 'create_price';
    case UPDATE_PRICE = 'update_price';
    case DELETE_PRICE = 'delete_price';

    // Dashboard Activity
    case VIEW_DASHBOARD = 'view_dashboard';
    case VIEW_REPORT = 'view_report';
    case VIEW_ANALYTICS = 'view_analytics';
    case EXPORT_REPORT = 'export_report';
    case VIEW_CUSTOMER_INFO = 'view_customer_info';
    case VIEW_PRODUCT_INFO = 'view_product_info';

    // Administration
    case SETTINGS_CHANGE = 'settings_change';
    case CONFIGURATION_UPDATE = 'configuration_update';
    case PERMISSION_CHANGE = 'permission_change';
    case FEATURE_TOGGLE = 'feature_toggle';
    case API_KEY_CHANGE = 'api_key_change';
    case SECURITY_SETTINGS_CHANGE = 'security_settings_change';

    // File Operations
    case UPLOAD_FILE = 'upload_file';
    case DOWNLOAD_FILE = 'download_file';
    case DELETE_FILE = 'delete_file';
    case REPLACE_FILE = 'replace_file';
    case EXPORT_FILE = 'export_file';
    case IMPORT_FILE = 'import_file';

    // API Activity
    case API_REQUEST = 'api_request';

    // Security
    case UNAUTHORIZED_ACCESS = 'unauthorized_access';
    case PRIVILEGE_ESCALATION = 'privilege_escalation';

    // Audit Log Management
    case VIEW_AUDIT_LOGS = 'view_audit_logs';
    case EXPORT_AUDIT_LOGS = 'export_audit_logs';
    case DELETE_AUDIT_LOG = 'delete_audit_log';
    case PRUNE_AUDIT_LOGS = 'prune_audit_logs';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::LOGIN => 'Login',
            self::LOGOUT => 'Logout',
            self::FAILED_LOGIN => 'Failed Login',
            self::PASSWORD_RESET => 'Password Reset',
            self::PASSWORD_CHANGE => 'Password Change',
            self::MFA_VERIFICATION => 'MFA Verification',
            self::ACCOUNT_LOCK => 'Account Locked',
            self::SESSION_EXPIRED => 'Session Expired',
            self::CREATE_USER => 'Create User',
            self::EDIT_USER => 'Edit User',
            self::DELETE_USER => 'Delete User',
            self::ENABLE_USER => 'Enable User',
            self::DISABLE_USER => 'Disable User',
            self::CHANGE_ROLE => 'Change Role',
            self::RESET_USER_PASSWORD => 'Reset User Password',
            self::VIEW_PRICES => 'View Prices',
            self::SEARCH_PRICES => 'Search Prices',
            self::FILTER_PRICES => 'Filter Prices',
            self::EXPORT_PRICES => 'Export Prices',
            self::DOWNLOAD_PRICING_SHEET => 'Download Pricing Sheet',
            self::IMPORT_PRICING_SHEET => 'Import Pricing Sheet',
            self::UPLOAD_PRICING_SHEET => 'Upload Pricing Sheet',
            self::CREATE_PRICE => 'Create Price',
            self::UPDATE_PRICE => 'Update Price',
            self::DELETE_PRICE => 'Delete Price',
            self::VIEW_DASHBOARD => 'View Dashboard',
            self::VIEW_REPORT => 'View Report',
            self::VIEW_ANALYTICS => 'View Analytics',
            self::EXPORT_REPORT => 'Export Report',
            self::VIEW_CUSTOMER_INFO => 'View Customer Info',
            self::VIEW_PRODUCT_INFO => 'View Product Info',
            self::SETTINGS_CHANGE => 'Settings Change',
            self::CONFIGURATION_UPDATE => 'Configuration Update',
            self::PERMISSION_CHANGE => 'Permission Change',
            self::FEATURE_TOGGLE => 'Feature Toggle',
            self::API_KEY_CHANGE => 'API Key Change',
            self::SECURITY_SETTINGS_CHANGE => 'Security Settings Change',
            self::UPLOAD_FILE => 'Upload File',
            self::DOWNLOAD_FILE => 'Download File',
            self::DELETE_FILE => 'Delete File',
            self::REPLACE_FILE => 'Replace File',
            self::EXPORT_FILE => 'Export File',
            self::IMPORT_FILE => 'Import File',
            self::API_REQUEST => 'API Request',
            self::UNAUTHORIZED_ACCESS => 'Unauthorized Access',
            self::PRIVILEGE_ESCALATION => 'Privilege Escalation',
            self::VIEW_AUDIT_LOGS => 'View Audit Logs',
            self::EXPORT_AUDIT_LOGS => 'Export Audit Logs',
            self::DELETE_AUDIT_LOG => 'Delete Audit Log',
            self::PRUNE_AUDIT_LOGS => 'Prune Audit Logs',
        };
    }

    /**
     * Get the category this action belongs to.
     */
    public function category(): AuditCategory
    {
        return match ($this) {
            self::LOGIN, self::LOGOUT, self::FAILED_LOGIN,
            self::PASSWORD_RESET, self::PASSWORD_CHANGE,
            self::MFA_VERIFICATION, self::ACCOUNT_LOCK,
            self::SESSION_EXPIRED => AuditCategory::AUTHENTICATION,

            self::CREATE_USER, self::EDIT_USER, self::DELETE_USER,
            self::ENABLE_USER, self::DISABLE_USER, self::CHANGE_ROLE,
            self::RESET_USER_PASSWORD => AuditCategory::USER_MANAGEMENT,

            self::VIEW_PRICES, self::SEARCH_PRICES, self::FILTER_PRICES,
            self::EXPORT_PRICES, self::DOWNLOAD_PRICING_SHEET,
            self::IMPORT_PRICING_SHEET, self::UPLOAD_PRICING_SHEET,
            self::CREATE_PRICE, self::UPDATE_PRICE,
            self::DELETE_PRICE => AuditCategory::PRICING,

            self::VIEW_DASHBOARD, self::VIEW_REPORT, self::VIEW_ANALYTICS,
            self::EXPORT_REPORT, self::VIEW_CUSTOMER_INFO,
            self::VIEW_PRODUCT_INFO => AuditCategory::DASHBOARD,

            self::SETTINGS_CHANGE, self::CONFIGURATION_UPDATE,
            self::PERMISSION_CHANGE, self::FEATURE_TOGGLE,
            self::API_KEY_CHANGE,
            self::SECURITY_SETTINGS_CHANGE => AuditCategory::ADMINISTRATION,

            self::UPLOAD_FILE, self::DOWNLOAD_FILE, self::DELETE_FILE,
            self::REPLACE_FILE, self::EXPORT_FILE,
            self::IMPORT_FILE => AuditCategory::FILE_OPERATIONS,

            self::API_REQUEST => AuditCategory::API_ACTIVITY,

            self::UNAUTHORIZED_ACCESS, self::PRIVILEGE_ESCALATION,
            self::VIEW_AUDIT_LOGS, self::EXPORT_AUDIT_LOGS,
            self::DELETE_AUDIT_LOG,
            self::PRUNE_AUDIT_LOGS => AuditCategory::SECURITY,
        };
    }
}
