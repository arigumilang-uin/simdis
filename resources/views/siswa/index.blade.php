@extends('layouts.app')

@section('title', 'Data Siswa')
@section('subtitle', 'Kelola data seluruh siswa di sekolah Anda.')
@section('page-header', true)

@section('actions')
    @can('create', App\Models\Siswa::class)
        <a href="{{ route('siswa.deleted') }}" class="btn btn-secondary">
            <x-ui.icon name="archive" size="18" />
            <span>Arsip Siswa</span>
        </a>
        <a href="{{ route('siswa.bulk-create') }}" class="btn btn-secondary">
            <x-ui.icon name="upload" size="18" />
            <span>Import Excel</span>
        </a>
        <a href="{{ route('siswa.create') }}" class="btn btn-primary">
            <x-ui.icon name="plus" size="18" />
            <span>Tambah Siswa</span>
        </a>
    @endcan
@endsection

@section('content')
@php
    $tableConfig = [
        'endpoint' => route('siswa.index'),
        'filters' => [
            'search' => request('search'),
            'jurusan_id' => request('jurusan_id'),
            'kelas_id' => request('kelas_id')
        ],
        'containerId' => 'siswa-table-container'
    ];
@endphp

<div class="space-y-6" x-data='dataTable(@json($tableConfig))' 
     @enter-selection.window="selectionMode = true; if (!selected.includes($event.detail.id)) selected.push($event.detail.id); if (navigator.vibrate) navigator.vibrate(50)">
    {{-- Filter Card --}}
    <div class="card" x-data="{ expanded: {{ request()->hasAny(['search', 'jurusan_id', 'kelas_id']) ? 'true' : 'false' }} }">
        <div class="card-header cursor-pointer" @click="expanded = !expanded">
            <div class="flex items-center gap-2">
                <x-ui.icon name="filter" class="text-gray-400" size="18" />
                <span class="card-title">Filter Data</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500" x-show="isLoading">Memuat...</span>
                <x-ui.icon name="chevron-down" size="20" class="text-gray-400 transition-transform" ::class="{ 'rotate-180': expanded }" />
            </div>
        </div>
        
        <div x-show="expanded" x-collapse.duration.300ms x-cloak>
            <div class="card-body border-t border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {{-- Search --}}
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
                    
                    {{-- Jurusan --}}
                    <div class="form-group">
                        <x-forms.select
                            name="jurusan_id" 
                            label="Jurusan"
                            x-model="filters.jurusan_id"
                            :options="$allJurusan"
                            optionValue="id"
                            optionLabel="nama_jurusan"
                            placeholder="Semua Jurusan"
                        />
                    </div>
                    
                    {{-- Kelas --}}
                    <div class="form-group">
                        <x-forms.select
                            name="kelas_id" 
                            label="Kelas"
                            x-model="filters.kelas_id"
                            :options="$allKelas"
                            optionValue="id"
                            optionLabel="nama_kelas"
                            placeholder="Semua Kelas"
                        />
                    </div>
                    
                    {{-- Actions --}}
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
    
    {{-- Bulk Action Toolbar --}}
    <div x-show="selected.length > 0" x-transition x-cloak class="bg-indigo-50 p-3 flex flex-col sm:flex-row justify-between items-center gap-3 rounded-xl border border-indigo-100 shadow-sm">
        <div class="flex items-center gap-2">
            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-bold" x-text="selected.length"></span>
            <span class="text-sm font-medium text-indigo-900">Siswa Terpilih</span>
        </div>
        <div class="flex flex-wrap gap-2">
            <button 
                type="button" 
                @click="$dispatch('open-bulk-delete-modal', { ids: selected })" 
                class="btn btn-sm btn-white text-red-600 border-red-200 hover:bg-red-50"
            >
                <x-ui.icon name="trash" size="14" />
                Hapus Massal
            </button>
        </div>
    </div>
    
    {{-- Data Table Container --}}
    <div id="siswa-table-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        @include('siswa._table')
    </div>
</div>

