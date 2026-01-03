@extends('layouts.app')

@section('title', 'Master Jenis Pelanggaran')

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
                    indigo: { 600: '#4f46e5' },
                    emerald: { 500: '#10b981', 600: '#059669' }
                },
                boxShadow: { 'soft': '0 4px 10px rgba(0,0,0,0.05)' }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

<div class="page-wrap bg-gray-50 min-h-screen p-4 sm:p-6">

    <div class="max-w-7xl mx-auto">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 pb-3 border-b border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Master Jenis Pelanggaran</h1>
                <p class="text-sm text-gray-500 mt-1">Daftar aturan dan poin kedisiplinan siswa.</p>
            </div>
            
            <div class="flex space-x-2 mt-3 sm:mt-0">
                <a href="{{ route('dashboard.admin') }}" class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-200 transition-all flex items-center gap-2 no-underline">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <a href="{{ route('jenis-pelanggaran.create') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all active:scale-95 flex items-center gap-2 no-underline">
                    <i class="fas fa-plus"></i> Tambah Aturan Baru
                </a>
            </div>
        </div>

        {{-- ALERTS --}}
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-4 text-sm shadow-sm flex justify-between items-center">
                <div class="flex items-center gap-2"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl mb-4 text-sm shadow-sm flex justify-between items-center">
                <div class="flex items-center gap-2"><i class="fas fa-exclamation-triangle"></i> {{ session('error') }}</div>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden mt-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left table-auto">
                    <thead class="bg-gray-100 text-slate-600 text-xs uppercase font-bold tracking-wider">
                        <tr>
                            <th class="px-6 py-3" style="width: 5%">No</th>
                            <th class="px-6 py-3" style="width: 45%">Nama Pelanggaran</th>
                            <th class="px-6 py-3" style="width: 25%">Kategori</th>
                            <th class="px-6 py-3 text-center" style="width: 10%">Poin</th>
                            <th class="px-6 py-3 text-center" style="width: 15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($jenisPelanggaran as $item)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3 text-sm text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-6 py-3 font-semibold text-slate-800">{{ $item->nama_pelanggaran }}</td>
                            <td class="px-6 py-3">
                                @php
                                    $categoryName = strtolower($item->kategoriPelanggaran->nama_kategori ?? '');
                                    $badgeClass = 'bg-gray-100 text-gray-700'; // Default
                                    
                                    if (str_contains($categoryName, 'ringan')) {
                                        $badgeClass = 'bg-green-100 text-green-700';
                                    } elseif (str_contains($categoryName, 'sedang')) {
                                        $badgeClass = 'bg-yellow-100 text-yellow-700';
                                    } elseif (str_contains($categoryName, 'berat')) {
                                        $badgeClass = 'bg-red-100 text-red-700';
                                    } elseif (str_contains($categoryName, 'khusus')) {
                                        $badgeClass = 'bg-purple-100 text-purple-700';
                                    }
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $badgeClass }}">
                                    {{ $item->kategoriPelanggaran->nama_kategori ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                                    {{ $item->poin }} Poin
                                </span>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <div class="flex justify-center space-x-2">
                                    {{-- TOMBOL EDIT (TETAP SAMA) --}}
                                    <a href="{{ route('jenis-pelanggaran.edit', $item->id) }}" class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition" title="Edit Data">
                                        <i class="fas fa-edit w-4 h-4"></i>
                                    </a>
                                    
                                    {{-- TOMBOL HAPUS BARU (MENGGUNAKAN <a> DENGAN FORM SUBMIT VIA JS) --}}
                                    <a href="#" 
                                       onclick="event.preventDefault(); if(confirm('Yakin ingin menghapus jenis pelanggaran ini? Aksi ini tidak dapat dibatalkan.')) { document.getElementById('delete-form-{{ $item->id }}').submit(); }"
                                       class="p-2 text-rose-600 hover:bg-rose-100 rounded-lg transition focus:ring-0 focus:outline-none" 
                                       title="Hapus Permanen">
                                        <i class="fas fa-trash w-4 h-4"></i>
                                    </a>
                                    
                                    {{-- Hidden Form untuk Hapus --}}
                                    <form id="delete-form-{{ $item->id }}" action="{{ route('jenis-pelanggaran.destroy', $item->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-12 text-slate-400 text-sm">
                                <div class="flex flex-col items-center opacity-60">
                                    <i class="fas fa-inbox text-3xl mb-2 text-slate-300"></i>
                                    <span class="font-semibold">Belum ada data jenis pelanggaran yang terdaftar.</span>
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

@push('scripts')
    <script src="{{ asset('js/pages/jenis_pelanggaran/index.js') }}"></script>
@endpush

@section('styles')
<style>
    .page-wrap { font-family: 'Inter', sans-serif; }
    
    /* Table Fixes */
    .table-auto td, .table-auto th {
        vertical-align: middle;
    }
</style>
@endsection