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
                        // RAW HTTP CALL: Bypasses Service Class issues entirely
                        return Cache::remember('variant_form_products_raw_v3', 60, function() {
                            try {
                                $token = app(ApiTokenService::class)->getToken();
                                $response = Http::withHeaders([
                                    'Authorization' => 'Bearer ' . $token,
                                    'Accept' => 'application/json'
                                ])->timeout(5)->get('https://bestrepairegypt.com/v1/products');
                                
                                if (!$response->successful()) return [];
                                
                                $data = $response->json();
                                $products = $data['products'] ?? $data ?? [];

                                // Strict Integer Mapping [Integer ID => String Name]
                                return collect($products)->pluck('name', 'id')
                                    ->mapWithKeys(fn($name, $id) => [(int)$id => $name])
                                    ->toArray();

                            } catch (\Exception $e) {
                                Log::error('VariantForm API Error: ' . $e->getMessage());
                                return [];
                            }
                        });
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(fn ($set) => $set('variant_features', [])),

                TextInput::make('buying_price')->numeric(),
                TextInput::make('price_before_discount')->numeric(),
                TextInput::make('discount')->numeric(),
                TextInput::make('price_after_discount')->numeric(),
                TextInput::make('stock')->numeric(),

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

    // --- Safe Helper Methods ---

    protected static function getFeaturesMap($productId)
    {
        if (!$productId) return [];
        $features = self::fetchProductFeatures((int)$productId);
        
        return collect($features)->pluck('name', 'id')
            ->mapWithKeys(fn($name, $id) => [(int)$id => $name])
            ->toArray();
    }

    protected static function getFeatureValues($productId, $featureId)
    {
        if (!$productId || !$featureId) return [];
        $features = self::fetchProductFeatures((int)$productId);
        $feature = collect($features)->firstWhere('id', (int)$featureId);
        
        return collect($feature['values'] ?? [])
            ->pluck('value', 'id') 
            ->mapWithKeys(fn($val, $id) => [(int)$id => $val])
            ->toArray();
    }

    protected static function fetchProductFeatures(int $productId): array
    {
        return Cache::remember("product_features_raw_{$productId}", 600, function () use ($productId) {
            try {
                $token = app(ApiTokenService::class)->getToken();
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json'
                ])->timeout(5)->get("https://bestrepairegypt.com/v1/products/{$productId}");
                
                return $response->json()['features'] ?? [];
            } catch (\Exception $e) {
                return [];
            }
        });
    }
}
