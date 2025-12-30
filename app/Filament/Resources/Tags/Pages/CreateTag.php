<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Resources\Tags\TagResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateTag extends CreateRecord
{
    protected static string $resource = TagResource::class;

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
            ->label('Create Tag');
    }

    public function saveForm(): void
    {
        $this->validate();

        try {
            $data = $this->form->getState();
            
            // Create new Tag instance and save via API
            $tag = new \App\Models\Tag($data);
            $tag->save();

            Notification::make()
                ->success()
                ->title('Tag Created')
                ->body('Tag has been created successfully via the API.')
                ->send();

            $this->redirect($this->getRedirectUrl());
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error Creating Tag')
                ->body($e->getMessage())
                ->send();
        }
    }
}
