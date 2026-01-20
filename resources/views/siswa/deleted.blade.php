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
@php
    $tableConfig = [
        'endpoint' => route('siswa.deleted'),
        'containerId' => 'siswa-deleted-table-container',
        'filters' => [
            'search' => request('search'),
            'jurusan_id' => request('jurusan_id'),
            'konsentrasi_id' => request('konsentrasi_id'),
            'tingkat' => request('tingkat'),
            'kelas_id' => request('kelas_id'),
            'alasan_keluar' => request('alasan_keluar')
        ]
    ];
    $tingkatOptions = collect([
        (object)['id' => 'X', 'label' => 'Kelas X'],
        (object)['id' => 'XI', 'label' => 'Kelas XI'],
        (object)['id' => 'XII', 'label' => 'Kelas XII'],
    ]);
@endphp

<div class="space-y-4" 
     x-data='{
         ...dataTable(@json($tableConfig)),
         selected: [],
         selectionMode: false,
         selectAll: false,
         pageIds: [],
         totalItems: 0,
         allSelected: false,
         setupSelectionLogic() {
             this.$watch("selectAll", val => {
                 this.selected = val ? [...this.pageIds] : [];
                 if (!val) this.allSelected = false;
             });
             this.$watch("selected", val => {
                 if (val.length === 0) {
                     this.selectionMode = false;
                     this.allSelected = false;
                 }
                 if (this.pageIds.length > 0 && val.length !== this.pageIds.length) {
                     this.selectAll = false;
                     this.allSelected = false;
                 } else if (this.pageIds.length > 0 && val.length === this.pageIds.length) {
                     this.selectAll = true;
                 }
             });
         }
     }'
     x-init="setupSelectionLogic()"
     @enter-selection.window="selectionMode = true; if (!selected.includes(String($event.detail.id))) selected.push(String($event.detail.id)); if (navigator.vibrate) navigator.vibrate(50)"
     @update-page-ids="pageIds = $event.detail"
     @update-total-data="totalItems = $event.detail.total"
     @toggle-selection-mode.window="selectionMode = $event.detail !== undefined ? $event.detail : !selectionMode">
    
    <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm overflow-hidden mb-8 border-b border-gray-200 md:border-b-0">
        {{-- Toolbar --}}
        <div class="px-4 md:px-6 py-5 border-b border-gray-100 bg-white">
            <x-ui.action-bar :total="$deletedSiswa->total()" totalLabel="Siswa Diarsipkan" class="!gap-4">
                <x-slot:search>
                    <input 
                        type="text" 
                        x-model.debounce.500ms="filters.search"
                        class="w-full md:w-80 rounded-xl border-0 bg-gray-100/80 text-sm text-gray-800 py-2.5 pl-10 pr-4 hover:bg-gray-100 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:shadow-lg focus:shadow-indigo-500/5 transition-all duration-200 placeholder-gray-400"
                        placeholder="Nama atau NISN..."
                    >
                </x-slot:search>
                <x-slot:filters>
                    <x-ui.filter-select 
                        label="Alasan Keluar"
                        x-model="filters.alasan_keluar"
                        :options="collect($alasanOptions)->map(fn($opt) => (object)['id' => $opt, 'label' => $opt])"
                        optionValue="id"
                        optionLabel="label"
                        placeholder="Semua Alasan"
                    />
                    <x-ui.filter-select 
                        label="Jurusan"
                        x-model="filters.jurusan_id"
                        :options="$allJurusan"
                        optionValue="id"
                        optionLabel="nama_jurusan"
                        placeholder="Semua Jurusan"
                    />
                    <x-ui.filter-select 
                        label="Konsentrasi"
                        x-model="filters.konsentrasi_id"
                        :options="$allKonsentrasi"
                        optionValue="id"
                        optionLabel="nama_konsentrasi"
                        placeholder="Semua Konsentrasi"
                    />
                    <x-ui.filter-select 
                        label="Tingkat"
                        x-model="filters.tingkat"
                        :options="$tingkatOptions"
                        optionValue="id"
                        optionLabel="label"
                        placeholder="Semua Tingkat"
                    />
                    <x-ui.filter-select 
                        label="Kelas"
                        x-model="filters.kelas_id"
                        :options="$allKelas"
                        optionValue="id"
                        optionLabel="nama_kelas"
                        placeholder="Semua Kelas"
                    />
                </x-slot:filters>
                <x-slot:reset>
                    <button type="button" @click="resetFilters()" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Reset</button>
                </x-slot:reset>
            </x-ui.action-bar>
            
            {{-- Bulk Action Toolbar --}}
            <div x-show="selected.length > 0" x-transition x-cloak class="mt-3 bg-indigo-50 p-2 flex flex-col sm:flex-row justify-between items-center gap-3 rounded-lg border border-indigo-100">
                <div class="flex items-center gap-2 px-1">
                    <span class="flex items-center justify-center w-auto min-w-[1.25rem] px-1 h-5 rounded-full bg-indigo-600 text-white text-[10px] font-bold" x-text="allSelected ? totalItems : selected.length"></span>
                    <span class="text-sm font-medium text-indigo-900">Siswa Terpilih</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="$dispatch('open-bulk-restore-modal', { ids: selected, allSelected: allSelected, filters: JSON.parse(JSON.stringify(filters)), totalItems: totalItems })" class="btn btn-sm btn-white text-emerald-600 border-emerald-200 hover:bg-emerald-50">
                        <x-ui.icon name="rotate-ccw" size="14" />
                        Restore
                    </button>
                    <button type="button" @click="$dispatch('open-bulk-permanent-delete-modal', { ids: selected, allSelected: allSelected, filters: JSON.parse(JSON.stringify(filters)), totalItems: totalItems })" class="btn btn-sm btn-white text-red-600 border-red-200 hover:bg-red-50">
                        <x-ui.icon name="trash" size="14" />
                        Hapus Permanen
                    </button>
                    <button type="button" @click="selected = []; selectionMode = false;" class="btn btn-sm btn-white">
                        Batal
                    </button>
                </div>
            </div>
        </div>

        {{-- Banner Select All --}}
        <div x-show="selectAll && !allSelected && totalItems > pageIds.length" x-cloak class="px-6 py-3 bg-indigo-50 border-b border-indigo-100 text-center text-sm text-indigo-800 transition-all">
            <span class="mr-1">Semua <span class="font-bold" x-text="selected.length"></span> data di halaman ini terpilih.</span>
            <template x-if="totalItems > selected.length">
                <button type="button" @click="allSelected = true" class="font-bold text-indigo-600 hover:text-indigo-800 hover:underline focus:outline-none ml-1">
                    Pilih semua <span x-text="totalItems"></span> data sesuai filter
                </button>
            </template>
        </div>
        
        <div x-show="allSelected" x-cloak class="px-6 py-3 bg-indigo-50 border-b border-indigo-100 text-center text-sm text-indigo-800 transition-all">
            <span class="mr-1">Semua <span class="font-bold" x-text="totalItems"></span> data terpilih.</span>
            <button type="button" @click="allSelected = false; selectAll = false; selected = []" class="font-bold text-indigo-600 hover:text-indigo-800 hover:underline focus:outline-none ml-1">
                Batalkan pilihan
            </button>
        </div>

        {{-- Data Table Container --}}
        <div id="siswa-deleted-table-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
            @include('siswa._table_deleted')
        </div>
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

