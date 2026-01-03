@extends('layouts.app')

@section('content')

{{-- 1. TAILWIND CONFIG & SETUP --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    // Konfigurasi warna dasar agar seragam
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#0f172a', // Slate 900
                    accent: '#3b82f6',  // Blue 500
                    rose: { 500: '#f43f5e' }, 
                    amber: { 500: '#f59e0b' },
                    indigo: { 600: '#4f46e5' }
                },
                boxShadow: { 'xl-soft': '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)' }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

<div class="page-wrap bg-slate-50 min-h-screen p-4 sm:p-6">
    
    <div class="max-w-7xl mx-auto">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 pb-3 border-b border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Detail Program Studi</h1>
                <p class="text-sm text-gray-500 mt-1">Informasi lengkap Jurusan: <span class="font-semibold text-slate-800">{{ $jurusan->nama_jurusan }}</span></p>
            </div>
            
            <div class="flex space-x-3 mt-3 sm:mt-0">
                <a href="{{ route('jurusan.edit', $jurusan) }}" class="px-4 py-2 bg-amber-500 text-white text-sm font-bold rounded-xl hover:bg-amber-600 shadow-md transition-all active:scale-95 flex items-center gap-2 no-underline">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="{{ route('jurusan.index') }}" class="px-4 py-2 bg-gray-600 text-white text-sm font-bold rounded-xl hover:bg-gray-700 shadow-md transition-all active:scale-95 flex items-center gap-2 no-underline">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        {{-- ALERTS --}}
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-6 text-sm shadow-sm flex justify-between items-center">
                <div class="flex items-center gap-2"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-1 space-y-6">
                
                <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                         <h3 class="text-base font-bold text-slate-700 m-0 flex items-center gap-2">
                             <i class="fas fa-info-circle text-blue-500"></i> Informasi Dasar
                         </h3>
                    </div>
                    <div class="p-6">
                        
                        <div class="detail-row">
                            <span class="detail-label">Kode Jurusan:</span>
                            <span class="detail-value text-slate-800 font-semibold">{{ $jurusan->kode_jurusan ?? '-' }}</span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">Kepala Program:</span>
                            <span class="detail-value text-right">
                                @if($jurusan->kaprodi)
                                    <span class="block text-sm font-semibold text-slate-800 leading-tight">
                                        {{ $jurusan->kaprodi->nama }}
                                    </span>
                                    <span class="block text-xs font-medium text-slate-500 mt-1">
                                        ({{ $jurusan->kaprodi->username }})
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 font-medium">- Belum Ditugaskan -</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    
                    <div class="stat-mini-card bg-indigo-50 border-l-4 border-indigo-600">
                        <i class="fas fa-chalkboard text-indigo-500 text-2xl mb-1"></i>
                        <h4 class="text-3xl font-extrabold text-indigo-700">{{ $jurusan->kelas->count() }}</h4>
                        <p class="text-xs font-semibold text-indigo-600 uppercase">Total Kelas</p>
                    </div>

                    <div class="stat-mini-card bg-rose-50 border-l-4 border-rose-600">
                        <i class="fas fa-user-graduate text-rose-500 text-2xl mb-1"></i>
                        <h4 class="text-3xl font-extrabold text-rose-700">{{ $jurusan->siswa()->count() }}</h4>
                        <p class="text-xs font-semibold text-rose-600 uppercase">Total Siswa</p>
                    </div>

                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                         <h3 class="text-base font-bold text-slate-700 m-0">Daftar Kelas di Jurusan Ini</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left table-auto">
                            <thead class="bg-gray-100 text-slate-600 text-xs uppercase font-bold tracking-wider">
                                <tr>
                                    <th class="px-6 py-3">Nama Kelas</th>
                                    <th class="px-6 py-3">Wali Kelas</th>
                                    <th class="px-6 py-3 text-center">Siswa</th>
                                    <th class="px-6 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($jurusan->kelas as $k)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-3 font-semibold text-slate-800">{{ $k->nama_kelas }}</td>
                                        <td class="px-6 py-3 text-sm text-slate-600">
                                            {{ $k->waliKelas?->nama ?? '- Belum Ditugaskan -' }}
                                        </td>
                                        <td class="px-6 py-3 text-center">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">{{ $k->siswa()->count() }}</span>
                                        </td>
                                        <td class="px-6 py-3 text-center">
                                            <div class="flex justify-center space-x-2">
                                                <a href="{{ route('kelas.show', $k) }}" class="p-2 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition" title="Detail Kelas"><i class="fas fa-eye w-4 h-4"></i></a>
                                                <a href="{{ route('kelas.edit', $k) }}" class="p-2 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-100 transition" title="Edit Kelas"><i class="fas fa-edit w-4 h-4"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-12 text-slate-400 text-sm">
                                            <div class="flex flex-col items-center opacity-60">
                                                <i class="fas fa-chalkboard text-3xl mb-2 text-slate-300"></i>
                                                <span class="font-semibold">Belum ada kelas yang terdaftar di jurusan ini.</span>
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
    </div>
</div>

@endsection

@section('styles')
<style>
    .page-wrap { font-family: 'Inter', sans-serif; }
    
    .detail-row {
        /* Standard detail row (for Code, Kaprodi) */
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px dashed #f1f5f9;
        align-items: center;
    }
    .detail-row:last-of-type { 
        border-bottom: none;
    }

    .detail-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .detail-value {
        font-weight: 700;
        color: #1e293b;
    }
    
    /* STAT MINI CARD STYLES */
    .stat-mini-card {
        border-radius: 0.75rem;
        padding: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s;
        text-align: center;
    }
    .stat-mini-card:hover {
        transform: translateY(-4px);
    }

    /* Table Styles */
    .table-auto td, .table-auto th {
        vertical-align: middle;
    }
</style>
@endsection