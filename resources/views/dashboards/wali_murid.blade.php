@extends('layouts.app')

@section('page-header', false)

@section('content')
<div class="space-y-6">
    {{-- Child Selector (if multiple children) --}}
    @if($semuaAnak->count() > 1)
        <div class="card">
            <div class="card-body">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-sm font-medium text-gray-600">Pilih Anak:</span>
                    @foreach($semuaAnak as $anak)
                        <a href="{{ route('dashboard.wali_murid', ['siswa_id' => $anak->id]) }}" 
                           class="btn {{ $siswa->id === $anak->id ? 'btn-primary' : 'btn-secondary' }}">
                            {{ $anak->nama_siswa }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    
    {{-- Student Info Card (Custom Banner) --}}
    <div class="relative rounded-2xl bg-gradient-to-r from-amber-500 to-orange-600 p-6 overflow-hidden text-white shadow-xl shadow-amber-900/10">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-yellow-300 opacity-10 rounded-full blur-2xl -ml-10 -mb-10 pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center gap-6">
            <div class="w-20 h-20 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-3xl font-bold border border-white/20 text-white shadow-inner">
                {{ strtoupper(substr($siswa->nama_siswa ?? 'S', 0, 1)) }}
            </div>
            
            <div class="flex-1">
                <h2 class="text-2xl font-bold">{{ $siswa->nama_siswa }}</h2>
                <p class="text-amber-50 mt-1 font-medium opacity-90">NISN: {{ $siswa->nisn }} • {{ $siswa->kelas->nama_kelas ?? '-' }}</p>
            </div>
            
            <div class="text-center bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/20 shadow-lg min-w-[120px]">
                <p class="text-4xl font-bold">{{ $totalPoin ?? 0 }}</p>
                <p class="text-sm text-amber-50 font-medium">Poin Kumulatif</p>
            </div>
        </div>
    </div>
    
    {{-- Pembinaan Alert (if active) --}}
    @if($pembinaanAktif)
        <x-ui.alert type="warning" title="Status Pembinaan Aktif" dismissible="false">
            Anak Anda saat ini dalam masa pembinaan: <strong>{{ $pembinaanAktif->rule->nama_rule ?? 'Pembinaan Internal' }}</strong>
            @if($pembinaanAktif->dibinaOleh)
                dibina oleh {{ $pembinaanAktif->dibinaOleh->username }}
            @endif
        </x-ui.alert>
    @endif
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Riwayat Pelanggaran --}}
        <div class="card h-full">
            <div class="card-header">
                <h3 class="card-title">Riwayat Pelanggaran</h3>
            </div>
            <div class="card-body p-0">
                @forelse($riwayat as $r)
                    <div class="flex items-start gap-4 p-4 border-b border-gray-100 last:border-b-0 hover:bg-gray-50 transition-colors">
                        <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                            <span class="font-bold text-sm">{{ $r->poin ?? 0 }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-800">{{ $r->jenisPelanggaran->nama_pelanggaran ?? 'Tidak diketahui' }}</p>
                            <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($r->tanggal_kejadian)->format('d M Y') }}</p>
                            @if($r->catatan)
                                <p class="text-sm text-gray-600 mt-1 italic">"{{ $r->catatan }}"</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <x-ui.empty-state 
                        icon="check-circle" 
                        title="Tidak Ada Pelanggaran" 
                        description="Anak Anda belum memiliki catatan pelanggaran."
                    />
                @endforelse
            </div>
        </div>
        
        {{-- Kasus / Tindak Lanjut --}}
        <div class="card h-full">
            <div class="card-header">
                <h3 class="card-title">Tindak Lanjut & Surat</h3>
            </div>
            <div class="card-body p-0">
                @forelse($kasus as $k)
                    <div class="flex items-start gap-4 p-4 border-b border-gray-100 last:border-b-0 hover:bg-gray-50 transition-colors">
                        <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                            <x-ui.icon name="file-text" size="18" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="font-medium text-gray-800">{{ $k->jenis_tindak_lanjut ?? 'Tindak Lanjut' }}</p>
                                @php
                                    $statusColors = [
                                        'Baru' => 'badge-info',
                                        'Menunggu Persetujuan' => 'badge-warning',
                                        'Disetujui' => 'badge-success',
                                        'Ditangani' => 'badge-primary',
                                        'Selesai' => 'badge-neutral',
                                    ];
                                    $statusValue = $k->status->value ?? $k->status;
                                @endphp
                                <span class="badge {{ $statusColors[$statusValue] ?? 'badge-neutral' }}">{{ $statusValue }}</span>
                            </div>
                            <p class="text-sm text-gray-500">{{ $k->created_at->format('d M Y') }}</p>
                            @if($k->catatan)
                                <p class="text-sm text-gray-600 mt-1 italic">"{{ $k->catatan }}"</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <x-ui.empty-state 
                        icon="inbox" 
                        title="Tidak Ada Tindak Lanjut" 
                        description="Belum ada surat panggilan atau tindak lanjut."
                    />
                @endforelse
            </div>
        </div>
    </div>
    
    {{-- Info Contact --}}
    <div class="card bg-slate-50 border-slate-200">
        <div class="card-body">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-white text-primary-600 flex items-center justify-center shrink-0 border border-slate-200 shadow-sm">
                    <x-ui.icon name="help-circle" size="24" />
                </div>
                <div>
                    <h3 class="font-semibold text-slate-800">Butuh Bantuan?</h3>
                    <p class="text-slate-600 text-sm mt-1">
                        Jika Anda memiliki pertanyaan mengenai kedisiplinan anak Anda, silakan hubungi Wali Kelas atau bagian Kesiswaan sekolah.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
