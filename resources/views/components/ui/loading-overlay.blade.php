{{-- 
    Loading Overlay Component
    
    Usage (inside a relative container):
    <div class="relative">
        <table>...</table>
        <x-ui.loading-overlay />
    </div>
    
    Custom show condition:
    <x-ui.loading-overlay show="isProcessing" />
    
    With custom styling:
    <x-ui.loading-overlay class="bg-indigo-50/80" />
--}}

@props(['show' => 'isLoading'])

<div x-show="{{ $show }}" 
     x-cloak
     x-transition:enter="transition-opacity duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     {{ $attributes->merge(['class' => 'absolute inset-0 bg-white/70 backdrop-blur-[1px] flex items-center justify-center z-10 rounded-lg']) }}>
    <div class="flex flex-col items-center gap-2">
        <x-ui.loading-spinner size="lg" class="text-indigo-600" />
        <span class="text-xs font-medium text-slate-500">Memuat...</span>
    </div>
</div>
