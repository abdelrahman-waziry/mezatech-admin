<?php

namespace App\Filament\Resources\Variants\Schemas;

use App\Services\ApiTokenService;
use App\Services\ExternalProductService; // Ensure this matches your service namespace
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class VariantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Variant Name')
                    ->required()
                    ->maxLength(255),

                Select::make('product_id')
                    ->label('Product')
                    ->options(function () {
                        // Cache the list for performance, but only for 60 seconds
                        return Cache::remember('variant_form_products_list_v2', 60, function() {
                            try {
                                $service = new ExternalProductService();
                                $products = $service->fetchProducts(); 

                                if (empty($products)) return [];

                                // Map [Integer ID => String Name]
                                return collect($products)
                                    ->pluck('name', 'id')
                                    ->mapWithKeys(function ($name, $id) {
                                        return [(int)$id => $name];
                                    })
                                    ->toArray();
                            } catch (\Exception $e) {
                                Log::error('VariantForm Product Load Error: ' . $e->getMessage());
                                return [];
                            }
                        });
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(fn ($set) => $set('variant_features', [])),

                TextInput::make('buying_price')->label('Buying Price')->numeric(),
                TextInput::make('price_before_discount')->label('Price Before Discount')->numeric(),
                TextInput::make('discount')->label('Discount')->numeric(),
                TextInput::make('price_after_discount')->label('Price After Discount')->numeric(),
                TextInput::make('stock')->label('Stock')->numeric(),

                Repeater::make('variant_features')
                    ->label('Variant Features')
                    ->schema([
                        Select::make('feature_id')
                            ->label('Feature')
                            // Load features for the SELECTED product (from state)
                            ->options(fn ($get) => self::getFeaturesMap($get('../../product_id')))
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($set) => $set('feature_value_id', null)),

                        Select::make('feature_value_id')
                            ->label('Value')
                            // Load values for the SELECTED feature
                            ->options(fn ($get) => self::getFeatureValues($get('../../product_id'), $get('feature_id')))
                            ->required()
                            ->reactive(),
                    ])
                    ->defaultItems(0)
                    ->collapsible()
                    ->itemLabel(fn ($state, $get) => 
                        (self::getFeaturesMap($get('../../product_id'))[$state['feature_id'] ?? ''] ?? 'Feature') . ' - ' .
                        (self::getFeatureValues($get('../../product_id'), $state['feature_id'] ?? '')[$state['feature_value_id'] ?? ''] ?? 'Value')
                    ),
            ]);
    }

    // --- Helper Methods ---

    protected static function getFeaturesMap($productId)
    {
        if (!$productId) return [];
        
        $features = self::fetchProductFeatures((int)$productId);
        
        return collect($features)
            ->pluck('name', 'id')
            ->mapWithKeys(fn ($name, $id) => [(int)$id => $name])
            ->toArray();
    }

    protected static function getFeatureValues($productId, $featureId)
    {
        if (!$productId || !$featureId) return [];
        
        $features = self::fetchProductFeatures((int)$productId);
        $feature = collect($features)->firstWhere('id', (int)$featureId);
        
        return collect($feature['values'] ?? [])
            ->pluck('value', 'id') 
            ->mapWithKeys(fn ($val, $id) => [(int)$id => $val])
            ->toArray();
    }

    /**
     * Helper to fetch features for a specific product
     */
    protected static function fetchProductFeatures(int $productId): array
    {
        return Cache::remember("product_features_simple_{$productId}", 600, function () use ($productId) {
            try {
                // We use raw HTTP here to keep it lightweight, or reuse a service method if available
                $token = app(ApiTokenService::class)->getToken();
                $response = Http::withHeaders(['Authorization' => 'Bearer ' . $token])
                    ->timeout(5)
                    ->get("https://bestrepairegypt.com/v1/products/{$productId}");
                
                if (!$response->successful()) return [];

                return $response->json()['features'] ?? [];
            } catch (\Exception $e) {
                Log::error("Failed to fetch features for product {$productId}: " . $e->getMessage());
                return [];
            }
        });
    }
}
