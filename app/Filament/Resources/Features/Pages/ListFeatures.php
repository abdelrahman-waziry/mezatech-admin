<?php

namespace App\Filament\Resources\Features\Pages;

use App\Filament\Resources\Features\FeatureResource;
use App\Models\Feature;
use App\Models\Product;
use App\Services\ApiTokenService;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\Paginator as BasePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ListFeatures extends ListRecords
{
    protected static string $resource = FeatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function makeTable(): \Filament\Tables\Table
    {
        $table = \Filament\Tables\Table::make($this)
            ->records(fn (): Collection => $this->getFeatureRecords());

        FeatureResource::configureTable($table);

        return $table;
    }

    protected function getFeatureRecords(): Collection
    {
        try {
            // Get product filter if applied
            $productId = $this->getTableFilterState('product_id')['value'] ?? null;

            // Fetch all products to get their features
            $token = app(ApiTokenService::class)->getToken();
            $productsResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->timeout(30)->get('https://bestrepairegypt.com/v1/products');

            $productsData = $productsResponse->json() ?? [];
            $products = $productsData['products'] ?? (is_array($productsData) ? $productsData : []);

            // Build product lookup cache
            $productCache = [];
            foreach ($products as $product) {
                // Ensure $product is an array
                if (!is_array($product)) {
                    continue;
                }
                $productCache[$product['id']] = $product['name'] ?? 'Unknown Product';
            }

            // Collect all features from all products (or filtered product)
            $allFeatures = collect();

            foreach ($products as $product) {
                // Ensure $product is an array
                if (!is_array($product)) {
                    continue;
                }
                
                // Apply product filter if set
                if ($productId && $product['id'] != $productId) {
                    continue;
                }

                $productIdValue = $product['id'] ?? null;
                $productName = $productCache[$productIdValue] ?? 'Unknown Product';
                $features = $product['features'] ?? [];

                foreach ($features as $feature) {
                    $values = $feature['values'] ?? [];
                    $valuesSummary = collect($values)->pluck('value')->implode(', ');

                    $allFeatures->push([
                        'id' => $feature['id'] ?? null,
                        'name' => $feature['name'] ?? 'Unknown Feature',
                        'product_id' => $productIdValue,
                        'product_name' => $productName,
                        'values' => $values,
                        'values_count' => count($values),
                        'values_summary' => $valuesSummary,
                        'created_at' => $product['createdAt'] ?? now(),
                    ]);
                }
            }

            // Sort by product name, then feature name
            return $allFeatures
                ->filter(fn ($feature) => $feature['id'] !== null)
                ->sortBy([
                    ['product_name', 'asc'],
                    ['name', 'asc'],
                ])
                ->values();
        } catch (\Exception $e) {
            Log::error('Failed to fetch features: ' . $e->getMessage());
            return collect();
        }
    }

    public function updatedTableFilters(): void
    {
        // Clear any caches if needed when filters change
    }
}
