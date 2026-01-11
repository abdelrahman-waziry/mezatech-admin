<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AnalyticsEvent;
use Illuminate\Auth\Access\HandlesAuthorization;

class AnalyticsEventPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AnalyticsEvent');
    }

    public function view(AuthUser $authUser, AnalyticsEvent $analyticsEvent): bool
    {
        return $authUser->can('View:AnalyticsEvent');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AnalyticsEvent');
    }

    public function update(AuthUser $authUser, AnalyticsEvent $analyticsEvent): bool
    {
        return $authUser->can('Update:AnalyticsEvent');
    }

    public function delete(AuthUser $authUser, AnalyticsEvent $analyticsEvent): bool
    {
        return $authUser->can('Delete:AnalyticsEvent');
    }

    public function restore(AuthUser $authUser, AnalyticsEvent $analyticsEvent): bool
    {
        return $authUser->can('Restore:AnalyticsEvent');
    }

    public function forceDelete(AuthUser $authUser, AnalyticsEvent $analyticsEvent): bool
    {
        return $authUser->can('ForceDelete:AnalyticsEvent');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AnalyticsEvent');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AnalyticsEvent');
    }

    public function replicate(AuthUser $authUser, AnalyticsEvent $analyticsEvent): bool
    {
        return $authUser->can('Replicate:AnalyticsEvent');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AnalyticsEvent');
    }

}