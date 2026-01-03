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

<div {{ $attributes->merge(['class' => 'card']) }} x-data="{ expanded: {{ $expandedValue }} }">
    <div class="card-header cursor-pointer select-none" @click="expanded = !expanded">
        <div class="flex items-center gap-2">
            <x-ui.icon name="filter" :size="16" class="text-gray-400" />
            <h3 class="card-title">{{ $title }}</h3>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-500" x-show="isLoading">Memuat Data...</span>
            <x-ui.icon name="chevron-down" :size="20" class="text-gray-400 transition-transform" x-bind:class="{ 'rotate-180': expanded }" />
        </div>
    </div>
    
    <div x-show="expanded" x-collapse.duration.300ms x-cloak>
        <div class="card-body border-t border-gray-100">
            <div class="grid grid-cols-1 {{ $gridClass }} gap-4 items-end">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
