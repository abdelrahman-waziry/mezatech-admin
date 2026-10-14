<?php

namespace App\Filament\Resources\Parts\Pages;

use App\Filament\Resources\Parts\PartResource;
use App\Models\Part;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class EditPart extends EditRecord
{
    protected static string $resource = PartResource::class;

    protected function resolveRecord($key): Model
    {
        return Part::query()->findOrFail($key);
    }

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
            ->label('Update Part');
    }

    public function saveForm(): void
    {
        $this->validate();

        try {
            $data = $this->form->getState();
            
            $this->record->fill($data);
            $this->record->save();

            Notification::make()
                ->success()
                ->title('Part Updated')
                ->body('Part has been updated successfully.')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Error Updating Part')
                ->body($e->getMessage())
                ->send();
        }
    }
}
