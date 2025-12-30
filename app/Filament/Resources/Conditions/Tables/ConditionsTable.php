<?php

namespace App\Filament\Resources\Conditions\Tables;

use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Conditions\ConditionResource;

class ConditionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Condition')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('price_modifier')
                    ->label('Price Modifier')
                    ->formatStateUsing(fn ($state) => is_numeric($state) ? number_format($state, 2) : $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->wrap()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->url(fn ($record) => ConditionResource::getUrl('edit', ['record' => $record['id']])),
                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        try {
                            // Conditions don't seem to have a dedicated service, so we'll use a generic approach
                            $token = app(\App\Services\ApiTokenService::class)->getToken();
                            $response = \Illuminate\Support\Facades\Http::withHeaders([
                                'Authorization' => 'Bearer ' . $token,
                                'Accept' => 'application/json',
                            ])->delete('https://bestrepairegypt.com/v1/conditions/' . $record['id']);

                            if ($response->successful()) {
                                \Filament\Notifications\Notification::make()
                                    ->success()
                                    ->title('Condition Deleted')
                                    ->body('Condition has been deleted successfully via the API.')
                                    ->send();

                                // Refresh the table
                                return redirect(request()->header('Referer'));
                            } else {
                                throw new \Exception('API returned status ' . $response->status());
                            }
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Error Deleting Condition')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),
            ])
;
    }
}
