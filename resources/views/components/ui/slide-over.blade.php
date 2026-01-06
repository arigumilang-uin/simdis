@props([
    'id' => 'slide-over',
    'title' => '',
    'size' => 'lg', // sm, md, lg, xl, 2xl
    'icon' => null,
])

<div 
    id="{{ $id }}"
    x-data="slideOver({ size: '{{ $size }}', title: '{{ $title }}' })"
    x-on:keydown.escape.window="handleEscape($event)"
    x-on:open-{{ $id }}.window="show($event.detail || {})"
    x-on:close-{{ $id }}.window="hide()"
    x-cloak
    {{ $attributes }}
>
    {{-- Backdrop --}}
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="hide()"
        class="slide-over-backdrop"
    ></div>
    
    {{-- Panel --}}
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="transform translate-x-full sm:translate-x-full"
        x-transition:enter-end="transform translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="transform translate-x-0"
        x-transition:leave-end="transform translate-x-full sm:translate-x-full"
        class="slide-over-panel size-{{ $size }}"
        :class="{ 'open': open }"
        @click.stop
    >
        {{-- Loading Overlay --}}
        <div x-show="loading" x-transition class="slide-over-loading">
            <div class="flex flex-col items-center gap-3">
                <svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-sm text-gray-500">Memuat...</span>
            </div>
        </div>
        
        {{-- Header --}}
        <div class="slide-over-header">
            <h2 class="slide-over-title">
                @if($icon)
                    <span class="w-8 h-8 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center">
                        <x-ui.icon :name="$icon" size="18" />
                    </span>
                @endif
                <span x-text="title || '{{ $title }}'">{{ $title }}</span>
            </h2>
            <button type="button" @click="hide()" class="slide-over-close">
                <x-ui.icon name="x" size="18" />
            </button>
        </div>
        
        {{-- Body --}}
        <div class="slide-over-body">
            {{ $slot }}
        </div>
        
        {{-- Footer (if provided) --}}
        @if(isset($footer))
        <div class="slide-over-footer">
            {{ $footer }}
        </div>
        @endif
    </div>
</div>
