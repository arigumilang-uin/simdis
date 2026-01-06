@props([
    'cols' => 2, // 1, 2, 3, 4
    'gap' => 4, // 2, 3, 4, 6
])

@php
$colClasses = [
    1 => 'grid-cols-1',
    2 => 'grid-cols-1 md:grid-cols-2',
    3 => 'grid-cols-1 md:grid-cols-3',
    4 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
];
$gapClasses = [
    2 => 'gap-2',
    3 => 'gap-3',
    4 => 'gap-4',
    6 => 'gap-6',
];
$colClass = $colClasses[$cols] ?? 'grid-cols-1 md:grid-cols-2';
$gapClass = $gapClasses[$gap] ?? 'gap-4';
@endphp

<div {{ $attributes->merge(['class' => "grid {$colClass} {$gapClass}"]) }}>
    {{ $slot }}
</div>
