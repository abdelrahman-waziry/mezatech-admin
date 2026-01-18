<?php

namespace App\Models;

use App\Services\ApiTokenService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Sushi\Sushi;

class Brand extends Model
{
    use Sushi;

    protected $table = 'brands';

    // Static cache for request-scoped caching
    protected static array $requestCache = [];

    protected $fillable = [
        'id',
        'name',
        'image',
    ];

    protected $schema = [
        'id' => 'integer',
        'name' => 'string',
        'image' => 'string',
    ];

    protected $casts = [
        'id' => 'integer',
    ];

    /**
     * Fetch brands from external API
     */
    public function getRows(): array
    {
        // Smart Caching: Request-scoped cache
        if (isset(static::$requestCache['brands_data'])) {
             return static::$requestCache['brands_data'];
        }

        try {
                $token = app(ApiTokenService::class)->getToken();
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ])->timeout(30)->get('https://bestrepairegypt.com/v1/brands');

            if (!$response->successful()) {
                Log::error('Brands API failed', ['status' => $response->status()]);
                return [];
            }

            $data = $response->json();
            

            // Extract brands from response according to openapi.yaml BrandsResponse schema
            // BrandsResponse has: { brands: BrandDTO[] }
            if (isset($data['brands']) && is_array($data['brands'])) {
                $brands = $data['brands'];
            } elseif (is_array($data)) {
                // Fallback: if response is directly an array
                $brands = $data;
            } else {
                Log::warning('Brands API did not return expected structure', ['response' => $data]);
                return [];
            }

            if (empty($brands) || !is_array($brands)) {
                Log::warning('No brands found in API response', ['response' => $data]);
                return [];
            }

            Log::info('Brands API processed: ' . count($brands) . ' items');

            $rows = collect($brands)->map(function ($brand) {
                return [
                    'id' => $brand['id'] ?? null,
                    'name' => $brand['name'] ?? 'Unknown',
                    'image' => $brand['image'] ?? null,
                ];
            })->filter(fn ($row) => $row['id'] !== null)->all();

            Log::info('Total brands mapped: ' . count($rows));

            static::$requestCache['brands_data'] = $rows;

            return $rows;
        } catch (\Exception $e) {
            Log::error('Failed to fetch brands from API', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function getIncrementing()
    {
        return false;
    }

    protected $keyType = 'int';

    /**
     * Override save to use API for creating/updating brands
     */
    public function save(array $options = [])
    {
        try {
            $service = new \App\Services\BrandService();

            if ($this->exists && isset($this->id)) {
                // Update existing brand
                $result = $service->update((string) $this->id, $this->attributes);
            } else {
                // Create new brand
                $result = $service->create($this->attributes);
            }

            // Update attributes with API response
            if (isset($result['id'])) {
                $this->setAttribute('id', $result['id']);
                $this->exists = true;
            }

            Log::info('Brand saved via API', ['id' => $this->id ?? 'new']);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to save brand: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Override delete to use API
     */
    public function delete()
    {
        try {
            $service = new \App\Services\BrandService();
            $service->delete((string) $this->id);
            Log::info('Brand deleted via API', ['id' => $this->id]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete brand: ' . $e->getMessage());
            throw $e;
        }
    }
}



