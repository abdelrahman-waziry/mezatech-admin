<?php

namespace App\Filament\Resources\Features\Pages;

use App\Filament\Resources\Features\FeatureResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateFeature extends CreateRecord
{
    protected static string $resource = FeatureResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        \Illuminate\Support\Facades\Log::info('CreateFeature: mutateFormDataBeforeCreate called', [
            'data' => $data,
            'has_values' => isset($data['values']),
            'values' => $data['values'] ?? 'NOT SET'
        ]);

        // Ensure values are included and directly accessible
        if (isset($data['values']) && is_array($data['values'])) {
            \Illuminate\Support\Facades\Log::info('CreateFeature: Values found in form data', [
                'values_count' => count($data['values']),
                'values_detail' => $data['values']
            ]);
        } else {
            \Illuminate\Support\Facades\Log::error('CreateFeature: No values in form data!', [
                'all_keys' => array_keys($data)
            ]);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        \Illuminate\Support\Facades\Log::info('CreateFeature: handleRecordCreation called', [
            'data' => $data,
            'has_values' => isset($data['values'])
        ]);

        // Process file uploads for feature values - convert file paths to URLs
        if (isset($data['values']) && is_array($data['values'])) {
            foreach ($data['values'] as &$value) {
                if (!empty($value['image']) && is_string($value['image'])) {
                    // If it's a stored file path, convert to full URL
                    if (str_starts_with($value['image'], 'feature-values/')) {
                        $value['image'] = \Illuminate\Support\Facades\Storage::disk('public')->url($value['image']);
                    }
                }
            }
            unset($value); // Break reference
        }

        // Create new Feature instance using the FeatureService directly
        $service = new \App\Services\FeatureService();
        
        // Prepare data for API
        $apiData = [
            'name' => $data['name'] ?? '',
            'product_id' => $data['product_id'] ?? null,
            'values' => $data['values'] ?? [],
        ];
        
        \Illuminate\Support\Facades\Log::info('CreateFeature: Calling FeatureService->create', [
            'apiData' => $apiData,
            'values_count' => count($apiData['values'])
        ]);
        
        // Create via API
        $result = $service->create($apiData);
        
        \Illuminate\Support\Facades\Log::info('CreateFeature: API result', [
            'result' => $result
        ]);
        
        // Create a Feature model instance with the returned data
        $feature = new \App\Models\Feature();
        if (isset($result['id'])) {
            $feature->id = $result['id'];
            $feature->exists = true;
        }
        $feature->name = $result['name'] ?? $apiData['name'];
        $feature->product_id = $result['productId'] ?? $result['product_id'] ?? $apiData['product_id'];
        
        return $feature;
    }
}
