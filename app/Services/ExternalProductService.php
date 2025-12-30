<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Brand;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ExternalProductService extends BaseExternalService
{
     public function __construct(ApiTokenService $tokenService = null)
    {
        $this->httpClient = new Client();
        $this->tokenService = $tokenService ?? app(ApiTokenService::class);
    }
    protected function getResourceName(): string
    {
        return 'products';
    }

    /**
     * Backward compatible method
     */
    public function fetchProducts()
    {
        return $this->fetchAll();
    }

    /**
     * Format product data for API
     */
    protected function formatPayload(array $data): array
    {
        return [
            'name' => $data['name'],
            'description' => $data['name'] ?? '',
            'condition' => (int) $data['condition'],
            'notes' => $data['features_summary'] ?? '',
            'minimumBuyingPrice' => (float) $data['minimum_buying_price'],
            'wastePrice' => (float) $data['waste_price'],
            'brand' => isset($data['brand_id']) && !empty($data['brand_id']) 
                ? ['id' => (int) $data['brand_id']]
                : (isset($data['brand_name']) && !empty($data['brand_name']) 
                    ? ['name' => $data['brand_name']]
                    : null),
            'tags' => isset($data['tags_summary']) && !empty($data['tags_summary'])
                ? $this->parseTagsFromSummary($data['tags_summary'])
                : [],
        ];
    }

    /**
     * Sync external products to database
     */
    public function syncProducts()
    {
        $products = $this->fetchProducts();

        if (empty($products)) {
            return 0;
        }

        $synced = 0;

        foreach ($products as $productData) {
            try {
                // Find or ensure brand exists
                $brand = null;
                if (isset($productData['brand']) && is_array($productData['brand'])) {
                    $brand = Brand::firstOrCreate(
                        ['name' => $productData['brand']['name'] ?? 'Unknown'],
                        ['description' => $productData['brand']['description'] ?? null]
                    );
                }

                // Create or update product
                Product::updateOrCreate(
                    ['sku' => $productData['sku']],
                    [
                        'name' => $productData['name'],
                        'description' => $productData['description'],
                        'condition' => $productData['condition'] ?? 0,
                        'notes' => $productData['notes'],
                        'minimum_buying_price' => $productData['minimumBuyingPrice'],
                        'waste_price' => $productData['wastePrice'],
                        'brand_id' => $brand ? $brand->id : null,
                    ]
                );

                $synced++;
            } catch (\Exception $e) {
                Log::error('Failed to sync product: ' . $e->getMessage());
            }
        }

        return $synced;
    }

    /**
     * Get paginated products from API
     */
    public function getPaginatedProducts(int $page = 1, int $perPage = 15)
    {
        $products = $this->fetchProducts();
        $total = count($products);
        $paginated = array_slice($products, ($page - 1) * $perPage, $perPage);

        return [
            'data' => $paginated,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Create a new product via API
     */
    public function createProduct(array $data)
    {
        \Illuminate\Support\Facades\Log::info('ExternalProductService: createProduct called', [
            'input_data' => $data
        ]);

        try {
            $payload = [
                'name' => $data['name'],
                'sku' => $data['sku'] ?? null,
                'description' => $data['name'] ?? '', // Use name as description if not provided
                'condition' => (int) $data['condition'],
                'notes' => $data['features_summary'] ?? '',
                'minimumBuyingPrice' => (float) $data['minimum_buying_price'],
                'wastePrice' => (float) $data['waste_price'],
            ];

            // Add brand if provided (can be ID or name)
            if (!empty($data['brand_id'])) {
                $payload['brand'] = ['id' => (int) $data['brand_id']];
            } elseif (!empty($data['brand_name'])) {
                $payload['brand'] = ['name' => $data['brand_name']];
            }

            // Add tags if provided (parse from summary string or array)
            if (!empty($data['tags_summary'])) {
                if (is_array($data['tags_summary'])) {
                    // If it's already an array of IDs, convert to SubDTO format
                    $payload['tags'] = array_map(function ($tagId) {
                        return ['id' => (int) $tagId];
                    }, $data['tags_summary']);
                } else {
                    // Parse from comma-separated string
                    $payload['tags'] = $this->parseTagsFromSummary($data['tags_summary']);
                }
            }

            \Illuminate\Support\Facades\Log::info('ExternalProductService: createProduct payload', [
                'payload' => $payload
            ]);

            $token = $this->tokenService->getToken();

            $response = $this->httpClient->request('POST', $this->baseUrl . '/products', [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
                'timeout' => 30,
            ]);

            $result = json_decode($response->getBody(), true);
            
            // if (empty($result['id'])) {
            //     throw new \Exception('API did not return product ID');
            // }

            Log::info('Product created via API', ['id' => $result['id']]);

            // Clear related caches
            \Illuminate\Support\Facades\Cache::forget('products_data');

            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to create product via API: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing product via API
     */
    public function updateProduct(string $productId, array $data)
    {
        \Illuminate\Support\Facades\Log::info('ExternalProductService: updateProduct called', [
            'product_id' => $productId,
            'input_data' => $data
        ]);

        try {
            $payload = [
                'name' => $data['name'],
                'sku' => $data['sku'] ?? null,
                'description' => $data['name'] ?? '', // Use name as description if not provided
                'condition' => (int) $data['condition'],
                'notes' => $data['features_summary'] ?? '',
                'minimumBuyingPrice' => (float) $data['minimum_buying_price'],
                'wastePrice' => (float) $data['waste_price'],
            ];

            // Add brand if provided (can be ID or name)
            if (!empty($data['brand_id'])) {
                $payload['brand'] = ['id' => (int) $data['brand_id']];
            } elseif (!empty($data['brand_name'])) {
                $payload['brand'] = ['name' => $data['brand_name']];
            }

            // Add tags if provided (parse from summary string or array)
            if (!empty($data['tags_summary'])) {
                if (is_array($data['tags_summary'])) {
                    // If it's already an array of IDs, convert to SubDTO format
                    $payload['tags'] = array_map(function ($tagId) {
                        return ['id' => (int) $tagId];
                    }, $data['tags_summary']);
                } else {
                    // Parse from comma-separated string
                    $payload['tags'] = $this->parseTagsFromSummary($data['tags_summary']);
                }
            }

            \Illuminate\Support\Facades\Log::info('ExternalProductService: updateProduct payload', [
                'payload' => $payload
            ]);

            $token = $this->tokenService->getToken();

            $response = $this->httpClient->request('PUT', $this->baseUrl . '/products/' . $productId, [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
                'timeout' => 30,
            ]);

            $result = json_decode($response->getBody(), true);
            Log::info('Product updated via API', ['id' => $productId]);

            // Clear related caches
            \Illuminate\Support\Facades\Cache::forget('products_data');

            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to update product via API: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteProduct(string $productId): bool
    {
        try {
            $token = $this->tokenService->getToken();
            $response = $this->httpClient->request('DELETE', $this->baseUrl . '/products/' . $productId, [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
            ]);

            if ($response->getStatusCode() === 204) {
                Log::info('Product deleted via API', ['id' => $productId]);
                return true;
            }

            Log::warning('Unexpected response deleting product via API', [
                'id' => $productId,
                'status' => $response->getStatusCode(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Failed to delete product via API: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Parse tags summary string into tag objects with IDs
     * Expects comma-separated tag IDs (e.g. "1001,1002,1003")
     */
    protected function parseTagsFromSummary(string $summary): array
    {
        if (empty($summary)) {
            return [];
        }

        return array_map(function ($tag) {
            $tagId = (int) trim($tag);
            return ['id' => $tagId];
        }, explode(',', $summary));
    }
}
