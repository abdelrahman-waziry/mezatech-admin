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
     * Override how Filament finds the record.
     * We ignore the database/Sushi and fetch strictly from the API.
     * * @param string|int $key The ID from the URL (Variant ID)
     */
    protected function resolveRecord($key): Model
    {
        try {
            $service = new VariantService();
            $data = $service->fetchOne($key);

            if (!$data) {
                // Log this so you can see it in: docker-compose logs app
                Log::error("EditVariant: API returned null for ID $key");
                abort(404);
            }

            // DEFENSIVE CODING: Handle multiple API structures for Product ID
            $productId = null;
            if (isset($data['product']) && is_array($data['product'])) {
                $productId = $data['product']['id'] ?? null;
            } elseif (isset($data['productId'])) {
                $productId = $data['productId'];
            }

            // Map API Data to Model Attributes safely
            $attributes = [
                'id' => $data['id'] ?? $key,
                'name' => $data['name'] ?? '',
                'buying_price' => $data['buyingPrice'] ?? $data['buying_price'] ?? 0,
                'price_before_discount' => $data['priceBeforeDiscount'] ?? $data['price_before_discount'] ?? 0,
                'discount' => $data['discount'] ?? 0,
                'price_after_discount' => $data['priceAfterDiscount'] ?? $data['price_after_discount'] ?? 0,
                'stock' => $data['stock'] ?? 0,
                
                // CRITICAL: Force to Integer to match the Select Options in the Form
                'product_id' => $productId ? (int)$productId : null,
                
                'product_name' => $data['product']['name'] ?? 'Unknown',
            ];

            // Create a fake model instance to hold the data
            $record = new Variant();
            $record->forceFill($attributes);
            $record->exists = true; // Tells Filament "This is an Edit page, not Create"

            return $record;

        } catch (\Exception $e) {
            // This prevents the generic 500 page and logs the real error
            Log::error("EditVariant Crash: " . $e->getMessage());
            Log::error("Stack Trace: " . $e->getTraceAsString());
            abort(500, "Variant Load Error: " . $e->getMessage());
        }
    }

    /**
     * Override saving to bypass database and use API
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            $service = new VariantService();
            // $record->id is the Variant ID from the URL
            $service->update($record->id, $data);
            
            // Update local instance so the form doesn't go blank
            $record->fill($data);
            
            Notification::make()->success()->title('Saved via API')->send();

            return $record;
        } catch (\Exception $e) {
            Notification::make()->danger()->title('Save Failed')->body($e->getMessage())->send();
            throw $e;
        }
    }

    /**
     * Load the Repeater data (Features)
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        try {
            $service = new VariantService();
            // Fetch fresh data using Variant ID
            $variant = $service->fetchOne($this->record->id);

            if ($variant && !empty($variant['variantFeatures'])) {
                $data['variant_features'] = collect($variant['variantFeatures'])->map(function ($vf) {
                    // Safe access with defaults
                    $featureId = $vf['feature']['id'] ?? $vf['feature_id'] ?? null;
                    $valueId = $vf['featureValue']['id'] ?? $vf['feature_value_id'] ?? null;

                    return [
                        'feature_id' => $featureId ? (int)$featureId : null,
                        'feature_value_id' => $valueId ? (int)$valueId : null,
                    ];
                })->filter(fn($item) => $item['feature_id'] && $item['feature_value_id'])->values()->toArray();
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
