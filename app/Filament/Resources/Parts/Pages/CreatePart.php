<?php

namespace App\Filament\Resources\Parts\Pages;

use App\Filament\Resources\Parts\PartResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePart extends CreateRecord
{
    protected static string $resource = PartResource::class;

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
            ->label('Create Part');
    }

    public function saveForm(): void
    {
        $this->validate();

        try {
            $data = $this->form->getState();
            
            // Create new Part instance and save via API
            $part = new \App\Models\Part($data);
            $part->save();

            Notification::make()
                ->success()
                ->title('Part Created')
                ->body('Part has been created successfully via the API.')
                ->send();

            $this->redirect($this->getRedirectUrl());
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error Creating Part')
                ->body($e->getMessage())
                ->send();
        }
    }
}
