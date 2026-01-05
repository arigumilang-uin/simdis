@props([
    'title' => '',
    'subtitle' => '',
    'chartId' => 'chart',
    'minHeight' => '320px',
    'centered' => false,
    'variant' => 'default' // default, gradient
])

@php
$variantClasses = [
    'default' => 'bg-white',
    'gradient' => 'bg-gradient-to-br from-white to-gray-50/50',
];
$bgClass = $variantClasses[$variant] ?? $variantClasses['default'];
@endphp

<div {{ $attributes->merge(['class' => "relative overflow-hidden rounded-2xl {$bgClass} ring-1 ring-gray-900/5 shadow-lg hover:shadow-xl transition-shadow duration-300 h-full flex flex-col"]) }}>
    
    {{-- Decorative Background --}}
    <div class="absolute top-0 right-0 w-64 h-64 opacity-[0.02] pointer-events-none">
        <svg viewBox="0 0 200 200" class="w-full h-full text-gray-900">
            <defs>
                <pattern id="grid-{{ $chartId }}" width="20" height="20" patternUnits="userSpaceOnUse">
                    <circle cx="1" cy="1" r="1" fill="currentColor"/>
                </pattern>
            </defs>
            <rect fill="url(#grid-{{ $chartId }})" width="200" height="200"/>
        </svg>
    </div>
    
    {{-- Header --}}
    @if($title)
    <div class="relative z-10 px-6 py-4 border-b border-gray-100/80">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-800">{{ $title }}</h3>
                @if($subtitle)
                <p class="text-xs text-gray-500 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            
            {{-- Optional: Chart Type Indicator --}}
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></div>
                <span class="text-xs text-gray-400 font-medium">Live</span>
            </div>
        </div>
    </div>
    @endif
    
    {{-- Chart Container --}}
    <div 
        class="relative z-10 flex-1 p-4 {{ $centered ? 'flex items-center justify-center' : '' }}" 
        style="min-height: {{ $minHeight }}"
    >
        {{-- Loading Overlay (controlled by Alpine) --}}
        <div 
            x-show="isLoading" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute inset-0 bg-white/60 backdrop-blur-sm flex items-center justify-center z-20 rounded-b-2xl"
            style="display: none;"
        >
            <div class="flex flex-col items-center gap-3">
                <div class="relative">
                    <div class="w-10 h-10 border-4 border-primary-200 rounded-full"></div>
                    <div class="absolute top-0 left-0 w-10 h-10 border-4 border-primary-600 rounded-full border-t-transparent animate-spin"></div>
                </div>
                <p class="text-sm text-gray-500 font-medium">Memuat data...</p>
            </div>
        </div>
        
        {{-- Chart Canvas --}}
        @if($chartId)
        <div class="relative w-full h-full">
            <canvas id="{{ $chartId }}" class="w-full h-full"></canvas>
        </div>
        @endif
        
        {{ $slot }}
    </div>
    
    {{-- Footer --}}
    @if(isset($footer))
    <div class="relative z-10 px-6 py-3 bg-gray-50/50 border-t border-gray-100/80 rounded-b-2xl">
        {{ $footer }}
    </div>
    @endif
</div>
