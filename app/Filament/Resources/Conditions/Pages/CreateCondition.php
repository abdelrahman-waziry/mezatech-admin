<?php

namespace App\Filament\Resources\Conditions\Pages;

use App\Filament\Resources\Conditions\ConditionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCondition extends CreateRecord
{
    protected static string $resource = ConditionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return static::$resource::getModel()::create($data);
    }
}
