@extends('layouts.app')

@section('title', 'Arsip Siswa')

@section('page-header')
    <x-page-header 
        title="Arsip Siswa" 
        subtitle="Siswa yang telah dihapus. Dapat di-restore atau dihapus permanen."
        :total="$deletedSiswa->total()"
    />
@endsection

@section('content')
{{-- Action Buttons --}}
<div class="flex flex-wrap justify-end gap-2 mb-6">
    <a href="{{ route('siswa.index') }}" class="btn btn-secondary">
        <x-ui.icon name="chevron-left" size="18" />
        <span>Kembali ke Data Siswa</span>
    </a>
</div>

@php
    $tableConfig = [
        'endpoint' => route('siswa.deleted'),
        'containerId' => 'siswa-deleted-table-container',
        'filters' => [
            'search' => request('search'),
            'kelas_id' => request('kelas_id'),
            'alasan_keluar' => request('alasan_keluar')
        ]
    ];
@endphp

<div class="space-y-6" x-data='Object.assign(dataTable(@json($tableConfig)), { selectionMode: false, selectAll: false })'>
    {{-- Bulk Action Toolbar --}}
    <div x-show="selected.length > 0" x-transition x-cloak class="bg-indigo-50 p-3 flex flex-col sm:flex-row justify-between items-center gap-3 rounded-xl border border-indigo-100 shadow-sm">
        <div class="flex items-center gap-2">
            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-bold" x-text="selected.length"></span>
            <span class="text-sm font-medium text-indigo-900">Siswa Terpilih</span>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" @click="if(confirm('Restore ' + selected.length + ' siswa terpilih ke daftar aktif?')) { alert('Fitur bulk restore sedang dalam pengembangan.'); }" class="btn btn-sm btn-white text-emerald-600 border-emerald-200 hover:bg-emerald-50">
                <x-ui.icon name="rotate-ccw" size="14" />
                Restore Massal
            </button>
            <button type="button" @click="if(confirm('Hapus PERMANEN ' + selected.length + ' siswa terpilih? Data tidak dapat dikembalikan!')) { alert('Fitur bulk delete sedang dalam pengembangan.'); }" class="btn btn-sm btn-white text-red-600 border-red-200 hover:bg-red-50">
                <x-ui.icon name="trash" size="14" />
                Hapus Permanen
            </button>
            <button type="button" @click="selected = []; selectionMode = false;" class="btn btn-sm btn-white">
                Batal
            </button>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card" x-data="{ expanded: {{ request()->hasAny(['search', 'kelas_id', 'alasan_keluar']) ? 'true' : 'false' }} }">
        <div class="card-header cursor-pointer" @click="expanded = !expanded">
            <div class="flex items-center gap-2">
                <x-ui.icon name="filter" class="text-gray-400" size="18" />
                <span class="card-title">Filter Arsip</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500" x-show="isLoading">Memuat...</span>
                <x-ui.icon name="chevron-down" size="20" class="text-gray-400 transition-transform" ::class="{ 'rotate-180': expanded }" />
            </div>
        </div>

        <div x-show="expanded" x-collapse.duration.300ms x-cloak>
            <div class="card-body border-t border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="form-group md:col-span-2">
                    <x-forms.input 
                        name="search" 
                        label="Cari"
                        x-model.debounce.500ms="filters.search"
                        placeholder="Nama atau NISN..."
                    />
                    <div class="absolute right-0 top-8 pr-3 pointer-events-none" x-show="isLoading">
                        <x-ui.icon name="loader" class="animate-spin text-gray-400" size="16" />
                    </div>
                </div>
                
                <div class="form-group">
                    <x-forms.select 
                        name="alasan_keluar" 
                        label="Alasan Keluar" 
                        x-model="filters.alasan_keluar"
                        :options="$alasanOptions ?? []"
                        :simple="true"
                        placeholder="Semua Alasan"
                    />
                </div>
                
                <div class="form-group">
                    <x-forms.select 
                        name="kelas_id" 
                        label="Kelas" 
                        x-model="filters.kelas_id"
                        :options="$allKelas ?? []"
                        optionValue="id"
                        optionLabel="nama_kelas"
                        placeholder="Semua Kelas"
                    />
                </div>
                
                <div class="md:col-span-4 flex justify-end">
                    <button type="button" @click="resetFilters()" class="btn btn-secondary text-xs">
                         <x-ui.icon name="refresh-cw" size="14" />
                        <span>Reset Filter</span>
                    </button>
                </div>
            </div>
            </div>
        </div>
    </div>

    {{-- Data Table Container --}}
    <div id="siswa-deleted-table-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        @include('siswa._table_deleted')
    </div>
</div>

{{-- Permanent Delete Modal --}}
<div 
    x-data="{ 
        open: false, 
        siswaId: null, 
        siswaName: '', 
        siswaNisn: '',
        confirmed: false
    }"
    @open-permanent-delete-modal.window="
        open = true; 
        siswaId = $event.detail.id; 
        siswaName = $event.detail.nama; 
        siswaNisn = $event.detail.nisn;
        confirmed = false;
    "
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
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
        @click="open = false"
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
            class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl"
            @click.stop
        >
            {{-- Header --}}
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                        <x-ui.icon name="alert-triangle" size="24" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-red-600">⚠️ Hapus Permanen</h3>
                        <p class="text-sm text-gray-500">Data tidak dapat dikembalikan!</p>
                    </div>
                </div>
            </div>
            
            {{-- Body --}}
            <form :action="'/siswa/' + siswaId + '/force-delete'" method="POST">
                @csrf
                @method('DELETE')
                
                <div class="p-6 space-y-4">
                    {{-- Siswa Info --}}
                    <div class="p-4 bg-red-50 rounded-xl border border-red-100">
                        <p class="text-sm text-red-600">Siswa yang akan dihapus permanen:</p>
                        <p class="font-semibold text-red-800" x-text="siswaName"></p>
                        <p class="text-sm text-red-600 font-mono" x-text="'NISN: ' + siswaNisn"></p>
                    </div>
                    
                    {{-- Warning --}}
                    <div class="p-4 bg-gray-50 rounded-xl space-y-2">
                        <p class="text-sm font-medium text-gray-800">⚠️ Tindakan ini akan menghapus:</p>
                        <ul class="text-sm text-gray-600 space-y-1 pl-4">
                            <li>• Data siswa secara permanen</li>
                            <li>• Semua riwayat pelanggaran terkait</li>
                            <li>• Semua kasus tindak lanjut terkait</li>
                        </ul>
                    </div>
                    
                    {{-- Confirmation Checkbox --}}
                    <label class="flex items-start gap-3 cursor-pointer p-3 bg-amber-50 rounded-lg border border-amber-100">
                        <input 
                            type="checkbox" 
                            name="confirm_permanent" 
                            value="1" 
                            x-model="confirmed"
                            class="w-4 h-4 mt-0.5 rounded border-gray-300 text-red-600 focus:ring-red-500"
                        >
                        <span class="text-sm text-amber-800">
                            Saya mengerti bahwa tindakan ini <strong>TIDAK DAPAT DIBATALKAN</strong> dan semua data akan dihapus permanen.
                        </span>
                    </label>
                </div>
                
                {{-- Footer --}}
                <div class="p-6 border-t border-gray-100 flex gap-3 justify-end">
                    <button type="button" @click="open = false" class="btn btn-secondary">Batal</button>
                    <button 
                        type="submit" 
                        class="btn btn-danger"
                        :disabled="!confirmed"
                    >
                        <x-ui.icon name="trash" size="18" />
                        <span>Hapus Permanen</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

