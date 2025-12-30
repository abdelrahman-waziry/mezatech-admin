<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ProductsChart;
use App\Filament\Widgets\ProductsConditionChart;
use App\Filament\Widgets\ProductsVariantChart;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        return [
            ProductsChart::class,
            ProductsConditionChart::class,
            ProductsVariantChart::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }
}
