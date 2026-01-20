@props(['icon', 'label' => null, 'href' => null, 'type' => 'a'])

@php
    $baseClasses = "flex w-full items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors cursor-pointer group";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClasses]) }}>
        @if($icon)
            <x-ui.icon :name="$icon" size="16" class="text-gray-400 group-hover:text-indigo-600 transition-colors" />
        @endif
        <span class="font-medium">{{ $slot->isEmpty() ? $label : $slot }}</span>
    </a>
@else
    <button type="{{ $type == 'a' ? 'button' : $type }}" {{ $attributes->merge(['class' => $baseClasses]) }}>
        @if($icon)
            <x-ui.icon :name="$icon" size="16" class="text-gray-400 group-hover:text-current transition-colors" />
        @endif
        <span class="font-medium">{{ $slot->isEmpty() ? $label : $slot }}</span>
    </button>
@endif
