@extends('layouts.app')

@section('title', 'Arsip Kelas')

@section('page-header')
    <x-page-header 
        title="Arsip Kelas" 
        subtitle="Kelas yang telah diarsipkan. Dapat di-restore atau dihapus permanen."
        :total="$kelasList->count()"
    />
@endsection

@section('content')
@php
    $allIds = $kelasList->pluck('id')->map(fn($id) => (string) $id)->values()->toArray();
@endphp

<div class="space-y-4" 
     x-data='{
         selected: [],
         selectionMode: false,
         selectAll: false,
         allIds: @json($allIds),
         get selectedCount() {
             return this.selected.length;
         },
         toggleSelectAll() {
             if (this.selectAll) {
                 this.selected = [...this.allIds];
             } else {
                 this.selected = [];
             }
         }
     }'
     x-init="$watch('selectAll', () => toggleSelectAll()); $watch('selected', val => { if (val.length === 0) selectionMode = false; selectAll = val.length === allIds.length && allIds.length > 0; })"
     @toggle-selection-mode.window="selectionMode = $event.detail !== undefined ? $event.detail : !selectionMode"
     @enter-selection.window="selectionMode = true; if (!selected.includes(String($event.detail.id))) selected.push(String($event.detail.id))">
    
    <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm overflow-hidden mb-8 border-b border-gray-200 md:border-b-0">
        {{-- Toolbar --}}
        <div class="px-4 md:px-6 py-5 border-b border-gray-100 bg-white">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                {{-- Left side: Total Counter --}}
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Kelas Diarsipkan</span>
                    <span class="text-lg font-bold text-gray-900 leading-none">{{ $kelasList->count() }}</span>
                </div>
                

            </div>
            
            {{-- Bulk Action Toolbar --}}
            <div x-show="selected.length > 0" x-transition x-cloak class="mt-3 bg-indigo-50 p-2 flex flex-col sm:flex-row justify-between items-center gap-3 rounded-lg border border-indigo-100">
                <div class="flex items-center gap-2 px-1">
                    <span class="flex items-center justify-center w-auto min-w-[1.25rem] px-1 h-5 rounded-full bg-indigo-600 text-white text-[10px] font-bold" x-text="selectedCount"></span>
                    <span class="text-sm font-medium text-indigo-900">Kelas Terpilih</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="$dispatch('open-bulk-restore-modal', { ids: selected, count: selectedCount })" class="btn btn-sm btn-white text-emerald-600 border-emerald-200 hover:bg-emerald-50">
                        <x-ui.icon name="rotate-ccw" size="14" />
                        Restore
                    </button>
                    <button type="button" @click="$dispatch('open-bulk-delete-modal', { ids: selected, count: selectedCount })" class="btn btn-sm btn-white text-red-600 border-red-200 hover:bg-red-50">
                        <x-ui.icon name="trash" size="14" />
                        Hapus Permanen
                    </button>
                    <button type="button" @click="selected = []; selectionMode = false;" class="btn btn-sm btn-white">
                        Batal
                    </button>
                </div>
            </div>
        </div>

        {{-- Data Table --}}
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Nama Kelas</th>
                        <th>Jurusan</th>
                        <th>Wali Kelas</th>
                        <th class="text-center">Siswa</th>
                        <th>Tanggal Dihapus</th>
                        <x-table.action-header />
                    </tr>
                </thead>
                <tbody>
                    @forelse($kelasList as $index => $kelas)
                        <tr :class="{ 'bg-indigo-50/40': selected.includes('{{ $kelas->id }}') }">
                            <td class="text-gray-500">{{ $index + 1 }}</td>
                            <td class="font-medium text-gray-800">{{ $kelas->nama_kelas }}</td>
                            <td>
                                <span class="badge badge-primary">{{ $kelas->jurusan->nama_jurusan ?? '-' }}</span>
                            </td>
                            <td class="text-gray-500 text-sm">{{ $kelas->waliKelas->nama ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge badge-neutral">{{ $kelas->siswa_count }} siswa</span>
                            </td>
                            <td class="text-gray-500 text-sm">{{ $kelas->deleted_at ? $kelas->deleted_at->format('d M Y H:i') : '-' }}</td>
                            <x-table.action-column :id="$kelas->id">
                                <x-table.action-item 
                                    icon="rotate-ccw" 
                                    class="text-emerald-600 hover:bg-emerald-50 hover:text-emerald-700"
                                    @click="open = false; if(confirm('Restore kelas ini?')) { document.getElementById('restore-form-{{ $kelas->id }}').submit(); }"
                                >
                                    Restore
                                </x-table.action-item>
                                
                                <x-table.action-separator />
                                
                                <x-table.action-item 
                                    icon="trash" 
                                    class="text-red-600 hover:bg-red-50 hover:text-red-700"
                                    @click="open = false; if(confirm('HAPUS PERMANEN kelas ini? Data tidak dapat dikembalikan!')) { document.getElementById('force-delete-form-{{ $kelas->id }}').submit(); }"
                                >
                                    Hapus Permanen
                                </x-table.action-item>
                            </x-table.action-column>
                        </tr>
                        
                        {{-- Hidden Forms --}}
                        <form id="restore-form-{{ $kelas->id }}" action="{{ route('kelas.restore', $kelas->id) }}" method="POST" class="hidden">
                            @csrf
                        </form>
                        <form id="force-delete-form-{{ $kelas->id }}" action="{{ route('kelas.forceDelete', $kelas->id) }}" method="POST" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-ui.empty-state 
                                    icon="check-circle" 
                                    title="Tidak Ada Data Arsip" 
                                    description="Tidak ada kelas yang telah diarsipkan." 
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Bulk Restore Modal --}}
<div 
    x-data="{ open: false, selectedIds: [], selectedCount: 0 }"
    @open-bulk-restore-modal.window="open = true; selectedIds = $event.detail.ids; selectedCount = $event.detail.count;"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
