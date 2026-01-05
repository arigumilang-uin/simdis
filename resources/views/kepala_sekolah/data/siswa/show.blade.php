@extends('layouts.app')

@section('page-header')
    <x-page-header title="Detail Statistik Siswa" subtitle="Monitoring perilaku siswa secara detail" icon="user" back-url="{{ route('kepala-sekolah.data.siswa') }}" />
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Left Column: Profile --}}
    <div class="space-y-6">
        <div class="card text-center p-6">
            <div class="w-24 h-24 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="text-3xl font-bold text-primary-600">{{ strtoupper(substr($siswa->nama_siswa, 0, 2)) }}</span>
            </div>
            <h3 class="text-xl font-bold text-gray-900">{{ $siswa->nama_siswa }}</h3>
            <p class="text-gray-500 font-mono text-sm mb-4">{{ $siswa->nisn ?? 'No NISN' }}</p>
            
            <div class="flex justify-center gap-2 mb-6">
                <span class="badge badge-primary">{{ $siswa->kelas->nama_kelas }}</span>
                <span class="badge badge-secondary">{{ $siswa->kelas->jurusan->kode_jurusan ?? 'UMUM' }}</span>
            </div>

            <div class="border-t border-gray-100 pt-4 text-left space-y-3">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-500">Wali Murid</span>
                    <span class="font-medium text-gray-900">{{ $siswa->waliMurid->nama_wali ?? '-' }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-500">Telepon</span>
                    <span class="font-medium text-gray-900">{{ $siswa->nomor_hp_wali_murid ?? '-' }}</span>
                </div>
            </div>
        </div>

        {{-- Statistics Card --}}
        <div class="card p-4">
            <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Ringkasan Poin</h4>
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-600">Total Akumulasi</span>
                @php
                    $color = 'success';
                    if($totalPoin > 100) $color = 'danger';
                    elseif($totalPoin > 50) $color = 'warning';
                @endphp
                <span class="text-2xl font-bold text-{{ $color == 'success' ? 'green' : ($color == 'warning' ? 'yellow' : 'red') }}-600">
                    {{ $totalPoin }}
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-{{ $color == 'success' ? 'green' : ($color == 'warning' ? 'yellow' : 'red') }}-500 h-2.5 rounded-full" 
                     style="width: {{ min(($totalPoin / 200) * 100, 100) }}%"></div>
            </div>
            <p class="text-xs text-gray-400 mt-2 text-right">Threshold maksimal: 200 (Surat 3)</p>
        </div>
    </div>

    {{-- Right Column: History --}}
    <div class="lg:col-span-2 space-y-6">
        
        {{-- Active Cases --}}
        @if($kasus->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani'])->count() > 0)
        <div class="card border-l-4 border-l-warning-500">
            <div class="card-header bg-warning-50">
                <h3 class="card-title text-warning-800 flex items-center gap-2">
                    <x-ui.icon name="alert-circle" size="18" />
                    Sedang Dalam Penanganan
                </h3>
            </div>
            <div class="card-body p-0">
                @foreach($kasus->whereIn('status', ['Baru', 'Menunggu Persetujuan', 'Disetujui', 'Ditangani']) as $item)
                    <div class="p-4 border-b border-gray-100 last:border-0">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-bold text-gray-800">{{ $item->pemicu }}</p>
                                <p class="text-sm text-gray-600 mt-1">{{ $item->sanksi_deskripsi }}</p>
                                <div class="text-xs text-gray-400 mt-2 flex items-center gap-2">
                                    <x-ui.icon name="clock" size="12" />
                                    {{ $item->created_at->diffForHumans() }}
                                </div>
                            </div>
                            <span class="badge badge-warning">{{ $item->status }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Violation History --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Riwayat Pelanggaran</h3>
            </div>
            <div class="card-body p-0">
                @if($riwayat->isEmpty())
                    <div class="p-8 text-center text-gray-500">
                        <x-ui.icon name="check-circle" size="32" class="text-green-400 mx-auto mb-2" />
                        <p>Siswa ini belum memiliki catatan pelanggaran.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Pelanggaran</th>
                                    <th>Poin</th>
                                    <th>Pencatat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($riwayat as $item)
                                    <tr>
                                        <td class="whitespace-nowrap">{{ $item->tanggal_kejadian->format('d/m/Y') }}</td>
                                        <td>
                                            <div class="font-medium text-gray-900">{{ $item->jenisPelanggaran->nama_pelanggaran }}</div>
                                            <div class="text-xs text-gray-500">Kategori: {{ $item->jenisPelanggaran->kategori }}</div>
                                        </td>
                                        <td>
                                            <span class="badge badge-danger">+{{ $item->jenisPelanggaran->poin }}</span>
                                        </td>
                                        <td class="text-sm text-gray-600">{{ $item->user->nama ?? $item->user->username ?? 'System' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
