@props([
    'variant' => 'primary', // primary, violet, slate, emerald, amber
    'badge' => '',
    'title' => 'Selamat Datang!',
    'subtitle' => '',
    'showDate' => false
])

@php
$variants = [
    'primary' => 'from-primary-600 to-primary-800',
    'violet' => 'from-violet-600 to-purple-700',
    'slate' => 'from-slate-800 to-primary-900',
    'emerald' => 'from-emerald-600 to-teal-700',
    'amber' => 'from-amber-500 to-orange-600',
    'rose' => 'from-rose-500 to-pink-600',
    'blue' => 'from-blue-600 to-indigo-700',
];
$gradientClass = $variants[$variant] ?? $variants['primary'];

$badgeColors = [
    'primary' => 'text-primary-100 bg-green-300',
    'violet' => 'text-purple-100 bg-purple-300',
    'slate' => 'text-primary-100 bg-green-400',
    'emerald' => 'text-emerald-100 bg-emerald-300',
    'amber' => 'text-amber-100 bg-amber-300',
    'rose' => 'text-rose-100 bg-rose-300',
    'blue' => 'text-blue-100 bg-blue-300',
];
$badgeDotColor = $badgeColors[$variant] ?? $badgeColors['primary'];
@endphp

<div {{ $attributes->merge(['class' => "relative rounded-2xl bg-gradient-to-r {$gradientClass} p-6 overflow-hidden text-white shadow-xl"]) }}>
    {{-- Decorative Blurs --}}
    <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl -ml-10 -mb-10 pointer-events-none"></div>
    
    <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            @if($badge)
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-sm border border-white/10 text-xs font-medium {{ explode(' ', $badgeDotColor)[0] }} mb-3">
                <span class="w-1.5 h-1.5 rounded-full {{ explode(' ', $badgeDotColor)[1] }} animate-pulse"></span>
                {{ $badge }}
            </div>
            @endif
            
            <h2 class="text-xl md:text-2xl font-bold">{{ $title }}</h2>
            
            @if($subtitle)
            <p class="text-white/80 text-sm mt-1">{{ $subtitle }}</p>
            @endif
        </div>
        
        <div class="flex items-center gap-3">
            {{-- Optional Slot for Actions/Extra Content --}}
            {{ $slot }}
            
            @if($showDate)
            <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/10 shadow-inner">
                <div class="bg-white/10 p-2 rounded-lg text-white/80">
                    <x-ui.icon name="calendar" :size="24" />
                </div>
                <div>
                    <span class="block text-2xl font-bold leading-none tracking-tight">{{ date('d') }}</span>
                    <span class="block text-xs uppercase tracking-wider text-white/70">{{ date('F Y') }}</span>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
