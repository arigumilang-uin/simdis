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

<div class="space-y-4" x-data='dataTable(@json($tableConfig))'>
    
    <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm overflow-hidden mb-8 border-b border-gray-200 md:border-b-0">
        {{-- Toolbar --}}
        <div class="px-4 md:px-6 py-5 border-b border-gray-100 bg-white">
            <x-ui.action-bar :total="$users->total()" totalLabel="User" class="!gap-4">
                <x-slot:search>
                    <input 
                        type="text" 
                        x-model.debounce.500ms="filters.search"
                        class="w-full md:w-80 rounded-xl border-0 bg-gray-100/80 text-sm text-gray-800 py-2.5 pl-10 pr-4 hover:bg-gray-100 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:shadow-lg focus:shadow-indigo-500/5 transition-all duration-200 placeholder-gray-400"
                        placeholder="Nama, Username, atau Email..."
                    >
                </x-slot:search>
                <x-slot:filters>
                    <div class="space-y-4">
                        {{-- Status --}}
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-gray-500 uppercase">Status</label>
                            <select x-model="filters.status" class="form-select w-full text-sm rounded-lg">
                                <option value="">Semua Status</option>
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                        
                        {{-- Role --}}
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-gray-500 uppercase">Role</label>
                            <select x-model="filters.role_id" class="form-select w-full text-sm rounded-lg">
                                <option value="">Semua Role</option>
                                @foreach($roles ?? [] as $role)
                                    <option value="{{ $role->id }}">{{ $role->nama_role }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </x-slot:filters>
                <x-slot:reset>
                    <button type="button" @click="resetFilters()" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Reset</button>
                </x-slot:reset>
            </x-ui.action-bar>
        </div>

        {{-- Table --}}
        <div id="status-table-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
            @include('kepala_sekolah.activity._table_status')
        </div>
    </div>
</div>
