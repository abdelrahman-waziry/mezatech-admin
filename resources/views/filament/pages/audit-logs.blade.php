<x-filament-panels::page>
    @vite(['resources/css/app.css'])
    <div x-data="auditLogDetail()" class="space-y-6">

        <x-filament::section>
            <div class="flex flex-col gap-4">

                <!-- Filters & Actions Bar -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">

                    <!-- Search & Filters -->
                    <div class="flex flex-wrap items-center gap-2 flex-grow">
                        <!-- Search Input -->
                        <div class="w-full md:w-64">
                            <x-filament::input.wrapper icon="heroicon-m-magnifying-glass">
                                <x-filament::input
                                    type="text"
                                    wire:model.live.debounce.500ms="search"
                                    placeholder="Search logs..."
                                />
                            </x-filament::input.wrapper>
                        </div>

                        <!-- Category Filter -->
                        <div class="w-full md:w-48">
                            <x-filament::input.wrapper>
                                <x-filament::input.select wire:model.live="category">
                                    <option value="">All Categories</option>
                                    <option value="authentication">Authentication</option>
                                    <option value="user_management">User Management</option>
                                    <option value="pricing">Pricing</option>
                                    <option value="dashboard">Dashboard</option>
                                    <option value="administration">Administration</option>
                                    <option value="file_operations">File Operations</option>
                                    <option value="api_activity">API Activity</option>
                                    <option value="security">Security</option>
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>

                        <!-- Suspicious Filter -->
                        <div class="w-full md:w-48">
                            <x-filament::input.wrapper>
                                <x-filament::input.select wire:model.live="is_suspicious">
                                    <option value="">All Events</option>
                                    <option value="1">Suspicious Only</option>
                                    <option value="0">Normal Only</option>
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2">
                        <x-filament::button
                            color="gray"
                            icon="heroicon-m-arrow-down-tray"
                            tag="a"
                            :href="'/api/v1/admin/audit-logs/export?' . http_build_query(array_filter(['search' => $search, 'category' => $category, 'is_suspicious' => $is_suspicious], fn($v) => $v !== ''))"
                        >
                            Export CSV
                        </x-filament::button>
                        <x-filament::button
                            icon="heroicon-m-arrow-path"
                            wire:click="$refresh"
                        >
                            Refresh
                        </x-filament::button>
                    </div>
                </div>

                <!-- Loading overlay -->
                <div wire:loading.flex class="items-center gap-2 text-sm text-gray-500">
                    <x-filament::loading-indicator class="w-4 h-4 text-primary-500" />
                    Loading...
                </div>

                <!-- Data Table -->
                <div class="border rounded-xl shadow-sm bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-800 overflow-hidden" wire:loading.class="opacity-50">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left divide-y table-auto divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-3 text-sm font-semibold text-gray-950 dark:text-white">Date & Time</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-gray-950 dark:text-white">User</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-gray-950 dark:text-white">Action</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-gray-950 dark:text-white">Resource</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-gray-950 dark:text-white">IP / Location</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-gray-950 dark:text-white">Device</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-gray-950 dark:text-white">Status</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800 whitespace-nowrap">

                                @forelse ($logs as $log)
                                    <tr
                                        class="hover:bg-gray-50 dark:hover:bg-white/5 transition {{ $log['is_suspicious'] ? 'bg-danger-50 dark:bg-danger-900/20' : '' }}"
                                    >
                                        <td class="px-4 py-3 text-sm">
                                            {{ \Carbon\Carbon::parse($log['created_at'])->toDateTimeString() }}
                                        </td>

                                        <td class="px-4 py-3 text-sm">
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{ $log['user_name'] ?? 'System' }}</span>
                                                <span class="text-xs text-gray-500">{{ $log['user_email'] ?? '' }}</span>
                                            </div>
                                        </td>

                                        <td class="px-4 py-3 text-sm">
                                            <x-filament::badge size="sm" color="gray">
                                                {{ collect(explode('_', $log['action']))->map(fn($w) => ucfirst($w))->join(' ') }}
                                            </x-filament::badge>
                                        </td>

                                        <td class="px-4 py-3 text-sm">
                                            <span>{{ $log['resource'] ?? '-' }}</span>
                                            @if (!empty($log['resource_id']))
                                                <span class="text-xs text-gray-500">#{{ $log['resource_id'] }}</span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 text-sm">
                                            <div class="flex flex-col">
                                                <span>{{ $log['ip_address'] }}</span>
                                                <span class="text-xs text-gray-500">
                                                    {{ implode(', ', array_filter([$log['city'] ?? null, $log['country'] ?? null])) ?: 'Unknown' }}
                                                </span>
                                            </div>
                                        </td>

                                        <td class="px-4 py-3 text-sm">
                                            <div class="flex flex-col">
                                                <span>{{ ucfirst($log['device_type'] ?? 'Unknown') }}</span>
                                                <span class="text-xs text-gray-500">
                                                    {{ implode(' / ', array_filter([$log['browser'] ?? null, $log['operating_system'] ?? null])) ?: 'Unknown' }}
                                                </span>
                                            </div>
                                        </td>

                                        <td class="px-4 py-3 text-sm">
                                            @if ($log['is_suspicious'])
                                                <span class="inline-flex items-center justify-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset bg-danger-50 text-danger-600 ring-danger-600/10 dark:bg-danger-500/10 dark:text-danger-400 dark:ring-danger-500/20">
                                                    Suspicious
                                                </span>
                                            @elseif ($log['success'])
                                                <span class="inline-flex items-center justify-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset bg-success-50 text-success-600 ring-success-600/10 dark:bg-success-500/10 dark:text-success-400 dark:ring-success-500/20">
                                                    Success
                                                </span>
                                            @else
                                                <span class="inline-flex items-center justify-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset bg-warning-50 text-warning-600 ring-warning-600/10 dark:bg-warning-500/10 dark:text-warning-400 dark:ring-warning-500/20">
                                                    Failed
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-4 py-3 text-sm text-right">
                                            <x-filament::button
                                                color="gray"
                                                size="sm"
                                                labeled-from="sm"
                                                icon="heroicon-m-eye"
                                                x-on:click="openDetail({{ json_encode($log) }})"
                                            >
                                                View
                                            </x-filament::button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                            No audit logs found matching your filters.
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Controls -->
                    @if ($total > 0)
                        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-800 flex items-center justify-between">
                            <div class="text-sm text-gray-500">
                                Showing <span class="font-medium">{{ $from }}</span>
                                to <span class="font-medium">{{ $to }}</span>
                                of <span class="font-medium">{{ $total }}</span> results
                            </div>
                            <div class="flex gap-2">
                                <x-filament::button
                                    color="gray"
                                    size="sm"
                                    wire:click="previousPage"
                                    :disabled="$currentPage <= 1"
                                >
                                    Previous
                                </x-filament::button>
                                <x-filament::button
                                    color="gray"
                                    size="sm"
                                    wire:click="nextPage"
                                    :disabled="$currentPage >= $lastPage"
                                >
                                    Next
                                </x-filament::button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </x-filament::section>

        <!-- Detail Slide-over -->
        <x-filament::modal id="audit-log-modal" slide-over width="3xl">
            <x-slot name="heading">
                Audit Log Details
            </x-slot>

            <div x-show="selectedLog" class="space-y-6">
                <!-- Suspicious Warning -->
                <div x-show="selectedLog?.is_suspicious" class="p-4 bg-danger-50 text-danger-600 rounded-xl dark:bg-danger-500/10 dark:text-danger-400 flex gap-3">
                    <x-filament::icon icon="heroicon-m-shield-exclamation" class="h-6 w-6 shrink-0" />
                    <div>
                        <h4 class="font-semibold text-sm">Suspicious Activity Flagged</h4>
                        <p class="text-sm mt-1" x-text="selectedLog?.suspicious_reason"></p>
                    </div>
                </div>

                <!-- Basic Info Grid -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">ID</h4>
                        <p class="text-sm" x-text="selectedLog?.uuid"></p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Timestamp</h4>
                        <p class="text-sm" x-text="selectedLog?.created_at"></p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">User</h4>
                        <p class="text-sm">
                            <span x-text="selectedLog?.user_name || 'System'"></span>
                            <span x-show="selectedLog?.user_email" class="text-gray-500 block" x-text="selectedLog?.user_email"></span>
                        </p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Action / Resource</h4>
                        <p class="text-sm">
                            <span x-text="selectedLog ? formatAction(selectedLog.action) : ''"></span>
                            <span x-show="selectedLog?.resource" class="text-gray-500 block" x-text="selectedLog?.resource + (selectedLog?.resource_id ? ' #' + selectedLog.resource_id : '')"></span>
                        </p>
                    </div>
                </div>

                <!-- Network & Device -->
                <div class="grid grid-cols-2 gap-4 border-t pt-4 dark:border-gray-800">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Network</h4>
                        <ul class="text-sm space-y-1">
                            <li><span class="text-gray-500">IP:</span> <span x-text="selectedLog?.ip_address"></span></li>
                            <li x-show="selectedLog?.country"><span class="text-gray-500">Location:</span> <span x-text="selectedLog?.city ? selectedLog.city + ', ' + selectedLog.country : selectedLog?.country"></span></li>
                            <li x-show="selectedLog?.isp"><span class="text-gray-500">ISP:</span> <span x-text="selectedLog?.isp"></span></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Device & Request</h4>
                        <ul class="text-sm space-y-1">
                            <li><span class="text-gray-500">Browser:</span> <span x-text="selectedLog?.browser ? selectedLog.browser + ' ' + (selectedLog?.browser_version || '') : 'Unknown'"></span></li>
                            <li><span class="text-gray-500">OS:</span> <span x-text="selectedLog?.operating_system || 'Unknown'"></span></li>
                            <li x-show="selectedLog?.http_method"><span class="text-gray-500">Request:</span> <span x-text="selectedLog?.http_method + ' ' + selectedLog?.response_status"></span></li>
                            <li x-show="selectedLog?.execution_time_ms"><span class="text-gray-500">Duration:</span> <span x-text="selectedLog?.execution_time_ms + 'ms'"></span></li>
                        </ul>
                    </div>
                </div>

                <!-- Changes & Metadata -->
                <div x-show="hasJsonData(selectedLog?.previous_value) || hasJsonData(selectedLog?.new_value)" class="border-t pt-4 dark:border-gray-800">
                    <h4 class="text-sm font-medium text-gray-500 mb-3">Resource Changes</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div x-show="hasJsonData(selectedLog?.previous_value)">
                            <h5 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Previous Value</h5>
                            <pre class="bg-gray-50 dark:bg-gray-950 p-3 rounded-lg text-xs overflow-x-auto text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-800" x-text="formatJson(selectedLog?.previous_value)"></pre>
                        </div>
                        <div x-show="hasJsonData(selectedLog?.new_value)">
                            <h5 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">New Value</h5>
                            <pre class="bg-gray-50 dark:bg-gray-950 p-3 rounded-lg text-xs overflow-x-auto text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-800" x-text="formatJson(selectedLog?.new_value)"></pre>
                        </div>
                    </div>
                </div>

                <div x-show="hasJsonData(selectedLog?.metadata)" class="border-t pt-4 dark:border-gray-800">
                    <h4 class="text-sm font-medium text-gray-500 mb-3">Additional Metadata</h4>
                    <pre class="bg-gray-50 dark:bg-gray-950 p-3 rounded-lg text-xs overflow-x-auto text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-800" x-text="formatJson(selectedLog?.metadata)"></pre>
                </div>
            </div>

            <x-slot name="footerActions">
                <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'audit-log-modal' })">
                    Close
                </x-filament::button>
                <x-filament::button
                    color="danger"
                    icon="heroicon-m-trash"
                    x-show="selectedLog"
                    x-on:click="confirmDelete()"
                >
                    Delete Record
                </x-filament::button>
            </x-slot>
        </x-filament::modal>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('auditLogDetail', () => ({
                selectedLog: null,

                openDetail(log) {
                    this.selectedLog = log;
                    this.$dispatch('open-modal', { id: 'audit-log-modal' });
                },

                confirmDelete() {
                    if (!this.selectedLog) return;
                    if (!confirm('Are you sure you want to delete this audit log? This action is permanent and will generate its own audit event.')) {
                        return;
                    }
                    const uuid = this.selectedLog.uuid;
                    this.$dispatch('close-modal', { id: 'audit-log-modal' });
                    this.selectedLog = null;
                    // Delegate deletion to Livewire
                    Livewire.dispatch('deleteLog', { uuid });
                },

                formatAction(actionStr) {
                    if (!actionStr) return '';
                    return actionStr.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
                },

                hasJsonData(data) {
                    if (!data) return false;
                    if (Array.isArray(data) && data.length === 0) return false;
                    if (typeof data === 'object' && Object.keys(data).length === 0) return false;
                    return true;
                },

                formatJson(data) {
                    if (!data) return '';
                    try {
                        return JSON.stringify(data, null, 2);
                    } catch (e) {
                        return String(data);
                    }
                },
            }));
        });
    </script>
</x-filament-panels::page>
