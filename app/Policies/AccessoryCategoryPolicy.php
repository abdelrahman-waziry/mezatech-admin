<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AccessoryCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccessoryCategoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AccessoryCategory');
    }

    public function view(AuthUser $authUser, AccessoryCategory $accessoryCategory): bool
    {
        return $authUser->can('View:AccessoryCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AccessoryCategory');
    }

    public function update(AuthUser $authUser, AccessoryCategory $accessoryCategory): bool
    {
        return $authUser->can('Update:AccessoryCategory');
    }

    public function delete(AuthUser $authUser, AccessoryCategory $accessoryCategory): bool
    {
        return $authUser->can('Delete:AccessoryCategory');
    }

    public function restore(AuthUser $authUser, AccessoryCategory $accessoryCategory): bool
    {
        return $authUser->can('Restore:AccessoryCategory');
    }

    public function forceDelete(AuthUser $authUser, AccessoryCategory $accessoryCategory): bool
    {
        return $authUser->can('ForceDelete:AccessoryCategory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AccessoryCategory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AccessoryCategory');
    }

    public function replicate(AuthUser $authUser, AccessoryCategory $accessoryCategory): bool
    {
        return $authUser->can('Replicate:AccessoryCategory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AccessoryCategory');
    }

}