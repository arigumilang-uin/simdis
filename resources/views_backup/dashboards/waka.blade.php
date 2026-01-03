@extends('layouts.app')

@section('content')

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

<script>
    function toggleFilterAdmin() {
        const content = document.getElementById('filterContentAdmin');
        content.classList.toggle('hidden');
    }
</script>

<div class="page-wrap">

    <div class="relative rounded-2xl bg-gradient-to-r from-slate-800 to-blue-900 p-6 shadow-lg mb-8 text-white flex flex-col md:flex-row items-center justify-between gap-4 border border-blue-800/50 overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500 opacity-10 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-cyan-400 opacity-10 rounded-full blur-2xl -ml-10 -mb-10 pointer-events-none"></div>
        <div class="relative z-10 w-full md:w-auto">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-sm border border-white/10 text-[10px] font-medium text-blue-200 mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                Waka Kesiswaan Panel
            </div>
            <h1 class="text-xl md:text-2xl font-bold leading-tight">Halo, {{ auth()->user()->username }}! 👋</h1>
            <p class="text-blue-100 text-xs md:text-sm opacity-80 mt-1">Dashboard khusus untuk monitoring surat panggilan dan kasus siswa seluruh sekolah.</p>
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

    {{-- Filter & Pencarian Section --}}
<div id="adminFilterCard" class="bg-white rounded-2xl shadow-soft border border-slate-200 overflow-hidden mb-6">
    
    {{-- Header Kartu yang Bisa di-toggle --}}
    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center cursor-pointer transition-colors hover:bg-slate-50 group" onclick="toggleFilterAdmin()">
        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider m-0 flex items-center gap-2 group-hover:text-blue-600 transition-colors">
            <span class="p-1.5 bg-blue-50 border border-blue-100 rounded-lg text-blue-600 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
            </span>
            Filter Periode & Kategori Laporan
        </h3>
        <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
    </div>

    {{-- Konten Filter --}}
    <div id="filterContentAdmin" class="p-6 transition-all duration-300">
        <form action="{{ route('dashboard.admin') }}" method="GET" class="w-full">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 items-end">
                
                {{-- Dari Tanggal --}}
                <div class="col-span-1">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" 
                           class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5 shadow-sm transition-all hover:bg-white outline-none">
                </div>

                {{-- Sampai Tanggal --}}
                <div class="col-span-1">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" 
                           class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5 shadow-sm transition-all hover:bg-white outline-none">
                </div>

                {{-- Jurusan --}}
                <div class="col-span-1">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Jurusan</label>
                    <div class="relative">
                        <select name="jurusan_id" class="w-full appearance-none bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5 pr-8 shadow-sm transition-all hover:bg-white cursor-pointer" onchange="this.form.submit()">
                            <option value="">- Semua Jurusan -</option>
                            @foreach($allJurusan as $j)
                                <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>{{ $j->kode_jurusan }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                {{-- Kelas --}}
                <div class="col-span-1">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Kelas</label>
                    <div class="relative">
                        <select name="kelas_id" class="w-full appearance-none bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-2.5 pr-8 shadow-sm transition-all hover:bg-white cursor-pointer" onchange="this.form.submit()">
                            <option value="">- Semua Kelas -</option>
                            @foreach($allKelas as $k)
                                <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                        <div class="col-span-1 flex gap-2">
                            <button type="submit" class="flex-1 inline-flex justify-center items-center gap-2 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-md shadow-blue-200 transition-all active:scale-95 border-none cursor-pointer h-[42px]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                Filter
                            </button>
                            
                            @if(request()->has('start_date') || request()->has('end_date') || request()->has('jurusan_id') || request()->has('kelas_id'))
                                <a href="{{ route('dashboard.admin') }}" class="inline-flex justify-center items-center w-12 bg-rose-50 border border-rose-100 text-rose-600 rounded-xl hover:bg-rose-100 transition-all active:scale-95 h-[42px]" title="Reset Filter">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

   <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="group hover-lift bg-white rounded-xl p-4 shadow-sm border border-slate-100 relative overflow-hidden cursor-default">
        <div class="flex justify-between items-start mb-3">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Populasi</span>
            <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg group-hover:bg-emerald-500 group-hover:text-white transition-colors duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
            </div>
        </div>
        <h3 class="text-2xl font-bold text-slate-700 mb-1">{{ $totalSiswa }}</h3>
        <p class="text-xs text-slate-500">Total Siswa Sekolah</p>
        <div class="absolute bottom-0 left-0 w-full h-1 bg-emerald-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
    </div>

    <div class="group hover-lift bg-white rounded-xl p-4 shadow-sm border border-slate-100 relative overflow-hidden cursor-default">
        <div class="flex justify-between items-start mb-3">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kasus Surat</span>
            <div class="p-2 bg-rose-50 text-rose-600 rounded-lg group-hover:bg-rose-500 group-hover:text-white transition-colors duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
            </div>
        </div>
        <h3 class="text-2xl font-bold text-slate-700 mb-1">{{ $totalKasus }}</h3>
        <p class="text-xs text-slate-500">Melibatkan Saya</p>
        <div class="absolute bottom-0 left-0 w-full h-1 bg-rose-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
    </div>

    <div class="group hover-lift bg-white rounded-xl p-4 shadow-sm border border-slate-100 relative overflow-hidden cursor-default">
        <div class="flex justify-between items-start mb-3">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pelanggaran</span>
            <div class="p-2 bg-amber-50 text-amber-600 rounded-lg group-hover:bg-amber-500 group-hover:text-white transition-colors duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
        </div>
        <h3 class="text-2xl font-bold text-slate-700 mb-1">{{ $pelanggaranFiltered }}</h3>
        <p class="text-xs text-slate-500">Periode Ini</p>
        <div class="absolute bottom-0 left-0 w-full h-1 bg-amber-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
    </div>

    <div class="group hover-lift bg-white rounded-xl p-4 shadow-sm border border-slate-100 relative overflow-hidden cursor-default">
        <div class="flex justify-between items-start mb-3">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kasus Aktif</span>
            <div class="p-2 bg-blue-50 text-blue-600 rounded-lg group-hover:bg-blue-500 group-hover:text-white transition-colors duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <h3 class="text-2xl font-bold text-slate-700 mb-1">{{ $kasusAktif }}</h3>
        <p class="text-xs text-slate-500">Belum Selesai</p>
        <div class="absolute bottom-0 left-0 w-full h-1 bg-blue-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
    </div>
</div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8 items-start">
        
        <div class="lg:col-span-8 bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-white">
                <h3 class="text-sm font-bold text-slate-700 m-0">Daftar Kasus Surat Panggilan</h3>
                @if($totalKasus > 0)
                    <span class="text-[10px] font-bold text-rose-600 bg-rose-50 px-2 py-1 rounded border border-rose-100">{{ $totalKasus }} Kasus</span>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left table-fixed">
                    <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase font-bold tracking-widest">
                        <tr>
                            <th class="w-1/3 px-5 py-3 border-b border-slate-100">Siswa & Kelas</th>
                            <th class="w-1/2 px-5 py-3 border-b border-slate-100">Pemicu Masalah</th>
                            <th class="w-24 px-5 py-3 text-right border-b border-slate-100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($daftarKasus as $kasus)
                        <tr class="hover:bg-slate-50/80 transition-all">
                            <td class="px-5 py-3">
                                <div class="text-xs font-bold text-slate-800 truncate">{{ $kasus->siswa->nama_siswa }}</div>
                                <div class="text-[9px] text-slate-500 font-bold uppercase tracking-tighter">{{ $kasus->siswa->kelas->nama_kelas ?? 'N/A' }}</div>
                            </td>
                            <td class="px-5 py-3">
                                <p class="text-[11px] text-slate-600 leading-relaxed m-0 line-clamp-2 italic">"{{ $kasus->pemicu }}"</p>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('kasus.edit', $kasus->id) }}" class="text-indigo-600 text-[11px] font-bold hover:underline">Detail</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center py-12 text-slate-400 text-xs italic">Tidak ada kasus surat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="lg:col-span-4 flex flex-col gap-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                <h3 class="text-[11px] font-bold text-slate-400 uppercase mb-4 tracking-widest flex items-center gap-2">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full"></span> Pelanggaran Populer
                </h3>
                <div class="h-60 relative"> <canvas id="chartPelanggaran"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                <h3 class="text-[11px] font-bold text-slate-400 uppercase mb-4 tracking-widest flex items-center gap-2">
                    <span class="w-2 h-2 bg-rose-500 rounded-full"></span> Kelas Ternakal
                </h3>
                <div class="h-60 relative">
                    <canvas id="chartKelas"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
