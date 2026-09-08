<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Part;
use App\Models\Product;
use App\Support\VendorContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group Vendor API - Parts
 */
class PartController extends Controller
{
    /**
     * List accessible parts/inventory
     *
     * Returns parts across all products this vendor has been granted access to.
     *
     * @authenticated
     *
     * @response 200 scenario="success" {
     *   "data": [
     *     {"id": 5, "name": "Screen", "price": 50, "product_id": 1}
     *   ]
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $vendor = VendorContext::current();
        $selectedProductId = $request->integer('productId') ?: null;

        // No vendor bound (e.g. the public embed-key route) -> fetch directly, unscoped.
        $productIds = $vendor
            ? DB::table('vendor_products')->where('vendor_id', $vendor->id)->pluck('product_id')
            : Product::all(['id'])->pluck('id');

        if ($selectedProductId !== null) {
            $productIds = $productIds->filter(fn ($productId) => (int) $productId === $selectedProductId)->values();
        }

        $parts = collect();

        foreach ($productIds as $productId) {
            Part::$currentProductId = $productId;
            $parts = $parts->merge(Part::all(['id', 'name', 'price', 'type', 'condition', 'product_id']));
        }

        return response()->json([
            'data' => $parts->values(),
        ]);
    }
}
