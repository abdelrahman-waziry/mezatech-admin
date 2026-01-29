<?php

namespace App\Filament\Resources\Variants;

use App\Filament\Resources\Variants\Pages\CreateVariant;
use App\Filament\Resources\Variants\Pages\EditVariant;
use App\Filament\Resources\Variants\Pages\ListVariants;
use App\Filament\Resources\Variants\Schemas\VariantForm;
use App\Filament\Resources\Variants\Tables\VariantsTable;
use App\Models\Variant;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use BackedEnum;
use UnitEnum;

class VariantResource extends Resource
{
    protected static ?string $model = Variant::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-swatch';
    protected static string|UnitEnum|null $navigationGroup = "Pricing Management";
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return VariantForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        // 1. Load the base table config (Columns, Filters)
        $table = VariantsTable::configure($table);

        // 2. Override the Actions to fix the Edit URL
        return $table
            ->actions([
                EditAction::make()
                    ->url(function (Variant $record) {
                        // Priority 1: Get ID from the record itself (Sushi loads it)
                        // Priority 2: Get ID from the current URL filter
                        $productId = $record->product_id 
                            ?? request()->input('filters.product_id.value')
                            ?? request()->input('tableFilters.product_id.value');

                        return self::getUrl('edit', [
                            'record' => $record->id,
                            'product_id' => $productId, // <--- Passes ?product_id=21 to the Edit Page
                        ]);
                    }),
                
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVariants::route('/'),
            'create' => CreateVariant::route('/create'),
            'edit' => EditVariant::route('/{record}/edit'),
        ];
    }
}
