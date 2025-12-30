<?php

namespace App\Filament\Resources\Variants\Pages;

use App\Filament\Resources\Variants\VariantResource;
use App\Services\VariantService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditVariant extends EditRecord
{
    protected static string $resource = VariantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Delete action removed since we're using API-backed data
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Fetch variant from API to get variant features
        try {
            $service = new VariantService();
            $variant = $service->fetchOne($this->record['id']);

            if ($variant && isset($variant['variantFeatures'])) {
                // Transform variant features to form format
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
            // If fetching fails, just continue without variant features
            \Illuminate\Support\Facades\Log::warning('Failed to load variant features: ' . $e->getMessage());
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

            \Illuminate\Support\Facades\Log::info('EditVariant: Form data', [
                'form_data' => $data
            ]);

            // Update via API
            $this->record->fill($data);
            $this->record->save();

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
