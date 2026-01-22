@extends('layouts.app')

@section('title', 'Log Pelanggaran')

@section('page-header')
    <x-page-header 
        title="Log Pelanggaran" 
        subtitle="Riwayat pencatatan pelanggaran siswa"
    >
        <x-slot:actions>
            <a href="{{ route('riwayat.create') }}" class="btn btn-primary">
                <x-ui.icon name="plus" size="18" />
                <span>Catat Pelanggaran</span>
            </a>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
@php
    $tableConfig = [
        'endpoint' => route('riwayat.index'),
        'containerId' => 'riwayat-table-container',
        'filters' => [
            'search' => request('search'),
            'jurusan_id' => request('jurusan_id'),
            'kelas_id' => request('kelas_id'),
            'start_date' => request('start_date'),
            'end_date' => request('end_date')
        ],
        // Exclude date filters from auto-watch (handled manually via @change)
        'excludeFromWatch' => ['start_date', 'end_date', 'jurusan_id'],
        // Custom state for cascade dropdown
        'customState' => [
            'kelasList' => $allKelas ?? [],
            'allKelasOriginal' => $allKelas ?? [],
            'loadingKelas' => false
        ]
    ];
@endphp

<div class="space-y-4" x-data="riwayatPage()" x-init="initPage()">
    
    <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm overflow-hidden mb-8 border-b border-gray-200 md:border-b-0">
        {{-- Unified Toolbar --}}
        <div class="px-4 md:px-6 py-5 border-b border-gray-100 bg-white">
            <x-ui.action-bar :total="$riwayat->total()" totalLabel="Log" class="!gap-4">
                <x-slot:search>
                    <input 
                        type="text" 
                        x-model.debounce.500ms="filters.search"
                        class="w-full md:w-80 rounded-xl border-0 bg-gray-100/80 text-sm text-gray-800 py-2.5 pl-10 pr-4 hover:bg-gray-100 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:shadow-lg focus:shadow-indigo-500/5 transition-all duration-200 placeholder-gray-400"
                        placeholder="Cari nama, NISN, jenis pelanggaran..."
                    >
                </x-slot:search>
                <x-slot:filters>
                    <div class="space-y-4">
                        {{-- Dari Tanggal --}}
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-gray-500 uppercase">Dari Tanggal</label>
                            <input type="date" x-model="filters.start_date" @change="fetchData()" class="form-input w-full text-sm rounded-lg">
                        </div>
                        
                        {{-- Sampai Tanggal --}}
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-gray-500 uppercase">Sampai Tanggal</label>
                            <input type="date" x-model="filters.end_date" @change="fetchData()" class="form-input w-full text-sm rounded-lg">
                        </div>
                        
                        {{-- Jurusan --}}
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-gray-500 uppercase">Jurusan</label>
                            <select x-model="filters.jurusan_id" @change="onJurusanChange()" class="form-select w-full text-sm rounded-lg">
                                <option value="">Semua Jurusan</option>
                                @foreach($allJurusan ?? [] as $jurusan)
                                    <option value="{{ $jurusan->id }}">{{ $jurusan->nama_jurusan }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Kelas (Dynamic) --}}
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-gray-500 uppercase">Kelas</label>
                            <select x-model="filters.kelas_id" @change="fetchData()" class="form-select w-full text-sm rounded-lg" :disabled="loadingKelas">
                                <option value="">Semua Kelas</option>
                                <template x-for="kelas in kelasList" :key="kelas.id">
                                    <option :value="kelas.id" x-text="kelas.nama_kelas"></option>
                                </template>
                            </select>
                            <p class="text-xs text-blue-500" x-show="loadingKelas">Memuat kelas...</p>
                        </div>
                    </div>
                </x-slot:filters>
                <x-slot:reset>
                    <button type="button" @click="resetFilters()" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Reset</button>
                </x-slot:reset>
            </x-ui.action-bar>
        </div>

        {{-- Table Container --}}
        <div id="riwayat-table-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
            @include('riwayat._table')
        </div>
    </div>
</div>

