@extends('layouts.app')

@section('title', 'Arsip Konsentrasi')

@section('page-header')
    <x-page-header 
        title="Arsip Konsentrasi" 
        subtitle="Konsentrasi keahlian yang telah diarsipkan. Dapat di-restore atau dihapus permanen."
        :total="$konsentrasiList->count()"
    />
@endsection

@section('content')
<div class="space-y-6">

    {{-- Action Bar --}}
    <div class="flex justify-between items-center"
     x-data='{
         selected: [],
         selectionMode: false,
         selectAll: false,
         allIds: @json($allIds ?? []),
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
     @toggle-selection-mode.window="selectionMode = $event.detail !== undefined ? $event.detail : !selectionMode"
     @enter-selection.window="selectionMode = true; if (!selected.includes(String($event.detail.id))) selected.push(String($event.detail.id))">
    
    <div class="w-full bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm overflow-hidden mb-8 border-b border-gray-200 md:border-b-0">
        {{-- Toolbar --}}
        <div class="px-4 md:px-6 py-5 border-b border-gray-100 bg-white">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                {{-- Left side: Total Counter --}}
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Konsentrasi Diarsipkan</span>
                    <span class="text-lg font-bold text-gray-900 leading-none">{{ $konsentrasiList->count() }}</span>
                </div>
            </div>
            
            {{-- Bulk Action Toolbar --}}
            <div x-show="selected.length > 0" x-transition x-cloak class="mt-3 bg-indigo-50 p-2 flex flex-col sm:flex-row justify-between items-center gap-3 rounded-lg border border-indigo-100">
                <div class="flex items-center gap-2 px-1">
                    <span class="flex items-center justify-center w-auto min-w-[1.25rem] px-1 h-5 rounded-full bg-indigo-600 text-white text-[10px] font-bold" x-text="selectedCount"></span>
                    <span class="text-sm font-medium text-indigo-900">Konsentrasi Terpilih</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    <form action="{{ route('konsentrasi.bulk-restore') }}" method="POST" class="inline">
                        @csrf
                        <template x-for="id in selected" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>
                        <button type="submit" class="btn btn-sm btn-white text-emerald-600 border-emerald-200 hover:bg-emerald-50">
                            <x-ui.icon name="rotate-ccw" size="14" />
                            Restore
                        </button>
                    </form>

                    <form action="{{ route('konsentrasi.bulk-force-delete') }}" method="POST" class="inline" onsubmit="return confirm('HAPUS PERMANEN data terpilih?')">
                        @csrf
                        @method('DELETE')
                        <template x-for="id in selected" :key="id">
                             <input type="hidden" name="ids[]" :value="id">
                        </template>
                        <button type="submit" class="btn btn-sm btn-white text-red-600 border-red-200 hover:bg-red-50">
                            <x-ui.icon name="trash" size="14" />
                            Hapus Permanen
                        </button>
                    </form>

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
                         <th class="w-10">
                            <input type="checkbox" x-model="selectAll" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </th>
                        <th>Konsentrasi</th>
                        <th>Jurusan</th>
                        <th class="w-24 text-center">Kelas</th>
                        <th class="w-40 text-center">Diarsipkan pada</th>
                        <th class="w-28 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($konsentrasiList as $konsentrasi)
                        <tr class="bg-slate-50 transition-colors" :class="{ 'bg-indigo-50/50': selected.includes('{{ $konsentrasi->id }}') }">
                            <td>
                                <input type="checkbox" value="{{ $konsentrasi->id }}" x-model="selected" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            </td>
                            <td>
                                <div class="font-medium text-slate-600">{{ $konsentrasi->nama_konsentrasi }}</div>
                                @if($konsentrasi->kode_konsentrasi)
                                    <div class="text-xs text-slate-400">{{ $konsentrasi->kode_konsentrasi }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="text-sm text-slate-500">{{ $konsentrasi->jurusan->nama_jurusan ?? '-' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-slate">{{ $konsentrasi->kelas_count }} kelas</span>
                            </td>
                            <td class="text-center text-sm text-slate-500">
                                {{ $konsentrasi->deleted_at->format('d M Y H:i') }}
                            </td>
                            <td>
                                <div class="flex items-center justify-center gap-1">
                                    <form action="{{ route('konsentrasi.restore', $konsentrasi->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Pulihkan konsentrasi ini?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-icon btn-success" title="Pulihkan">
                                            <x-ui.icon name="refresh-cw" size="14" />
                                        </button>
                                    </form>
                                    <form action="{{ route('konsentrasi.forceDelete', $konsentrasi->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('HAPUS PERMANEN konsentrasi ini? Data tidak dapat dikembalikan!')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-white text-red-600 hover:text-red-700" title="Hapus Permanen">
                                            <x-ui.icon name="trash-2" size="14" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-ui.empty-state
                                    icon="archive"
                                    title="Arsip Kosong"
                                    description="Tidak ada konsentrasi yang diarsipkan."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>
</div>
@endsection
