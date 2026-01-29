<?php

namespace App\Filament\Resources\Variants\Pages;

use App\Filament\Resources\Variants\VariantResource;
use App\Models\Variant;
use App\Services\VariantService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class EditVariant extends EditRecord
{
    protected static string $resource = VariantResource::class;

    /**
     * CRITICAL FIX: Override how Filament finds the record.
     * We fetch directly from the API to bypass the Sushi model limitations.
     */
    protected function resolveRecord($key): Model
    {
        try {
            // 1. Fetch Variant from API
            $service = new VariantService();
            $data = $service->fetchOne($key);

            dd($data);
            if (!$data) {
                Log::error("EditVariant: API returned null for ID $key");
                // Return a blank record to allow the page to load (prevent 500 crash)
                $record = new Variant();
                $record->exists = false;
                return $record;
            }

            // 2. Extract Product ID safely
            $productId = null;
            if (isset($data['product']) && is_array($data['product'])) {
                $productId = $data['product']['id'] ?? null;
            } elseif (isset($data['productId'])) {
                $productId = $data['productId'];
            }

            // 3. Map Attributes with Strict Types
            $attributes = [
                'id' => $data['id'] ?? $key,
                'name' => $data['name'] ?? '',
                'buying_price' => $data['buyingPrice'] ?? $data['buying_price'] ?? 0,
                'price_before_discount' => $data['priceBeforeDiscount'] ?? $data['price_before_discount'] ?? 0,
                'discount' => $data['discount'] ?? 0,
                'price_after_discount' => $data['priceAfterDiscount'] ?? $data['price_after_discount'] ?? 0,
                'stock' => $data['stock'] ?? 0,
                
                // FORCE INTEGER: This ensures it matches the Form's select options
                'product_id' => $productId ? (int)$productId : null,
                
                'product_name' => $data['product']['name'] ?? 'Unknown',
            ];

            // Create a temporary model instance
            $record = new Variant();
            $record->forceFill($attributes);
            $record->exists = true; 

            return $record;

        } catch (\Exception $e) {
            // Log the actual error to storage/logs/laravel.log
            Log::error("EditVariant Crash: " . $e->getMessage());
            throw $e;
        }
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Fetch variant features from API to fill the repeater
        try {
            $service = new VariantService();
            // We use the ID from the record we just resolved
            $variant = $service->fetchOne($this->record->id);

            if ($variant && isset($variant['variantFeatures'])) {
                $data['variant_features'] = collect($variant['variantFeatures'])->map(function ($vf) {
                    // Handle various API key formats safely
                    $featureId = $vf['feature']['id'] ?? $vf['feature_id'] ?? null;
                    $valueId = $vf['featureValue']['id'] ?? $vf['feature_value_id'] ?? null;

                    if (!$featureId || !$valueId) return null;

                    return [
                        'feature_id' => (int)$featureId,
                        'feature_value_id' => (int)$valueId,
                    ];
                })->filter()->values()->toArray();
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

            Notification::make()
                ->success()
                ->title('Variant Updated')
                ->body('Variant has been updated successfully via the API.')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error Updating Variant')
                ->body($e->getMessage())
                ->send();
        }
    }
}
