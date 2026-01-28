@extends('layouts.app')

@section('title', 'Mata Pelajaran')

@section('page-header')
    <x-page-header 
        title="Mata Pelajaran" 
        subtitle="Kelola daftar mata pelajaran dan guru pengampunya"
    >
        <x-slot:actions>
            @if($kurikulumId)
                <a href="{{ route('admin.mata-pelajaran.create', ['kurikulum_id' => $kurikulumId, 'kelompok' => $kelompok]) }}" 
                   class="btn btn-primary">
                    <x-ui.icon name="plus" size="18" />
                    <span>Tambah Mapel</span>
                </a>
            @endif
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
<div class="space-y-6" x-data="{ selectionMode: false, selected: [], selectAll: false }">

    {{-- Kurikulum Tabs --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 px-4">
            <nav class="-mb-px flex space-x-1 overflow-x-auto" aria-label="Kurikulum Tabs">
                @foreach($kurikulums as $kur)
                    <a href="{{ route('admin.mata-pelajaran.index', ['kurikulum_id' => $kur->id, 'kelompok' => $kelompok]) }}"
                       class="flex items-center gap-2 py-3 px-4 border-b-2 font-medium text-sm transition-colors whitespace-nowrap
                              {{ $kurikulumId == $kur->id 
                                 ? 'border-indigo-500 text-indigo-600 bg-indigo-50/50' 
                                 : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                        <x-ui.icon name="book" size="16" class="{{ $kurikulumId == $kur->id ? 'text-indigo-500' : 'text-slate-400' }}" />
                        {{ $kur->nama }}
                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium
                                     {{ $kurikulumId == $kur->id ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $kur->mataPelajaran->count() }}
                        </span>
                    </a>
                @endforeach
            </nav>
        </div>
    </div>

    @if($selectedKurikulum)
        <div class="bg-white md:border md:border-slate-200 md:rounded-xl md:shadow-sm overflow-hidden">
            {{-- Toolbar --}}
            <div class="px-4 md:px-6 py-4 border-b border-slate-100 bg-white">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    
                    {{-- Kelompok Pills --}}
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach($kelompokOptions as $kode => $label)
                            @php
                                $isActive = $kelompok == $kode;
                                $shortLabel = explode(' - ', $label)[1] ?? $label;
                                $count = $kelompokCounts[$kode] ?? 0;
                            @endphp
                            <a href="{{ route('admin.mata-pelajaran.index', ['kurikulum_id' => $kurikulumId, 'kelompok' => $kode]) }}"
                               class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium transition-all border
                                      {{ $isActive 
                                         ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' 
                                         : 'bg-white text-slate-600 border-slate-200 hover:border-indigo-300 hover:bg-indigo-50' }}">
                                {{ $shortLabel }}
                                <span class="ml-2 inline-flex items-center justify-center px-1.5 min-w-[1.25rem] h-5 rounded-full text-xs
                                             {{ $isActive ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $count }}
                                </span>
                            </a>
                        @endforeach
                    </div>

                    {{-- Search --}}
                    <form method="GET">
                        <input type="hidden" name="kurikulum_id" value="{{ $kurikulumId }}">
                        <input type="hidden" name="kelompok" value="{{ $kelompok }}">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <x-ui.icon name="search" size="16" class="text-slate-400" />
                            </div>
                            <input type="text" 
                                   name="search" 
                                   value="{{ $search }}" 
                                   class="block w-full md:w-64 pl-10 pr-3 py-2 text-sm border border-slate-200 rounded-lg bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                                   placeholder="Cari mapel...">
                        </div>
                    </form>
                </div>
            </div>

            {{-- Table --}}
            @if($mataPelajaran->count() > 0)
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="w-16">No</th>
                                <th class="w-24">Kode</th>
                                <th>Mata Pelajaran</th>
                                <th>Guru Pengampu</th>
                                <th class="w-24 text-center">Status</th>
                                <x-table.action-header />
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mataPelajaran as $index => $mp)
                                <tr>
                                    <td class="font-medium text-slate-500">{{ $index + 1 }}</td>
                                    <td class="font-mono text-sm font-medium text-slate-600">{{ $mp->kode_mapel ?? '-' }}</td>
                                    <td>
                                        <div class="font-semibold text-slate-800">{{ $mp->nama_mapel }}</div>
                                        @if($mp->deskripsi)
                                            <div class="text-xs text-slate-500 mt-0.5 max-w-md truncate">{{ $mp->deskripsi }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($mp->guruPengampu->count() > 0)
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                @foreach($mp->guruPengampu->take(2) as $guru)
                                                    <span class="badge badge-secondary">
                                                        {{ $guru->username }}
                                                    </span>
                                                @endforeach
                                                @if($mp->guruPengampu->count() > 2)
                                                    <span class="badge badge-secondary" title="Dan {{ $mp->guruPengampu->count() - 2 }} guru lainnya">
                                                        +{{ $mp->guruPengampu->count() - 2 }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-slate-400 text-sm flex items-center gap-1">
                                                <x-ui.icon name="user-x" size="14" />
                                                <span>Belum ada</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($mp->is_active)
                                            <span class="badge badge-success">Aktif</span>
                                        @else
                                            <span class="badge badge-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <x-table.action-column :id="$mp->id">
                                        <x-table.action-item icon="edit" :href="route('admin.mata-pelajaran.edit', $mp->id)">
                                            Edit
                                        </x-table.action-item>
                                        <x-table.action-separator />
                                        <form action="{{ route('admin.mata-pelajaran.destroy', $mp->id) }}" method="POST" onsubmit="return confirm('Hapus mata pelajaran ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-table.action-item icon="trash" type="submit" class="text-red-600 hover:text-red-700 hover:bg-red-50">
                                                Hapus
                                            </x-table.action-item>
                                        </form>
                                    </x-table.action-column>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-ui.empty-state 
                    title="Belum Ada Mata Pelajaran" 
                    :description="'Belum ada data untuk kelompok ' . ($kelompokOptions[$kelompok] ?? $kelompok) . ' di kurikulum ' . $selectedKurikulum->nama"
                    icon="book-open"
                >
                    <x-slot:action>
                        <a href="{{ route('admin.mata-pelajaran.create', ['kurikulum_id' => $kurikulumId, 'kelompok' => $kelompok]) }}" 
                           class="btn btn-primary">
                            <x-ui.icon name="plus" size="18" />
                            <span>Tambah Mata Pelajaran</span>
                        </a>
                    </x-slot:action>
                </x-ui.empty-state>
            @endif
        </div>
    @else
        <x-ui.empty-state 
            title="Pilih Kurikulum" 
            description="Silakan pilih kurikulum terlebih dahulu untuk melihat daftar mata pelajaran."
            icon="layers"
        />
    @endif
</div>
@endsection