{{-- Bulk Permanent Delete Modal --}}
<div 
    x-data="{ 
        open: false, 
        selectedIds: [],
        selectedCount: 0,
        allSelected: false,
        activeFilters: {},
        confirmed: false
    }"
    @open-bulk-permanent-delete-modal.window="
        open = true; 
        selectedIds = $event.detail.ids; 
        allSelected = $event.detail.allSelected || false;
        activeFilters = $event.detail.filters || {};
        selectedCount = allSelected ? $event.detail.totalItems : selectedIds.length;
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
                        <h3 class="text-lg font-bold text-red-600">⚠️ Hapus Permanen Massal</h3>
                        <p class="text-sm text-gray-500">Data tidak dapat dikembalikan!</p>
                    </div>
                </div>
            </div>
            
            {{-- Body --}}
            <form action="{{ route('siswa.bulk-force-delete') }}" method="POST">
                @csrf
                
                {{-- Hidden input for IDs or Filters --}}
                <template x-if="!allSelected">
                    <template x-for="id in selectedIds" :key="id">
                        <input type="hidden" name="siswa_ids[]" :value="id">
                    </template>
                </template>
                
                <template x-if="allSelected">
                    <div>
                        <input type="hidden" name="all_selected" value="1">
                        <template x-for="(value, key) in activeFilters" :key="key">
                            <template x-if="value">
                                <input type="hidden" :name="'filters[' + key + ']'" :value="value">
                            </template>
                        </template>
                    </div>
                </template>
                
                <div class="p-6 space-y-4">
                    {{-- Count Info --}}
                    <div class="p-4 bg-red-50 rounded-xl border border-red-100 text-center">
                        <p class="text-sm text-red-600">Jumlah siswa yang akan dihapus permanen:</p>
                        <p class="text-3xl font-bold text-red-800" x-text="selectedCount"></p>
                    </div>
                    
                    {{-- Warning --}}
                    <div class="p-4 bg-gray-50 rounded-xl space-y-2">
                        <p class="text-sm font-medium text-gray-800">⚠️ Tindakan ini akan menghapus:</p>
                        <ul class="text-sm text-gray-600 space-y-1 pl-4">
                            <li>• Semua data siswa terpilih secara permanen</li>
                            <li>• Semua riwayat pelanggaran terkait</li>
                            <li>• Semua kasus tindak lanjut terkait</li>
                            <li>• Akun wali murid yang tidak memiliki anak lain</li>
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
                        <span>Hapus <span x-text="selectedCount"></span> Siswa Permanen</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Bulk Restore Modal --}}
