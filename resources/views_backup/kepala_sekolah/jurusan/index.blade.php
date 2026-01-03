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
        
        {{-- HEADER: Monitoring Data Jurusan --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-3 gap-1 pb-1 custom-header-row">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 m-0 tracking-tight">Data Jurusan</h1>
                <p class="text-slate-500 text-sm mt-1">Overview jurusan dengan statistik pembinaan.</p>
            </div>
            
            <a href="{{ route('dashboard.kepsek') }}" class="btn-clean-action">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
        
        {{-- DAFTAR JURUSAN HEADER --}}
        <div class="flex justify-between items-end px-2 mb-3 mt-8">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Daftar Program Studi</span>
             <span class="text-xs text-slate-500 bg-white px-3 py-1 rounded-full border border-slate-200 shadow-sm">
                Total: <b class="text-blue-600">{{ $jurusanList->count() }}</b>
            </span>
        </div>

        {{-- TABEL JURUSAN (Floating Style) --}}
        <div class="overflow-x-auto pb-6">
            <table class="float-table custom-table-header text-left">
                <thead>
                    <tr class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        <th class="col-nama-jurusan px-6 py-3 pl-8">Nama Jurusan</th>
                        <th class="col-kode px-6 py-3 text-center">Kode</th>
                        <th class="col-kaprodi px-6 py-3">Kaprodi</th>
                        <th class="col-jml-kelas px-6 py-3 text-center">Jml. Kelas</th>
                        <th class="col-jml-siswa px-6 py-3 text-center">Jml. Siswa</th>
                        <th class="col-aksi px-6 py-3 text-center pr-8">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jurusanList as $j)
                    <tr class="float-row group custom-float-row">
                        
                        {{-- Nama Jurusan --}}
                        <td class="px-6 py-4 pl-8 col-nama-jurusan">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl custom-avatar-bg flex items-center justify-center font-bold text-sm shadow-sm border border-blue-100">
                                    {{ substr($j->nama_jurusan, 0, 1) }}
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-slate-700">{{ $j->nama_jurusan }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Kode --}}
                        <td class="px-6 py-4 col-kode text-center">
                            <span class="custom-badge-base bg-slate-100 text-slate-600">
                                {{ $j->kode_jurusan ?? '-' }}
                            </span>
                        </td>

                        {{-- Kaprodi --}}
                        <td class="px-6 py-4 col-kaprodi">
                            @if($j->kaprodi)
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-slate-600">{{ $j->kaprodi->username }}</span>
                                </div>
                            @else
                                <span class="text-xs text-slate-300 italic flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" x2="19.07" y1="4.93" y2="19.07"/></svg>
                                    Kosong
                                </span>
                            @endif
                        </td>

                        {{-- Jumlah Kelas --}}
                        <td class="px-6 py-4 text-center col-jml-kelas">
                            <span class="custom-badge-base bg-blue-100 text-blue-600">
                                {{ $j->kelas_count }}
                            </span>
                        </td>
                        
                        {{-- Jumlah Siswa --}}
                        <td class="px-6 py-4 text-center col-jml-siswa">
                            <span class="custom-badge-base bg-indigo-50 text-indigo-700">
                                {{ $j->siswa_count }}
                            </span>
                        </td>

                        {{-- Aksi (Monitoring View) --}}
                        <td class="px-6 py-4 text-center pr-8 col-aksi">
                            <div class="flex justify-center gap-1 opacity-100 transition-opacity duration-200">
                                <a href="{{ route('kepala-sekolah.data.jurusan.show', $j) }}" class="btn-action hover:text-blue-600 hover:border-blue-100" title="Detail & Statistik">
                                    <i class="fas fa-chart-bar w-4 h-4 mr-1"></i> Detail
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-12 text-slate-400">
                            <div class="flex flex-col items-center opacity-60">
                                <i class="fas fa-building text-3xl mb-2 text-slate-300"></i>
                                <p class="text-sm">Belum ada data program studi yang dimasukkan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Placeholder --}}
        <div class="mt-2 flex justify-center">
            @if(method_exists($jurusanList, 'links'))
                <div class="bg-white px-3 py-2 rounded-2xl shadow-sm border border-slate-100">
                    {{ $jurusanList->links('pagination.custom') }} 
                </div>
            @endif
        </div>
        
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

/* --- PERBAIKAN ALIGNMENT KOLOM KHUSUS --- */

/* Atur lebar kolom untuk KODE dan KAPRODI */
/* Memberikan lebar spesifik agar tidak tergeser oleh kolom Nama Jurusan */
.col-kode, .float-table th:nth-child(2) {
    width: 80px; /* Lebar minimum untuk kode APHP/ATP */
    text-align: center !important;
}
.col-kaprodi, .float-table th:nth-child(3) {
    width: 250px; /* Lebar yang cukup untuk nama kaprodi/jurusan */
}
/* Menjaga align header KAPRODI rata kiri seperti kontennya */
.float-table th:nth-child(3) {
    text-align: left !important;
}

/* Kolom Jumlah Kelas dan Jumlah Siswa (Pusat) */
.col-jml-kelas, .float-table th:nth-child(4) {
    width: 100px; /* Lebar Konsisten */
    text-align: center !important;
}
.col-jml-siswa, .float-table th:nth-child(5) {
    width: 100px; /* Lebar Konsisten */
    text-align: center !important;
}
.col-aksi, .float-table th:nth-child(6) {
    width: 120px;
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
.bg-rose-100 { background-color: #ffe4e6; }
.text-rose-600 { color: #e11d48; }
.bg-amber-100 { background-color: #fffbeb; }
.text-amber-700 { color: #b45309; }

/* Avatar/Icon */
.custom-avatar-bg {
    background: linear-gradient(135deg, #e0e7ff, #dbeafe); /* indigo-100 to blue-100 */
    color: #4f46e5;
}

/* Action Button Style */
.btn-action { 
    padding: 6px; 
    border-radius: 8px; 
    transition: 0.2s; 
    color: #64748b; 
    border: 1px solid transparent; 
    background: transparent; 
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.btn-action:hover { 
    background: white; 
    border-color: #e2e8f0; 
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