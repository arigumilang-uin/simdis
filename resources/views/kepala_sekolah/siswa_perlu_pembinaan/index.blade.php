@extends('layouts.app')

@section('page-header')
    <x-page-header title="Siswa Perlu Pembinaan" subtitle="Daftar siswa yang mencapai threshold poin pelanggaran" icon="alert-triangle">
        <div class="flex gap-2">
            <a href="{{ route('kepala-sekolah.siswa-perlu-pembinaan.export-csv', request()->all()) }}" class="btn btn-outline-success">
                <x-ui.icon name="file-text" size="18" class="mr-2" />
                Export CSV
            </a>
        </div>
    </x-page-header>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="card p-4 flex items-center justify-between bg-red-50 border-red-100">
            <div>
                <p class="text-sm font-medium text-red-600">Total Siswa</p>
                <p class="text-2xl font-bold text-red-700">{{ $stats['total_siswa'] }}</p>
            </div>
            <div class="bg-red-100 p-2 rounded-full">
                <x-ui.icon name="users" class="text-red-600" />
            </div>
        </div>
        
        @foreach($stats['by_range'] as $stat)
            @if($stat['count'] > 0)
            <div class="card p-4 border-l-4 border-l-primary-500">
                <p class="text-xs text-gray-500 font-medium truncate" title="{{ $stat['rule']->keterangan }}">{{ $stat['rule']->range_text }}</p>
                <p class="text-lg font-bold text-gray-800">{{ $stat['count'] }} Siswa</p>
                <p class="text-xs text-primary-600 truncate">{{ $stat['rule']->keterangan }}</p>
            </div>
            @endif
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="card">
        <div class="card-body py-4">
            <form action="{{ route('kepala-sekolah.siswa-perlu-pembinaan.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="form-group mb-0 w-full md:w-auto">
                    <label class="form-label text-xs">Range Poin</label>
                    <select name="rule_id" class="form-input form-select form-input-sm" onchange="this.form.submit()">
                        <option value="">Semua Range</option>
                        @foreach($rules as $rule)
                            <option value="{{ $rule->id }}" {{ $ruleId == $rule->id ? 'selected' : '' }}>
                                {{ $rule->range_text }} ({{ $rule->keterangan }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group mb-0 w-full md:w-auto">
                    <label class="form-label text-xs">Jurusan</label>
                    <select name="jurusan_id" class="form-input form-select form-input-sm" onchange="this.form.submit()">
                        <option value="">Semua Jurusan</option>
                        @foreach($jurusanList as $jurusan)
                            <option value="{{ $jurusan->id }}" {{ $jurusanId == $jurusan->id ? 'selected' : '' }}>
                                {{ $jurusan->nama_jurusan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group mb-0 w-full md:w-auto">
                    <label class="form-label text-xs">Kelas</label>
                    <select name="kelas_id" class="form-input form-select form-input-sm" onchange="this.form.submit()">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" {{ $kelasId == $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                @if($ruleId || $kelasId || $jurusanId)
                    <a href="{{ route('kepala-sekolah.siswa-perlu-pembinaan.index') }}" class="btn btn-secondary btn-sm mb-0.5">Reset</a>
                @endif
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
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Total Poin</th>
                            <th>Kategori</th>
                            <th>Rekomendasi Pembina</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siswaList as $index => $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="font-medium text-gray-900">{{ $item['siswa']->nama_siswa }}</div>
                                <div class="text-xs text-gray-500">{{ $item['siswa']->nisn ?? $item['siswa']->nis }}</div>
                            </td>
                            <td>
                                <div class="text-sm">{{ $item['siswa']->kelas->nama_kelas }}</div>
                                <div class="text-xs text-gray-500">{{ $item['siswa']->kelas->jurusan->kode_jurusan ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-danger text-sm px-2 py-1">{{ $item['total_poin'] }}</span>
                            </td>
                            <td>
                                <span class="text-sm font-medium block">{{ $item['rekomendasi']['range_text'] }}</span>
                                <span class="text-xs text-gray-500">{{ $item['rekomendasi']['keterangan'] }}</span>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($item['rekomendasi']['pembina_roles'] as $role)
                                        <span class="badge badge-info text-xs">{{ $role }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('siswa.show', $item['siswa']->id) }}" class="btn btn-sm btn-light" title="Lihat Profil">
                                    <x-ui.icon name="user" size="14" />
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-500">
                                <div class="flex flex-col items-center">
                                    <x-ui.icon name="check-circle" size="48" class="text-green-500 mb-2 opacity-50" />
                                    <p class="font-medium">Tidak ada siswa yang memerlukan pembinaan.</p>
                                    @if($ruleId || $kelasId)
                                        <p class="text-sm mt-1">Coba reset filter untuk melihat data lainnya.</p>
                                    @endif
                                </div>
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
