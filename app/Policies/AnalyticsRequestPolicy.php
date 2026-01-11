<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AnalyticsRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class AnalyticsRequestPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AnalyticsRequest');
    }

    public function view(AuthUser $authUser, AnalyticsRequest $analyticsRequest): bool
    {
        return $authUser->can('View:AnalyticsRequest');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AnalyticsRequest');
    }

    public function update(AuthUser $authUser, AnalyticsRequest $analyticsRequest): bool
    {
        return $authUser->can('Update:AnalyticsRequest');
    }

    public function delete(AuthUser $authUser, AnalyticsRequest $analyticsRequest): bool
    {
        return $authUser->can('Delete:AnalyticsRequest');
    }

    public function restore(AuthUser $authUser, AnalyticsRequest $analyticsRequest): bool
    {
        return $authUser->can('Restore:AnalyticsRequest');
    }

    public function forceDelete(AuthUser $authUser, AnalyticsRequest $analyticsRequest): bool
    {
        return $authUser->can('ForceDelete:AnalyticsRequest');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AnalyticsRequest');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AnalyticsRequest');
    }

    public function replicate(AuthUser $authUser, AnalyticsRequest $analyticsRequest): bool
    {
        return $authUser->can('Replicate:AnalyticsRequest');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AnalyticsRequest');
    }

}