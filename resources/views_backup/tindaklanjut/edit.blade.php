@extends('layouts.app')

@section('content')

{{-- 1. TAILWIND CONFIG - Samakan persis dengan halaman Input Pelanggaran --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#0f172a',
                    indigo: { 600: '#4f46e5', 50: '#eef2ff', 100: '#e0e7ff', 700: '#4338ca' },
                    emerald: { 50: '#ecfdf5', 100: '#d1fae5', 600: '#059669', 700: '#047857' },
                    rose: { 50: '#fff1f2', 100: '#ffe4e6', 600: '#e11d48', 700: '#be123c' }
                }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

<style>
    .page-wrap-custom { font-family: 'Inter', sans-serif; }
    .btn-clean-action {
        padding: 0.5rem 1rem; border-radius: 0.75rem; background: #fff; color: #475569; 
        font-size: 0.75rem; font-weight: 700; border: 1px solid #e2e8f0; transition: 0.2s;
    }
    .btn-clean-action:hover { background: #f1f5f9; color: #0f172a; }
</style>

<div class="page-wrap-custom min-h-screen p-5 bg-slate-50">
    <div class="max-w-5xl mx-auto">
        
        {{-- Header Section --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('dashboard.walikelas') }}" class="text-xs font-bold text-slate-400 hover:text-indigo-600 transition-colors">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <span class="text-slate-300">/</span>
                    <span class="text-xs font-bold text-slate-500">Manajemen Kasus</span>
                </div>
                <h1 class="text-xl font-black text-slate-800 tracking-tight">
                    Tindak Lanjut Kasus
                </h1>
            </div>
            <a href="javascript:history.back()" class="btn-clean-action no-underline flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> <span>Kembali</span>
            </a>
        </div>

        {{-- Unified "Case File" Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            
            {{-- 1. Card Header: Student & Status --}}
            <div class="bg-slate-50/50 p-6 border-b border-slate-100 flex flex-col md:flex-row gap-4 items-start justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-lg font-black shadow-indigo-100 shadow-md">
                        {{ strtoupper(substr($kasus->siswa->nama_siswa, 0, 1)) }}
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 leading-tight">{{ $kasus->siswa->nama_siswa }}</h2>
                        <div class="flex items-center gap-3 mt-1 text-xs font-medium text-slate-500">
                            <span class="flex items-center gap-1"><i class="fas fa-id-card opacity-50"></i> {{ $kasus->siswa->nisn }}</span>
                            <span class="text-slate-300">|</span>
                            <span class="text-indigo-600 font-bold bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">{{ $kasus->siswa->kelas->nama_kelas }}</span>
                        </div>
                    </div>
                </div>

                @php
                    $statusColors = [
                        'Baru' => 'bg-rose-50 text-rose-600 border-rose-100 ring-rose-500/10',
                        'Menunggu Persetujuan' => 'bg-amber-50 text-amber-600 border-amber-100 ring-amber-500/10',
                        'Disetujui' => 'bg-emerald-50 text-emerald-600 border-emerald-100 ring-emerald-500/10',
                        'Ditangani' => 'bg-indigo-50 text-indigo-600 border-indigo-100 ring-indigo-500/10',
                        'Selesai' => 'bg-slate-50 text-slate-600 border-slate-100 ring-slate-500/10',
                    ];
                    $statusColor = $statusColors[$kasus->status->value] ?? 'bg-slate-50 text-slate-600 border-slate-200';
                @endphp
                <div class="px-3 py-1.5 rounded-lg border ring-1 {{ $statusColor }} flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 currentColor"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 currentColor bg-current"></span>
                    </span>
                    <span class="text-xs font-bold uppercase tracking-wider">{{ $kasus->status->value }}</span>
                </div>
            </div>

            {{-- 2. Card Body: Grid Layout --}}
            <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-slate-100">
                
                {{-- Left Side: Details --}}
                <div class="p-6 space-y-5">
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 block">Pemicu Kasus</label>
                        <p class="text-sm text-slate-700 font-medium italic">"{{ $kasus->pemicu }}"</p>
                    </div>
                    
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 block">Sanksi Sistem</label>
                        <div class="text-sm font-semibold text-rose-600 bg-rose-50 px-3 py-2 rounded-lg border border-rose-100 inline-block">
                            {{ $kasus->sanksi_deskripsi }}
                        </div>
                    </div>

                    @if($kasus->suratPanggilan && $kasus->suratPanggilan->keperluan)
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 block">Keperluan</label>
                        <p class="text-sm text-slate-600 leading-relaxed">{{ $kasus->suratPanggilan->keperluan }}</p>
                    </div>
                    @endif
                </div>
            </div>

                {{-- Right Side: Letter Info & Actions --}}
                <div class="p-6 bg-slate-50/30">
                     @if($kasus->suratPanggilan)
                        <div class="mb-6">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 block flex items-center gap-2">
                                <i class="fas fa-envelope text-indigo-400"></i> Detail Surat
                            </label>
                            
                            <div class="grid grid-cols-2 gap-y-4 gap-x-2">
                                <div>
                                    <span class="text-[10px] text-slate-400 block">Nomor Surat</span>
                                    <span class="text-xs font-bold text-slate-700 font-mono">{{ $kasus->suratPanggilan->nomor_surat }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 block">Tanggal Undangan</span>
                                    <span class="text-xs font-bold text-slate-700">
                                        {{ \Carbon\Carbon::parse($kasus->suratPanggilan->tanggal_pertemuan)->format('d M Y') }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 block">Waktu</span>
                                    <span class="text-xs font-bold text-slate-700">{{ $kasus->suratPanggilan->waktu_pertemuan }} WIB</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 block">Status Cetak</span>
                                    @if($kasus->suratPanggilan->printLogs->count() > 0)
                                        <span class="text-xs font-bold text-emerald-600">{{ $kasus->suratPanggilan->printLogs->count() }}x Dicetak</span>
                                    @else
                                        <span class="text-xs font-bold text-slate-400 italic">Belum dicetak</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <a href="{{ route('tindak-lanjut.preview-surat', $kasus->id) }}" 
                                   class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 hover:text-indigo-600 transition-all shadow-sm no-underline">
                                    <i class="fas fa-eye"></i> Preview
                                </a>
                                <a href="{{ route('tindak-lanjut.cetak-surat', $kasus->id) }}" 
                                   target="_blank"
                                   onclick="return confirm('Cetak surat?')"
                                   class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 hover:text-emerald-600 transition-all shadow-sm no-underline">
                                    <i class="fas fa-print"></i> Cetak
                                </a>
                            </div>

                        @if($kasus->status->value === 'Baru')
                        <form action="{{ route('tindak-lanjut.mulai-tangani', $kasus->id) }}" method="POST" 
                              onsubmit="return confirm('Mulai menangani kasus ini?')" class="ml-auto">
                            @csrf
                            @method('PUT')
                            <button type="submit" 
                                    class="flex items-center gap-2 px-5 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs uppercase tracking-wider transition-all shadow-lg shadow-indigo-200">
                                <i class="fas fa-play-circle"></i>
                                <span>Mulai Tangani</span>
                            </button>
                        </form>
                        @endif

                            @if($kasus->status->value === 'Ditangani')
                            <form action="{{ route('tindak-lanjut.selesaikan', $kasus->id) }}" method="POST" onsubmit="return confirm('Selesaikan kasus?')">
                                @csrf @method('PUT')
                                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider shadow-emerald-100 shadow-lg transition-all">
                                    <i class="fas fa-check-circle"></i> Selesaikan Kasus
                                </button>
                            </form>
                            @endif
                        </div>

                     @else
                        <div class="h-full flex flex-col items-center justify-center text-center p-6 text-slate-400">
                            <i class="fas fa-file-excel text-4xl mb-3 opacity-20"></i>
                            <span class="text-xs font-medium">Belum ada surat panggilan yang dibuat untuk kasus ini.</span>
                        </div>
                     @endif
                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection