{{-- Tab: Activity Logs --}}
@php
    $tableConfig = [
        'endpoint' => route('audit.activity.index'),
        'containerId' => 'activity-logs-table-container',
        'filters' => [
            'tab' => 'activity', // Always preserve tab
            'search' => request('search'),
            'type' => request('type'),
            'dari_tanggal' => request('dari_tanggal'),
            'sampai_tanggal' => request('sampai_tanggal')
        ]
    ];
@endphp

<div class="space-y-6" x-data='dataTable(@json($tableConfig))'>
    {{-- Filter --}}
    <div class="card" x-data="{ expanded: {{ request()->hasAny(['search', 'type', 'dari_tanggal', 'sampai_tanggal']) ? 'true' : 'false' }} }">
        <div class="card-header cursor-pointer" @click="expanded = !expanded">
            <div class="flex items-center gap-2">
                <x-ui.icon name="filter" size="18" class="text-gray-400" />
                <span class="card-title">Filter Parameter</span>
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
                        <label class="form-label">Cari Deskripsi / User</label>
                    <div class="relative">
                        <input 
                            type="text" 
                            x-model.debounce.500ms="filters.search" 
                            class="form-input pr-10 w-full" 
                            placeholder="Kata kunci..."
                        >
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" x-show="isLoading">
                            <x-ui.icon name="spinner" size="16" class="animate-spin h-4 w-4 text-gray-400" />
                        </div>
                    </div>
                </div>
                
                <div class="form-group md:col-span-2">
                    <label class="form-label">Jenis Log</label>
                    <select x-model="filters.type" class="form-input form-select w-full">
                        <option value="">Semua Jenis</option>
                        @foreach($activityTypes ?? [] as $type)
                            <option value="{{ $type }}">
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group md:col-span-2">
                    <label class="form-label">Dari</label>
                    <input type="date" x-model="filters.dari_tanggal" class="form-input w-full">
                </div>

                <div class="form-group md:col-span-2">
                    <label class="form-label">Sampai</label>
                    <input type="date" x-model="filters.sampai_tanggal" class="form-input w-full">
                </div>
                
                <div class="md:col-span-4 flex justify-end">
                    <button type="button" @click="filters.search = ''; filters.type = ''; filters.dari_tanggal = ''; filters.sampai_tanggal = '';" class="btn btn-secondary text-xs">
                        <x-ui.icon name="refresh-cw" size="14" />
                        <span>Reset Filter</span>
                    </button>
                </div>
            </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div id="activity-logs-table-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        @include('kepala_sekolah.activity._table_logs')
    </div>
</div>

