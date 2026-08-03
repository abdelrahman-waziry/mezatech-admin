<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RepairPrice;
use Illuminate\Auth\Access\HandlesAuthorization;

class RepairPricePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RepairPrice');
    }

    public function view(AuthUser $authUser, RepairPrice $repairPrice): bool
    {
        return $authUser->can('View:RepairPrice');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RepairPrice');
    }

    public function update(AuthUser $authUser, RepairPrice $repairPrice): bool
    {
        return $authUser->can('Update:RepairPrice');
    }

    public function delete(AuthUser $authUser, RepairPrice $repairPrice): bool
    {
        return $authUser->can('Delete:RepairPrice');
    }

    public function restore(AuthUser $authUser, RepairPrice $repairPrice): bool
    {
        return $authUser->can('Restore:RepairPrice');
    }

    public function forceDelete(AuthUser $authUser, RepairPrice $repairPrice): bool
    {
        return $authUser->can('ForceDelete:RepairPrice');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RepairPrice');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RepairPrice');
    }

    public function replicate(AuthUser $authUser, RepairPrice $repairPrice): bool
    {
        return $authUser->can('Replicate:RepairPrice');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RepairPrice');
    }

}