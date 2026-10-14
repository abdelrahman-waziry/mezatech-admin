<?php

namespace App\Filament\Resources\Conditions\Pages;

use App\Filament\Resources\Conditions\ConditionResource;
use Filament\Actions\DeleteAction;
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
        $record->update($data);
        return $record->refresh();
    }
}
