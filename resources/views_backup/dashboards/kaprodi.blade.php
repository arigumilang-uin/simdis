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
    function toggleFilterKaprodi() {
        const content = document.getElementById('filterContentKaprodi');
        content.classList.toggle('hidden');
    }
</script>

<div class="page-wrap">

    {{-- Header Section --}}
    <div class="relative rounded-2xl bg-gradient-to-r from-slate-800 to-blue-900 p-6 shadow-lg mb-8 text-white flex flex-col md:flex-row items-center justify-between gap-4 border border-blue-800/50 overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500 opacity-10 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-cyan-400 opacity-10 rounded-full blur-2xl -ml-10 -mb-10 pointer-events-none"></div>

        <div class="relative z-10 w-full md:w-auto">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-sm border border-white/10 text-[10px] font-medium text-blue-200 mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                Kaprodi Panel
            </div>
            <h1 class="text-xl md:text-2xl font-bold leading-tight">
                Halo, {{ auth()->user()->username }}! 👋
            </h1>
            <p class="text-blue-100 text-xs md:text-sm opacity-80 mt-1">
                Dashboard monitoring surat panggilan prodi {{ $jurusan->nama_jurusan }}.
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

    {{-- Filter Section --}}
    <div id="kaprodiFilterCard" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center cursor-pointer transition-colors hover:bg-slate-50 group" onclick="toggleFilterKaprodi()">
            <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider m-0 flex items-center gap-2 group-hover:text-blue-600 transition-colors">
                <span class="p-1.5 bg-blue-50 border border-blue-100 rounded-lg text-blue-600 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                </span>
                Filter Periode & Kelas
            </h3>
            <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </div>

        <div id="filterContentKaprodi" class="p-6 transition-all duration-300">
            <form method="GET" action="{{ route('dashboard.kaprodi') }}" class="w-full">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                    <div class="col-span-1">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl block p-2.5 outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl block p-2.5 outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Kelas</label>
                        <select name="kelas_id" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl block p-2.5 outline-none cursor-pointer">
                            <option value="">-- Semua Kelas --</option>
                            @foreach($kelasJurusan as $k)
                                <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-1">
                        <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold uppercase rounded-xl shadow-md transition-all h-[42px] border-none cursor-pointer">
                            Filter Data
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Stats Cards dengan Efek Hover Dashboard Operator --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
        <div class="group hover-lift bg-white rounded-xl p-4 shadow-sm border border-slate-100 relative overflow-hidden cursor-default transition-all duration-300">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Populasi Siswa</span>
                <div class="text-emerald-500 bg-emerald-50 p-2 rounded-lg group-hover:bg-emerald-500 group-hover:text-white transition-colors duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-slate-700 mb-1 leading-none">{{ $totalSiswa }}</h3>
            <p class="text-xs text-slate-500">Siswa Jurusan {{ $jurusan->kode_jurusan }}</p>
            <div class="absolute bottom-0 left-0 w-full h-1 bg-emerald-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
        </div>

        <div class="group hover-lift bg-white rounded-xl p-4 shadow-sm border border-slate-100 relative overflow-hidden cursor-default transition-all duration-300">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kasus Surat</span>
                <div class="text-rose-500 bg-rose-50 p-2 rounded-lg group-hover:bg-rose-500 group-hover:text-white transition-colors duration-300 {{ $totalKasus > 0 ? 'animate-pulse' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-slate-700 mb-1 leading-none">{{ $totalKasus }}</h3>
            <p class="text-xs text-slate-500">Kasus Perlu Penanganan</p>
            <div class="absolute bottom-0 left-0 w-full h-1 bg-rose-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
        </div>

        <div class="group hover-lift bg-white rounded-xl p-4 shadow-sm border border-slate-100 relative overflow-hidden cursor-default transition-all duration-300">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Pelanggaran</span>
                <div class="text-amber-500 bg-amber-50 p-2 rounded-lg group-hover:bg-amber-500 group-hover:text-white transition-colors duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-slate-700 mb-1 leading-none">{{ $totalPelanggaran }}</h3>
            <p class="text-xs text-slate-500">Akumulasi Periode Ini</p>
            <div class="absolute bottom-0 left-0 w-full h-1 bg-amber-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8 items-start">
        
        <div class="lg:col-span-8 bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-white">
                <h3 class="text-sm font-bold text-slate-700 m-0 flex items-center gap-2">
                    @if($totalKasus > 0)
                        <span class="relative flex h-2.5 w-2.5">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
                        </span>
                        Daftar Kasus Surat Panggilan
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        Status Jurusan Aman
                    @endif
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left table-fixed">
                    <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase font-bold tracking-widest">
                        <tr>
                            <th class="w-1/3 px-5 py-3 border-b border-slate-100">Siswa & Kelas</th>
                            <th class="w-1/2 px-5 py-3 border-b border-slate-100">Pemicu & Surat</th>
                            <th class="w-24 px-5 py-3 text-right border-b border-slate-100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($kasusBaru as $kasus)
                        <tr class="hover:bg-slate-50/80 transition-all group">
                            <td class="px-5 py-4">
                                <div class="text-xs font-bold text-slate-800 truncate">{{ $kasus->siswa->nama_siswa }}</div>
                                <div class="text-[9px] text-slate-500 font-bold uppercase tracking-tighter">{{ $kasus->siswa->kelas->nama_kelas }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-[11px] text-slate-600 leading-relaxed m-0 line-clamp-1 italic">"{{ $kasus->pemicu }}"</p>
                                <span class="text-[9px] font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded uppercase">{{ $kasus->suratPanggilan->tipe_surat ?? 'N/A' }}</span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('kasus.edit', $kasus->id) }}" class="text-indigo-600 text-[11px] font-bold hover:underline no-underline uppercase tracking-tighter">Detail</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center py-12 text-slate-400 text-xs italic">Tidak ada kasus surat aktif di prodi ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="lg:col-span-4 flex flex-col gap-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                <h3 class="text-[11px] font-bold text-slate-400 uppercase mb-4 tracking-widest flex items-center gap-2">
                    <span class="w-2 h-2 bg-purple-500 rounded-full"></span> Pelanggaran Populer
                </h3>
                <div class="h-64 relative">
                    <canvas id="chartPelanggaran"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
// Konfigurasi Horizontal Bar (Konsisten dengan dashboard lain)
const horizontalOptions = {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    plugins: { 
        legend: { display: false },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 10,
            cornerRadius: 8
        }
    },
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
            backgroundColor: 'rgba(147, 51, 234, 0.8)',
            borderRadius: 4,
            barThickness: 12
        }]
    },
    options: horizontalOptions
});
</script>

<style>
    .page-wrap { background: #f8fafc; min-height: 100vh; padding: 1.5rem; font-family: 'Inter', sans-serif; }
    .hover-lift { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease; }
    .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); }
    .line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
</style>

@endsection