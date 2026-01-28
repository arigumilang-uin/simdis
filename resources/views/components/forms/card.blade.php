@props([
    'title' => null,
    'subtitle' => null,
    'maxWidth' => 'full',
    'action' => null,
    'method' => 'POST',
    'hasFiles' => false,
    'layout' => 'default', // default, sidebar (main 2/3 + sidebar 1/3)
])

@php
$widthClasses = [
    'sm' => 'max-w-sm',
    'md' => 'max-w-md',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
    '3xl' => 'max-w-3xl',
    '4xl' => 'max-w-4xl',
    '5xl' => 'max-w-5xl',
    '6xl' => 'max-w-6xl',
    'full' => 'w-full max-w-none',
];
$widthClass = $widthClasses[$maxWidth] ?? 'w-full max-w-none';
@endphp

<div class="form-page-container {{ $widthClass }}">
    <div class="form-card {{ $layout === 'sidebar' ? '!bg-transparent !border-0 !shadow-none !p-0' : '' }}">
        {{-- Header only for default layout --}}
        @if($title && $layout !== 'sidebar')
        <div class="form-card-header">
            <div class="form-card-header-content">
                <h2 class="form-card-title">{{ $title }}</h2>
                @if($subtitle)
                    <p class="form-card-subtitle">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
        @endif
        
        <div class="form-card-body {{ $layout === 'sidebar' ? '!p-0' : '' }}">
            @if($action)
            <form 
                action="{{ $action }}" 
                method="{{ $method === 'GET' ? 'GET' : 'POST' }}" 
                @if($hasFiles) enctype="multipart/form-data" @endif
                {{ $attributes->merge(['class' => $layout === 'sidebar' ? '' : 'space-y-6']) }}
            >
                @csrf
                @if(!in_array($method, ['GET', 'POST']))
                    @method($method)
                @endif
                
                @if($layout === 'sidebar')
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
                        {{-- Slots will be manually placed by user using div wrappers, or we just render slot directly --}}
                         {{ $slot }}
                    </div>
                @else
                    {{ $slot }}
                @endif
            </form>
            @else
                <div {{ $attributes->merge(['class' => $layout === 'sidebar' ? 'grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start' : 'space-y-6']) }}>
                    {{ $slot }}
                </div>
            @endif
        </div>
    </div>
</div>
