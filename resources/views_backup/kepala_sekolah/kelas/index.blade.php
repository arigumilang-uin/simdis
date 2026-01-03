@extends('layouts.app')

@section('content')

{{-- 1. TAILWIND CONFIG & SETUP (Dummy for reference, actual styling in <style>) --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    // Konfigurasi warna dasar agar seragam
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#0f172a', // Slate 900
                    accent: '#3b82f6',  // Blue 500
                    success: '#10b981', // Emerald 500
                    info: '#3b82f6', // Blue 500
                    warning: '#f59e0b', // Amber 500
                    rose: { 500: '#f43f5e', 100: '#ffe4e6' },
                    indigo: { 600: '#4f46e5', 50: '#eef2ff', 100: '#e0e7ff', 700: '#4338ca' },
                    blue: { 50: '#eff6ff', 100: '#dbeafe', 600: '#2563eb' }
                },
                boxShadow: { 'soft': '0 4px 10px rgba(0,0,0,0.05)' }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

<div class="page-wrap-custom min-h-screen p-6">
    
    <div class="max-w-7xl mx-auto">
        
        {{-- HEADER: Data Kelas --}}
        <div class="flex justify-between items-center mb-6 pb-3 border-b border-gray-200 custom-header-row">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 m-0 tracking-tight">Data Kelas</h1>
                <p class="text-slate-500 text-sm mt-1">Overview kelas dengan statistik pembinaan.</p>
            </div>
            
            <a href="{{ route('dashboard.kepsek') }}" class="btn-clean-action">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
        
        {{-- DAFTAR KELAS HEADER --}}
        <div class="flex justify-between items-end px-2 mb-3 mt-8">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Daftar Kelas Aktif</span>
             <span class="text-xs text-slate-500 bg-white px-3 py-1 rounded-full border border-slate-200 shadow-sm">
                Total: <b class="text-blue-600">{{ $kelasList->count() }}</b>
            </span>
        </div>

        {{-- TABEL KELAS (Floating Style) --}}
        <div class="overflow-x-auto pb-6">
            <table class="float-table custom-table-header text-left">
                <thead>
                    <tr class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        <th class="col-nama-kelas px-6 py-3 pl-8">Nama Kelas</th>
                        <th class="col-jurusan px-6 py-3">Jurusan</th>
                        <th class="col-wali-kelas px-6 py-3">Wali Kelas</th>
                        <th class="col-jml-siswa px-6 py-3 text-center">Jml. Siswa</th>
                        <th class="col-aksi px-6 py-3 text-center pr-8">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kelasList as $k)
                    @php
                        // Ambil tingkat kelas (Contoh: "X" dari "X APH P 1")
                        $tingkat_kelas = explode(' ', $k->nama_kelas)[0] ?? '?'; 
                    @endphp
                    <tr class="float-row group custom-float-row">
                        
                        {{-- Nama Kelas --}}
                        <td class="px-6 py-4 pl-8 col-nama-kelas">
                            <div class="flex items-center gap-3">
                                {{-- PERBAIKAN 2: Ganti Avatar dengan Tingkat Kelas --}}
                                <div class="w-10 h-10 rounded-xl custom-avatar-bg bg-info-light text-info flex items-center justify-center font-bold text-sm shadow-sm border border-blue-100">
                                    {{ $tingkat_kelas }}
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-slate-700">{{ $k->nama_kelas }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Jurusan --}}
                        <td class="px-6 py-4 col-jurusan">
                            <span class="custom-badge-base bg-blue-100 text-blue-600">
                                {{ $k->jurusan?->nama_jurusan ?? '-' }}
                            </span>
                        </td>

                        {{-- Wali Kelas --}}
                        <td class="px-6 py-4 col-wali-kelas text-sm text-slate-600">
                            {{ $k->waliKelas?->username ?? '-' }}
                        </td>

                        {{-- Jumlah Siswa --}}
                        <td class="px-6 py-4 text-center col-jml-siswa">
                            <span class="custom-badge-base bg-indigo-50 text-indigo-700">
                                {{ $k->siswa->count() }} siswa
                            </span>
                        </td>

                        {{-- Aksi --}}
                        <td class="px-6 py-4 text-center pr-8 col-aksi">
                            <div class="flex justify-center gap-1 opacity-100 transition-opacity duration-200">
                                <a href="{{ route('kepala-sekolah.data.kelas.show', $k) }}" class="btn-action hover:text-blue-600 hover:border-blue-100" title="Detail & Statistik">
                                    <i class="fas fa-chart-bar w-4 h-4 mr-1"></i> Detail
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-12 text-slate-400">
                            <div class="flex flex-col items-center opacity-60">
                                <i class="fas fa-chalkboard-teacher text-3xl mb-2 text-slate-300"></i>
                                <p class="text-sm">Belum ada data kelas yang dimasukkan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Placeholder --}}
        {{-- Jika Anda menggunakan pagination, ini adalah tempatnya --}}
        
    </div>
