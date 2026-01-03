@props([
    'title' => '',
    'subtitle' => '',
    'chartId' => 'chart',
    'minHeight' => '300px',
    'centered' => false
])

<div {{ $attributes->merge(['class' => 'card h-full flex flex-col']) }}>
    @if($title)
    <div class="card-header border-b border-gray-100">
        <h3 class="card-title">{{ $title }}</h3>
        @if($subtitle)
        <span class="text-xs text-gray-500 font-normal">{{ $subtitle }}</span>
        @endif
    </div>
    @endif
    
    <div class="card-body flex-1 relative {{ $centered ? 'flex items-center justify-center' : '' }}" style="min-height: {{ $minHeight }}">
        @if($chartId)
            <canvas id="{{ $chartId }}"></canvas>
        @endif
        {{ $slot }}
    </div>
    
    @if(isset($footer))
    <div class="card-footer border-t border-gray-100">
        {{ $footer }}
    </div>
    @endif
</div>
