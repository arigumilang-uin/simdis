@extends('layouts.app')

@section('content')

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: { colors: { primary: '#3b82f6', slate: { 800: '#1e293b', 900: '#0f172a' } } }
        },
        corePlugins: { preflight: false }
    }
</script>

<style>
    .page-wrap { background: #f8fafc; min-height: 100vh; padding: 1.5rem; font-family: 'Inter', sans-serif; }
    
    /* Tabel Clean */
    .float-table { border-collapse: separate; border-spacing: 0 8px; width: 100%; }
    .float-row { background: white; transition: 0.2s; border: 1px solid #f1f5f9; }
    .float-row td:first-child { border-radius: 8px 0 0 8px; border-left: 1px solid #f1f5f9; }
    .float-row td:last-child { border-radius: 0 8px 8px 0; border-right: 1px solid #f1f5f9; }
    .float-row:hover { transform: translateY(-2px); border-color: #bfdbfe; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    
    /* Tombol Biasa (Clean) */
    .btn-back { background: white; border: 1px solid #e2e8f0; color: #64748b; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.875rem; transition: 0.2s; display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 500; }
    .btn-back:hover { background: #f1f5f9; color: #334155; }
    
    .btn-edit { background: #fffbeb; border: 1px solid #fcd34d; color: #b45309; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.875rem; transition: 0.2s; display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 500; }
    .btn-edit:hover { background: #fef3c7; }
</style>

<div class="page-wrap">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-400 mb-1">
                <a href="{{ route('kelas.index') }}" class="hover:text-blue-600 no-underline">Manajemen Kelas</a>
                <span>/</span>
                <span class="text-slate-600">Detail</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 m-0">{{ $kelas->nama_kelas }}</h1>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('kelas.index') }}" class="btn-back">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Kembali
            </a>
            
            @if(auth()->user()->hasRole('Operator Sekolah'))
                <a href="{{ route('kelas.edit', $kelas) }}" class="btn-edit">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                    Edit Data
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/><path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/><path d="m22 11.7-9.17 4.16a2 2 0 0 1-1.66 0L2 11.7"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-bold uppercase">Jurusan</p>
                <p class="text-sm font-semibold text-slate-700">{{ $kelas->jurusan->nama_jurusan ?? '-' }}</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-bold uppercase">Wali Kelas</p>
                <p class="text-sm font-semibold text-slate-700">{{ $kelas->waliKelas->username ?? 'Belum Ada' }}</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-bold uppercase">Total Siswa</p>
                <p class="text-sm font-semibold text-slate-700">{{ $kelas->siswa->count() }} Siswa</p>
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm">
        <h3 class="text-sm font-bold text-slate-700 mb-4 px-2">Daftar Siswa</h3>
        
        <div class="overflow-x-auto">
            <table class="float-table text-left">
                <thead>
                    <tr class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        <th class="px-4 py-2 pl-6">No</th>
                        <th class="px-4 py-2">Nama Siswa</th>
                        <th class="px-4 py-2">NISN</th>
                        <th class="px-4 py-2">Wali Murid</th>
                        <th class="px-4 py-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kelas->siswa as $index => $s)
                    <tr class="float-row group">
                        <td class="px-4 py-3 pl-6 text-slate-400 text-sm font-mono">{{ $index + 1 }}</td>

                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs">
                                    {{ substr($s->nama_siswa ?? $s->nama, 0, 1) }}
                                </div>
                                <span class="font-medium text-slate-700 text-sm">{{ $s->nama_siswa ?? $s->nama }}</span>
                            </div>
                        </td>

                        <td class="px-4 py-3 text-sm text-slate-600 font-mono">{{ $s->nisn ?? '-' }}</td>

                        <td class="px-4 py-3 text-sm text-slate-500">
                            {{ $s->waliMurid->nama ?? $s->waliMurid->username ?? '-' }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-slate-400 text-sm">
                            Belum ada siswa terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection