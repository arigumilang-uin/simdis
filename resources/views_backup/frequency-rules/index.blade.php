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
                    indigo: { 600: '#4f46e5', 50: '#eef2ff', 100: '#e0e7ff', 700: '#4338ca' }
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
                    <i class="fas fa-sliders-h text-indigo-600"></i> Kelola Aturan Pelanggaran
                </h1>
                <p class="text-slate-500 text-sm mt-1">Kelola jenis pelanggaran, poin, sanksi, dan frequency rules.</p>
            </div>
            
            <a href="{{ route('jenis-pelanggaran.create') }}" class="btn-primary-custom no-underline">
                <i class="fas fa-plus-circle mr-2"></i> Tambah Jenis Pelanggaran
            </a>
        </div>

        {{-- ALERTS --}}
        @if(session('success'))
            <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 shadow-sm alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle text-emerald-600"></i>
                <span class="font-medium text-sm">{{ session('success') }}</span>
                <button type="button" class="close ml-auto outline-none border-none bg-transparent" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <div class="mb-8 p-4 bg-slate-800 rounded-2xl shadow-lg text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-10">
                <i class="fas fa-info-circle fa-4x"></i>
            </div>
            <div class="relative z-10 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <span class="text-[10px] font-bold text-indigo-300 uppercase tracking-widest block mb-1">Status</span>
                    <p class="text-xs text-slate-300 m-0">Toggle aktifkan pelanggaran. Pelanggaran nonaktif tidak akan muncul di form.</p>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-indigo-300 uppercase tracking-widest block mb-1">Frequency Rules</span>
                    <p class="text-xs text-slate-300 m-0">Atur poin & sanksi otomatis berdasarkan berapa kali siswa melanggar.</p>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-indigo-300 uppercase tracking-widest block mb-1">Poin Default</span>
                    <p class="text-xs text-slate-300 m-0">Gunakan poin langsung jika pelanggaran tidak memiliki frequency rules.</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-3 rounded-2xl border border-slate-200 shadow-sm mb-6 flex flex-wrap items-center gap-4">
            <form method="GET" action="{{ route('frequency-rules.index') }}" class="flex items-center gap-2 m-0">
                <select name="kategori_id" class="custom-input-clean py-1.5 min-w-[200px]">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoris as $kat)
                        <option value="{{ $kat->id }}" {{ $kategoriId == $kat->id ? 'selected' : '' }}>
                            {{ $kat->nama_kategori }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn-filter-primary py-1.5 px-4">Filter</button>
                <a href="{{ route('frequency-rules.index') }}" class="btn-filter-secondary py-1.5 px-4 no-underline">Reset</a>
            </form>
        </div>

        {{-- MAIN SOLID TABLE --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse custom-solid-table">
                    <thead>
                        <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4 w-32">Kategori</th>
                            <th class="px-6 py-4">Nama Pelanggaran</th>
                            <th class="px-6 py-4 w-[40%]">Rules (Frekuensi, Poin & Sanksi)</th>
                            <th class="px-6 py-4 text-center w-32">Status</th>
                            <th class="px-6 py-4 text-center w-48">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($jenisPelanggaran as $jp)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            {{-- Kategori --}}
                            <td class="px-6 py-4">
                                @php
                                    $kategoriNama = $jp->kategoriPelanggaran->nama_kategori ?? 'Unknown';
                                    $colorClass = $kategoriNama == 'Ringan' ? 'bg-blue-100 text-blue-600' : ($kategoriNama == 'Sedang' ? 'bg-amber-100 text-amber-600' : 'bg-rose-100 text-rose-600');
                                @endphp
                                <span class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase {{ $colorClass }}">
                                    {{ $kategoriNama }}
                                </span>
                            </td>

                            {{-- Nama --}}
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-700 leading-snug">{{ $jp->nama_pelanggaran }}</div>
                                <div class="text-[10px] font-mono text-slate-400 mt-1 uppercase tracking-tighter">ID: {{ $jp->id }}</div>
                            </td>

                            {{-- Rules List --}}
                            <td class="px-6 py-4">
                                @if($jp->frequencyRules->count() > 0)
                                    <div class="space-y-2">
                                        @foreach($jp->frequencyRules as $rule)
                                        <div class="p-2.5 rounded-xl border border-slate-100 bg-white shadow-sm hover:border-indigo-200 transition-all">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-slate-800 text-white">
                                                    @if($rule->frequency_min == 1 && !$rule->frequency_max) Setiap @elseif($rule->frequency_max) {{$rule->frequency_min}}-{{$rule->frequency_max}}x @else {{$rule->frequency_min}}+x @endif
                                                </span>
                                                <span class="text-[10px] font-bold text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded">{{ $rule->poin }} Poin</span>
                                                @if($rule->trigger_surat)
                                                    <span class="text-[9px] font-bold text-amber-600 bg-amber-50 border border-amber-100 px-1.5 py-0.5 rounded flex items-center gap-1"><i class="fas fa-envelope"></i> SURAT</span>
                                                @endif
                                            </div>
                                            <div class="text-[11px] text-slate-600 leading-tight">
                                                <span class="font-bold text-slate-400 uppercase text-[8px]">Sanksi:</span> {{ $rule->sanksi_description }}
                                            </div>
                                            <div class="flex flex-wrap gap-1 mt-1">
                                                @foreach($rule->pembina_roles as $role)
                                                    <span class="text-[8px] bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded font-bold uppercase">{{ $role }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="p-3 rounded-xl border border-dashed border-slate-200 bg-slate-50 text-center">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase">Default: {{ $jp->poin }} Poin (Setiap Kejadian)</span>
                                    </div>
                                @endif
                            </td>

                            {{-- Toggle Status --}}
                            <td class="px-6 py-4 text-center">
                                @if($jp->frequencyRules->count() > 0)
                                    <label class="relative inline-flex items-center cursor-pointer group">
                                        <input type="checkbox" class="sr-only peer toggle-active" data-id="{{ $jp->id }}" {{ $jp->is_active ? 'checked' : '' }}>
                                        <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                        <span class="ml-2 text-[10px] font-bold text-slate-400 peer-checked:text-emerald-600 uppercase toggle-label-text">
                                            {{ $jp->is_active ? 'Aktif' : 'Off' }}
                                        </span>
                                    </label>
                                @else
                                    <span class="text-[10px] font-bold text-rose-400 uppercase italic">No Rules</span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('frequency-rules.show', $jp->id) }}" class="btn-action hover:text-indigo-600 hover:border-indigo-100" title="Rules">
                                        <i class="fas fa-sliders-h"></i>
                                    </a>
                                    <a href="{{ route('jenis-pelanggaran.edit', $jp->id) }}" class="btn-action hover:text-amber-500 hover:border-amber-100" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('jenis-pelanggaran.destroy', $jp->id) }}" method="POST" class="m-0" onsubmit="return confirm('Yakin ingin menghapus?')">
                                        @csrf @method('DELETE')
                                        <button class="btn-action hover:text-rose-500 hover:border-rose-100 border-none bg-transparent outline-none"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">Tidak ada data jenis pelanggaran.</td></tr>
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
/* --- CORE STYLING --- */
.page-wrap-custom { background: #f8fafc; min-height: 100vh; padding: 1.5rem; font-family: 'Inter', sans-serif; }
.custom-header-row { border-bottom: 1px solid #e2e8f0; }

.btn-primary-custom {
    background-color: #10b981; color: white !important; padding: 0.6rem 1.2rem; border-radius: 0.75rem;
    font-weight: 700; font-size: 0.85rem; border: none; display: inline-flex; align-items: center;
    transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2); text-decoration: none !important;
}
.btn-primary-custom:hover { background-color: #059669; transform: translateY(-1px); }

/* Form Controls */
.custom-input-clean {
    border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.4rem 0.8rem;
    font-size: 0.85rem; background: white; outline: none; transition: 0.2s;
}
.custom-input-clean:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }

.btn-filter-primary {
    background: #4f46e5; color: white; border: none; border-radius: 0.75rem; font-weight: 700; font-size: 0.8rem; transition: 0.2s; cursor: pointer;
}
.btn-filter-secondary {
    background: #f1f5f9; color: #475569; border-radius: 0.75rem; font-weight: 700; font-size: 0.8rem; border: none; cursor: pointer;
}

/* Solid Table Styling */
.custom-solid-table thead th { vertical-align: middle; }
.custom-solid-table tbody td { vertical-align: middle; border-top: 1px solid #f1f5f9; }

.btn-action { 
    width: 32px; height: 32px; border-radius: 8px; transition: 0.2s; color: #94a3b8; border: 1px solid transparent; 
    background: transparent; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;
}
.btn-action:hover { background: #f8fafc; border-color: #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }

/* Switch Toggle UI */
.peer-checked\:text-emerald-600:checked ~ .toggle-label-text { color: #059669; }

.close { background: transparent; border: none; font-size: 1.5rem; cursor: pointer; }
</style>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Ajax Toggle Active
    $('.toggle-active').change(function() {
        const checkbox = $(this);
        const id = checkbox.data('id');
        const isChecked = checkbox.is(':checked');
        const labelText = checkbox.closest('label').find('.toggle-label-text');
        
        $.ajax({
            url: `/frequency-rules/${id}/toggle-active`,
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    labelText.text(response.is_active ? 'Aktif' : 'Off');
                    toastr.success(response.message);
                }
            },
            error: function() {
                checkbox.prop('checked', !isChecked);
                toastr.error('Gagal mengubah status');
            }
        });
    });
});
</script>
@endpush