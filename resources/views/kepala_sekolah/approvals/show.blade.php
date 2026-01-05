@extends('layouts.app')

@section('page-header')
    <x-page-header title="Detail Persetujuan" subtitle="Tinjau detail pelanggaran sebelum memberikan keputusan" icon="check-square" back-url="{{ route('kepala-sekolah.approvals.index') }}" />
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Info --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Siswa Info --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi Siswa</h3>
            </div>
            <div class="card-body">
                <div class="flex items-start gap-4">
                    <div class="bg-primary-100 rounded-full p-3">
                        <x-ui.icon name="user" size="24" class="text-primary-600" />
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-gray-900">{{ $kasus->siswa->nama_siswa }}</h4>
                        <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1 text-sm text-gray-500">
                            <span>NISN: <span class="font-medium text-gray-700">{{ $kasus->siswa->nisn }}</span></span>
                            <span>•</span>
                            <span>Kelas: <span class="font-medium text-gray-700">{{ $kasus->siswa->kelas->nama_kelas }}</span></span>
                            <span>•</span>
                            <span>Jurusan: <span class="font-medium text-gray-700">{{ $kasus->siswa->kelas->jurusan->nama_jurusan }}</span></span>
                        </div>
                    </div>
                </div>
                
                <hr class="my-4 border-gray-100">
                
                <h5 class="text-sm font-semibold text-gray-900 mb-2">Wali Murid</h5>
                <p class="text-sm text-gray-600">{{ $kasus->siswa->waliMurid->nama_wali ?? 'Tidak ada data' }} ({{ $kasus->siswa->waliMurid->nomor_hp ?? '-' }})</p>
            </div>
        </div>

        {{-- Violation Info --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Kasus</h3>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pemicu / Pelanggaran</span>
                    <p class="text-base font-medium text-gray-900 bg-gray-50 p-3 rounded-lg border border-gray-100 mt-1">
                        {{ $kasus->pemicu }}
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Status Saat Ini</span>
                        <div class="mt-1">
                            <span class="badge badge-warning">{{ $kasus->status }}</span>
                        </div>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal Kasus</span>
                        <p class="text-sm font-medium text-gray-900 mt-1">{{ \Carbon\Carbon::parse($kasus->created_at)->format('d F Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Decision Panel --}}
    <div class="space-y-6">
        <div class="card">
            <div class="card-header bg-gray-50">
                <h3 class="card-title text-gray-800">Tindakan & Sanksi</h3>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">Bentuk Sanksi</span>
                    <p class="text-sm text-gray-800 bg-red-50 p-2 rounded border border-red-100">{{ $kasus->sanksi_deskripsi }}</p>
                </div>
                
                @if($kasus->suratPanggilan)
                    <div class="mb-4">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block mb-1">Surat Panggilan</span>
                        <div class="flex items-center justify-between bg-blue-50 p-2 rounded border border-blue-100">
                            <div>
                                <p class="text-sm font-medium text-blue-900">{{ $kasus->suratPanggilan->tipe_surat }}</p>
                                <p class="text-xs text-blue-700">{{ \Carbon\Carbon::parse($kasus->suratPanggilan->tanggal_surat)->format('d/m/Y') }}</p>
                            </div>
                            <x-ui.icon name="file-text" class="text-blue-400" />
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Approval Form --}}
        <div class="card border-t-4 border-t-primary-500">
            <div class="card-header">
                <h3 class="card-title">Keputusan Kepala Sekolah</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('kepala-sekolah.approvals.process', $kasus->id) }}" method="POST">
                    @csrf
                    
                    <div class="form-group">
                        <label class="form-label">Catatan / Arahan (Opsional)</label>
                        <textarea name="catatan_kepala_sekolah" rows="4" class="form-input" placeholder="Tuliskan catatan atau arahan khusus..."></textarea>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3 mt-6">
                        <button type="submit" name="action" value="reject" class="btn btn-outline-danger w-full justify-center" onclick="return confirm('Apakah Anda yakin ingin menolak kasus ini?')">
                            <x-ui.icon name="x" size="18" class="mr-2" />
                            Tolak
                        </button>
                        
                        <button type="submit" name="action" value="approve" class="btn btn-primary w-full justify-center" onclick="return confirm('Apakah Anda yakin menyetujui penanganan kasus ini?')">
                            <x-ui.icon name="check" size="18" class="mr-2" />
                            Setujui
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
