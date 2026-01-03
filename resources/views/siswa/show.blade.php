@extends('layouts.app')

@section('title', 'Detail Siswa')
@section('subtitle', $siswa->nama_siswa)
@section('page-header', true)

@section('actions')
    <button type="button" onclick="history.back()" class="btn btn-secondary">
        <x-ui.icon name="chevron-left" size="18" />
        <span>Kembali</span>
    </button>
    @can('update', $siswa)
    <a href="{{ route('siswa.edit', $siswa->id) }}" class="btn btn-primary">
        <x-ui.icon name="edit" size="18" />
        <span>Edit</span>
    </a>
    @endcan
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Profile Card --}}
    <div class="lg:col-span-1 space-y-6">
        <div class="card">
            <div class="card-body text-center">
                <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white text-3xl font-bold shadow-lg shadow-blue-500/30">
                    {{ strtoupper(substr($siswa->nama_siswa, 0, 1)) }}
                </div>
                
                <h3 class="text-xl font-bold text-gray-800 mt-4">{{ $siswa->nama_siswa }}</h3>
                <p class="text-gray-500 font-mono">NISN: {{ $siswa->nisn }}</p>
                
                <div class="mt-4">
                    <span class="badge badge-primary text-sm">{{ $siswa->kelas->nama_kelas ?? '-' }}</span>
                    @if($siswa->kelas?->jurusan)
                        <span class="badge badge-neutral text-sm ml-1">{{ $siswa->kelas->jurusan->nama_jurusan }}</span>
                    @endif
                </div>
                
                {{-- Poin Display --}}
                <div class="mt-6 pt-6 border-t border-gray-100">
                    <div class="text-center">
                        @php
                            $poinClass = $totalPoin > 50 ? 'text-red-500' : ($totalPoin > 20 ? 'text-amber-500' : 'text-emerald-500');
                        @endphp
                        <p class="text-4xl font-bold {{ $poinClass }}">{{ $totalPoin ?? 0 }}</p>
                        <p class="text-sm text-gray-500 mt-1">Poin Kumulatif</p>
                    </div>
                </div>
                
                {{-- Pembinaan Status --}}
                @if($pembinaanAktif ?? false)
                    <div class="mt-4 p-4 bg-amber-50 rounded-xl border border-amber-100">
                        <div class="flex items-center gap-2 justify-center">
                            <x-ui.icon name="alert-triangle" class="text-amber-600" size="16" />
                            <p class="text-sm font-semibold text-amber-800">Dalam Pembinaan</p>
                        </div>
                        <p class="text-xs text-amber-600 mt-1">{{ $pembinaanAktif->rule->nama_rule ?? 'Pembinaan Internal' }}</p>
                    </div>
                @elseif(!empty($pembinaanRekomendasi) && is_array($pembinaanRekomendasi) && !empty($pembinaanRekomendasi['nama_rule'] ?? $pembinaanRekomendasi['pembina_roles'] ?? null))
                    <div class="mt-4 p-4 bg-red-50 rounded-xl border border-red-100">
                        <p class="text-sm font-semibold text-red-800">Rekomendasi Pembinaan</p>
                        <p class="text-xs text-red-600 mt-1">
                            {{ $pembinaanRekomendasi['nama_rule'] ?? 'Pembinaan diperlukan' }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
        
        {{-- Contact Info --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Kontak Wali Murid</h3>
            </div>
            <div class="card-body space-y-4">
                @if($siswa->nomor_hp_wali_murid)
                    <a href="https://wa.me/62{{ ltrim($siswa->nomor_hp_wali_murid, '0') }}" target="_blank" 
                       class="flex items-center gap-3 p-3 bg-emerald-50 rounded-xl hover:bg-emerald-100 transition-colors">
                        <div class="w-10 h-10 rounded-lg bg-emerald-500 text-white flex items-center justify-center">
                            <x-ui.icon name="brand-whatsapp" size="18" class="fill-current" strokeWidth="0" />
                        </div>
                        <div>
                            <p class="font-medium text-emerald-800">{{ $siswa->nomor_hp_wali_murid }}</p>
                            <p class="text-xs text-emerald-600">Klik untuk WhatsApp</p>
                        </div>
                    </a>
                @else
                    <div class="text-center py-4">
                        <x-ui.icon name="slash" size="32" class="mx-auto text-gray-300 mb-2" />
                        <p class="text-gray-400 text-sm">Tidak ada kontak wali</p>
                    </div>
                @endif
                
                @if($siswa->waliMurid)
                    <div class="p-3 bg-blue-50 rounded-xl border border-blue-100">
                        <p class="text-xs text-blue-600 mb-1">Akun Wali Murid</p>
                        <p class="font-medium text-blue-800">{{ $siswa->waliMurid->username }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    {{-- Detail Info & History --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Basic Info --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi Siswa</h3>
            </div>
            <div class="card-body">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 bg-gray-50 rounded-xl">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">NISN</dt>
                        <dd class="mt-1 font-semibold text-gray-800 font-mono text-lg">{{ $siswa->nisn }}</dd>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Kelas</dt>
                        <dd class="mt-1 font-semibold text-gray-800 text-lg">{{ $siswa->kelas->nama_kelas ?? '-' }}</dd>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Jurusan</dt>
                        <dd class="mt-1 font-semibold text-gray-800">{{ $siswa->kelas->jurusan->nama_jurusan ?? '-' }}</dd>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Wali Kelas</dt>
                        <dd class="mt-1 font-semibold text-gray-800">{{ $siswa->kelas->waliKelas->username ?? '-' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
        
        {{-- Riwayat Pelanggaran --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Riwayat Pelanggaran</h3>
                @can('create', App\Models\RiwayatPelanggaran::class)
                <a href="{{ route('riwayat.create', ['siswa_id' => $siswa->id]) }}" class="btn btn-sm btn-primary">
                    + Catat Pelanggaran
                </a>
                @endcan
            </div>
            <div class="table-container !rounded-none !border-0">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Pelanggaran</th>
                            <th class="text-center">Poin</th>
                            <th>Dicatat Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siswa->riwayatPelanggaran ?? [] as $riwayat)
                            <tr>
                                <td class="text-gray-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($riwayat->tanggal_kejadian)->format('d M Y') }}</td>
                                <td>
                                    <p class="font-medium text-gray-800">{{ $riwayat->jenisPelanggaran->nama_pelanggaran ?? '-' }}</p>
                                    @if($riwayat->keterangan)
                                        <p class="text-sm text-gray-500">{{ Str::limit($riwayat->keterangan, 50) }}</p>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-danger">{{ $riwayat->jenisPelanggaran->poin ?? 0 }}</span>
                                </td>
                                <td class="text-gray-500 text-sm">{{ $riwayat->pencatat->username ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-ui.empty-state 
                                        icon="check-circle" 
                                        title="" 
                                        description="Tidak ada riwayat pelanggaran 👍" 
                                        class="py-8"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        {{-- Kasus Tindak Lanjut --}}
        @if($siswa->tindakLanjut->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Kasus Tindak Lanjut</h3>
            </div>
            <div class="card-body p-0">
                @foreach($siswa->tindakLanjut as $kasus)
                    <div class="flex items-center gap-4 p-4 border-b border-gray-100 last:border-b-0">
                        <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                            <x-ui.icon name="file-text" size="18" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-800">{{ $kasus->jenis_tindak_lanjut ?? 'Tindak Lanjut' }}</p>
                            <p class="text-sm text-gray-500">{{ $kasus->created_at->format('d M Y') }}</p>
                        </div>
                        <span class="badge badge-{{ $kasus->status->color() }}">{{ $kasus->status->value }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
