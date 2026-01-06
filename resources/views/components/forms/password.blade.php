@props([
    'name',
    'id' => null,
    'label' => null,
    'placeholder' => null,
    'required' => false,
    'help' => null,
    'autocomplete' => 'current-password',
])

@php
$inputId = $id ?? $name;
$hasError = $errors->has($name);
$errorClass = $hasError ? 'error' : '';
@endphp

<div class="form-group" x-data="{ show: false }">
    @if($label)
        <label for="{{ $inputId }}" class="form-label {{ $required ? 'form-label-required' : '' }}">
            {{ $label }}
        </label>
    @endif
    
    <div class="relative">
        <input 
            :type="show ? 'text' : 'password'" 
            id="{{ $inputId }}" 
            name="{{ $name }}"
            class="form-input !pr-10 {{ $errorClass }}"
            autocomplete="{{ $autocomplete }}"
            @if($placeholder) placeholder="{{ $placeholder }}" @endif
            @if($required) required @endif
            {{ $attributes->except(['class', 'id', 'type']) }}
        >
        <button 
            type="button" 
            @click="show = !show" 
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
            tabindex="-1"
        >
            <template x-if="!show">
                <x-ui.icon name="eye" size="18" />
            </template>
            <template x-if="show">
                <x-ui.icon name="eye-off" size="18" />
            </template>
        </button>
    </div>
    
    @if($help)
        <p class="form-help">{{ $help }}</p>
    @endif
    
    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>
