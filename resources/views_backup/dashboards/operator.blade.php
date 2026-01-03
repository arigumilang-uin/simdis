@extends('layouts.app')

@section('content')

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#4f46e5',
                },
                screens: {
                    'xs': '375px',
                }
            }
        },
        corePlugins: {
            preflight: false, /* Biar Sidebar AdminLTE Aman */
        }
    }
</script>

<style>
    .dashboard-v3 {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        background-color: #f8fafc;
        min-height: 100vh;
    }
    .dashboard-v3 a { text-decoration: none !important; }
    
    /* Efek Smooth untuk Hover */
    .hover-lift {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease;
    }
    .hover-lift:hover {
        transform: translateY(-5px); /* Naik sedikit */
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    }
</style>

<div class="dashboard-v3 p-3 md:p-6">

    <div class="relative rounded-2xl bg-gradient-to-r from-slate-800 to-blue-900 p-6 shadow-lg mb-8 overflow-hidden text-white flex flex-col md:flex-row items-center justify-between gap-4 border border-blue-800/50">
        
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500 opacity-10 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-40 h-40 bg-cyan-400 opacity-10 rounded-full blur-2xl -ml-10 -mb-10 pointer-events-none"></div>

        <div class="relative z-10 w-full md:w-auto">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-sm border border-white/10 text-[10px] font-medium text-blue-200 mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                System Active
            </div>
            <h1 class="text-xl md:text-2xl font-bold leading-tight">
                Halo, {{ auth()->user()->username ?? 'Operator' }}! 👋
            </h1>
            <p class="text-blue-100 text-xs md:text-sm opacity-80 mt-1">
                Selamat bekerja, data hari ini siap dikelola.
            </p>
        </div>

        <div class="hidden xs:flex items-center gap-3 bg-white/10 backdrop-blur-md px-4 py-3 rounded-2xl border border-white/10 shadow-inner min-w-[140px]">
            <div class="bg-blue-500/20 p-2 rounded-lg text-blue-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
            </div>
            <div>
                <span class="block text-2xl font-bold leading-none tracking-tight">{{ date('d') }}</span>
                <span class="block text-[10px] uppercase tracking-wider text-blue-200">{{ date('F Y') }}</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        
        <a href="{{ route('users.index') }}" class="group hover-lift bg-white rounded-xl p-4 shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total User</span>
                <div class="text-blue-500 bg-blue-50 p-2 rounded-lg group-hover:bg-blue-500 group-hover:text-white transition-colors duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-slate-700 mb-1">{{ $totalUser ?? 0 }}</h3>
            <p class="text-xs text-slate-500">Akun Terdaftar</p>
            <div class="absolute bottom-0 left-0 w-full h-1 bg-blue-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
        </a>

        <a href="{{ route('siswa.index') }}" class="group hover-lift bg-white rounded-xl p-4 shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Siswa</span>
                <div class="text-emerald-500 bg-emerald-50 p-2 rounded-lg group-hover:bg-emerald-500 group-hover:text-white transition-colors duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-slate-700 mb-1">{{ $totalSiswa ?? 0 }}</h3>
            <p class="text-xs text-slate-500">Data Pokok</p>
            <div class="absolute bottom-0 left-0 w-full h-1 bg-emerald-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
        </a>

        <a href="{{ route('jenis-pelanggaran.index') }}" class="group hover-lift bg-white rounded-xl p-4 shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pelanggaran</span>
                <div class="text-rose-500 bg-rose-50 p-2 rounded-lg group-hover:bg-rose-500 group-hover:text-white transition-colors duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-slate-700 mb-1">{{ $totalAturan ?? 0 }}</h3>
            <p class="text-xs text-slate-500">Jenis Poin</p>
            <div class="absolute bottom-0 left-0 w-full h-1 bg-rose-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
        </a>

        <a href="{{ route('kelas.index') }}" class="group hover-lift bg-white rounded-xl p-4 shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Rombel</span>
                <div class="text-amber-500 bg-amber-50 p-2 rounded-lg group-hover:bg-amber-500 group-hover:text-white transition-colors duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4 8 4v14"/><path d="M17 21v-8.33A2 2 0 0 0 15 10.67h-6a2 2 0 0 0-2 2V21"/></svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-slate-700 mb-1">{{ $totalKelas ?? 0 }}</h3>
            <p class="text-xs text-slate-500">Kelas Aktif</p>
            <div class="absolute bottom-0 left-0 w-full h-1 bg-amber-500 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
        </a>
    </div>

    <h3 class="text-xs font-bold text-slate-500 mb-4 uppercase tracking-widest px-1">Aksi Cepat</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        
        <a href="{{ route('users.create') }}" class="group hover-lift flex items-center gap-4 p-4 bg-white rounded-xl shadow-sm border border-slate-100 hover:border-blue-300 transition-all">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="20" x2="20" y1="8" y2="14"/><line x1="23" x2="17" y1="11" y2="11"/></svg>
            </div>
            <div>
                <h4 class="text-sm font-bold text-slate-800 group-hover:text-blue-600 transition-colors">Tambah User Baru</h4>
                <p class="text-xs text-slate-500 mt-0.5">Guru, Staff, atau Wali Murid</p>
            </div>
            <div class="ml-auto text-slate-300 group-hover:text-blue-500 group-hover:translate-x-1 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </div>
        </a>

        <a href="{{ route('siswa.index') }}" class="group hover-lift flex items-center gap-4 p-4 bg-white rounded-xl shadow-sm border border-slate-100 hover:border-emerald-300 transition-all">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            </div>
            <div>
                <h4 class="text-sm font-bold text-slate-800 group-hover:text-emerald-600 transition-colors">Cari Data Siswa</h4>
                <p class="text-xs text-slate-500 mt-0.5">Lihat pelanggaran & profil</p>
            </div>
            <div class="ml-auto text-slate-300 group-hover:text-emerald-500 group-hover:translate-x-1 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </div>
        </a>

    </div>

</div>
@endsection