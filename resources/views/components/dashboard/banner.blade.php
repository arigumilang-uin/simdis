@props([
    'variant' => 'slate', // primary, slate, etc.. (now acts as subtle accent)
    'badge' => '',
    'title' => 'Selamat Datang!',
    'subtitle' => '',
    'showDate' => false
])

@php
// Accent colors for the blobs/decorations based on variant
$accents = [
    'primary' => ['blob1' => 'bg-emerald-500', 'blob2' => 'bg-teal-400', 'dot' => 'bg-emerald-400'],
    'slate' => ['blob1' => 'bg-slate-500', 'blob2' => 'bg-gray-400', 'dot' => 'bg-green-400'], // Default Operator ish
    'violet' => ['blob1' => 'bg-violet-500', 'blob2' => 'bg-purple-400', 'dot' => 'bg-violet-400'],
    'rose' => ['blob1' => 'bg-rose-500', 'blob2' => 'bg-pink-400', 'dot' => 'bg-rose-400'],
    'amber' => ['blob1' => 'bg-amber-500', 'blob2' => 'bg-orange-400', 'dot' => 'bg-amber-400'],
    'blue' => ['blob1' => 'bg-blue-500', 'blob2' => 'bg-indigo-400', 'dot' => 'bg-blue-400'],
];
$style = $accents[$variant] ?? $accents['slate'];
@endphp

<div {{ $attributes->merge(['class' => "relative rounded-2xl bg-gradient-to-r from-slate-800 to-primary-900 p-6 overflow-hidden text-white shadow-xl shadow-primary-900/10"]) }}>
    
    {{-- Decorative elements (Matches Operator) --}}
    <div class="absolute top-0 right-0 w-64 h-64 {{ $style['blob1'] }} opacity-10 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-40 h-40 {{ $style['blob2'] }} opacity-10 rounded-full blur-2xl -ml-10 -mb-10 pointer-events-none"></div>
    
    <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            @if($badge)
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-sm border border-white/10 text-xs font-medium text-primary-100 mb-3">
                <span class="w-1.5 h-1.5 rounded-full {{ $style['dot'] }} animate-pulse"></span>
                {{ $badge }}
            </div>
            @endif
            
            <h2 class="text-xl md:text-2xl font-bold">
                {{ $title }}
            </h2>
            
            @if($subtitle)
            <p class="text-slate-300 text-sm opacity-90 mt-1">
                {{ $subtitle }}
            </p>
            @endif
        </div>
        
        <div class="flex items-center gap-4 shrink-0">
            {{ $slot }}
            
            @if($showDate)
            <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/10 shadow-inner">
                <div class="bg-primary-500/20 p-2 rounded-lg text-primary-200">
                    <x-ui.icon name="calendar" size="24" />
                </div>
                <div>
                    <span class="block text-2xl font-bold leading-none tracking-tight">{{ date('d') }}</span>
                    <span class="block text-xs uppercase tracking-wider text-primary-100 opacity-80">{{ date('F Y') }}</span>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
