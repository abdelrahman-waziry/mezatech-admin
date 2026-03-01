<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AnalyticsStatsOverview;

use App\Filament\Widgets\MostTradedVariantsWidget;

use App\Filament\Widgets\TradeInDemandChart;
use App\Filament\Widgets\TradeInStatusRatioWidget;
use App\Filament\Widgets\TrafficChart;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    protected string $view = 'filament.pages.dashboard';

    public function getTrafficWidgets(): array
    {
        return [
            AnalyticsStatsOverview::class,
            TrafficChart::class,
        ];
    }

    public function getTradeInWidgets(): array
    {
        return [
            MostTradedVariantsWidget::class,
            TradeInStatusRatioWidget::class,
            TradeInDemandChart::class,
        ];
    }


    // We override content to prevent Filament from automatically 
    // trying to render the widgets using the native grid system again.
    public function content(Schema $schema): Schema
    {
        return $schema;
    }

    public function getFilteredWidgets(array $widgets): array
    {
        return array_filter($widgets, fn (string $widget): bool => $widget::canView());
    }
}

