@props([
    'title' => null,
    'icon' => null,
    'variant' => 'default', // default, primary, info, success, warning, danger
    'collapsible' => false,
    'collapsed' => false,
])

@php
$variants = [
    'default' => [
        'bg' => 'bg-gray-50',
        'border' => 'border-gray-100',
        'title' => 'text-gray-800',
        'icon' => 'text-gray-400',
    ],
    'primary' => [
        'bg' => 'bg-primary-50',
        'border' => 'border-primary-100',
        'title' => 'text-primary-800',
        'icon' => 'text-primary-500',
    ],
    'info' => [
        'bg' => 'bg-blue-50',
        'border' => 'border-blue-100',
        'title' => 'text-blue-800',
        'icon' => 'text-blue-500',
    ],
    'success' => [
        'bg' => 'bg-emerald-50',
        'border' => 'border-emerald-100',
        'title' => 'text-emerald-800',
        'icon' => 'text-emerald-500',
    ],
    'warning' => [
        'bg' => 'bg-amber-50',
        'border' => 'border-amber-100',
        'title' => 'text-amber-800',
        'icon' => 'text-amber-500',
    ],
    'danger' => [
        'bg' => 'bg-rose-50',
        'border' => 'border-rose-100',
        'title' => 'text-rose-800',
        'icon' => 'text-rose-500',
    ],
    'card' => [
        'bg' => 'bg-white shadow-sm transition-shadow hover:shadow-md',
        'border' => 'border-slate-200',
        'title' => 'text-slate-800',
        'icon' => 'bg-slate-50 text-slate-500 rounded-lg p-2.5 w-10 h-10 flex items-center justify-center', 
        'header' => 'px-6 py-5 md:px-8 border-b border-slate-100 flex items-center justify-between', // Added flex layout back
    ],
];
$style = $variants[$variant] ?? $variants['default'];
@endphp

<div 
    {{ $attributes->merge(['class' => $variant === 'card' 
        ? "{$style['bg']} rounded-xl border {$style['border']} overflow-hidden relative" 
        : "p-4 {$style['bg']} rounded-xl border {$style['border']} space-y-4"
    ]) }}
    @if($collapsible) x-data="{ open: {{ $collapsed ? 'false' : 'true' }} }" @endif
>
    @if($title && $variant === 'card')
        {{-- Header (Full Width) --}}
        <div class="{{ $style['header'] }}">
            <div class="flex items-center gap-4">
                    @if($icon)
                    <div class="{{ $style['icon'] }}">
                        <x-ui.icon :name="$icon" size="20" stroke-width="2" />
                    </div>
                @endif
                <div>
                    <h3 class="text-base font-bold {{ $style['title'] }}">{{ $title }}</h3>
                        @if(isset($description))
                        <p class="text-xs text-slate-500 mt-0.5">{{ $description }}</p>
                    @endif
                </div>
            </div>
                @if(isset($actions))
                {{ $actions }}
            @endif
        </div>
        
        {{-- Body (Padded) --}}
        <div class="p-6 md:p-8" @if($collapsible) x-show="open" x-collapse @endif>
                {{ $slot }}
        </div>
    @elseif($title)
    <div 
        class="flex items-center justify-between gap-2 {{ $collapsible ? 'cursor-pointer select-none' : '' }}"
        @if($collapsible) @click="open = !open" @endif
    >
        <h4 class="font-semibold {{ $style['title'] }} flex items-center gap-2">
            @if($icon)
                <x-ui.icon :name="$icon" size="18" class="{{ $style['icon'] }}" />
            @endif
            {{ $title }}
        </h4>
        @if($collapsible)
            <x-ui.icon name="chevron-down" size="18" class="text-gray-400 transition-transform" ::class="{ 'rotate-180': open }" />
        @endif
    </div>
    
    <div @if($collapsible) x-show="open" x-collapse @endif>
        {{ $slot }}
    </div>
    @else
        <div @if($collapsible) x-show="open" x-collapse @endif>
            {{ $slot }}
        </div>
    @endif
</div>
