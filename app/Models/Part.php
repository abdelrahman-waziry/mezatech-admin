<?php

namespace App\Models;

use App\Services\ApiTokenService;
use App\Services\PartService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Sushi\Sushi;

class Part extends Model
{
    use Sushi;

    protected $table = 'parts';

    /**
     * Static property to store the product ID filter for the current request
     * This is set by ListParts before querying
     */
    public static ?int $currentProductId = null;

    /**
     * Get the product ID filter - check static property first, then request
     */
    protected function getFilterProductId(): ?int
    {
        // Check static property first (set by ListParts)
        if (static::$currentProductId !== null) {
            return static::$currentProductId;
        }

        // Fallback to request (for backwards compatibility)
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

        return $productId ? (int) $productId : null;
    }

    protected $fillable = [
        'id',
        'name',
        'price',
        'type',
        'condition',
        'info',
        'product_id',
        'product_name',
        'created_at',
        'updated_at',
    ];

    protected $schema = [
        'id' => 'integer',
        'name' => 'string',
        'price' => 'float',
        'type' => 'integer',
        'condition' => 'integer',
        'info' => 'text',
        'product_id' => 'integer',
        'product_name' => 'string',
        'created_at' => 'string',
        'updated_at' => 'string',
    ];

    protected $casts = [
        'id' => 'integer',
        'price' => 'float',
        'type' => 'integer',
        'condition' => 'integer',
        'info' => 'array',
        'product_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getRows(): array
    {
        try {
            // According to openapi.yaml, /v1/parts requires productId as a query parameter
            // If a product filter is set, only fetch parts for that product
            // Otherwise, return empty array (user must select a product)
            $productId = $this->getFilterProductId();
            
            if (!$productId) {
                // No product selected - return empty array
                // User must select a product from the dropdown filter
                return [];
            }

            // Fetch parts for the selected product only
            $token = app(ApiTokenService::class)->getToken();
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->timeout(30)->get('https://bestrepairegypt.com/v1/parts', [
                'productId' => $productId,
            ]);

            if (!$response->successful()) {
                Log::error('Parts API failed for product', ['productId' => $productId, 'status' => $response->status()]);
                return [];
            }

            $data = $response->json();
            
            // Extract parts from response according to openapi.yaml PartsResponse schema
            // PartsResponse has: { parts: PartDTO[] }
            $parts = [];
            if (isset($data['parts']) && is_array($data['parts'])) {
                $parts = $data['parts'];
            } elseif (is_array($data)) {
                // Check if it's a numeric array (direct array of parts)
                if (isset($data[0]) || empty($data)) {
                    $parts = $data;
                } else {
                    // Associative array with other keys - try to find parts
                    Log::warning('Parts API returned associative array', ['keys' => array_keys($data), 'productId' => $productId]);
                    // Try common keys
                    foreach (['data', 'items', 'results'] as $key) {
                        if (isset($data[$key]) && is_array($data[$key])) {
                            $parts = $data[$key];
                            break;
                        }
                    }
                }
            }

            if (empty($parts)) {
                Log::warning('No parts found in API response', ['data_structure' => is_array($data) ? array_keys($data) : gettype($data), 'productId' => $productId]);
                return [];
            }

            Log::info('Parts extracted from API response', ['count' => count($parts), 'productId' => $productId]);

            // Also fetch product details to get product name
            $productResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->timeout(30)->get('https://bestrepairegypt.com/v1/products/' . $productId);

            $productData = $productResponse->json() ?? [];
            $productName = $productData['name'] ?? 'Unknown Product';

            $allParts = [];
            foreach ($parts as $part) {
                $productRef = $part['product'] ?? [];
                $info = $part['info'] ?? null;
                
                // Convert info to JSON string for storage (Sushi doesn't support array type)
                // It will be cast back to array via the $casts property
                if (is_array($info)) {
                    $infoJson = json_encode($info, JSON_UNESCAPED_UNICODE);
                } elseif (is_string($info) && $info !== '') {
                    // Already a string, keep it as is
                    $infoJson = $info;
                } else {
                    $infoJson = null;
                }

                $partRow = [
                    'id' => $part['id'] ?? null,
                    'name' => $part['name'] ?? 'Unknown',
                    'price' => (float) ($part['price'] ?? 0),
                    'type' => (int) ($part['type'] ?? 0),
                    'condition' => isset($part['condition']) ? (int) $part['condition'] : null,
                    'info' => $infoJson, // Store as JSON string
                    'product_id' => $productRef['id'] ?? $productId ?? null,
                    'product_name' => $productRef['name'] ?? $productName,
                    'created_at' => $this->normalizeDate($part['createdAt'] ?? $part['created_at'] ?? now()),
                    'updated_at' => $this->normalizeDate($part['updatedAt'] ?? $part['updated_at'] ?? now()),
                ];

                // Only add if ID exists
                if ($partRow['id'] !== null) {
                    $allParts[] = $partRow;
                }
            }

            Log::info('Parts mapped successfully', [
                'mapped_count' => count($allParts),
                'original_count' => count($parts),
                'productId' => $productId
            ]);
            
            // Debug: Log first part structure if available
            if (!empty($allParts)) {
                Log::debug('First mapped part', ['part' => $allParts[0]]);
            } else {
                Log::warning('No parts mapped despite having parts in response', [
                    'parts_count' => count($parts),
                    'productId' => $productId
                ]);
            }
            
            return $allParts;
        } catch (\Exception $e) {
            Log::error('Failed to fetch parts from API', ['error' => $e->getMessage()]);
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
    /**
     * Make cache key unique per product ID
     * This ensures different product filters get different cache entries
     */
    public static function getSushiCacheKey(): string
    {
        $productId = static::$currentProductId ?? 'none';
        return static::class . '_parts_product_' . $productId;
    }

    public function save(array $options = [])
    {
        try {
            $service = new PartService();

            if ($this->exists && $this->id) {
                $result = $service->update((string) $this->id, $this->attributesToArray());
            } else {
                $result = $service->create($this->attributesToArray());
            }

            if (isset($result['id'])) {
                $this->setAttribute('id', $result['id']);
                $this->exists = true;
            }

            Log::info('Part saved via API', ['id' => $this->id ?? 'new']);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to save part: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete(): bool
    {
        try {
            $service = new PartService();
            $service->delete((string) $this->id);

            Log::info('Part deleted via API', ['id' => $this->id]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete part: ' . $e->getMessage());
            throw $e;
        }
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
