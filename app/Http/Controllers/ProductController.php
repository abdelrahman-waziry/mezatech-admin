<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Brand;
use App\Models\Tag;
use App\Models\Customer;
use App\Models\Transaction;
use App\Services\PriceCalculationService;
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

        if ($request->has('maxPrice')) {
            $query->where('minimum_buying_price', '<=', $request->maxPrice);
        }

        if ($request->has('brands')) {
            $brandIds = is_array($request->brands)
                ? $request->brands
                : explode(',', $request->brands);

            $query->whereIn('brand_id', array_filter($brandIds, 'is_numeric'));
        }

        if ($request->has('condition')) {
            $query->where('condition', $request->condition);
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

        $result = DB::transaction(function () use ($validated): array {
            $calculation = app(PriceCalculationService::class)->calculate(
                (int) $validated['variantId'],
                $validated['partsWithValuesMap'],
                $validated['repairedPartsBefore'] ?? []
            );

            $customerData = $validated['customer'];
            $customer = Customer::firstOrCreate(
                [
                    'name' => $customerData['name'],
                    'phone_number' => $customerData['phoneNumber'],
                ],
                [
                    'email' => $customerData['email'],
                    'password' => str()->random(40),
                ],
            );

            Transaction::create([
                'customer_id' => $customer->id,
                'product_id' => $calculation['variant']->product_id,
                'price' => $calculation['finalPrice'],
            ]);

            return $calculation;
        });

        return response()->json(['price' => $result['finalPrice']]);
    }
}

