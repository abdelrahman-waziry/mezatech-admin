<x-filament-panels::page>
    <style>
        /* ─── Price Navigator Scoped Styles ─── */
        .pn-root { max-width: 1152px; margin: 0 auto; font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; overflow: hidden; width: 100%; box-sizing: border-box; }

        /* Tabs */
        .pn-tabs { display: inline-flex; background: #f3f4f6; border-radius: 12px; padding: 4px; gap: 4px; }
        .fi-dark .pn-tabs { background: #1f2937; }
        .pn-tab { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; color: #9ca3af; transition: all .2s; cursor: pointer; border: none; background: none; }
        .pn-tab:hover { color: #374151; }
        .fi-dark .pn-tab:hover { color: #d1d5db; }
        .pn-tab.active { background: #fff; color: #111827; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .fi-dark .pn-tab.active { background: #374151; color: #f9fafb; }

        /* Search */
        .pn-search-wrap { position: relative; flex: 1; max-width: 360px; min-width: 200px; }
        .pn-search-wrap svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #9ca3af; }
        .pn-search-wrap input { width: 100%; border: 1px solid #e5e7eb; border-radius: 10px; padding: 10px 14px 10px 38px; font-size: 14px; background: #fff; color: #111827; outline: none; transition: box-shadow .2s, border-color .2s; }
        .fi-dark .pn-search-wrap input { background: #111827; border-color: #374151; color: #f9fafb; }
        .pn-search-wrap input:focus { box-shadow: 0 0 0 3px rgba(0,251,2,.15); border-color: #00FB02; }
        .pn-search-wrap input::placeholder { color: #9ca3af; }

        /* Filter bar */
        .pn-filter-bar { display: flex; flex-direction: column; gap: 12px; }
        .pn-filter-top { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }

        /* Pills */
        .pn-pills { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
        .pn-pill { padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; background: #f3f4f6; color: #374151; transition: all .2s; cursor: pointer; border: none; white-space: nowrap; }
        .fi-dark .pn-pill { background: #1f2937; color: #d1d5db; }
        .pn-pill:hover { opacity: .8; }
        .pn-pill.active { background: #00FB02; color: #000; box-shadow: 0 4px 14px rgba(0,251,2,.25); }

        /* Sub chips */
        .pn-sub-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding: 8px 12px; background: #f9fafb; border-radius: 10px; border: 1px solid #f3f4f6; }
        .fi-dark .pn-sub-row { background: #111827; border-color: #1f2937; }
        .pn-sub-label { font-size: 11px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; margin-right: 4px; }
        .pn-chip { padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; background: #f3f4f6; color: #6b7280; transition: all .15s; cursor: pointer; border: none; white-space: nowrap; }
        .fi-dark .pn-chip { background: #1f2937; color: #9ca3af; }
        .pn-chip:hover { color: #111827; }
        .fi-dark .pn-chip:hover { color: #f3f4f6; }
        .pn-chip.active { background: rgba(0,251,2,.12); color: #00a801; }
        .fi-dark .pn-chip.active { color: #00FB02; }

        /* Results count */
        .pn-count { font-size: 14px; color: #6b7280; display: flex; align-items: center; justify-content: space-between; }
        .pn-count strong { color: #111827; }
        .fi-dark .pn-count strong { color: #f9fafb; }
        .pn-reset { font-size: 12px; font-weight: 600; color: #9ca3af; cursor: pointer; border: none; background: none; transition: color .15s; }
        .pn-reset:hover { color: #ef4444; }

        /* Card grid */
        .pn-grid { display: grid; gap: 16px; grid-template-columns: repeat(3, 1fr); }
        @media (max-width: 1024px) { .pn-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px) { .pn-grid { grid-template-columns: 1fr; } }

        .pn-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 20px; transition: all .3s; box-shadow: 0 1px 3px rgba(0,0,0,.04); animation: pnFadeUp .35s ease both; cursor: default; }
        .fi-dark .pn-card { background: #111827; border-color: #1f2937; }
        .pn-card:hover { box-shadow: 0 8px 30px rgba(0,251,2,.1); border-color: rgba(0,251,2,.4); transform: translateY(-2px); }

        @keyframes pnFadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }

        .pn-card-top { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
        .pn-card-info { flex: 1; min-width: 0; }
        .pn-card-name { font-size: 14px; font-weight: 700; line-height: 1.4; color: #111827; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; word-break: break-word; }
        .fi-dark .pn-card-name { color: #f9fafb; }
        .pn-card-desc { font-size: 12px; color: #6b7280; margin-top: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%; }
        .pn-card-price { flex-shrink: 0; display: flex; flex-direction: column; align-items: flex-end; justify-content: flex-start; }
        .pn-price-old { font-size: 13px; color: #9ca3af; text-decoration: line-through; font-weight: 600; margin-bottom: 2px; }
        .fi-dark .pn-price-old { color: #6b7280; }
        .pn-price-main { display: flex; align-items: baseline; gap: 4px; }
        .pn-price-main span { font-size: 1.5rem; font-weight: 800; color: #00a801; letter-spacing: -.02em; line-height: 1; }
        .fi-dark .pn-price-main span { color: #00FB02; }
        .pn-price-main small { font-size: 11px; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }

        .pn-card-tags { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .pn-tag { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 8px; font-size: 11px; font-weight: 600; background: #f3f4f6; color: #374151; }
        .fi-dark .pn-tag { background: #1f2937; color: #d1d5db; }
        .pn-tag.sub { color: #9ca3af; }
        .pn-tag.warranty { background: rgba(16,185,129,.1); color: #059669; }
        .fi-dark .pn-tag.warranty { color: #34d399; }
        .pn-tag.sla { background: rgba(245,158,11,.1); color: #d97706; }
        .fi-dark .pn-tag.sla { color: #fbbf24; }
        .pn-tag.code-tag { background: none; color: #9ca3af; margin-left: auto; padding: 0; font-family: ui-monospace, monospace; font-size: 10px; }

        /* Empty state */
        .pn-empty { grid-column: 1 / -1; text-align: center; padding: 80px 20px; }
        .pn-empty p { color: #6b7280; font-size: 18px; font-weight: 600; }
        .pn-empty .pn-hint { font-size: 14px; opacity: .6; margin-top: 4px; }

        /* Discount badge */
        .pn-discount { display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: 700; background: rgba(0,251,2,.12); color: #00a801; margin-top: 2px; }
        .fi-dark .pn-discount { color: #00FB02; }

        /* Section spacing */
        .pn-section { display: flex; flex-direction: column; gap: 24px; }
    </style>

    <div class="pn-root">
        <div class="pn-section">

            {{-- Tabs --}}
            <div class="pn-tabs">
                <button wire:click="setTab('repairs')" class="pn-tab {{ $activeTab === 'repairs' ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                    Repair Prices
                </button>
                <button wire:click="setTab('accessories')" class="pn-tab {{ $activeTab === 'accessories' ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    Accessories
                </button>
            </div>

            {{-- Filter Bar --}}
            <div class="pn-filter-bar">
                <div class="pn-filter-top">
                    {{-- Search --}}
                    <div class="pn-search-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search prices...">
                    </div>

                    {{-- Category Pills --}}
                    <div class="pn-pills">
                        @php
                            $isRepairs = $activeTab === 'repairs';
                            $currentCatId = $isRepairs ? $repairCategoryId : $accessoryCategoryId;
                            $categories = $isRepairs ? $this->repairCategories : $this->accessoriesCategories;
                        @endphp

                        <button
                            wire:click="{{ $isRepairs ? 'selectRepairCategory(null)' : 'selectAccessoryCategory(null)' }}"
                            class="pn-pill {{ !$currentCatId ? 'active' : '' }}"
                        >All</button>

                        @foreach($categories as $cat)
                            <button
                                wire:click="{{ $isRepairs ? 'selectRepairCategory('.$cat->id.')' : 'selectAccessoryCategory('.$cat->id.')' }}"
                                class="pn-pill {{ $currentCatId === $cat->id ? 'active' : '' }}"
                            >{{ $cat->name }}</button>
                        @endforeach
                    </div>
                </div>

                {{-- Subcategory / Brand chips --}}
                @if($currentCatId)
                    <div class="pn-sub-row">
                        <span class="pn-sub-label">{{ $isRepairs ? 'Sub:' : 'Brand:' }}</span>

                        <button
                            wire:click="{{ $isRepairs ? 'selectRepairSubcategory(null)' : 'selectAccessoryBrand(null)' }}"
                            class="pn-chip {{ ($isRepairs ? !$repairSubcategoryId : !$accessoryBrand) ? 'active' : '' }}"
                        >All</button>

                        @if($isRepairs)
                            @foreach($this->repairSubcategories as $sub)
                                <button
                                    wire:click="selectRepairSubcategory({{ $sub->id }})"
                                    class="pn-chip {{ $repairSubcategoryId === $sub->id ? 'active' : '' }}"
                                >{{ $sub->name }}</button>
                            @endforeach
                        @else
                            @foreach($this->accessoryBrands as $brand)
                                <button
                                    wire:click="selectAccessoryBrand('{{ $brand }}')"
                                    class="pn-chip {{ $accessoryBrand === $brand ? 'active' : '' }}"
                                >{{ $brand }}</button>
                            @endforeach
                        @endif
                    </div>
                @endif
            </div>

            {{-- Count + Reset --}}
            <div class="pn-count">
                <span><strong>{{ count($this->filteredItems) }}</strong> items found</span>
                <button wire:click="resetFilters" class="pn-reset">Reset filters</button>
            </div>

            {{-- Results Grid --}}
            <div class="pn-grid">
                @forelse($this->filteredItems as $idx => $item)
                    @php
                        $title       = $isRepairs ? $item->model : $item->name;
                        $catLabel    = $isRepairs ? $item->subcategory->category->name : $item->category->name;
                        $subLabel    = $isRepairs ? $item->subcategory->name : $item->brand;
                        $code        = $isRepairs ? $item->product_number : $item->item_code;
                        $hasDiscount = $item->discount > 0;
                    @endphp

                    <div class="pn-card" style="animation-delay: {{ $idx * 40 }}ms">
                        <div class="pn-card-top">
                            <div class="pn-card-info">
                                <div class="pn-card-name">{{ $title }}</div>
                                @if(!$isRepairs && $item->notes)
                                    <div class="pn-card-desc">{{ $item->notes }}</div>
                                @endif
                            </div>
                            <div class="pn-card-price">
                                @if($hasDiscount)
                                    <div class="pn-price-old">{{ number_format($item->price, 0) }} EGP</div>
                                @endif
                                <div class="pn-price-main">
                                    <span>{{ number_format($item->price_after_discount, 0) }}</span>
                                    <small>EGP</small>
                                </div>
                                @if($hasDiscount)
                                    <div class="pn-discount">-{{ floatval($item->discount) }}%</div>
                                @endif
                            </div>
                        </div>
                        <div class="pn-card-tags">
                            <span class="pn-tag">{{ $catLabel }}</span>
                            @if($subLabel)
                                <span class="pn-tag sub">{{ $subLabel }}</span>
                            @endif
                            @if($isRepairs && $item->warranty)
                                <span class="pn-tag warranty">✓ {{ $item->warranty }}</span>
                            @endif
                            @if($isRepairs && $item->sla)
                                <span class="pn-tag sla">⚡ {{ $item->sla }}</span>
                            @endif
                            @if($code)
                                <span class="pn-tag code-tag">{{ $code }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="pn-empty">
                        <p>No items match your filters</p>
                        <p class="pn-hint">Try adjusting your search or category</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-panels::page>
