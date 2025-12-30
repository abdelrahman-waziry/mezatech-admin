<?php

namespace App\Models;

use App\Services\ApiTokenService;
use App\Services\FeatureService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Sushi\Sushi;

class Feature extends Model
{
    use Sushi;

    protected $table = 'features';

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

    public function getRows(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('features_data', 300, function () {
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

                return collect($features)->map(function ($feature) {
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
            } catch (\Exception $e) {
                Log::error('Failed to fetch features from API', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }


    public function getIncrementing(): bool
    {
        return false;
    }

    protected $keyType = 'int';

    public function save(array $options = [])
    {
        try {
            $service = new FeatureService();

            // Build data array directly from attributes since Sushi's attributesToArray() 
            // doesn't include non-schema attributes
            $data = [];
            
            // Get standard attributes
            if (isset($this->attributes['name'])) {
                $data['name'] = $this->attributes['name'];
            }
            
            if (isset($this->attributes['product_id'])) {
                $data['product_id'] = $this->attributes['product_id'];
            }
            
            if (isset($this->attributes['id'])) {
                $data['id'] = $this->attributes['id'];
            }
            
            // IMPORTANT: Include values if they were set
            if (isset($this->attributes['values'])) {
                $data['values'] = $this->attributes['values'];
            }
            

            \Illuminate\Support\Facades\Log::info('Feature: save() called', [
                'raw_attributes' => $this->attributes,
                'prepared_data' => $data,
                'exists' => $this->exists,
                'has_values' => isset($data['values']),
                'values_count' => isset($data['values']) ? count($data['values']) : 0,
                'values_detail' => $data['values'] ?? null
            ]);

            if ($this->exists && $this->id) {
                $result = $service->update((string) $this->id, $data);
            } else {
                $result = $service->create($data);
            }

            if (isset($result['id'])) {
                $this->setAttribute('id', $result['id']);
                $this->exists = true;
            }

            Log::info('Feature saved via API', ['id' => $this->id ?? 'new', 'result' => $result]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to save feature: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete(): bool
    {
        try {
            $service = new FeatureService();
            $service->delete((string) $this->id);

            Log::info('Feature deleted via API', ['id' => $this->id]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete feature: ' . $e->getMessage());
            throw $e;
        }
    }
}
