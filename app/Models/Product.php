<?php

namespace App\Models;

use App\Services\ApiTokenService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Sushi\Sushi;
use Carbon\Carbon;

class Product extends Model
{
    use Sushi;

    protected $table = 'products';
    
    protected $fillable = [
        'id',
        'name',
        'sku',
        'brand_id',
        'brand_name',
        'minimum_buying_price',
        'waste_price',
        'condition',
        'features_summary',
        'tags_summary',
        'created_at',
    ];
    
    protected $schema = [
        'id' => 'integer',
        'name' => 'string',
        'sku' => 'string',
        'brand_name' => 'string',
        'minimum_buying_price' => 'float',
        'waste_price' => 'float',
        'condition' => 'integer',
        'features_summary' => 'string',
        'tags_summary' => 'string',
        'created_at' => 'string',
    ];

    protected $casts = [
        'id' => 'integer',
        'minimum_buying_price' => 'float',
        'waste_price' => 'float',
        'condition' => 'integer',
        'created_at' => 'datetime',
    ];

    public function getRows(): array
    {
        Log::info('Product::getRows called - checking cache');
        return \Illuminate\Support\Facades\Cache::remember('products_data', 300, function () { // Cache for 5 minutes
            Log::info('Product cache miss - fetching from API');
            try {
                // Fetch API
                $token = app(ApiTokenService::class)->getToken();
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ])->timeout(10)->get('https://bestrepairegypt.com/v1/products');

                if (!$response->successful()) {
                    Log::error('Products API failed', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'headers' => $response->headers()
                    ]);

                    // Return fallback data for development
                    return $this->getFallbackData();
                }

                $data = $response->json();
            
            // Extract products from response according to openapi.yaml ProductsResponse schema
            // ProductsResponse has: { products: ProductDTO[] }
            if (isset($data['products']) && is_array($data['products'])) {
                $products = $data['products'];
            } elseif (is_array($data)) {
                // Fallback: if response is directly an array
                $products = $data;
            } else {
                Log::warning('Products API did not return expected structure', ['response' => $data]);
                return [];
            }
            
            if (!is_array($products)) {
                Log::warning('Products API did not return array', ['response' => $products]);
                return [];
            }

            Log::info('Products API returned ' . count($products) . ' items');

            // Map and flatten
            $rows = collect($products)->map(function ($product) {
                $createdAt = $product['createdAt'] ?? now();
                // Handle ISO datetime strings
                if (is_string($createdAt)) {
                    $createdAt = \Carbon\Carbon::parse($createdAt)->toDateTimeString();
                }

                $row = [
                    'id' => $product['id'] ?? null,
                    'name' => $product['name'] ?? 'Unknown',
                    'sku' => $product['sku'] ?? null,
                    'brand_name' => $product['brand']['name'] ?? 'Unknown',
                    'minimum_buying_price' => (float) ($product['minimumBuyingPrice'] ?? 0),
                    'waste_price' => (float) ($product['wastePrice'] ?? 0),
                    'condition' => (int) ($product['condition'] ?? 0),
                    'features_summary' => $this->formatFeatures($product['features'] ?? []),
                    'tags_summary' => collect($product['tags'] ?? [])->pluck('name')->implode(', '),
                    'created_at' => $createdAt,
                ];

                Log::debug('Mapped product row', $row);

                return $row;
            })->filter(fn ($row) => $row['id'] !== null)->all();

            Log::info('Total products mapped: ' . count($rows));

                return $rows;
            } catch (\Exception $e) {
                Log::error('Failed to fetch products from API', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    protected function formatFeatures(array $features): string
    {
        return collect($features)->map(function ($feature) {
            $values = collect($feature['values'])->pluck('value')->implode('/');
            return "{$feature['name']}: {$values}";
        })->implode(' | ');
    }

    /**
     * Get fallback data for development when API is not available
     */
    protected function getFallbackData(): array
    {
        Log::info('Using fallback product data - API not available');

        return [
            [
                'id' => 1,
                'name' => 'Sample iPhone 15 Pro',
                'sku' => 'IP15P-128',
                'brand_name' => 'Apple',
                'minimum_buying_price' => 1200.00,
                'waste_price' => 800.00,
                'condition' => 0,
                'features_summary' => 'Storage: 128GB/256GB/512GB/1TB | Color: Black/Titanium/Blue',
                'tags_summary' => 'smartphone,apple,iphone',
                'created_at' => now()->toDateTimeString(),
            ],
            [
                'id' => 2,
                'name' => 'Sample Samsung Galaxy S24',
                'sku' => 'SGS24-256',
                'brand_name' => 'Samsung',
                'minimum_buying_price' => 1000.00,
                'waste_price' => 700.00,
                'condition' => 0,
                'features_summary' => 'Storage: 256GB/512GB | Color: Black/Blue/Silver',
                'tags_summary' => 'smartphone,samsung,android',
                'created_at' => now()->toDateTimeString(),
            ],
            [
                'id' => 3,
                'name' => 'Sample MacBook Pro M3',
                'sku' => 'MBPM3-16',
                'brand_name' => 'Apple',
                'minimum_buying_price' => 2500.00,
                'waste_price' => 1800.00,
                'condition' => 0,
                'features_summary' => 'RAM: 16GB/32GB/64GB | Storage: 512GB/1TB/2TB',
                'tags_summary' => 'laptop,apple,macbook',
                'created_at' => now()->toDateTimeString(),
            ],
        ];
    }

    public function getIncrementing()
    {
        return false;
    }

    protected $keyType = 'int';

    /**
     * Override save to use API for creating/updating products
     * Do NOT save to database - only call API
     */
    public function save(array $options = [])
    {
        try {
            $service = new \App\Services\ExternalProductService();
            
            if ($this->exists && isset($this->id)) {
                // Update existing product
                $result = $service->updateProduct((string) $this->id, $this->attributes);
            } else {
                // Create new product
                $result = $service->createProduct($this->attributes);
            }

            // Update attributes with API response but don't actually save to DB
            if (isset($result['id'])) {
                $this->setAttribute('id', $result['id']);
                $this->exists = true;
            }

            Log::info('Product saved via API', ['id' => $this->id ?? 'new']);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to save product: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Override delete to use API for deleting products
     */
    public function delete()
    {
        try {
            $service = new \App\Services\ExternalProductService();
            $service->deleteProduct((string) $this->id);
            
            Log::info('Product deleted via API', ['id' => $this->id]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete product: ' . $e->getMessage());
            throw $e;
        }
    }

    public function variants()
    {
        // Adjust the class if your model is named differently
        return $this->hasMany(\App\Models\Variant::class);
    }
}
