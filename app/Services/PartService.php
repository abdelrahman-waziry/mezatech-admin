<?php

namespace App\Services;

class PartService extends BaseExternalService
{
    protected function getResourceName(): string
    {
        return 'parts';
    }

    /**
     * Format part data for API
     */
    protected function formatPayload(array $data): array
    {
        $payload = [
            'name' => $data['name'] ?? null,
            'price' => isset($data['price']) ? (float) $data['price'] : 0,
            'type' => isset($data['type']) ? (int) $data['type'] : null,
            'condition' => isset($data['condition']) ? (int) $data['condition'] : null,
            'info' => $this->prepareInfo($data['info'] ?? null),
        ];

        if (isset($data['product_id'])) {
            $payload['product'] = ['id' => (int) $data['product_id']];
        }

        return $payload;
    }

    protected function prepareInfo(mixed $info): ?string
    {
        if (is_array($info)) {
            $encoded = json_encode($info, JSON_UNESCAPED_UNICODE);
            return $encoded === false ? null : $encoded;
        }

        if (is_string($info) && $info !== '') {
            return $info;
        }

        return null;
    }
}
