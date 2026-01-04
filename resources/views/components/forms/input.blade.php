@props([
    'name',
    'id' => null,
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'help' => null,
    'icon' => null
])

@php
    $inputId = $id ?? $name;
    $hasError = $errors->has($name);
    $errorClass = $hasError ? 'error' : '';
    $inputClasses = "form-input {$errorClass}";
    
    // Add icon padding if icon exists
    if ($icon) {
        $inputClasses .= ' pl-10';
    }
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'form-group']) }}>
    @if($label)
        <label for="{{ $inputId }}" class="form-label {{ $required ? 'form-label-required' : '' }}">
            {{ $label }}
        </label>
    @endif

    <div class="relative">
        @if($icon)
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                <x-ui.icon :name="$icon" :size="18" />
            </div>
        @endif

        <input 
            type="{{ $type }}"
            id="{{ $inputId }}"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            class="{{ $inputClasses }}"
            @if($placeholder) placeholder="{{ $placeholder }}" @endif
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
            {{ $attributes->except(['class', 'id']) }}
        >
    </div>

    @if($help)
        <p class="form-help">{{ $help }}</p>
    @endif

    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>
