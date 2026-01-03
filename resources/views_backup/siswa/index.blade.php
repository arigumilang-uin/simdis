@extends('layouts.app')

{{-- 1. TAILWIND CONFIG & SETUP (Penting untuk styling) --}}
@section('styles')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    // Konfigurasi warna dasar agar seragam
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#0f172a', // Slate 900
                    accent: '#3b82f6',  // Blue 500
                    rose: { 500: '#f43f5e', 100: '#ffe4e6' }, // Tambah rose-100
                    amber: { 500: '#f59e0b', 100: '#fffbe3' },
                    indigo: { 50: '#eef2ff', 100: '#e0e7ff', 600: '#4f46e5', 700: '#4338ca' },
                    blue: { 50: '#eff6ff', 100: '#dbeafe', 600: '#2563eb' },
                    emerald: { 50: '#ecfdf5', 200: '#a7f3d0', 800: '#065f46' } // Tambah emerald
                },
                boxShadow: { 'soft': '0 4px 10px rgba(0,0,0,0.05)' }
            }
        },
        corePlugins: { preflight: false } // Menjaga kompatibilitas dengan Bootstrap/AdminLTE
    }
</script>
<style>


    /* CSS Utility untuk menyamakan bentuk tombol */
    .btn-action-custom {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.2rem;
        color: white !important;
        font-weight: 800;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        border-radius: 0.75rem;
        transition: all 0.2s ease;
        border: none;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        text-decoration: none !important;
        cursor: pointer;
    }

    .btn-action-custom:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 12px -2px rgba(0, 0, 0, 0.15);
    }

    .btn-action-custom:active {
        transform: translateY(0);
    }


    /* Custom CSS untuk memperindah tampilan dan memastikan kompatibilitas */
    .page-wrap { background: #f8fafc; min-height: 100vh; padding: 1.5rem; }
    
    /* Tabel Clean Floating */
    .float-table { border-collapse: separate; border-spacing: 0 10px; width: 100%; }
    .float-row { 
        background: white; 
        transition: 0.2s cubic-bezier(0.4, 0, 0.2, 1); 
        border: 1px solid #f1f5f9; 
        box-shadow: 0 2px 4px rgba(0,0,0,0.02); 
    }
    .float-row td:first-child { border-radius: 10px 0 0 10px; border-left: 1px solid #f1f5f9; }
    .float-row td:last-child { border-radius: 0 10px 10px 0; border-right: 1px solid #f1f5f9; }
    .float-row:hover { 
        transform: translateY(-3px); 
        border-color: #bfdbfe; 
        box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.1); 
        z-index: 10; 
        position: relative; 
    }
    
    /* Tombol Primary (Solid Blue) */
    .btn-primary { 
        background-color: #2563eb; 
        color: white; 
        padding: 0.6rem 1.2rem; 
        border-radius: 0.75rem; 
        font-weight: 600; 
        font-size: 0.875rem; 
        text-decoration: none; 
        display: inline-flex; 
        align-items: center; 
        gap: 0.5rem; 
        transition: 0.2s; 
        border: 1px solid #1d4ed8; 
        box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2); 
    }
    .btn-primary:hover { 
        background-color: #1d4ed8; 
        transform: translateY(-1px); 
        box-shadow: 0 6px 8px -1px rgba(37, 99, 235, 0.3); 
    }
    
    /* Tombol Outline Secondary (Dashboard back) */
    .btn-outline-secondary {
        border: 1px solid #e2e8f0;
        color: #475569;
        background: white;
        padding: 0.6rem 1.2rem; 
        border-radius: 0.75rem; 
        font-weight: 600; 
        font-size: 0.875rem; 
        text-decoration: none; 
        display: inline-flex; 
        align-items: center; 
        gap: 0.5rem; 
        transition: 0.2s;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .btn-outline-secondary:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    /* Tombol Aksi Kecil (di dalam tabel) */
    .btn-action { 
        padding: 6px; 
        border-radius: 8px; 
        transition: 0.2s; 
        color: #64748b; 
        border: 1px solid transparent; 
        background: transparent; 
    }
    .btn-action:hover { 
        background: white; 
        border-color: #e2e8f0; 
        box-shadow: 0 2px 4px rgba(0,0,0,0.05); 
    }

    /* Memastikan konten tabel rata tengah/vertikal */
    .float-table td, .float-table th {
        vertical-align: middle;
        border-top: none !important;
        border-bottom: none !important;
    }
</style>
{{-- Mengimpor kembali style dan script lama jika ada logika filter di dalamnya, tapi utamakan styling baru --}}
<link rel="stylesheet" href="{{ asset('css/pages/siswa/index.css') }}">
<link rel="stylesheet" href="{{ asset('css/pages/siswa/filters.css') }}">
@endsection

@section('content')

    @php
        $userRole = Auth::user()->effectiveRoleName() ?? Auth::user()->role?->nama_role;
        $isOperator = Auth::user()->hasRole('Operator Sekolah');
        $isWaliKelas = Auth::user()->hasRole('Wali Kelas');
        $isWaka = Auth::user()->hasRole('Waka Kesiswaan');
        $isKaprodi = Auth::user()->hasRole('Kaprodi');
    @endphp

    <div class="page-wrap">
        
        <div class="max-w-7xl mx-auto">
            
            {{-- HEADER BARU --}}
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 pb-1 border-b border-gray-200">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 m-0 tracking-tight flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 18a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2"/><rect width="10" height="8" x="7" y="2" rx="2"/><path d="M12 22V16"/></svg>
                        @if($isWaliKelas) Siswa Kelas Anda @else Data Induk Siswa @endif
                    </h1>
                    <p class="text-slate-500 text-sm mt-1">Kelola data seluruh siswa di sekolah Anda.</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    
                    {{-- Container Tombol --}}
<div class="flex flex-wrap items-center gap-3">
    @if($isOperator)
        {{-- Tombol Tambah Siswa --}}
        <a href="{{ route('siswa.create') }}" class="btn-action-custom bg-indigo-600 hover:bg-indigo-700 shadow-indigo-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            <span>Tambah Siswa</span>
        </a>
        
        {{-- Tombol Bulk Delete --}}
        <button type="button" onclick="$('#bulkDeleteModal').modal('show')" 
                class="btn-action-custom bg-rose-500 hover:bg-rose-600 shadow-rose-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
            </svg>
            <span>Hapus Per Kelas</span>
        </button>
        
        {{-- Tombol Lihat Data Terhapus --}}
        <a href="{{ route('siswa.deleted') }}" 
           class="btn-action-custom bg-amber-500 hover:bg-amber-600 shadow-amber-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/>
                <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/>
            </svg>
            <span>Data Terhapus</span>
        </a>
    @endif
</div>
                </div>
            </div>

            {{-- ALERTS --}}
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    <span class="font-medium text-sm">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center gap-3 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                    <span class="font-medium text-sm">{{ session('error') }}</span>
                </div>
            @endif

            @if(session('wali_created'))
                @php $c = session('wali_created'); @endphp
                <div class="mb-8 bg-white border border-blue-100 rounded-2xl p-5 shadow-soft relative overflow-hidden group">
                    <div class="absolute top-0 right-0 bg-blue-50 w-24 h-24 rounded-bl-full -mr-4 -mt-4 opacity-50"></div>
                    
                    <h4 class="text-blue-700 font-bold m-0 flex items-center gap-2 mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Akun Wali Murid Telah Dibuat Otomatis
                    </h4>
                    
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="bg-blue-50/50 p-3 rounded-lg border border-blue-100 flex-1">
                            <span class="text-xs text-blue-400 uppercase font-bold tracking-wider">Username</span>
                            <div class="font-mono text-blue-700 font-bold text-lg">{{ $c['username'] }}</div>
                        </div>
                        <div class="bg-blue-50/50 p-3 rounded-lg border border-blue-100 flex-1">
                            <span class="text-xs text-blue-400 uppercase font-bold tracking-wider">Password (Sampel)</span>
                            <div class="font-mono text-rose-600 font-bold text-lg">{{ $c['password'] }}</div>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mt-3 italic">* Pastikan untuk menyampaikan kredensial ini dan menyarankan perubahan password.</p>
                </div>
            @endif

            @if(session('bulk_wali_created'))
                @php $list = session('bulk_wali_created'); @endphp
                {{-- Menggunakan style alert Success yang dapat ditutup --}}
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start gap-3 shadow-sm relative" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    <div>
                        <strong class="font-medium text-sm">Sukses!</strong> <span class="text-sm">{{ count($list) }} akun Wali Murid telah dibuat. File Excel kredensial otomatis diunduh ke device Anda.</span>
                    </div>
                    <button type="button" class="ml-auto -mt-1 p-1 text-emerald-600 hover:text-emerald-800 rounded-full transition-colors" data-dismiss="alert" aria-label="Close" onclick="this.closest('.alert').style.display='none'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
            @endif

            {{-- FILTER SECTION - STICKY CARD --}}
            <div>
                
                    @include('components.siswa.filter-form')
                
            </div>

            {{-- DAFTAR SISWA HEADER --}}
            <div class="flex justify-between items-end px-2 mb-3 mt-8">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Daftar Data Siswa</span>
                <span class="text-xs text-slate-500 bg-white px-3 py-1 rounded-full border border-slate-200 shadow-sm">
                    Total: <b class="text-blue-600">{{ $siswa->total() }}</b> Data
                </span>
            </div>

            {{-- TABEL DATA BARU (FLOAT TABLE) --}}
            <div class="overflow-x-auto pb-6">
                <table class="float-table text-left">
                    <thead>
                        <tr class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <th class="px-6 py-3 pl-8 w-10">No</th>
                            <th class="px-6 py-3">NISN</th>
                            <th class="px-6 py-3">Nama Siswa</th>
                            @if(!$isWaliKelas) <th class="px-6 py-3">Kelas</th> @endif
                            <th class="px-6 py-3">Kontak Wali Murid</th>
                            <th class="px-6 py-3 text-center pr-8 w-40">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siswa as $key => $s)
                        <tr class="float-row group">
                            <td class="px-6 py-4 pl-8 text-sm text-slate-500">{{ $siswa->firstItem() + $key }}</td>
                            
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200 font-mono">
                                    {{ $s->nisn }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold border border-blue-100 flex-shrink-0">
                                        {{ substr($s->nama_siswa, 0, 1) }}
                                    </div>
                                    <div class="text-sm font-bold text-slate-700 hover:text-blue-600 transition-colors">
                                        <a href="{{ route('siswa.show', $s->id) }}" class="font-bold text-slate-700 hover:text-blue-600">
                                            {{ $s->nama_siswa }}
                                        </a>
                                        @if($isWaliKelas && !$s->waliMurid)
                                            <span class="inline-block ml-1 text-rose-500" title="Akun Wali Murid belum dihubungkan oleh Operator">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 inline-block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            @if(!$isWaliKelas)
                                <td class="px-6 py-4">
                                    <span class="text-xs font-medium text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">
                                        {{ $s->kelas->nama_kelas ?? '-' }}
                                    </span>
                                </td>
                            @endif
                            
                            <td class="px-6 py-4">
                                @if($s->nomor_hp_wali_murid)
                                    <a href="https://wa.me/62{{ ltrim($s->nomor_hp_wali_murid, '0') }}" target="_blank" class="text-sm font-semibold text-emerald-600 hover:text-emerald-700 transition-colors flex items-center gap-1">
                                        
                                        {{-- MENGGANTI DENGAN IKON FONT AWESOME UNTUK KOMPATIBILITAS MAKSIMAL --}}
                                        <i class="fab fa-whatsapp text-l" style="color: #25D366;"></i>
                                        
                                        {{ $s->nomor_hp_wali_murid }}
                                    </a>
                                @else
                                    <span class="text-xs text-slate-300 italic flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" x2="19.07" y1="4.93" y2="19.07"/></svg>
                                        Kosong
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center pr-8">
                                <div class="flex justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    
                                    @if($isWaliKelas)
                                        <a href="{{ route('siswa.edit', $s->id) }}" class="btn-action hover:text-amber-500 hover:border-amber-100" title="Update Kontak">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                        </a>
                                        <a href="{{ route('siswa.show', $s->id) }}" class="btn-action hover:text-blue-600 hover:border-blue-100" title="Detail Siswa">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                        <a href="{{ route('riwayat.index', ['cari_siswa' => $s->nama_siswa]) }}" class="btn-action hover:text-indigo-600 hover:border-indigo-100" title="Lihat Riwayat">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4"/><path d="M5.3 10.7l2.8-2.8"/><path d="M2 12h4"/><path d="M5.3 13.3l2.8 2.8"/><path d="M12 18v4"/><path d="M16.9 16.9l2.8 2.8"/><path d="M18 12h4"/><path d="M16.9 7.1l2.8-2.8"/></svg>
                                        </a>
                                    @elseif($isOperator)
                                        <a href="{{ route('siswa.edit', $s->id) }}" class="btn-action hover:text-amber-500 hover:border-amber-100" title="Edit Data">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                        </a>
                                        <a href="{{ route('siswa.show', $s->id) }}" class="btn-action hover:text-blue-600 hover:border-blue-100" title="Detail Siswa">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                        <button type="button" 
                                                onclick="openDeleteModal({{ $s->id }}, '{{ addslashes($s->nama_siswa) }}')" 
                                                class="btn-action hover:text-rose-500 hover:border-rose-100 cursor-pointer" 
                                                title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                        </button>
                                    @elseif($isWaka)
                                        <a href="{{ route('riwayat.index', ['cari_siswa' => $s->nama_siswa]) }}" class="btn-action bg-blue-50 text-blue-600 border border-blue-100 hover:bg-blue-100 hover:shadow-md transition-all font-semibold px-3 py-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 inline mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4"/><path d="M5.3 10.7l2.8-2.8"/><path d="M2 12h4"/><path d="M5.3 13.3l2.8 2.8"/><path d="M12 18v4"/><path d="M16.9 16.9l2.8 2.8"/><path d="M18 12h4"/><path d="M16.9 7.1l2.8-2.8"/></svg>
                                            Riwayat
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $isWaliKelas ? 5 : 6 }}" class="text-center py-12 text-slate-400">
                                <div class="flex flex-col items-center opacity-60">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-slate-300 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                    <p class="text-sm">Data siswa tidak ditemukan dengan filter ini.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="mt-4 flex justify-between items-center px-2">
                <div class="text-sm text-slate-500">
                    Menampilkan {{ $siswa->firstItem() }} sampai {{ $siswa->lastItem() }} dari {{ $siswa->total() }} data
                </div>
                <div class="bg-white px-3 py-2 rounded-2xl shadow-soft border border-slate-100">
                    {{-- Menggunakan Laravel/Bootstrap pagination bawaan, atau jika Anda memiliki custom Tailwind pagination, ganti di sini --}}
                    {{ $siswa->links('pagination::bootstrap-4') }}
                </div>
            </div>


        </div>
    </div>
    
    {{-- Bulk Delete Modal Component --}}
    @include('components.siswa.bulk-delete-modal')
    
    {{-- Single Delete Modal Component --}}
    @include('components.siswa.delete-single-modal')
@endsection

@section('scripts')
    {{-- Mengimpor kembali script lama --}}
    <script src="{{ asset('js/pages/siswa/filters.js') }}"></script>
    <script src="{{ asset('js/pages/siswa/index.js') }}"></script>
    {{-- Tambahkan script untuk membuat filter card menjadi sticky (jika Anda ingin mempertahankan fungsionalitas tersebut) --}}
    <script>
        // Sederhana: tambahkan class 'sticky top-0 z-10' pada div filter
        document.addEventListener('DOMContentLoaded', function() {
            const stickyFilter = document.getElementById('stickyFilter');
            if (stickyFilter) {
                stickyFilter.classList.add('sticky', 'top-4', 'z-10');
            }
        });
    </script>
@endsection