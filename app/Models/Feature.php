<?php

namespace App\Models;

use App\Services\ApiTokenService;
use App\Services\FeatureService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Feature extends Model
{
    protected $table = 'features';
    // Static cache for request-scoped caching
    protected static array $requestCache = [];

    protected $fillable = [
        'id',
        'name',
        'product_id',
        'values_summary',
    ];

    protected $schema = [
        'id' => 'integer',
        'name' => 'string',
        'product_id' => 'integer',
        'values_summary' => 'string',
    ];

    protected $casts = [
        'id' => 'integer',
        'product_id' => 'integer',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(FeatureValue::class);
    }

    public function getRows(): array
    {
        return static::query()->get()->toArray();

        // Smart Caching: Request-scoped cache
        if (isset(static::$requestCache['features_data'])) {
             return static::$requestCache['features_data'];
        }

        try {
                $token = app(ApiTokenService::class)->getToken();
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ])->timeout(30)->get('https://bestrepairegypt.com/v1/features');

                if (!$response->successful()) {
                    Log::error('Features API failed', ['status' => $response->status()]);
                    return [];
                }

                $data = $response->json();


                // Extract features from response according to openapi.yaml FeaturesResponse schema
                // FeaturesResponse has: { features: FeatureDTO[] }
                // FeatureDTO has: { id: int64, name: string, values: FeatureValueDTO[], productId: int64 }
                if (isset($data['features']) && is_array($data['features'])) {
                    $features = $data['features'];
                } elseif (is_array($data)) {
                    // Fallback: if response is directly an array
                    $features = $data;
                } else {
                    Log::warning('Features API did not return expected structure', ['response' => $data]);
                    return [];
                }



            $rows = collect($features)->map(function ($feature) {
                // Format values as summary string for display
                $valuesSummary = '';
                if (isset($feature['values']) && is_array($feature['values'])) {
                    $valuesSummary = collect($feature['values'])->pluck('value')->implode(', ');
                }

                return [
                    'id' => $feature['id'] ?? null,
                    'name' => $feature['name'] ?? 'Unnamed',
                    'product_id' => $feature['productId'] ?? $feature['product_id'] ?? null,
                    'values_summary' => $valuesSummary,
                ];
            })->filter(fn ($row) => $row['id'] !== null)->all();
            
            static::$requestCache['features_data'] = $rows;
            return $rows;
        } catch (\Exception $e) {
            Log::error('Failed to fetch features from API', ['error' => $e->getMessage()]);
            return [];
        }
    }


}
