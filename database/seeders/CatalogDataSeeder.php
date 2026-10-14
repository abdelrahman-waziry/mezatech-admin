<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CatalogDataSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/catalog');
        $read = static fn (string $name): array => json_decode(
            File::get($path.'/'.$name.'.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $date = static fn (?string $value): string => $value
            ? Carbon::parse($value)->format('Y-m-d H:i:s')
            : now()->format('Y-m-d H:i:s');

        DB::transaction(function () use ($read, $date): void {
            $now = now();
            $brands = $read('brands');
            $tags = $read('tags');
            $products = $read('products');
            $features = $read('features');
            $variants = $read('variants');
            $parts = $read('parts');
            $conditions = [
                ['id' => 1, 'name' => 'Excellent', 'description' => 'Fully functional with minimal signs of use.', 'price_modifier' => 1.00],
                ['id' => 2, 'name' => 'Good', 'description' => 'Functional with normal signs of use.', 'price_modifier' => 0.85],
                ['id' => 3, 'name' => 'Fair', 'description' => 'Functional with visible wear or defects.', 'price_modifier' => 0.65],
                ['id' => 4, 'name' => 'Poor', 'description' => 'Heavily worn or partially functional.', 'price_modifier' => 0.40],
                ['id' => 5, 'name' => 'Non-functional', 'description' => 'Does not function and is valued for parts or waste.', 'price_modifier' => 0.00],
            ];

            DB::table('conditions')->upsert(array_map(fn (array $condition): array => [
                ...$condition,
                'created_at' => $now,
                'updated_at' => $now,
            ], $conditions), ['id'], ['name', 'description', 'price_modifier', 'updated_at']);

            DB::table('brands')->upsert(array_map(fn (array $brand): array => [
                'id' => $brand['id'],
                'name' => $brand['name'],
                'image' => $brand['image'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ], $brands), ['id'], ['name', 'image', 'updated_at']);

            DB::table('tags')->upsert(array_map(fn (array $tag): array => [
                'id' => $tag['id'],
                'name' => $tag['name'],
                'created_at' => $now,
                'updated_at' => $now,
            ], $tags), ['id'], ['name', 'updated_at']);

            $brandIds = DB::table('brands')->pluck('id', 'name');
            $tagIds = DB::table('tags')->pluck('id', 'name');

            DB::table('products')->upsert(array_map(fn (array $product): array => [
                'id' => $product['id'],
                'name' => $product['name'],
                'sku' => $product['sku'],
                'description' => null,
                'condition' => $product['condition'] ?? 0,
                'notes' => null,
                'minimum_buying_price' => $product['minimum_buying_price'] ?? 0,
                'waste_price' => $product['waste_price'] ?? 0,
                'brand_id' => $brandIds[$product['brand_name'] ?? ''] ?? null,
                'created_at' => $date($product['created_at'] ?? null),
                'updated_at' => $now,
            ], $products), ['id'], ['name', 'sku', 'condition', 'minimum_buying_price', 'waste_price', 'brand_id', 'updated_at']);

            foreach ($products as $product) {
                foreach (array_filter(array_map('trim', explode(',', $product['tags_summary'] ?? ''))) as $tagName) {
                    if (isset($tagIds[$tagName])) {
                        DB::table('product_tag')->insertOrIgnore([
                            'product_id' => $product['id'],
                            'tag_id' => $tagIds[$tagName],
                        ]);
                    }
                }
            }

            DB::table('features')->upsert(array_map(fn (array $feature): array => [
                'id' => $feature['id'],
                'name' => $feature['name'],
                'product_id' => $feature['product_id'] ?? null,
                'values_summary' => $feature['values_summary'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ], $features), ['id'], ['name', 'product_id', 'values_summary', 'updated_at']);

            DB::table('variants')->upsert(array_map(fn (array $variant): array => [
                'id' => $variant['id'],
                'product_id' => $variant['product_id'],
                'name' => $variant['name'],
                'buying_price' => $variant['buying_price'] ?? 0,
                'price_before_discount' => $variant['price_before_discount'] ?? 0,
                'discount' => $variant['discount'] ?? 0,
                'price_after_discount' => $variant['price_after_discount'] ?? 0,
                'stock' => $variant['stock'] ?? 0,
                'created_at' => $date($variant['created_at'] ?? null),
                'updated_at' => $date($variant['updated_at'] ?? null),
            ], $variants), ['id'], ['product_id', 'name', 'buying_price', 'price_before_discount', 'discount', 'price_after_discount', 'stock', 'updated_at']);

            DB::table('parts')->upsert(array_map(fn (array $part): array => [
                'id' => $part['id'],
                'product_id' => $part['product_id'],
                'name' => $part['name'],
                'price' => $part['price'] ?? 0,
                'type' => $part['type'] ?? null,
                'condition' => $part['condition'] ?? null,
                'info' => isset($part['info'])
                    ? json_encode($part['info'], JSON_THROW_ON_ERROR)
                    : null,
                'created_at' => $date($part['created_at'] ?? null),
                'updated_at' => $date($part['updated_at'] ?? null),
            ], $parts), ['id'], ['product_id', 'name', 'price', 'type', 'condition', 'info', 'updated_at']);
        });
    }
}