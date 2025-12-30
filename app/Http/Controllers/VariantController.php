<?php

namespace App\Http\Controllers;

use App\Models\Variant;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VariantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Variant::with(['product', 'variantFeatures.feature', 'variantFeatures.featureValue']);

        // Filter by productId
        if ($request->has('productId')) {
            $query->where('product_id', $request->productId);
        }

        $variants = $query->get();

        return response()->json([
            'data' => $variants,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'buyingPrice' => 'nullable|numeric|min:0',
            'priceBeforeDiscount' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'priceAfterDiscount' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'variantFeatures' => 'nullable|array',
            'variantFeatures.*.feature.id' => 'required|integer',
            'variantFeatures.*.featureValue.id' => 'required|integer',
            'product.id' => 'required|exists:products,id',
        ]);

        DB::beginTransaction();
        try {
            $variant = Variant::create([
                'name' => $validated['name'],
                'buying_price' => $validated['buyingPrice'] ?? null,
                'price_before_discount' => $validated['priceBeforeDiscount'] ?? null,
                'discount' => $validated['discount'] ?? null,
                'price_after_discount' => $validated['priceAfterDiscount'] ?? null,
                'stock' => $validated['stock'] ?? 0,
                'product_id' => $validated['product']['id'],
            ]);

            // Create variant features
            if (isset($validated['variantFeatures'])) {
                foreach ($validated['variantFeatures'] as $vf) {
                    $variant->variantFeatures()->create([
                        'feature_id' => $vf['feature']['id'],
                        'feature_value_id' => $vf['featureValue']['id'],
                    ]);
                }
            }

            DB::commit();

            $variant->load(['product', 'variantFeatures.feature', 'variantFeatures.featureValue']);

            return response()->json([
                'message' => 'Variant created successfully',
                'data' => $variant,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create variant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Variant $variant): JsonResponse
    {
        $variant->load(['product', 'variantFeatures.feature', 'variantFeatures.featureValue']);

        return response()->json([
            'data' => $variant,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Variant $variant): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'buyingPrice' => 'nullable|numeric|min:0',
            'priceBeforeDiscount' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'priceAfterDiscount' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'variantFeatures' => 'nullable|array',
            'variantFeatures.*.feature.id' => 'required|exists:features,id',
            'variantFeatures.*.featureValue.id' => 'required|exists:feature_values,id',
            'product.id' => 'sometimes|required|exists:products,id',
        ]);

        DB::beginTransaction();
        try {
            $updateData = [];
            if (isset($validated['name'])) $updateData['name'] = $validated['name'];
            if (isset($validated['buyingPrice'])) $updateData['buying_price'] = $validated['buyingPrice'];
            if (isset($validated['priceBeforeDiscount'])) $updateData['price_before_discount'] = $validated['priceBeforeDiscount'];
            if (isset($validated['discount'])) $updateData['discount'] = $validated['discount'];
            if (isset($validated['priceAfterDiscount'])) $updateData['price_after_discount'] = $validated['priceAfterDiscount'];
            if (isset($validated['stock'])) $updateData['stock'] = $validated['stock'];
            if (isset($validated['product']['id'])) $updateData['product_id'] = $validated['product']['id'];

            $variant->update($updateData);

            // Sync variant features
            if (isset($validated['variantFeatures'])) {
                $variant->variantFeatures()->delete();
                foreach ($validated['variantFeatures'] as $vf) {
                    $variant->variantFeatures()->create([
                        'feature_id' => $vf['feature']['id'],
                        'feature_value_id' => $vf['featureValue']['id'],
                    ]);
                }
            }

            DB::commit();

            $variant->load(['product', 'variantFeatures.feature', 'variantFeatures.featureValue']);

            return response()->json([
                'message' => 'Variant updated successfully',
                'data' => $variant->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update variant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Variant $variant): JsonResponse
    {
        $variant->delete();

        return response()->json([
            'message' => 'Variant deleted successfully',
        ]);
    }
}

