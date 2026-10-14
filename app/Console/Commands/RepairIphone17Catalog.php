<?php

namespace App\Console\Commands;

use App\Services\ApiTokenService;
use App\Services\VariantService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class RepairIphone17Catalog extends Command
{
    protected $signature = 'catalog:repair-iphone17 {--apply : Persist the repaired variant mappings}';

    protected $description = 'Repair iPhone 17 storage variant and variant-feature mappings';

    public function handle(ApiTokenService $tokenService, VariantService $variantService): int
    {
        $headers = [
            'Authorization' => 'Bearer ' . $tokenService->getToken(),
            'Accept' => 'application/json',
        ];

        $productResponse = Http::withHeaders($headers)->get('https://bestrepairegypt.com/v1/products');
        if (!$productResponse->successful()) {
            $this->error('Could not retrieve products from the catalog API.');
            return self::FAILURE;
        }

        $products = $productResponse->json('products') ?? $productResponse->json();
        $targetNames = ['iPhone 17', 'iPhone 17 Pro', 'iPhone 17 Pro Max'];
        $changed = 0;

        foreach ($targetNames as $productName) {
            $product = collect($products)->firstWhere('name', $productName);
            if (!$product) {
                $this->error("Product not found: {$productName}");
                return self::FAILURE;
            }

            $storageFeature = collect($product['features'] ?? [])->first(
                fn (array $feature) => strcasecmp($feature['name'] ?? '', 'Storage') === 0
            );
            $storageValues = collect($storageFeature['values'] ?? [])
                ->keyBy(fn (array $value) => $this->normalizeStorage($value['value'] ?? ''));

            if (!$storageFeature || $storageValues->isEmpty()) {
                $this->error("Storage feature is incomplete for {$productName}.");
                return self::FAILURE;
            }

            $variantResponse = Http::withHeaders($headers)->get(
                'https://bestrepairegypt.com/v1/variants',
                ['productId' => $product['id']]
            );
            $variants = $variantResponse->json('variants')
                ?? $variantResponse->json('data')
                ?? $variantResponse->json();

            foreach (is_array($variants) ? $variants : [] as $variant) {
                $storage = $this->extractStorage($variant['name'] ?? '');
                $value = $storageValues->get($storage);

                if (!$value) {
                    $this->error("Storage value not found for variant {$variant['id']}: {$variant['name']}");
                    return self::FAILURE;
                }

                $expectedLinks = [[
                    'feature_id' => (int) $storageFeature['id'],
                    'feature_value_id' => (int) $value['id'],
                ]];
                $currentLinks = collect($variant['variantFeatures'] ?? $variant['variant_features'] ?? [])
                    ->map(fn (array $link) => [
                        'feature_id' => (int) data_get($link, 'feature.id', $link['feature_id'] ?? 0),
                        'feature_value_id' => (int) data_get($link, 'featureValue.id', $link['feature_value_id'] ?? 0),
                    ])->values()->all();
                $normalizedName = preg_replace('/1TGB/i', '1TB', $variant['name'] ?? '');

                if ($currentLinks === $expectedLinks && $normalizedName === ($variant['name'] ?? '')) {
                    $this->line("OK {$variant['id']} {$variant['name']} -> {$storage}");
                    continue;
                }

                $this->line(($this->option('apply') ? 'REPAIR' : 'WOULD REPAIR') . " {$variant['id']} {$variant['name']} -> {$storage}");
                if (!$this->option('apply')) {
                    continue;
                }

                $variantService->update((string) $variant['id'], [
                    'name' => $normalizedName,
                    'buying_price' => $variant['buyingPrice'] ?? $variant['buying_price'] ?? 0,
                    'price_before_discount' => $variant['priceBeforeDiscount'] ?? $variant['price_before_discount'] ?? 0,
                    'discount' => $variant['discount'] ?? 0,
                    'price_after_discount' => $variant['priceAfterDiscount'] ?? $variant['price_after_discount'] ?? 0,
                    'stock' => $variant['stock'] ?? 0,
                    'product_id' => (int) $product['id'],
                    'variant_features' => $expectedLinks,
                ]);
                $changed++;
            }
        }

        $this->info($this->option('apply') ? "Repaired {$changed} variant(s)." : 'Dry run complete. Re-run with --apply to persist repairs.');
        return self::SUCCESS;
    }

    private function extractStorage(string $name): string
    {
        if (preg_match('/(256GB|512GB|1TB|1TGB)/i', $name, $matches)) {
            return $this->normalizeStorage($matches[1]);
        }

        return '';
    }

    private function normalizeStorage(string $storage): string
    {
        return strtoupper(str_replace('1TGB', '1TB', trim($storage)));
    }
}