<?php

namespace App\Filament\Resources\Conditions\Pages;

use App\Filament\Resources\Conditions\ConditionResource;
use App\Services\ConditionService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCondition extends CreateRecord
{
    protected static string $resource = ConditionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            $service = new ConditionService();
            $payload = $service->create($data);

            Notification::make()
                ->title('Condition created successfully')
                ->success()
                ->send();

            return $this->hydrateModel($payload);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to create condition')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }

    protected function hydrateModel(array $payload): Model
    {
        $class = static::$resource::getModel();
        return $class::hydrate([$payload])->first() ?? new $class($payload);
    }
}
