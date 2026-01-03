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
                    <i class="fas fa-history text-indigo-600"></i> Riwayat Pelanggaran Saya
                </h1>
                <p class="text-slate-500 text-sm mt-1">Daftar semua catatan pelanggaran siswa yang telah Anda laporkan.</p>
            </div>
            
            <div class="flex items-center gap-2">
                <a href="{{ route('riwayat.create') }}" class="btn-primary-custom no-underline bg-indigo-600 hover:bg-indigo-700 shadow-indigo-200">
                    <i class="fas fa-plus-circle mr-2"></i> Lapor Pelanggaran
                </a>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="p-6">
                <form method="GET" action="{{ route('my-riwayat.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <div class="md:col-span-4">
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-1.5 block tracking-tight">Dari Tanggal</label>
                        <input type="date" name="start_date" class="custom-input-clean w-full" value="{{ request('start_date') }}">
                    </div>
                    <div class="md:col-span-4">
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-1.5 block tracking-tight">Sampai Tanggal</label>
                        <input type="date" name="end_date" class="custom-input-clean w-full" value="{{ request('end_date') }}">
                    </div>
                    <div class="md:col-span-4 flex gap-2">
                        <button type="submit" class="btn-filter-primary flex-1">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        <a href="{{ route('my-riwayat.index') }}" class="btn-filter-secondary px-4 no-underline flex items-center">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100 flex justify-between items-center">
                <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Daftar Catatan</span>
                <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100">
                    Total: {{ $riwayat->total() }} Laporan
                </span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse custom-solid-table">
                    <thead>
                        <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4 w-16 text-center">#</th>
                            <th class="px-6 py-4 w-44">Waktu Kejadian</th>
                            <th class="px-6 py-4 w-64">Siswa & Kelas</th>
                            <th class="px-6 py-4">Jenis Pelanggaran</th>
                            <th class="px-6 py-4 w-24 text-center">Bukti</th>
                            <th class="px-6 py-4 text-center w-32 pr-8">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($riwayat as $r)
                        <tr class="hover:bg-slate-50/50 transition-all duration-200">
                            <td class="px-6 py-4 text-center">
                                <span class="text-xs font-bold text-slate-300 leading-none">
                                    {{ $loop->iteration + ($riwayat->currentPage()-1)*$riwayat->perPage() }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-700 leading-tight">
                                        {{ optional($r->tanggal_kejadian)->format('d M Y') }}
                                    </span>
                                    <span class="text-[10px] font-mono text-slate-400 uppercase tracking-tighter mt-1">
                                        {{ optional($r->tanggal_kejadian)->format('H:i') }} WIB
                                    </span>
                                </div>
                            </td>
                                                        <td class="px-6 py-4">
                                <div class="flex flex-col min-w-0">
                                    <span class="text-sm font-bold text-slate-700 leading-tight truncate">
                                        {{ $r->siswa?->nama }}
                                    </span>
                                    <span class="text-[10px] text-indigo-500 font-bold uppercase tracking-wide mt-1">
                                        {{ $r->siswa?->kelas?->nama_kelas }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-slate-700 leading-relaxed">{{ $r->jenisPelanggaran?->nama_pelanggaran }}</span>
                                    <span class="text-[10px] text-slate-400 italic line-clamp-1 mt-1">{{ $r->keterangan }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($r->bukti_foto_path)
                                    <a href="{{ route('bukti.show', $r->bukti_foto_path) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 transition-colors">
                                        <i class="fas fa-image text-lg"></i>
                                    </a>
                                @else
                                    <span class="text-slate-300 text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center pr-8">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('my-riwayat.edit', $r->id) }}" class="btn-action hover:text-indigo-600 hover:border-indigo-100" title="Edit">
                                        <i class="fas fa-edit text-xs"></i>
                                    </a>
                                    <form action="{{ route('my-riwayat.destroy', $r->id) }}" method="POST" class="m-0" onsubmit="return confirm('Yakin ingin menghapus catatan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action hover:text-rose-500 hover:border-rose-100 border-none bg-transparent">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center text-slate-400 bg-white">
                                <div class="flex flex-col items-center opacity-40">
                                    <i class="fas fa-history text-4xl mb-4 text-slate-300"></i>
                                    <p class="text-sm font-bold uppercase tracking-widest">Belum ada riwayat pelanggaran</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($riwayat->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30">
                <div class="flex justify-center">
                    {{ $riwayat->links() }}
                </div>
            </div>
            @endif
        </div>

    </div>
</div>

@endsection

@section('styles')
<style>
/* --- CORE STYLING --- */
.page-wrap-custom { background: #f8fafc; min-height: 100vh; padding: 1.5rem; font-family: 'Inter', sans-serif; }
.custom-header-row { border-bottom: 1px solid #e2e8f0; }

.btn-primary-custom {
    color: white !important; padding: 0.65rem 1.2rem; border-radius: 0.75rem;
    font-weight: 800; font-size: 0.8rem; border: none; display: inline-flex; align-items: center;
    transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
}
.btn-primary-custom:hover { transform: translateY(-1px); filter: brightness(1.1); }

/* Form Controls */
.custom-input-clean {
    border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.65rem 1rem;
    font-size: 0.85rem; background: white; outline: none; transition: 0.2s;
}
.custom-input-clean:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }

.btn-filter-primary {
    background: #4f46e5; color: white; border: none; border-radius: 0.75rem; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; padding: 0.65rem 1rem; cursor: pointer; transition: 0.2s;
}
.btn-filter-secondary {
    background: #f1f5f9; color: #64748b; border-radius: 0.75rem; font-weight: 800; border: none; cursor: pointer; transition: 0.2s;
}

/* Solid Table Styling */
.custom-solid-table thead th { vertical-align: middle; }
.custom-solid-table tbody td { vertical-align: middle; border-bottom: 1px solid #f1f5f9; }

.btn-action { 
    width: 32px; height: 32px; border-radius: 10px; transition: 0.2s; color: #94a3b8; border: 1px solid #f1f5f9; 
    background: #f8fafc; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;
}
.btn-action:hover { background: white; border-color: #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }

/* Fix Pagination Bootstrap vs Tailwind */
.pagination { margin: 0; display: flex; list-style: none; }
.page-item .page-link { border-radius: 8px !important; margin: 0 2px; color: #64748b; border: 1px solid #e2e8f0; padding: 0.5rem 0.8rem; }
.page-item.active .page-link { background-color: #4f46e5 !important; border-color: #4f46e5 !important; color: white; }
</style>
@endsection