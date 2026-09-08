<?php

namespace App\Support;

use App\Models\Vendor;

/**
 * Resolves the vendor bound to the container by ResolveVendorFromClient for the current request.
 */
class VendorContext
{
    public static function current(): ?Vendor
    {
        return app()->bound(Vendor::class) ? app(Vendor::class) : null;
    }
}
