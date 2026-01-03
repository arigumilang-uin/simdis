@props([
    'name' => 'modal',
    'title' => '',
    'maxWidth' => 'lg', // sm, md, lg, xl, 2xl
    'closeable' => true,
    'showHeader' => true,
    'showFooter' => false,
])

@php
$maxWidthClasses = [
    'sm' => 'max-w-sm',
    'md' => 'max-w-md',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
];
$maxWidthClass = $maxWidthClasses[$maxWidth] ?? 'max-w-lg';
@endphp

<div 
    x-data="{ open: false }" 
    @open-{{ $name }}.window="open = true"
    @close-{{ $name }}.window="open = false"
    @keydown.escape.window="{{ $closeable ? 'open = false' : '' }}"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    {{ $attributes }}
>
    {{-- Backdrop --}}
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"
        @if($closeable) @click="open = false" @endif
    ></div>
    
    {{-- Modal Content --}}
    <div class="flex min-h-full items-center justify-center p-4">
        <div 
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="relative w-full {{ $maxWidthClass }} bg-white rounded-2xl shadow-2xl"
            @click.stop
        >
            {{-- Header --}}
            @if($showHeader)
            <div class="p-4 sm:p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    @if(isset($header))
                        {{ $header }}
                    @else
                        <h3 class="text-lg font-bold text-gray-800">{{ $title }}</h3>
                    @endif
                    @if($closeable)
                    <button type="button" @click="open = false" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <x-ui.icon name="x" :size="20" />
                    </button>
                    @endif
                </div>
            </div>
            @endif

            {{-- Body --}}
            <div class="p-4 sm:p-6">
                {{ $slot }}
            </div>

            {{-- Footer --}}
            @if($showFooter && isset($footer))
            <div class="p-4 sm:p-6 border-t border-gray-100 bg-gray-50/50 rounded-b-2xl">
                {{ $footer }}
            </div>
            @endif
        </div>
    </div>
</div>