// Konfigurasi Horizontal Bar (Teks di samping pasti nampak)
const horizontalOptions = {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        x: { beginAtZero: true, ticks: { font: { size: 9 }, precision: 0 }, grid: { color: '#f1f5f9' } },
        y: { 
            ticks: { 
                font: { size: 9, weight: 'bold' },
                callback: function(value) {
                    const label = this.getLabelForValue(value);
                    return label.length > 15 ? label.substr(0, 15) + '...' : label;
                }
            }, 
            grid: { display: false } 
        }
    }
};

new Chart(document.getElementById('chartPelanggaran'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [{
            data: {!! json_encode($chartData) !!},
            backgroundColor: '#10b981',
            borderRadius: 4,
            barThickness: 12
        }]
    },
    options: horizontalOptions
});

new Chart(document.getElementById('chartKelas'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartKelasLabels) !!},
        datasets: [{
            data: {!! json_encode($chartKelasData) !!},
            backgroundColor: '#ef4444',
            borderRadius: 4,
            barThickness: 12
        }]
    },
    options: horizontalOptions
});
</script>

<style>
    .page-wrap { background: #f8fafc; min-height: 100vh; padding: 1.5rem; font-family: 'Inter', sans-serif; }
    .stat-card { background: white; border-radius: 1rem; padding: 1.25rem; border: 1px solid #f1f5f9; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .hover-lift { transition: all 0.2s ease; }
    .hover-lift:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); }
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>

@endsection