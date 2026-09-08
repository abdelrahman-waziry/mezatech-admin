<?php

namespace App\Http\Middleware;

use App\Models\Vendor;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Guards\TokenGuard;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates the Passport client_credentials bearer token and binds the
 * owning Vendor into the container for VendorScope to pick up.
 */
class ResolveVendorFromClient
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var TokenGuard $guard */
        $guard = Auth::guard('api');
        $client = $guard->client();

        if (! $client) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $vendor = $client->owner;

        if (! $vendor instanceof Vendor) {
            return response()->json(['message' => 'This client is not associated with a vendor.'], 401);
        }

        if (! $vendor->isActive()) {
            return response()->json(['message' => 'Vendor account is suspended.'], 403);
        }

        app()->instance(Vendor::class, $vendor);

        return $next($request);
    }
}
