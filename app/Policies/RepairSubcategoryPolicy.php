<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RepairSubcategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class RepairSubcategoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RepairSubcategory');
    }

    public function view(AuthUser $authUser, RepairSubcategory $repairSubcategory): bool
    {
        return $authUser->can('View:RepairSubcategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RepairSubcategory');
    }

    public function update(AuthUser $authUser, RepairSubcategory $repairSubcategory): bool
    {
        return $authUser->can('Update:RepairSubcategory');
    }

    public function delete(AuthUser $authUser, RepairSubcategory $repairSubcategory): bool
    {
        return $authUser->can('Delete:RepairSubcategory');
    }

    public function restore(AuthUser $authUser, RepairSubcategory $repairSubcategory): bool
    {
        return $authUser->can('Restore:RepairSubcategory');
    }

    public function forceDelete(AuthUser $authUser, RepairSubcategory $repairSubcategory): bool
    {
        return $authUser->can('ForceDelete:RepairSubcategory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RepairSubcategory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RepairSubcategory');
    }

    public function replicate(AuthUser $authUser, RepairSubcategory $repairSubcategory): bool
    {
        return $authUser->can('Replicate:RepairSubcategory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RepairSubcategory');
    }

}