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
                    accent: '#3b82f6',
                    success: '#10b981',
                    info: '#3b82f6',
                    warning: '#f59e0b',
                    danger: '#f43f5e',
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
        
        <div class="flex justify-between items-center mb-2 pb-2 border-b border-gray-200 custom-header-row">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 m-0 tracking-tight flex items-center gap-3">
                    <i class="fas fa-file-signature text-indigo-600"></i> Persetujuan Kasus
                </h1>
                <p class="text-slate-500 text-sm mt-1">Validasi dan tindak lanjut kasus pelanggaran siswa.</p>
            </div>
            
            <a href="{{ route('dashboard.kepsek') }}" class="btn-clean-action">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="mb-6">
            <div class="custom-status-alert flex items-center justify-between p-4 rounded-xl bg-blue-50 border border-blue-100 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <span class="block font-bold text-blue-800 text-sm">{{ $kasusMenunggu->total() }} Kasus Menunggu Persetujuan</span>
                        <span class="text-blue-600 text-xs">Tinjau dan berikan keputusan untuk validasi poin/sanksi.</span>
                    </div>
                </div>
            </div>
        </div>

        @if($kasusMenunggu->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-100 p-12 text-center shadow-sm">
                <div class="flex flex-col items-center">
                    <div class="w-20 h-20 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center text-3xl mb-4">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h5 class="text-slate-700 font-bold">Tidak Ada Kasus Menunggu</h5>
                    <p class="text-slate-400 text-sm max-w-xs mx-auto">Semua kasus telah diproses dan divalidasi oleh sistem.</p>
                </div>
            </div>
        @else
            <div class="overflow-x-auto pb-6">
                <table class="float-table custom-table-header text-left">
                    <thead>
                        <tr class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                            <th class="px-6 py-3 pl-8">Waktu & Tanggal</th>
                            <th class="px-6 py-3">Data Siswa</th>
                            <th class="px-6 py-3">Kelas</th>
                            <th class="px-6 py-3">Pelanggaran</th>
                            <th class="px-6 py-3">Rekomendasi</th>
                            <th class="px-6 py-3 text-center pr-8">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kasusMenunggu as $kasus)
                        <tr class="float-row group custom-float-row">
                            {{-- Tanggal --}}
                            <td class="px-6 py-4 pl-8">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-700">{{ $kasus->created_at->format('d M Y') }}</span>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-wide">{{ $kasus->created_at->diffForHumans() }}</span>
                                </div>
                            </td>

                            {{-- Siswa --}}
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-800">{{ $kasus->siswa->nama_siswa }}</span>
                                    <span class="text-[10px] font-mono text-slate-400">NISN: {{ $kasus->siswa->nisn }}</span>
                                </div>
                            </td>

                            {{-- Kelas --}}
                            <td class="px-6 py-4">
                                <span class="custom-badge-base bg-blue-50 text-blue-600">
                                    {{ $kasus->siswa->kelas->nama_kelas ?? 'N/A' }}
                                </span>
                            </td>

                            {{-- Pemicu --}}
                            <td class="px-6 py-4">
                                <p class="text-xs text-slate-600 m-0 italic line-clamp-1">
                                    "{{ Str::limit($kasus->pemicu, 40) }}"
                                </p>
                            </td>

                            {{-- Sanksi --}}
                            <td class="px-6 py-4">
                                <div class="px-3 py-2 bg-rose-50 border-l-4 border-rose-400 rounded-r-lg text-xs font-bold text-rose-700">
                                    {{ $kasus->sanksi_deskripsi ?? 'Belum ditentukan' }}
                                </div>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-6 py-4 text-center pr-8">
                                {{-- PERBAIKAN: Ganti $item->id menjadi $kasus->id --}}
                                <a href="{{ route('tindak-lanjut.show', $kasus->id) }}" class="btn-action hover:text-indigo-600 hover:border-indigo-100" title="Tinjau Kasus">
                                    <i class="fas fa-eye w-4 h-4 mr-2"></i> Tinjau
                                </a>
                            </td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="mt-4 flex justify-center">
                <div class="bg-white px-4 py-2 rounded-2xl shadow-sm border border-slate-100">
                    {{ $kasusMenunggu->links('pagination::bootstrap-4') }}
                </div>
            </div>
        @endif

    </div>
</div>
@endsection

@section('styles')
<style>
/* --- FUNGSI UTAMA (GAYA MODERN VIA CSS) --- */
.page-wrap-custom { 
    background: #f8fafc; 
    min-height: 100vh; 
    padding: 1.5rem; 
    font-family: 'Inter', sans-serif; 
}
.custom-header-row {
    border-bottom: 1px solid #e2e8f0;
}

/* Tombol Aksi */
.btn-clean-action {
    padding: 0.5rem 1rem; 
    border-radius: 0.75rem;
    background-color: #f1f5f9; 
    color: #475569; 
    font-size: 0.875rem;
    font-weight: 600;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
}
.btn-clean-action:hover {
    background-color: #e2e8f0;
    text-decoration: none;
    color: #334155;
}

/* FLOATING TABLE STYLE */
.float-table { 
    border-collapse: separate; 
    border-spacing: 0 10px; 
    width: 100%; 
}
.custom-table-header th {
    padding: 0.75rem 0;
    color: #94a3b8;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.float-row { 
    background: white; 
    transition: 0.2s cubic-bezier(0.4, 0, 0.2, 1); 
    box-shadow: 0 2px 4px rgba(0,0,0,0.02); 
}
.custom-float-row td:first-child { border-radius: 12px 0 0 12px; border-left: 1px solid #f1f5f9; }
.custom-float-row td:last-child { border-radius: 0 12px 12px 0; border-right: 1px solid #f1f5f9; }
.custom-float-row:hover { 
    transform: translateY(-2px); 
    border-color: #e2e8f0;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); 
    z-index: 10; 
    position: relative; 
}

/* Badge Styling */
.custom-badge-base {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.6rem;
    border-radius: 0.5rem;
    font-size: 0.7rem;
    font-weight: 700;
}

/* Action Button Style */
.btn-action { 
    padding: 6px 12px; 
    border-radius: 10px; 
    transition: 0.2s; 
    color: #4f46e5; 
    border: 1px solid #eef2ff; 
    background: #eef2ff; 
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
    text-decoration: none !important;
}
.btn-action:hover { 
    background: #4f46e5; 
    color: white !important;
    box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2); 
}

/* Utility */
.float-table td {
    vertical-align: middle;
}
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}

/* Pagination Overrides */
.pagination {
    margin-bottom: 0;
    gap: 4px;
}
.page-item.active .page-link {
    background-color: #4f46e5;
    border-color: #4f46e5;
    border-radius: 8px;
}
.page-link {
    border-radius: 8px;
    color: #475569;
    font-size: 0.8rem;
    padding: 6px 12px;
}
</style>
@endsection