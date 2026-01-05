@props([
    'href' => '#',
    'icon' => 'star',
    'title' => '',
    'description' => '',
    'color' => 'primary' // primary, emerald, amber, rose, violet, blue, cyan, indigo
])

@php
$styles = [
    'primary' => [
        'gradient' => 'from-primary-500 to-emerald-600',
        'bg' => 'bg-gradient-to-br from-primary-50 to-emerald-50',
        'icon_bg' => 'bg-gradient-to-br from-primary-500 to-emerald-600',
        'ring' => 'ring-primary-500/10',
        'arrow' => 'text-primary-500 group-hover:text-primary-600',
    ],
    'emerald' => [
        'gradient' => 'from-emerald-500 to-teal-600',
        'bg' => 'bg-gradient-to-br from-emerald-50 to-teal-50',
        'icon_bg' => 'bg-gradient-to-br from-emerald-500 to-teal-600',
        'ring' => 'ring-emerald-500/10',
        'arrow' => 'text-emerald-500 group-hover:text-emerald-600',
    ],
    'amber' => [
        'gradient' => 'from-amber-500 to-orange-600',
        'bg' => 'bg-gradient-to-br from-amber-50 to-orange-50',
        'icon_bg' => 'bg-gradient-to-br from-amber-500 to-orange-600',
        'ring' => 'ring-amber-500/10',
        'arrow' => 'text-amber-500 group-hover:text-amber-600',
    ],
    'rose' => [
        'gradient' => 'from-rose-500 to-pink-600',
        'bg' => 'bg-gradient-to-br from-rose-50 to-pink-50',
        'icon_bg' => 'bg-gradient-to-br from-rose-500 to-pink-600',
        'ring' => 'ring-rose-500/10',
        'arrow' => 'text-rose-500 group-hover:text-rose-600',
    ],
    'violet' => [
        'gradient' => 'from-violet-500 to-purple-600',
        'bg' => 'bg-gradient-to-br from-violet-50 to-purple-50',
        'icon_bg' => 'bg-gradient-to-br from-violet-500 to-purple-600',
        'ring' => 'ring-violet-500/10',
        'arrow' => 'text-violet-500 group-hover:text-violet-600',
    ],
    'blue' => [
        'gradient' => 'from-blue-500 to-indigo-600',
        'bg' => 'bg-gradient-to-br from-blue-50 to-indigo-50',
        'icon_bg' => 'bg-gradient-to-br from-blue-500 to-indigo-600',
        'ring' => 'ring-blue-500/10',
        'arrow' => 'text-blue-500 group-hover:text-blue-600',
    ],
    'cyan' => [
        'gradient' => 'from-cyan-500 to-teal-600',
        'bg' => 'bg-gradient-to-br from-cyan-50 to-teal-50',
        'icon_bg' => 'bg-gradient-to-br from-cyan-500 to-teal-600',
        'ring' => 'ring-cyan-500/10',
        'arrow' => 'text-cyan-500 group-hover:text-cyan-600',
    ],
    'indigo' => [
        'gradient' => 'from-indigo-500 to-blue-600',
        'bg' => 'bg-gradient-to-br from-indigo-50 to-blue-50',
        'icon_bg' => 'bg-gradient-to-br from-indigo-500 to-blue-600',
        'ring' => 'ring-indigo-500/10',
        'arrow' => 'text-indigo-500 group-hover:text-indigo-600',
    ],
];

$s = $styles[$color] ?? $styles['primary'];
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => "relative overflow-hidden rounded-2xl bg-white p-5 ring-1 {$s['ring']} shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group flex items-center gap-5"]) }}>
    
    {{-- Subtle background gradient on hover --}}
    <div class="absolute inset-0 {{ $s['bg'] }} opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
    
    {{-- Icon with glow --}}
    <div class="relative">
        {{-- Glow effect --}}
        <div class="absolute inset-0 {{ $s['icon_bg'] }} rounded-2xl blur-lg opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
        
        {{-- Icon box --}}
        <div class="relative w-14 h-14 rounded-2xl {{ $s['icon_bg'] }} text-white flex items-center justify-center shrink-0 shadow-lg group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
            <x-ui.icon :name="$icon" :size="26" />
        </div>
    </div>
    
    {{-- Content --}}
    <div class="relative flex-1 min-w-0">
        <h4 class="font-semibold text-gray-800 group-hover:text-gray-900 transition-colors text-base">{{ $title }}</h4>
        @if($description)
        <p class="text-sm text-gray-500 mt-0.5">{{ $description }}</p>
        @endif
    </div>
    
    {{-- Arrow indicator --}}
    <div class="relative {{ $s['arrow'] }} opacity-0 group-hover:opacity-100 -translate-x-2 group-hover:translate-x-0 transition-all duration-300">
        <x-ui.icon name="arrow-right" :size="20" />
    </div>
</a>
