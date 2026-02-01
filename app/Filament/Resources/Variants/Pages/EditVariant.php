<?php

namespace App\Filament\Resources\Variants\Pages;

use App\Filament\Resources\Variants\VariantResource;
use App\Models\Variant;
use App\Services\VariantService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EditVariant extends EditRecord
{
    protected static string $resource = VariantResource::class;

    /**
     * Override record resolution to fetch directly from API
     * This bypasses Sushi's SQLite cache which requires a product filter
     */
    public function resolveRecord(int|string $key): Model
    {
        try {
            $service = new VariantService();
            $variantData = $service->fetchOne((string) $key);

            if (!$variantData || !isset($variantData['id'])) {
                throw new ModelNotFoundException("Variant with ID {$key} not found.");
            }

            // Set the product ID filter for subsequent Sushi operations
            // Check API response first, then URL query parameter (passed from listing page), then session
            $productId = $variantData['product']['id'] 
                ?? $variantData['productId'] 
                ?? $variantData['product_id'] 
                ?? request()->query('product_id')
                ?? session('variant_edit_product_id')
                ?? null;
            
            if ($productId) {
                Variant::$currentProductId = (int) $productId;
                // Store in session for AJAX request persistence
                session(['variant_edit_product_id' => (int) $productId]);
            }

            // Create a Variant model instance from API data
            $variant = new Variant();
            $variant->setRawAttributes([
                'id' => $variantData['id'],
                'name' => $variantData['name'] ?? 'Unnamed',
                'buying_price' => (float) ($variantData['buyingPrice'] ?? $variantData['buying_price'] ?? 0),
                'price_before_discount' => (float) ($variantData['priceBeforeDiscount'] ?? $variantData['price_before_discount'] ?? 0),
                'discount' => (float) ($variantData['discount'] ?? 0),
                'price_after_discount' => (float) ($variantData['priceAfterDiscount'] ?? $variantData['price_after_discount'] ?? 0),
                'stock' => (int) ($variantData['stock'] ?? 0),
                'product_id' => $productId,
                'product_name' => $variantData['product']['name'] ?? 'Unknown Product',
                'created_at' => $variantData['createdAt'] ?? $variantData['created_at'] ?? now(),
                'updated_at' => $variantData['updatedAt'] ?? $variantData['updated_at'] ?? now(),
            ]);
            $variant->exists = true;
            $variant->wasRecentlyCreated = false;

            return $variant;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to resolve variant for edit', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            throw new ModelNotFoundException("Variant with ID {$key} not found.");
        }
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Fetch variant from API to get full variant data including features
        try {
            $service = new VariantService();
            // We use the ID from the record we just resolved
            $variant = $service->fetchOne($this->record->id);

            if (!$variant) {
                \Illuminate\Support\Facades\Log::warning('EditVariant: Could not fetch variant from API', [
                    'record_id' => $this->record['id'] ?? null,
                ]);
                return $data;
            }

            // Extract product_id - check multiple sources:
            // 1. From the API response (if available)
            // 2. From the URL query parameter (passed from listing page)
            // 3. From session (for AJAX requests)
            // 4. From the record attributes
            // 5. From existing form data
            $productId = $variant['product']['id'] 
                ?? $variant['productId'] 
                ?? $variant['product_id'] 
                ?? request()->query('product_id')
                ?? session('variant_edit_product_id')
                ?? $this->record['product_id']
                ?? $data['product_id'] 
                ?? null;

            if ($productId) {
                $data['product_id'] = (int) $productId;
                // Also set on the static property for Sushi operations
                Variant::$currentProductId = (int) $productId;
                // Store in session for AJAX request persistence
                session(['variant_edit_product_id' => (int) $productId]);
            }

            \Illuminate\Support\Facades\Log::info('EditVariant: mutateFormDataBeforeFill', [
                'record_id' => $this->record['id'] ?? null,
                'url_product_id' => request()->query('product_id'),
                'extracted_product_id' => $productId,
                'data_product_id' => $data['product_id'] ?? null,
                'has_variant_features' => isset($variant['variantFeatures']) ? count($variant['variantFeatures']) : 0,
            ]);

            // Transform variant features to form format
            if (isset($variant['variantFeatures']) && is_array($variant['variantFeatures'])) {
                // Note: form field is 'feature_value', not 'feature_value_id'
                $data['variant_features'] = collect($variant['variantFeatures'])->map(function ($vf) {
                    // Handle various API key formats safely
                    $featureId = $vf['feature']['id'] ?? $vf['feature_id'] ?? null;
                    $valueId = $vf['featureValue']['id'] ?? $vf['feature_value_id'] ?? null;

                    if (!$featureId || !$valueId) return null;

                    return [
                        'feature_id' => $vf['feature']['id'] ?? $vf['feature_id'] ?? null,
                        'feature_value' => $vf['featureValue']['value'] ?? $vf['featureValue']['id'] ?? $vf['feature_value'] ?? null,
                    ];
                })->filter(function ($vf) {
                    return !empty($vf['feature_id']) && !empty($vf['feature_value']);
                })->values()->toArray();

                \Illuminate\Support\Facades\Log::info('EditVariant: Mapped variant features', [
                    'count' => count($data['variant_features']),
                    'features' => $data['variant_features'],
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to load variant features: ' . $e->getMessage());
        }

        return $data;
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->action('saveForm')
            ->keyBindings(['mod+s'])
            ->label('Update Variant');
    }

    public function saveForm(): void
    {
        $this->validate();

        try {
            $data = $this->form->getState();

            // Update via API
            $service = new VariantService();
            $service->update($this->record->id, $data);

            // Clear the session product_id since we're done editing
            $productId = session('variant_edit_product_id');
            session()->forget('variant_edit_product_id');

            Notification::make()
                ->success()
                ->title('Variant Updated')
                ->body('Variant has been updated successfully via the API.')
                ->send();

            // Redirect to listing page with the product filter preserved
            $this->redirect(VariantResource::getUrl('index', [
                'tableFilters[product_id][value]' => $productId,
            ]));
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error Updating Variant')
                ->body($e->getMessage())
                ->send();
        }
    }
}
