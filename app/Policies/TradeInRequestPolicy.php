<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TradeInRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class TradeInRequestPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TradeInRequest');
    }

    public function view(AuthUser $authUser, TradeInRequest $tradeInRequest): bool
    {
        return $authUser->can('View:TradeInRequest');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TradeInRequest');
    }

    public function update(AuthUser $authUser, TradeInRequest $tradeInRequest): bool
    {
        return $authUser->can('Update:TradeInRequest');
    }

    public function delete(AuthUser $authUser, TradeInRequest $tradeInRequest): bool
    {
        return $authUser->can('Delete:TradeInRequest');
    }

    public function restore(AuthUser $authUser, TradeInRequest $tradeInRequest): bool
    {
        return $authUser->can('Restore:TradeInRequest');
    }

    public function forceDelete(AuthUser $authUser, TradeInRequest $tradeInRequest): bool
    {
        return $authUser->can('ForceDelete:TradeInRequest');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TradeInRequest');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TradeInRequest');
    }

    public function replicate(AuthUser $authUser, TradeInRequest $tradeInRequest): bool
    {
        return $authUser->can('Replicate:TradeInRequest');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TradeInRequest');
    }

}