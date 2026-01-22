{{--
    Filter Search Component - Wide search input for use with action-bar
--}}

@props([
    'placeholder' => 'Cari...',
    'name' => 'search',
    'id' => null,
])

@php
    $inputId = $id ?? $name;
@endphp

<input 
    type="text" 
    id="{{ $inputId }}" 
    name="{{ $name }}"
    {{ $attributes->merge(['class' => 'w-full md:w-80 rounded-xl border-0 bg-gray-100/80 text-sm text-gray-800 py-2.5 pl-10 pr-4 hover:bg-gray-100 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:shadow-lg focus:shadow-indigo-500/5 transition-all duration-200 placeholder-gray-400', 'placeholder' => $placeholder]) }}
>
