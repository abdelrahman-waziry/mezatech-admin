<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;

class ProductsChart extends ChartWidget
{
    protected ?string $heading = 'Products by Brand';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'half';

    protected function getData(): array
    {
        $products = Product::query()->get();

        $grouped = $products
            ->groupBy('brand_name')
            ->map(fn ($items) => $items->count())
            ->sortDesc();

        return [
            'datasets' => [
                [
                    'label' => 'Products',
                    'data' => $grouped->values()->all(),
                ],
            ],
            'labels' => $grouped->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
