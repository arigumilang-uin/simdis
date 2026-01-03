@extends('layouts.app')

@section('title', 'Dashboard Waka Kesiswaan')
@section('subtitle', 'Monitoring disiplin siswa dan kasus pembinaan.')
@section('page-header', false)

@section('content')
@php
    $initData = [
        'endpoint' => route('dashboard.admin'),
        'filters' => [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'jurusan_id' => request('jurusan_id'),
            'kelas_id' => request('kelas_id'),
        ],
        'defaults' => [
            'start_date' => date('Y-m-01'),
            'end_date' => date('Y-m-d'),
        ],
        'charts' => [
            'chartPelanggaran' => [
                'type' => 'doughnut',
                'data' => [
                    'labels' => $chartLabels ?? [],
                    'datasets' => [[
                        'data' => $chartData ?? [],
                        'backgroundColor' => ['#10b981', '#059669', '#34d399', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#6366f1'],
                        'borderWidth' => 0,
                        'hoverOffset' => 4
                    ]]
                ],
                'options' => [
                    'cutout' => '70%',
                    'plugins' => [
                        'legend' => ['position' => 'bottom', 'labels' => ['usePointStyle' => true, 'boxWidth' => 8, 'padding' => 20]]
                    ]
                ]
            ],
            'chartKelas' => [
                'type' => 'bar',
                'data' => [
                    'labels' => $chartKelasLabels ?? [],
                    'datasets' => [[
                        'label' => 'Jumlah Pelanggaran',
                        'data' => $chartKelasData ?? [],
                        'backgroundColor' => '#ef4444', 
                        'borderRadius' => 6,
                        'barThickness' => 20
                    ]]
                ],
                'options' => [
                    'indexAxis' => 'y',
                    'maintainAspectRatio' => false,
                    'responsive' => true,
                    'plugins' => ['legend' => ['display' => false]],
                    'scales' => [
                        'x' => [ 'beginAtZero' => true, 'grid' => ['display' => false] ],
                        'y' => [ 'grid' => ['borderDash' => [2, 2]], 'ticks' => ['font' => ['size' => 11]] ]
                    ]
                ]
            ]
        ]
    ];
@endphp

<div class="space-y-6" x-data="analyticsDashboard(@json($initData))">
    {{-- Welcome Banner --}}
    <div class="relative rounded-2xl bg-gradient-to-r from-slate-800 to-primary-900 p-6 overflow-hidden text-white shadow-xl shadow-primary-900/10">
        <div class="absolute top-0 right-0 w-64 h-64 bg-primary-500 opacity-10 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-accent-400 opacity-10 rounded-full blur-2xl -ml-10 -mb-10 pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-sm border border-white/10 text-xs font-medium text-primary-100 mb-3">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                    Waka Kesiswaan Panel
                </div>
                <h2 class="text-xl md:text-2xl font-bold">
                    Halo, {{ auth()->user()->username ?? 'Waka Kesiswaan' }}! 👋
                </h2>
                <p class="text-slate-300 text-sm opacity-90 mt-1">
                    Panel monitoring kedisiplinan dan pembinaan karakter siswa.
                </p>
            </div>
            
            <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/10 shadow-inner">
                <div class="bg-primary-500/20 p-2 rounded-lg text-primary-200">
                    <x-ui.icon name="calendar" size="24" />
                </div>
                <div>
                    <span class="block text-2xl font-bold leading-none tracking-tight">{{ date('d') }}</span>
                    <span class="block text-xs uppercase tracking-wider text-primary-100 opacity-80">{{ date('F Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div id="stats-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        @include('dashboards._waka_stats')
    </div>

    {{-- Filter Section (Collapsible) --}}
    <div class="card" x-data="{ expanded: {{ request()->hasAny(['start_date', 'end_date', 'jurusan_id', 'kelas_id']) ? 'true' : 'false' }} }">
        <div class="card-header cursor-pointer" @click="expanded = !expanded">
            <div class="flex items-center gap-2">
                <x-ui.icon name="filter" size="18" class="text-gray-400" />
                <span class="card-title">Filter Analisis Data</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500" x-show="isLoading">Memuat Data...</span>
                <x-ui.icon name="chevron-down" size="20" class="text-gray-400 transition-transform" ::class="{ 'rotate-180': expanded }" />
            </div>
        </div>
        <div class="card-body" x-show="expanded" x-collapse>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                <div class="form-group">
                    <label for="start_date" class="form-label">Dari Tanggal</label>
                    <input type="date" x-model="filters.start_date" class="form-input">
                </div>
                <div class="form-group">
                    <label for="end_date" class="form-label">Sampai Tanggal</label>
                    <input type="date" x-model="filters.end_date" class="form-input">
                </div>
                <div class="form-group">
                    <label for="jurusan_id" class="form-label">Jurusan</label>
                    <select x-model="filters.jurusan_id" class="form-input form-select">
                        <option value="">Semua Jurusan</option>
                        @foreach($allJurusan ?? [] as $j)
                            <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="kelas_id" class="form-label">Kelas</label>
                    <select x-model="filters.kelas_id" class="form-input form-select">
                        <option value="">Semua Kelas</option>
                        @foreach($allKelas ?? [] as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group flex justify-end">
                    <button type="button" @click="resetFilters()" class="btn btn-secondary w-full">
                        <x-ui.icon name="refresh-cw" size="14" />
                        <span>Reset</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        {{-- Pelanggaran Populer --}}
        <div class="card h-full flex flex-col">
            <div class="card-header border-b border-gray-100">
                <h3 class="card-title">Statistik Pelanggaran</h3>
                <span class="text-xs text-gray-500 font-normal">Top 10 Jenis</span>
            </div>
            <div class="card-body flex-1 min-h-[300px] relative">
                <canvas id="chartPelanggaran"></canvas>
            </div>
        </div>
        
        {{-- Kelas Ternakal --}}
        <div class="card h-full flex flex-col">
            <div class="card-header border-b border-gray-100">
                <h3 class="card-title">Statistik Kelas</h3>
                <span class="text-xs text-gray-500 font-normal">Top 10 Kelas Pelanggaran</span>
            </div>
            <div class="card-body flex-1 min-h-[300px] relative">
                <canvas id="chartKelas"></canvas>
            </div>
        </div>
    </div>
    
    {{-- Kasus Terbaru Table --}}
    <div id="table-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        @include('dashboards._waka_table')
    </div>
</div>
@endsection
