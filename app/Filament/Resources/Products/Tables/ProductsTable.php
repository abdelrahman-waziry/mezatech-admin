<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Products\ProductResource;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->copyable(),

                Tables\Columns\TextColumn::make('brand_name')
                    ->label('Brand')
                    ->sortable(),

                Tables\Columns\TextColumn::make('minimum_buying_price')
                    ->label('Min Buying Price')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('waste_price')
                    ->label('Waste Price')
                    ->money('EGP'),

                Tables\Columns\TextColumn::make('features_summary')
                    ->label('Features')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('tags_summary')
                    ->label('Tags')
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // Add filters later if needed (brand, condition, etc.)
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->url(fn ($record) => ProductResource::getUrl('edit', ['record' => $record['id']])),
                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        try {
                            $service = new \App\Services\ExternalProductService();
                            $service->deleteProduct((string) $record['id']);

                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Product Deleted')
                                ->body('Product has been deleted successfully via the API.')
                                ->send();

                            // Refresh the table
                            return redirect(request()->header('Referer'));
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Error Deleting Product')
                                ->body($e->getMessage())
                                ->send();
                        }
                    })
            ])
            ->toolbarActions([
                // Intentionally empty:
                // Avoid destructive actions on external data
            ])
            ->defaultSort('created_at', 'desc');
    }
}
