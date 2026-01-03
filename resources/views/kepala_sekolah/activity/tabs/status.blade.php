{{-- Tab: Status Akun --}}
@php
    $tableConfig = [
        'endpoint' => route('audit.activity.index'),
        'containerId' => 'status-table-container',
        'filters' => [
            'tab' => 'status',
            'search' => request('search'),
            'status' => request('status'),
            'role_id' => request('role_id')
        ]
    ];
@endphp

<div class="space-y-6" x-data='dataTable(@json($tableConfig))'>
    <div class="card" x-data="{ expanded: {{ request()->hasAny(['search', 'role_id', 'status']) ? 'true' : 'false' }} }">
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
                    <div class="form-group md:col-span-2">
                        <label class="form-label">Pencarian</label>
                    <input type="text" x-model.debounce.500ms="filters.search" class="form-input w-full" placeholder="Nama, Username, atau Email...">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select x-model="filters.status" class="form-input form-select w-full">
                        <option value="">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select x-model="filters.role_id" class="form-input form-select w-full">
                        <option value="">Semua Role</option>
                        @foreach($roles ?? [] as $role)
                            <option value="{{ $role->id }}">{{ $role->nama_role }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-4 flex justify-end">
                    <button type="button" @click="resetFilters()" class="btn btn-secondary text-xs">Reset</button>
                </div>
            </div>
            </div>
        </div>
    </div>
    <div id="status-table-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        @include('kepala_sekolah.activity._table_status')
    </div>
</div>