<div 
    x-data="{ 
        open: false, 
        selectedIds: [],
        selectedCount: 0,
        allSelected: false,
        activeFilters: {}
    }"
    @open-bulk-restore-modal.window="
        open = true; 
        selectedIds = $event.detail.ids; 
        allSelected = $event.detail.allSelected || false;
        activeFilters = $event.detail.filters || {};
        selectedCount = allSelected ? $event.detail.totalItems : selectedIds.length;
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
            class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl"
            @click.stop
        >
            {{-- Header --}}
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                        <x-ui.icon name="rotate-ccw" size="24" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-emerald-700">Restore Siswa Massal</h3>
                        <p class="text-sm text-gray-500">Kembalikan siswa ke daftar aktif</p>
                    </div>
                </div>
            </div>
            
            {{-- Body --}}
            <form action="{{ route('siswa.bulk-restore') }}" method="POST">
                @csrf
                
                {{-- Hidden input for IDs or Filters --}}
                <template x-if="!allSelected">
                    <template x-for="id in selectedIds" :key="id">
                        <input type="hidden" name="siswa_ids[]" :value="id">
                    </template>
                </template>
                
                <template x-if="allSelected">
                    <div>
                        <input type="hidden" name="all_selected" value="1">
                        <template x-for="(value, key) in activeFilters" :key="key">
                            <template x-if="value">
                                <input type="hidden" :name="'filters[' + key + ']'" :value="value">
                            </template>
                        </template>
                    </div>
                </template>
                
                <div class="p-6 space-y-4">
                    {{-- Count Info --}}
                    <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100 text-center">
                        <p class="text-sm text-emerald-600">Jumlah siswa yang akan di-restore:</p>
                        <p class="text-3xl font-bold text-emerald-800" x-text="selectedCount"></p>
                    </div>
                    
                    {{-- Info --}}
                    <div class="p-4 bg-blue-50 rounded-xl space-y-2">
                        <p class="text-sm font-medium text-blue-800">ℹ️ Tindakan ini akan:</p>
                        <ul class="text-sm text-blue-600 space-y-1 pl-4">
                            <li>• Mengembalikan siswa ke daftar aktif</li>
                            <li>• Mengaktifkan kembali akun wali murid terkait</li>
                        </ul>
                    </div>
                </div>
                
                {{-- Footer --}}
                <div class="p-6 border-t border-gray-100 flex gap-3 justify-end">
                    <button type="button" @click="open = false" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <x-ui.icon name="rotate-ccw" size="18" />
                        <span>Restore <span x-text="selectedCount"></span> Siswa</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
