@props([
    'name',
    'id' => null,
    'label' => null,
    'options' => [], // Array of options or Collection
    'value' => null, // Selected value
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'help' => null,
    'optionValue' => 'id', // Key for value in options array/collection
    'optionLabel' => 'name', // Key for label in options array/collection
    'simple' => false, // Set true if options is simple ['key' => 'value'] array
])

@php
    $inputId = $id ?? $name;
    $hasError = $errors->has($name);
    $errorClass = $hasError ? 'error' : '';
    // Removed form-input to avoid conflict with form-select (arrow icon positioning)
    $inputClasses = "form-select w-full border-slate-200 rounded-lg text-sm focus:border-indigo-500 focus:ring-indigo-500/10 {$errorClass}";
    $selectedValue = old($name, $value);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'form-group']) }}>
    @if($label)
        <label for="{{ $inputId }}" class="form-label {{ $required ? 'form-label-required' : '' }}">
            {{ $label }}
        </label>
    @endif

    <select 
        id="{{ $inputId }}"
        name="{{ $name }}"
        class="{{ $inputClasses }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        {{ $attributes->except(['class', 'id']) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach($options as $key => $option)
            @php
                if ($simple) {
                    $optValue = $key;
                    $optLabel = $option;
                } else {
                    // Check if object or array
                    $item = is_object($option) ? $option : (object)$option;
                    $optValue = $item->{$optionValue};
                    $optLabel = $item->{$optionLabel};
                }
                
                $isSelected = $selectedValue == $optValue;
            @endphp
            <option value="{{ $optValue }}" {{ $isSelected ? 'selected' : '' }}>
                {{ $optLabel }}
            </option>
        @endforeach
        
        {{ $slot }} {{-- Custom options if needed --}}
    </select>

    @if($help)
        <p class="form-help">{{ $help }}</p>
    @endif

    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>
