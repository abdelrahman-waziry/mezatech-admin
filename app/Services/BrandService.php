<?php

namespace App\Services;

class BrandService extends BaseExternalService
{
    protected function getResourceName(): string
    {
        return 'brands';
    }

    /**
     * Format brand data for API
     */
    protected function formatPayload(array $data): array
    {
        return [
            'name' => $data['name'] ?? null,
            'image' => $data['image'] ?? null,
        ];
    }
}
