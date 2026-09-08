<?php

namespace App\Http\Middleware;

use App\Models\Vendor;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates the public embed key (used by the browser-embeddable widget, no OAuth
 * secret involved). Does NOT bind Vendor::class, so VendorScope stays a no-op and the
 * widget fetches the full catalog directly — per-vendor whitelisting is not enforced yet
 * for this path (temporary, see repo memory).
 */
class ResolveVendorFromEmbedKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-Client-Id') ?? $request->query('client_id');

        if (! $key) {
            return response()->json([
                'code' => 'missing_client_id',
                'message' => 'Missing client-id.',
            ], 401);
        }

        $vendor = Vendor::where('embed_key', $key)->first();

        if (! $vendor) {
            return response()->json([
                'code' => 'invalid_client_id',
                'message' => 'Invalid or expired client-id.',
            ], 401);
        }

        if (! $vendor->isActive()) {
            return response()->json([
                'code' => 'suspended_vendor',
                'message' => 'Vendor account is suspended.',
            ], 403);
        }

        $origin = $request->header('Origin') ?? $request->header('Referer');

        if (! $vendor->originAllowed($origin)) {
            return response()->json([
                'code' => 'origin_not_allowed',
                'message' => 'Origin not allowed for this client-id.',
            ], 403);
        }

        return $next($request);
    }
}
