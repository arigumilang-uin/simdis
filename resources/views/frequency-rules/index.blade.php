@extends('layouts.app')

@section('title', 'Kelola Aturan Pelanggaran')
@section('subtitle', 'Atur jenis pelanggaran, poin, sanksi, dan frequency rules.')
@section('page-header', true)

@section('content')
@php
    $tableConfig = [
        'endpoint' => route('frequency-rules.index'),
        'containerId' => 'frequency-rules-table-container',
        'filters' => [
            'kategori_id' => $kategoriId ?? request('kategori_id')
        ]
    ];
@endphp

<div class="space-y-6" x-data='dataTable(@json($tableConfig))'>
    {{-- Action Button --}}
    <div class="flex justify-end">
        <a href="{{ route('jenis-pelanggaran.create') }}" class="btn btn-primary">
            <x-ui.icon name="plus" size="18" />
            <span>Tambah Jenis Pelanggaran</span>
        </a>
    </div>

    {{-- Filter --}}
    <div class="card" x-data="{ expanded: {{ request()->has('kategori_id') ? 'true' : 'false' }} }">
        <div class="card-header cursor-pointer" @click="expanded = !expanded">
            <div class="flex items-center gap-2">
                <x-ui.icon name="filter" size="18" class="text-gray-400" />
                <span class="card-title">Filter Data</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500" x-show="isLoading">Memuat...</span>
                <x-ui.icon name="chevron-down" size="20" class="text-gray-400 transition-transform" ::class="{ 'rotate-180': expanded }" />
            </div>
        </div>
        
        <div x-show="expanded" x-collapse.duration.300ms x-cloak>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <x-forms.select 
                            name="kategori_id" 
                            label="Filter Kategori" 
                            x-model="filters.kategori_id"
                        >
                            <option value="">Semua Kategori</option>
                            @foreach($kategoris ?? [] as $kat)
                                <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    
                    <div class="md:col-span-4 flex justify-end">
                        <button type="button" @click="resetFilters()" class="btn btn-secondary text-xs">
                            <x-ui.icon name="refresh-cw" size="14" />
                            <span>Reset Filter</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div id="frequency-rules-table-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        @include('frequency-rules._table')
    </div>
</div>
@endsection

