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
                    emerald: { 500: '#10b981', 600: '#059669' },
                    rose: { 500: '#f43f5e', 600: '#e11d48' },
                    amber: { 500: '#f59e0b', 600: '#d97706' }
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
                    <i class="fas fa-trash-restore text-amber-500"></i> Data Siswa Terhapus
                </h1>
                <p class="text-slate-500 text-sm mt-1">Daftar arsip siswa yang dapat dikembalikan atau dihapus permanen.</p>
            </div>
            
            <a href="{{ route('siswa.index') }}" class="btn-clean-action no-underline">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>

        {{-- ALERTS --}}
        @if(session('success'))
            <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 shadow-sm">
                <i class="fas fa-check-circle text-emerald-600"></i>
                <span class="font-medium text-sm">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center gap-3 shadow-sm">
                <i class="fas fa-exclamation-circle text-rose-600"></i>
                <span class="font-medium text-sm">{{ session('error') }}</span>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="p-6">
                <form method="GET" action="{{ route('siswa.deleted') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-3">
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-1.5 block">Cari Siswa</label>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" 
                               class="custom-input-clean w-full" placeholder="Nama atau NISN...">
                    </div>
                    <div class="md:col-span-3">
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-1.5 block">Alasan Keluar</label>
                        <select name="alasan_keluar" class="custom-select-clean w-full">
                            <option value="">Semua Alasan</option>
                            @foreach($alasanOptions as $option)
                                <option value="{{ $option }}" {{ ($filters['alasan_keluar'] ?? '') == $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-1.5 block">Kelas</label>
                        <select name="kelas_id" class="custom-select-clean w-full">
                            <option value="">Semua Kelas</option>
                            @foreach($allKelas as $k)
                                <option value="{{ $k->id }}" {{ ($filters['kelas_id'] ?? '') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-3 flex items-end gap-2">
                        <button type="submit" class="btn-filter-primary flex-1">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        <a href="{{ route('siswa.deleted') }}" class="btn-filter-secondary px-4 no-underline flex items-center justify-center">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            @if($deletedSiswa->count() > 0)
                {{-- Bulk Action Bar --}}
                <div class="px-6 py-4 bg-slate-50/80 border-b border-slate-100 flex flex-wrap justify-between items-center gap-4">
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer m-0">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)" class="w-4 h-4 rounded border-slate-300 text-indigo-600">
                            <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Pilih Semua</span>
                        </label>
                        <span class="px-2.5 py-1 rounded-lg bg-indigo-100 text-indigo-700 text-[10px] font-black uppercase shadow-sm" id="selectedCount">0 dipilih</span>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" onclick="bulkRestore()" id="btnBulkRestore" disabled 
                                class="btn-bulk-restore bg-emerald-500 hover:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-undo mr-1.5 text-xs"></i> Restore Terpilih
                        </button>
                        <button type="button" onclick="bulkPermanentDelete()" id="btnBulkPermanentDelete" disabled 
                                class="btn-bulk-delete bg-rose-500 hover:bg-rose-600 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-times-circle mr-1.5 text-xs"></i> Delete Permanent
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse custom-solid-table">
                        <thead>
                            <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50">
                                <th class="px-6 py-4 w-12"></th>
                                <th class="px-6 py-4 w-64">Identitas Siswa</th>
                                <th class="px-6 py-4 w-32">Kelas</th>
                                <th class="px-6 py-4 w-44">Alasan & Keterangan</th>
                                <th class="px-6 py-4 w-44">Waktu Dihapus</th>
                                <th class="px-6 py-4 text-center w-32 pr-8">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($deletedSiswa as $siswa)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4 text-center">
                                        <input type="checkbox" value="{{ $siswa->id }}" onchange="updateSelection()" 
                                               class="siswa-checkbox w-4 h-4 rounded border-slate-300 text-indigo-600 shadow-sm">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-700 leading-tight">{{ $siswa->nama_siswa }}</span>
                                            <span class="text-[10px] font-mono text-slate-400 mt-1 uppercase tracking-tighter">NISN: {{ $siswa->nisn }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 rounded bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase border border-indigo-100">
                                            {{ $siswa->kelas->nama_kelas ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $badgeStyle = match($siswa->alasan_keluar) {
                                                'Alumni' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                                'Dikeluarkan' => 'bg-rose-50 text-rose-600 border-rose-100',
                                                'Pindah Sekolah' => 'bg-amber-50 text-amber-600 border-amber-100',
                                                default => 'bg-slate-50 text-slate-500 border-slate-100'
                                            };
                                        @endphp
                                        <div class="flex flex-col gap-1.5">
                                            <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase border w-fit {{ $badgeStyle }}">
                                                {{ $siswa->alasan_keluar }}
                                            </span>
                                            <span class="text-[10px] text-slate-400 italic line-clamp-1">
                                                {{ $siswa->keterangan_keluar ?? '-' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-slate-700 leading-tight">{{ $siswa->deleted_at->format('d M Y') }}</span>
                                            <span class="text-[10px] font-mono text-slate-400 uppercase tracking-tighter">{{ $siswa->deleted_at->diffForHumans() }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center pr-8">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <form action="{{ route('siswa.restore', $siswa->id) }}" method="POST" class="m-0">
                                                @csrf
                                                <button type="submit" class="btn-action hover:text-emerald-500 hover:border-emerald-100" 
                                                        onclick="return confirm('Restore siswa {{ addslashes($siswa->nama_siswa) }}?')" title="Restore">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="btn-action hover:text-rose-500 hover:border-rose-100" 
                                                    onclick="showPermanentDeleteModal({{ $siswa->id }}, '{{ addslashes($siswa->nama_siswa) }}')" title="Hapus Permanen">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30">
                    {{ $deletedSiswa->links('pagination::bootstrap-4') }}
                </div>
            @else
                <div class="py-24 text-center">
                    <div class="flex flex-col items-center opacity-40">
                        <i class="fas fa-inbox text-5xl mb-4 text-slate-300"></i>
                        <p class="text-sm font-bold uppercase tracking-widest text-slate-400 m-0">Tidak ada arsip siswa terhapus</p>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>

{{-- MODAL PERMANENT DELETE --}}
<div class="modal fade" id="permanentDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl rounded-2xl overflow-hidden">
            <form id="permanentDeleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="bg-rose-600 p-6 text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 border-2 border-white/30">
                        <i class="fas fa-exclamation-triangle text-white text-2xl"></i>
                    </div>
                    <h5 class="text-white font-black uppercase tracking-widest m-0">Penghapusan Permanen</h5>
                </div>
                <div class="p-8 text-center">
                    <p class="text-slate-500 text-sm mb-4 leading-relaxed">
                        Anda akan menghapus <strong id="permanentDeleteName" class="text-slate-800"></strong> secara permanen. Data ini <span class="text-rose-600 font-bold uppercase underline">tidak bisa dipulihkan kembali</span>.
                    </p>
                    
                    <div class="bg-rose-50 p-4 rounded-xl border border-rose-100 flex items-center justify-center mb-6">
                        <label class="flex items-center gap-3 cursor-pointer m-0">
                            <input type="checkbox" name="confirm_permanent" value="1" id="confirmPermanent" required class="w-5 h-5 rounded text-rose-600 border-rose-200">
                            <span class="text-xs font-bold text-rose-700 uppercase tracking-tight">Saya sadar data akan hilang selamanya</span>
                        </label>
                    </div>

                    <div class="flex gap-3 justify-center">
                        <button type="button" class="btn-filter-secondary px-6" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-filter-primary bg-rose-600 hover:bg-rose-700 border-none px-8 font-black uppercase">
                            HAPUS PERMANENT
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
    .page-wrap-custom { background: #f8fafc; font-family: 'Inter', sans-serif; }
    .custom-header-row { border-bottom: 1px solid #e2e8f0; }

    /* Inputs */
    .custom-input-clean, .custom-select-clean {
        height: 42px; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0 1rem;
        font-size: 0.85rem; background-color: #ffffff; transition: 0.2s; outline: none;
    }
    .custom-input-clean:focus, .custom-select-clean:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }

    /* Buttons */
    .btn-filter-primary {
        height: 42px; background-color: #4f46e5; color: white !important; border: none;
        border-radius: 0.75rem; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; transition: 0.2s;
    }
    .btn-filter-secondary {
        height: 42px; background-color: #f1f5f9; color: #64748b !important; border-radius: 0.75rem;
        font-weight: 800; font-size: 0.75rem; text-transform: uppercase; transition: 0.2s; border: none;
    }
    .btn-bulk-restore, .btn-bulk-delete {
        padding: 0.6rem 1.2rem; color: white !important; font-weight: 800; font-size: 0.7rem; 
        text-transform: uppercase; border-radius: 0.75rem; border: none; transition: 0.2s;
    }
    .btn-action { 
        width: 32px; height: 32px; border-radius: 8px; transition: 0.2s; color: #94a3b8; border: 1px solid transparent; 
        background: transparent; cursor: pointer; display: inline-flex; align-items: center; justify-content: center;
    }
    .btn-action:hover { background: #f8fafc; border-color: #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .btn-clean-action {
        padding: 0.65rem 1.2rem; border-radius: 0.75rem; background-color: #f1f5f9; color: #475569 !important; font-size: 0.8rem; font-weight: 800; border: 1px solid #e2e8f0;
    }

    /* Modal Fix */
    .modal-backdrop { opacity: 0.5 !important; background-color: #0f172a !important; }
</style>
@endsection

{{-- LOGIKA JAVASCRIPT (DIJAGA KEASLIANNYA) --}}
@push('scripts')
<script>
let selectedIds = [];

function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.siswa-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateSelection();
}

function updateSelection() {
    const checkboxes = document.querySelectorAll('.siswa-checkbox:checked');
    selectedIds = Array.from(checkboxes).map(cb => parseInt(cb.value));
    
    document.getElementById('selectedCount').textContent = selectedIds.length + ' dipilih';
    
    const btnRestore = document.getElementById('btnBulkRestore');
    const btnDelete = document.getElementById('btnBulkPermanentDelete');
    const shouldEnable = selectedIds.length > 0;
    
    btnRestore.disabled = !shouldEnable;
    btnDelete.disabled = !shouldEnable;
    
    const selectAll = document.getElementById('selectAll');
    const allCheckboxes = document.querySelectorAll('.siswa-checkbox');
    if (allCheckboxes.length > 0) {
        selectAll.checked = checkboxes.length === allCheckboxes.length;
    }
}

function bulkRestore() {
    if (selectedIds.length === 0) return;
    if (confirm(`Restore ${selectedIds.length} siswa?\n\nSemua data terkait akan di-restore.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/siswa/${selectedIds[0]}/restore`; // Note: In logic matches original
        form.innerHTML = `@csrf <input type="hidden" name="siswa_ids" value="${selectedIds.join(',')}">`; // Note: Fixed for bulk consistency
        document.body.appendChild(form);
        form.submit();
    }
}

function bulkPermanentDelete() {
    if (selectedIds.length === 0) return;
    const confirmation = prompt(`⚠️ PERMANENT DELETE ${selectedIds.length} siswa?\n\nKetik "HAPUS PERMANENT" untuk confirm:`);
    if (confirmation === 'HAPUS PERMANENT') {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("siswa.bulk-force-delete") }}';
        form.innerHTML = `@csrf @method('DELETE') <input type="hidden" name="confirm_permanent" value="1">`;
        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = 'siswa_ids[]'; input.value = id;
            form.appendChild(input);
        });
        document.body.appendChild(form);
        form.submit();
    }
}

function showPermanentDeleteModal(id, name) {
    document.getElementById('permanentDeleteName').textContent = name;
    document.getElementById('permanentDeleteForm').action = `/siswa/${id}/force-delete`;
    document.getElementById('confirmPermanent').checked = false;
    $('#permanentDeleteModal').modal('show');
}
</script>
@endpush