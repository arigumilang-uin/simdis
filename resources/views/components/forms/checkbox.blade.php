@props([
    'name',
    'id' => null,
    'label' => null,
    'description' => null,
    'value' => '1',
    'checked' => false,
    'disabled' => false,
    'variant' => 'default', // default, info, success, warning, danger
])

@php
$inputId = $id ?? $name;
$isChecked = old($name, $checked) ? true : false;

$variants = [
    'default' => [
        'bg' => 'bg-gray-50 hover:bg-gray-100',
        'border' => 'border-gray-200',
        'check' => 'text-gray-600',
        'label' => 'text-gray-800',
        'desc' => 'text-gray-500',
    ],
    'info' => [
        'bg' => 'bg-blue-50 hover:bg-blue-100',
        'border' => 'border-blue-100',
        'check' => 'text-blue-600',
        'label' => 'text-blue-800',
        'desc' => 'text-blue-600',
    ],
    'success' => [
        'bg' => 'bg-emerald-50 hover:bg-emerald-100',
        'border' => 'border-emerald-100',
        'check' => 'text-emerald-600',
        'label' => 'text-emerald-800',
        'desc' => 'text-emerald-600',
    ],
    'warning' => [
        'bg' => 'bg-amber-50 hover:bg-amber-100',
        'border' => 'border-amber-100',
        'check' => 'text-amber-600',
        'label' => 'text-amber-800',
        'desc' => 'text-amber-600',
    ],
    'danger' => [
        'bg' => 'bg-rose-50 hover:bg-rose-100',
        'border' => 'border-rose-100',
        'check' => 'text-rose-600',
        'label' => 'text-rose-800',
        'desc' => 'text-rose-600',
    ],
];
$style = $variants[$variant] ?? $variants['default'];
@endphp

<div class="form-group">
    <label class="flex items-start gap-3 cursor-pointer p-3 {{ $style['bg'] }} rounded-lg border {{ $style['border'] }} transition-colors {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}">
        <input 
            type="checkbox" 
            id="{{ $inputId }}"
            name="{{ $name }}" 
            value="{{ $value }}" 
            {{ $isChecked ? 'checked' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            class="w-4 h-4 mt-0.5 rounded border-gray-300 {{ $style['check'] }} focus:ring-2 focus:ring-offset-0"
            {{ $attributes->except(['class']) }}
        >
        <div class="flex-1">
            @if($label)
                <span class="text-sm font-medium {{ $style['label'] }}">{{ $label }}</span>
            @endif
            @if($description)
                <p class="text-xs {{ $style['desc'] }} mt-0.5">{{ $description }}</p>
            @endif
            {{ $slot }}
        </div>
    </label>
    
    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>
