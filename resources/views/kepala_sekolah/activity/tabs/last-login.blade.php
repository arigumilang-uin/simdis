{{-- Tab: Last Login --}}
@php
    $tableConfig = [
        'endpoint' => route('audit.activity.index'),
        'containerId' => 'last-login-table-container',
        'filters' => [
            'tab' => 'last-login', // Always preserve tab
            'search' => request('search'),
            'role_id' => request('role_id'),
            'dari_tanggal' => request('dari_tanggal'),
            'sampai_tanggal' => request('sampai_tanggal')
        ]
    ];
@endphp

<div class="space-y-6" x-data='dataTable(@json($tableConfig))'>
    {{-- Filter --}}
    <div class="card" x-data="{ expanded: {{ request()->hasAny(['search', 'role_id']) ? 'true' : 'false' }} }">
        <div class="card-header cursor-pointer" @click="expanded = !expanded">
            <div class="flex items-center gap-2">
                <x-ui.icon name="filter" size="18" class="text-gray-400" />
                <span class="card-title">Filter Pengguna</span>
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
                        <label class="form-label">Cari Pengguna</label>
                    <div class="relative">
                        <input 
                            type="text" 
                            x-model.debounce.500ms="filters.search" 
                            class="form-input pr-10 w-full" 
                            placeholder="Nama, Username, atau Email..."
                        >
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" x-show="isLoading">
                            <x-ui.icon name="spinner" size="16" class="animate-spin h-4 w-4 text-gray-400" />
                        </div>
                    </div>
                </div>
                
                {{-- Role --}}
                <div class="form-group md:col-span-2">
                    <label class="form-label">Role / Jabatan</label>
                    <select x-model="filters.role_id" class="form-input form-select w-full">
                        <option value="">Semua Role</option>
                        @foreach($roles ?? [] as $role)
                            <option value="{{ $role->id }}">{{ $role->nama_role }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Dari --}}
                <div class="form-group md:col-span-2">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" x-model="filters.dari_tanggal" class="form-input w-full">
                </div>

                {{-- Sampai --}}
                <div class="form-group md:col-span-2">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" x-model="filters.sampai_tanggal" class="form-input w-full">
                </div>
                
                {{-- Actions --}}
                <div class="md:col-span-4 flex justify-end">
                    <button type="button" @click="filters.search = ''; filters.role_id = ''; filters.dari_tanggal = ''; filters.sampai_tanggal = '';" class="btn btn-secondary text-xs">
                        <x-ui.icon name="refresh-cw" size="14" />
                        <span>Reset Filter</span>
                    </button>
                </div>
            </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div id="last-login-table-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        @include('kepala_sekolah.activity._table_last_login')
    </div>
</div>

