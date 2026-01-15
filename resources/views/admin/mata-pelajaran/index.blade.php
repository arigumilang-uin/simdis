@extends('layouts.app')

@section('title', 'Mata Pelajaran')

@section('page-header')
    <x-page-header 
        title="Mata Pelajaran" 
        subtitle="Kelola daftar mata pelajaran dan guru pengampunya"
        :total="$mataPelajaran->count()"
    />
@endsection

@section('content')
<div class="space-y-6">

    {{-- Filter Kurikulum (Tabs Style) --}}
    <div class="border-b border-slate-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            @foreach($kurikulums as $kur)
                <a href="{{ route('admin.mata-pelajaran.index', ['kurikulum_id' => $kur->id, 'kelompok' => $kelompok]) }}"
                   class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors
                          {{ $kurikulumId == $kur->id 
                             ? 'border-emerald-500 text-emerald-600' 
                             : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                    <x-ui.icon name="book" size="18" class="mr-2 {{ $kurikulumId == $kur->id ? 'text-emerald-500' : 'text-slate-400 group-hover:text-slate-500' }}" />
                    {{ $kur->nama }}
                </a>
            @endforeach
        </nav>
    </div>

    @if($selectedKurikulum)
        <div class="card">
            {{-- Toolbar: Filter Kelompok & Search & Action --}}
            <div class="card-header py-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    
                    {{-- Filter Kelompok (Pills) --}}
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach($kelompokOptions as $kode => $label)
                            @php
                                $isActive = $kelompok == $kode;
                                $shortLabel = explode(' - ', $label)[1] ?? $label; // Ambil "Umum", "Kejuruan", dll
                                $count = \App\Models\MataPelajaran::forKurikulum($kurikulumId)->forKelompok($kode)->count();
                            @endphp
                            <a href="{{ route('admin.mata-pelajaran.index', ['kurikulum_id' => $kurikulumId, 'kelompok' => $kode]) }}"
                               class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium transition-colors border
                                      {{ $isActive 
                                         ? 'bg-slate-800 text-white border-slate-800 shadow-sm' 
                                         : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
                                {{ $shortLabel }}
                                <span class="ml-2 inline-flex items-center justify-center px-1.5 min-w-[1.25rem] h-5 rounded-full text-xs
                                             {{ $isActive ? 'bg-slate-600 text-white' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $count }}
                                </span>
                            </a>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-3">
                        {{-- Search --}}
                        <form method="GET" class="relative">
                            <input type="hidden" name="kurikulum_id" value="{{ $kurikulumId }}">
                            <input type="hidden" name="kelompok" value="{{ $kelompok }}">
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                    <x-ui.icon name="search" size="16" />
                                </span>
                                <input type="text" 
                                       name="search" 
                                       value="{{ $search }}" 
                                       class="form-input pl-9 py-1.5 text-sm w-full md:w-64" 
                                       placeholder="Cari mapel...">
                            </div>
                        </form>

                        {{-- Add Button --}}
                        <a href="{{ route('admin.mata-pelajaran.create', ['kurikulum_id' => $kurikulumId, 'kelompok' => $kelompok]) }}" 
                           class="btn btn-primary btn-sm py-2">
                            <x-ui.icon name="plus" size="16" />
                            <span class="hidden sm:inline ml-1">Tambah Mapel</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            @if($mataPelajaran->count() > 0)
                <div class="table-container">
                    <table class="table">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="w-16 pl-6">No</th>
                                <th class="w-24">Kode</th>
                                <th>Mata Pelajaran</th>
                                <th>Guru Pengampu</th>
                                <th class="w-24 text-center">Status</th>
                                <th class="w-28 text-center pr-6">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($mataPelajaran as $index => $mp)
                                <tr class="hover:bg-slate-50 transition-colors group">
                                    <td class="pl-6 font-medium text-slate-500">{{ $index + 1 }}</td>
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
                                                @foreach($mp->guruPengampu->take(3) as $guru)
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs border bg-slate-50 text-slate-600 border-slate-200">
                                                        <span class="font-medium">{{ $guru->username }}</span>
                                                    </span>
                                                @endforeach
                                                @if($mp->guruPengampu->count() > 3)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs bg-slate-100 text-slate-500 border border-slate-200" title="Dan {{ $mp->guruPengampu->count() - 3 }} guru lainnya">
                                                        +{{ $mp->guruPengampu->count() - 3 }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-slate-400 text-sm flex items-center gap-1">
                                                <x-ui.icon name="alert-circle" size="14" />
                                                <span>Belum ada guru</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($mp->is_active)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center pr-6">
                                        <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('admin.mata-pelajaran.edit', $mp->id) }}" 
                                               class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" 
                                               title="Edit">
                                                <x-ui.icon name="edit-2" size="16" />
                                            </a>
                                            <form action="{{ route('admin.mata-pelajaran.destroy', $mp->id) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Hapus mata pelajaran ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" 
                                                        title="Hapus">
                                                    <x-ui.icon name="trash-2" size="16" />
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-12 flex flex-col items-center justify-center text-center p-6">
                    <div class="bg-slate-50 p-4 rounded-full mb-3">
                        <x-ui.icon name="book-open" size="32" class="text-slate-300" />
                    </div>
                    <h3 class="text-slate-900 font-medium mb-1">Belum Ada Mata Pelajaran</h3>
                    <p class="text-slate-500 text-sm max-w-sm mb-6">
                        Belum ada data mata pelajaran untuk kategori 
                        <span class="font-medium text-slate-700">{{ $kelompokOptions[$kelompok] ?? $kelompok }}</span> 
                        di kurikulum <span class="font-medium text-slate-700">{{ $selectedKurikulum->nama }}</span>.
                    </p>
                    <a href="{{ route('admin.mata-pelajaran.create', ['kurikulum_id' => $kurikulumId, 'kelompok' => $kelompok]) }}" 
                       class="btn btn-primary">
                        <x-ui.icon name="plus" size="18" />
                        <span>Tambah Mata Pelajaran</span>
                    </a>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
