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
            <div class="flex items-center gap-4">
                <a href="{{ route('frequency-rules.index') }}" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50 transition-all shadow-sm no-underline">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 m-0 tracking-tight">Frequency Rules</h1>
                    <p class="text-slate-500 text-sm mt-1 flex items-center gap-2">
                        <span class="font-bold text-slate-700">{{ $jenisPelanggaran->nama_pelanggaran }}</span>
                        <span class="text-slate-300">|</span>
                        @php
                            $kategoriNama = $jenisPelanggaran->kategoriPelanggaran->nama_kategori ?? 'Unknown';
                            $colorClass = $kategoriNama == 'Ringan' ? 'text-blue-600 bg-blue-50 border-blue-100' : ($kategoriNama == 'Sedang' ? 'text-amber-600 bg-amber-50 border-amber-100' : 'text-rose-600 bg-rose-50 border-rose-100');
                        @endphp
                        <span class="px-2 py-0.5 rounded border {{ $colorClass }} text-[10px] font-black uppercase">{{ $kategoriNama }}</span>
                    </p>
                </div>
            </div>
            
            <div class="flex gap-2">
                <button class="btn-primary-custom" data-toggle="modal" data-target="#modalAddRule">
                    <i class="fas fa-plus-circle mr-2"></i> Tambah Rule
                </button>
                <a href="{{ route('jenis-pelanggaran.edit', $jenisPelanggaran->id) }}" class="btn-filter-secondary no-underline flex items-center gap-2">
                    <i class="fas fa-edit"></i> Edit
                </a>
            </div>
        </div>

        {{-- ALERTS --}}
        @if(session('success'))
            <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 shadow-sm alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle text-emerald-600"></i>
                <span class="font-medium text-sm">{{ session('success') }}</span>
                <button type="button" class="close ml-auto outline-none border-none bg-transparent" data-dismiss="alert">&times;</button>
            </div>
        @endif

        @if($jenisPelanggaran->frequencyRules->count() == 0)
            <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-xl shadow-sm">
                <div class="flex gap-3">
                    <i class="fas fa-info-circle mt-1 text-blue-600"></i>
                    <div class="text-sm text-blue-800">
                        <span class="font-bold">Belum ada rule:</span> Sistem akan menggunakan poin default 
                        <span class="font-black">({{ $jenisPelanggaran->poin }} poin)</span> setiap kali tercatat.
                    </div>
                </div>
            </div>
        @endif

        {{-- MAIN SOLID TABLE --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse custom-solid-table">
                    <thead>
                        <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4 w-16 text-center">Order</th>
                            <th class="px-6 py-4 w-32">Frekuensi</th>
                            <th class="px-6 py-4 w-24">Poin</th>
                            <th class="px-6 py-4">Sanksi / Deskripsi</th>
                            <th class="px-6 py-4 w-32 text-center">Surat</th>
                            <th class="px-6 py-4">Pembina</th>
                            <th class="px-6 py-4 text-center w-24 pr-8">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($jenisPelanggaran->frequencyRules as $rule)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 text-center">
                                <span class="text-xs font-black text-slate-400">{{ $rule->display_order }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded bg-slate-800 text-white text-[10px] font-bold whitespace-nowrap">
                                    @if($rule->frequency_max)
                                        {{ $rule->frequency_min }}-{{ $rule->frequency_max }}x
                                    @else
                                        {{ $rule->frequency_min }}+x
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold {{ $rule->poin > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                    +{{ $rule->poin }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-slate-700 font-medium leading-tight">{{ $rule->sanksi_description }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($rule->trigger_surat)
                                    <span class="px-2 py-1 rounded bg-amber-50 text-amber-600 border border-amber-100 text-[9px] font-black uppercase">
                                        <i class="fas fa-envelope mr-1"></i> Ya
                                    </span>
                                @else
                                    <span class="text-slate-300 text-[10px] font-bold uppercase tracking-tighter">Tidak</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($rule->pembina_roles as $role)
                                        <span class="text-[9px] bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded font-bold uppercase border border-indigo-100 whitespace-nowrap">
                                            {{ $role }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center pr-8">
                                <div class="flex items-center justify-center gap-1">
                                    <button class="btn-action hover:text-amber-500 hover:border-amber-100 btn-edit-rule" 
                                            data-id="{{ $rule->id }}"
                                            data-frequency-min="{{ $rule->frequency_min }}"
                                            data-frequency-max="{{ $rule->frequency_max }}"
                                            data-poin="{{ $rule->poin }}"
                                            data-sanksi="{{ $rule->sanksi_description }}"
                                            data-trigger-surat="{{ $rule->trigger_surat }}"
                                            data-pembina-roles="{{ json_encode($rule->pembina_roles) }}"
                                            data-display-order="{{ $rule->display_order }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('frequency-rules.destroy', $rule->id) }}" method="POST" class="m-0" onsubmit="return confirm('Hapus rule ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn-action hover:text-rose-500 hover:border-rose-100 outline-none"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400 italic">Belum ada frequency rules.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODALS TETAP MENGGUNAKAN LOGIKA ASLI (Hanya Refactor Style) --}}
<div class="modal fade" id="modalAddRule" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-2xl border-none shadow-2xl overflow-hidden">
            <form action="{{ route('frequency-rules.store', $jenisPelanggaran->id) }}" method="POST" id="formAddRule">
                @csrf
                <div class="modal-header bg-indigo-600 text-white border-none p-6">
                    <h5 class="modal-title font-bold">Tambah Frequency Rule</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-6 bg-slate-50 space-y-4">
                    @php
                        $existingRules = $jenisPelanggaran->frequencyRules;
                        $suggestedFreqMin = 1; $suggestedDisplayOrder = 1;
                        if ($existingRules->isNotEmpty()) {
                            $highestMax = $existingRules->max('frequency_max');
                            $suggestedFreqMin = $highestMax !== null ? $highestMax + 1 : $existingRules->max('frequency_min') + 1;
                            $suggestedDisplayOrder = $existingRules->max('display_order') + 1;
                        }
                    @endphp
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white p-4 rounded-xl border border-slate-200">
                            <label class="text-[10px] font-bold text-slate-400 uppercase mb-2 block tracking-widest">Atur Frekuensi</label>
                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="exactFrequencyMode">
                                <label class="custom-control-label text-xs font-bold text-slate-700" for="exactFrequencyMode">Mode Exact (Min=Max)</label>
                            </div>
                            <div class="flex gap-2">
                                <div class="flex-1">
                                    <input type="number" name="frequency_min" id="add_frequency_min" class="custom-input-clean w-full" value="{{ old('frequency_min', $suggestedFreqMin) }}" min="1" required placeholder="Min">
                                </div>
                                <div class="flex-1">
                                    <input type="number" name="frequency_max" id="add_frequency_max" class="custom-input-clean w-full" min="1" placeholder="Max (Opsional)">
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-4 rounded-xl border border-slate-200 flex flex-col justify-between">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase mb-2 block tracking-widest">Poin & Urutan</label>
                                <div class="flex gap-2">
                                    <input type="number" name="poin" class="custom-input-clean w-full" placeholder="Poin" required min="0" value="0">
                                    <input type="number" name="display_order" class="custom-input-clean w-full" value="{{ $suggestedDisplayOrder }}" min="1">
                                </div>
                            </div>
                            <div class="flex items-center gap-2 mt-3">
                                <input type="checkbox" name="trigger_surat" value="1" id="trigger_surat_add" class="w-4 h-4 rounded text-indigo-600">
                                <label for="trigger_surat_add" class="text-xs font-bold text-amber-600 mb-0">Trigger Surat Pemanggilan</label>
                            </div>
                            <p class="text-[9px] text-amber-600 mt-1 leading-tight" id="trigger_surat_hint_add" style="display: none;">
                                💡 <strong>Penting:</strong> Pembina yang dipilih di bawah akan menjadi penanda tangan surat. Hindari pilih "Semua Guru & Staff" jika trigger surat aktif.
                            </p>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-xl border border-slate-200">
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-2 block tracking-widest">Deskripsi Sanksi</label>
                        <textarea name="sanksi_description" class="custom-input-clean w-full" rows="2" required placeholder="Tulis rincian sanksi..."></textarea>
                    </div>

                    <div class="bg-white p-4 rounded-xl border border-slate-200">
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-2 block tracking-widest">Pembina Terkait</label>
                        <p class="text-[9px] text-slate-500 mb-2 leading-tight">Pembina yang dipilih akan menjadi <strong>penanda tangan surat</strong> jika "Trigger Surat" diaktifkan.</p>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['Semua Guru & Staff', 'Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Waka Sarana', 'Kepala Sekolah'] as $role)
                                <label class="flex items-center gap-2 text-xs text-slate-600 mb-0 cursor-pointer">
                                    <input type="checkbox" name="pembina_roles[]" value="{{ $role }}" class="rounded text-indigo-600 pembina-checkbox-add"> {{ $role }}
                                </label>
                            @endforeach
                        </div>
                        <div class="mt-2 p-2 bg-red-50 border border-red-200 rounded text-[9px] text-red-600" id="warning_semua_guru_add" style="display: none;">
                            ⚠️ <strong>Perhatian:</strong> "Semua Guru & Staff" tidak dapat menandatangani surat resmi. Pilih pembina spesifik untuk surat formal.
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white p-4 gap-2">
                    <button type="button" class="btn-filter-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-filter-primary bg-indigo-600 border-none">Simpan Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditRule" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-2xl border-none shadow-2xl overflow-hidden">
            <form id="formEditRule" method="POST">
                @csrf @method('PUT')
                <div class="modal-header bg-slate-800 text-white border-none p-6">
                    <h5 class="modal-title font-bold">Edit Frequency Rule</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body p-6 bg-slate-50 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white p-4 rounded-xl border border-slate-200">
                            <label class="text-[10px] font-bold text-slate-400 uppercase mb-2 block tracking-widest">Ubah Frekuensi</label>
                            <div class="flex gap-2">
                                <input type="number" name="frequency_min" id="edit_frequency_min" class="custom-input-clean w-full" min="1" required>
                                <input type="number" name="frequency_max" id="edit_frequency_max" class="custom-input-clean w-full" min="1">
                            </div>
                        </div>
                        <div class="bg-white p-4 rounded-xl border border-slate-200 flex flex-col justify-between">
                            <div class="flex gap-2">
                                <div class="w-full">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Poin</label>
                                    <input type="number" name="poin" id="edit_poin" class="custom-input-clean w-full" required min="0">
                                </div>
                                <div class="w-full">
                                    <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Urutan</label>
                                    <input type="number" name="display_order" id="edit_display_order" class="custom-input-clean w-full" min="1">
                                </div>
                            </div>
                            <div class="flex items-center gap-2 mt-3">
                                <input type="checkbox" name="trigger_surat" value="1" id="edit_trigger_surat" class="w-4 h-4 rounded text-indigo-600">
                                <label for="edit_trigger_surat" class="text-xs font-bold text-amber-600 mb-0">Trigger Surat</label>
                            </div>
                            <p class="text-[9px] text-amber-600 mt-1 leading-tight" id="trigger_surat_hint_edit" style="display: none;">
                                💡 <strong>Penting:</strong> Pembina yang dipilih akan menjadi penanda tangan surat.
                            </p>
                        </div>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-slate-200">
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-2 block tracking-widest">Sanksi</label>
                        <textarea name="sanksi_description" id="edit_sanksi" class="custom-input-clean w-full" rows="2" required></textarea>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-slate-200">
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-2 block tracking-widest">Pembina</label>
                        <p class="text-[9px] text-slate-500 mb-2 leading-tight">Pembina yang dipilih akan menjadi <strong>penanda tangan surat</strong> jika "Trigger Surat" diaktifkan.</p>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['Semua Guru & Staff', 'Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Waka Sarana', 'Kepala Sekolah'] as $role)
                                <label class="flex items-center gap-2 text-xs text-slate-600 mb-0 cursor-pointer">
                                    <input type="checkbox" name="pembina_roles[]" value="{{ $role }}" class="pembina-checkbox-edit rounded text-indigo-600"> {{ $role }}
                                </label>
                            @endforeach
                        </div>
                        <div class="mt-2 p-2 bg-red-50 border border-red-200 rounded text-[9px] text-red-600" id="warning_semua_guru_edit" style="display: none;">
                            ⚠️ <strong>Perhatian:</strong> "Semua Guru & Staff" tidak dapat menandatangani surat resmi.
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white p-4 gap-2">
                    <button type="button" class="btn-filter-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-filter-primary bg-slate-800 border-none">Update Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
/* --- CORE STYLING (Solid Precision) --- */
.page-wrap-custom { background: #f8fafc; min-height: 100vh; padding: 1.5rem; font-family: 'Inter', sans-serif; }
.custom-header-row { border-bottom: 1px solid #e2e8f0; }

.btn-primary-custom {
    background-color: #4f46e5; color: white !important; padding: 0.6rem 1.2rem; border-radius: 0.75rem;
    font-weight: 700; font-size: 0.85rem; border: none; display: inline-flex; align-items: center;
    transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2); text-decoration: none !important;
}
.btn-primary-custom:hover { background-color: #4338ca; transform: translateY(-1px); }

/* Form Controls */
.custom-input-clean {
    border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.5rem 0.8rem;
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

.close { background: transparent; border: none; font-size: 1.5rem; cursor: pointer; }
.modal-backdrop { opacity: 0.5 !important; }
</style>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Exact Frequency Mode Toggle
    $('#exactFrequencyMode').change(function() {
        const isExact = $(this).is(':checked');
        const maxInput = $('#add_frequency_max');
        if (isExact) {
            maxInput.prop('readonly', true).addClass('bg-slate-50 text-slate-400');
            maxInput.val($('#add_frequency_min').val());
        } else {
            maxInput.prop('readonly', false).removeClass('bg-slate-50 text-slate-400');
        }
    });
    
    $('#add_frequency_min').on('input', function() {
        if ($('#exactFrequencyMode').is(':checked')) { $('#add_frequency_max').val($(this).val()); }
    });

    // Toggle Helper Hint when Trigger Surat is checked
    $('#trigger_surat_add').change(function() {
        if ($(this).is(':checked')) {
            $('#trigger_surat_hint_add').slideDown(200);
            checkSemuaGuruWarning('add');
        } else {
            $('#trigger_surat_hint_add').slideUp(200);
            $('#warning_semua_guru_add').slideUp(200);
        }
    });

    $('#edit_trigger_surat').change(function() {
        if ($(this).is(':checked')) {
            $('#trigger_surat_hint_edit').slideDown(200);
            checkSemuaGuruWarning('edit');
        } else {
            $('#trigger_surat_hint_edit').slideUp(200);
            $('#warning_semua_guru_edit').slideUp(200);
        }
    });

    // Warning untuk "Semua Guru & Staff" jika trigger surat aktif
    function checkSemuaGuruWarning(mode) {
        const triggerChecked = mode === 'add' ? $('#trigger_surat_add').is(':checked') : $('#edit_trigger_surat').is(':checked');
        const semuaGuruChecked = $(`.pembina-checkbox-${mode}[value="Semua Guru & Staff"]`).is(':checked');
        
        if (triggerChecked && semuaGuruChecked) {
            $(`#warning_semua_guru_${mode}`).slideDown(200);
        } else {
            $(`#warning_semua_guru_${mode}`).slideUp(200);
        }
    }

    // Monitor perubahan checkbox pembina
    $('.pembina-checkbox-add').change(function() {
        checkSemuaGuruWarning('add');
    });

    // Reset modal
    $('#modalAddRule').on('hidden.bs.modal', function() {
        $('#formAddRule')[0].reset();
        $('#exactFrequencyMode').prop('checked', false).trigger('change');
        $('#add_frequency_min').val('{{ $suggestedFreqMin ?? 1 }}');
        $('#trigger_surat_hint_add, #warning_semua_guru_add').hide();
    });

    // Edit rule trigger
    $('.btn-edit-rule').click(function() {
        const id = $(this).data('id');
        $('#formEditRule').attr('action', `/frequency-rules/rule/${id}`);
        $('#edit_frequency_min').val($(this).data('frequency-min'));
        $('#edit_frequency_max').val($(this).data('frequency-max') || '');
        $('#edit_poin').val($(this).data('poin'));
        $('#edit_sanksi').val($(this).data('sanksi'));
        $('#edit_display_order').val($(this).data('display-order'));
        
        const triggerSurat = $(this).data('trigger-surat') == 1;
        $('#edit_trigger_surat').prop('checked', triggerSurat);
        
        // Show/hide hint
        if (triggerSurat) {
            $('#trigger_surat_hint_edit').show();
        } else {
            $('#trigger_surat_hint_edit').hide();
        }
        
        // Populate pembina roles
        $('.pembina-checkbox-edit').prop('checked', false);
        const roles = $(this).data('pembina-roles');
        roles.forEach(role => { 
            $(`.pembina-checkbox-edit[value="${role}"]`).prop('checked', true); 
        });
        
        // Monitor checkbox pembina di edit modal
        $('.pembina-checkbox-edit').off('change').on('change', function() {
            checkSemuaGuruWarning('edit');
        });
        
        // Check warning initially
        checkSemuaGuruWarning('edit');
        
        $('#modalEditRule').modal('show');
    });
});
</script>
@endpush