{{-- Bulk Delete Riwayat Modal --}}
<div 
    x-data="{ 
        open: false, 
        selectedIds: [],
        selectedCount: 0,
        confirmed: false
    }"
    @open-bulk-delete-riwayat-modal.window="
        open = true; 
        selectedIds = $event.detail.ids; 
        selectedCount = $event.detail.count;
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
                        <x-ui.icon name="trash" size="24" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-red-600">Hapus Data Pelanggaran</h3>
                        <p class="text-sm text-gray-500">Konfirmasi penghapusan data</p>
                    </div>
                </div>
            </div>
            
            {{-- Body --}}
            <form action="{{ route('riwayat.bulk-delete') }}" method="POST">
                @csrf
                
                {{-- Hidden input for IDs --}}
                <input type="hidden" name="ids" :value="selectedIds.join(',')">
                
                <div class="p-6 space-y-4">
                    {{-- Count Info --}}
                    <div class="p-4 bg-red-50 rounded-xl border border-red-100 text-center">
                        <p class="text-sm text-red-600">Jumlah data yang akan dihapus:</p>
                        <p class="text-3xl font-bold text-red-800" x-text="selectedCount"></p>
                    </div>
                    
                    {{-- Warning --}}
                    <div class="p-4 bg-amber-50 rounded-xl space-y-2">
                        <p class="text-sm font-medium text-amber-800">⚠️ Perhatian:</p>
                        <ul class="text-sm text-amber-700 space-y-1 pl-4">
                            <li>• Data pelanggaran yang dihapus tidak dapat dikembalikan</li>
                            <li>• Poin siswa akan dihitung ulang secara otomatis</li>
                            <li>• Bukti foto (jika ada) akan dihapus dari storage</li>
                        </ul>
                    </div>
                    
                    {{-- Confirmation Checkbox --}}
                    <label class="flex items-start gap-3 cursor-pointer p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <input 
                            type="checkbox" 
                            x-model="confirmed"
                            class="w-4 h-4 mt-0.5 rounded border-gray-300 text-red-600 focus:ring-red-500"
                        >
                        <span class="text-sm text-gray-700">
                            Saya yakin ingin menghapus <strong x-text="selectedCount"></strong> data pelanggaran terpilih.
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
                        <span>Hapus <span x-text="selectedCount"></span> Data</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('riwayatPage', () => ({
        // Core dataTable state
        isLoading: false,
        endpoint: @json(route('riwayat.index')),
        containerId: 'riwayat-table-container',
        filters: {
            search: @json(request('search')),
            jurusan_id: @json(request('jurusan_id')),
            kelas_id: @json(request('kelas_id')),
            start_date: @json(request('start_date')),
            end_date: @json(request('end_date'))
        },
        selectionMode: false,
        selectAll: false,
        selected: [],
        
        // Custom state for cascade dropdown
        kelasList: @json($allKelas ?? []),
        allKelasOriginal: @json($allKelas ?? []),
        loadingKelas: false,
        
        initPage() {
            // Watch search filter only
            this.$watch('filters.search', () => this.fetchData());
            
            // Watch selection mode
            this.$watch('selectionMode', (value) => {
                if (!value) {
                    this.selected = [];
                    this.selectAll = false;
                }
            });
            
            // Pagination click listener
            const container = document.getElementById(this.containerId);
            if (container) {
                container.addEventListener('click', (e) => {
                    const link = e.target.closest('.pagination a');
                    if (link) {
                        e.preventDefault();
                        this.fetchData(link.href);
                    }
                });
            }
            
            // Browser back button
            window.addEventListener('popstate', () => {
                this.fetchData(window.location.href, false);
            });
            
            // Load kelas if jurusan already selected
            if (this.filters.jurusan_id) {
                this.loadKelasByJurusan(this.filters.jurusan_id);
            }
        },
        
        async onJurusanChange() {
            this.filters.kelas_id = '';
            if (this.filters.jurusan_id) {
                await this.loadKelasByJurusan(this.filters.jurusan_id);
            } else {
                this.kelasList = this.allKelasOriginal;
            }
            this.fetchData();
        },
        
        async loadKelasByJurusan(jurusanId) {
            this.loadingKelas = true;
            try {
                const response = await fetch('/api/kelas-by-jurusan?jurusan_id=' + jurusanId, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (response.ok) {
                    this.kelasList = await response.json();
                }
            } catch (error) {
                console.error('Error loading kelas:', error);
                this.kelasList = this.allKelasOriginal;
            } finally {
                this.loadingKelas = false;
            }
        },
        
        async fetchData(url = null, updatePushState = true) {
            this.isLoading = true;
            let fetchUrl = url;

            if (!fetchUrl) {
                const params = new URLSearchParams();
                Object.entries(this.filters).forEach(([key, value]) => {
                    if (value !== null && value !== '' && value !== undefined) {
                        params.append(key, value);
                    }
                });

                const cleanUrl = `${this.endpoint}?${params.toString()}`;
                if (updatePushState) {
                    try { window.history.pushState({}, '', cleanUrl); } catch(e) {}
                }

                params.append('render_partial', '1');
                fetchUrl = `${this.endpoint}?${params.toString()}`;
            } else {
                const urlObj = new URL(url);
                urlObj.searchParams.append('render_partial', '1');
                fetchUrl = urlObj.toString();

                if (updatePushState) {
                    try { window.history.pushState({}, '', url); } catch(e) {}
                }
            }

            try {
                const response = await fetch(fetchUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
                });

                if (response.ok) {
                    const html = await response.text();
                    const container = document.getElementById(this.containerId);
                    if (container) {
                        container.innerHTML = html;
                        if (this.selectAll) {
                            this.selected = [];
                            this.selectAll = false;
                        }
                    }
                }
            } catch (error) {
                console.error('Data table error:', error);
            } finally {
                this.isLoading = false;
            }
        },
        
        resetFilters() {
            Object.keys(this.filters).forEach(key => {
                this.filters[key] = '';
            });
            this.kelasList = this.allKelasOriginal;
        },
        
        toggleSelectionMode() {
            this.selectionMode = !this.selectionMode;
        },
        
        toggleSelectAll() {
            const container = document.getElementById(this.containerId);
            if (!container) return;
            const checkboxes = container.querySelectorAll('input[type="checkbox"][value]');
            if (this.selectAll) {
                this.selected = Array.from(checkboxes).map(cb => cb.value);
            } else {
                this.selected = [];
            }
        }
    }));
});
</script>
@endpush
@endsection
