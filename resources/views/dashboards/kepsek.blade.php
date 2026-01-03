@extends('layouts.app')

@section('title', 'Dashboard Kepala Sekolah')
@section('subtitle', 'Ringkasan eksekutif dan monitoring kedisiplinan siswa.')
@section('page-header', false)

@section('content')
@php
    // Konfigurasi Dashboard Terpusat
    // Semua logika tampilan chart didefinisikan di sini, JS hanya merender.
    $initData = [
        'endpoint' => route('dashboard.kepsek'),
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
            'chartTrend' => [
                'type' => 'line',
                'data' => [
                    'labels' => $chartTrendLabels ?? [],
                    'datasets' => [[
                        'label' => 'Jumlah Pelanggaran',
                        'data' => $chartTrendData ?? [],
                        'borderColor' => '#059669', // Primary Green
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                        'pointRadius' => 4,
                        'pointBackgroundColor' => '#059669'
                    ]]
                ],
                'options' => [
                    'scales' => [
                        'y' => [ 'beginAtZero' => true, 'grid' => ['borderDash' => [2, 2]] ],
                        'x' => [ 'grid' => ['display' => false] ]
                    ]
                ]
            ],
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
                    'cutout' => '70%'
                ]
            ],
            'chartJurusan' => [
                'type' => 'bar',
                'data' => [
                    'labels' => $chartJurusanLabels ?? [],
                    'datasets' => [[
                        'label' => 'Jumlah Pelanggaran',
                        'data' => $chartJurusanData ?? [],
                        'backgroundColor' => '#059669',
                        'borderRadius' => 6
                    ]]
                ],
                'options' => [
                    'plugins' => ['legend' => ['display' => false]],
                    'scales' => [
                        'y' => [ 'beginAtZero' => true, 'grid' => ['borderDash' => [2, 2]] ],
                        'x' => [ 'grid' => ['display' => false] ]
                    ]
                ]
            ]
        ]
    ];
@endphp

<div class="space-y-6" x-data='analyticsDashboard(@json($initData))'>
    {{-- Welcome Banner --}}
    <x-dashboard.banner 
        variant="slate" 
        title="Selamat Datang, Kepala Sekolah! 👋" 
        subtitle="Ringkasan statistik kedisiplinan dan kinerja sekolah."
        badge="Executive Panel"
        showDate="true"
    />
    
    {{-- Statistics Cards --}}
    <div id="stats-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        @include('dashboards._kepsek_stats')
    </div>
    
    {{-- Filter Data (Automatic Watch) --}}
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
                <x-ui.icon name="refresh" :size="14" />
                <span>Reset</span>
            </button>
        </div>
    </x-dashboard.filter-card>
    
    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        {{-- Trend Chart --}}
        <div class="lg:col-span-2">
            <x-dashboard.chart-card 
                title="Tren Pelanggaran" 
                subtitle="History Bulanan" 
                chartId="chartTrend"
            />
        </div>
        
        {{-- Pelanggaran Populer --}}
        <x-dashboard.chart-card 
            title="Top Pelanggaran" 
            subtitle="Sering Terjadi" 
            chartId="chartPelanggaran"
        />
    </div>
    
    {{-- Jurusan Chart --}}
    <div class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        <x-dashboard.chart-card 
            title="Sebaran Per Jurusan" 
            chartId="chartJurusan"
            minHeight="250px"
        />
    </div>
    
    {{-- Kasus Menunggu Persetujuan --}}
    <div id="table-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
         @include('dashboards._kepsek_table')
    </div>
</div>
@endsection
