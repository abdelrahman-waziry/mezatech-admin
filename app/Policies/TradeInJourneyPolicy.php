<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TradeInJourney;
use Illuminate\Auth\Access\HandlesAuthorization;

class TradeInJourneyPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TradeInJourney');
    }

    public function view(AuthUser $authUser, TradeInJourney $tradeInJourney): bool
    {
        return $authUser->can('View:TradeInJourney');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TradeInJourney');
    }

    public function update(AuthUser $authUser, TradeInJourney $tradeInJourney): bool
    {
        return $authUser->can('Update:TradeInJourney');
    }

    public function delete(AuthUser $authUser, TradeInJourney $tradeInJourney): bool
    {
        return $authUser->can('Delete:TradeInJourney');
    }

    public function restore(AuthUser $authUser, TradeInJourney $tradeInJourney): bool
    {
        return $authUser->can('Restore:TradeInJourney');
    }

    public function forceDelete(AuthUser $authUser, TradeInJourney $tradeInJourney): bool
    {
        return $authUser->can('ForceDelete:TradeInJourney');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TradeInJourney');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TradeInJourney');
    }

    public function replicate(AuthUser $authUser, TradeInJourney $tradeInJourney): bool
    {
        return $authUser->can('Replicate:TradeInJourney');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TradeInJourney');
    }

}