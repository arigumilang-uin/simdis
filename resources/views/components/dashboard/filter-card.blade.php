@props([
    'title' => 'Filter Data',
    'expanded' => false,
    'expandedCondition' => null, // Blade expression for dynamic expansion
    'columns' => 4
])

@php
$expandedValue = $expandedCondition ?? ($expanded ? 'true' : 'false');
$gridClass = match((int)$columns) {
    2 => 'md:grid-cols-2',
    3 => 'md:grid-cols-3',
    5 => 'md:grid-cols-5',
    6 => 'md:grid-cols-6',
    default => 'md:grid-cols-4',
};
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-2xl bg-white ring-1 ring-gray-900/5 shadow-sm hover:shadow-md transition-shadow duration-300']) }} x-data="{ expanded: {{ $expandedValue }} }">
    
    {{-- Header --}}
    <div class="relative px-5 py-4 cursor-pointer select-none group" @click="expanded = !expanded">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                {{-- Icon with gradient background --}}
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-gray-100 to-gray-50 flex items-center justify-center group-hover:from-primary-50 group-hover:to-primary-100/50 transition-colors duration-300">
                    <x-ui.icon name="sliders" :size="18" class="text-gray-500 group-hover:text-primary-600 transition-colors" />
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">{{ $title }}</h3>
                    <p class="text-xs text-gray-400" x-show="!expanded">Klik untuk membuka filter</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                {{-- Loading indicator --}}
                <span class="text-xs text-primary-600 font-medium flex items-center gap-1.5" x-show="isLoading">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary-500 animate-pulse"></span>
                    Memuat...
                </span>
                
                {{-- Expand/Collapse Icon --}}
                <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center group-hover:bg-gray-100 transition-colors">
                    <x-ui.icon name="chevron-down" :size="18" class="text-gray-400 transition-transform duration-300" x-bind:class="{ 'rotate-180': expanded }" />
                </div>
            </div>
        </div>
    </div>
    
    {{-- Collapsible Content --}}
    <div x-show="expanded" x-collapse.duration.300ms x-cloak>
        <div class="px-5 pb-5 pt-4 border-t border-gray-100/80 bg-gradient-to-b from-gray-50/50 to-white">
            <div class="grid grid-cols-1 {{ $gridClass }} gap-4 items-end">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
