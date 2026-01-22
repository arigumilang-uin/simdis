@extends('layouts.app')

@section('title', 'Arsip Jurusan')

@section('page-header')
    <x-page-header 
        title="Arsip Jurusan" 
        subtitle="Jurusan yang telah diarsipkan. Dapat di-restore atau dihapus permanen."
        :total="$jurusanList->count()"
    />
@endsection

@section('content')
@php
    $allIds = $jurusanList->pluck('id')->map(fn($id) => (string) $id)->values()->toArray();
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
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Jurusan Diarsipkan</span>
                    <span class="text-lg font-bold text-gray-900 leading-none">{{ $jurusanList->count() }}</span>
                </div>
                

            </div>
            
            {{-- Bulk Action Toolbar --}}
            <div x-show="selected.length > 0" x-transition x-cloak class="mt-3 bg-indigo-50 p-2 flex flex-col sm:flex-row justify-between items-center gap-3 rounded-lg border border-indigo-100">
                <div class="flex items-center gap-2 px-1">
                    <span class="flex items-center justify-center w-auto min-w-[1.25rem] px-1 h-5 rounded-full bg-indigo-600 text-white text-[10px] font-bold" x-text="selectedCount"></span>
                    <span class="text-sm font-medium text-indigo-900">Jurusan Terpilih</span>
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
                        <th>Nama Jurusan</th>
                        <th>Kode</th>
                        <th class="text-center">Kelas</th>
                        <th>Tanggal Dihapus</th>
                        <x-table.action-header />
                    </tr>
                </thead>
                <tbody>
                    @forelse($jurusanList as $index => $jurusan)
                        <tr :class="{ 'bg-indigo-50/40': selected.includes('{{ $jurusan->id }}') }">
                            <td class="text-gray-500">{{ $index + 1 }}</td>
                            <td class="font-medium text-gray-800">{{ $jurusan->nama_jurusan }}</td>
                            <td>
                                <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded-md">{{ $jurusan->kode_jurusan }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-neutral">{{ $jurusan->kelas_count }} kelas</span>
                            </td>
                            <td class="text-gray-500 text-sm">{{ $jurusan->deleted_at ? $jurusan->deleted_at->format('d M Y H:i') : '-' }}</td>
                            <x-table.action-column :id="$jurusan->id">
                                <x-table.action-item 
                                    icon="rotate-ccw" 
                                    class="text-emerald-600 hover:bg-emerald-50 hover:text-emerald-700"
                                    @click="open = false; if(confirm('Restore jurusan ini beserta kelas dan konsentrasi?')) { document.getElementById('restore-form-{{ $jurusan->id }}').submit(); }"
                                >
                                    Restore
                                </x-table.action-item>
                                
                                <x-table.action-separator />
                                
                                <x-table.action-item 
                                    icon="trash" 
                                    class="text-red-600 hover:bg-red-50 hover:text-red-700"
                                    @click="open = false; if(confirm('HAPUS PERMANEN jurusan ini? Data tidak dapat dikembalikan!')) { document.getElementById('force-delete-form-{{ $jurusan->id }}').submit(); }"
                                >
                                    Hapus Permanen
                                </x-table.action-item>
                            </x-table.action-column>
                        </tr>
                        
                        {{-- Hidden Forms --}}
                        <form id="restore-form-{{ $jurusan->id }}" action="{{ route('jurusan.restore', $jurusan->id) }}" method="POST" class="hidden">
                            @csrf
                        </form>
                        <form id="force-delete-form-{{ $jurusan->id }}" action="{{ route('jurusan.forceDelete', $jurusan->id) }}" method="POST" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-ui.empty-state 
                                    icon="check-circle" 
                                    title="Tidak Ada Data Arsip" 
                                    description="Tidak ada jurusan yang telah diarsipkan." 
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
                        <h3 class="text-lg font-bold text-emerald-700">Restore Jurusan Massal</h3>
                        <p class="text-sm text-gray-500">Kembalikan jurusan ke daftar aktif</p>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('jurusan.bulk-restore') }}" method="POST">
                @csrf
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                
                <div class="p-6 space-y-4">
                    <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100 text-center">
                        <p class="text-sm text-emerald-600">Jumlah jurusan yang akan di-restore:</p>
                        <p class="text-3xl font-bold text-emerald-800" x-text="selectedCount"></p>
                    </div>
                    
                    <div class="p-4 bg-blue-50 rounded-xl space-y-2">
                        <p class="text-sm font-medium text-blue-800">ℹ️ Tindakan ini akan:</p>
                        <ul class="text-sm text-blue-600 space-y-1 pl-4">
                            <li>• Mengembalikan jurusan ke daftar aktif</li>
                            <li>• Mengembalikan kelas dan konsentrasi terkait</li>
                        </ul>
                    </div>
                </div>
                
                <div class="p-6 border-t border-gray-100 flex gap-3 justify-end">
                    <button type="button" @click="open = false" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <x-ui.icon name="rotate-ccw" size="18" />
                        <span>Restore <span x-text="selectedCount"></span> Jurusan</span>
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
            
            <form action="{{ route('jurusan.bulk-force-delete') }}" method="POST">
                @csrf
                @method('DELETE')
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                
                <div class="p-6 space-y-4">
                    <div class="p-4 bg-red-50 rounded-xl border border-red-100 text-center">
                        <p class="text-sm text-red-600">Jumlah jurusan yang akan dihapus permanen:</p>
                        <p class="text-3xl font-bold text-red-800" x-text="selectedCount"></p>
                    </div>
                    
                    <div class="p-4 bg-gray-50 rounded-xl space-y-2">
                        <p class="text-sm font-medium text-gray-800">⚠️ Tindakan ini akan menghapus:</p>
                        <ul class="text-sm text-gray-600 space-y-1 pl-4">
                            <li>• Semua data jurusan terpilih secara permanen</li>
                            <li>• Semua kelas dan konsentrasi terkait</li>
                        </ul>
                    </div>
                    
                    <label class="flex items-start gap-3 cursor-pointer p-3 bg-amber-50 rounded-lg border border-amber-100">
                        <input type="checkbox" name="confirm_permanent" value="1" x-model="confirmed" class="w-4 h-4 mt-0.5 rounded border-gray-300 text-red-600 focus:ring-red-500">
                        <span class="text-sm text-amber-800">
                            Saya mengerti bahwa tindakan ini <strong>TIDAK DAPAT DIBATALKAN</strong> dan semua data akan dihapus permanen.
                        </span>
                    </label>
                </div>
                
                <div class="p-6 border-t border-gray-100 flex gap-3 justify-end">
                    <button type="button" @click="open = false" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-danger" :disabled="!confirmed">
                        <x-ui.icon name="trash" size="18" />
                        <span>Hapus <span x-text="selectedCount"></span> Jurusan Permanen</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
