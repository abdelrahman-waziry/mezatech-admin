<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;

class ProductsConditionChart extends ChartWidget
{
    protected ?string $heading = 'Products by Condition';

    protected int|string|array $columnSpan = 'half'; 

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        // Map condition integers to readable labels
        $conditionLabels = [
            0 => 'New',
            1 => 'Refurbished',
            2 => 'Damaged',
        ];

        $products = Product::query()->get();

        // Group by condition and count
        $grouped = $products->groupBy('condition')
            ->map(fn($items, $key) => $items->count());

        // Make sure all condition labels are represented
        foreach ($conditionLabels as $key => $label) {
            if (!isset($grouped[$key])) {
                $grouped[$key] = 0;
            }
        }

        return [
            'labels' => array_values($conditionLabels),
            'datasets' => [
                [
                    'label' => 'Products',
                    'data' => array_values($grouped->toArray()),
                ],
            ],
        ];
    }
}
