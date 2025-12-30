<?php

namespace App\Filament\Resources\Brands\Pages;

use App\Filament\Resources\Brands\BrandResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateBrand extends CreateRecord
{
    protected static string $resource = BrandResource::class;

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
            ->label('Create Brand');
    }

    public function saveForm(): void
    {
        $this->validate();

        try {
            $data = $this->form->getState();
            
            // Create new Brand instance and save via API
            $brand = new \App\Models\Brand($data);
            $brand->save();

            Notification::make()
                ->success()
                ->title('Brand Created')
                ->body('Brand has been created successfully via the API.')
                ->send();

            $this->redirect($this->getRedirectUrl());
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error Creating Brand')
                ->body($e->getMessage())
                ->send();
        }
    }
}
