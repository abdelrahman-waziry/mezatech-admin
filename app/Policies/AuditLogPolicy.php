<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AuditLogPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the given user can view any audit logs.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(config('audit.super_admin_role', 'super_admin'));
    }

    /**
     * Determine if the given user can view the given audit log.
     */
    public function view(User $user, AuditLog $auditLog): bool
    {
        return $user->hasRole(config('audit.super_admin_role', 'super_admin'));
    }

    /**
     * Determine if the given user can create audit logs.
     * (System-only operation, never via UI)
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine if the given user can update the given audit log.
     * (Audit logs are immutable)
     */
    public function update(User $user, AuditLog $auditLog): bool
    {
        return false;
    }

    /**
     * Determine if the given user can delete the given audit log.
     */
    public function delete(User $user, AuditLog $auditLog): bool
    {
        return $user->hasRole(config('audit.super_admin_role', 'super_admin'));
    }

    /**
     * Determine if the given user can restore the given audit log.
     */
    public function restore(User $user, AuditLog $auditLog): bool
    {
        return false;
    }

    /**
     * Determine if the given user can permanently delete the given audit log.
     */
    public function forceDelete(User $user, AuditLog $auditLog): bool
    {
        return $user->hasRole(config('audit.super_admin_role', 'super_admin'));
    }
}
