<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\VariantFeature;
use Illuminate\Auth\Access\HandlesAuthorization;

class VariantFeaturePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:VariantFeature');
    }

    public function view(AuthUser $authUser, VariantFeature $variantFeature): bool
    {
        return $authUser->can('View:VariantFeature');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:VariantFeature');
    }

    public function update(AuthUser $authUser, VariantFeature $variantFeature): bool
    {
        return $authUser->can('Update:VariantFeature');
    }

    public function delete(AuthUser $authUser, VariantFeature $variantFeature): bool
    {
        return $authUser->can('Delete:VariantFeature');
    }

    public function restore(AuthUser $authUser, VariantFeature $variantFeature): bool
    {
        return $authUser->can('Restore:VariantFeature');
    }

    public function forceDelete(AuthUser $authUser, VariantFeature $variantFeature): bool
    {
        return $authUser->can('ForceDelete:VariantFeature');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:VariantFeature');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:VariantFeature');
    }

    public function replicate(AuthUser $authUser, VariantFeature $variantFeature): bool
    {
        return $authUser->can('Replicate:VariantFeature');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:VariantFeature');
    }

}