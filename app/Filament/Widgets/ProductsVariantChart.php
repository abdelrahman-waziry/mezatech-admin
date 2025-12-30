<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Variant;
use Filament\Widgets\ChartWidget;

class ProductsVariantChart extends ChartWidget
{
    protected ?string $heading = 'Products by Variant';

    // Half-width so it can sit side by side with another chart
    protected int|string|array $columnSpan = 'half';

    public ?string $filter = null;

    protected function getType(): string
    {
        return 'bar';
    }

    public function mount(): void
    {
        if (! filled($this->filter)) {
            $defaultProductId = Product::query()->orderBy('name')->value('id');
            if ($defaultProductId) {
                $this->filter = (string) $defaultProductId;
            }
        }

        parent::mount();
    }

    protected function getData(): array
    {
        $productId = $this->resolveProductId();

        if (! $productId) {
            return $this->emptyDataset();
        }

        Variant::$currentProductId = $productId;
        $variantRows = (new Variant())->getRows();
        Variant::$currentProductId = null;

        $counts = [];

        foreach ($variantRows as $variant) {
            $name = $variant['name'] ?? 'Unnamed variant';
            $counts[$name] = ($counts[$name] ?? 0) + 1;
        }

        if (empty($counts)) {
            return $this->emptyDataset();
        }

        $colors = [
            '#60a5fa', '#f472b6', '#34d399', '#fbbf24', '#f87171', '#a78bfa', '#fcd34d'
        ];

        return [
            'labels' => array_keys($counts),
            'datasets' => [
                [
                    'label' => $this->getDatasetLabel($productId),
                    'data' => array_values($counts),
                    'backgroundColor' => array_slice($colors, 0, count($counts)),
                    'borderColor' => ['#ffffff'],
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function emptyDataset(): array
    {
        return [
            'labels' => ['No variants'],
            'datasets' => [
                [
                    'label' => 'Variants',
                    'data' => [0],
                    'backgroundColor' => ['#e5e7eb'],
                    'borderColor' => ['#ffffff'],
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    protected function getFilters(): ?array
    {
        $products = Product::query()
            ->orderBy('name')
            ->pluck('name', 'id');

        return ['' => 'Select a product'] + $products->toArray();
    }

    public function updatedFilter(): void
    {
        $this->cachedData = null;
        $this->dataChecksum = null;
    }

    protected function resolveProductId(): ?int
    {
        if (filled($this->filter)) {
            return (int) $this->filter;
        }

        return Product::query()->orderBy('name')->value('id');
    }

    protected function getDatasetLabel(int $productId): string
    {
        return Product::find($productId)?->name ?? 'Variants';
    }
}
