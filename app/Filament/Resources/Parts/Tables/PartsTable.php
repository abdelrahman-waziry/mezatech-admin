<?php

namespace App\Filament\Resources\Parts\Tables;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Parts\PartResource;

class PartsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Part')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('product_name')->label('Product')->sortable(),
                Tables\Columns\TextColumn::make('price')->label('Price')->money('EGP')->sortable(),
                // Tables\Columns\TextColumn::make('type')->label('Type ID')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Created')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Product')
                    ->options(fn () => Product::all()->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->placeholder('Select a product to view parts')
                    ->default(null)
                    ->query(function ($query, array $data) {
                        // This filter will be handled by the ListParts page
                        // which sets Part::$filterProductId before querying
                        return $query;
                    }),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->url(fn ($record) => PartResource::getUrl('edit', ['record' => $record['id']])),
                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        try {
                            // Parts don't seem to have a dedicated service, so we'll use a generic approach
                            $token = app(\App\Services\ApiTokenService::class)->getToken();
                            $response = \Illuminate\Support\Facades\Http::withHeaders([
                                'Authorization' => 'Bearer ' . $token,
                                'Accept' => 'application/json',
                            ])->delete('https://bestrepairegypt.com/v1/parts/' . $record['id']);

                            if ($response->successful()) {
                                \Filament\Notifications\Notification::make()
                                    ->success()
                                    ->title('Part Deleted')
                                    ->body('Part has been deleted successfully via the API.')
                                    ->send();

                                // Refresh the table
                                return redirect(request()->header('Referer'));
                            } else {
                                throw new \Exception('API returned status ' . $response->status());
                            }
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Error Deleting Part')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),
            ])
;
    }
}
