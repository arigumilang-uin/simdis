@extends('layouts.app')

@section('content')

{{-- 1. TAILWIND CONFIG & SETUP --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#0f172a',
                    indigo: { 600: '#4f46e5', 50: '#eef2ff', 100: '#e0e7ff', 700: '#4338ca' },
                    emerald: { 500: '#10b981', 600: '#059669' }
                }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

<div class="page-wrap-custom min-h-screen p-6">
    <div class="max-w-7xl mx-auto">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-3 gap-1 pb-1 custom-header-row">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 m-0 tracking-tight flex items-center gap-3">
                    <i class="fas fa-history text-indigo-600"></i> Audit & Log Sistem
                </h1>
                <p class="text-slate-500 text-sm mt-1">Pantau integritas data dan riwayat aktivitas pengguna sistem secara real-time.</p>
            </div>
            
            <div class="flex items-center gap-2">
                @if(!isset($tab) || $tab === 'activity')
                <a href="{{ route('audit.activity.export-csv', request()->query()) }}" class="btn-primary-custom no-underline bg-emerald-500 hover:bg-emerald-600 shadow-emerald-200">
                    <i class="fas fa-download mr-2"></i> Export CSV
                </a>
                @endif
                <a href="{{ route('dashboard.admin') }}" class="btn-clean-action no-underline">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div>
            <div class="px-4 py-3 bg-slate-50/50 border-b border-slate-100 flex flex-wrap gap-2">
                <a href="{{ route('audit.activity.index', ['tab' => 'activity']) }}" 
                   class="no-underline px-4 py-2 rounded-xl text-[11px] font-black uppercase tracking-wider transition-all {{ (!isset($tab) || $tab === 'activity') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-100' }}">
                    <i class="fas fa-list mr-1"></i> Log Aktivitas
                </a>
                
                <a href="{{ route('audit.activity.index', ['tab' => 'last-login']) }}" 
                   class="no-underline px-4 py-2 rounded-xl text-[11px] font-black uppercase tracking-wider transition-all {{ (isset($tab) && $tab === 'last-login') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-100' }}">
                    <i class="fas fa-sign-in-alt mr-1"></i> Last Login
                </a>
                
                <a href="{{ route('audit.activity.index', ['tab' => 'status']) }}" 
                   class="no-underline px-4 py-2 rounded-xl text-[11px] font-black uppercase tracking-wider transition-all {{ (isset($tab) && $tab === 'status') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-100' }}">
                    <i class="fas fa-user-check mr-1"></i> Status Akun
                </a>
            </div>

            <div class="tab-content-wrapper">
                @if(!isset($tab) || $tab === 'activity')
                    @include('kepala_sekolah.activity.tabs.activity')
                @elseif($tab === 'last-login')
                    @include('kepala_sekolah.activity.tabs.last-login')
                @elseif($tab === 'status')
                    @include('kepala_sekolah.activity.tabs.status')
                @endif
            </div>
        </div>

        <div class="flex items-center gap-3 p-4 bg-indigo-50/50 border border-indigo-100 rounded-2xl">
            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                <i class="fas fa-shield-alt text-xs"></i>
            </div>
            <p class="text-[11px] text-indigo-700/70 m-0 leading-relaxed font-medium">
                Log sistem mencatat setiap perubahan data penting untuk keamanan. Gunakan data ini untuk meninjau validasi aktivitas atau mendeteksi akses yang tidak sah secara berkala.
            </p>
        </div>

    </div>
</div>
@endsection

@section('styles')
<style>
/* --- CORE STYLING (MATCHING PREVIOUS MODULES) --- */
.page-wrap-custom { 
    background: #f8fafc; 
    min-height: 100vh; 
    padding: 1.5rem; 
    font-family: 'Inter', sans-serif; 
}

.custom-header-row { 
    border-bottom: 1px solid #e2e8f0; 
}

/* Tombol Emerald (Export) */
.btn-primary-custom {
    background-color: #10b981; 
    color: white !important; 
    padding: 0.6rem 1.2rem; 
    border-radius: 0.75rem;
    font-weight: 700; 
    font-size: 0.8rem; 
    border: none; 
    display: inline-flex; 
    align-items: center;
    transition: all 0.2s; 
    box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2);
}
.btn-primary-custom:hover { 
    background-color: #059669;
    transform: translateY(-1px); 
}

/* Tombol Gray (Kembali) */
.btn-clean-action {
    padding: 0.6rem 1.2rem; 
    border-radius: 0.75rem;
    background-color: #f1f5f9; 
    color: #475569; 
    font-size: 0.8rem;
    font-weight: 700;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border: 1px solid #e2e8f0;
}
.btn-clean-action:hover {
    background-color: #e2e8f0;
    color: #1e293b;
}

/* SOLID TABLE UI (Pastikan file di folder tabs menggunakan class ini) */
.custom-solid-table thead th {
    background-color: #f8fafc;
    border-bottom: 1px solid #f1f5f9;
    color: #94a3b8;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    padding: 1rem 1.5rem;
}

.custom-solid-table tbody td {
    padding: 1rem 1.5rem;
    vertical-align: middle;
    border-bottom: 1px solid #f8fafc;
    color: #475569;
    font-size: 13px;
}

.custom-solid-table tbody tr:hover {
    background-color: #f8fafc;
}

/* Reset Default Bootstrap Tabs */
.nav-tabs { border: none !important; }
</style>
@endsection