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
    public function calculate(
        int $variantId,
        array $partsWithValuesMap,
        array $repairedPartsBefore = []
    ): array
    {
        $variant = Variant::findOrFail($variantId);

        $parts = Part::where('product_id', $variant->product_id)->get()->keyBy('id');

        $price = (float) ($variant->buying_price ?? 0);
        $minimumPrice = (float) ($variant->product?->minimum_buying_price ?? 0);
        $wastePrice = (float) ($variant->product?->waste_price ?? 0);
        $checkedTypes = [];
        $functionalFailure = false;
        $selectedParts = collect($partsWithValuesMap)
            ->mapWithKeys(fn ($value, $partId) => [(string) $partId => (int) $value]);

        $partByType = fn (int $type) => $parts->first(fn (Part $part) => (int) $part->type === $type);
        $valueFor = fn (?Part $part): ?int => $part
            ? $selectedParts->get((string) $part->id)
            : null;

        $functionalPart = $partByType(0);
        $functionalValue = $valueFor($functionalPart);
        if ($functionalPart && $functionalValue !== null) {
            $checkedTypes[] = 0;
            if ($functionalValue !== 1) {
                $price = $wastePrice;
                $functionalFailure = true;
            }
        }

        if (! $functionalFailure) {
            $repairedPart = $partByType(1);
            $repairedValue = $valueFor($repairedPart);
            if ($repairedPart && $repairedValue !== null) {
                $checkedTypes[] = 1;
                if ($repairedValue === 1) {
                    foreach ($repairedPartsBefore as $repairedPartId) {
                        $previousPart = $parts->get((int) $repairedPartId);
                        if ($previousPart) {
                            $price -= (float) $previousPart->price;
                            $checkedTypes[] = (int) $previousPart->type;
                        }
                    }
                }
            }

            $framePart = $partByType(4);
            $frameValue = $valueFor($framePart);
            if ($framePart && $frameValue !== null) {
                $price -= $this->bandPrice($framePart, $frameValue === 0 ? 1 : 2);
                $checkedTypes = array_merge($checkedTypes, $frameValue === 0 ? [4, 29, 30, 31, 32] : [4]);
            }

            $trueTonePart = $partByType(18);
            $trueToneValue = $valueFor($trueTonePart);
            if ($trueTonePart && $trueToneValue !== null) {
                if ($trueToneValue === 0) {
                    $price -= (float) $trueTonePart->price;
                    $checkedTypes[] = 3;
                }
                $checkedTypes[] = 18;
            }
        }

        foreach ($selectedParts as $partId => $value) {
            $part = $parts->get((int) $partId);

            if (! $part) {
                continue;
            }

            $type = (int) $part->type;

            if (in_array($type, [2, 3, 29, 30, 31, 32], true)) {
                if ($type === 2) {
                    $info = is_array($part->info) ? $part->info : [];
                    $price -= $value < 85
                        ? (float) ($info['price1'] ?? 0)
                        : ($value < 90
                            ? (float) ($info['price2'] ?? 0)
                            : ($value < 98 ? (float) ($info['price3'] ?? 0) : 0));
                } elseif ($value === 0) {
                    $price -= $this->bandPrice($part, 1);
                } elseif ($value === 1) {
                    $price -= $this->bandPrice($part, 2);
                }
                $checkedTypes[] = $type;
                if ($price <= $minimumPrice) {
                    $price = $minimumPrice;
                    break;
                }
                continue;
            }

            if (! in_array($type, $checkedTypes, true) && $value === 0) {
                $price -= (float) $part->price;
            }

            $checkedTypes[] = $type;
            if ($price <= $minimumPrice) {
                $price = $minimumPrice;
                break;
            }
        }

        return [
            'variant' => $variant,
            'variantPrice' => (float) ($variant->buying_price ?? 0),
            'partsTotal' => (float) ($price - (float) ($variant->buying_price ?? 0)),
            'finalPrice' => max($minimumPrice, (float) $price),
        ];
    }

    protected function bandPrice(Part $part, int $priceNumber): float
    {
        $info = is_array($part->info) ? $part->info : [];

        return (float) ($info['price'.$priceNumber] ?? 0);
    }
}
