<?php

namespace App\Services;

class ConditionService extends BaseExternalService
{
    // TODO: /v1/conditions endpoint is not defined in openapi.yaml
    // This service should be updated once the endpoint is added to the API specification
    
    protected function getResourceName(): string
    {
        return 'conditions';
    }

    /**
     * Format condition data for API
     */
    protected function formatPayload(array $data): array
    {
        return [
            'name' => $data['name'] ?? null,
            'description' => $data['description'] ?? null,
            'priceModifier' => isset($data['price_modifier']) ? (float) $data['price_modifier'] : 1.0,
        ];
    }
}
