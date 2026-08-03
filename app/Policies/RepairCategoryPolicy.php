<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RepairCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class RepairCategoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RepairCategory');
    }

    public function view(AuthUser $authUser, RepairCategory $repairCategory): bool
    {
        return $authUser->can('View:RepairCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RepairCategory');
    }

    public function update(AuthUser $authUser, RepairCategory $repairCategory): bool
    {
        return $authUser->can('Update:RepairCategory');
    }

    public function delete(AuthUser $authUser, RepairCategory $repairCategory): bool
    {
        return $authUser->can('Delete:RepairCategory');
    }

    public function restore(AuthUser $authUser, RepairCategory $repairCategory): bool
    {
        return $authUser->can('Restore:RepairCategory');
    }

    public function forceDelete(AuthUser $authUser, RepairCategory $repairCategory): bool
    {
        return $authUser->can('ForceDelete:RepairCategory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RepairCategory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RepairCategory');
    }

    public function replicate(AuthUser $authUser, RepairCategory $repairCategory): bool
    {
        return $authUser->can('Replicate:RepairCategory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RepairCategory');
    }

}