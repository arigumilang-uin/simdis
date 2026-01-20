{{--
    Filter Date Input Component - For date filters
--}}

@props([
    'name' => '',
    'label' => '',
    'id' => null,
])

@php
    $inputId = $id ?? $name;
@endphp

<div>
    @if($label)
        <label for="{{ $inputId }}" class="block text-xs font-medium text-gray-500 mb-1.5">{{ $label }}</label>
    @endif
    <input 
        type="date" 
        id="{{ $inputId }}" 
        name="{{ $name }}"
        {{ $attributes->merge(['class' => 'w-full rounded-lg border border-gray-200 bg-white text-sm text-gray-700 py-2 px-3 hover:border-gray-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors']) }}
    >
</div>
