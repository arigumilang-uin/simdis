@props([
    'name',
    'label' => null,
    'value' => null,
    'required' => false,
    'disabled' => false,
    'help' => null
])

@php
    $hasError = $errors->has($name);
    $errorClass = $hasError ? 'error' : '';
    $inputClasses = "form-input {$errorClass}";
@endphp

<div {{ $attributes->merge(['class' => 'form-group']) }}>
    @if($label)
        <label for="{{ $name }}" class="form-label {{ $required ? 'form-label-required' : '' }}">
            {{ $label }}
        </label>
    @endif

    <div class="relative">
        <input 
            type="date"
            id="{{ $name }}"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            class="{{ $inputClasses }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
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
