<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section collapsible icon="heroicon-o-chart-bar">
            <x-slot name="heading">
                Traffic & Analytics
            </x-slot>

            <x-slot name="description">
                Website traffic, request stats, and endpoint performance
            </x-slot>

            <x-filament-widgets::widgets
                :widgets="$this->getFilteredWidgets($this->getTrafficWidgets())"
                :columns="$this->getColumns()"
                :data="$this->getWidgetData()"
            />
        </x-filament::section>

        <div style="margin-top: 2rem;">
            <x-filament::section collapsible icon="heroicon-o-device-phone-mobile">
            <x-slot name="heading">
                Trade-In Insights
            </x-slot>

            <x-slot name="description">
                Trade-in demand, most traded variants, and acceptance ratios
            </x-slot>

            <x-filament-widgets::widgets
                :widgets="$this->getFilteredWidgets($this->getTradeInWidgets())"
                :columns="$this->getColumns()"
                :data="$this->getWidgetData()"
            />
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>
