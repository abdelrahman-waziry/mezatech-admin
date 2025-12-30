<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Brand;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['brand', 'tags']);

        // Filter by name
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Filter by tags
        if ($request->has('tags')) {
            $tags = explode(',', $request->tags);
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('name', $tags);
            });
        }

        // Filter by minimum price
        if ($request->has('minPrice')) {
            $query->where('minimum_buying_price', '>=', $request->minPrice);
        }

        $products = $query->get();

        // $http = GuzzleHttp\Client();

        // $items = $http->request('GET', 'https://api.example.com/data');
        // dd($items);
        
        return response()->json([
            'data' => $products,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'condition' => 'nullable|integer',
            'notes' => 'nullable|string',
            'minimumBuyingPrice' => 'nullable|numeric|min:0',
            'wastePrice' => 'nullable|numeric|min:0',
            'tags' => 'nullable|array',
            'tags.*.id' => 'required|exists:tags,id',
            'brand.id' => 'required|exists:brands,id',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'condition' => $validated['condition'] ?? 0,
                'notes' => $validated['notes'] ?? null,
                'minimum_buying_price' => $validated['minimumBuyingPrice'] ?? null,
                'waste_price' => $validated['wastePrice'] ?? null,
                'brand_id' => $validated['brand']['id'],
            ]);

            // Attach tags
            if (isset($validated['tags'])) {
                $tagIds = collect($validated['tags'])->pluck('id')->toArray();
                $product->tags()->attach($tagIds);
            }

            DB::commit();

            $product->load(['brand', 'tags']);

            return response()->json([
                'message' => 'Product created successfully',
                'data' => $product,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): JsonResponse
    {
        $product->load(['brand', 'tags', 'variants', 'parts']);

        return response()->json([
            'data' => $product,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'condition' => 'nullable|integer',
            'notes' => 'nullable|string',
            'minimumBuyingPrice' => 'nullable|numeric|min:0',
            'wastePrice' => 'nullable|numeric|min:0',
            'tags' => 'nullable|array',
            'tags.*.id' => 'required|exists:tags,id',
            'brand.id' => 'sometimes|required|exists:brands,id',
        ]);

        DB::beginTransaction();
        try {
            $updateData = [];
            if (isset($validated['name'])) $updateData['name'] = $validated['name'];
            if (isset($validated['description'])) $updateData['description'] = $validated['description'];
            if (isset($validated['condition'])) $updateData['condition'] = $validated['condition'];
            if (isset($validated['notes'])) $updateData['notes'] = $validated['notes'];
            if (isset($validated['minimumBuyingPrice'])) $updateData['minimum_buying_price'] = $validated['minimumBuyingPrice'];
            if (isset($validated['wastePrice'])) $updateData['waste_price'] = $validated['wastePrice'];
            if (isset($validated['brand']['id'])) $updateData['brand_id'] = $validated['brand']['id'];

            $product->update($updateData);

            // Sync tags
            if (isset($validated['tags'])) {
                $tagIds = collect($validated['tags'])->pluck('id')->toArray();
                $product->tags()->sync($tagIds);
            }

            DB::commit();

            $product->load(['brand', 'tags']);

            return response()->json([
                'message' => 'Product updated successfully',
                'data' => $product->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }

    /**
     * Calculate price for a product variant.
     */
    public function calculatePrice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'variantId' => 'required|exists:variants,id',
            'customer' => 'required|array',
            'customer.name' => 'required|string',
            'customer.email' => 'required|email',
            'customer.phoneNumber' => 'required|string',
            'repairedPartsBefore' => 'nullable|array',
            'partsWithValuesMap' => 'required|array',
        ]);

        $variant = \App\Models\Variant::with(['product', 'product.parts'])->findOrFail($validated['variantId']);

        // Calculate price based on parts
        $totalPrice = $variant->price_after_discount ?? $variant->price_before_discount ?? 0;
        $partsTotal = 0;

        foreach ($validated['partsWithValuesMap'] as $partId => $value) {
            $part = $variant->product->parts->firstWhere('id', $partId);
            if ($part) {
                // If part has info JSON with prices, use the value to determine price
                if ($part->info && is_array($part->info)) {
                    $priceKey = 'price' . $value;
                    if (isset($part->info[$priceKey])) {
                        $partsTotal += $part->info[$priceKey];
                    } else {
                        $partsTotal += $part->price * $value;
                    }
                } else {
                    $partsTotal += $part->price * $value;
                }
            }
        }

        $finalPrice = $totalPrice + $partsTotal;

        return response()->json([
            'data' => [
                'variant' => $variant,
                'customer' => $validated['customer'],
                'partsTotal' => $partsTotal,
                'variantPrice' => $totalPrice,
                'finalPrice' => $finalPrice,
            ],
        ]);
    }
}

