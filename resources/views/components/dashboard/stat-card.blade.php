@props([
    'value' => 0,
    'label' => '',
    'icon' => 'bar-chart',
    'trend' => null, // 'up', 'down', or null
    'trendValue' => '',
    'color' => 'primary', // primary, emerald, amber, rose, violet, blue, cyan, indigo
    'href' => null,
    'footer' => null,
    'variant' => 'default' // Ignored, unified style
])

@php
// Map component colors to CSS classes defined in app.css
$colorMap = [
    'primary' => 'primary',
    'emerald' => 'success',
    'green' => 'success',
    'amber' => 'warning',
    'yellow' => 'warning',
    'rose' => 'danger',
    'red' => 'danger',
    'blue' => 'blue',
    'info' => 'info',
    'violet' => 'violet',
    'purple' => 'violet',
    'indigo' => 'indigo',
    'cyan' => 'cyan',
    'gray' => 'primary',
];
$cssColor = $colorMap[$color] ?? 'primary';

$tag = $href ? 'a' : 'div';
$linkAttrs = $href ? "href=\"{$href}\"" : '';
@endphp

<{{ $tag }} {!! $linkAttrs !!} {{ $attributes->merge(['class' => 'stat-card group ' . ($href ? 'cursor-pointer' : '')]) }}>
    <div class="stat-card-icon {{ $cssColor }}">
        <x-ui.icon :name="$icon" size="24" />
    </div>
    <div class="stat-card-content">
        <p class="stat-card-label">{{ $label }}</p>
        <p class="stat-card-value">{{ $value }}</p>
        
        {{-- Footer / Subtext --}}
        @if($footer)
            @if(is_string($footer))
                <p class="text-xs text-gray-500 mt-1">{{ $footer }}</p>
            @else
                <div class="mt-1 text-xs text-gray-500">
                    {{ $footer }}
                </div>
            @endif
        @endif
        
        {{-- Trend Indicator --}}
        @if($trend && $trendValue)
            <div class="stat-card-trend {{ $trend }}">
                <x-ui.icon :name="$trend === 'up' ? 'trending-up' : 'trending-down'" size="14" />
                <span>{{ $trendValue }}</span>
            </div>
        @endif
    </div>
</{{ $tag }}>
