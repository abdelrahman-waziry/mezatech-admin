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

    protected function resolveRecord($key): Model
    {
        try {
            // 1. Fetch from API
            $service = new VariantService();
            $data = $service->fetchOne($key);

            if (!$data) {
                Log::error("EditVariant: API returned null for ID $key");
                // Return a blank record instead of crashing, so you can at least see the UI
                $record = new Variant();
                $record->exists = false;
                return $record;
            }

            // 2. DEFENSIVE ID EXTRACTION
            // Check every possible place the Product ID might be
            $productId = null;
            if (isset($data['product']) && is_array($data['product'])) {
                $productId = $data['product']['id'] ?? null;
            } elseif (isset($data['productId'])) {
                $productId = $data['productId'];
            }

            // 3. MAP ATTRIBUTES SAFELY
            $attributes = [
                'id' => $data['id'] ?? $key,
                'name' => $data['name'] ?? '',
                'buying_price' => $data['buyingPrice'] ?? $data['buying_price'] ?? 0,
                'price_before_discount' => $data['priceBeforeDiscount'] ?? $data['price_before_discount'] ?? 0,
                'discount' => $data['discount'] ?? 0,
                'price_after_discount' => $data['priceAfterDiscount'] ?? $data['price_after_discount'] ?? 0,
                'stock' => $data['stock'] ?? 0,
                
                // CRITICAL: Force (int). Matches the keys in the Form.
                'product_id' => $productId ? (int)$productId : null,
                
                'product_name' => $data['product']['name'] ?? 'Unknown',
            ];

            $record = new Variant();
            $record->forceFill($attributes);
            $record->exists = true; // Crucial for Edit Page context

            return $record;

        } catch (\Exception $e) {
            // Log the REAL error so we can debug without crashing
            Log::error("EditVariant Critical Crash: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            // Re-throw so Filament handles the 500, but now we have logs
            throw $e;
        }
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            $service = new VariantService();
            $service->update($record->id, $data);
            
            $record->fill($data);
            Notification::make()->success()->title('Saved via API')->send();

            return $record;
        } catch (\Exception $e) {
            Notification::make()->danger()->title('Save Failed')->body($e->getMessage())->send();
            throw $e;
        }
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        try {
            // Load features safely
            $service = new VariantService();
            $variant = $service->fetchOne($this->record->id);

            if ($variant && !empty($variant['variantFeatures'])) {
                $data['variant_features'] = collect($variant['variantFeatures'])->map(function ($vf) {
                    $fId = $vf['feature']['id'] ?? $vf['feature_id'] ?? null;
                    $vId = $vf['featureValue']['id'] ?? $vf['feature_value_id'] ?? null;
                    
                    if (!$fId || !$vId) return null;

                    return [
                        'feature_id' => (int)$fId,
                        'feature_value_id' => (int)$vId,
                    ];
                })->filter()->values()->toArray();
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
