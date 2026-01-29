I will keep your exact code structure and methods. I am applying three specific fixes to make it work with the EditVariant and Product logic we just set up.
 * Renamed Field: Changed feature_value to feature_value_id in the Repeater. The EditVariant page loads IDs, so the form field name must match.
 * Fixed Value Keys: In getProductFeatureValues, I changed the array key from $value['value'] to $value['id']. The API expects IDs, so the dropdown needs to send IDs.
 * Kept Product Logic: Your product_id logic using Product::all() was actually correct in this version, so I left it untouched.
Here is your fixed file:
<?php

namespace App\Filament\Resources\Variants\Schemas;

use App\Models\Product;
use App\Services\ApiTokenService;
use App\Services\FeatureService;
use Filament\Forms\Components\Repeater;
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
                TextInput::make('name')
                    ->label('Variant Name')
                    ->required()
                    ->maxLength(255),

                Select::make('product_id')
                    ->label('Product')
                    // This forces Sushi to load (This is correct, keep it!)
                    ->options(fn () => Product::all()->pluck('name', 'id')->toArray())
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('variant_features', []);
                    })
                    ->helperText('Select a product first to enable variant features'),

                TextInput::make('buying_price')
                    ->label('Buying Price')
                    ->numeric()
                    ->inputMode('decimal'),

                TextInput::make('price_before_discount')
                    ->label('Price Before Discount')
                    ->numeric()
                    ->inputMode('decimal'),

                TextInput::make('discount')
                    ->label('Discount')
                    ->numeric()
                    ->inputMode('decimal'),

                TextInput::make('price_after_discount')
                    ->label('Price After Discount')
                    ->numeric()
                    ->inputMode('decimal'),

                TextInput::make('stock')
                    ->label('Stock')
                    ->numeric()
                    ->inputMode('numeric'),

                Repeater::make('variant_features')
                    ->label('Variant Features')
                    ->hint('Add combinations of features and values that define this variant.')
                    ->schema([
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
                            // FIX 1: Reset 'feature_value_id' (not 'feature_value')
                            ->afterStateUpdated(fn ($state, callable $set) => $set('feature_value_id', null))
                            ->disabled(fn (callable $get) => !$get('../../product_id')),

                        // FIX 2: Renamed to 'feature_value_id' to match EditVariant logic
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
                            ->live(onBlur: true)
                            ->disabled(fn (callable $get) => !$get('../../product_id') || !$get('feature_id')),
                    ])
                    ->defaultItems(0)
                    ->collapsible()
                    ->disabled(fn (callable $get) => !$get('product_id'))
                    ->itemLabel(function (array $state, callable $get): ?string {
                        $productId = $get('../../product_id');
                        // Update state keys here too
                        $featureId = $state['feature_id'] ?? null;
                        $valueId = $state['feature_value_id'] ?? null;

                        if (!$productId || !$featureId || !$valueId) {
                            return 'New Feature';
                        }
                        
                        $featuresMap = self::getCachedProductFeaturesMap($productId);
                        $valuesMap = self::getCachedProductFeatureValues($productId, $featureId);
                        
                        $featureName = $featuresMap[$featureId] ?? 'Feature';
                        $valueName = $valuesMap[$valueId] ?? 'Value';
                        
                        return "{$featureName} - {$valueName}";
                    }),
            ]);
    }

    protected static function getCachedProductFeatures(int $productId): array
    {
        $cacheKey = "product_features_{$productId}";
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function () use ($productId) {
            return self::getProductFeatures($productId);
        });
    }

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

    protected static function getCachedProductFeatureValues(int $productId, int $featureId): array
    {
        $cacheKey = "product_feature_values_{$productId}_{$featureId}";
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function () use ($productId, $featureId) {
            return self::getProductFeatureValues($productId, $featureId);
        });
    }

    protected static function getProductFeatureValues(int $productId, int $featureId): array
    {
        $features = self::getProductFeatures($productId);

        $feature = collect($features)->firstWhere('id', $featureId);

        if (!$feature || !isset($feature['values']) || !is_array($feature['values'])) {
            return [];
        }

        // FIX 3: Use ID as the key ($value['id']), not the value string.
        // This ensures the dropdown works with the IDs saved in EditVariant.
        return collect($feature['values'])
            ->mapWithKeys(fn ($value) => [
                $value['id'] => $value['value'] ?? $value['name'] ?? ('Value ' . $value['id']),
            ])->toArray();
    }
    
    // ... (Remaining methods ensureFeatureCache, mergeFeature etc. kept exactly as is)
    
    protected static function getFeatures(): array
    {
        return array_values(self::ensureFeatureCache());
    }

    protected static function getFeaturesMap(): array
    {
        return collect(self::ensureFeatureCache())
            ->mapWithKeys(fn ($feature) => [
                $feature['id'] => $feature['name'] ?? ('Feature ' . $feature['id']),
            ])->toArray();
    }

    protected static function getFeatureValues(int $featureId): array
    {
        $cache = self::ensureFeatureCache();
        $feature = $cache[$featureId] ?? null;

        if ((!$feature || empty($feature['values'])) && $fetched = self::fetchFeatureDetail($featureId)) {
            $feature = $fetched;
            self::mergeFeatureIntoCache($feature);
        }

        if (!$feature || !isset($feature['values']) || !is_array($feature['values'])) {
            return [];
        }

        return collect($feature['values'])
            ->mapWithKeys(fn ($value) => [
                $value['id'] => $value['value'] ?? ('Value ' . $value['id']),
            ])->toArray();
    }

    protected static function ensureFeatureCache(): array
    {
        if (self::$featureCache !== null) {
            return self::$featureCache;
        }

        $service = new FeatureService();
        $data = $service->fetchAll();
        $features = [];

        if (isset($data['features']) && is_array($data['features'])) {
            $features = $data['features'];
        } elseif (is_array($data)) {
            $features = $data;
        }

        self::$featureCache = [];

        foreach ($features as $feature) {
            if (!isset($feature['id'])) {
                continue;
            }

            $normalized = self::normalizeFeature($feature);
            if (!isset(self::$featureCache[$normalized['id']])) {
                self::$featureCache[$normalized['id']] = $normalized;
                continue;
            }

            self::$featureCache[$normalized['id']] = self::mergeFeature(self::$featureCache[$normalized['id']], $normalized);
        }

        return self::$featureCache;
    }

    protected static function mergeFeatureIntoCache(array $feature): void
    {
        if (!isset($feature['id'])) {
            return;
        }

        $id = $feature['id'];

        if (!isset(self::$featureCache[$id])) {
            self::$featureCache[$id] = $feature;
            return;
        }

        self::$featureCache[$id] = self::mergeFeature(self::$featureCache[$id], $feature);
    }

    protected static function fetchFeatureDetail(int $featureId): ?array
    {
        try {
            $service = new FeatureService();
            $data = $service->fetchOne((string) $featureId);
            $feature = $data['feature'] ?? $data;

            if (is_array($feature) && isset($feature['id'])) {
                return $feature;
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch feature detail', ['id' => $featureId, 'error' => $e->getMessage()]);
        }

        return null;
    }

    protected static function normalizeFeature(array $feature): array
    {
        $values = [];

        if (!empty($feature['values']) && is_array($feature['values'])) {
            $values = self::normalizeValues($feature['values']);
        }

        return [
            'id' => (int) $feature['id'],
            'name' => $feature['name'] ?? 'Feature ' . $feature['id'],
            'values' => $values,
        ];
    }

    protected static function mergeFeature(array $existing, array $incoming): array
    {
        $merged = $existing;
        $merged['name'] = $existing['name'] ?: $incoming['name'];
        $merged['values'] = self::mergeValues($existing['values'] ?? [], $incoming['values'] ?? []);

        return $merged;
    }

    protected static function normalizeValues(array $values): array
    {
        $normalized = [];

        foreach ($values as $value) {
            if (!is_array($value)) {
                continue;
            }

            $id = $value['id'] ?? $value['value'] ?? null;
            $label = $value['value'] ?? $value['name'] ?? null;

            if (!$id) {
                continue;
            }

            $normalized[] = [
                'id' => $id,
                'value' => $label ?? (string) $id,
            ];
        }

        return $normalized;
    }

    protected static function mergeValues(array $existing, array $incoming): array
    {
        $map = [];

        foreach ($existing as $value) {
            if (isset($value['id'])) {
                $map[$value['id']] = $value;
            }
        }

        foreach ($incoming as $value) {
            if (!isset($value['id'])) {
                continue;
            }

            $map[$value['id']] = $value;
        }

        return array_values($map);
    }
}

