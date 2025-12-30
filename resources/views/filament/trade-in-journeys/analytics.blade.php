<x-filament::page>
    @php
        $statuses = $statusBreakdown;
        $priceData = $priceByCondition;
        $ratings = collect($priceData)->pluck('rating')->toArray();
        $avgPrices = collect($priceData)->pluck('avg_price')->toArray();
        $requestBuckets = $requestCounts ?? [];
        $requestLabels = array_keys($requestBuckets);
        $requestTotals = array_values($requestBuckets);
        $successFailure = $successFailure ?? [];
        $endpoints = $avgResponseByEndpoint ?? [];
    @endphp

    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach ($statuses as $status => $count)
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 border">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ \Illuminate\Support\Str::headline($status) }}</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ number_format($count) }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">journeys</p>
                </div>
            @endforeach
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg border p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Average Estimated Price by Condition</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Updated just now</p>
            </div>
            <canvas id="priceChart" height="140"></canvas>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg border p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">API Monitoring</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Live</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <canvas id="requestsChart" height="150"></canvas>
                </div>
                <div class="space-y-4">
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-dashed border-gray-200 dark:border-gray-700">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Success vs Failure</p>
                        <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white flex items-baseline space-x-3">
                            @foreach($successFailure as $label => $value)
                                <span>{{ \Illuminate\Support\Str::headline($label) }} {{ $value }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-dashed border-gray-200 dark:border-gray-700 space-y-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Avg response per endpoint</p>
                        @foreach($endpoints as $endpoint)
                            <div class="text-xs text-gray-600 dark:text-gray-300">
                                <span class="font-semibold">{{ $endpoint['endpoint'] }}:</span>
                                {{ number_format($endpoint['avg'], 0) }}ms
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg border p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Latest Conditions</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @foreach ($priceData as $entry)
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-dashed border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Condition rating</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $entry['rating'] }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Avg. price {{ number_format($entry['avg_price']) }} EGP</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <x-slot name="scripts">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('alpine:init', () => {
                const priceCtx = document.getElementById('priceChart');
                if (priceCtx) {
                    new Chart(priceCtx, {
                        type: 'line',
                        data: {
                            labels: @json($ratings),
                            datasets: [{
                                label: 'Avg estimated price (EGP)',
                                data: @json($avgPrices),
                                borderColor: '#2563eb',
                                backgroundColor: 'rgba(37, 99, 235, 0.2)',
                                fill: true,
                                tension: 0.35,
                            }],
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: value => new Intl.NumberFormat().format(value),
                                    },
                                },
                            },
                        },
                    });
                }

                const requestCtx = document.getElementById('requestsChart');
                if (requestCtx) {
                    new Chart(requestCtx, {
                        type: 'bar',
                        data: {
                            labels: @json($requestLabels),
                            datasets: [{
                                label: 'API requests',
                                data: @json($requestTotals),
                                backgroundColor: '#10b981',
                            }],
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true,
                                },
                            },
                        },
                    });
                }
            });
        </script>
    </x-slot>
</x-filament::page>

