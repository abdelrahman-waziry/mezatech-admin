<?php

namespace App\Filament\Resources\Variants\Tables;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Variants\VariantResource;

class VariantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Variant')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('product_name')->label('Product')->sortable(),
                Tables\Columns\TextColumn::make('buying_price')
                    ->label('Buying Price')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_after_discount')
                    ->label('Final Price')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')->label('Stock')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Created')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Product')
                    ->options(fn () => Product::all()->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->placeholder('Select a product to view variants')
                    ->default(null)
                    ->query(function ($query, array $data) {
                        // This filter will be handled by the ListVariants page
                        // which sets Variant::$currentProductId before querying
                        return $query;
                    }),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->url(fn ($record) => VariantResource::getUrl('edit', ['record' => $record['id']])),
                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        try {
                            $service = new \App\Services\VariantService();
                            $service->delete((string) $record['id']);

                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Variant Deleted')
                                ->body('Variant has been deleted successfully via the API.')
                                ->send();

                            // Refresh the table
                            return redirect(request()->header('Referer'));
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Error Deleting Variant')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),
            ])
;
    }
}
