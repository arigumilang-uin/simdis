@props([
    'title' => null,
    'subtitle' => null,
    'maxWidth' => '2xl', // sm, md, lg, xl, 2xl, 3xl, 4xl, full
    'action' => null,
    'method' => 'POST',
    'hasFiles' => false,
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
    'full' => 'w-full',
];
$widthClass = $widthClasses[$maxWidth] ?? 'max-w-2xl';
@endphp

<div class="{{ $widthClass }}">
    <div class="card">
        @if($title)
        <div class="card-header">
            <div>
                <h3 class="card-title">{{ $title }}</h3>
                @if($subtitle)
                    <p class="text-sm text-gray-500 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
        @endif
        
        <div class="card-body">
            @if($action)
            <form 
                action="{{ $action }}" 
                method="{{ $method === 'GET' ? 'GET' : 'POST' }}" 
                @if($hasFiles) enctype="multipart/form-data" @endif
                {{ $attributes->merge(['class' => 'space-y-6']) }}
            >
                @csrf
                @if(!in_array($method, ['GET', 'POST']))
                    @method($method)
                @endif
                
                {{ $slot }}
            </form>
            @else
                <div {{ $attributes->merge(['class' => 'space-y-6']) }}>
                    {{ $slot }}
                </div>
            @endif
        </div>
    </div>
</div>
