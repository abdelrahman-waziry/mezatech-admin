<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

/**
 * @group Vendor API - Products
 */
class ProductController extends Controller
{
    /**
     * List products
     *
     * Returns the products this vendor has been granted access to.
     *
     * @authenticated
     *
     * @response 200 scenario="success" {
     *   "data": [
     *     {"id": 1, "name": "iPhone 13", "sku": "IP13", "condition": 1}
     *   ]
     * }
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Product::with('brand')->get(['id', 'name', 'sku', 'brand_id', 'condition']),
        ]);
    }
}
