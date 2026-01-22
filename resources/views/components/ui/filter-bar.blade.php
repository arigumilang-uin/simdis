{{--
    Filter Bar Component
    
    Layout: [Filter Button] [Search Input]
    - Filter button opens a dropdown card with filter options
    - Search is always visible
--}}

@props([
    'id' => 'filter-panel'
])

<div {{ $attributes }} x-data="{ filterOpen: false }">
    <div class="flex items-center gap-2">
        {{-- Filter Button + Dropdown Panel --}}
        @if(isset($filters))
        <div class="relative">
            {{-- Filter Toggle Button --}}
            <button 
                type="button"
                @click="filterOpen = !filterOpen"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg border transition-colors"
                :class="filterOpen ? 'bg-indigo-50 border-indigo-200 text-indigo-700' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300'"
            >
                <x-ui.icon name="sliders" size="16" />
                <span>Filter</span>
                <x-ui.icon name="chevron-down" size="14" class="transition-transform" ::class="filterOpen && 'rotate-180'" />
            </button>
            
            {{-- Filter Dropdown Panel --}}
            <div 
                x-show="filterOpen"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-1"
                @click.outside="filterOpen = false"
                x-cloak
                class="absolute right-0 mt-2 z-50 w-80 bg-white rounded-xl border border-gray-200 shadow-lg"
            >
                {{-- Panel Header --}}
                <div class="px-4 py-3 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-700">Filter Data</span>
                        @if(isset($reset))
                            {{ $reset }}
                        @endif
                    </div>
                </div>
                
                {{-- Filter Options --}}
                <div class="p-4 space-y-4">
                    {{ $filters }}
                </div>
            </div>
        </div>
        @endif
        
        {{-- Search Input (always visible) --}}
        @if(isset($search))
            {{ $search }}
        @endif
    </div>
</div>
