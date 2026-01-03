@extends('layouts.app')

@section('title', 'Kelola Frequency Rules')
@section('subtitle', $jenisPelanggaran->nama_pelanggaran ?? 'Atur Rules')
@section('page-header', true)

@section('actions')
    <button type="button" onclick="history.back()" class="btn btn-secondary">
        <x-ui.icon name="chevron-left" size="18" />
        <span>Kembali</span>
    </button>
    <a href="{{ route('jenis-pelanggaran.edit', $jenisPelanggaran->id) }}" class="btn btn-secondary">
        <x-ui.icon name="edit" size="18" />
        <span>Edit Info</span>
    </a>
@endsection

@section('content')
<div class="space-y-6" x-data="frequencyRulesManager()">
    {{-- Pelanggaran Info --}}
    <div class="card p-4">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex-1">
                <h2 class="text-lg font-bold text-gray-800">{{ $jenisPelanggaran->nama_pelanggaran }}</h2>
                <div class="flex items-center gap-2 mt-1">
                    @php
                        $kategoriNama = strtolower($jenisPelanggaran->kategoriPelanggaran->nama_kategori ?? '');
                        $badgeClass = 'badge-neutral';
                        if (str_contains($kategoriNama, 'ringan')) $badgeClass = 'badge-info';
                        elseif (str_contains($kategoriNama, 'sedang')) $badgeClass = 'badge-warning';
                        elseif (str_contains($kategoriNama, 'berat')) $badgeClass = 'badge-danger';
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $jenisPelanggaran->kategoriPelanggaran->nama_kategori ?? '-' }}</span>
                    <span class="text-sm text-gray-400">|</span>
                    <span class="badge {{ $jenisPelanggaran->is_active ? 'badge-success' : 'badge-neutral' }}">
                        {{ $jenisPelanggaran->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>
            <button type="button" @click="openAddModal()" class="btn btn-primary">
                <x-ui.icon name="plus" size="18" />
                <span>Tambah Rule</span>
            </button>
        </div>
    </div>

    {{-- No Rules Info --}}
    @if($jenisPelanggaran->frequencyRules->count() == 0)
    <div class="p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-xl">
        <div class="flex items-start gap-3">
            <x-ui.icon name="info" size="20" class="text-blue-500 shrink-0" />
            <p class="text-sm text-blue-800">
                <strong>Belum ada rule:</strong> Sistem akan menggunakan poin default 
                <strong>({{ $jenisPelanggaran->poin }} poin)</strong> setiap kali tercatat.
            </p>
        </div>
    </div>
    @endif

    {{-- Rules Table --}}
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th class="w-16 text-center">Order</th>
                    <th class="w-32">Frekuensi</th>
                    <th class="w-24">Poin</th>
                    <th>Sanksi / Deskripsi</th>
                    <th class="w-24 text-center">Surat</th>
                    <th>Pembina</th>
                    <th class="w-24 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jenisPelanggaran->frequencyRules as $rule)
                <tr>
                    <td class="text-center">
                        <span class="text-sm font-bold text-gray-400">{{ $rule->display_order }}</span>
                    </td>
                    <td>
                        <span class="px-2 py-1 rounded bg-gray-800 text-white text-xs font-bold">
                            @if($rule->frequency_max)
                                {{ $rule->frequency_min }}-{{ $rule->frequency_max }}x
                            @else
                                {{ $rule->frequency_min }}+x
                            @endif
                        </span>
                    </td>
                    <td>
                        <span class="text-lg font-bold {{ $rule->poin > 0 ? 'text-red-600' : 'text-gray-400' }}">
                            +{{ $rule->poin }}
                        </span>
                    </td>
                    <td class="text-gray-700">{{ $rule->sanksi_description }}</td>
                    <td class="text-center">
                        @if($rule->trigger_surat)
                            <span class="badge badge-warning">
                                <x-ui.icon name="mail" size="12" />
                                Ya
                            </span>
                        @else
                            <span class="text-gray-300 text-xs">Tidak</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex flex-wrap gap-1">
                            @foreach($rule->pembina_roles ?? [] as $role)
                                <span class="text-[10px] bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded font-bold">{{ $role }}</span>
                            @endforeach
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="flex items-center justify-center gap-1">
                            <button type="button" 
                                    @click="openEditModal({{ json_encode([
                                        'id' => $rule->id,
                                        'frequency_min' => $rule->frequency_min,
                                        'frequency_max' => $rule->frequency_max,
                                        'poin' => $rule->poin,
                                        'sanksi_description' => $rule->sanksi_description,
                                        'trigger_surat' => $rule->trigger_surat,
                                        'pembina_roles' => $rule->pembina_roles ?? [],
                                        'display_order' => $rule->display_order,
                                    ]) }})" 
                                    class="btn btn-icon btn-outline" title="Edit">
                                <x-ui.icon name="edit" size="16" />
                            </button>
                            <form action="{{ route('frequency-rules.destroy', $rule->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus rule ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-outline text-red-500 hover:bg-red-50" title="Hapus">
                                    <x-ui.icon name="trash" size="16" />
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <x-ui.empty-state 
                            icon="settings" 
                            title="Belum Ada Rules" 
                            description="Tambahkan frequency rules untuk mengatur poin dan sanksi berdasarkan frekuensi pelanggaran." 
                        >
                            <x-slot:action>
                                <button type="button" @click="openAddModal()" class="btn btn-primary">Tambah Rule Pertama</button>
                            </x-slot:action>
                        </x-ui.empty-state>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Add Rule Modal --}}
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showAddModal = false">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="showAddModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl" @click.stop x-transition>
                <form action="{{ route('frequency-rules.store', $jenisPelanggaran->id) }}" method="POST">
                    @csrf
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800">Tambah Frequency Rule</h3>
                        <p class="text-sm text-gray-500">Atur poin dan sanksi berdasarkan frekuensi pelanggaran</p>
                    </div>
                    
                    <div class="p-6 space-y-5">
                        {{-- Frekuensi --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label form-label-required">Frekuensi Min</label>
                                <input type="number" name="frequency_min" class="form-input" value="{{ $jenisPelanggaran->frequencyRules->max('frequency_max') + 1 ?? 1 }}" min="1" required>
                                <p class="form-help">Mulai dari pelanggaran ke-?</p>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Frekuensi Max <span class="text-gray-400">(opsional)</span></label>
                                <input type="number" name="frequency_max" class="form-input" min="1">
                                <p class="form-help">Kosongkan jika "X kali ke atas"</p>
                            </div>
                        </div>

                        {{-- Poin & Order --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label form-label-required">Poin</label>
                                <input type="number" name="poin" class="form-input" value="0" min="0" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Urutan Tampil</label>
                                <input type="number" name="display_order" class="form-input" value="{{ $jenisPelanggaran->frequencyRules->max('display_order') + 1 ?? 1 }}" min="1">
                            </div>
                        </div>

                        {{-- Sanksi --}}
                        <div class="form-group">
                            <label class="form-label form-label-required">Deskripsi Sanksi</label>
                            <textarea name="sanksi_description" rows="2" class="form-input form-textarea" required placeholder="Tulis rincian sanksi..."></textarea>
                        </div>

                        {{-- Trigger Surat --}}
                        <div class="form-group">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="trigger_surat" value="1" class="w-5 h-5 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                                <span class="text-sm font-medium text-amber-700">Trigger Surat Pemanggilan</span>
                            </label>
                        </div>

                        {{-- Pembina --}}
                        <div class="form-group">
                            <label class="form-label">Pembina Terkait</label>
                            <div class="grid grid-cols-2 gap-2 mt-2">
                                @foreach(['Semua Guru & Staff', 'Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Waka Sarana', 'Kepala Sekolah'] as $role)
                                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                                        <input type="checkbox" name="pembina_roles[]" value="{{ $role }}" class="rounded border-gray-300 text-indigo-600">
                                        {{ $role }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6 border-t border-gray-100 flex gap-3 justify-end">
                        <button type="button" @click="showAddModal = false" class="btn btn-secondary">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Rule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Rule Modal --}}
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showEditModal = false">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="showEditModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl" @click.stop x-transition>
                <form :action="'/frequency-rules/rule/' + editRule.id" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800">Edit Frequency Rule</h3>
                    </div>
                    
                    <div class="p-6 space-y-5">
                        {{-- Frekuensi --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label form-label-required">Frekuensi Min</label>
                                <input type="number" name="frequency_min" class="form-input" x-model="editRule.frequency_min" min="1" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Frekuensi Max</label>
                                <input type="number" name="frequency_max" class="form-input" x-model="editRule.frequency_max" min="1">
                            </div>
                        </div>

                        {{-- Poin & Order --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label form-label-required">Poin</label>
                                <input type="number" name="poin" class="form-input" x-model="editRule.poin" min="0" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Urutan Tampil</label>
                                <input type="number" name="display_order" class="form-input" x-model="editRule.display_order" min="1">
                            </div>
                        </div>

                        {{-- Sanksi --}}
                        <div class="form-group">
                            <label class="form-label form-label-required">Deskripsi Sanksi</label>
                            <textarea name="sanksi_description" rows="2" class="form-input form-textarea" x-model="editRule.sanksi_description" required></textarea>
                        </div>

                        {{-- Trigger Surat --}}
                        <div class="form-group">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="trigger_surat" value="1" class="w-5 h-5 rounded border-gray-300 text-amber-600 focus:ring-amber-500" x-model="editRule.trigger_surat">
                                <span class="text-sm font-medium text-amber-700">Trigger Surat Pemanggilan</span>
                            </label>
                        </div>

                        {{-- Pembina --}}
                        <div class="form-group">
                            <label class="form-label">Pembina Terkait</label>
                            <div class="grid grid-cols-2 gap-2 mt-2">
                                @foreach(['Semua Guru & Staff', 'Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Waka Sarana', 'Kepala Sekolah'] as $role)
                                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                                        <input type="checkbox" name="pembina_roles[]" value="{{ $role }}" 
                                               class="rounded border-gray-300 text-indigo-600"
                                               :checked="editRule.pembina_roles && editRule.pembina_roles.includes('{{ $role }}')">
                                        {{ $role }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6 border-t border-gray-100 flex gap-3 justify-end">
                        <button type="button" @click="showEditModal = false" class="btn btn-secondary">Batal</button>
                        <button type="submit" class="btn btn-primary">Update Rule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function frequencyRulesManager() {
    return {
        showAddModal: false,
        showEditModal: false,
        editRule: {
            id: null,
            frequency_min: 1,
            frequency_max: null,
            poin: 0,
            sanksi_description: '',
            trigger_surat: false,
            pembina_roles: [],
            display_order: 1
        },
        
        openAddModal() {
            this.showAddModal = true;
        },
        
        openEditModal(rule) {
            this.editRule = {
                ...rule,
                trigger_surat: rule.trigger_surat == 1 || rule.trigger_surat === true
            };
            this.showEditModal = true;
        }
    }
}
</script>
@endpush
@endsection
