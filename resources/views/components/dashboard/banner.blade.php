@props([
    'variant' => 'primary', // primary, violet, slate, emerald, amber, rose, blue, cyan
    'badge' => '',
    'title' => 'Selamat Datang!',
    'subtitle' => '',
    'showDate' => false
])

@php
// Modern gradient variants with mesh-like appearance
$variants = [
    'primary' => 'from-primary-600 via-primary-700 to-emerald-800',
    'violet' => 'from-violet-600 via-purple-700 to-indigo-800',
    'slate' => 'from-slate-700 via-slate-800 to-gray-900',
    'emerald' => 'from-emerald-600 via-teal-700 to-cyan-800',
    'amber' => 'from-amber-500 via-orange-600 to-red-700',
    'rose' => 'from-rose-500 via-pink-600 to-purple-700',
    'blue' => 'from-blue-600 via-indigo-700 to-purple-800',
    'cyan' => 'from-cyan-500 via-teal-600 to-emerald-700',
];
$gradientClass = $variants[$variant] ?? $variants['primary'];

// Badge colors
$badgeColors = [
    'primary' => ['text' => 'text-emerald-100', 'dot' => 'bg-emerald-400'],
    'violet' => ['text' => 'text-purple-100', 'dot' => 'bg-purple-400'],
    'slate' => ['text' => 'text-slate-100', 'dot' => 'bg-green-400'],
    'emerald' => ['text' => 'text-emerald-100', 'dot' => 'bg-cyan-400'],
    'amber' => ['text' => 'text-orange-100', 'dot' => 'bg-yellow-400'],
    'rose' => ['text' => 'text-pink-100', 'dot' => 'bg-rose-400'],
    'blue' => ['text' => 'text-indigo-100', 'dot' => 'bg-blue-400'],
    'cyan' => ['text' => 'text-cyan-100', 'dot' => 'bg-teal-400'],
];
$badge_style = $badgeColors[$variant] ?? $badgeColors['primary'];
@endphp

<div {{ $attributes->merge(['class' => "relative rounded-3xl bg-gradient-to-br {$gradientClass} p-6 md:p-8 overflow-hidden text-white shadow-2xl ring-1 ring-white/10"]) }}>
    
    {{-- Animated Background Elements --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        {{-- Large blur --}}
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/10 rounded-full blur-3xl animate-pulse" style="animation-duration: 4s;"></div>
        <div class="absolute -bottom-32 -left-32 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
        
        {{-- Grid pattern overlay --}}
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 24px 24px;"></div>
        
        {{-- Floating shapes --}}
        <div class="absolute top-1/2 right-1/4 w-20 h-20 border border-white/10 rounded-2xl rotate-12 hidden md:block"></div>
        <div class="absolute bottom-1/4 right-1/3 w-12 h-12 border border-white/5 rounded-xl -rotate-6 hidden md:block"></div>
    </div>
    
    {{-- Content --}}
    <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="flex-1">
            {{-- Badge --}}
            @if($badge)
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-semibold {{ $badge_style['text'] }} mb-4 shadow-lg">
                <span class="w-2 h-2 rounded-full {{ $badge_style['dot'] }} animate-pulse shadow-lg" style="box-shadow: 0 0 8px currentColor;"></span>
                {{ $badge }}
            </div>
            @endif
            
            {{-- Title --}}
            <h2 class="text-2xl md:text-3xl font-bold tracking-tight leading-tight">
                {{ $title }}
            </h2>
            
            {{-- Subtitle --}}
            @if($subtitle)
            <p class="text-white/70 text-sm md:text-base mt-2 max-w-lg">{{ $subtitle }}</p>
            @endif
        </div>
        
        {{-- Right Side --}}
        <div class="flex items-center gap-4 shrink-0">
            {{-- Optional Slot for Actions --}}
            {{ $slot }}
            
            {{-- Date Widget --}}
            @if($showDate)
            <div class="flex items-center gap-4 bg-white/10 backdrop-blur-xl px-5 py-4 rounded-2xl border border-white/20 shadow-xl">
                {{-- Calendar Icon --}}
                <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center">
                    <x-ui.icon name="calendar" :size="24" class="text-white/90" />
                </div>
                
                {{-- Date Info --}}
                <div>
                    <span class="block text-3xl font-bold leading-none tracking-tight">{{ date('d') }}</span>
                    <span class="block text-xs uppercase tracking-widest text-white/60 mt-1">{{ date('F Y') }}</span>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
