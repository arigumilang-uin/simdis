@extends('layouts.app')

@section('title', 'Data Siswa')

@section('page-header')
    @php
        $headerTitle = match($userRole ?? 'Operator Sekolah') {
            'Wali Kelas' => 'Siswa Kelas Saya',
            'Kaprodi' => 'Data Siswa Jurusan',
            default => 'Data Siswa'
        };
        
        $headerSubtitle = match($userRole ?? 'Operator Sekolah') {
            'Wali Kelas' => 'Kelola data siswa di kelas yang Anda ampu.',
            'Kaprodi' => 'Kelola data siswa di jurusan dan konsentrasi yang Anda kelola.',
            default => 'Kelola data seluruh siswa di sekolah Anda.'
        };
    @endphp
    
    <x-page-header 
        :title="$headerTitle" 
        :subtitle="$headerSubtitle"
    >
        <x-slot:actions>
            @if(!in_array($userRole ?? '', ['Wali Kelas', 'Kaprodi']))
                @can('create', App\Models\Siswa::class)
                <a href="{{ route('siswa.create') }}" class="btn btn-primary">
                    <x-ui.icon name="plus" size="18" />
                    <span>Tambah Siswa</span>
                </a>
                @endcan
            @endif
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
@php
    $tableConfig = [
        'endpoint' => route('siswa.index'),
        'filters' => [
            'search' => request('search'),
            'jurusan_id' => request('jurusan_id'),
            'konsentrasi_id' => request('konsentrasi_id'),
            'tingkat' => request('tingkat'),
            'kelas_id' => request('kelas_id'),
            'user_role' => $userRole ?? 'Operator Sekolah' // Pass role to partial
        ],
        'containerId' => 'siswa-table-container'
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
     @toggle-selection-mode.window="selectionMode = $event.detail !== undefined ? $event.detail : !selectionMode"
>
    
    <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm overflow-hidden mb-8 border-b border-gray-200 md:border-b-0">
        {{-- Unified Toolbar --}}
        <div class="px-4 md:px-6 py-5 border-b border-gray-100 bg-white">
            <x-ui.action-bar :total="$siswa->total()" totalLabel="Siswa" class="!gap-4" :showFilterButton="!in_array($userRole ?? '', ['Wali Kelas'])">
                <x-slot:search>
                    <input 
                        type="text" 
                        x-model.debounce.500ms="filters.search"
                        class="w-full md:w-80 rounded-xl border-0 bg-gray-100/80 text-sm text-gray-800 py-2.5 pl-10 pr-4 hover:bg-gray-100 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:shadow-lg focus:shadow-indigo-500/5 transition-all duration-200 placeholder-gray-400"
                        placeholder="Cari nama atau NISN..."
                    >
                </x-slot:search>
                
                <x-slot:filters>
                    {{-- Wali Kelas: Only search, no dropdowns --}}
                    @if(($userRole ?? '') !== 'Wali Kelas')
                        {{-- Kaprodi: No Jurusan filter (auto-applied) --}}
                        @if(($userRole ?? '') !== 'Kaprodi')
                            <x-ui.filter-select 
                                label="Jurusan"
                                x-model="filters.jurusan_id"
                                :options="$allJurusan"
                                optionValue="id"
                                optionLabel="nama_jurusan"
                                placeholder="Semua Jurusan"
                            />
                        @endif

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
                    @endif
                </x-slot:filters>
                
                <x-slot:reset>
                    @if(($userRole ?? '') !== 'Wali Kelas')
                        <x-ui.filter-reset @click="resetFilters(); filterOpen = false" />
                    @endif
                </x-slot:reset>
            </x-ui.action-bar>
            
            {{-- Bulk Action Toolbar - Only for Operator/Waka (not for Wali Kelas/Kaprodi) --}}
            @if(!in_array($userRole ?? '', ['Wali Kelas', 'Kaprodi']))
            <div x-show="selected.length > 0" x-transition x-cloak class="mt-3 bg-indigo-50 p-2 flex flex-col sm:flex-row justify-between items-center gap-3 rounded-lg border border-indigo-100">
                <div class="flex items-center gap-2 px-1">
                    <span class="flex items-center justify-center w-auto min-w-[1.25rem] px-1 h-5 rounded-full bg-indigo-600 text-white text-[10px] font-bold" x-text="allSelected ? totalItems : selected.length"></span>
                    <span class="text-sm font-medium text-indigo-900">Siswa Terpilih</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button 
                        type="button" 
                        @click="$dispatch('open-bulk-delete-modal', { ids: selected, allSelected: allSelected, filters: JSON.parse(JSON.stringify(filters)), totalItems: totalItems })" 
                        class="btn btn-sm btn-white text-red-600 border-red-200 hover:bg-red-50 py-1"
                    >
                        <x-ui.icon name="trash" size="14" />
                        Hapus Massal
                    </button>
                    <button 
                        type="button" 
                        @click="selected = []" 
                        class="btn btn-sm btn-white py-1"
                    >
                        Batal
                    </button>
                </div>
            </div>
            @endif
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

        {{-- Data Table --}}
        <div id="siswa-table-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
            @include('siswa._table')
        </div>
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
        allSelected: false,
        activeFilters: {},
        selectedCount: 0
    }"
    @open-delete-modal.window="
        open = true; 
        bulkMode = false;
        allSelected = false;
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
        allSelected = $event.detail.allSelected || false;
        activeFilters = $event.detail.filters || {};
        selectedCount = allSelected ? $event.detail.totalItems : $event.detail.ids.length;
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
                    <div>
                        <input type="hidden" name="ids" x-model="bulkIdsString">
                        <template x-if="allSelected">
                            <input type="hidden" name="all_selected" value="1">
                        </template>
                        <template x-for="(value, key) in activeFilters" :key="key">
                            <template x-if="value">
                                <input type="hidden" :name="'filters[' + key + ']'" :value="value">
                            </template>
                        </template>
                    </div>
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
