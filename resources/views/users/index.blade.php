@extends('layouts.app')

@section('title', 'Manajemen User')

@section('page-header')
    <x-page-header 
        title="Manajemen User" 
        subtitle="Kelola akun pengguna sistem."
        :total="$users->total()"
        icon="users"
    />
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

<div class="space-y-6" x-data='dataTable(@json($tableConfig))'>
    {{-- Action Button --}}
    <div class="flex justify-end">
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <x-ui.icon name="plus" size="18" />
            <span>Tambah User</span>
        </a>
    </div>
    {{-- Filter --}}
    <div class="card" x-data="{ expanded: {{ request()->hasAny(['search', 'role_id']) ? 'true' : 'false' }} }">
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
            <div class="card-body border-t border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="form-group md:col-span-2">
                        <label for="search" class="form-label">Cari</label>
                        <div class="relative">
                            <input 
                                type="text" 
                                id="search" 
                                x-model.debounce.500ms="filters.search" 
                                class="form-input pr-10 w-full" 
                                placeholder="Cari username atau keterangan..."
                            >
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" x-show="isLoading">
                                <x-ui.icon name="loader" size="16" class="animate-spin text-gray-400" />
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="role" class="form-label">Peran</label>
                        <select id="role" x-model="filters.role_id" class="form-input form-select w-full">
                            <option value="">Semua Peran</option>
                            @foreach($roles ?? [] as $role)
                                <option value="{{ $role->id }}">{{ $role->nama_role }}</option>
                            @endforeach
                        </select>
                    </div>
                    
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
    
    {{-- Table --}}
    <div id="users-table-container" class="transition-opacity duration-200" :class="{ 'opacity-50': isLoading }">
        @include('users._table')
    </div>
</div>
@endsection
