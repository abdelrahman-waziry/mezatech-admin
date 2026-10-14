<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\JsonResponse;

/**
 * @group Vendor API - Variants
 */
class VariantController extends Controller
{
    /**
     * List variants for a product
     *
     * Returns the variants for the given product, provided the product is within
     * this vendor's whitelisted catalog. Returns 404 if not accessible.
     *
     * @authenticated
     *
     * @urlParam product int required The product ID. Example: 1
     *
     * @response 200 scenario="success" {
     *   "data": [
     *     {"id": 10, "name": "128GB", "buying_price": 100, "price_after_discount": 90, "stock": 5}
     *   ]
     * }
     */
    public function index(Product $product): JsonResponse
    {
        return response()->json([
            'data' => Variant::where('product_id', $product->id)
                ->get(['id', 'name', 'buying_price', 'price_before_discount', 'discount', 'price_after_discount', 'stock', 'product_id']),
        ]);
    }
}
