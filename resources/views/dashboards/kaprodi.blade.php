@extends('layouts.app')

@section('title', 'Dashboard Kaprodi')
@section('subtitle', 'Monitoring siswa jurusan {{ $jurusan->nama_jurusan ?? "" }}')
@section('page-header', false)

@section('content')
@php
    $initData = [
        'endpoint' => route('dashboard.kaprodi'),
        'filters' => [
            'start_date' => $startDate,
            'end_date' => $endDate,
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
                        'backgroundColor' => [
                            '#3b82f6', '#10b981', '#f59e0b', '#ef4444', 
                            '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', 
                            '#6366f1', '#22c55e'
                        ],
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
            ]
        ]
    ];
@endphp

<div class="space-y-6" x-data='analyticsDashboard(@json($initData))'>
    {{-- Jurusan Info Banner --}}
    <x-dashboard.banner 
        variant="violet" 
        title="{{ $jurusan->nama_jurusan ?? 'Belum ada jurusan' }}" 
        subtitle="Jurusan yang Diampu"
        badge="Kaprodi Panel"
    >
        <a href="{{ route('siswa.index') }}" class="btn bg-white/10 backdrop-blur-md text-white border border-white/20 hover:bg-white/20 shadow-lg group">
            <x-ui.icon name="users" :size="18" />
            <span>Lihat Data Siswa</span>
            <x-ui.icon name="arrow-right" :size="16" class="ml-2 group-hover:translate-x-1 transition-transform" />
        </a>
    </x-dashboard.banner>
    
    {{-- Statistics Cards (Dynamic) --}}
    <div id="stats-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        @include('dashboards._kaprodi_stats')
    </div>
    
    {{-- Filter Card (Alpine) --}}
    <x-dashboard.filter-card title="Filter Data" columns="4">
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
            name="kelas_id" 
            label="Kelas" 
            :options="$kelasJurusan" 
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
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        {{-- Chart --}}
        <x-dashboard.chart-card 
            title="Top 10 Pelanggaran di Jurusan" 
            subtitle="Sering Terjadi" 
            chartId="chartPelanggaran"
            centered="true"
        />
        
        {{-- Kasus Terbaru Table --}}
        <div id="table-container" class="h-full">
            @include('dashboards._kaprodi_table')
        </div>
    </div>
</div>
@endsection
