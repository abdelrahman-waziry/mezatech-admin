<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CosmeticReport;
use Illuminate\Auth\Access\HandlesAuthorization;

class CosmeticReportPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CosmeticReport');
    }

    public function view(AuthUser $authUser, CosmeticReport $cosmeticReport): bool
    {
        return $authUser->can('View:CosmeticReport');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CosmeticReport');
    }

    public function update(AuthUser $authUser, CosmeticReport $cosmeticReport): bool
    {
        return $authUser->can('Update:CosmeticReport');
    }

    public function delete(AuthUser $authUser, CosmeticReport $cosmeticReport): bool
    {
        return $authUser->can('Delete:CosmeticReport');
    }

    public function restore(AuthUser $authUser, CosmeticReport $cosmeticReport): bool
    {
        return $authUser->can('Restore:CosmeticReport');
    }

    public function forceDelete(AuthUser $authUser, CosmeticReport $cosmeticReport): bool
    {
        return $authUser->can('ForceDelete:CosmeticReport');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CosmeticReport');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CosmeticReport');
    }

    public function replicate(AuthUser $authUser, CosmeticReport $cosmeticReport): bool
    {
        return $authUser->can('Replicate:CosmeticReport');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CosmeticReport');
    }

}