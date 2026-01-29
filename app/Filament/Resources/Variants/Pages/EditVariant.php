<?php

namespace App\Filament\Resources\Variants\Pages;

use App\Filament\Resources\Variants\VariantResource;
use App\Models\Variant; // Import the Model
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
     * NUCLEAR OPTION: Override how Filament finds the record.
     * This bypasses the Database/Sushi entirely and fetches directly from API.
     */
    protected function resolveRecord($key): Model
    {
        try {
            $service = new VariantService();
            // Fetch the raw data using the ID ($key) from the URL
            $data = $service->fetchOne($key);

            if (!$data) {
                throw new \Exception("API returned empty data for ID: $key");
            }

            // Map API (camelCase) to Model (snake_case)
            // We must manually fill the attributes needed for the form
            $attributes = [
                'id' => $data['id'],
                'name' => $data['name'] ?? '',
                'buying_price' => $data['buyingPrice'] ?? $data['buying_price'] ?? 0,
                'price_before_discount' => $data['priceBeforeDiscount'] ?? $data['price_before_discount'] ?? 0,
                'discount' => $data['discount'] ?? 0,
                'price_after_discount' => $data['priceAfterDiscount'] ?? $data['price_after_discount'] ?? 0,
                'stock' => $data['stock'] ?? 0,
                'product_id' => $data['product']['id'] ?? $data['productId'] ?? null,
                // Add product_name if you display it
                'product_name' => $data['product']['name'] ?? 'Unknown',
            ];

            // Create a temporary Model instance
            $record = new Variant();
            $record->forceFill($attributes);
            
            // CRITICAL: Tell Filament this record "exists" so it treats this as an Edit page
            $record->exists = true; 

            return $record;

        } catch (\Exception $e) {
            Log::error("EditVariant resolveRecord failed: " . $e->getMessage());
            // If this fails, we want to see the error, not a generic 404
            // throwing this exception will show a 500 error with the message
            throw $e; 
        }
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // We still keep this to load the complex nested features
        try {
            $service = new VariantService();
            // We use $this->record->id which we just set in resolveRecord
            $variant = $service->fetchOne($this->record->id);

            if ($variant && isset($variant['variantFeatures'])) {
                $data['variant_features'] = collect($variant['variantFeatures'])->map(function ($vf) {
                    return [
                        'feature_id' => $vf['feature']['id'] ?? $vf['feature_id'] ?? null,
                        'feature_value_id' => $vf['featureValue']['id'] ?? $vf['feature_value_id'] ?? null,
                    ];
                })->filter(function ($vf) {
                    return !empty($vf['feature_id']) && !empty($vf['feature_value_id']);
                })->values()->toArray();
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
            ->label('Update Variant');
    }

    public function saveForm(): void
    {
        $this->validate();

        try {
            $data = $this->form->getState();

            $service = new VariantService();
            // Use the API to update
            $service->update($this->record->id, $data);

            Notification::make()
                ->success()
                ->title('Variant Updated')
                ->body('Saved via API successfully.')
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error Updating')
                ->body($e->getMessage())
                ->send();
        }
    }
}
