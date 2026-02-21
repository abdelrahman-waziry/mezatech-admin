<?php

namespace App\Models;

use App\Services\ApiTokenService;
use App\Services\VariantService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Sushi\Sushi;

class Variant extends Model
{
    use Sushi;

    protected $table = 'variants';

    /**
     * Static property to store the product ID filter for the current request
     * This is set by ListVariants before querying
     */
    public static ?int $currentProductId = null;
    
    // Static cache for request-scoped caching
    protected static array $requestCache = [];

    /**
     * Clear the request cache - useful for pagination/filter changes
     */
    public static function clearRequestCache(): void
    {
        static::$requestCache = [];
    }

    /**
     * Get the product ID filter - check multiple sources
     * Priority: static property > URL query > session > request input
     */
    protected function getFilterProductId(): ?int
    {
        // Check static property first (set by ListVariants or EditVariant)
        if (static::$currentProductId !== null) {
            // Also store in session for AJAX request persistence
            session(['variant_edit_product_id' => static::$currentProductId]);
            return static::$currentProductId;
        }

        // Check URL query parameter (passed from listing page to edit page)
        $productId = request()->query('product_id');
        if ($productId) {
            static::$currentProductId = (int) $productId;
            session(['variant_edit_product_id' => (int) $productId]);
            return (int) $productId;
        }

        // Check session (for AJAX requests that lose the static property)
        $sessionProductId = session('variant_edit_product_id');
        if ($sessionProductId) {
            static::$currentProductId = (int) $sessionProductId;
            return (int) $sessionProductId;
        }

        // Fallback to request input (for table filters)
        $productId = request()->query('tableFilters.product_id.value')
            ?? request()->query('filters.product_id');

        if (!$productId) {
            $tableFilters = request()->input('tableFilters', []);
            $productId = $tableFilters['product_id']['value'] ?? null;
        }

        if (!$productId) {
            $filters = request()->input('filters', []);
            $productId = $filters['product_id'] ?? null;
        }

        if ($productId) {
            static::$currentProductId = (int) $productId;
        }

        return $productId ? (int) $productId : null;
    }

