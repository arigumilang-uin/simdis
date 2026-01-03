@props([
    'type' => 'info', // info, warning, danger, success
    'title' => null,
    'dismissible' => false,
    'icon' => null // override default icon
])

@php
$styles = [
    'info' => [
        'bg' => 'bg-indigo-50',
        'border' => 'border-indigo-100',
        'borderLeft' => 'border-l-4 border-l-indigo-500',
        'text' => 'text-indigo-800',
        'textLight' => 'text-indigo-700',
        'icon' => 'info',
        'iconClass' => 'text-indigo-500'
    ],
    'warning' => [
        'bg' => 'bg-amber-50',
        'border' => 'border-amber-100',
        'borderLeft' => 'border-l-4 border-l-amber-500',
        'text' => 'text-amber-800',
        'textLight' => 'text-amber-700',
        'icon' => 'alert-triangle',
        'iconClass' => 'text-amber-600'
    ],
    'danger' => [
        'bg' => 'bg-red-50',
        'border' => 'border-red-100',
        'borderLeft' => 'border-l-4 border-l-red-500',
        'text' => 'text-red-800',
        'textLight' => 'text-red-700',
        'icon' => 'alert-circle',
        'iconClass' => 'text-red-600'
    ],
    'success' => [
        'bg' => 'bg-emerald-50',
        'border' => 'border-emerald-100',
        'borderLeft' => 'border-l-4 border-l-emerald-500',
        'text' => 'text-emerald-800',
        'textLight' => 'text-emerald-700',
        'icon' => 'check-circle',
        'iconClass' => 'text-emerald-600'
    ],
];

$style = $styles[$type] ?? $styles['info'];
$iconName = $icon ?? $style['icon'];
@endphp

<div {{ $attributes->merge(['class' => "p-4 {$style['bg']} border {$style['border']} rounded-xl"]) }}
    @if($dismissible) x-data="{ show: true }" x-show="show" x-transition @endif
>
    <div class="flex gap-3">
        <x-ui.icon :name="$iconName" :size="20" class="{{ $style['iconClass'] }} shrink-0 mt-0.5" />
        <div class="flex-1">
            @if($title)
                <p class="font-medium {{ $style['text'] }}">{{ $title }}</p>
                <div class="text-sm {{ $style['textLight'] }} mt-1">
                    {{ $slot }}
                </div>
            @else
                <div class="text-sm {{ $style['textLight'] }}">
                    {{ $slot }}
                </div>
            @endif
        </div>
        @if($dismissible)
            <button type="button" @click="show = false" class="shrink-0 {{ $style['textLight'] }} hover:{{ $style['text'] }} transition-colors">
                <x-ui.icon name="x" :size="18" />
            </button>
        @endif
    </div>
</div>
