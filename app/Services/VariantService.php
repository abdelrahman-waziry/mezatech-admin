<?php

namespace App\Services;

class VariantService extends BaseExternalService
{
    protected function getResourceName(): string
    {
        return 'variants';
    }

    /**
     * Format variant data for API
     */
    protected function formatPayload(array $data): array
    {
        \Illuminate\Support\Facades\Log::info('VariantService: formatPayload called', [
            'input_data' => $data
        ]);

        $payload = [
            'name' => $data['name'] ?? null,
            'buyingPrice' => isset($data['buying_price']) ? (float) $data['buying_price'] : null,
            'priceBeforeDiscount' => isset($data['price_before_discount']) ? (float) $data['price_before_discount'] : null,
            'discount' => isset($data['discount']) ? (float) $data['discount'] : null,
            'priceAfterDiscount' => isset($data['price_after_discount']) ? (float) $data['price_after_discount'] : null,
            'stock' => isset($data['stock']) ? (int) $data['stock'] : null,
        ];

        if (isset($data['product_id'])) {
            $payload['product'] = ['id' => (int) $data['product_id']];
        }

        // Format variant features
        if (isset($data['variant_features']) && is_array($data['variant_features'])) {
            $payload['variantFeatures'] = array_map(function ($vf) {
                return [
                    'feature' => ['id' => (int) $vf['feature_id']],
                    'featureValue' => ['id' => (int) $vf['feature_value_id']],
                ];
            }, array_filter($data['variant_features'], function ($vf) {
                return !empty($vf['feature_id']) && !empty($vf['feature_value_id']);
            }));
        }

        \Illuminate\Support\Facades\Log::info('VariantService: formatted payload', [
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
        $this->clearVariantCaches($data['product_id'] ?? null);
        return $result;
    }

    /**
     * Override update to clear caches
     */
    public function update(string $id, array $data)
    {
        $result = parent::update($id, $data);
        $this->clearVariantCaches($data['product_id'] ?? null);
        return $result;
    }

    /**
     * Clear variant caches after operations
     */
    protected function clearVariantCaches($productId = null): void
    {
        if ($productId) {
            \Illuminate\Support\Facades\Cache::forget("variants_data_product_{$productId}");
        }
        // Also clear general caches that might be affected
        \Illuminate\Support\Facades\Cache::forget('products_data');
    }
}
