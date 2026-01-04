@props([
    'title',
    'subtitle' => null,
    'total' => null,
    'totalLabel' => 'data',
    'icon' => null
])

<div class="page-header-stylish">
    <div class="page-header-text">
        <h1 class="page-header-title">{{ $title }}</h1>
        @if($subtitle)
            <p class="page-header-subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    
    @if($total !== null)
        <div class="page-header-stats" 
             x-data="{ total: {{ $total }} }"
             @update-total-data.window="total = $event.detail.total">
            @if($icon)
                <x-ui.icon :name="$icon" size="16" />
            @else
                <x-ui.icon name="database" size="16" />
            @endif
            <span>Total: <span class="count" x-text="total">{{ $total }}</span> {{ $totalLabel }}</span>
        </div>
    @endif
    
    {{ $slot }}
</div>
