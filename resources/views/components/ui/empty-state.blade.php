@props([
    'icon' => 'file',
    'title' => 'Tidak Ada Data',
    'description' => 'Belum ada data yang tersedia.',
    'action' => null,
    'actionUrl' => null,
    'actionText' => 'Tambah Data'
])

<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <x-ui.icon :name="$icon" class="empty-state-icon" :size="48" :strokeWidth="1.5" />
    <h3 class="empty-state-title">{{ $title }}</h3>
    <p class="empty-state-description">{{ $description }}</p>
    @if($action || $actionUrl)
        @if($actionUrl)
            <a href="{{ $actionUrl }}" class="btn btn-primary btn-sm mt-4">
                <x-ui.icon name="plus" :size="14" />
                {{ $actionText }}
            </a>
        @else
            {{ $action }}
        @endif
    @endif
</div>
