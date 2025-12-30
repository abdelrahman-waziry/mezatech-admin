<?php

namespace App\Filament\Resources\Conditions\Pages;

use App\Filament\Resources\Conditions\ConditionResource;
use App\Services\ConditionService;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditCondition extends EditRecord
{
    protected static string $resource = ConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            $service = new ConditionService();
            $service->update($record->id, $data);

            Notification::make()
                ->title('Condition updated successfully')
                ->success()
                ->send();

            return $record;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to update condition')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }
}
