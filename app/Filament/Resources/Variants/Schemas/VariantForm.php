<?php

namespace App\Filament\Resources\Variants\Schemas;

use App\Services\ApiTokenService;
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
                        // FIX: Fetch directly from API to avoid empty Sushi tables
                        return Cache::remember('variant_form_products_list', 60, function() {
                            try {
                                $token = app(ApiTokenService::class)->getToken();
                                $response = Http::withHeaders(['Authorization' => 'Bearer ' . $token])
                                    ->timeout(5)
                                    ->get('https://bestrepairegypt.com/v1/products');
                                
                                if (!$response->successful()) return [];

                                $data = $response->json();
                                $products = $data['products'] ?? $data ?? [];

                                // Map to [Integer ID => String Name]
                                return collect($products)->pluck('name', 'id')->mapWithKeys(function ($name, $id) {
                                    return [(int)$id => $name];
                                })->toArray();
                            } catch (\Exception $e) {
                                Log::error('Form Options Error: ' . $e->getMessage());
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
                            ->options(fn ($get) => self::getFeaturesMap($get('../../product_id')))
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($set) => $set('feature_value_id', null)),

                        Select::make('feature_value_id')
                            ->label('Value')
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

    // --- Helper Methods (Cached & Simplified) ---

    protected static function getFeaturesMap($productId)
    {
        if (!$productId) return [];
        $features = self::fetchProductFeatures((int)$productId);
        
        // Return [Integer ID => String Name]
        return collect($features)->pluck('name', 'id')->mapWithKeys(function ($name, $id) {
            return [(int)$id => $name];
        })->toArray();
    }

    protected static function getFeatureValues($productId, $featureId)
    {
        if (!$productId || !$featureId) return [];
        $features = self::fetchProductFeatures((int)$productId);
        $feature = collect($features)->firstWhere('id', (int)$featureId);
        
        return collect($feature['values'] ?? [])
            ->pluck('value', 'id') 
            ->mapWithKeys(function ($val, $id) {
                return [(int)$id => $val];
            })
            ->toArray();
    }

    protected static function fetchProductFeatures(int $productId): array
    {
        return Cache::remember("product_features_simple_{$productId}", 600, function () use ($productId) {
            try {
                $token = app(ApiTokenService::class)->getToken();
                $response = Http::withHeaders(['Authorization' => 'Bearer ' . $token])
                    ->get("https://bestrepairegypt.com/v1/products/{$productId}");
                
                return $response->json()['features'] ?? [];
            } catch (\Exception $e) {
                return [];
            }
        });
    }
}
