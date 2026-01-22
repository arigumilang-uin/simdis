@extends('layouts.app')

@section('title', 'Manajemen User')

@section('page-header')
    <x-page-header 
        title="Manajemen User" 
        subtitle="Kelola akun pengguna sistem."
    >
        <x-slot:actions>
            <a href="{{ route('users.trash') }}" class="btn btn-white">
                <x-ui.icon name="archive" size="16" />
                <span>Arsip</span>
            </a>
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <x-ui.icon name="plus" size="18" />
                <span>Tambah User</span>
            </a>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
@php
    $tableConfig = [
        'endpoint' => route('users.index'),
        'filters' => [
            'search' => request('search'),
            'role_id' => request('role_id')
        ],
        'containerId' => 'users-table-container'
    ];
@endphp

<div class="space-y-4" x-data='dataTable(@json($tableConfig))'>
    
    <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm overflow-hidden mb-8 border-b border-gray-200 md:border-b-0">
        {{-- Unified Toolbar --}}
        <div class="px-4 md:px-6 py-5 border-b border-gray-100 bg-white">
            <x-ui.action-bar :total="$users->total()" totalLabel="User" class="!gap-4">
                <x-slot:search>
                    <input 
                        type="text" 
                        x-model.debounce.500ms="filters.search"
                        class="w-full md:w-80 rounded-xl border-0 bg-gray-100/80 text-sm text-gray-800 py-2.5 pl-10 pr-4 hover:bg-gray-100 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:shadow-lg focus:shadow-indigo-500/5 transition-all duration-200 placeholder-gray-400"
                        placeholder="Cari username atau keterangan..."
                    >
                </x-slot:search>
                
                <x-slot:filters>
                    <x-ui.filter-select 
                        label="Peran"
                        x-model="filters.role_id"
                        :options="$roles ?? []"
                        optionValue="id"
                        optionLabel="nama_role"
                        placeholder="Semua Peran"
                    />
                </x-slot:filters>
                
                <x-slot:reset>
                    <x-ui.filter-reset @click="resetFilters(); filterOpen = false" />
                </x-slot:reset>
            </x-ui.action-bar>
        </div>
        
        {{-- Table --}}
        <div id="users-table-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
            @include('users._table')
        </div>
    </div>
</div>
@endsection
