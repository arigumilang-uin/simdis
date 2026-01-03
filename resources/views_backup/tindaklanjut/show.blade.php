@extends('layouts.app')

@section('content')

{{-- Tailwind Config --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#0f172a',
                    accent: '#3b82f6',
                    success: '#10b981',
                    info: '#3b82f6',
                    warning: '#f59e0b',
                    danger: '#f43f5e',
                }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

@php
    // Status badge colors
    $statusColors = [
        'Baru' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-700', 'badge' => 'bg-blue-100 text-blue-700'],
        'Menunggu Persetujuan' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-700', 'badge' => 'bg-amber-100 text-amber-700'],
        'Disetujui' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-700', 'badge' => 'bg-emerald-100 text-emerald-700'],
        'Ditolak' => ['bg' => 'bg-rose-50', 'border' => 'border-rose-200', 'text' => 'text-rose-700', 'badge' => 'bg-rose-100 text-rose-700'],
        'Ditangani' => ['bg' => 'bg-indigo-50', 'border' => 'border-indigo-200', 'text' => 'text-indigo-700', 'badge' => 'bg-indigo-100 text-indigo-700'],
        'Selesai' => ['bg' => 'bg-slate-50', 'border' => 'border-slate-200', 'text' => 'text-slate-700', 'badge' => 'bg-slate-100 text-slate-700'],
    ];
    $sc = $statusColors[$tindakLanjut->status->value] ?? $statusColors['Baru'];
@endphp

<div class="page-wrap-custom min-h-screen p-6">
    <div class="max-w-5xl mx-auto">
        
        {{-- HEADER --}}
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-2 pb-3 border-b border-slate-200/60">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="text-[9px] font-black uppercase tracking-wider bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded border border-indigo-100/50">
                Detail Kasus
            </span>
            <span class="text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded border {{ $sc['badge'] }}">
                {{ $tindakLanjut->status->label() }}
            </span>
        </div>
        <h1 class="text-lg font-extrabold text-slate-800 m-0 tracking-tight flex items-center gap-2">
            <i class="fas fa-folder-open text-indigo-500 text-base"></i> Kasus #{{ $tindakLanjut->id }}
        </h1>
        <p class="text-slate-400 text-[10px] mt-0.5 font-medium flex items-center gap-1">
            <i class="far fa-calendar-alt"></i> Dibuat: {{ $tindakLanjut->created_at->format('d M Y, H:i') }}
        </p>
    </div>
    
    <div class="flex items-center gap-2 shrink-0">
        @if($tindakLanjut->status->isActive())
            <a href="{{ route('tindak-lanjut.edit', $tindakLanjut->id) }}" 
               class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-indigo-600 text-white text-[11px] font-bold hover:bg-indigo-700 hover:-translate-y-0.5 shadow-sm shadow-indigo-200 transition-all no-underline active:scale-95">
                <i class="fas fa-edit text-[10px]"></i> Kelola Kasus
            </a>
        @endif
        
        <a href="{{ route('tindak-lanjut.index') }}" 
           class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-white text-slate-600 text-[11px] font-bold border border-slate-200 hover:bg-slate-50 hover:text-slate-900 hover:border-slate-300 transition-all no-underline shadow-sm active:scale-95">
            <i class="fas fa-arrow-left text-[10px]"></i> Kembali
        </a>
    </div>
</div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- LEFT COLUMN - Main Info --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- STUDENT INFO --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-sm font-bold text-slate-700 m-0 flex items-center gap-2">
                            <i class="fas fa-user-graduate text-indigo-500"></i> Informasi Siswa
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 text-white flex items-center justify-center font-black text-2xl shadow-lg">
                                {{ strtoupper(substr($tindakLanjut->siswa->nama_siswa ?? 'X', 0, 1)) }}
                            </div>
                            <div class="flex-1">
                                <a href="{{ route('siswa.show', $tindakLanjut->siswa_id) }}" class="text-lg font-bold text-slate-800 hover:text-indigo-600 no-underline">
                                    {{ $tindakLanjut->siswa->nama_siswa ?? '-' }}
                                </a>
                                <div class="grid grid-cols-2 gap-4 mt-3 text-sm">
                                    <div>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase block">NISN</span>
                                        <span class="font-mono text-slate-700">{{ $tindakLanjut->siswa->nisn ?? '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase block">Kelas</span>
                                        <span class="text-slate-700 font-bold">{{ $tindakLanjut->siswa->kelas->nama_kelas ?? '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase block">Jurusan</span>
                                        <span class="text-slate-700">{{ $tindakLanjut->siswa->kelas->jurusan->nama_jurusan ?? '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase block">Wali Kelas</span>
                                        <span class="text-slate-700">{{ $tindakLanjut->siswa->kelas->waliKelas->username ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CASE DETAILS --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-sm font-bold text-slate-700 m-0 flex items-center gap-2">
                            <i class="fas fa-exclamation-circle text-rose-500"></i> Detail Kasus
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Sanksi yang Dijatuhkan</span>
                            <p class="text-slate-700 leading-relaxed m-0">{{ $tindakLanjut->sanksi_deskripsi ?? '-' }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Tanggal Tindak Lanjut</span>
                                <span class="text-slate-700 font-bold">{{ $tindakLanjut->tanggal_tindak_lanjut ? \Carbon\Carbon::parse($tindakLanjut->tanggal_tindak_lanjut)->format('d M Y') : '-' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Penyetuju</span>
                                <span class="text-slate-700">{{ $tindakLanjut->penyetuju->username ?? '-' }}</span>
                            </div>
                        </div>
                        @if($tindakLanjut->catatan)
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Catatan</span>
                            <p class="text-slate-600 italic leading-relaxed m-0 bg-slate-50 p-3 rounded-lg">{{ $tindakLanjut->catatan }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- SUMMONS LETTER --}}
                @if($tindakLanjut->suratPanggilan)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                        <h3 class="text-sm font-bold text-slate-700 m-0 flex items-center gap-2">
                            <i class="fas fa-envelope text-amber-500"></i> Surat Panggilan
                        </h3>
                        @if($tindakLanjut->status->isActive())
                        <a href="{{ route('tindak-lanjut.preview-surat', $tindakLanjut->id) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 no-underline">
                            <i class="fas fa-eye mr-1"></i> Preview
                        </a>
                        @endif
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase block">No. Surat</span>
                                <span class="font-mono text-slate-700">{{ $tindakLanjut->suratPanggilan->nomor_surat ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase block">Tanggal Surat</span>
                                <span class="text-slate-700">{{ $tindakLanjut->suratPanggilan->tanggal_surat ? \Carbon\Carbon::parse($tindakLanjut->suratPanggilan->tanggal_surat)->format('d M Y') : '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- RIGHT COLUMN - Status & Timeline --}}
            <div class="space-y-6">
                {{-- STATUS BOX --}}
                
                {{-- TIMELINE --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-sm font-bold text-slate-700 m-0 flex items-center gap-2">
                            <i class="fas fa-history text-slate-400"></i> Riwayat Kasus
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="relative space-y-4">
                            <div class="absolute left-[11px] top-3 bottom-3 w-0.5 bg-slate-100"></div>
                            
                            {{-- Created --}}
                            <div class="relative flex items-start gap-4">
                                <div class="w-6 h-6 rounded-full bg-indigo-500 text-white flex items-center justify-center text-[10px] z-10">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div class="flex-1">
                                    <span class="text-xs font-bold text-slate-700 block">Kasus Dibuat</span>
                                    <span class="text-[10px] text-slate-400">{{ $tindakLanjut->created_at->format('d M Y, H:i') }}</span>
                                </div>
                            </div>

                            {{-- Approval Status --}}
                            @if(in_array($tindakLanjut->status->value, ['Disetujui', 'Ditangani', 'Selesai']))
                            <div class="relative flex items-start gap-4">
                                <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] z-10">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="flex-1">
                                    <span class="text-xs font-bold text-slate-700 block">Disetujui</span>
                                    <span class="text-[10px] text-slate-400">Oleh {{ $tindakLanjut->penyetuju->username ?? '-' }}</span>
                                </div>
                            </div>
                            @elseif($tindakLanjut->status->value === 'Ditolak')
                            <div class="relative flex items-start gap-4">
                                <div class="w-6 h-6 rounded-full bg-rose-500 text-white flex items-center justify-center text-[10px] z-10">
                                    <i class="fas fa-times"></i>
                                </div>
                                <div class="flex-1">
                                    <span class="text-xs font-bold text-slate-700 block">Ditolak</span>
                                    <span class="text-[10px] text-slate-400">Oleh {{ $tindakLanjut->penyetuju->username ?? '-' }}</span>
                                </div>
                            </div>
                            @elseif($tindakLanjut->status->value === 'Menunggu Persetujuan')
                            <div class="relative flex items-start gap-4">
                                <div class="w-6 h-6 rounded-full bg-amber-500 text-white flex items-center justify-center text-[10px] z-10 animate-pulse">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="flex-1">
                                    <span class="text-xs font-bold text-slate-700 block">Menunggu Persetujuan</span>
                                    <span class="text-[10px] text-slate-400">Menunggu Kepala Sekolah</span>
                                </div>
                            </div>
                            @endif

                            {{-- Handled --}}
                            @if(in_array($tindakLanjut->status->value, ['Ditangani', 'Selesai']))
                            <div class="relative flex items-start gap-4">
                                <div class="w-6 h-6 rounded-full bg-indigo-500 text-white flex items-center justify-center text-[10px] z-10">
                                    <i class="fas fa-cog"></i>
                                </div>
                                <div class="flex-1">
                                    <span class="text-xs font-bold text-slate-700 block">Ditangani</span>
                                    <span class="text-[10px] text-slate-400">Proses penanganan berjalan</span>
                                </div>
                            </div>
                            @endif

                            {{-- Completed --}}
                            @if($tindakLanjut->status->value === 'Selesai')
                            <div class="relative flex items-start gap-4">
                                <div class="w-6 h-6 rounded-full bg-slate-500 text-white flex items-center justify-center text-[10px] z-10">
                                    <i class="fas fa-flag-checkered"></i>
                                </div>
                                <div class="flex-1">
                                    <span class="text-xs font-bold text-slate-700 block">Selesai</span>
                                    <span class="text-[10px] text-slate-400">{{ $tindakLanjut->updated_at->format('d M Y, H:i') }}</span>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- QUICK ACTIONS --}}
                @if($tindakLanjut->status->isActive())
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-sm font-bold text-slate-700 m-0">Aksi Cepat</h3>
                    </div>
                    <div class="p-4 space-y-2">
                        <a href="{{ route('tindak-lanjut.edit', $tindakLanjut->id) }}" class="block w-full px-4 py-3 rounded-xl bg-indigo-50 text-indigo-700 text-sm font-bold text-center hover:bg-indigo-100 transition-all no-underline">
                            <i class="fas fa-edit mr-2"></i> Kelola Kasus
                        </a>
                        @if($tindakLanjut->suratPanggilan)
                        <a href="{{ route('tindak-lanjut.cetak-surat', $tindakLanjut->id) }}" class="block w-full px-4 py-3 rounded-xl bg-amber-50 text-amber-700 text-sm font-bold text-center hover:bg-amber-100 transition-all no-underline">
                            <i class="fas fa-print mr-2"></i> Cetak Surat
                        </a>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .page-wrap-custom { background: #f8fafc; font-family: 'Inter', sans-serif; }
</style>
@endsection
