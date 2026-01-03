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
                    danger: '#f43f5e', // Rose 500
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
        
        <div class="flex justify-between items-center mb-3 pb-1 border-b border-gray-200 custom-header-row">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 m-0 tracking-tight flex items-center gap-3">
                    <i class="fas fa-chart-line text-info"></i> {{ $jurusan->nama_jurusan }}
                </h1>
                <p class="text-slate-500 text-sm mt-1">Statistik dan monitoring jurusan</p>
            </div>
            
            <a href="{{ route('kepala-sekolah.data.jurusan') }}" class="btn-clean-action">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8 custom-card-grid">
            
            {{-- Total Kelas (Info/Blue) --}}
            <div class="dashboard-card group bg-white border border-info-light">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-card-label text-slate-400">Total Kelas</span>
                    <div class="icon-circle bg-info-light text-info group-hover:bg-info group-hover:text-white">
                        <i class="fas fa-school w-5 h-5"></i>
                    </div>
                </div>
                <h3 class="text-card-value text-slate-700">{{ $jurusan->kelas->count() }}</h3>
                <p class="text-xs text-slate-500">Kelas Aktif</p>
                <div class="bottom-bar bg-info"></div>
            </div>
            
            {{-- Total Siswa (Success/Emerald) --}}
            <div class="dashboard-card group bg-white border border-success-light">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-card-label text-slate-400">Total Siswa</span>
                    <div class="icon-circle bg-success-light text-success group-hover:bg-success group-hover:text-white">
                        <i class="fas fa-user-graduate w-5 h-5"></i>
                    </div>
                </div>
                <h3 class="text-card-value text-slate-700">{{ $totalSiswa }}</h3>
                <p class="text-xs text-slate-500">Data Pokok</p>
                <div class="bottom-bar bg-success"></div>
            </div>
            
            {{-- Total Pelanggaran (Warning/Amber) --}}
            <div class="dashboard-card group bg-white border border-warning-light">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-card-label text-slate-400">Total Pelanggaran</span>
                    <div class="icon-circle bg-warning-light text-warning group-hover:bg-warning group-hover:text-white">
                        <i class="fas fa-exclamation-triangle w-5 h-5"></i>
                    </div>
                </div>
                <h3 class="text-card-value text-slate-700">{{ $totalPelanggaran }}</h3>
                <p class="text-xs text-slate-500">Sejak Awal</p>
                <div class="bottom-bar bg-warning"></div>
            </div>
            
            {{-- Siswa Perlu Pembinaan (Danger/Rose) --}}
            <div class="dashboard-card group bg-white border border-danger-light">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-card-label text-slate-400">Perlu Pembinaan</span>
                    <div class="icon-circle bg-danger-light text-danger group-hover:bg-danger group-hover:text-white">
                        <i class="fas fa-user-check w-5 h-5"></i>
                    </div>
                </div>
                <h3 class="text-card-value text-slate-700">{{ $siswaPerluPembinaan }}</h3>
                <p class="text-xs text-slate-500">Siswa Aktif</p>
                <div class="bottom-bar bg-danger"></div>
            </div>
            
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h5 class="text-base font-bold text-slate-700 m-0">Informasi Dasar Jurusan</h5>
            </div>
            <div class="p-6">
                <div class="row">
                    <div class="col-md-6">
                        <table class="custom-detail-table">
                            <tr>
                                <th class="custom-detail-th">Kode Jurusan:</th>
                                <td><span class="custom-badge-base bg-slate-100 text-slate-600">{{ $jurusan->kode_jurusan ?? '-' }}</span></td>
                            </tr>
                            <tr>
                                <th class="custom-detail-th">Kaprodi:</th>
                                <td class="text-sm font-medium text-slate-600">{{ $jurusan->kaprodi?->username ?? 'Belum ditentukan' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h5 class="text-base font-bold text-slate-700 m-0">Daftar Kelas di {{ $jurusan->nama_jurusan }}</h5>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left table-auto custom-table">
                    <thead class="custom-table-header-clean">
                        <tr>
                            <th class="px-6 py-3 col-nama-kelas">Nama Kelas</th>
                            <th class="px-6 py-3 col-wali-kelas">Wali Kelas</th>
                            <th class="px-6 py-3 text-center col-jml-siswa">Jumlah Siswa</th>
                            <th class="px-6 py-3 text-center col-pelanggaran">Pelanggaran (Total)</th>
                            <th class="px-6 py-3 text-center col-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($jurusan->kelas as $kelas)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3 font-semibold text-slate-800">{{ $kelas->nama_kelas }}</td>
                            <td class="px-6 py-3 text-sm text-slate-600">{{ $kelas->waliKelas?->username ?? '-' }}</td>
                            <td class="px-6 py-3 text-center col-jml-siswa">
                                <span class="custom-badge-base bg-indigo-50 text-indigo-700">{{ $kelas->siswa_count }} siswa</span>
                            </td>
                            <td class="px-6 py-3 text-center col-pelanggaran">
                                @if($kelas->pelanggaran_count > 0)
                                    <span class="custom-badge-base bg-rose-100 text-danger">{{ $kelas->pelanggaran_count }} kasus</span>
                                @else
                                    <span class="custom-badge-base bg-success-light text-success">Bersih</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-center col-aksi">
                                <a href="{{ route('kepala-sekolah.data.kelas.show', $kelas) }}" class="btn-action hover:text-blue-600 hover:border-blue-100" title="Lihat Detail Statistik">
                                    <i class="fas fa-chart-bar w-4 h-4 mr-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-12 text-slate-400 text-sm">
                                <div class="flex flex-col items-center opacity-60">
                                    <i class="fas fa-chalkboard-teacher text-3xl mb-2 text-slate-300"></i>
                                    <span class="font-semibold">Belum ada kelas terdaftar di jurusan ini.</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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

/* 1. SUMMARY CARDS (Small Box Replacement - Simetris & Hover-Lift) */
.custom-card-grid {
    display: flex;
    flex-wrap: wrap;
    align-items: stretch; /* Memastikan semua kartu memiliki tinggi yang sama */
    gap: 1rem;
}
.custom-card-grid > div {
    flex: 1 1 23%; /* Lebar untuk 4 kolom */
    min-width: 200px;
}

.dashboard-card {
    border-radius: 0.75rem; /* rounded-xl */
    padding: 1rem; /* p-4 */
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); /* shadow-sm */
    position: relative;
    overflow: hidden;
    transition: transform 0.3s, box-shadow 0.3s;
    display: flex; 
    flex-direction: column; 
    height: 100%; /* PENTING: Untuk simetris */
}
.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.text-card-label {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-top: 0;
}
.text-card-value {
    font-size: 1.5rem;
    font-weight: 700;
}
.dashboard-card p.text-xs {
    margin-top: auto; /* Ini yang membuat elemen bawah rata */
    padding-top: 0.25rem;
}

.icon-circle {
    padding: 0.5rem;
    border-radius: 0.5rem;
    transition: background-color 0.3s, color 0.3s;
    flex-shrink: 0;
}
.dashboard-card:hover .icon-circle {
    background-color: white !important;
    color: var(--card-color-main) !important;
}

.bottom-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 0.25rem;
    transform: scaleX(0);
    transition: transform 0.3s;
    transform-origin: left;
}
.dashboard-card:hover .bottom-bar {
    transform: scaleX(1);
}

/* Color Setup (Menggunakan CSS Variables untuk Hover Icon) */

/* Total Kelas (Info/Blue) */
.custom-card-grid > div:nth-child(1) { --card-color-main: #3b82f6; }
.custom-card-grid > div:nth-child(1) .icon-circle { background-color: #eef2ff; color: #3b82f6; }
.custom-card-grid > div:nth-child(1) .bottom-bar { background-color: #3b82f6; }

/* Total Siswa (Success/Emerald) */
.custom-card-grid > div:nth-child(2) { --card-color-main: #10b981; }
.custom-card-grid > div:nth-child(2) .icon-circle { background-color: #ecfdf5; color: #10b981; }
.custom-card-grid > div:nth-child(2) .bottom-bar { background-color: #10b981; }

/* Total Pelanggaran (Warning/Amber) */
.custom-card-grid > div:nth-child(3) { --card-color-main: #f59e0b; }
.custom-card-grid > div:nth-child(3) .icon-circle { background-color: #fffbeb; color: #f59e0b; }
.custom-card-grid > div:nth-child(3) .bottom-bar { background-color: #f59e0b; }

/* Siswa Perlu Pembinaan (Danger/Rose) */
.custom-card-grid > div:nth-child(4) { --card-color-main: #f43f5e; }
.custom-card-grid > div:nth-child(4) .icon-circle { background-color: #fff1f2; color: #f43f5e; }
.custom-card-grid > div:nth-child(4) .bottom-bar { background-color: #f43f5e; }


/* 2. INFO JURUSAN TABLE */
.custom-detail-table { width: 100%; border-collapse: separate; }
.custom-detail-table th { font-weight: 600; color: #475569; padding: 0.5rem 0; text-align: left; }
.custom-detail-th { width: 150px; }


/* 3. DAFTAR KELAS TABLE */
.custom-table { width: 100%; border-collapse: collapse; }
.custom-table-header-clean th {
    padding: 0.75rem 1.5rem;
    background-color: #f3f4f6; 
    color: #475569; 
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 2px solid #e5e7eb;
}
.custom-table tbody td {
    padding: 0.9rem 1.5rem;
    vertical-align: middle;
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
.text-danger { color: #f43f5e; }
.text-success { color: #10b981; }
.bg-indigo-50 { background-color: #eef2ff; }
.text-indigo-700 { color: #4338ca; }
.bg-rose-100 { background-color: #ffe4e6; }
.bg-success-light { background-color: #ecfdf5; }

/* Action Button Style */
.btn-action { 
    padding: 6px 10px; 
    border-radius: 8px; 
    transition: 0.2s; 
    color: #4f46e5; 
    border: 1px solid transparent; 
    background: #eef2ff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
}
.btn-action:hover { 
    background: #e0e7ff;
    border-color: #c7d2fe; 
    color: #4338ca;
}
</style>
@endsection