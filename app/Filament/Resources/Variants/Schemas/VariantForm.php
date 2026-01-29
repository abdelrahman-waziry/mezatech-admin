<?php

namespace App\Filament\Resources\Variants\Schemas;

use App\Models\Product;
use App\Services\ApiTokenService;
use App\Services\FeatureService;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VariantForm
{
    protected static ?array $featureCache = null;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Variant Details')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('product_id')
                                ->label('Product')
                                // FIX 1: Ensure we use options() to force Sushi to load
                                ->options(fn () => Product::all()->pluck('name', 'id')->toArray())
                                ->required()
                                ->searchable()
                                ->preload()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    // Clear variant features when product changes
                                    $set('variant_features', []);
                                })
                                ->helperText('Select a product first to enable variant features'),

                            TextInput::make('name')
                                ->label('Variant Name')
                                ->required()
                                ->maxLength(255),
                        ]),

                        Grid::make(3)->schema([
                            TextInput::make('buying_price')
                                ->label('Buying Price')
                                ->numeric()
                                ->prefix('EGP'),

                            TextInput::make('price_before_discount')
                                ->label('Price Before Discount')
                                ->numeric()
                                ->prefix('EGP'),

                            TextInput::make('discount')
                                ->label('Discount')
                                ->numeric()
                                ->prefix('EGP'),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('price_after_discount')
                                ->label('Price After Discount')
                                ->numeric()
                                ->prefix('EGP'),

                            TextInput::make('stock')
                                ->label('Stock')
                                ->numeric(),
                        ]),
                    ]),

                Section::make('Features')
                    ->schema([
                        Repeater::make('variant_features')
                            ->label('Variant Features')
                            ->hint('Add combinations of features and values that define this variant.')
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('feature_id')
                                        ->label('Feature')
                                        ->options(function (callable $get) {
                                            $productId = $get('../../product_id');
                                            if (!$productId) {
                                                return [];
                                            }
                                            return self::getCachedProductFeaturesMap($productId);
                                        })
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->live(onBlur: true)
                                        // Reset value when feature changes
                                        ->afterStateUpdated(fn ($state, callable $set) => $set('feature_value_id', null))
                                        ->disabled(fn (callable $get) => !$get('../../product_id')),

                                    // FIX 2: Renamed to feature_value_id to match EditVariant logic
                                    Select::make('feature_value_id') 
                                        ->label('Feature Value')
                                        ->options(function (callable $get) {
                                            $productId = $get('../../product_id');
                                            $featureId = $get('feature_id');

                                            if (!$productId || !$featureId) {
                                                return [];
                                            }

                                            return self::getCachedProductFeatureValues($productId, $featureId);
                                        })
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->disabled(fn (callable $get) => !$get('../../product_id') || !$get('feature_id')),
                                ]),
                            ])
                            ->defaultItems(0)
                            ->collapsible()
                            // Disable adding items if no product is selected
                            ->disabled(fn (callable $get) => !$get('product_id'))
                            // Label items for better UX
                            ->itemLabel(function (array $state, callable $get): ?string {
                                $productId = $get('../../product_id');
                                $featureId = $state['feature_id'] ?? null;
                                $valueId = $state['feature_value_id'] ?? null;

                                if (!$productId || !$featureId || !$valueId) {
                                    return 'New Feature';
                                }
                                
                                $featuresMap = self::getCachedProductFeaturesMap($productId);
                                $valuesMap = self::getCachedProductFeatureValues($productId, $featureId);
                                
                                $featureName = $featuresMap[$featureId] ?? 'Feature';
                                $valueName = $valuesMap[$valueId] ?? 'Value';
                                
                                return "{$featureName}: {$valueName}";
                            }),
                    ]),
            ]);
    }

    /**
     * Get features for a specific product (cached version)
     */
    protected static function getCachedProductFeatures(int $productId): array
    {
        $cacheKey = "product_features_{$productId}";
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function () use ($productId) {
            return self::getProductFeatures($productId);
        });
    }

    /**
     * Get features for a specific product
     */
    protected static function getProductFeatures(int $productId): array
    {
        try {
            $token = app(ApiTokenService::class)->getToken();
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->timeout(30)->get("https://bestrepairegypt.com/v1/products/{$productId}");

            if (!$response->successful()) {
                Log::error('Failed to fetch product features', ['productId' => $productId, 'status' => $response->status()]);
                return [];
            }

            $product = $response->json();
            return $product['features'] ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch product features', ['productId' => $productId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get features map for a specific product (id => name) - cached
     */
    protected static function getCachedProductFeaturesMap(int $productId): array
    {
        $cacheKey = "product_features_map_{$productId}";
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function () use ($productId) {
            return self::getProductFeaturesMap($productId);
        });
    }

    protected static function getProductFeaturesMap(int $productId): array
    {
        $features = self::getProductFeatures($productId);

        return collect($features)
            ->mapWithKeys(fn ($feature) => [
                $feature['id'] => $feature['name'] ?? ('Feature ' . $feature['id']),
            ])->toArray();
    }

    /**
     * Get feature values for a specific product and feature - cached
     */
    protected static function getCachedProductFeatureValues(int $productId, int $featureId): array
    {
        $cacheKey = "product_feature_values_{$productId}_{$featureId}";
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function () use ($productId, $featureId) {
            return self::getProductFeatureValues($productId, $featureId);
        });
    }

    /**
     * FIX 3: Return ID => Value mapping (not Value => Value)
     */
    protected static function getProductFeatureValues(int $productId, int $featureId): array
    {
        $features = self::getProductFeatures($productId);

        $feature = collect($features)->firstWhere('id', $featureId);

        if (!$feature || !isset($feature['values']) || !is_array($feature['values'])) {
            return [];
        }

        // We map ID as the key, because EditVariant is saving/loading IDs
        return collect($feature['values'])
            ->mapWithKeys(fn ($value) => [
                $value['id'] => $value['value'] ?? $value['name'] ?? ('Value ' . $value['id']),
            ])->toArray();
    }
}
