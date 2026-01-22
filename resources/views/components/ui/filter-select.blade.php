{{--
    Filter Select Component - Dropdown for filter panel
    
    Usage:
    <x-ui.filter-select 
        label="Status"
        x-model="filters.status"
        :options="$statuses"
        optionValue="id"
        optionLabel="name"
        placeholder="All Status"
    />
--}}

@props([
    'name' => '',
    'label' => '',
    'placeholder' => '',
    'id' => null,
    'options' => [],
    'optionValue' => 'value',
    'optionLabel' => 'label',
])

@php
    $inputId = $id ?? $name;
@endphp

<div>
    @if($label)
        <label for="{{ $inputId }}" class="block text-xs font-medium text-gray-500 mb-1.5">{{ $label }}</label>
    @endif
    <div class="relative">
        <select 
            id="{{ $inputId }}" 
            name="{{ $name }}"
            {{ $attributes->merge(['class' => 'w-full appearance-none rounded-lg border border-gray-200 bg-white text-sm text-gray-700 py-2 pl-3 pr-8 hover:border-gray-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors cursor-pointer']) }}
        >
            @if($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            @foreach($options as $option)
                @php
                    $value = is_array($option) ? ($option[$optionValue] ?? '') : (is_object($option) ? ($option->{$optionValue} ?? '') : $option);
                    $labelText = is_array($option) ? ($option[$optionLabel] ?? '') : (is_object($option) ? ($option->{$optionLabel} ?? '') : $option);
                @endphp
                <option value="{{ $value }}">{{ $labelText }}</option>
            @endforeach
            {{ $slot }}
        </select>
        <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none">
            <x-ui.icon name="chevron-down" size="14" class="text-gray-400" />
        </div>
    </div>
</div>
