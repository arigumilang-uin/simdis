@extends('layouts.app')

@section('title', 'Detail Konsentrasi')

@section('page-header')
    <x-page-header 
        title="Detail Konsentrasi" 
        :subtitle="$konsentrasi->nama_konsentrasi"
    />
@endsection

@section('content')
{{-- Action Buttons --}}
<div class="flex flex-wrap justify-end gap-2 mb-6">
    <a href="{{ route('konsentrasi.edit', $konsentrasi->id) }}" class="btn btn-primary">
        <x-ui.icon name="edit" size="18" />
        <span>Edit</span>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Info Card --}}
    <div class="lg:col-span-1">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi Konsentrasi</h3>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Kode</p>
                    <p class="font-mono text-lg font-bold">{{ $konsentrasi->kode_konsentrasi ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Nama Konsentrasi</p>
                    <p class="font-medium text-gray-800">{{ $konsentrasi->nama_konsentrasi }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Jurusan (Program Keahlian)</p>
                    <p class="font-medium text-blue-600">{{ $konsentrasi->jurusan->nama_jurusan ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Kaprodi</p>
                    <p class="font-medium text-gray-800">{{ $konsentrasi->jurusan->kaprodi->username ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Status</p>
                    @if($konsentrasi->is_active)
                        <span class="badge badge-success">Aktif</span>
                    @else
                        <span class="badge badge-secondary">Nonaktif</span>
                    @endif
                </div>
                @if($konsentrasi->deskripsi)
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Deskripsi</p>
                    <p class="text-sm text-gray-600">{{ $konsentrasi->deskripsi }}</p>
                </div>
                @endif
            </div>
        </div>
        
        {{-- Statistics --}}
        <div class="card mt-6">
            <div class="card-body">
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-4 bg-blue-50 rounded-xl">
                        <p class="text-3xl font-bold text-blue-600">{{ $konsentrasi->kelas->count() }}</p>
                        <p class="text-xs text-blue-600 uppercase tracking-wider">Kelas</p>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-xl">
                        <p class="text-3xl font-bold text-green-600">{{ $konsentrasi->kelas->sum(fn($k) => $k->siswa->count()) }}</p>
                        <p class="text-xs text-green-600 uppercase tracking-wider">Siswa</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Kelas List --}}
    <div class="lg:col-span-2">
        <div class="card">
            <div class="card-header flex justify-between items-center">
                <h3 class="card-title">Daftar Kelas</h3>
            </div>
            <div class="card-body p-0">
                @if($konsentrasi->kelas->count() > 0)
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tingkat</th>
                                <th>Nama Kelas</th>
                                <th>Wali Kelas</th>
                                <th class="text-center">Siswa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($konsentrasi->kelas->sortBy('tingkat') as $kelas)
                                <tr>
                                    <td>
                                        <span class="badge {{ $kelas->tingkat == 'X' ? 'badge-secondary' : ($kelas->tingkat == 'XI' ? 'badge-primary' : 'badge-success') }}">
                                            {{ $kelas->tingkat }}
                                        </span>
                                    </td>
                                    <td class="font-medium text-gray-800">{{ $kelas->nama_kelas }}</td>
                                    <td class="text-gray-500">{{ $kelas->waliKelas->username ?? '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-primary">{{ $kelas->siswa->count() }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="px-6 pb-6">
                        <x-ui.empty-state 
                            icon="layers" 
                            title="Belum Ada Kelas" 
                            description="Konsentrasi ini belum memiliki kelas terdaftar." 
                        />
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="mt-6">
    <button type="button" onclick="history.back()" class="btn btn-secondary">
        <x-ui.icon name="chevron-left" size="18" />
        Kembali
    </button>
</div>
@endsection
