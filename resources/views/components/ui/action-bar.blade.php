{{--
    Action Bar Component - Total counter and search with integrated filter
    
    Layout:
    [123 data]                    [🔍 Search...  ≡]
                                        ↓
                                  [Filter Panel]
    
    Usage:
    <x-ui.action-bar :total="$data->total()">
        <x-slot:filters>
            <x-ui.filter-select ... />
        </x-slot:filters>
        <x-slot:search>
            <x-ui.filter-search ... />
        </x-slot:search>
    </x-ui.action-bar>
--}}

@props([
    'total' => null,
    'totalLabel' => 'data',
])

<div {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4']) }} x-data="{ filterOpen: false }">
    
    {{-- Left side: Total Counter (Modern) --}}
    @if($total !== null)
        <div class="flex items-center gap-2 shrink-0"
             x-data="{ total: {{ $total }} }"
             @update-total-data.window="total = $event.detail.total">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">{{ $totalLabel }}</span>
            <span class="text-lg font-bold text-gray-900 leading-none" x-text="total">{{ $total }}</span>
        </div>
    @else
        <div></div>
    @endif
    
    {{-- Right side: Search and Filter Group --}}
    <div class="flex flex-col md:flex-row gap-3 items-stretch md:items-center relative z-20">
        
        {{-- Search Input Wrapper --}}
        <div class="relative grow md:grow-0">
            {{-- Search Icon (left) --}}
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <x-ui.icon name="search" size="16" class="text-gray-400" />
            </div>
            
            {{-- Search Input --}}
            @if(isset($search))
                {{ $search }}
            @else
                <input 
                    type="text" 
                    class="w-full md:w-80 rounded-xl border-0 bg-gray-100/80 text-sm text-gray-800 py-2.5 pl-9 pr-4 hover:bg-gray-100 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:shadow-lg focus:shadow-indigo-500/5 transition-all duration-200 placeholder-gray-400"
                    placeholder="Cari..."
                >
            @endif
        </div>
        
        {{-- Standalone Filter Button & Dropdown --}}
        @if(isset($filters))
        <div class="relative shrink-0" 
             x-data="{ 
                 filterOpen: false,
                 top: 0,
                 left: 0,
                 updatePosition() {
                     if (!this.filterOpen) return;
                     const rect = this.$refs.trigger.getBoundingClientRect();
                     this.top = rect.bottom;
                     this.left = rect.right;
                 }
             }"
             @scroll.window="updatePosition()"
             @resize.window="updatePosition()"
        >
            <button 
                x-ref="trigger"
                type="button"
                @click="filterOpen = !filterOpen; $nextTick(() => updatePosition())"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-gray-600 text-sm hover:border-gray-300 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 transition-all duration-200"
                :class="filterOpen && 'border-indigo-400 bg-indigo-50/50 text-indigo-600'"
            >
                {{-- Filter Icon - 3 stacked lines --}}
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="4" y1="6" x2="20" y2="6"></line>
                    <line x1="6" y1="12" x2="18" y2="12"></line>
                    <line x1="8" y1="18" x2="16" y2="18"></line>
                </svg>
                <span>Filter</span>
            </button>
            
            {{-- Filter Dropdown Panel (Teleported to Body) --}}
            <template x-teleport="body">
                <div 
                    x-show="filterOpen"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-2"
                    @click.outside="filterOpen = false"
                    x-cloak
                    style="display: none;"
                    :style="`top: ${top + 8}px; left: ${left}px; transform: translateX(-100%);`"
                    class="fixed z-[9999] w-72 bg-white rounded-xl border border-gray-100 shadow-xl"
                >
                    {{-- Panel Header --}}
                    <div class="px-4 py-3 border-b border-gray-50 flex items-center justify-between bg-gray-50/50 rounded-t-xl">
                        <span class="text-sm font-semibold text-gray-700">Filter Data</span>
                        @if(isset($reset))
                            {{ $reset }}
                        @endif
                    </div>
                    
                    {{-- Filter Options --}}
                    <div class="p-4 space-y-4 max-h-[60vh] overflow-y-auto">
                        {{ $filters }}
                    </div>
                </div>
            </template>
        </div>
        @endif
    </div>
</div>
