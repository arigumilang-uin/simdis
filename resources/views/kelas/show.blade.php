@extends('layouts.app')

@section('title', 'Detail Kelas')
@section('subtitle', $kelas->nama_kelas ?? 'Detail Kelas')
@section('page-header', true)

@section('actions')
    <button type="button" onclick="history.back()" class="btn btn-secondary">
        <x-ui.icon name="chevron-left" size="18" />
        <span>Kembali</span>
    </button>
    @can('update', $kelas)
    <a href="{{ route('kelas.edit', $kelas->id) }}" class="btn btn-primary">
        <x-ui.icon name="edit" size="18" />
        <span>Edit Kelas</span>
    </a>
    @endcan
@endsection

@section('content')
<div class="space-y-6">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Jurusan --}}
        <div class="card p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                <x-ui.icon name="layers" size="24" />
            </div>
            <div>
                <p class="text-xs text-gray-400 font-bold uppercase">Jurusan</p>
                <p class="text-lg font-semibold text-gray-800">{{ $kelas->jurusan->nama_jurusan ?? '-' }}</p>
            </div>
        </div>
        
        {{-- Wali Kelas --}}
        <div class="card p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                <x-ui.icon name="users" size="24" />
            </div>
            <div>
                <p class="text-xs text-gray-400 font-bold uppercase">Wali Kelas</p>
                <p class="text-lg font-semibold text-gray-800">{{ $kelas->waliKelas->nama ?? $kelas->waliKelas->username ?? 'Belum ditentukan' }}</p>
            </div>
        </div>
        
        {{-- Total Siswa --}}
        <div class="card p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                <x-ui.icon name="users" size="24" />
            </div>
            <div>
                <p class="text-xs text-gray-400 font-bold uppercase">Total Siswa</p>
                <p class="text-lg font-semibold text-gray-800">{{ $kelas->siswa->count() }} Siswa</p>
            </div>
        </div>
    </div>
    
    {{-- Daftar Siswa --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Siswa</h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Nama Siswa</th>
                        <th>NISN</th>
                        <th>Wali Murid</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kelas->siswa as $index => $s)
                        <tr>
                            <td class="text-gray-500">{{ $index + 1 }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($s->nama_siswa ?? 'S', 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-gray-800">{{ $s->nama_siswa }}</span>
                                </div>
                            </td>
                            <td class="font-mono text-gray-600">{{ $s->nisn ?? '-' }}</td>
                            <td class="text-gray-500">{{ $s->waliMurid->nama ?? $s->waliMurid->username ?? '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('siswa.show', $s->id) }}" class="btn btn-icon btn-outline" title="Detail">
                                    <x-ui.icon name="eye" size="16" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-ui.empty-state 
                                    icon="users" 
                                    title="Belum Ada Siswa" 
                                    description="Kelas ini belum memiliki siswa terdaftar." 
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
