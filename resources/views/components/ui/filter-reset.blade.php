{{--
    Filter Reset Button - Compact reset for filter panel header
--}}

@props([])

<button 
    type="button" 
    {{ $attributes->merge(['class' => 'text-xs font-medium text-gray-400 hover:text-red-500 transition-colors']) }}
>
    Reset
</button>
