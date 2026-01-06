@props([
    'label' => null,
])

@if($label)
<div {{ $attributes->merge(['class' => 'relative py-2']) }}>
    <div class="absolute inset-0 flex items-center">
        <div class="w-full border-t border-gray-200"></div>
    </div>
    <div class="relative flex justify-center">
        <span class="px-3 bg-white text-sm text-gray-500">{{ $label }}</span>
    </div>
</div>
@else
<hr {{ $attributes->merge(['class' => 'border-gray-100 my-2']) }}>
@endif
