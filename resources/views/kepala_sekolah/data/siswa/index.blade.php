@extends('layouts.app')

@section('page-header')
    <x-page-header title="Data Siswa (Monitoring)" subtitle="Statistik perilaku dan pelanggaran siswa" icon="users" />
@endsection

@section('content')
<div class="space-y-6">
    {{-- Statistics Overview --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="card p-4 flex items-center justify-between border-l-4 border-l-primary-500">
            <div>
                <p class="text-sm font-medium text-gray-500">Total Siswa Terdata</p>
                <p class="text-2xl font-bold text-gray-900">{{ $totalSiswa }}</p>
            </div>
            <div class="bg-primary-50 p-3 rounded-full">
                <x-ui.icon name="users" class="text-primary-600" size="24" />
            </div>
        </div>
        
        <div class="card p-4 flex items-center justify-between border-l-4 border-l-red-500">
            <div>
                <p class="text-sm font-medium text-red-600">Siswa Pernah Melanggar</p>
                <p class="text-2xl font-bold text-red-700">{{ $siswaBermasalah }}</p>
            </div>
            <div class="bg-red-50 p-3 rounded-full">
                <x-ui.icon name="alert-triangle" class="text-red-600" size="24" />
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card">
        <div class="card-body py-4">
            <form action="{{ route('kepala-sekolah.data.siswa') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                
                <div class="form-group mb-0 w-full md:w-auto flex-1 min-w-[200px]">
                    <label class="form-label text-xs">Cari Nama / NISN</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-ui.icon name="search" size="14" class="text-gray-400" />
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-input pl-10" placeholder="Cari siswa...">
                    </div>
                </div>

                <div class="form-group mb-0 w-full md:w-auto">
                    <label class="form-label text-xs">Jurusan</label>
                    <select name="jurusan_id" class="form-input form-select" onchange="this.form.submit()">
                        <option value="">Semua Jurusan</option>
                        @foreach($jurusanList as $jurusan)
                            <option value="{{ $jurusan->id }}" {{ request('jurusan_id') == $jurusan->id ? 'selected' : '' }}>
                                {{ $jurusan->nama_jurusan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group mb-0 w-full md:w-auto">
                    <label class="form-label text-xs">Kelas</label>
                    <select name="kelas_id" class="form-input form-select" onchange="this.form.submit()">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2 mb-2 ml-2">
                    <input type="checkbox" id="bermasalah_only" name="bermasalah_only" value="1" class="form-checkbox text-primary-500 rounded" {{ request('bermasalah_only') ? 'checked' : '' }} onchange="this.form.submit()">
                    <label for="bermasalah_only" class="text-sm font-medium text-gray-700 cursor-pointer select-none">Hanya yang bermasalah</label>
                </div>
                
                <div class="flex gap-2 mb-0.5">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    @if(request()->hasAny(['search', 'jurusan_id', 'kelas_id', 'bermasalah_only']))
                        <a href="{{ route('kepala-sekolah.data.siswa') }}" class="btn btn-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-hover w-full">
                    <thead>
                        <tr>
                            <th class="w-16">No</th>
                            <th>Identitas Siswa</th>
                            <th>Kelas & Jurusan</th>
                            <th>Statistik Pelanggaran</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siswa as $index => $item)
                        <tr>
                            <td>{{ $siswa->firstItem() + $index }}</td>
                            <td>
                                <div class="font-medium text-gray-900">{{ $item->nama_siswa }}</div>
                                <div class="text-xs text-gray-500 font-mono">{{ $item->nisn ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="text-sm font-medium">{{ $item->kelas->nama_kelas }}</div>
                                <div class="text-xs text-gray-500">{{ $item->kelas->jurusan->nama_jurusan ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="flex items-center gap-4">
                                    <div class="text-center">
                                        <div class="text-xs text-gray-500 uppercase">Poin</div>
                                        @php
                                            $color = 'success';
                                            if($item->total_poin > 100) $color = 'danger';
                                            elseif($item->total_poin > 50) $color = 'warning';
                                        @endphp
                                        <span class="badge badge-{{ $color }} font-bold text-sm px-2">
                                            {{ $item->total_poin }}
                                        </span>
                                    </div>
                                    <div class="h-8 w-px bg-gray-200"></div>
                                    <div class="text-center">
                                        <div class="text-xs text-gray-500 uppercase">Kasus</div>
                                        <span class="font-medium text-gray-900">{{ $item->jumlah_pelanggaran }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('kepala-sekolah.data.siswa.show', $item->id) }}" class="btn btn-sm btn-outline-primary">
                                    Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-500">
                                <div class="flex flex-col items-center">
                                    <x-ui.icon name="search" size="48" class="text-gray-300 mb-2" />
                                    <p class="font-medium">Tidak ada data siswa ditemukan.</p>
                                    @if(request()->anyFilled(['search', 'jurusan_id', 'kelas_id']))
                                        <p class="text-sm mt-1">Coba ubah filter pencarian Anda.</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100">
                {{ $siswa->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
