<x-filament-widgets::widget>
    @php $progress = $this->getProgress(); @endphp
    
    <div wire:poll.5s>
        @if($progress)
            <x-filament::section icon="heroicon-o-arrow-path" icon-color="primary" class="animate-pulse shadow-lg ring-1 ring-primary-500/20">
                <x-slot name="heading">
                    <div class="flex justify-between items-center w-full">
                        <div class="flex items-center gap-2">
                             <div class="animate-spin text-primary-600">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                             </div>
                             <span class="text-sm font-semibold tracking-tight text-gray-900 dark:text-gray-100 uppercase">FixTech Sync In Progress</span>
                        </div>
                        <span class="text-sm font-bold font-mono text-primary-600 dark:text-primary-400 bg-primary-100 dark:bg-primary-900/30 px-2 py-0.5 rounded shadow-inner">{{ $progress['percent'] }}%</span>
                    </div>
                </x-slot>

                <div class="space-y-4 py-1">
                    <div class="relative w-full bg-gray-200 rounded-full h-3 dark:bg-gray-800 border border-gray-100/10 shadow-inner overflow-hidden">
                        <div class="bg-primary-600 h-3 rounded-full transition-all duration-1000 ease-in-out shadow-[0_0_12px_rgba(59,130,246,0.6)]" style="width: {{ $progress['percent'] }}%">
                             <div class="w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent animate-shimmer scale-x-150"></div>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-end text-xs text-gray-500 dark:text-gray-400 font-medium italic">
                        <div class="flex items-center gap-1.5 antialiased">
                            <span class="relative flex h-2 w-2">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-500"></span>
                            </span>
                            {{ $progress['status'] }}
                        </div>
                        <p class="font-normal not-italic opacity-60">Heartbeat: {{ \Carbon\Carbon::parse($progress['updated_at'])->diffForHumans() }}</p>
                    </div>
                </div>
            </x-filament::section>
        @endif
    </div>
    
    <style>
        @keyframes shimmer {
            0% { transform: translateX(-100%) skewX(-20deg); }
            100% { transform: translateX(200%) skewX(-20deg); }
        }
        .animate-shimmer {
            animation: shimmer 2s infinite linear;
        }
    </style>
</x-filament-widgets::widget>
