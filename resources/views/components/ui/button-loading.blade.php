{{-- 
    Button Loading Component
    
    Usage:
    <x-ui.button-loading 
        type="submit" 
        :loading="$isSubmitting"
        text="Simpan"
        loadingText="Menyimpan..."
    />
    
    With Alpine:
    <x-ui.button-loading 
        x-bind:loading="isSubmitting"
        text="Simpan"
    />
--}}

@props([
    'type' => 'submit',
    'text' => 'Simpan',
    'loadingText' => null,
    'loading' => false,
    'variant' => 'primary',
    'size' => 'md',
    'icon' => 'check',
])

@php
$variants = [
    'primary' => 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm',
    'secondary' => 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200',
    'danger' => 'bg-red-600 hover:bg-red-700 text-white shadow-sm',
    'success' => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm',
];

$sizes = [
    'sm' => 'px-3 py-1.5 text-xs',
    'md' => 'px-5 py-2.5 text-sm',
    'lg' => 'px-6 py-3 text-base',
];

$variantClass = $variants[$variant] ?? $variants['primary'];
$sizeClass = $sizes[$size] ?? $sizes['md'];

$loadingTextFinal = $loadingText ?? $text . '...';
@endphp

<button 
    type="{{ $type }}"
    {{ $attributes->merge([
        'class' => "inline-flex items-center justify-center gap-2 rounded-lg font-semibold transition-all active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed disabled:active:scale-100 {$variantClass} {$sizeClass}"
    ]) }}
    x-bind:disabled="{{ $loading ? 'true' : 'loading' }}"
    x-bind:class="{ 'opacity-60 cursor-wait': {{ $loading ? 'true' : 'loading' }} }"
>
    {{-- Loading State --}}
    <template x-if="{{ $loading ? 'true' : 'loading' }}">
        <span class="flex items-center gap-2">
            <x-ui.loading-spinner size="sm" />
            <span>{{ $loadingTextFinal }}</span>
        </span>
    </template>
    
    {{-- Normal State --}}
    <template x-if="!{{ $loading ? 'true' : 'loading' }}">
        <span class="flex items-center gap-2">
            @if($icon)
                <x-ui.icon :name="$icon" size="16" />
            @endif
            <span>{{ $text }}</span>
        </span>
    </template>
</button>
