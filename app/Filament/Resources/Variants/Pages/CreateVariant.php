<?php

namespace App\Filament\Resources\Variants\Pages;

use App\Filament\Resources\Variants\VariantResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateVariant extends CreateRecord
{
    protected static string $resource = VariantResource::class;

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
            ->label('Create Variant');
    }

    public function saveForm(): void
    {
        $this->validate();

        try {
            $data = $this->form->getState();

            \Illuminate\Support\Facades\Log::info('CreateVariant: Form data', [
                'form_data' => $data
            ]);

            // Create new Variant instance and save via API
            $variant = new \App\Models\Variant($data);
            $variant->save();

            Notification::make()
                ->success()
                ->title('Variant Created')
                ->body('Variant has been created successfully via the API.')
                ->send();

            $this->redirect($this->getRedirectUrl());
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error Creating Variant')
                ->body($e->getMessage())
                ->send();
        }
    }
}
