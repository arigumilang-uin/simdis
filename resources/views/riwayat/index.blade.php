@extends('layouts.app')

@section('title', 'Log Pelanggaran')

@section('page-header')
    <div class="page-header-stylish">
        <div class="page-header-text">
            <h1 class="page-header-title">Log Pelanggaran</h1>
            <p class="page-header-subtitle">Riwayat pencatatan pelanggaran siswa</p>
        </div>
        <div class="page-header-stats" 
             x-data="{ total: {{ $riwayat->total() }} }" 
             @update-total-data.window="total = $event.detail.total">
            <x-ui.icon name="database" size="16" />
            <span>Total: <span class="count" x-text="total">{{ $riwayat->total() }}</span> data</span>
        </div>
    </div>
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

<div class="space-y-6" x-data="riwayatPage()" x-init="initPage()">
    {{-- Action Button --}}
    <div class="flex justify-end">
        <a href="{{ route('riwayat.create') }}" class="btn btn-primary">
            <x-ui.icon name="plus" size="18" />
            <span>Catat Pelanggaran</span>
        </a>
    </div>
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
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {{-- Search --}}
                    <div class="form-group md:col-span-4">
                        <x-forms.input 
                            id="search_filter"
                            name="search" 
                            label="Cari" 
                            x-model.debounce.500ms="filters.search"
                            placeholder="Cari nama siswa, NISN, jenis pelanggaran, atau pencatat..."
                        />
                        <div class="absolute right-0 top-8 pr-3 pointer-events-none" x-show="isLoading">
                            <x-ui.icon name="loader" class="animate-spin text-gray-400" size="16" />
                        </div>
                    </div>
                    
                    {{-- Dari Tanggal --}}
                    <div class="form-group">
                        <x-forms.input 
                            id="start_date"
                            type="date"
                            name="start_date"
                            label="Dari Tanggal"
                            x-model="filters.start_date"
                            @change="fetchData()"
                        />
                    </div>
                    
                    {{-- Sampai Tanggal --}}
                    <div class="form-group">
                        <x-forms.input 
                            id="end_date"
                            type="date"
                            name="end_date"
                            label="Sampai Tanggal"
                            x-model="filters.end_date"
                            @change="fetchData()"
                        />
                    </div>
                    
                    {{-- Jurusan Dropdown --}}
                    <div class="form-group">
                        <x-forms.select 
                            id="jurusan_id"
                            name="jurusan_id" 
                            label="Jurusan"
                            x-model="filters.jurusan_id"
                            @change="onJurusanChange()"
                            :options="$allJurusan ?? []"
                            optionValue="id"
                            optionLabel="nama_jurusan"
                            placeholder="Semua Jurusan"
                        />
                    </div>
                    
                    {{-- Kelas Dropdown (Dynamic with x-for because options change dynamically) --}}
                    <div class="form-group">
                        <label for="kelas_id" class="form-label">Kelas</label>
                        <select id="kelas_id" name="kelas_id" x-model="filters.kelas_id" @change="fetchData()" class="form-input form-select w-full" :disabled="loadingKelas">
                            <option value="">Semua Kelas</option>
                            <template x-for="kelas in kelasList" :key="kelas.id">
                                <option :value="kelas.id" x-text="kelas.nama_kelas"></option>
                            </template>
                        </select>
                        <p class="text-xs text-blue-500 mt-1" x-show="loadingKelas">Memuat kelas...</p>
                    </div>
                    
                    {{-- Reset Button --}}
                    <div class="md:col-span-4 flex justify-end">
                        <button type="button" @click="resetFilters()" class="btn btn-secondary">
                            <x-ui.icon name="refresh-cw" size="14" />
                            <span>Reset Filter</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Table Container --}}
    <div id="riwayat-table-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        @include('riwayat._table')
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
