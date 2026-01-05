@extends('layouts.app')

@section('page-header', false)

@section('content')
@php
    // Simplified config - chart data comes from controller
    $initData = [
        'endpoint' => route('dashboard.kepsek'),
        'filters' => [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'jurusan_id' => request('jurusan_id'),
            'kelas_id' => request('kelas_id'),
            'chart_mode' => $chartMode ?? 'trend',
        ],
        'defaults' => [
            'start_date' => date('Y-m-01'),
            'end_date' => date('Y-m-d'),
            'chart_mode' => 'trend',
        ],
    ];
    $jsonData = json_encode($initData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
@endphp

<div class="space-y-6" x-data="analyticsDashboard({{ $jsonData }})">
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
    
    {{-- Filter Data --}}
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
    
    {{-- Unified Chart - Single Chart with Mode Selector --}}
    <div class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        <x-dashboard.unified-chart 
            :chartData="$chartData"
            chartId="mainChart"
            minHeight="400px"
        />
    </div>
    
    {{-- Kasus Menunggu Persetujuan --}}
    <div id="table-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
         @include('dashboards._kepsek_table')
    </div>
</div>
@endsection
