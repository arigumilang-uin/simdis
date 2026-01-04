@extends('layouts.app')

@section('title', 'Manajemen Konsentrasi Keahlian')

@section('page-header')
    <x-page-header 
        title="Manajemen Konsentrasi" 
        subtitle="Kelola data konsentrasi keahlian per jurusan."
        :total="$konsentrasiList->total()"
    />
@endsection

@section('content')
@php
    $tableConfig = [
        'endpoint' => route('konsentrasi.index'),
        'filters' => [
            'search' => request('search'),
            'jurusan_id' => request('jurusan_id')
        ],
        'containerId' => 'konsentrasi-table-container'
    ];
@endphp

<div class="space-y-6" x-data='dataTable(@json($tableConfig))'>
    {{-- Action Button --}}
    <div class="flex justify-end">
        <a href="{{ route('konsentrasi.create') }}" class="btn btn-primary">
            <x-ui.icon name="plus" size="18" />
            <span>Tambah Konsentrasi</span>
        </a>
    </div>

    {{-- Filter Card --}}
    <div class="card" x-data="{ expanded: {{ request()->hasAny(['search', 'jurusan_id']) ? 'true' : 'false' }} }">
        <div class="card-header cursor-pointer" @click="expanded = !expanded">
            <div class="flex items-center gap-2">
                <x-ui.icon name="filter" class="text-gray-400" size="18" />
                <span class="card-title">Filter Data</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500" x-show="isLoading">Memuat...</span>
                <x-ui.icon name="chevron-down" size="20" class="text-gray-400 transition-transform" ::class="{ 'rotate-180': expanded }" />
            </div>
        </div>
        
        <div x-show="expanded" x-collapse.duration.300ms x-cloak>
            <div class="card-body border-t border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Search --}}
                    <div class="form-group">
                        <x-forms.input
                            name="search"
                            label="Cari"
                            x-model.debounce.500ms="filters.search"
                            placeholder="Nama/Kode konsentrasi..."
                        />
                    </div>
                    
                    {{-- Jurusan --}}
                    <div class="form-group">
                        <x-forms.select
                            name="jurusan_id" 
                            label="Jurusan"
                            x-model="filters.jurusan_id"
                            :options="$jurusanList"
                            optionValue="id"
                            optionLabel="nama_jurusan"
                            placeholder="Semua Jurusan"
                        />
                    </div>
                    
                    {{-- Empty space for alignment --}}
                    <div class="hidden md:block"></div>
                    
                    {{-- Actions --}}
                    <div class="md:col-span-3 flex justify-end">
                        <button type="button" @click="resetFilters()" class="btn btn-secondary text-xs">
                            <x-ui.icon name="refresh-cw" size="14" />
                            <span>Reset Filter</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table Container for AJAX --}}
    <div id="konsentrasi-table-container">
        @include('konsentrasi._table')
    </div>
</div>
@endsection
