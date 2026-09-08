<?php

namespace App\Models\Concerns;

use App\Models\Scopes\VendorScope;

/**
 * Models using this trait must implement vendorScopeColumn() and vendorAllowedIds().
 */
trait HasVendorScope
{
    protected static function bootHasVendorScope(): void
    {
        static::addGlobalScope(new VendorScope);
    }
}
