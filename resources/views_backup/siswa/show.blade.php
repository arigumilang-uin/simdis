@extends('layouts.app')
@section('content')

{{-- 1. TAILWIND CONFIG & SETUP --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#0f172a',
                    indigo: { 600: '#4f46e5', 50: '#eef2ff', 100: '#e0e7ff', 700: '#4338ca' },
                    emerald: { 50: '#ecfdf5', 100: '#d1fae5', 600: '#059669', 700: '#047857' },
                    rose: { 50: '#fff1f2', 100: '#ffe4e6', 600: '#e11d48', 700: '#be123c' },
                    amber: { 50: '#fffbeb', 100: '#fef3c7', 600: '#d97706', 700: '#b45309' }
                }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

<div class="page-wrap-custom min-h-screen p-6">
    <div class="max-w-7xl mx-auto">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-3 gap-1 pb-1 custom-header-row">
            <div>
                <div class="flex items-center gap-2 text-indigo-600 mb-1">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">Manajemen Siswa</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-800 m-0 tracking-tight flex items-center gap-3">
                    <i class="fas fa-user-graduate text-indigo-600"></i> Profil Siswa
                </h1>
            </div>
            
            <a href="{{ url()->previous() }}" class="btn-clean-action no-underline">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 animate-in fade-in duration-500">
            
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden sticky top-6">
                    <div class="h-24 bg-slate-900 relative">
                        <div class="absolute -bottom-12 left-1/2 -translate-x-1/2">
                            <img class="w-24 h-24 rounded-2xl object-cover ring-4 ring-white shadow-lg bg-white" 
                                 src="https://ui-avatars.com/api/?name={{ urlencode($siswa->nama_siswa) }}&background=4f46e5&color=ffffff&size=128&bold=true" 
                                 alt="Avatar">
                        </div>
                    </div>
                    
                    <div class="pt-16 pb-6 px-6 text-center">
                        <h2 class="text-lg font-black text-slate-800 mb-1 tracking-tight">{{ $siswa->nama_siswa }}</h2>
                        <span class="text-[10px] font-mono text-indigo-500 uppercase font-bold tracking-widest">NISN: {{ $siswa->nisn }}</span>
                        
                        <div class="flex flex-wrap justify-center gap-2 mt-4">
                            <span class="px-3 py-1 text-[10px] font-black uppercase rounded-lg bg-slate-100 text-slate-600 border border-slate-200">
                                {{ $siswa->kelas->nama_kelas }}
                            </span>
                            <span class="px-3 py-1 text-[10px] font-black uppercase rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-100">
                                {{ $siswa->kelas->jurusan->nama_jurusan }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mt-6">
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Poin</span>
                                @php
                                    $poinColor = $totalPoin >= 300 ? 'text-rose-600' : ($totalPoin >= 100 ? 'text-amber-600' : 'text-emerald-600');
                                @endphp
                                <span class="text-xl font-black {{ $poinColor }}">{{ $totalPoin }}</span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Kasus</span>
                                <span class="text-xl font-black text-slate-700">{{ $siswa->riwayatPelanggaran->count() }}</span>
                            </div>
                        </div>
                    </div>

                    @if($pembinaanAktif)
                    {{-- Status Pembinaan AKTIF (Perlu Pembinaan / Sedang Dibina) --}}
                    @php
                        $statusColors = [
                            'Perlu Pembinaan' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-700', 'badge' => 'bg-amber-100 text-amber-700 border-amber-200'],
                            'Sedang Dibina' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-700', 'badge' => 'bg-blue-100 text-blue-700 border-blue-200'],
                            'Selesai' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-700', 'badge' => 'bg-emerald-100 text-emerald-700 border-emerald-200'],
                        ];
                        $sc = $statusColors[$pembinaanAktif->status->value] ?? $statusColors['Perlu Pembinaan'];
                    @endphp
                    <div class="p-5 {{ $sc['bg'] }} border-t {{ $sc['border'] }}">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-user-check {{ $sc['text'] }} text-sm"></i>
                                <span class="text-[10px] font-black {{ $sc['text'] }} uppercase tracking-wider">Status Pembinaan</span>
                            </div>
                            <span class="px-2 py-1 rounded-lg text-[9px] font-black uppercase border {{ $sc['badge'] }}">
                                {{ $pembinaanAktif->status->value }}
                            </span>
                        </div>
                        <p class="text-[11px] {{ $sc['text'] }} leading-relaxed font-bold italic mb-3">
                            "{{ $pembinaanAktif->keterangan_pembinaan }}"
                        </p>
                        <div class="text-[10px] {{ $sc['text'] }} space-y-1 mb-3">
                            <div><i class="fas fa-chart-line mr-1"></i> {{ $pembinaanAktif->range_text }} ({{ $pembinaanAktif->total_poin_saat_trigger }} poin)</div>
                            @if($pembinaanAktif->dibinaOleh)
                            <div><i class="fas fa-user mr-1"></i> Dibina oleh: {{ $pembinaanAktif->dibinaOleh->username }}</div>
                            @endif
                            @if($pembinaanAktif->dibina_at)
                            <div><i class="fas fa-clock mr-1"></i> Mulai: {{ $pembinaanAktif->dibina_at->format('d M Y H:i') }}</div>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($pembinaanAktif->pembina_roles as $role)
                                <span class="px-2 py-1 {{ $sc['text'] }} bg-white/50 text-[9px] font-black rounded-md uppercase tracking-tighter border {{ $sc['border'] }}">
                                    {{ $role }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @elseif($pembinaanSelesai)
                    {{-- Pembinaan SELESAI untuk range poin saat ini --}}
                    <div class="p-5 bg-emerald-50 border-t border-emerald-200">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-check-circle text-emerald-600 text-sm"></i>
                                <span class="text-[10px] font-black text-emerald-700 uppercase tracking-wider">Pembinaan Selesai</span>
                            </div>
                            <span class="px-2 py-1 rounded-lg text-[9px] font-black uppercase bg-emerald-100 text-emerald-700 border border-emerald-200">
                                Tuntas
                            </span>
                        </div>
                        <p class="text-[11px] text-emerald-700 leading-relaxed font-bold italic mb-3">
                            "{{ $pembinaanSelesai->keterangan_pembinaan }}"
                        </p>
                        <div class="text-[10px] text-emerald-700 space-y-1 mb-3">
                            <div><i class="fas fa-chart-line mr-1"></i> {{ $pembinaanSelesai->range_text }} ({{ $pembinaanSelesai->total_poin_saat_trigger }} poin saat dibina)</div>
                            @if($pembinaanSelesai->dibinaOleh)
                            <div><i class="fas fa-user mr-1"></i> Dibina oleh: {{ $pembinaanSelesai->dibinaOleh->username }}</div>
                            @endif
                            @if($pembinaanSelesai->dibina_at)
                            <div><i class="fas fa-play-circle mr-1"></i> Mulai: {{ $pembinaanSelesai->dibina_at->format('d M Y H:i') }}</div>
                            @endif
                            @if($pembinaanSelesai->diselesaikanOleh)
                            <div><i class="fas fa-user-check mr-1"></i> Diselesaikan oleh: {{ $pembinaanSelesai->diselesaikanOleh->username }}</div>
                            @endif
                            @if($pembinaanSelesai->selesai_at)
                            <div><i class="fas fa-check-double mr-1"></i> Selesai: {{ $pembinaanSelesai->selesai_at->format('d M Y H:i') }}</div>
                            @endif
                        </div>
                        @if($pembinaanSelesai->hasil_pembinaan)
                        <div class="bg-white/50 rounded-lg p-3 border border-emerald-200 mt-3">
                            <span class="text-[9px] font-black text-emerald-600 uppercase tracking-wider block mb-1">Hasil Pembinaan:</span>
                            <p class="text-[11px] text-emerald-800 m-0">{{ $pembinaanSelesai->hasil_pembinaan }}</p>
                        </div>
                        @endif
                    </div>
                    @elseif(!empty($pembinaanRekomendasi['pembina_roles']))
                    {{-- Fallback: Perlu pembinaan tapi belum ada record --}}
                    <div class="p-5 bg-rose-50 border-t border-rose-100">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fas fa-exclamation-triangle text-rose-600 text-sm"></i>
                            <span class="text-[10px] font-black text-rose-700 uppercase tracking-wider">Perlu Pembinaan</span>
                        </div>
                        <p class="text-[11px] text-rose-800 leading-relaxed font-bold italic mb-3">
                            "{{ $pembinaanRekomendasi['keterangan'] }}"
                        </p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($pembinaanRekomendasi['pembina_roles'] as $role)
                                <span class="px-2 py-1 bg-rose-600 text-white text-[9px] font-black rounded-md uppercase tracking-tighter">
                                    {{ $role }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-8 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100">
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-500 m-0 flex items-center gap-2">
                            <i class="fas fa-graduation-cap text-indigo-600"></i> Informasi Akademik & Keluarga
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        @php $fields = [
                            ['icon' => 'fas fa-user-shield', 'label' => 'Wali Murid', 'val' => $siswa->waliMurid->username ?? '-'],
                            ['icon' => 'fas fa-phone-alt', 'label' => 'No. HP Wali', 'val' => $siswa->nomor_hp_wali_murid ?? '-'],
                            ['icon' => 'fas fa-user-tie', 'label' => 'Wali Kelas', 'val' => $siswa->kelas->waliKelas->username ?? '-'],
                            ['icon' => 'fas fa-user-cog', 'label' => 'Kaprodi', 'val' => $siswa->kelas->jurusan->kaprodi->username ?? '-']
                        ]; @endphp
                        
                        @foreach($fields as $f)
                        <div class="flex items-center gap-4 p-4 rounded-xl border border-slate-100 hover:bg-slate-50 transition-colors">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm border border-indigo-100">
                                <i class="{{ $f['icon'] }}"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">{{ $f['label'] }}</span>
                                <span class="text-sm font-black text-slate-700 truncate block leading-tight">{{ $f['val'] }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-500 m-0">Riwayat Pelanggaran Terkini</h3>
                    </div>
                    
                    <div class="p-6">
                        @if($siswa->riwayatPelanggaran->count() > 0)
                        <div class="relative space-y-4 px-2">
    <div class="absolute inset-0 left-8 w-0.5 bg-slate-100 h-full"></div>

    @forelse($siswa->riwayatPelanggaran->sortByDesc('tanggal_kejadian') as $riwayat)
    <div class="relative flex items-start gap-6 group">
        
        <div class="flex flex-col items-center flex-shrink-0 w-12 pt-1">
            <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter leading-none mb-2">
                {{ \Carbon\Carbon::parse($riwayat->tanggal_kejadian)->format('M Y') }}
            </span>
            <div class="w-8 h-8 rounded-xl bg-white border-2 border-rose-500 text-rose-500 flex items-center justify-center z-10 shadow-sm group-hover:bg-rose-500 group-hover:text-white transition-all duration-300">
                <i class="fas fa-exclamation text-[10px]"></i>
            </div>
        </div>

        <div class="flex-1 bg-white rounded-2xl border border-slate-200 p-4 shadow-sm hover:border-indigo-300 hover:shadow-md transition-all duration-300 mb-2">
            
            <div class="flex justify-between items-start mb-3 gap-4">
                <div class="min-w-0">
                    <div class="text-[10px] font-mono text-indigo-500 font-bold uppercase tracking-tight mb-1">
                        <i class="far fa-clock mr-1"></i> {{ \Carbon\Carbon::parse($riwayat->tanggal_kejadian)->format('d/m/Y - H:i') }} WIB
                    </div>
                    <h4 class="text-sm font-black text-slate-800 tracking-tight leading-tight">
                        {{ $riwayat->jenisPelanggaran->nama_pelanggaran }}
                    </h4>
                </div>

                @php
                    $poinInfo = \App\Helpers\PoinDisplayHelper::getPoinForRiwayat($riwayat);
                    $poinValue = $poinInfo['matched'] && $poinInfo['poin'] > 0 ? $poinInfo['poin'] : 0;
                @endphp
                <div class="flex-shrink-0 text-right">
                    <span class="px-3 py-1.5 rounded-xl bg-rose-600 text-white text-[10px] font-black uppercase shadow-sm flex items-center gap-2">
                        +{{ $poinValue }} <span class="opacity-70 text-[8px]">Poin</span>
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center pt-3 border-t border-slate-50">
                <div class="md:col-span-8">
                    <p class="text-[11px] text-slate-600 leading-relaxed italic m-0">
                        <i class="fas fa-quote-left text-slate-200 mr-1"></i>
                        {{ $riwayat->keterangan ?? 'Tidak ada keterangan tambahan yang dicatat untuk pelanggaran ini.' }}
                    </p>
                </div>

                <div class="md:col-span-4 flex justify-end">
                    <div class="inline-flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                        <div class="w-5 h-5 rounded-md bg-indigo-600 text-white flex items-center justify-center text-[8px]">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <div class="flex flex-col leading-none">
                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-tighter">Dicatat Oleh</span>
                            <span class="text-[10px] font-bold text-slate-700 truncate max-w-[100px]">{{ $riwayat->guruPencatat->username ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-20 bg-white rounded-3xl border-2 border-dashed border-slate-100">
        <div class="opacity-30">
            <i class="fas fa-shield-alt text-5xl mb-4 text-slate-300"></i>
            <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Record Bersih</p>
        </div>
    </div>
    @endforelse
</div>
                        @else
                        <div class="text-center py-10 opacity-40">
                            <i class="fas fa-check-circle text-4xl text-emerald-500 mb-3"></i>
                            <p class="text-sm font-black text-slate-400 uppercase tracking-widest">Catatan Bersih</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
/* --- CORE STYLING (IDENTIK DENGAN MODUL SEBELUMNYA) --- */
.page-wrap-custom { background: #f8fafc; font-family: 'Inter', sans-serif; }
.custom-header-row { border-bottom: 1px solid #e2e8f0; }

.btn-clean-action {
    padding: 0.65rem 1.2rem; 
    border-radius: 0.75rem;
    background-color: #f1f5f9; 
    color: #475569; 
    font-size: 0.8rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    transition: 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border: 1px solid #e2e8f0;
}
.btn-clean-action:hover { background-color: #e2e8f0; color: #1e293b; }

/* Animasi Fade In */
.animate-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endsection