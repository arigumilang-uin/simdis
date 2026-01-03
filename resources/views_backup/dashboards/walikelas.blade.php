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

<div class="page-wrap">

    <!-- Header Section -->
    <div class="relative rounded-2xl bg-gradient-to-r from-slate-800 to-blue-900 p-6 shadow-lg mb-8 text-white flex flex-col md:flex-row items-center justify-between gap-4 border border-blue-800/50 overflow-hidden">
        
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500 opacity-10 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-cyan-400 opacity-10 rounded-full blur-2xl -ml-10 -mb-10 pointer-events-none"></div>

        <div class="relative z-10 w-full md:w-auto">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-sm border border-white/10 text-[10px] font-medium text-blue-200 mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                Wali Kelas Panel
            </div>
            <h1 class="text-xl md:text-2xl font-bold leading-tight">
                Halo, {{ auth()->user()->username }}! 👋
            </h1>
            <p class="text-blue-100 text-xs md:text-sm opacity-80 mt-1">
                Dashboard khusus untuk monitoring surat panggilan dan kasus siswa untuk kelas {{ $kelas->nama_kelas }}.
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

    <!-- Date Filter -->
   <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 cursor-pointer hover:bg-slate-100 transition-all group" 
         onclick="document.getElementById('filterContentToggle').classList.toggle('hidden')">
        
        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider m-0 flex items-center gap-2">
            <span class="text-blue-600">
                <i class="fas fa-filter text-xs"></i>
            </span>
            Konfigurasi Periode Laporan
        </h3>
        <i class="fas fa-chevron-down text-slate-400 group-hover:text-blue-600 transition-all"></i>
    </div>

    <div id="filterContentToggle" class="hidden p-6 transition-all duration-300">
        <form method="GET" action="{{ route('dashboard.walikelas') }}">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-5">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Rentang Mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="block w-full p-2.5 text-sm font-semibold text-slate-700 border border-slate-200 rounded-xl bg-slate-50 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                </div>
                <div class="md:col-span-5">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Rentang Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="block w-full p-2.5 text-sm font-semibold text-slate-700 border border-slate-200 rounded-xl bg-slate-50 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="w-full h-[42px] bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-xl shadow-lg transition-all active:scale-95 border-none cursor-pointer">
                        Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
        
        <div class="group hover-lift bg-white rounded-xl p-4 shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Populasi</span>
                <div class="text-emerald-500 bg-emerald-50 p-2 rounded-lg group-hover:bg-emerald-500 group-hover:text-white transition-colors duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-slate-700 mb-1">{{ $totalSiswa }}</h3>
            <p class="text-xs text-slate-500">Siswa di Kelas {{ $kelas->nama_kelas }}</p>
            <div class="absolute bottom-0 left-0 w-full h-1 bg-emerald-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
        </div>

        <div class="group hover-lift bg-white rounded-xl p-4 shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kasus Surat</span>
                <div class="text-rose-500 bg-rose-50 p-2 rounded-lg group-hover:bg-rose-500 group-hover:text-white transition-colors duration-300 {{ $totalKasus > 0 ? 'animate-pulse' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-slate-700 mb-1">{{ $totalKasus }}</h3>
            <p class="text-xs text-slate-500">Kasus Perlu Penanganan</p>
            <div class="absolute bottom-0 left-0 w-full h-1 bg-rose-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
        </div>

        <div class="group hover-lift bg-white rounded-xl p-4 shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pelanggaran</span>
                <div class="text-amber-500 bg-amber-50 p-2 rounded-lg group-hover:bg-amber-500 group-hover:text-white transition-colors duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-slate-700 mb-1">{{ $totalPelanggaran }}</h3>
            <p class="text-xs text-slate-500">Total Periode Ini</p>
            <div class="absolute bottom-0 left-0 w-full h-1 bg-amber-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
        </div>

    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8 items-start">
        
        <!-- Kasus Table -->
        <div class="lg:col-span-8 bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col overflow-hidden text-sm">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-white">
                <h3 class="text-sm font-bold text-slate-700 m-0 flex items-center gap-2">
                    @if($totalKasus > 0)
                        <span class="relative flex h-2.5 w-2.5">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
                        </span>
                        Kasus Surat Panggilan
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        Status Kelas: Aman
                    @endif
                </h3>
                @if($totalKasus > 0)
                    <span class="text-[10px] font-bold text-rose-600 bg-rose-50 px-2 py-1 rounded border border-rose-100">
                        {{ $totalKasus }} Pending
                    </span>
                @endif
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 text-slate-500 text-[10px] uppercase font-bold tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 pl-6 font-bold w-[25%]">Siswa</th>
                            <th class="px-4 py-3 font-bold w-[30%]">Pemicu</th>
                            <th class="px-4 py-3 font-bold w-[12%] whitespace-nowrap text-center">Tipe Surat</th>
                            <th class="px-4 py-3 text-center font-bold w-[13%] whitespace-nowrap">Status</th>
                            <th class="px-4 py-3 text-right pr-6 font-bold w-[10%] whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($kasusBaru as $kasus)
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="px-4 py-3 pl-6 align-middle">
                                <div class="text-xs font-bold text-slate-700">{{ $kasus->siswa->nama_siswa }}</div>
                                <div class="text-[10px] text-slate-500 font-medium mt-0.5">NISN: {{ $kasus->siswa->nisn }}</div>
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <div class="text-xs text-slate-600 line-clamp-2 leading-relaxed" title="{{ $kasus->pemicu }}">
                                    {{ $kasus->pemicu }}
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap align-middle text-center">
                                <span class="text-[10px] font-bold bg-blue-50 text-blue-600 border border-blue-100 px-2 py-1 rounded inline-block min-w-[60px]">
                                    {{ $kasus->suratPanggilan->tipe_surat ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap align-middle">
                                @if($kasus->status == 'Menunggu Persetujuan')
                                    <span class="text-[10px] font-bold text-amber-600 bg-amber-50 border border-amber-100 px-2 py-1 rounded inline-block min-w-[70px]">Menunggu</span>
                                @elseif($kasus->status == 'Baru')
                                    <span class="text-[10px] font-bold text-rose-600 bg-rose-50 border border-rose-100 px-2 py-1 rounded inline-block min-w-[70px] animate-pulse">Baru</span>
                                @else
                                    <span class="text-[10px] font-bold text-blue-600 bg-blue-50 border border-blue-100 px-2 py-1 rounded inline-block min-w-[70px]">{{ $kasus->status }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right pr-6 whitespace-nowrap align-middle">
                                <a href="{{ route('kasus.edit', $kasus->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 border border-slate-200 text-slate-500 hover:text-blue-600 hover:bg-blue-50 hover:border-blue-100 transition-all shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-10 text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                    <span class="text-xs font-medium">Tidak ada data kasus surat saat ini.</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="lg:col-span-4 flex flex-col h-full">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col h-full">
                <!-- Header Chart (Matching Table Header) -->
                <div class="px-6 py-4 border-b border-slate-100 bg-white rounded-t-2xl">
                    <h3 class="text-sm font-bold text-slate-700 m-0 flex items-center gap-2">
                        <span class="w-2 h-2 bg-indigo-500 rounded-full ring-2 ring-indigo-100"></span>
                        Statistik Pelanggaran
                    </h3>
                </div>

                <!-- Chart Body -->
                <div class="p-6 flex-1 flex flex-col justify-center">
                    @if(collect($chartData)->sum() > 0)
                        <div class="relative h-[250px] w-full">
                            <canvas id="chartPelanggaran"></canvas>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center h-[200px] text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 mb-2 opacity-50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/></svg>
                            <span class="text-xs font-medium">Belum ada data pelanggaran</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

</div>

<script>
// Chart Pelanggaran Populer (Horizontal)
const ctx = document.getElementById('chartPelanggaran').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [{
            label: 'Jumlah Pelanggaran',
            data: {!! json_encode($chartData) !!},
            backgroundColor: '#4f46e5', // Indigo-600 (Darker)
            hoverBackgroundColor: '#4338ca', // Indigo-700
            borderRadius: 4,
            barThickness: 24, // Thicker bars
            maxBarThickness: 32
        }]
    },
    options: {
        indexAxis: 'y', // Horizontal
        responsive: true,
        maintainAspectRatio: false,
        layout: {
            padding: {
                left: 0,
                right: 20, // Add space for labels
                top: 0,
                bottom: 0
            }
        },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 1)', // Dark background for high contrast
                titleColor: '#ffffff',
                bodyColor: '#e2e8f0',
                padding: 12,
                titleFont: { size: 13, weight: 'bold' },
                bodyFont: { size: 12 },
                cornerRadius: 8,
                displayColors: false,
                callbacks: {
                    label: function(context) {
                        return 'Total: ' + context.parsed.x + ' Kasus';
                    }
                }
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                grid: { 
                    color: '#e2e8f0', // Visible subtle grid
                    borderDash: [4, 4],
                    drawBorder: false 
                },
                ticks: { 
                    font: { size: 11, weight: '600', family: "'Inter', sans-serif" }, 
                    color: '#475569', // Darker text
                    stepSize: 1 
                },
                border: { display: false }
            },
            y: {
                grid: { display: false, drawBorder: false },
                ticks: {
                    font: { size: 11, weight: '600', family: "'Inter', sans-serif" },
                    color: '#334155', // Slate-700 (High contrast)
                    autoSkip: false,
                    callback: function(value) {
                         const label = this.getLabelForValue(value);
                         return label.length > 18 ? label.substr(0, 18) + '...' : label;
                    }
                },
                border: { display: false }
            }
        },
        animation: {
            duration: 800,
            easing: 'easeOutQuart'
        }
    }
});
</script>

<style>
    .page-wrap { background: #f8fafc; min-height: 100vh; padding: 1.5rem; font-family: 'Inter', sans-serif; }
    
    /* Hover Lift Effect */
    .hover-lift { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease; }
    .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); }
</style>

@endsection