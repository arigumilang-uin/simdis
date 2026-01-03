@props([
    'href' => '#',
    'icon' => 'star',
    'title' => '',
    'description' => '',
    'color' => 'primary' // primary, emerald, amber, rose, violet, blue
])

@php
$styles = [
    'primary' => [
        'bg' => 'bg-primary-50',
        'icon' => 'text-primary-600',
        'title_hover' => 'group-hover:text-primary-600',
        'border_hover' => 'hover:border-primary-200',
        'shadow_hover' => 'hover:shadow-primary-100/50',
    ],
    'emerald' => [
        'bg' => 'bg-emerald-50',
        'icon' => 'text-emerald-600',
        'title_hover' => 'group-hover:text-emerald-600',
        'border_hover' => 'hover:border-emerald-200',
        'shadow_hover' => 'hover:shadow-emerald-100/50',
    ],
    'amber' => [
        'bg' => 'bg-amber-50',
        'icon' => 'text-amber-600',
        'title_hover' => 'group-hover:text-amber-600',
        'border_hover' => 'hover:border-amber-200',
        'shadow_hover' => 'hover:shadow-amber-100/50',
    ],
    'rose' => [
        'bg' => 'bg-rose-50',
        'icon' => 'text-rose-600',
        'title_hover' => 'group-hover:text-rose-600',
        'border_hover' => 'hover:border-rose-200',
        'shadow_hover' => 'hover:shadow-rose-100/50',
    ],
    'violet' => [
        'bg' => 'bg-violet-50',
        'icon' => 'text-violet-600',
        'title_hover' => 'group-hover:text-violet-600',
        'border_hover' => 'hover:border-violet-200',
        'shadow_hover' => 'hover:shadow-violet-100/50',
    ],
    'blue' => [
        'bg' => 'bg-blue-50',
        'icon' => 'text-blue-600',
        'title_hover' => 'group-hover:text-blue-600',
        'border_hover' => 'hover:border-blue-200',
        'shadow_hover' => 'hover:shadow-blue-100/50',
    ],
];

$s = $styles[$color] ?? $styles['primary'];
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => "card flex items-center gap-4 p-4 {$s['border_hover']} hover:shadow-lg {$s['shadow_hover']} transition-all group"]) }}>
    <div class="w-12 h-12 rounded-xl {{ $s['bg'] }} {{ $s['icon'] }} flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
        <x-ui.icon :name="$icon" :size="24" />
    </div>
    <div class="flex-1 min-w-0">
        <h4 class="font-semibold text-gray-800 {{ $s['title_hover'] }} transition-colors">{{ $title }}</h4>
        <p class="text-sm text-gray-500">{{ $description }}</p>
    </div>
</a>
