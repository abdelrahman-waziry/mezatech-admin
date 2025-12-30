<?php

namespace App\Filament\Resources\Parts\Pages;

use App\Filament\Resources\Parts\PartResource;
use App\Models\Part;
use App\Services\PartService;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class EditPart extends EditRecord
{
    protected static string $resource = PartResource::class;

    protected function resolveRecord($key): Model
    {
        // Fetch the part data from API since Sushi models don't support find()
        $partService = new PartService();
        $partData = $partService->fetchOne($key);

        if (!$partData) {
            abort(404, 'Part not found');
        }

        // Create a Part model instance with the fetched data
        $part = new Part();
        $part->setRawAttributes([
            'id' => $partData['id'] ?? null,
            'name' => $partData['name'] ?? '',
            'price' => (float) ($partData['price'] ?? 0),
            'type' => (int) ($partData['type'] ?? 0),
            'condition' => isset($partData['condition']) ? (int) $partData['condition'] : null,
            'info' => is_array($partData['info'] ?? null) ? json_encode($partData['info']) : $partData['info'],
            'product_id' => $partData['product']['id'] ?? null,
            'product_name' => $partData['product']['name'] ?? '',
            'created_at' => $partData['createdAt'] ?? $partData['created_at'] ?? now(),
            'updated_at' => $partData['updatedAt'] ?? $partData['updated_at'] ?? now(),
        ]);
        $part->exists = true;
        $part->wasRecentlyCreated = false;

        return $part;
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
            
            // Update via API
            $this->record->fill($data);
            $this->record->save();

            Notification::make()
                ->success()
                ->title('Part Updated')
                ->body('Part has been updated successfully via the API.')
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
