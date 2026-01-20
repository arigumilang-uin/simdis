{{--
    Page Header Component - Title, subtitle, and action buttons
    
    Layout:
    [Title & Subtitle]                    [Action Buttons...]
    
    Usage:
    <x-page-header title="..." subtitle="...">
        <x-slot:actions>
            <a href="..." class="btn btn-primary">Add</a>
        </x-slot:actions>
    </x-page-header>
--}}

@props([
    'title',
    'subtitle' => null,
])

<div class="page-header-stylish !flex !flex-row !items-start md:!items-end !justify-between !gap-4">
    <div class="page-header-text flex-1 min-w-0">
        <h1 class="page-header-title break-words pr-2">{{ $title }}</h1>
        @if($subtitle)
            <p class="page-header-subtitle break-words pr-2">{{ $subtitle }}</p>
        @endif
    </div>
    
    {{-- Action Buttons (right side) --}}
    @if(isset($actions))
        <div class="flex flex-col md:flex-row items-end md:items-center gap-2 md:gap-3 shrink-0">
            {{ $actions }}
        </div>
    @endif
</div>
