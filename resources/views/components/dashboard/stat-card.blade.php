@props([
    'value' => 0,
    'label' => '',
    'icon' => 'bar-chart',
    'trend' => null, // 'up', 'down', or null
    'trendValue' => '',
    'color' => 'primary', // primary, emerald, amber, rose, violet, blue
    'href' => null
])

@php
$colorStyles = [
    'primary' => [
        'bg' => 'bg-primary-50',
        'icon' => 'text-primary-600',
        'value' => 'text-primary-700',
    ],
    'emerald' => [
        'bg' => 'bg-emerald-50',
        'icon' => 'text-emerald-600',
        'value' => 'text-emerald-700',
    ],
    'amber' => [
        'bg' => 'bg-amber-50',
        'icon' => 'text-amber-600',
        'value' => 'text-amber-700',
    ],
    'rose' => [
        'bg' => 'bg-rose-50',
        'icon' => 'text-rose-600',
        'value' => 'text-rose-700',
    ],
    'violet' => [
        'bg' => 'bg-violet-50',
        'icon' => 'text-violet-600',
        'value' => 'text-violet-700',
    ],
    'blue' => [
        'bg' => 'bg-blue-50',
        'icon' => 'text-blue-600',
        'value' => 'text-blue-700',
    ],
    'gray' => [
        'bg' => 'bg-gray-50',
        'icon' => 'text-gray-600',
        'value' => 'text-gray-700',
    ],
];
$style = $colorStyles[$color] ?? $colorStyles['primary'];

$trendColors = [
    'up' => 'text-emerald-600 bg-emerald-50',
    'down' => 'text-rose-600 bg-rose-50',
];
@endphp

@php
$tag = $href ? 'a' : 'div';
$linkAttrs = $href ? "href=\"{$href}\"" : '';
@endphp

<{{ $tag }} {!! $linkAttrs !!} {{ $attributes->merge(['class' => 'card p-4 hover:shadow-lg hover:border-gray-200 hover:-translate-y-1 transition-all duration-300 group ' . ($href ? 'cursor-pointer' : 'cursor-default')]) }}>
    <div class="flex items-start justify-between gap-4">
        <div class="flex-1 min-w-0">
            <p class="text-sm text-gray-500 font-medium truncate">{{ $label }}</p>
            <p class="text-2xl font-bold {{ $style['value'] }} mt-1">{{ $value }}</p>
            
            @if($trend && $trendValue)
            <div class="flex items-center gap-1 mt-2">
                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-xs font-medium {{ $trendColors[$trend] ?? '' }}">
                    @if($trend === 'up')
                        <x-ui.icon name="trending-up" :size="12" />
                    @else
                        <x-ui.icon name="trending-down" :size="12" />
                    @endif
                    {{ $trendValue }}
                </span>
            </div>
            @endif
        </div>
        
        <div class="w-12 h-12 rounded-xl {{ $style['bg'] }} {{ $style['icon'] }} flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-300">
            <x-ui.icon :name="$icon" :size="24" />
        </div>
    </div>
    
    @if(isset($footer))
    <div class="mt-3 pt-3 border-t border-gray-100">
        {{ $footer }}
    </div>
    @endif
</{{ $tag }}>
