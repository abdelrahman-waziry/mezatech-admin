<?php

namespace App\Filament\Resources\Brands\Tables;

use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Brands\BrandResource;

class BrandsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Brand Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->url(fn ($record) => BrandResource::getUrl('edit', ['record' => $record['id']])),
                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        try {
                            // Brands don't seem to have a dedicated service, so we'll use a generic approach
                            $token = app(\App\Services\ApiTokenService::class)->getToken();
                            $response = \Illuminate\Support\Facades\Http::withHeaders([
                                'Authorization' => 'Bearer ' . $token,
                                'Accept' => 'application/json',
                            ])->delete('https://bestrepairegypt.com/v1/brands/' . $record['id']);

                            if ($response->successful()) {
                                \Filament\Notifications\Notification::make()
                                    ->success()
                                    ->title('Brand Deleted')
                                    ->body('Brand has been deleted successfully via the API.')
                                    ->send();

                                // Refresh the table
                                return redirect(request()->header('Referer'));
                            } else {
                                throw new \Exception('API returned status ' . $response->status());
                            }
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Error Deleting Brand')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),
            ])
            ->defaultSort('name');
    }
}
