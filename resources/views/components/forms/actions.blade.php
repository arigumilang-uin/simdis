@props([
    'submitLabel' => 'Simpan',
    'submitIcon' => 'save',
    'cancelUrl' => null,
    'cancelLabel' => 'Batal',
    'showCancel' => true,
    'submitDisabled' => false,
])

<div {{ $attributes->merge(['class' => 'flex items-center gap-3 pt-4 border-t border-gray-100']) }}>
    <button 
        type="submit" 
        class="btn btn-primary"
        @if($submitDisabled) disabled @endif
        {{ $attributes->whereStartsWith('x-bind:disabled') }}
        {{ $attributes->whereStartsWith(':disabled') }}
    >
        @if($submitIcon)
            <x-ui.icon :name="$submitIcon" size="18" />
        @endif
        <span>{{ $submitLabel }}</span>
    </button>
    
    @if($showCancel && $cancelUrl)
        <a href="{{ $cancelUrl }}" class="btn btn-secondary">{{ $cancelLabel }}</a>
    @endif
    
    {{ $slot }}
</div>