{{-- Delete Siswa Modal (Single & Bulk) --}}
<div 
    x-data="{ 
        open: false, 
        siswaId: null, 
        siswaName: '', 
        siswaNisn: '',
        alasanKeluar: '',
        keteranganKeluar: '',
        bulkMode: false,
        bulkIdsString: '',
        selectedCount: 0
    }"
    @open-delete-modal.window="
        open = true; 
        bulkMode = false;
        siswaId = $event.detail.id; 
        siswaName = $event.detail.nama; 
        siswaNisn = $event.detail.nisn;
        alasanKeluar = '';
        keteranganKeluar = '';
    "
    @open-bulk-delete-modal.window="
        open = true;
        bulkMode = true;
        bulkIdsString = $event.detail.ids.join(',');
        selectedCount = $event.detail.ids.length;
        alasanKeluar = '';
        keteranganKeluar = '';
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
                        <h3 class="text-lg font-bold text-gray-800" x-text="bulkMode ? 'Hapus Data Massal' : 'Hapus Data Siswa'"></h3>
                        <p class="text-sm text-gray-500">Tindakan ini tidak dapat dibatalkan</p>
                    </div>
                </div>
            </div>
            
            {{-- Body --}}
            <form :action="bulkMode ? '{{ route('siswa.bulk-delete-selection') }}' : '/siswa/' + siswaId" method="POST">
                @csrf
                <template x-if="!bulkMode">
                    @method('DELETE')
                </template>
                <template x-if="bulkMode">
                    <input type="hidden" name="ids" x-model="bulkIdsString">
                </template>
                
                <div class="p-6 space-y-4">
                    {{-- Siswa Info / Bulk Info --}}
                    <div class="p-4 bg-gray-50 rounded-xl">
                        <template x-if="!bulkMode">
                            <div>
                                <p class="text-sm text-gray-500">Siswa yang akan dihapus:</p>
                                <p class="font-semibold text-gray-800" x-text="siswaName"></p>
                                <p class="text-sm text-gray-500 font-mono" x-text="'NISN: ' + siswaNisn"></p>
                            </div>
                        </template>
                        <template x-if="bulkMode">
                            <div>
                                <p class="text-sm text-gray-500">Jumlah siswa yang akan dihapus:</p>
                                <p class="font-semibold text-gray-800 text-lg" x-text="selectedCount + ' Siswa'"></p>
                                <p class="text-xs text-gray-400 mt-1">Siswa terpilih akan dipindahkan ke arsip.</p>
                            </div>
                        </template>
                    </div>
                    
                    {{-- Alasan Keluar --}}
                    <div class="form-group">
                        <x-forms.select 
                            name="alasan_keluar" 
                            label="Alasan Keluar" 
                            x-model="alasanKeluar"
                            :options="[
                                ['value' => 'Alumni', 'label' => 'Alumni (Lulus)'],
                                ['value' => 'Dikeluarkan', 'label' => 'Dikeluarkan'],
                                ['value' => 'Pindah Sekolah', 'label' => 'Pindah Sekolah'],
                                ['value' => 'Lainnya', 'label' => 'Lainnya'],
                            ]"
                            optionValue="value"
                            optionLabel="label"
                            placeholder="-- Pilih Alasan --"
                            required
                        />
                    </div>
                    
                    {{-- Keterangan --}}
                    <div class="form-group">
                        <x-forms.textarea 
                            name="keterangan_keluar" 
                            label="Keterangan Tambahan" 
                            x-model="keteranganKeluar"
                            rows="3" 
                            placeholder="Tuliskan keterangan tambahan (opsional)..." 
                        />
                    </div>
                    
                    {{-- Warning --}}
                    <div class="p-3 bg-amber-50 rounded-lg border border-amber-100">
                        <p class="text-xs text-amber-700">
                            <strong>Perhatian:</strong> Siswa akan dipindahkan ke data arsip dan dapat di-restore kembali jika diperlukan.
                        </p>
                    </div>
                </div>
                
                {{-- Footer --}}
                <div class="p-6 border-t border-gray-100 flex gap-3 justify-end">
                    <button type="button" @click="open = false" class="btn btn-secondary">Batal</button>
                    <button 
                        type="submit" 
                        class="btn btn-danger"
                        :disabled="!alasanKeluar"
                    >
                        <x-ui.icon name="trash" size="18" />
                        <span x-text="bulkMode ? 'Hapus Massal' : 'Hapus Siswa'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
