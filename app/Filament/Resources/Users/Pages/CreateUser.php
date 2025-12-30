<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

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
            ->label('Create Customer');
    }

    public function saveForm(): void
    {
        $this->validate();

        try {
            $data = $this->form->getState();
            
            // Create new Customer instance and save via API
            $customer = new \App\Models\Customer($data);
            $customer->save();

            Notification::make()
                ->success()
                ->title('Customer Created')
                ->body('Customer has been created successfully via the API.')
                ->send();

            $this->redirect($this->getRedirectUrl());
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error Creating Customer')
                ->body($e->getMessage())
                ->send();
        }
    }
}
