<?php

namespace App\Services;

use App\Models\Part;
use App\Models\Variant;

class PriceCalculationService
{
    /**
     * Calculate the final price for a variant given a map of part_id => selected value.
     *
     * @param  array<int|string, mixed>  $partsWithValuesMap
     * @return array{variant: Variant, variantPrice: float, partsTotal: float, finalPrice: float}
     */
    public function calculate(int $variantId, array $partsWithValuesMap): array
    {
        $variant = Variant::findOrFail($variantId);

        // Part is a Sushi model that only fetches rows for the product currently set here.
        Part::$currentProductId = $variant->product_id;
        $parts = Part::all()->keyBy('id');

        $variantPrice = $variant->price_after_discount ?? $variant->price_before_discount ?? 0;
        $partsTotal = 0;

        foreach ($partsWithValuesMap as $partId => $value) {
            $part = $parts->get($partId);

            if (! $part) {
                continue;
            }

            if ($part->info && is_array($part->info) && isset($part->info['price'.$value])) {
                $partsTotal += $part->info['price'.$value];
            } else {
                $partsTotal += $part->price * $value;
            }
        }

        return [
            'variant' => $variant,
            'variantPrice' => (float) $variantPrice,
            'partsTotal' => (float) $partsTotal,
            'finalPrice' => (float) ($variantPrice + $partsTotal),
        ];
    }
}