    /**
     * FIXED: Bypass Sushi for Edit Page lookups.
     * When editing a record (e.g. /variants/123/edit), there are no filters,
     * so getRows() returns empty. This manually fetches the single record.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        try {
            $service = new VariantService();
            // Fetch single variant by ID from API
            $data = $service->fetchOne($value);

            if (!$data) {
                abort(404);
            }

            // Map API data to Model attributes (Same logic as getRows)
            $productRef = $data['product'] ?? [];
            
            $attributes = [
                'id' => $data['id'] ?? $value,
                'name' => $data['name'] ?? 'Unnamed',
                'buying_price' => (float) ($data['buyingPrice'] ?? $data['buying_price'] ?? 0),
                'price_before_discount' => (float) ($data['priceBeforeDiscount'] ?? $data['price_before_discount'] ?? 0),
                'discount' => (float) ($data['discount'] ?? 0),
                'price_after_discount' => (float) ($data['priceAfterDiscount'] ?? $data['price_after_discount'] ?? 0),
                'stock' => (int) ($data['stock'] ?? 0),
                'product_id' => $productRef['id'] ?? $data['productId'] ?? null,
                'product_name' => $productRef['name'] ?? 'Unknown Product',
                'created_at' => $this->normalizeDate($data['createdAt'] ?? $data['created_at'] ?? now()),
                'updated_at' => $this->normalizeDate($data['updatedAt'] ?? $data['updated_at'] ?? now()),
            ];

            // Create instance and mark as existing so Filament knows it's an Update
            $variant = new static();
            $variant->forceFill($attributes);
            $variant->exists = true;

            return $variant;

        } catch (\Exception $e) {
            Log::error('ResolveRouteBinding failed for Variant: ' . $e->getMessage());
            abort(404);
        }
    }

    protected $fillable = [
        'id',
        'name',
        'buying_price',
        'price_before_discount',
        'discount',
        'price_after_discount',
        'stock',
        'product_id',
        'product_name',
        'created_at',
        'updated_at',
    ];

    protected $schema = [
        'id' => 'integer',
        'name' => 'string',
        'buying_price' => 'float',
        'price_before_discount' => 'float',
        'discount' => 'float',
        'price_after_discount' => 'float',
        'stock' => 'integer',
        'product_id' => 'integer',
        'product_name' => 'string',
        'created_at' => 'string',
        'updated_at' => 'string',
    ];

    protected $casts = [
        'id' => 'integer',
        'buying_price' => 'float',
        'price_before_discount' => 'float',
        'discount' => 'float',
        'price_after_discount' => 'float',
        'stock' => 'integer',
        'product_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getRows(): array
    {
        try {
            // According to openapi.yaml, /v1/variants requires productId as a query parameter
            // If a product filter is set, only fetch variants for that product
            // Otherwise, return empty array (user must select a product)
            $productId = $this->getFilterProductId();
            
            if (!$productId) {
                // No product selected - return empty array
                // User must select a product from the dropdown filter
                return [];
            }

            $cacheKey = "variants_data_product_{$productId}";
            
            // Smart Caching: Use static property to cache within the request only
            // This ensures fresh data on new requests (like after redirect) but avoids duplicate calls in same request
            if (isset(static::$requestCache[$cacheKey])) {
                return static::$requestCache[$cacheKey];
            }
            
            // Fetch variants for the selected product only
            $token = app(ApiTokenService::class)->getToken();
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->timeout(30)->get('https://bestrepairegypt.com/v1/variants', [
                'productId' => $productId,
            ]);

            if (!$response->successful()) {
                Log::error('Variants API failed for product', ['productId' => $productId, 'status' => $response->status()]);
                return [];
            }

            $data = $response->json();
            
            // Extract variants from response according to openapi.yaml VariantsResponse schema
            // VariantsResponse has: { variants: VariantDTO[] }
            $variants = [];
            if (isset($data['variants']) && is_array($data['variants'])) {
                $variants = $data['variants'];
            } elseif (is_array($data)) {
                // Check if it's a numeric array (direct array of variants)
                if (isset($data[0]) || empty($data)) {
                    $variants = $data;
                } else {
                    // Associative array with other keys - try to find variants
                    Log::warning('Variants API returned associative array', ['keys' => array_keys($data), 'productId' => $productId]);
                    // Try common keys
                    foreach (['data', 'items', 'results'] as $key) {
                        if (isset($data[$key]) && is_array($data[$key])) {
                            $variants = $data[$key];
                            break;
                        }
                    }
                }
            }

            if (empty($variants)) {
                Log::warning('No variants found in API response', ['data_structure' => is_array($data) ? array_keys($data) : gettype($data), 'productId' => $productId]);
                return [];
            }

            Log::info('Variants extracted from API response', ['count' => count($variants), 'productId' => $productId]);

            // Also fetch product details to get product name
            $productResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->timeout(30)->get('https://bestrepairegypt.com/v1/products/' . $productId);

            $productData = $productResponse->json() ?? [];
            $productName = $productData['name'] ?? 'Unknown Product';

            $allVariants = [];
            foreach ($variants as $variant) {
                $productRef = $variant['product'] ?? [];

                $allVariants[] = [
                    'id' => $variant['id'] ?? null,
                    'name' => $variant['name'] ?? 'Unnamed',
                    'buying_price' => (float) ($variant['buyingPrice'] ?? $variant['buying_price'] ?? 0),
                    'price_before_discount' => (float) ($variant['priceBeforeDiscount'] ?? $variant['price_before_discount'] ?? 0),
                    'discount' => (float) ($variant['discount'] ?? 0),
                    'price_after_discount' => (float) ($variant['priceAfterDiscount'] ?? $variant['price_after_discount'] ?? 0),
                    'stock' => (int) ($variant['stock'] ?? 0),
                    'product_id' => $productRef['id'] ?? $productId ?? null,
                    'product_name' => $productRef['name'] ?? $productName,
                    'created_at' => $this->normalizeDate($variant['createdAt'] ?? $variant['created_at'] ?? now()),
                    'updated_at' => $this->normalizeDate($variant['updatedAt'] ?? $variant['updated_at'] ?? now()),
                ];
            }


            Log::info('Variants mapped successfully', [
                'mapped_count' => count($allVariants),
                'original_count' => count($variants),
                'productId' => $productId
            ]);

            $result = array_filter($allVariants, fn ($row) => $row['id'] !== null);
            
            // Store in static cache
            static::$requestCache[$cacheKey] = $result;
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to fetch variants from API', ['error' => $e->getMessage()]);
            return [];
        }
    }


    /**
     * Fetch raw variant data from API including variantFeatures
     * This method is used when we need the full API response with nested data
     * (e.g., variantFeatures) that can't be stored in Sushi's SQLite cache
     */
    public static function fetchRawVariantsWithFeatures(?int $productId = null): array
    {
        try {
            if (!$productId) {
                return [];
            }

            $token = app(ApiTokenService::class)->getToken();
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->timeout(30)->get('https://bestrepairegypt.com/v1/variants', [
                'productId' => $productId,
            ]);

            if (!$response->successful()) {
                Log::error('Variants API failed for product', ['productId' => $productId, 'status' => $response->status()]);
                return [];
            }

            $data = $response->json();
            
            // Extract variants from response
            $variants = [];
            if (isset($data['variants']) && is_array($data['variants'])) {
                $variants = $data['variants'];
            } elseif (is_array($data)) {
                if (isset($data[0]) || empty($data)) {
                    $variants = $data;
                } else {
                    foreach (['data', 'items', 'results'] as $key) {
                        if (isset($data[$key]) && is_array($data[$key])) {
                            $variants = $data[$key];
                            break;
                        }
                    }
                }
            }

            return $variants;
        } catch (\Exception $e) {
            Log::error('Failed to fetch raw variants from API', ['error' => $e->getMessage()]);
            return [];
        }
    }

    protected function normalizeDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->toDateTimeString();
        }

        try {
            return Carbon::parse($value)->toDateTimeString();
        } catch (\Exception $e) {
            return now()->toDateTimeString();
        }
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    protected $keyType = 'int';

    /**
     * Make cache key unique per product ID
     * This ensures different product filters get different cache entries
     */
    public static function getSushiCacheKey(): string
    {
        $productId = static::$currentProductId ?? 'none';
        return static::class . '_variants_product_' . $productId;
    }

    public function save(array $options = [])
    {
        try {
            $service = new VariantService();

            if ($this->exists && $this->id) {
                $result = $service->update((string) $this->id, $this->attributesToArray());
            } else {
                $result = $service->create($this->attributesToArray());
            }

            if (isset($result['id'])) {
                $this->setAttribute('id', $result['id']);
                $this->exists = true;
            }

            Log::info('Variant saved via API', ['id' => $this->id ?? 'new']);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to save variant: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete(): bool
    {
        try {
            $service = new VariantService();

            // Clear variant features first via API to avoid FK constraint errors on the backend
            $service->clearVariantFeatures((string) $this->id);

            $service->delete((string) $this->id);
            

            Log::info('Variant deleted via API', ['id' => $this->id]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete variant: ' . $e->getMessage());
            throw $e;
        }
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variantFeatures()
    {
        return $this->hasMany(VariantFeature::class);
    }
}
