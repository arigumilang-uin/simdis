@extends('layouts.app')

@section('page-header', false)

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
    $jsonData = json_encode($initData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
@endphp

<div class="space-y-6" x-data="analyticsDashboard({{ $jsonData }})">
    {{-- Banner --}}
    <x-dashboard.banner 
        variant="amber" 
        title="Halo, {{ auth()->user()->username ?? 'Waka Sarana' }}! 👋" 
        subtitle="Monitoring kedisiplinan dan fasilitas sekolah."
        badge="Waka Sarana Panel"
        showDate="true"
    />

    {{-- Statistics Cards --}}
    <div id="stats-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        <x-dashboard.stat-card 
            label="Total Siswa" 
            value="{{ number_format($totalSiswa ?? 0) }}" 
            icon="users"
            color="blue"
        />
        
        <x-dashboard.stat-card 
            label="Pelanggaran" 
            value="{{ number_format($totalPelanggaran ?? 0) }}" 
            icon="alert-circle"
            color="rose"
            trend="up"
            trendValue="Periode Ini"
        />
        
        <x-dashboard.stat-card 
            label="Kasus Aktif" 
            value="{{ number_format($kasusAktif ?? 0) }}" 
            icon="clipboard"
            color="amber"
        />
        
        <x-dashboard.stat-card 
            label="Total Kasus" 
            value="{{ number_format($totalKasus ?? 0) }}" 
            icon="check-circle"
            color="emerald"
        />
    </div>
    
    {{-- Filter & Charts Container --}}
    <div class="grid grid-cols-1 gap-6">
        {{-- Filter --}}
        <x-dashboard.filter-card title="Filter Data" columns="3">
            <x-forms.date 
                name="start_date" 
                label="Dari Tanggal" 
                x-model="filters.start_date" 
            />
            
            <x-forms.date 
                name="end_date" 
                label="Sampai" 
                x-model="filters.end_date" 
            />
            
            <div class="form-group">
                <button type="button" @click="resetFilters()" class="btn btn-secondary w-full">
                     <x-ui.icon name="refresh-cw" :size="14" />
                     <span>Reset</span>
                </button>
            </div>
        </x-dashboard.filter-card>
        
        {{-- Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
            <x-dashboard.chart-card 
                title="Top 10 Pelanggaran" 
                chartId="chartPelanggaran"
            />
            
            <x-dashboard.chart-card 
                title="Top 10 Kelas" 
                chartId="chartKelas"
            />
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
                                        $statusValue = $kasus->status->value ?? $kasus->status;
                                    @endphp
                                    <span class="badge {{ $statusColors[$statusValue] ?? 'badge-neutral' }}">{{ $statusValue }}</span>
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
