<?php

namespace App\Filament\Resources\VariantFeatures\Pages;

use App\Filament\Resources\VariantFeatures\VariantFeatureResource;
use App\Models\Product;
use App\Models\Variant;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ListVariantFeatures extends ListRecords
{
    protected array $variantFeatureCache = [];
    protected static string $resource = VariantFeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function makeTable(): \Filament\Tables\Table
    {
        $table = \Filament\Tables\Table::make($this)
            ->records(fn (): Collection => $this->variantFeatureRecords());

        VariantFeatureResource::configureTable($table);

        return $table;
    }

    public function updatedTableFilters(): void
    {
        $this->variantFeatureCache = [];
    }

    protected function variantFeatureRecords(): Collection
    {
        $productId = $this->resolveSelectedProductId();

        if (! $productId) {
            return collect();
        }

        if (isset($this->variantFeatureCache[$productId])) {
            return $this->variantFeatureCache[$productId];
        }

        $variants = Variant::with('variantFeatures.feature')->where('product_id', $productId)->get();
        $productName = Product::find($productId)?->name ?? 'Unknown product';
        $features = \App\Models\Feature::where('product_id', $productId)->get()->map(fn ($feature) => [
            'id' => $feature->id,
            'name' => $feature->name,
            'values' => [],
        ])->all();
        $rawVariants = $variants->map(fn ($variant) => [
            'name' => $variant->name,
            'variantFeatures' => $variant->variantFeatures->map(fn ($link) => [
                'feature_id' => $link->feature_id,
                'feature_value_id' => $link->feature_value_id,
            ])->all(),
        ])->all();

        // Build lookup caches for features and feature values
        // Features are nested within the product, and each feature has its values
        // Use integer keys for consistent type matching
        $featuresCache = [];
        $featureValuesCache = [];
        
        foreach ($features as $feature) {
            $featureId = $feature['id'] ?? null;
            if ($featureId) {
                // Convert to integer for consistent lookup
                $featureId = (int) $featureId;
                $featuresCache[$featureId] = $feature['name'] ?? 'Unknown Feature';
                
                // Extract feature values from the nested values array
                $values = is_array($feature['values'] ?? null) ? $feature['values'] : [];
                foreach ($values as $value) {
                    $valueId = $value['id'] ?? null;
                    if ($valueId) {
                        // Convert to integer for consistent lookup
                        $valueId = (int) $valueId;
                        $featureValuesCache[$valueId] = $value['value'] ?? $value['name'] ?? 'Unknown Value';
                    }
                }
            }
        }

        $records = collect();

        foreach ($rawVariants as $variant) {
            $variantFeatures = Arr::get($variant, 'variantFeatures') ?? [];

            foreach ($variantFeatures as $featureRow) {
                // Try multiple possible structures for feature ID
                $featureId = Arr::get($featureRow, 'feature.id') 
                    ?? Arr::get($featureRow, 'featureId')
                    ?? Arr::get($featureRow, 'feature_id')
                    ?? null;
                
                // Try multiple possible structures for feature value ID
                $featureValueId = Arr::get($featureRow, 'featureValue.id')
                    ?? Arr::get($featureRow, 'featureValueId')
                    ?? Arr::get($featureRow, 'feature_value_id')
                    ?? null;

                // Convert to integers for consistent lookup
                $featureId = $featureId ? (int) $featureId : null;
                $featureValueId = $featureValueId ? (int) $featureValueId : null;

                // Skip if we don't have both IDs
                if (!$featureId || !$featureValueId) {
                    continue;
                }

                // Get feature name from cache
                $featureName = $featuresCache[$featureId] ?? 'Unknown Feature (ID: ' . $featureId . ')';

                // Get feature value from cache
                $featureValue = $featureValuesCache[$featureValueId] ?? 'Unknown Value (ID: ' . $featureValueId . ')';

                $records->push([
                    'product_name' => $productName,
                    'variant_name' => $variant['name'] ?? 'Unknown variant',
                    'feature_name' => $featureName,
                    'feature_value' => $featureValue,
                ]);
            }
        }
        

        return $this->variantFeatureCache[$productId] = $records;
    }

    protected function resolveSelectedProductId(): ?int
    {
        $filters = $this->getTableFilterState('product_id') ?? [];
        $value = $filters['value'] ?? null;

        if (filled($value)) {
            return (int) $value;
        }

        return Product::query()->orderBy('name')->value('id');
    }
}
