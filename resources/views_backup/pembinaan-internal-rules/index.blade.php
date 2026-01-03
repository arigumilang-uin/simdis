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
                    <i class="fas fa-user-check text-indigo-600"></i> Aturan Pembinaan Internal
                </h1>
                <p class="text-slate-500 text-sm mt-1">Threshold pembinaan berdasarkan akumulasi poin siswa.</p>
            </div>
            
            <button type="button" class="btn-primary-custom" data-toggle="modal" data-target="#modalTambahRule">
                <i class="fas fa-plus-circle mr-2"></i> Tambah Aturan Baru
            </button>
        </div>

        {{-- ALERTS --}}
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 shadow-sm alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle text-emerald-600"></i>
                <span class="font-medium text-sm">{{ session('success') }}</span>
                <button type="button" class="close ml-auto outline-none border-none bg-transparent" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <div class="mb-8 p-4 bg-indigo-50 border-l-4 border-indigo-500 rounded-r-xl shadow-sm text-sm text-indigo-800">
            <div class="flex gap-3">
                <i class="fas fa-info-circle mt-1"></i>
                <div>
                    <span class="font-bold">Penting:</span> Pembinaan internal adalah rekomendasi konseling, <strong>TIDAK</strong> memicu surat pemanggilan otomatis. Surat panggilan hanya dipicu oleh aturan frekuensi pelanggaran.
                </div>
            </div>
        </div>

        {{-- TABLE RULES (Solid Table Style) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Daftar Aturan Aktif</span>
                <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-full border border-indigo-100">
                    Total: {{ $rules->count() }} Aturan
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse custom-solid-table">
                    <thead>
                        <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4 w-16">#</th>
                            <th class="px-6 py-4">Range Poin</th>
                            <th class="px-6 py-4">Pembina Terlibat</th>
                            <th class="px-6 py-4">Keterangan</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($rules as $rule)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 text-sm font-bold text-slate-400">{{ $rule->display_order }}</td>
                            <td class="px-6 py-4">
                                <span class="custom-badge-base bg-indigo-100 text-indigo-700 font-bold whitespace-nowrap">
                                    {{ $rule->getRangeText() }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($rule->pembina_roles as $role)
                                        <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-600 text-[10px] font-bold border border-blue-100 whitespace-nowrap">
                                            {{ $role }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs text-slate-600 leading-relaxed max-w-md italic">
                                    {{ $rule->keterangan }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <button type="button" class="btn-action hover:text-amber-500 hover:border-amber-100" data-toggle="modal" data-target="#modalEditRule{{ $rule->id }}" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('pembinaan-internal-rules.destroy', $rule->id) }}" method="POST" class="m-0" onsubmit="return confirm('Hapus aturan ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn-action hover:text-rose-500 hover:border-rose-100 cursor-pointer border-none bg-transparent outline-none"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade" id="modalEditRule{{ $rule->id }}" tabindex="-1" role="dialog">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content rounded-2xl border-none shadow-2xl overflow-hidden">
                                    <form action="{{ route('pembinaan-internal-rules.update', $rule->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <div class="modal-header bg-slate-800 text-white border-none p-6">
                                            <h5 class="modal-title font-bold">Edit Aturan Poin</h5>
                                            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body p-6 bg-slate-50">
                                            @include('pembinaan-internal-rules.partials.form', ['rule' => $rule])
                                        </div>
                                        <div class="modal-footer bg-white p-4 gap-2">
                                            <button type="button" class="btn-filter-secondary" data-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn-filter-primary bg-indigo-600">Update Aturan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center opacity-60">
                                    <i class="fas fa-user-check text-3xl mb-2 text-slate-300"></i>
                                    <p class="text-sm">Belum ada aturan pembinaan internal.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <h6 class="text-indigo-700 font-bold flex items-center gap-2 mb-4 text-sm">
                <i class="fas fa-balance-scale"></i> Perbandingan Sistem Aturan
            </h6>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                            <th class="py-3 px-2">Fitur</th>
                            <th class="py-3 px-2">Frequency Rules</th>
                            <th class="py-3 px-2">Internal Rules (Akumulasi Poin)</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs text-slate-600">
                        <tr class="border-b border-slate-50">
                            <td class="py-3 px-2 font-bold text-slate-700">Dasar Hitung</td>
                            <td class="py-3 px-2">Frekuensi kejadian pelanggaran yang sama</td>
                            <td class="py-3 px-2">Total akumulasi poin dari seluruh pelanggaran</td>
                        </tr>
                        <tr class="border-b border-slate-50">
                            <td class="py-3 px-2 font-bold text-slate-700">Output Utama</td>
                            <td class="py-3 px-2 font-medium text-rose-600">Surat Panggilan Resmi Orang Tua</td>
                            <td class="py-3 px-2 font-medium text-indigo-600">Rekomendasi Konseling/Tingkat Pembina</td>
                        </tr>
                        <tr class="border-b border-slate-50">
                            <td class="py-3 px-2 font-bold text-slate-700">Sifat Penanganan</td>
                            <td class="py-3 px-2">Sanksi Administratif Luar</td>
                            <td class="py-3 px-2">Bimbingan & Monitoring Internal Sekolah</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahRule" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content rounded-2xl border-none shadow-2xl overflow-hidden">
            <form action="{{ route('pembinaan-internal-rules.store') }}" method="POST" id="formTambahRule">
                @csrf
                <div class="modal-header bg-indigo-600 text-white border-none p-6">
                    <h5 class="modal-title font-bold">Tambah Aturan Pembinaan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-6 bg-slate-50 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Poin Min *</label>
                            <input type="number" name="poin_min" id="new_poin_min" class="custom-input-clean w-full" value="{{ old('poin_min', $suggestedPoinMin) }}" min="0" required>
                            <small class="text-indigo-500 font-medium text-[9px] mt-1 block italic">Rekomendasi: {{ $suggestedPoinMin }}.</small>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Poin Max</label>
                            <input type="number" name="poin_max" id="new_poin_max" class="custom-input-clean w-full" value="{{ old('poin_max') }}" min="0">
                            <small class="text-slate-400 text-[9px] mt-1 block">Kosongkan untuk tak terhingga.</small>
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Pembina yang Terlibat *</label>
                        <div class="bg-white border border-slate-200 rounded-xl p-4 grid grid-cols-2 gap-2">
                            @foreach(['Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Kepala Sekolah'] as $role)
                                <label class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer mb-0">
                                    <input type="checkbox" name="pembina_roles[]" value="{{ $role }}" class="new-pembina-checkbox rounded border-slate-300">
                                    {{ $role }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Keterangan *</label>
                        <textarea name="keterangan" id="new_keterangan" rows="3" class="custom-input-clean w-full" placeholder="Contoh: Pembinaan sedang, monitoring ketat..." required>{{ old('keterangan') }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Urutan Tampilan</label>
                            <input type="number" name="display_order" id="new_display_order" class="custom-input-clean w-full" value="{{ old('display_order', $suggestedDisplayOrder) }}" min="1">
                        </div>
                        <div class="bg-blue-50 p-3 rounded-xl border border-blue-100 flex items-center gap-2 self-end">
                            <i class="fas fa-lightbulb text-blue-500 text-xs"></i>
                            <p class="m-0 text-[10px] text-blue-700 leading-tight">Urutan disarankan: <b>{{ $suggestedDisplayOrder }}</b>.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white p-4 gap-2">
                    <button type="button" class="btn-filter-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-filter-primary bg-indigo-600 border-none">Simpan Aturan</button>
                </div>
            </form>
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
    background-color: #10b981; color: white; padding: 0.6rem 1.2rem; border-radius: 0.75rem;
    font-weight: 700; font-size: 0.85rem; border: none; display: inline-flex; align-items: center;
    transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.2); text-decoration: none !important;
}
.btn-primary-custom:hover { background-color: #059669; transform: translateY(-1px); color: white; }

/* Form Controls */
.custom-input-clean {
    border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.6rem 0.8rem;
    font-size: 0.85rem; background: white; outline: none; transition: 0.2s;
}
.custom-input-clean:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }

.btn-filter-primary {
    background: #4f46e5; color: white; border: none; padding: 0.6rem 1.2rem;
    border-radius: 0.75rem; font-weight: 700; font-size: 0.85rem; transition: 0.2s; cursor: pointer;
}
.btn-filter-primary:hover { background: #4338ca; color: white; }

.btn-filter-secondary {
    background: #f1f5f9; color: #475569; padding: 0.6rem 1.2rem; border-radius: 0.75rem;
    font-weight: 700; font-size: 0.85rem; border: none; text-decoration: none !important; cursor: pointer;
}

/* Solid Table Styling */
.custom-solid-table thead th {
    vertical-align: middle;
}
.custom-solid-table tbody td {
    vertical-align: middle;
}

.custom-badge-base { display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; border-radius: 0.5rem; font-size: 0.7rem; }
.btn-action { 
    padding: 6px; border-radius: 8px; transition: 0.2s; color: #94a3b8; border: 1px solid transparent; 
    background: transparent; cursor: pointer; display: inline-flex;
}
.btn-action:hover { background: #f8fafc; border-color: #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }

/* Modals Override */
.modal-backdrop { opacity: 0.5 !important; }
.close { background: transparent; border: none; font-size: 1.5rem; cursor: pointer; }
</style>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    setTimeout(() => { $('.alert').fadeOut('slow'); }, 5000);
    
    $('#modalTambahRule').on('hidden.bs.modal', function () {
        $('#formTambahRule')[0].reset();
        $('#new_poin_min').val('{{ $suggestedPoinMin }}');
        $('#new_display_order').val('{{ $suggestedDisplayOrder }}');
        $('.new-pembina-checkbox').prop('checked', false);
    });
});
</script>
@endpush