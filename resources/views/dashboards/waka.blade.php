@extends('layouts.app')

@section('page-header', false)

@section('content')
@php
    $initData = [
        'endpoint' => route('dashboard.waka'),
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
    $jsonData = json_encode($initData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
@endphp

<div class="space-y-6" x-data="analyticsDashboard({{ $jsonData }})">
    {{-- Welcome Banner --}}
    <x-dashboard.banner 
        variant="slate" 
        title="Halo, {{ auth()->user()->username ?? 'Waka Kesiswaan' }}! 👋" 
        subtitle="Panel monitoring kedisiplinan dan pembinaan karakter siswa."
        badge="Waka Kesiswaan Panel"
        showDate="true"
    />

    {{-- Statistics Cards --}}
    <div id="stats-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        @include('dashboards._waka_stats')
    </div>

    {{-- Filter Section --}}
    <x-dashboard.filter-card title="Filter Analisis Data" columns="5">
        <x-forms.date 
            name="start_date" 
            label="Dari Tanggal" 
            x-model="filters.start_date" 
        />
        
        <x-forms.date 
            name="end_date" 
            label="Sampai Tanggal" 
            x-model="filters.end_date" 
        />
        
        <x-forms.select 
            name="jurusan_id" 
            label="Jurusan" 
            :options="$allJurusan" 
            optionValue="id" 
            optionLabel="nama_jurusan"
            placeholder="Semua Jurusan"
            x-model="filters.jurusan_id" 
        />
        
        <x-forms.select 
            name="kelas_id" 
            label="Kelas" 
            :options="$allKelas" 
            optionValue="id" 
            optionLabel="nama_kelas"
            placeholder="Semua Kelas"
            x-model="filters.kelas_id" 
        />

        <div class="form-group">
            <button type="button" @click="resetFilters()" class="btn btn-secondary w-full">
                <x-ui.icon name="refresh-cw" :size="14" />
                <span>Reset</span>
            </button>
        </div>
    </x-dashboard.filter-card>
    
    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        {{-- Pelanggaran Populer --}}
        <x-dashboard.chart-card 
            title="Statistik Pelanggaran" 
            subtitle="Top 10 Jenis" 
            chartId="chartPelanggaran"
        />
        
        {{-- Kelas Ternakal --}}
        <x-dashboard.chart-card 
            title="Statistik Kelas" 
            subtitle="Top 10 Kelas Pelanggaran" 
            chartId="chartKelas"
        />
    </div>
    
    {{-- Kasus Terbaru Table --}}
    <div id="table-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        @include('dashboards._waka_table')
    </div>
</div>
@endsection
