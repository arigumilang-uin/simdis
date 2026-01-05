@props([
    'value' => 0,
    'label' => '',
    'icon' => 'bar-chart',
    'trend' => null, // 'up', 'down', or null
    'trendValue' => '',
    'color' => 'primary', // primary, emerald, amber, rose, violet, blue, cyan, indigo
    'href' => null,
    'footer' => null,
    'variant' => 'default' // default, gradient, glass
])

@php
// Modern gradient color schemes
$colorStyles = [
    'primary' => [
        'bg' => 'bg-gradient-to-br from-primary-50 to-primary-100/50',
        'icon_bg' => 'bg-gradient-to-br from-primary-500 to-primary-600',
        'icon' => 'text-white',
        'value' => 'text-primary-700',
        'ring' => 'ring-primary-500/20',
        'glow' => 'shadow-primary-500/10',
    ],
    'emerald' => [
        'bg' => 'bg-gradient-to-br from-emerald-50 to-teal-100/50',
        'icon_bg' => 'bg-gradient-to-br from-emerald-500 to-teal-600',
        'icon' => 'text-white',
        'value' => 'text-emerald-700',
        'ring' => 'ring-emerald-500/20',
        'glow' => 'shadow-emerald-500/10',
    ],
    'amber' => [
        'bg' => 'bg-gradient-to-br from-amber-50 to-orange-100/50',
        'icon_bg' => 'bg-gradient-to-br from-amber-500 to-orange-500',
        'icon' => 'text-white',
        'value' => 'text-amber-700',
        'ring' => 'ring-amber-500/20',
        'glow' => 'shadow-amber-500/10',
    ],
    'rose' => [
        'bg' => 'bg-gradient-to-br from-rose-50 to-pink-100/50',
        'icon_bg' => 'bg-gradient-to-br from-rose-500 to-pink-600',
        'icon' => 'text-white',
        'value' => 'text-rose-700',
        'ring' => 'ring-rose-500/20',
        'glow' => 'shadow-rose-500/10',
    ],
    'violet' => [
        'bg' => 'bg-gradient-to-br from-violet-50 to-purple-100/50',
        'icon_bg' => 'bg-gradient-to-br from-violet-500 to-purple-600',
        'icon' => 'text-white',
        'value' => 'text-violet-700',
        'ring' => 'ring-violet-500/20',
        'glow' => 'shadow-violet-500/10',
    ],
    'blue' => [
        'bg' => 'bg-gradient-to-br from-blue-50 to-indigo-100/50',
        'icon_bg' => 'bg-gradient-to-br from-blue-500 to-indigo-600',
        'icon' => 'text-white',
        'value' => 'text-blue-700',
        'ring' => 'ring-blue-500/20',
        'glow' => 'shadow-blue-500/10',
    ],
    'cyan' => [
        'bg' => 'bg-gradient-to-br from-cyan-50 to-sky-100/50',
        'icon_bg' => 'bg-gradient-to-br from-cyan-500 to-sky-600',
        'icon' => 'text-white',
        'value' => 'text-cyan-700',
        'ring' => 'ring-cyan-500/20',
        'glow' => 'shadow-cyan-500/10',
    ],
    'indigo' => [
        'bg' => 'bg-gradient-to-br from-indigo-50 to-blue-100/50',
        'icon_bg' => 'bg-gradient-to-br from-indigo-500 to-blue-600',
        'icon' => 'text-white',
        'value' => 'text-indigo-700',
        'ring' => 'ring-indigo-500/20',
        'glow' => 'shadow-indigo-500/10',
    ],
    'gray' => [
        'bg' => 'bg-gradient-to-br from-gray-50 to-slate-100/50',
        'icon_bg' => 'bg-gradient-to-br from-gray-500 to-slate-600',
        'icon' => 'text-white',
        'value' => 'text-gray-700',
        'ring' => 'ring-gray-500/20',
        'glow' => 'shadow-gray-500/10',
    ],
];
$style = $colorStyles[$color] ?? $colorStyles['primary'];

$trendColors = [
    'up' => 'text-emerald-600 bg-emerald-50 border border-emerald-200/50',
    'down' => 'text-rose-600 bg-rose-50 border border-rose-200/50',
];

$tag = $href ? 'a' : 'div';
$linkAttrs = $href ? "href=\"{$href}\"" : '';

// Variant-based card class
$variantClasses = [
    'default' => 'bg-white',
    'gradient' => $style['bg'],
    'glass' => 'bg-white/70 backdrop-blur-sm',
];
$cardVariant = $variantClasses[$variant] ?? $variantClasses['default'];
@endphp

<{{ $tag }} {!! $linkAttrs !!} {{ $attributes->merge(['class' => "relative overflow-hidden rounded-2xl {$cardVariant} p-5 ring-1 {$style['ring']} shadow-lg {$style['glow']} hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group " . ($href ? 'cursor-pointer' : 'cursor-default')]) }}>
    
    {{-- Decorative Background Pattern --}}
    <div class="absolute top-0 right-0 w-32 h-32 opacity-[0.03] pointer-events-none">
        <svg viewBox="0 0 100 100" class="w-full h-full">
            <circle cx="80" cy="20" r="40" fill="currentColor"/>
        </svg>
    </div>
    
    <div class="relative z-10 flex items-start justify-between gap-4">
        <div class="flex-1 min-w-0">
            {{-- Label --}}
            <p class="text-sm text-gray-500 font-medium truncate mb-1">{{ $label }}</p>
            
            {{-- Value with counting animation placeholder --}}
            <p class="text-3xl font-bold {{ $style['value'] }} tracking-tight leading-none">
                {{ $value }}
            </p>
            
            {{-- Trend Indicator --}}
            @if($trend && $trendValue)
            <div class="flex items-center gap-1.5 mt-3">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold {{ $trendColors[$trend] ?? '' }} shadow-sm">
                    @if($trend === 'up')
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M17 7H7M17 7V17"/>
                        </svg>
                    @else
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 7L7 17M7 17H17M7 17V7"/>
                        </svg>
                    @endif
                    {{ $trendValue }}
                </span>
            </div>
            @endif

            {{-- Footer text --}}
            @if($footer && is_string($footer))
            <p class="text-xs text-gray-400 mt-2">{{ $footer }}</p>
            @endif
        </div>
        
        {{-- Animated Icon Container --}}
        <div class="relative">
            {{-- Glow Effect --}}
            <div class="absolute inset-0 {{ $style['icon_bg'] }} rounded-2xl blur-lg opacity-40 group-hover:opacity-60 transition-opacity duration-300"></div>
            
            {{-- Icon Box --}}
            <div class="relative w-14 h-14 rounded-2xl {{ $style['icon_bg'] }} {{ $style['icon'] }} flex items-center justify-center shrink-0 shadow-lg group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
                <x-ui.icon :name="$icon" :size="28" />
            </div>
        </div>
    </div>
    
    {{-- Slot-based Footer --}}
    @if(isset($footer) && !is_string($footer))
    <div class="relative z-10 mt-4 pt-4 border-t border-gray-100/80">
        {{ $footer }}
    </div>
    @endif
</{{ $tag }}>
