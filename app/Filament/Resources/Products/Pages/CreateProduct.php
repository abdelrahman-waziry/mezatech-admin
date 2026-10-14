<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->action('saveForm')
            ->submit()
            ->keyBindings(['mod+s'])
            ->label('Create Product');
    }

    public function saveForm(): void
    {
        $this->validate();

        try {
            $data = $this->form->getState();

            \Illuminate\Support\Facades\Log::info('CreateProduct: Form data', [
                'form_data' => $data
            ]);

            \App\Models\Product::create($data);

            Notification::make()
                ->success()
                ->title('Product Created')
                ->body('Product has been created successfully.')
                ->send();

            $this->redirect($this->getRedirectUrl());
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error Creating Product')
                ->body($e->getMessage())
                ->send();
        }
    }
}
