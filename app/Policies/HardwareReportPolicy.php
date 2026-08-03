<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\HardwareReport;
use Illuminate\Auth\Access\HandlesAuthorization;

class HardwareReportPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:HardwareReport');
    }

    public function view(AuthUser $authUser, HardwareReport $hardwareReport): bool
    {
        return $authUser->can('View:HardwareReport');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:HardwareReport');
    }

    public function update(AuthUser $authUser, HardwareReport $hardwareReport): bool
    {
        return $authUser->can('Update:HardwareReport');
    }

    public function delete(AuthUser $authUser, HardwareReport $hardwareReport): bool
    {
        return $authUser->can('Delete:HardwareReport');
    }

    public function restore(AuthUser $authUser, HardwareReport $hardwareReport): bool
    {
        return $authUser->can('Restore:HardwareReport');
    }

    public function forceDelete(AuthUser $authUser, HardwareReport $hardwareReport): bool
    {
        return $authUser->can('ForceDelete:HardwareReport');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:HardwareReport');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:HardwareReport');
    }

    public function replicate(AuthUser $authUser, HardwareReport $hardwareReport): bool
    {
        return $authUser->can('Replicate:HardwareReport');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:HardwareReport');
    }

}