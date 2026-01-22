@extends('layouts.app')

@section('title', 'Arsip Pengguna')

@section('page-header')
    <x-page-header 
        title="Arsip Pengguna" 
        subtitle="Pengguna yang telah diarsipkan. Dapat di-restore atau dihapus permanen."
        :total="$users->count()"
    />
@endsection

@section('content')
@php
    $allIds = $users->pluck('id')->map(fn($id) => (string) $id)->toArray();
@endphp

<div class="space-y-4"
     x-data='{
         selected: [],
         selectionMode: false,
         selectAll: false,
         allIds: @json($allIds),
         get selectedCount() {
             return this.selected.length;
         },
         toggleSelectAll() {
             if (this.selectAll) {
                 this.selected = [...this.allIds];
             } else {
                 this.selected = [];
             }
         }
     }'
     x-init="$watch('selectAll', () => toggleSelectAll()); $watch('selected', val => { if (val.length === 0) selectionMode = false; selectAll = val.length === allIds.length && allIds.length > 0; })"
     @toggle-selection-mode.window="selectionMode = $event.detail !== undefined ? $event.detail : !selectionMode">
    
    <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm overflow-hidden mb-8 border-b border-gray-200 md:border-b-0">
        {{-- Toolbar --}}
        {{-- Filter Toolbar --}}
        <form method="GET" action="{{ route('users.trash') }}" class="bg-white border-b border-gray-100">
            <div class="px-4 md:px-6 py-5">
                <x-ui.action-bar :total="$users->count()" totalLabel="Pengguna" class="!gap-4">
                    <x-slot:search>
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            class="w-full md:w-80 rounded-xl border-0 bg-gray-100/80 text-sm text-gray-800 py-2.5 pl-9 pr-4 hover:bg-gray-100 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:shadow-lg focus:shadow-indigo-500/5 transition-all duration-200 placeholder-gray-400"
                            placeholder="Cari username atau keterangan..."
                        >
                    </x-slot:search>
                    
                    <x-slot:filters>
                        <x-ui.filter-select name="role_id" label="Peran" onchange="this.form.submit()">
                            <option value="">Semua Peran</option>
                            @foreach($roles ?? [] as $role)
                                <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->nama_role }}
                                </option>
                            @endforeach
                        </x-ui.filter-select>

                        <div class="space-y-3 pt-3 border-t border-gray-50">
                            <span class="block text-xs font-medium text-gray-500">Tanggal Dihapus</span>
                            <x-ui.filter-date name="start_date" label="Dari" value="{{ request('start_date') }}" onchange="this.form.submit()" />
                            <x-ui.filter-date name="end_date" label="Sampai" value="{{ request('end_date') }}" onchange="this.form.submit()" />
                        </div>
                    </x-slot:filters>
                    
                    <x-slot:reset>
                        @if(request('search') || request('role_id') || request('start_date') || request('end_date'))
                            <a href="{{ route('users.trash') }}" class="text-xs font-medium text-gray-400 hover:text-red-500 transition-colors">
                                Reset
                            </a>
                        @endif
                    </x-slot:reset>
                </x-ui.action-bar>
            </div>
        </form>

        {{-- Bulk Action Toolbar --}}
        <div class="px-4 md:px-6 py-2" x-show="selected.length > 0" x-transition x-cloak>
            <div class="bg-indigo-50 p-2 flex flex-col sm:flex-row justify-between items-center gap-3 rounded-lg border border-indigo-100">
                <div class="flex items-center gap-2 px-1">
                    <span class="flex items-center justify-center w-auto min-w-[1.25rem] px-1 h-5 rounded-full bg-indigo-600 text-white text-[10px] font-bold" x-text="selected.length"></span>
                    <span class="text-sm font-medium text-indigo-900">Pengguna Terpilih</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="$dispatch('open-bulk-restore-modal', { ids: selected, count: selected.length })" class="btn btn-sm btn-white text-emerald-600 border-emerald-200 hover:bg-emerald-50">
                        <x-ui.icon name="rotate-ccw" size="14" />
                        Restore
                    </button>
                    <button type="button" @click="$dispatch('open-bulk-permanent-delete-modal', { ids: selected, count: selected.length })" class="btn btn-sm btn-white text-red-600 border-red-200 hover:bg-red-50">
                        <x-ui.icon name="trash" size="14" />
                        Hapus Permanen
                    </button>
                    <button type="button" @click="selected = []; selectionMode = false;" class="btn btn-sm btn-white">
                        Batal
                    </button>
                </div>
            </div>
        </div>

        {{-- Banner Select All Everything --}}
        <div x-show="selectAll && allIds.length > 0" x-cloak class="px-6 py-3 bg-indigo-50 border-b border-indigo-100 text-center text-sm text-indigo-800 transition-all">
            <span class="mr-1">Semua <span class="font-bold" x-text="selected.length"></span> pengguna di halaman ini terpilih.</span>
            <button type="button" @click="selected = []; selectAll = false; selectionMode = false;" class="font-bold text-indigo-600 hover:text-indigo-800 hover:underline focus:outline-none ml-1">
                Batalkan pilihan
            </button>
        </div>

        {{-- Data Table --}}
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Username</th>
                        <th>Keterangan</th>
                        <th class="text-center">Peran</th>
                        <th>Tanggal Dihapus</th>
                        <x-table.action-header />
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                        <tr :class="{ 'bg-indigo-50/40': selected.includes('{{ $user->id }}') }">
                            <td class="text-gray-500">{{ $index + 1 }}</td>
                            <td>
                                <span class="font-mono text-sm font-medium text-gray-800">
                                    {{ preg_replace('/_deleted_\d+$/', '', $user->username) }}
                                </span>
                            </td>
                            <td>
                                <div class="text-gray-600 text-sm">{{ $user->nama }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-neutral">{{ $user->role->nama_role ?? '-' }}</span>
                            </td>
                            <td class="text-gray-500 text-sm">{{ $user->deleted_at ? $user->deleted_at->format('d M Y H:i') : '-' }}</td>
                            <x-table.action-column :id="$user->id">
                                <x-table.action-item 
                                    icon="rotate-ccw" 
                                    class="text-emerald-600 hover:bg-emerald-50 hover:text-emerald-700"
                                    @click="open = false; if(confirm('Restore pengguna ini?')) { document.getElementById('restore-form-{{ $user->id }}').submit(); }"
                                >
                                    Restore
                                </x-table.action-item>
                                
                                <x-table.action-separator />
                                
                                <x-table.action-item 
                                    icon="trash" 
                                    class="text-red-600 hover:bg-red-50 hover:text-red-700"
                                    @click="open = false; if(confirm('HAPUS PERMANEN pengguna ini? Data tidak dapat dikembalikan!')) { document.getElementById('force-delete-form-{{ $user->id }}').submit(); }"
                                >
                                    Hapus Permanen
                                </x-table.action-item>
                            </x-table.action-column>
                        </tr>
                        
                        {{-- Hidden Forms --}}
                        <form id="restore-form-{{ $user->id }}" action="{{ route('users.restore', $user->id) }}" method="POST" class="hidden">
                            @csrf
                        </form>
                        <form id="force-delete-form-{{ $user->id }}" action="{{ route('users.forceDelete', $user->id) }}" method="POST" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-ui.empty-state 
                                    icon="check-circle" 
                                    title="Tidak Ada Data Arsip" 
                                    description="Tidak ada pengguna yang telah diarsipkan." 
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Bulk Restore Modal --}}
<div 
    x-data="{ 
        open: false,
        ids: [],
        count: 0,
        confirmed: false
    }"
    @open-bulk-restore-modal.window="
        open = true;
        ids = $event.detail.ids;
        count = $event.detail.count;
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
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"
    ></div>

    {{-- Modal Panel --}}
    <div class="flex min-h-full items-center justify-center p-4">
        <div 
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl"
            @click.outside="open = false"
        >
            {{-- Header --}}
            <div class="px-6 pt-6 pb-4 border-b border-gray-100">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                        <x-ui.icon name="rotate-ccw" class="w-6 h-6 text-emerald-600" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-semibold text-gray-900">Restore Pengguna Terpilih</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Anda akan memulihkan <span class="font-bold text-emerald-600" x-text="count"></span> pengguna dari arsip.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Body --}}
            <div class="px-6 py-4">
                <p class="text-sm text-gray-600">
                    Pengguna yang dipulihkan akan kembali aktif dan dapat mengakses sistem (sesuai status aktivasi sebelumnya).
                </p>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end gap-3">
                <button type="button" @click="open = false" class="btn btn-white">
                    Batal
                </button>
                <form method="POST" action="{{ route('users.bulk-restore') }}">
                    @csrf
                    <input type="hidden" name="ids" x-bind:value="ids.join(',')">
                    <button type="submit" class="btn btn-primary bg-emerald-600 hover:bg-emerald-700">
                        <x-ui.icon name="rotate-ccw" size="16" />
                        <span>Restore</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Bulk Permanent Delete Modal --}}
