@extends('layouts.app')

@section('title', 'Dashboard Wali Kelas')
@section('subtitle', 'Monitoring siswa kelas {{ $kelas->nama_kelas ?? "" }}')
@section('page-header', false)

@section('content')
@php
    $initData = [
        'endpoint' => route('dashboard.walikelas'),
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
            ]
        ]
    ];
@endphp

<div class="space-y-6" x-data='analyticsDashboard(@json($initData))'>
    {{-- Class Info Banner --}}
    <x-dashboard.banner 
        variant="primary" 
        title="{{ $kelas->nama_kelas ?? 'Belum ada kelas' }}" 
        subtitle="{{ $kelas->jurusan->nama_jurusan ?? '' }}"
        badge="Wali Kelas Panel"
    >
        <a href="{{ route('siswa.index') }}" class="btn bg-white/10 backdrop-blur-md text-white border border-white/20 hover:bg-white/20 shadow-lg group">
            <x-ui.icon name="users" :size="18" />
            <span>Lihat Data Siswa</span>
            <x-ui.icon name="arrow-right" :size="16" class="ml-2 group-hover:translate-x-1 transition-transform" />
        </a>
    </x-dashboard.banner>
    
    {{-- Statistics Cards --}}
    <div id="stats-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        @include('dashboards._walikelas_stats')
    </div>
    
    {{-- Filter Data (Date Only) --}}
    <x-dashboard.filter-card title="Filter Periode" columns="3">
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

        <div class="form-group">
            <button type="button" @click="resetFilters()" class="btn btn-secondary w-full">
                <x-ui.icon name="refresh" :size="14" />
                <span>Reset</span>
            </button>
        </div>
    </x-dashboard.filter-card>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        {{-- Pelanggaran Chart --}}
        <x-dashboard.chart-card 
            title="Statistik Pelanggaran" 
            subtitle="Top 10 Jenis" 
            chartId="chartPelanggaran"
            centered="true"
        />
        
        {{-- Kasus Terbaru --}}
        <div id="table-container" class="h-full">
            @include('dashboards._walikelas_table')
        </div>
    </div>
    
    {{-- Quick Actions --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-dashboard.quick-action 
            title="Catat Pelanggaran" 
            description="Input pelanggaran siswa baru"
            icon="file-text"
            color="primary"
            href="{{ route('riwayat.create') }}"
        />
        
        <x-dashboard.quick-action 
            title="Siswa Pembinaan" 
            description="Lihat milestone pembinaan"
            icon="shield-check"
            color="primary"
            href="{{ route('pembinaan.index') }}"
        />
    </div>
</div>
@endsection
