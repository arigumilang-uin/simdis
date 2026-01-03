@extends('layouts.app')

@section('content')

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: { primary: '#4f46e5', slate: { 800: '#1e293b', 900: '#0f172a' } },
                screens: { 'xs': '375px' }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

<div class="page-wrap">

    <div class="relative rounded-2xl bg-gradient-to-r from-slate-800 to-blue-900 p-6 shadow-lg mb-8 text-white flex flex-col md:flex-row items-center justify-between gap-4 border border-blue-800/50 overflow-hidden">
        
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500 opacity-10 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-cyan-400 opacity-10 rounded-full blur-2xl -ml-10 -mb-10 pointer-events-none"></div>

        <div class="relative z-10 w-full md:w-auto">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-sm border border-white/10 text-[10px] font-medium text-blue-200 mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                Portal Wali Murid
            </div>
            <h1 class="text-xl md:text-2xl font-bold leading-tight">
                Halo, Bapak/Ibu! 👋
            </h1>
            <p class="text-blue-100 text-xs md:text-sm opacity-80 mt-1">
                Memantau perkembangan karakter dan kedisiplinan <strong>{{ $siswa->nama_siswa }}</strong>.
            </p>
        </div>

        <div class="hidden xs:flex items-center gap-3 bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/10 shadow-inner min-w-[140px] relative z-10">
            <div class="bg-blue-500/20 p-2 rounded-lg text-blue-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
            </div>
            <div>
                <span class="block text-2xl font-bold leading-none tracking-tight">{{ date('d') }}</span>
                <span class="block text-[10px] uppercase tracking-wider text-blue-200">{{ date('F Y') }}</span>
            </div>
        </div>
    </div>

    @if($semuaAnak->count() > 1)
    <div class="mb-8">
        <h3 class="text-sm font-bold text-slate-500 mb-3 px-1 uppercase tracking-wider">Pilih Profil Anak</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            @foreach($semuaAnak as $anak)
                <a href="{{ route('dashboard.wali_murid', ['siswa_id' => $anak->id]) }}" 
                   class="flex items-center gap-3 p-3 rounded-xl border transition-all duration-200 group no-underline
                          {{ $anak->id == $siswa->id 
                             ? 'bg-blue-600 border-blue-600 shadow-md transform scale-[1.02]' 
                             : 'bg-white border-slate-200 hover:border-blue-300 hover:shadow-sm' }}">
                    
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold shrink-0
                                {{ $anak->id == $siswa->id ? 'bg-white text-blue-600' : 'bg-slate-100 text-slate-500' }}">
                        {{ substr($anak->nama_siswa, 0, 1) }}
                    </div>
                    
                    <div class="overflow-hidden">
                        <h4 class="text-sm font-bold truncate {{ $anak->id == $siswa->id ? 'text-white' : 'text-slate-700' }}">
                            {{ $anak->nama_siswa }}
                        </h4>
                        <p class="text-xs truncate {{ $anak->id == $siswa->id ? 'text-blue-100' : 'text-slate-400' }}">
                            {{ $anak->kelas->nama_kelas ?? 'Tanpa Kelas' }}
                        </p>
                    </div>

                    @if($anak->id == $siswa->id)
                        <div class="ml-auto text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-br from-blue-50 to-indigo-50"></div>
                
                <div class="relative text-center mt-4">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($siswa->nama_siswa) }}&background=0f172a&color=fff&size=128" 
                         alt="Foto Siswa" 
                         class="w-24 h-24 rounded-full border-4 border-white shadow-md mx-auto mb-3">
                    
                    <h2 class="text-lg font-bold text-slate-800">{{ $siswa->nama_siswa }}</h2>
                    <div class="flex items-center justify-center gap-2 mt-1">
                        <span class="px-2 py-0.5 bg-slate-100 text-slate-500 text-xs rounded border border-slate-200">{{ $siswa->kelas->nama_kelas }}</span>
                        <span class="px-2 py-0.5 bg-slate-100 text-slate-500 text-xs rounded border border-slate-200">{{ $siswa->nisn }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-6">
                    <div class="bg-rose-50 p-3 rounded-xl border border-rose-100 text-center">
                        <span class="block text-2xl font-bold text-rose-600">{{ $totalPoin }}</span>
                        <span class="text-[10px] font-bold text-rose-400 uppercase tracking-wide">Total Poin</span>
                    </div>
                    <div class="bg-blue-50 p-3 rounded-xl border border-blue-100 text-center">
                        <span class="block text-2xl font-bold text-blue-600">{{ $riwayat->count() }}</span>
                        <span class="text-[10px] font-bold text-blue-400 uppercase tracking-wide">Pelanggaran</span>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-slate-100">
                    <h4 class="text-xs font-bold text-slate-400 uppercase mb-3">Wali Kelas</h4>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-700">{{ $siswa->kelas->waliKelas->nama ?? 'Belum Ditentukan' }}</p>
                            <p class="text-xs text-slate-400">Hubungi jika ada masalah.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            
            @if($kasus->isNotEmpty())
                <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-24 h-24 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
                    </div>
                    
                    <div class="relative z-10">
                        <h3 class="text-lg font-bold text-amber-800 flex items-center gap-2">
                            <span class="relative flex h-3 w-3">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                            </span>
                            Perhatian: Ada Kasus Aktif
                        </h3>
                        <p class="text-sm text-amber-700 mt-1 mb-4">
                            Terdapat catatan kasus yang sedang dalam penanganan atau masa sanksi.
                        </p>

                        <div class="space-y-2">
                            @foreach($kasus as $k)
                                <div class="bg-white p-3 rounded-xl border border-amber-200 flex items-start gap-3">
                                    <div class="mt-0.5 text-amber-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-700">{{ $k->created_at->format('d M Y') }}</p>
                                        <p class="text-sm text-slate-600 mt-0.5">Sanksi: {{ $k->sanksi_deskripsi }}</p>
                                        <span class="inline-block mt-2 px-2 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-bold rounded uppercase">Status: {{ $k->status }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- STATUS PEMBINAAN AKTIF --}}
            @if($pembinaanAktif)
            @php
                $statusColors = [
                    'Perlu Pembinaan' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-700', 'icon' => 'text-amber-500'],
                    'Sedang Dibina' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-700', 'icon' => 'text-blue-500'],
                    'Selesai' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-700', 'icon' => 'text-emerald-500'],
                ];
                $sc = $statusColors[$pembinaanAktif->status->value] ?? $statusColors['Perlu Pembinaan'];
            @endphp
            <div class="{{ $sc['bg'] }} {{ $sc['border'] }} border rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base font-bold {{ $sc['text'] }} flex items-center gap-2">
                        <i class="fas fa-user-check {{ $sc['icon'] }}"></i>
                        Status Pembinaan
                    </h3>
                    <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase {{ $sc['bg'] }} {{ $sc['text'] }} border {{ $sc['border'] }}">
                        {{ $pembinaanAktif->status->value }}
                    </span>
                </div>
                
                <p class="text-sm {{ $sc['text'] }} italic mb-3">
                    "{{ $pembinaanAktif->keterangan_pembinaan }}"
                </p>
                
                <div class="bg-white/50 rounded-xl p-3 space-y-2 text-sm {{ $sc['text'] }}">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-chart-line w-4"></i>
                        <span>{{ $pembinaanAktif->range_text }} ({{ $pembinaanAktif->total_poin_saat_trigger }} poin)</span>
                    </div>
                    @if($pembinaanAktif->dibinaOleh)
                    <div class="flex items-center gap-2">
                        <i class="fas fa-user w-4"></i>
                        <span>Dibina oleh: <strong>{{ $pembinaanAktif->dibinaOleh->nama ?? $pembinaanAktif->dibinaOleh->username }}</strong></span>
                    </div>
                    @endif
                    @if($pembinaanAktif->dibina_at)
                    <div class="flex items-center gap-2">
                        <i class="fas fa-clock w-4"></i>
                        <span>Mulai: {{ $pembinaanAktif->dibina_at->format('d M Y, H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-base font-bold text-slate-700 m-0">📜 Riwayat Kedisiplinan</h3>
                </div>

                <div class="p-4">
                    @if($riwayat->isEmpty())
                        <div class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-50 text-emerald-500 mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            </div>
                            <h4 class="text-slate-800 font-bold">Bersih! Tidak Ada Pelanggaran</h4>
                            <p class="text-slate-500 text-sm mt-1">Anak Anda sangat disiplin. Pertahankan!</p>
                        </div>
                    @else
                        <div class="relative border-l-2 border-slate-100 ml-3 space-y-6 pb-2">
                            @foreach($riwayat as $r)
                                <div class="relative pl-6">
                                    <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full border-2 border-white bg-rose-500 shadow-sm"></div>
                                    
                                    <div class="bg-white border border-slate-100 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow group">
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1 block">
                                                    {{ $r->tanggal_kejadian->format('d F Y') }} • {{ $r->tanggal_kejadian->format('H:i') }}
                                                </span>
                                                <h4 class="text-sm font-bold text-slate-800 group-hover:text-rose-600 transition-colors">
                                                    {{ $r->jenisPelanggaran->nama_pelanggaran }}
                                                </h4>
                                            </div>
                                            @php
                                                $poinInfo = \App\Helpers\PoinDisplayHelper::getPoinForRiwayat($r);
                                            @endphp
                                            <div class="text-right">
                                                <span class="inline-block px-2 py-1 bg-rose-50 text-rose-600 text-xs font-bold rounded border border-rose-100">
                                                    +{{ $poinInfo['poin'] }} Poin
                                                </span>
                                                @if($poinInfo['frequency'])
                                                    <span class="block text-[10px] text-slate-400 mt-1">ke-{{ $poinInfo['frequency'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        @if($r->keterangan)
                                            <p class="text-xs text-slate-500 bg-slate-50 p-2 rounded mb-2 border border-slate-100">
                                                "{{ $r->keterangan }}"
                                            </p>
                                        @endif

                                        @if($r->bukti_foto_path)
                                            <a href="{{ route('bukti.show', ['path' => $r->bukti_foto_path]) }}" target="_blank" 
                                               class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:text-blue-700 bg-blue-50 px-2 py-1 rounded hover:bg-blue-100 transition no-underline">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                                Lihat Bukti Foto
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

</div>

<style>
    .page-wrap { background: #f8fafc; min-height: 100vh; padding: 1.5rem; font-family: 'Inter', sans-serif; }
    
    /* Hover Lift Effect */
    .hover-lift { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease; }
    .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); }
</style>

@endsection