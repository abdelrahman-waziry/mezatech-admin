<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

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
        $this->record->load(['brand', 'tags']);
        $data['brand_id'] = $this->record->brand_id;
        $data['tags_summary'] = $this->record->tags->pluck('id')->all();

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

            $this->record->update($data);

            Notification::make()
                ->success()
                ->title('Product Updated')
                ->body('Product has been updated successfully.')
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
