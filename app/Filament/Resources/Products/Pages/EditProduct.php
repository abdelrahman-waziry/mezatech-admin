<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Services\ApiTokenService;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Delete action removed since we're using API-backed data
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Fetch full product from API to get brand.id and tag IDs
        try {
            $token = app(ApiTokenService::class)->getToken();
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->timeout(30)->get("https://bestrepairegypt.com/v1/products/{$this->record['id']}");

            if ($response->successful()) {
                $productData = $response->json();
                
                // Extract brand ID
                if (isset($productData['brand']['id'])) {
                    $data['brand_id'] = (int) $productData['brand']['id'];
                } elseif (isset($productData['brand']['name'])) {
                    // Fallback: look up brand by name
                    $brand = \App\Models\Brand::where('name', $productData['brand']['name'])->first();
                    if ($brand) {
                        $data['brand_id'] = $brand->id;
                    }
                }

                // Extract tag IDs
                if (isset($productData['tags']) && is_array($productData['tags'])) {
                    $tagIds = collect($productData['tags'])->map(function ($tag) {
                        // Tag might have id or we need to look it up by name
                        if (isset($tag['id'])) {
                            return (int) $tag['id'];
                        } elseif (isset($tag['name'])) {
                            $tagModel = \App\Models\Tag::where('name', $tag['name'])->first();
                            return $tagModel ? (int) $tagModel->id : null;
                        }
                        return null;
                    })->filter()->values()->toArray();
                    
                    // Set as array of IDs (the form field will handle it)
                    $data['tags_summary'] = $tagIds;
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to load product data: ' . $e->getMessage());
        }

        return $data;
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->action('saveForm')
            ->keyBindings(['mod+s'])
            ->label('Update Product');
    }

    public function saveForm(): void
    {
        $this->validate();

        try {
            $data = $this->form->getState();

            \Illuminate\Support\Facades\Log::info('EditProduct: Form data', [
                'form_data' => $data
            ]);

            // Update via API directly
            $service = new \App\Services\ExternalProductService();
            $service->updateProduct((string) $this->record->id, $data);

            // Update the local record instance to reflect changes in UI (if needed before redirect/refresh)
            // $this->record->forceFill($data);

            Notification::make()
                ->success()
                ->title('Product Updated')
                ->body('Product has been updated successfully via the API.')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error Updating Product')
                ->body($e->getMessage())
                ->send();
        }
    }
}
