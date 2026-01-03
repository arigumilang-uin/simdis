@props([
    'name',
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
    $hasError = $errors->has($name);
    $errorClass = $hasError ? 'error' : '';
    $inputClasses = "form-input {$errorClass}";
    
    // Add icon padding if icon exists
    if ($icon) {
        $inputClasses .= ' pl-10';
    }
@endphp

<div {{ $attributes->merge(['class' => 'form-group']) }}>
    @if($label)
        <label for="{{ $name }}" class="form-label {{ $required ? 'form-label-required' : '' }}">
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
            id="{{ $name }}"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            class="{{ $inputClasses }}"
            @if($placeholder) placeholder="{{ $placeholder }}" @endif
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
            {{ $attributes->except('class') }}
        >
    </div>

    @if($help)
        <p class="form-help">{{ $help }}</p>
    @endif

    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>
