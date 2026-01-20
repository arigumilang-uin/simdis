@extends('layouts.app')

@section('title', 'Manajemen Kelas')

@section('page-header')
    <x-page-header 
        title="Manajemen Kelas" 
        subtitle="Kelola data rombongan belajar sekolah."
    >
        <x-slot:actions>
            <a href="{{ route('kelas.trash') }}" class="btn btn-white">
                <x-ui.icon name="archive" size="16" />
                <span>Arsip</span>
            </a>
            <a href="{{ route('kelas.create') }}" class="btn btn-primary">
                <x-ui.icon name="plus" size="18" />
                <span>Tambah Kelas</span>
            </a>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
<div class="space-y-4" x-data='{ 
    selectionMode: false, 
    selected: [], 
    selectAll: false,
    pageIds: {{ json_encode($kelasList->pluck('id')->map(fn($id) => (string) $id)) }},
    init() {
         this.$watch("selectAll", val => {
             this.selected = val ? [...this.pageIds] : [];
         });
         this.$watch("selected", val => {
             if (val.length === 0) this.selectionMode = false;
             if (this.pageIds.length > 0 && val.length !== this.pageIds.length) this.selectAll = false;
             else if (this.pageIds.length > 0 && val.length === this.pageIds.length) this.selectAll = true;
         });
    }
}' 
@toggle-selection-mode.window="selectionMode = $event.detail !== undefined ? $event.detail : !selectionMode"
>
    
    <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm overflow-hidden mb-8 border-b border-gray-200 md:border-b-0">
        {{-- Unified Toolbar --}}
        <div class="px-4 md:px-6 py-5 border-b border-gray-100 bg-white">
            <x-ui.action-bar :total="method_exists($kelasList, 'total') ? $kelasList->total() : $kelasList->count()" totalLabel="Kelas" class="!gap-4" />
            
            {{-- Bulk Action Toolbar --}}
            <div x-show="selected.length > 0" x-transition x-cloak class="mt-3 bg-indigo-50 p-2 flex flex-col sm:flex-row justify-between items-center gap-3 rounded-lg border border-indigo-100">
                <div class="flex items-center gap-2 px-1">
                    <span class="flex items-center justify-center w-5 h-5 rounded-full bg-indigo-600 text-white text-[10px] font-bold" x-text="selected.length"></span>
                    <span class="text-sm font-medium text-indigo-900">Kelas Terpilih</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="if(confirm('Hapus ' + selected.length + ' kelas terpilih?')) { alert('Fitur bulk delete sedang dalam pengembangan.'); }" class="btn btn-sm btn-white text-red-600 border-red-200 hover:bg-red-50 py-1">
                        <x-ui.icon name="trash" size="14" />
                        Hapus Massal
                    </button>
                    <button type="button" @click="selected = []; selectionMode = false;" class="btn btn-sm btn-white">
                        Batal
                    </button>
                </div>
            </div>
        </div>
        
        <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th class="w-12">No</th>
                    <th>Nama Kelas</th>
                    <th>Jurusan</th>
                    <th>Konsentrasi</th>
                    <th>Wali Kelas</th>
                    <th class="text-center">Siswa</th>
<x-table.action-header />
                </tr>
            </thead>
            <tbody>
                @forelse($kelasList ?? [] as $index => $k)
                    <tr :class="{ 'bg-indigo-50/40': selected.includes('{{ $k->id }}') }">
                        <td class="text-gray-500">{{ $loop->iteration }}</td>
                        <td class="font-medium text-gray-800">{{ $k->nama_kelas }}</td>
                        <td>
                            <span class="font-mono text-sm bg-blue-50 text-blue-700 px-2 py-1 rounded-md">
                                {{ $k->jurusan->kode_jurusan ?? strtoupper(substr($k->jurusan->nama_jurusan ?? '-', 0, 3)) }}
                            </span>
                        </td>
                        <td>
                            @if($k->konsentrasi)
                                <span class="font-mono text-sm bg-purple-50 text-purple-700 px-2 py-1 rounded-md">
                                    {{ $k->konsentrasi->kode_konsentrasi ?? strtoupper(substr($k->konsentrasi->nama_konsentrasi, 0, 3)) }}
                                </span>
                            @else
                                <span class="text-gray-300 text-sm">-</span>
                            @endif
                        </td>
                        <td class="text-gray-600">
                            {{ $k->waliKelas->username ?? '-' }}
                        </td>
                        <td class="text-center">
                            <span class="badge badge-info">{{ $k->siswa_count ?? $k->siswa()->count() }}</span>
                        </td>
                        <x-table.action-column :id="$k->id">
                            <x-table.action-item icon="info" href="{{ route('kelas.show', $k->id) }}">
                                Detail
                            </x-table.action-item>
                            
                            @can('update', $k)
                                <x-table.action-item icon="edit" href="{{ route('kelas.edit', $k->id) }}">
                                    Edit
                                </x-table.action-item>
                            @endcan
                            
                            @can('delete', $k)
                                <x-table.action-separator />
                                <form action="{{ route('kelas.destroy', $k->id) }}" method="POST" onsubmit="return confirm('Hapus kelas ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-table.action-item 
                                        type="submit" 
                                        icon="trash" 
                                        class="text-red-600 hover:bg-red-50 hover:text-red-700 w-full text-left"
                                    >
                                        Hapus
                                    </x-table.action-item>
                                </form>
                            @endcan
                        </x-table.action-column>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-ui.empty-state 
                                title="Tidak Ada Data Kelas" 
                                description="Belum ada kelas yang terdaftar." 
                                icon="layout"
                            >
                                <x-slot:action>
                                    @can('create', App\Models\Kelas::class)
                                    <a href="{{ route('kelas.create') }}" class="btn btn-primary">
                                        <x-ui.icon name="plus" size="18" />
                                        <span>Tambah Kelas</span>
                                    </a>
                                    @endcan
                                </x-slot:action>
                            </x-ui.empty-state>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    </div>
    
    {{-- Pagination --}}
    @if(method_exists($kelasList, 'links'))
        <div class="flex justify-center">
            {{ $kelasList->links() }}
        </div>
    @endif
</div>
@endsection

