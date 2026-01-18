<?php

namespace App\Filament\Resources\TradeInRequests\Pages;

use App\Filament\Resources\TradeInRequests\TradeInRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTradeInRequest extends EditRecord
{
    protected static string $resource = TradeInRequestResource::class;

    protected function afterSave(): void
    {
        $record = $this->getRecord();
        
        if ($record->wasChanged('status')) {
            try {
                \Illuminate\Support\Facades\Mail::to($record->customer_email)
                    ->send(new \App\Mail\TradeInStatusChanged($record));
                    
                \Filament\Notifications\Notification::make()
                    ->title('Email sent to customer')
                    ->success()
                    ->send();
            } catch (\Exception $e) {
                \Filament\Notifications\Notification::make()
                    ->title('Failed to email customer')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
