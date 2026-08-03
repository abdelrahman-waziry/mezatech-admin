<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DiagnosticDevice;
use Illuminate\Auth\Access\HandlesAuthorization;

class DiagnosticDevicePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DiagnosticDevice');
    }

    public function view(AuthUser $authUser, DiagnosticDevice $diagnosticDevice): bool
    {
        return $authUser->can('View:DiagnosticDevice');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DiagnosticDevice');
    }

    public function update(AuthUser $authUser, DiagnosticDevice $diagnosticDevice): bool
    {
        return $authUser->can('Update:DiagnosticDevice');
    }

    public function delete(AuthUser $authUser, DiagnosticDevice $diagnosticDevice): bool
    {
        return $authUser->can('Delete:DiagnosticDevice');
    }

    public function restore(AuthUser $authUser, DiagnosticDevice $diagnosticDevice): bool
    {
        return $authUser->can('Restore:DiagnosticDevice');
    }

    public function forceDelete(AuthUser $authUser, DiagnosticDevice $diagnosticDevice): bool
    {
        return $authUser->can('ForceDelete:DiagnosticDevice');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DiagnosticDevice');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DiagnosticDevice');
    }

    public function replicate(AuthUser $authUser, DiagnosticDevice $diagnosticDevice): bool
    {
        return $authUser->can('Replicate:DiagnosticDevice');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DiagnosticDevice');
    }

}