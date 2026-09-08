<?php

namespace App\Models\Scopes;

use App\Support\VendorContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Restricts query results to records whitelisted for the currently bound vendor.
 *
 * No-op when no vendor is bound (e.g. admin/Filament/internal usage), so this
 * scope only takes effect on requests that went through ResolveVendorFromClient.
 */
class VendorScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $vendor = VendorContext::current();

        if (! $vendor) {
            return;
        }

        $ids = $model->vendorAllowedIds($vendor);

        $builder->whereIn($model->vendorScopeColumn(), $ids);
    }
}