<div 
    x-data="{ 
        open: false,
        ids: [],
        count: 0,
        confirmed: false
    }"
    @open-bulk-permanent-delete-modal.window="
        open = true;
        ids = $event.detail.ids;
        count = $event.detail.count;
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
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"
    ></div>

    {{-- Modal Panel --}}
    <div class="flex min-h-full items-center justify-center p-4">
        <div 
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl"
            @click.outside="open = false"
        >
            {{-- Header --}}
            <div class="px-6 pt-6 pb-4 border-b border-gray-100">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                        <x-ui.icon name="alert-triangle" class="w-6 h-6 text-red-600" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-semibold text-gray-900">Hapus Permanen Pengguna</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Anda akan menghapus <span class="font-bold text-red-600" x-text="count"></span> pengguna secara permanen.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Body --}}
            <div class="px-6 py-4">
                <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                    <div class="flex gap-3">
                        <x-ui.icon name="alert-circle" class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" />
                        <div class="text-sm text-red-700">
                            <p class="font-medium">Peringatan!</p>
                            <p class="mt-1">Data yang dihapus permanen tidak dapat dikembalikan. Pengguna dengan jadwal mengajar atau riwayat pencatatan pelanggaran akan dilewati.</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="confirmed" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                        <span class="text-sm text-gray-700">Saya mengerti dan ingin melanjutkan penghapusan permanen</span>
                    </label>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex justify-end gap-3">
                <button type="button" @click="open = false" class="btn btn-white">
                    Batal
                </button>
                <form method="POST" action="{{ route('users.bulk-force-delete') }}">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="ids" x-bind:value="ids.join(',')">
                    <button type="submit" class="btn btn-danger" :disabled="!confirmed" :class="{ 'opacity-50 cursor-not-allowed': !confirmed }">
                        <x-ui.icon name="trash" size="16" />
                        <span>Hapus Permanen</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