>
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
    
    <div class="flex min-h-full items-center justify-center p-4">
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl" @click.stop>
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                        <x-ui.icon name="rotate-ccw" size="24" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-emerald-700">Restore Kelas Massal</h3>
                        <p class="text-sm text-gray-500">Kembalikan kelas ke daftar aktif</p>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('kelas.bulk-restore') }}" method="POST">
                @csrf
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                
                <div class="p-6 space-y-4">
                    <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100 text-center">
                        <p class="text-sm text-emerald-600">Jumlah kelas yang akan di-restore:</p>
                        <p class="text-3xl font-bold text-emerald-800" x-text="selectedCount"></p>
                    </div>
                </div>
                
                <div class="p-6 border-t border-gray-100 flex gap-3 justify-end">
                    <button type="button" @click="open = false" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <x-ui.icon name="rotate-ccw" size="18" />
                        <span>Restore <span x-text="selectedCount"></span> Kelas</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Bulk Delete Modal --}}
<div 
    x-data="{ open: false, selectedIds: [], selectedCount: 0, confirmed: false }"
    @open-bulk-delete-modal.window="open = true; selectedIds = $event.detail.ids; selectedCount = $event.detail.count; confirmed = false;"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
>
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
    
    <div class="flex min-h-full items-center justify-center p-4">
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl" @click.stop>
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                        <x-ui.icon name="alert-triangle" size="24" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-red-600">⚠️ Hapus Permanen Massal</h3>
                        <p class="text-sm text-gray-500">Data tidak dapat dikembalikan!</p>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('kelas.bulk-force-delete') }}" method="POST">
                @csrf
                @method('DELETE')
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                
                <div class="p-6 space-y-4">
                    <div class="p-4 bg-red-50 rounded-xl border border-red-100 text-center">
                        <p class="text-sm text-red-600">Jumlah kelas yang akan dihapus permanen:</p>
                        <p class="text-3xl font-bold text-red-800" x-text="selectedCount"></p>
                    </div>
                    
                    <label class="flex items-start gap-3 cursor-pointer p-3 bg-amber-50 rounded-lg border border-amber-100">
                        <input type="checkbox" name="confirm_permanent" value="1" x-model="confirmed" class="w-4 h-4 mt-0.5 rounded border-gray-300 text-red-600 focus:ring-red-500">
                        <span class="text-sm text-amber-800">
                            Saya mengerti bahwa tindakan ini <strong>TIDAK DAPAT DIBATALKAN</strong>.
                        </span>
                    </label>
                </div>
                
                <div class="p-6 border-t border-gray-100 flex gap-3 justify-end">
                    <button type="button" @click="open = false" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-danger" :disabled="!confirmed">
                        <x-ui.icon name="trash" size="18" />
                        <span>Hapus <span x-text="selectedCount"></span> Kelas Permanen</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
