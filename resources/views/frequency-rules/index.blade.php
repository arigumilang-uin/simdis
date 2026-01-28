@extends('layouts.app')

@section('title', 'Kelola Aturan Pelanggaran')

@section('page-header')
    <x-page-header 
        title="Kelola Aturan Pelanggaran" 
        subtitle="Atur jenis pelanggaran, poin, sanksi, dan frequency rules."
    >
        <x-slot:actions>
            <a href="{{ route('jenis-pelanggaran.create') }}" class="btn btn-primary">
                <x-ui.icon name="plus" size="18" />
                <span>Tambah Jenis Pelanggaran</span>
            </a>
        </x-slot:actions>
    </x-page-header>
@endsection

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

<div class="space-y-4" x-data='dataTable(@json($tableConfig))'
    x-init="$watch('selectAll', () => toggleSelectAll())"
    @toggle-selection-mode.window="selectionMode = $event.detail !== undefined ? $event.detail : !selectionMode"
    @enter-selection.window="selectionMode = true; if (!selected.includes(String($event.detail.id))) selected.push(String($event.detail.id))"
>
    
    <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm overflow-hidden mb-8 border-b border-gray-200 md:border-b-0">
        {{-- Toolbar --}}
        <div class="px-4 md:px-6 py-5 border-b border-gray-100 bg-white">
            <x-ui.action-bar :total="$jenisPelanggaran->count()" totalLabel="Aturan" class="!gap-4">
                <x-slot:filters>
                    <div class="space-y-3">
                        <label class="text-xs font-semibold text-gray-500 uppercase">Kategori</label>
                        <select x-model="filters.kategori_id" class="form-select w-full text-sm rounded-lg">
                            <option value="">Semua Kategori</option>
                            @foreach($kategoris ?? [] as $kat)
                                <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                </x-slot:filters>
                <x-slot:reset>
                    <button type="button" @click="resetFilters()" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Reset</button>
                </x-slot:reset>
            </x-ui.action-bar>
        </div>

        {{-- Table --}}
        <div id="frequency-rules-table-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
            @include('frequency-rules._table')
        </div>
    </div>
</div>
@endsection

