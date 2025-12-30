<?php

namespace App\Filament\Resources\Features\Tables;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\Features\FeatureResource;

class FeaturesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('product_name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Feature Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('values_count')
                    ->label('Values Count')
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ?? 0),
                Tables\Columns\TextColumn::make('values_summary')
                    ->label('Values')
                    ->wrap()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->values_summary ?? 'No values')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Product')
                    ->options(fn () => Product::query()->orderBy('name')->pluck('name', 'id')->toArray())
                    ->placeholder('All Products')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->url(fn ($record) => FeatureResource::getUrl('edit', [
                        'record' => $record['id'],
                        'productId' => $record['product_id'] ?? null
                    ])),
                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        try {
                            $service = new \App\Services\FeatureService();
                            $service->delete((string) $record['id']);

                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Feature Deleted')
                                ->body('Feature has been deleted successfully via the API.')
                                ->send();

                            // Refresh the table
                            return redirect(request()->header('Referer'));
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Error Deleting Feature')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),
            ])
            ->defaultSort('product_name', 'asc');
    }
}
