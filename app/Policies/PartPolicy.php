<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Part;
use Illuminate\Auth\Access\HandlesAuthorization;

class PartPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Part');
    }

    public function view(AuthUser $authUser, Part $part): bool
    {
        return $authUser->can('View:Part');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Part');
    }

    public function update(AuthUser $authUser, Part $part): bool
    {
        return $authUser->can('Update:Part');
    }

    public function delete(AuthUser $authUser, Part $part): bool
    {
        return $authUser->can('Delete:Part');
    }

    public function restore(AuthUser $authUser, Part $part): bool
    {
        return $authUser->can('Restore:Part');
    }

    public function forceDelete(AuthUser $authUser, Part $part): bool
    {
        return $authUser->can('ForceDelete:Part');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Part');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Part');
    }

    public function replicate(AuthUser $authUser, Part $part): bool
    {
        return $authUser->can('Replicate:Part');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Part');
    }

}