</div>
@endsection

@section('styles')
<style>
/* --- FUNGSI UTAMA (GAYA TAILWIND VIA CSS) --- */
.page-wrap-custom { 
    background: #f8fafc; /* bg-slate-50 */
    min-height: 100vh; 
    padding: 1.5rem; 
    font-family: 'Inter', sans-serif; 
}
.custom-header-row {
    border-bottom: 1px solid #e2e8f0; /* border-gray-200 */
}

/* Tombol Aksi */
.btn-clean-action {
    padding: 0.5rem 1rem; 
    border-radius: 0.75rem;
    background-color: #f1f5f9; /* slate-100 */
    color: #475569; /* slate-700 */
    font-size: 0.875rem;
    font-weight: 600;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
}
.btn-clean-action:hover {
    background-color: #e2e8f0; /* slate-200 */
}

/* 2. FLOATING TABLE (Mirip Referensi Kelas) */
.float-table { 
    border-collapse: separate; 
    border-spacing: 0 10px; 
    width: 100%; 
}
.custom-table-header th {
    /* Gaya header */
    padding: 0.75rem 0;
    color: #94a3b8; /* slate-400 */
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background-color: #f8fafc;
}
.float-row { 
    background: white; 
    transition: 0.2s cubic-bezier(0.4, 0, 0.2, 1); 
    border: 1px solid #f1f5f9; /* Border halus */
    box-shadow: 0 2px 4px rgba(0,0,0,0.02); 
}
.custom-float-row td:first-child { border-radius: 10px 0 0 10px; border-left: 1px solid #f1f5f9; }
.custom-float-row td:last-child { border-radius: 0 10px 10px 0; border-right: 1px solid #f1f5f9; }
.custom-float-row:hover { 
    transform: translateY(-3px); 
    border-color: #bfdbfe; /* border-blue-200 */
    box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.1); 
    z-index: 10; 
    position: relative; 
}

/* --- LEBAR KOLOM DAN ALIGNMENT (PERBAIKAN 1) --- */

.col-nama-kelas { 
    width: 25%; /* Cukup untuk Nama Kelas + Avatar */
}
.col-jurusan { 
    width: 25%; /* Cukup untuk Nama Jurusan */
}
.col-wali-kelas { 
    width: 30%; /* Lebih lebar untuk Nama Guru */
}
.col-jml-siswa { 
    width: 10%; 
    text-align: center !important;
}
.col-aksi { 
    width: 10%; 
    text-align: center !important;
}

/* Memastikan header sejajar dengan konten yang center/badge */
.float-table th:nth-child(4), .float-table th:nth-child(5) {
    text-align: center !important;
}

/* Badge Styling */
.custom-badge-base {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.6rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 700;
}
.bg-slate-100 { background-color: #f1f5f9; }
.text-slate-600 { color: #475569; }
.bg-blue-100 { background-color: #dbeafe; }
.text-blue-600 { color: #2563eb; }
.bg-indigo-50 { background-color: #eef2ff; }
.text-indigo-700 { color: #4338ca; }
.text-info { color: #3b82f6; }
.bg-info-light { background-color: #eef2ff; }

/* Avatar/Icon */
.custom-avatar-bg {
    /* Digunakan di kolom Nama Kelas */
    background: linear-gradient(135deg, #e0e7ff, #dbeafe); /* indigo-100 to blue-100 */
    color: #4f46e5;
}

/* Action Button Style */
.btn-action { 
    padding: 6px; 
    border-radius: 8px; 
    transition: 0.2s; 
    color: #4f46e5; 
    border: 1px solid transparent; 
    background: transparent; 
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
}
.btn-action:hover { 
    background: #eef2ff; 
    border-color: #dbeafe; 
    color: #3b82f6; 
    box-shadow: 0 2px 4px rgba(0,0,0,0.05); 
}

/* Final Alignments */
.float-table td, .float-table th {
    vertical-align: middle;
    border-top: none !important;
    border-bottom: none !important;
}
</style>
@endsection