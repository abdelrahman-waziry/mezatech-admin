<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Condition;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConditionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Condition');
    }

    public function view(AuthUser $authUser, Condition $condition): bool
    {
        return $authUser->can('View:Condition');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Condition');
    }

    public function update(AuthUser $authUser, Condition $condition): bool
    {
        return $authUser->can('Update:Condition');
    }

    public function delete(AuthUser $authUser, Condition $condition): bool
    {
        return $authUser->can('Delete:Condition');
    }

    public function restore(AuthUser $authUser, Condition $condition): bool
    {
        return $authUser->can('Restore:Condition');
    }

    public function forceDelete(AuthUser $authUser, Condition $condition): bool
    {
        return $authUser->can('ForceDelete:Condition');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Condition');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Condition');
    }

    public function replicate(AuthUser $authUser, Condition $condition): bool
    {
        return $authUser->can('Replicate:Condition');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Condition');
    }

}