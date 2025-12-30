<?php

namespace App\Services;

class TagService extends BaseExternalService
{
    protected function getResourceName(): string
    {
        return 'tags';
    }

    /**
     * Format tag data for API according to openapi.yaml TagDTO schema
     * TagDTO has: { id: int64, name: string }
     */
    protected function formatPayload(array $data): array
    {
        return [
            'name' => $data['name'] ?? null,
        ];
    }
}
