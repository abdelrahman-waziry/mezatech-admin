<?php

namespace App\Filament\Resources\Features\Pages;

use App\Filament\Resources\Features\FeatureResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditFeature extends EditRecord
{
    protected static string $resource = FeatureResource::class;

    public ?int $productId = null;
    public ?array $product = null;

    public function mount($record, $productId = null): void
    {
        $this->productId = $productId;
        
        // Fetch product BEFORE calling parent::mount() so it's available in resolveRecord()
        if ($this->productId) {
            $this->fetchProduct($this->productId);
        }
        
        parent::mount($record);
    }

    protected function fetchProduct(int $productId): void
    {
        try {
            $service = new \App\Services\ExternalProductService();
            $productData = $service->fetchOne((string)$productId);
            
            if ($productData) {
                $this->product = $productData;
                
                \Illuminate\Support\Facades\Log::info('EditFeature: Fetched product', [
                    'product_id' => $productId,
                    'product_data' => $productData
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('EditFeature: Failed to fetch product', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get the feature data from the product by matching feature ID
     */
    protected function getFeatureFromProduct(int $featureId): ?array
    {
        if (!$this->product || !isset($this->product['features'])) {
            \Illuminate\Support\Facades\Log::warning('EditFeature: Product or features not available', [
                'has_product' => !is_null($this->product),
                'has_features' => isset($this->product['features'])
            ]);
            return null;
        }

        foreach ($this->product['features'] as $feature) {
            if (isset($feature['id']) && $feature['id'] == $featureId) {
                \Illuminate\Support\Facades\Log::info('EditFeature: Found feature in product', [
                    'feature_id' => $featureId,
                    'feature' => $feature,
                    'values_count' => count($feature['values'] ?? [])
                ]);
                return $feature;
            }
        }

        \Illuminate\Support\Facades\Log::warning('EditFeature: Feature not found in product', [
            'looking_for_feature_id' => $featureId,
            'available_features' => array_column($this->product['features'], 'id')
        ]);

        return null;
    }

    /**
     * Get the product for use in the view
     */
    public function getProduct(): ?array
    {
        return $this->product;
    }

    /**
     * Override to ensure we load the full record from API
     */
    protected function resolveRecord($key): \Illuminate\Database\Eloquent\Model
    {
        $record = parent::resolveRecord($key);
        
        // Try to get feature data from the product first
        if ($this->product) {
            $featureData = $this->getFeatureFromProduct((int)$key);
            
            if ($featureData) {
                // Store the full API data in the model's attributes for form binding
                $record->id = $featureData['id'] ?? $key;
                $record->name = $featureData['name'] ?? '';
                $record->product_id = $this->product['id'];
                
                // Store values from the product's feature
                $record->setAttribute('values', $featureData['values'] ?? []);
                
                \Illuminate\Support\Facades\Log::info('EditFeature: resolveRecord loaded from product', [
                    'id' => $key,
                    'product_id' => $record->product_id,
                    'feature_data' => $featureData,
                    'values_count' => count($featureData['values'] ?? [])
                ]);
                
                return $record;
            }
        }
        
        // Fallback: Fetch full data from FeatureService API if product not available
        try {
            $service = new \App\Services\FeatureService();
            $featureData = $service->fetchOne((string)$key);
            
            if ($featureData) {
                // Store the full API data in the model's attributes for form binding
                $record->id = $featureData['id'] ?? $key;
                $record->name = $featureData['name'] ?? '';
                $record->product_id = $featureData['productId'] ?? $featureData['product_id'] ?? null;
                
                // Store values in the model (even though it's not in the schema)
                $record->setAttribute('values', $featureData['values'] ?? []);
                
                \Illuminate\Support\Facades\Log::info('EditFeature: resolveRecord loaded from FeatureService', [
                    'id' => $key,
                    'product_id' => $record->product_id,
                    'feature_data' => $featureData
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('EditFeature: Failed to load full data in resolveRecord', [
                'error' => $e->getMessage()
            ]);
        }
        
        return $record;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        \Illuminate\Support\Facades\Log::info('EditFeature: mutateFormDataBeforeFill called', [
            'original_data' => $data,
            'has_product' => !is_null($this->product),
            'productId' => $this->productId,
            'has_values_in_data' => isset($data['values'])
        ]);

        // Set product_id from URL parameter
        if ($this->productId) {
            $data['product_id'] = $this->productId;
        }

        // Get feature data from product if available
        if ($this->product && isset($data['id'])) {
            $featureData = $this->getFeatureFromProduct((int)$data['id']);
            
            if ($featureData) {
                // Override name from product's feature if available
                $data['name'] = $featureData['name'] ?? $data['name'] ?? '';
                
                // Get values from product's feature
                if (isset($featureData['values']) && is_array($featureData['values'])) {
                    $data['values'] = $featureData['values'];
                    \Illuminate\Support\Facades\Log::info('EditFeature: Set values from product feature', [
                        'raw_values' => $featureData['values']
                    ]);
                }
                
                \Illuminate\Support\Facades\Log::info('EditFeature: Using feature data from product', [
                    'feature_id' => $data['id'],
                    'feature_name' => $data['name'],
                    'values_count' => count($featureData['values'] ?? [])
                ]);
            }
        }

        // Map values array for the form if available
        if (isset($data['values']) && is_array($data['values']) && !empty($data['values'])) {
            \Illuminate\Support\Facades\Log::info('EditFeature: Mapping values for form', [
                'before_mapping' => $data['values']
            ]);
            
            $data['values'] = collect($data['values'])->map(function ($value) {
                return [
                    'value' => $value['value'] ?? '',
                    'image' => $value['image'] ?? null,
                ];
            })->toArray();
            
            \Illuminate\Support\Facades\Log::info('EditFeature: After mapping values', [
                'after_mapping' => $data['values']
            ]);
        } else {
            // Provide default empty value if no values exist
            \Illuminate\Support\Facades\Log::warning('EditFeature: No values found, using default empty', [
                'has_values' => isset($data['values']),
                'is_array' => isset($data['values']) && is_array($data['values']),
                'is_empty' => isset($data['values']) && empty($data['values'])
            ]);
            $data['values'] = [['value' => '', 'image' => null]];
        }

        \Illuminate\Support\Facades\Log::info('EditFeature: Final data for form', [
            'product_id' => $data['product_id'] ?? null,
            'name' => $data['name'] ?? null,
            'values_count' => count($data['values'] ?? []),
            'final_values' => $data['values'] ?? []
        ]);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            // Delete action removed since we're using API-backed data
        ];
    }


    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->action('saveForm')
            ->keyBindings(['mod+s'])
            ->label('Update Feature');
    }

    public function saveForm(): void
    {
        $this->validate();

        try {
            $data = $this->form->getState();

            \Illuminate\Support\Facades\Log::info('EditFeature: Form data before processing', [
                'form_data' => $data,
                'productId' => $this->productId,
                'product_from_url' => $this->product
            ]);

            // Ensure product_id is set from URL parameter
            if ($this->productId) {
                $data['product_id'] = $this->productId;
            } elseif ($this->product && isset($this->product['id'])) {
                // Fallback to product data if productId not in URL
                $data['product_id'] = $this->product['id'];
            }

            // Process file uploads for feature values - convert file paths to URLs
            if (isset($data['values']) && is_array($data['values'])) {
                foreach ($data['values'] as &$value) {
                    if (!empty($value['image']) && is_string($value['image'])) {
                        // If it's a stored file path, convert to full URL
                        // Filament stores files in public disk, so we need to get the URL
                        if (str_starts_with($value['image'], 'feature-values/')) {
                            $value['image'] = \Illuminate\Support\Facades\Storage::disk('public')->url($value['image']);
                        }
                        // If it's already a URL, keep it as is
                    }
                }
                unset($value); // Break reference
            }

            \Illuminate\Support\Facades\Log::info('EditFeature: Form data after processing', [
                'processed_data' => $data
            ]);

            // Update via API
            $this->record->fill($data);
            $this->record->save();

            Notification::make()
                ->success()
                ->title('Feature Updated')
                ->body('Feature has been updated successfully via the API.')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error Updating Feature')
                ->body($e->getMessage())
                ->send();
        }
    }
}
