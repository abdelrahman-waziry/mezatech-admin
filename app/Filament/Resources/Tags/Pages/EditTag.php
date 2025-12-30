<?php

namespace App\Filament\Resources\Tags\Pages;

use App\Filament\Resources\Tags\TagResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditTag extends EditRecord
{
    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Delete action removed since we're using API-backed data
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->action('saveForm')
            ->keyBindings(['mod+s'])
            ->label('Update Tag');
    }

    public function saveForm(): void
    {
        $this->validate();

        try {
            $data = $this->form->getState();
            
            // Update via API
            $this->record->fill($data);
            $this->record->save();

            Notification::make()
                ->success()
                ->title('Tag Updated')
                ->body('Tag has been updated successfully via the API.')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error Updating Tag')
                ->body($e->getMessage())
                ->send();
        }
    }
}
