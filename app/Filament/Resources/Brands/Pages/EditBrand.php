<?php

namespace App\Filament\Resources\Brands\Pages;

use App\Filament\Resources\Brands\BrandResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditBrand extends EditRecord
{
    protected static string $resource = BrandResource::class;

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
            ->label('Update Brand');
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
                ->title('Brand Updated')
                ->body('Brand has been updated successfully via the API.')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error Updating Brand')
                ->body($e->getMessage())
                ->send();
        }
    }
}
