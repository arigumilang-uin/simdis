@extends('layouts.app')

@section('title', 'Dashboard Waka Sarana')
@section('subtitle', 'Monitoring kedisiplinan dan fasilitas.')
@section('page-header', true)

@section('content')
@php
    $initData = [
        'endpoint' => route('dashboard.waka-sarana'),
        'filters' => [
            'start_date' => $startDate,
            'end_date' => $endDate,
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
                        'backgroundColor' => [
                            'rgba(59,130,246,0.8)','rgba(16,185,129,0.8)',
                            'rgba(245,158,11,0.8)','rgba(239,68,68,0.8)',
                            'rgba(139,92,246,0.8)','rgba(236,72,153,0.8)',
                            'rgba(20,184,166,0.8)','rgba(249,115,22,0.8)',
                            'rgba(99,102,241,0.8)','rgba(34,197,94,0.8)'
                        ],
                        'borderWidth' => 0
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
                        'label' => 'Pelanggaran',
                        'data' => $chartKelasData ?? [],
                        'backgroundColor' => 'rgba(239,68,68,0.8)', // Red
                        'borderRadius' => 6
                    ]]
                ],
                'options' => [
                    'indexAxis' => 'y',
                    'responsive' => true,
                    'maintainAspectRatio' => false,
                    'plugins' => ['legend' => ['display' => false]],
                    'scales' => [
                        'x' => [ 'beginAtZero' => true ]
                    ]
                ]
            ]
        ]
    ];
@endphp

<div class="space-y-6" x-data="analyticsDashboard(@json($initData))">
    {{-- Statistics Cards (With Loading State) --}}
    <div id="stats-container" class="grid grid-cols-2 md:grid-cols-4 gap-4" :class="{ 'opacity-50': isLoading }">
        <div class="stat-card">
                <x-ui.icon name="users" size="24" />
            <div class="stat-card-content">
                <p class="stat-card-label">Total Siswa</p>
                <p class="stat-card-value">{{ number_format($totalSiswa ?? 0) }}</p>
            </div>
        </div>
        
        <div class="stat-card">
                <x-ui.icon name="alert-circle" size="24" />
            <div class="stat-card-content">
                <p class="stat-card-label">Pelanggaran</p>
                <p class="stat-card-value">{{ number_format($totalPelanggaran ?? 0) }}</p>
                <p class="text-xs text-gray-500 mt-1">Periode ini</p>
            </div>
        </div>
        
        <div class="stat-card">
                <x-ui.icon name="clipboard" size="24" />
            <div class="stat-card-content">
                <p class="stat-card-label">Kasus Aktif</p>
                <p class="stat-card-value">{{ number_format($kasusAktif ?? 0) }}</p>
            </div>
        </div>
        
        <div class="stat-card">
                <x-ui.icon name="check-circle" size="24" />
            <div class="stat-card-content">
                <p class="stat-card-label">Total Kasus</p>
                <p class="stat-card-value">{{ number_format($totalKasus ?? 0) }}</p>
            </div>
        </div>
    </div>
    
    {{-- Filter & Charts Container --}}
    <div class="grid grid-cols-1 gap-6">
        {{-- Filter --}}
        <div class="card" x-data="{ expanded: {{ request()->hasAny(['start_date', 'end_date']) ? 'true' : 'false' }} }">
             <div class="card-header cursor-pointer select-none" @click="expanded = !expanded">
                <div class="flex items-center gap-2">
                    <x-ui.icon name="filter" size="16" class="text-gray-400" />
                    <h3 class="card-title">Filter Data</h3>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500" x-show="isLoading">Memuat Data...</span>
                     <x-ui.icon name="chevron-down" size="20" class="text-gray-400 transition-transform" ::class="{ 'rotate-180': expanded }" />
                </div>
            </div>
            <div class="card-body" x-show="expanded" x-collapse>
                <div class="flex flex-wrap gap-4 items-end">
                    <div class="form-group flex-1 min-w-[150px]">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" x-model="filters.start_date" class="form-input">
                    </div>
                    <div class="form-group flex-1 min-w-[150px]">
                        <label class="form-label">Sampai</label>
                        <input type="date" x-model="filters.end_date" class="form-input">
                    </div>
                    <div class="form-group">
                        <button type="button" @click="resetFilters()" class="btn btn-secondary">
                             <x-ui.icon name="refresh-cw" size="14" />
                             reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" :class="{ 'opacity-50': isLoading }">
            <div class="card h-full flex flex-col">
                <div class="card-header border-b border-gray-100">
                    <h3 class="card-title">Top 10 Pelanggaran</h3>
                </div>
                <div class="card-body flex-1 min-h-[300px] relative">
                    <canvas id="chartPelanggaran"></canvas>
                </div>
            </div>
            
            <div class="card h-full flex flex-col">
                <div class="card-header border-b border-gray-100">
                    <h3 class="card-title">Top 10 Kelas</h3>
                </div>
                <div class="card-body flex-1 min-h-[300px] relative">
                    <canvas id="chartKelas"></canvas>
                </div>
            </div>
        </div>
        
        {{-- Kasus Terbaru --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Kasus Terbaru</h3>
                <a href="{{ route('tindak-lanjut.index') }}" class="btn btn-sm btn-secondary">Lihat Semua</a>
            </div>
            <div class="table-container !rounded-none !border-0">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th class="w-20">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kasusBaru ?? [] as $kasus)
                            <tr>
                                <td class="font-medium">{{ $kasus->siswa->nama_siswa ?? '-' }}</td>
                                <td><span class="badge badge-primary">{{ $kasus->siswa->kelas->nama_kelas ?? '-' }}</span></td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'Baru' => 'badge-info',
                                            'Menunggu Persetujuan' => 'badge-warning',
                                            'Disetujui' => 'badge-success',
                                            'Ditangani' => 'badge-primary',
                                        ];
                                    @endphp
                                    <span class="badge {{ $statusColors[$kasus->status] ?? 'badge-neutral' }}">{{ $kasus->status }}</span>
                                </td>
                                <td class="text-gray-500 text-sm">{{ $kasus->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('tindak-lanjut.show', $kasus->id) }}" class="btn btn-icon btn-outline">
                                        <x-ui.icon name="eye" size="16" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-8 text-gray-400">Tidak ada kasus dalam periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
