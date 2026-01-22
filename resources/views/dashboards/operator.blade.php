@extends('layouts.app')

@section('page-header', false)

@section('content')
<div class="space-y-6">
    {{-- Welcome Banner --}}
    <div class="relative rounded-2xl bg-gradient-to-r from-slate-800 to-primary-900 p-6 overflow-hidden text-white shadow-xl shadow-primary-900/10">
        {{-- Decorative elements --}}
        <div class="absolute top-0 right-0 w-64 h-64 bg-primary-500 opacity-10 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-accent-400 opacity-10 rounded-full blur-2xl -ml-10 -mb-10 pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-sm border border-white/10 text-xs font-medium text-primary-100 mb-3">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                    System Active
                </div>
                <h2 class="text-xl md:text-2xl font-bold">
                    Halo, {{ auth()->user()->username ?? 'Operator' }}! 👋
                </h2>
                <p class="text-slate-300 text-sm opacity-90 mt-1">
                    Selamat bekerja, data hari ini siap dikelola.
                </p>
            </div>
            
            <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/10 shadow-inner">
                <div class="bg-primary-500/20 p-2 rounded-lg text-primary-200">
                    <x-ui.icon name="calendar" size="24" />
                </div>
                <div>
                    <span class="block text-2xl font-bold leading-none tracking-tight">{{ date('d') }}</span>
                    <span class="block text-xs uppercase tracking-wider text-primary-100 opacity-80">{{ date('F Y') }}</span>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Statistics Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Total Users --}}
        <a href="{{ route('users.index') }}" class="stat-card group">
            <div class="stat-card-icon primary">
                    <x-ui.icon name="users" size="24" />
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Total Pengguna</p>
                <p class="stat-card-value">{{ $totalUser ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Akun Terdaftar</p>
            </div>
        </a>
        
        {{-- Total Siswa --}}
        <a href="{{ route('siswa.index') }}" class="stat-card group">
            <div class="stat-card-icon success">
                    <x-ui.icon name="graduation" size="24" />
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Total Siswa</p>
                <p class="stat-card-value">{{ $totalSiswa ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Data Pokok</p>
            </div>
        </a>
        
        {{-- Total Pelanggaran --}}
        <a href="{{ route('riwayat.index') }}" class="stat-card group">
            <div class="stat-card-icon danger">
                    <x-ui.icon name="alert-circle" size="24" />
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Pelanggaran</p>
                <p class="stat-card-value">{{ $totalAturan ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Jenis Poin</p>
            </div>
        </a>
        
        {{-- Total Kelas --}}
        <a href="{{ route('kelas.index') }}" class="stat-card group">
            <div class="stat-card-icon warning">
                    <x-ui.icon name="building" size="24" />
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Rombel</p>
                <p class="stat-card-value">{{ $totalKelas ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Kelas Aktif</p>
            </div>
        </a>
    </div>
    
    {{-- Quick Actions --}}
    <div>
        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Aksi Cepat</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {{-- Add User --}}
            <a href="{{ route('users.create') }}" class="card flex items-center gap-4 p-4 hover:border-primary-200 hover:shadow-lg hover:shadow-primary-100/50 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                    <x-ui.icon name="user-plus" size="24" />
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-gray-800 group-hover:text-primary-600 transition-colors">Tambah Pengguna Baru</h4>
                    <p class="text-sm text-gray-500">Guru, Staff, atau Wali Murid</p>
                </div>
                <x-ui.icon name="chevron-right" size="20" class="text-gray-300 group-hover:text-primary-500 group-hover:translate-x-1 transition-all" />
            </a>
            
            {{-- Add Siswa --}}
            <a href="{{ route('siswa.create') }}" class="card flex items-center gap-4 p-4 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-100/50 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                    <x-ui.icon name="graduation" size="24" />
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-gray-800 group-hover:text-emerald-600 transition-colors">Tambah Data Siswa</h4>
                    <p class="text-sm text-gray-500">Input data siswa baru</p>
                </div>
                <x-ui.icon name="chevron-right" size="20" class="text-gray-300 group-hover:text-emerald-500 group-hover:translate-x-1 transition-all" />
            </a>
            
            {{-- Search Siswa --}}
            <a href="{{ route('siswa.index') }}" class="card flex items-center gap-4 p-4 hover:border-violet-200 hover:shadow-lg hover:shadow-violet-100/50 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                    <x-ui.icon name="search" size="24" />
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-gray-800 group-hover:text-violet-600 transition-colors">Cari Data Siswa</h4>
                    <p class="text-sm text-gray-500">Lihat pelanggaran & profil</p>
                </div>
                <x-ui.icon name="chevron-right" size="20" class="text-gray-300 group-hover:text-violet-500 group-hover:translate-x-1 transition-all" />
            </a>
            
            {{-- Bulk Import --}}
            <a href="{{ route('siswa.bulk-create') }}" class="card flex items-center gap-4 p-4 hover:border-amber-200 hover:shadow-lg hover:shadow-amber-100/50 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                    <x-ui.icon name="upload" size="24" />
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-gray-800 group-hover:text-amber-600 transition-colors">Import Siswa</h4>
                    <p class="text-sm text-gray-500">Upload data Excel/CSV</p>
                </div>
                <x-ui.icon name="chevron-right" size="20" class="text-gray-300 group-hover:text-amber-500 group-hover:translate-x-1 transition-all" />
            </a>
            
            {{-- Log Pelanggaran --}}
            <a href="{{ route('riwayat.index') }}" class="card flex items-center gap-4 p-4 hover:border-rose-200 hover:shadow-lg hover:shadow-rose-100/50 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                    <x-ui.icon name="clock" size="24" />
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-gray-800 group-hover:text-rose-600 transition-colors">Log Pelanggaran</h4>
                    <p class="text-sm text-gray-500">Lihat riwayat pelanggaran</p>
                </div>
                <x-ui.icon name="chevron-right" size="20" class="text-gray-300 group-hover:text-rose-500 group-hover:translate-x-1 transition-all" />
            </a>
            
            {{-- Audit Log --}}
            <a href="{{ route('audit.activity.index') }}" class="card flex items-center gap-4 p-4 hover:border-slate-200 hover:shadow-lg hover:shadow-slate-100/50 transition-all group">
                <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                    <x-ui.icon name="activity" size="24" />
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-gray-800 group-hover:text-slate-700 transition-colors">Audit Log</h4>
                    <p class="text-sm text-gray-500">Aktivitas sistem</p>
                </div>
                <x-ui.icon name="chevron-right" size="20" class="text-gray-300 group-hover:text-slate-500 group-hover:translate-x-1 transition-all" />
            </a>
        </div>
    </div>
</div>
@endsection
