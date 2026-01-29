<?php

namespace App\Filament\Resources\Variants\Pages;

use App\Filament\Resources\Variants\VariantResource;
use App\Models\Variant;
use App\Services\VariantService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class EditVariant extends EditRecord
{
    protected static string $resource = VariantResource::class;

    /**
     * 1. LOAD DATA: Fetch from API and force types to match Form
     */
    protected function resolveRecord($key): Model
    {
        try {
            $service = new VariantService();
            $data = $service->fetchOne($key);

            if (!$data) {
                abort(404, "Variant not found in API");
            }

            // ROBUST ID FINDER: Check both nested object and flat key
            $productId = $data['product']['id'] ?? $data['productId'] ?? null;

            $attributes = [
                'id' => $data['id'],
                'name' => $data['name'] ?? '',
                'buying_price' => $data['buyingPrice'] ?? $data['buying_price'] ?? 0,
                'price_before_discount' => $data['priceBeforeDiscount'] ?? $data['price_before_discount'] ?? 0,
                'discount' => $data['discount'] ?? 0,
                'price_after_discount' => $data['priceAfterDiscount'] ?? $data['price_after_discount'] ?? 0,
                'stock' => $data['stock'] ?? 0,
                
                // CRITICAL FIX: Force Integer. 
                // The Form options are Integers, so this MUST match exactly.
                'product_id' => $productId ? (int)$productId : null,
                
                'product_name' => $data['product']['name'] ?? 'Unknown',
            ];

            // Debug Log: Check this if it still fails
            Log::info('EditVariant Loaded:', ['id' => $data['id'], 'product_id' => $attributes['product_id']]);

            $record = new Variant();
            $record->forceFill($attributes);
            $record->exists = true; 

            return $record;

        } catch (\Exception $e) {
            Log::error("EditVariant Crash: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 2. SAVE DATA: Bypass Database, send to API
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            $service = new VariantService();
            // Use API to update
            $service->update($record->id, $data);
            
            // Refill local model so the form doesn't go blank
            $record->fill($data);
            
            Notification::make()->success()->title('Saved via API')->send();

            return $record;
        } catch (\Exception $e) {
            Notification::make()->danger()->title('Save Failed')->body($e->getMessage())->send();
            throw $e; // Throwing stops the redirect
        }
    }

    /**
     * 3. FILL REPEATER: Map nested features
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        try {
            $service = new VariantService();
            $variant = $service->fetchOne($this->record->id);

            if ($variant && !empty($variant['variantFeatures'])) {
                $data['variant_features'] = collect($variant['variantFeatures'])->map(function ($vf) {
                    return [
                        'feature_id' => (int)($vf['feature']['id'] ?? $vf['feature_id']),
                        'feature_value_id' => (int)($vf['featureValue']['id'] ?? $vf['feature_value_id']),
                    ];
                })->toArray();
            }
        } catch (\Exception $e) {
            Log::warning('Feature load failed: ' . $e->getMessage());
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
