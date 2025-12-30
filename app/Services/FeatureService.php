<?php

namespace App\Services;

class FeatureService extends BaseExternalService
{
    protected function getResourceName(): string
    {
        return 'features';
    }

    /**
     * Format feature data for API according to openapi.yaml FeatureDTO schema
     * FeatureDTO has: { id: int64, name: string, values: FeatureValueDTO[], productId: int64 }
     * FeatureValueDTO has: { id: int64, value: string, image: string }
     */
    protected function formatPayload(array $data): array
    {
        \Illuminate\Support\Facades\Log::info('FeatureService: formatPayload called', [
            'input_data' => $data
        ]);

        $payload = [
            'name' => $data['name'] ?? null,
        ];

        // Add productId if provided
        if (isset($data['product_id'])) {
            $payload['productId'] = (int) $data['product_id'];
        }

        // Handle values array - FeatureValueDTO[] with value and optional image
        if (isset($data['values']) && is_array($data['values'])) {
            \Illuminate\Support\Facades\Log::info('FeatureService: Processing values array', [
                'values_count' => count($data['values']),
                'raw_values' => $data['values']
            ]);
            
            $payload['values'] = array_map(function ($value) {
                $valueData = [
                    'value' => $value['value'] ?? '',
                ];

                // Add image if provided (can be a URL string or file path)
                if (!empty($value['image'])) {
                    $valueData['image'] = $value['image'];
                }

                return $valueData;
            }, $data['values']);
            
            \Illuminate\Support\Facades\Log::info('FeatureService: Mapped values', [
                'mapped_values' => $payload['values']
            ]);
        } else {
            \Illuminate\Support\Facades\Log::warning('FeatureService: No values in data', [
                'has_values_key' => isset($data['values']),
                'is_array' => isset($data['values']) && is_array($data['values'])
            ]);
        }

        \Illuminate\Support\Facades\Log::info('FeatureService: formatted payload', [
            'payload' => $payload
        ]);

        return $payload;
    }

    /**
     * Override create to clear caches
     */
    public function create(array $data)
    {
        $result = parent::create($data);
        $this->clearFeatureCaches();
        return $result;
    }

    /**
     * Override update to clear caches
     */
    public function update(string $id, array $data)
    {
        $result = parent::update($id, $data);
        $this->clearFeatureCaches();
        return $result;
    }

    /**
     * Clear feature caches after operations
     */
    protected function clearFeatureCaches(): void
    {
        \Illuminate\Support\Facades\Cache::forget('features_data');
        // Clear product caches as features are linked to products
        \Illuminate\Support\Facades\Cache::forget('products_data');
        // Note: Variant caches are cleared individually in VariantService
    }
}
