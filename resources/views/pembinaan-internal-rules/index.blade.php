@extends('layouts.app')

@section('title', 'Aturan Pembinaan Internal')
@section('subtitle', 'Threshold pembinaan berdasarkan akumulasi poin siswa.')
@section('page-header', true)

@section('content')
<div class="space-y-6" x-data="pembinaanRulesPage()">
    {{-- Action Button (inside x-data scope) --}}
    <div class="flex justify-end">
        <button type="button" @click="showAddModal = true" class="btn btn-primary">
            <x-ui.icon name="plus" size="18" />
            <span>Tambah Aturan Baru</span>
        </button>
    </div>

    {{-- Info Banner --}}
    <div class="p-4 bg-indigo-50 border-l-4 border-indigo-500 rounded-r-xl">
        <div class="flex items-start gap-3">
            <x-ui.icon name="info" size="20" class="text-indigo-500 shrink-0 mt-0.5" />
            <p class="text-sm text-indigo-800">
                <strong>Penting:</strong> Pembinaan internal adalah rekomendasi konseling, <strong>TIDAK</strong> memicu surat pemanggilan otomatis. Surat panggilan hanya dipicu oleh aturan frekuensi pelanggaran.
            </p>
        </div>
    </div>

    {{-- Bulk Action Toolbar --}}
    <div x-show="selected.length > 0" x-transition x-cloak class="bg-indigo-50 p-3 flex flex-col sm:flex-row justify-between items-center gap-3 mb-4 rounded-xl border border-indigo-100 shadow-sm">
        <div class="flex items-center gap-2">
            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-bold" x-text="selected.length"></span>
            <span class="text-sm font-medium text-indigo-900">Aturan Terpilih</span>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" @click="if(confirm('Hapus ' + selected.length + ' aturan terpilih?')) { alert('Fitur bulk delete sedang dalam pengembangan.'); }" class="btn btn-sm btn-white text-red-600 border-red-200 hover:bg-red-50">
                <x-ui.icon name="trash" size="14" />
                Hapus Massal
            </button>
            <button type="button" @click="selected = []; selectionMode = false;" class="btn btn-sm btn-white">
                Batal
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Daftar Aturan Aktif</span>
            <span class="badge badge-primary">Total: {{ $rules->count() }} Aturan</span>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-16">#</th>
                        <th>Range Poin</th>
                        <th>Pembina Terlibat</th>
                        <th>Keterangan</th>
                        <th class="w-20 text-center cursor-pointer select-none hover:bg-gray-100 transition-colors group" @click="selectionMode = !selectionMode" title="Klik untuk memilih data">
                            <div class="flex items-center justify-center">
                                <template x-if="!selectionMode">
                                    <div class="flex items-center justify-center gap-2 text-gray-400 group-hover:text-indigo-600 transition-colors p-1">
                                        <span class="text-[10px] font-bold uppercase tracking-wider">Pilih</span>
                                        <x-ui.icon name="check-square" size="16" />
                                    </div>
                                </template>
                                <template x-if="selectionMode">
                                    <div class="flex items-center justify-center gap-1">
                                        <input type="checkbox" x-model="selectAll"
                                            @change="selectAll ? selected = {{ Js::from($rules->pluck('id')->map(fn($id) => (string)$id)->values()) }} : selected = []"
                                            @click.stop class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer" title="Pilih Semua">
                                        <button type="button" @click.stop="selectionMode = false; selected = []; selectAll = false;" class="p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition-colors" title="Batalkan Pilih">
                                            <x-ui.icon name="x" size="14" />
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rules as $rule)
                        <tr :class="{ 'bg-indigo-50/40': selected.includes('{{ $rule->id }}') }">
                            <td class="font-bold text-gray-400">{{ $rule->display_order }}</td>
                            <td>
                                <span class="badge badge-info font-bold">{{ $rule->getRangeText() }}</span>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($rule->pembina_roles ?? [] as $role)
                                        <span class="text-[10px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded font-bold border border-blue-100">{{ $role }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="max-w-md">
                                <p class="text-xs text-gray-600 italic">{{ $rule->keterangan }}</p>
                            </td>
                            <td class="text-center relative">
                                {{-- Normal Mode: Kebab Dropdown --}}
                                <template x-if="!selectionMode">
                                    <div x-data="{
                                             open: false,
                                             pressTimer: null,
                                             isLongPress: false,
                                             startPress() {
                                                 this.isLongPress = false;
                                                 this.pressTimer = setTimeout(() => {
                                                     this.isLongPress = true;
                                                     this.selectionMode = true;
                                                     if (!this.selected.includes('{{ $rule->id }}')) {
                                                         this.selected.push('{{ $rule->id }}');
                                                     }
                                                     if (navigator.vibrate) navigator.vibrate(50);
                                                 }, 500);
                                             },
                                             endPress() { clearTimeout(this.pressTimer); },
                                             handleClick() {
                                                 if (!this.isLongPress) this.open = !this.open;
                                                 this.isLongPress = false;
                                             }
                                         }"
                                         class="relative inline-block text-left"
                                    >
                                        <button 
                                            x-ref="trigger" 
                                            @click="handleClick()" 
                                            @mousedown="startPress()"
                                            @touchstart.passive="startPress()"
                                            @mouseup="endPress()"
                                            @mouseleave="endPress()"
                                            @touchend="endPress()"
                                            type="button" 
                                            class="p-1.5 text-gray-400 rounded-lg hover:bg-gray-100 hover:text-gray-600 transition-colors select-none"
                                        >
                                            <x-ui.icon name="more-vertical" size="18" />
                                        </button>
                                        
                                        <template x-teleport="body">
                                            <div x-show="open" 
                                                 @click.outside="open = false"
                                                 x-transition
                                                 class="w-36 origin-top-right rounded-xl bg-white shadow-xl ring-1 ring-black ring-opacity-5 border border-gray-100"
                                                 :style="open ? (() => {
                                                     const rect = $refs.trigger.getBoundingClientRect();
                                                     return `position: fixed; z-index: 9999; top: ${rect.bottom + 4}px; left: ${rect.right - 144}px;`;
                                                 })() : 'display: none;'"
                                            >
                                                <div class="py-1">
                                                    <button type="button" 
                                                            @click="openEditModal({{ Js::from(['id' => $rule->id, 'poin_min' => $rule->poin_min, 'poin_max' => $rule->poin_max, 'pembina_roles' => $rule->pembina_roles ?? [], 'keterangan' => $rule->keterangan, 'display_order' => $rule->display_order]) }}); open = false" 
                                                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                                                        <x-ui.icon name="edit" size="14" />
                                                        Edit
                                                    </button>
                                                    <div class="border-t border-gray-100 my-1"></div>
                                                    <form action="{{ route('pembinaan-internal-rules.destroy', $rule->id) }}" method="POST" onsubmit="return confirm('Hapus aturan ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors">
                                                            <x-ui.icon name="trash" size="14" />
                                                            Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                {{-- Selection Mode: Checkbox --}}
                                <template x-if="selectionMode">
                                    <div class="flex justify-center">
                                        <input type="checkbox" value="{{ $rule->id }}" x-model="selected" class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer">
                                    </div>
                                </template>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-ui.empty-state 
                                    icon="user-check" 
                                    title="Belum Ada Aturan" 
                                    description="Klik tombol 'Tambah Aturan Baru' untuk membuat threshold pembinaan." 
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>


    {{-- Modal Tambah --}}
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showAddModal = false">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="showAddModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl" @click.stop x-transition>
                <form action="{{ route('pembinaan-internal-rules.store') }}" method="POST">
                    @csrf
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800">Tambah Aturan Pembinaan</h3>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label form-label-required">Poin Min</label>
                                <input type="number" name="poin_min" class="form-input" value="{{ $suggestedPoinMin ?? 0 }}" min="0" required>
                                <p class="form-help">Rekomendasi: {{ $suggestedPoinMin ?? 0 }}</p>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Poin Max</label>
                                <input type="number" name="poin_max" class="form-input" min="0">
                                <p class="form-help">Kosongkan untuk ∞</p>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label form-label-required">Pembina yang Terlibat</label>
                            <div class="grid grid-cols-2 gap-2 mt-2">
                                @foreach(['Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Kepala Sekolah'] as $role)
                                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                                        <input type="checkbox" name="pembina_roles[]" value="{{ $role }}" class="rounded border-gray-300 text-indigo-600">
                                        {{ $role }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label form-label-required">Keterangan</label>
                            <textarea name="keterangan" rows="3" class="form-input form-textarea" placeholder="Contoh: Pembinaan sedang, monitoring ketat..." required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Urutan Tampilan</label>
                            <input type="number" name="display_order" class="form-input" value="{{ $suggestedDisplayOrder ?? 1 }}" min="1">
                        </div>
                    </div>
                    
                    <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
                        <button type="button" @click="showAddModal = false" class="btn btn-secondary">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Aturan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showEditModal = false">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="showEditModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl" @click.stop x-transition>
                <form :action="'/pembinaan-internal-rules/' + editRule.id" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800">Edit Aturan Pembinaan</h3>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label form-label-required">Poin Min</label>
                                <input type="number" name="poin_min" class="form-input" x-model="editRule.poin_min" min="0" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Poin Max</label>
                                <input type="number" name="poin_max" class="form-input" x-model="editRule.poin_max" min="0">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label form-label-required">Pembina yang Terlibat</label>
                            <div class="grid grid-cols-2 gap-2 mt-2">
                                @foreach(['Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Kepala Sekolah'] as $role)
                                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                                        <input type="checkbox" name="pembina_roles[]" value="{{ $role }}" 
                                               class="rounded border-gray-300 text-indigo-600"
                                               :checked="editRule.pembina_roles && editRule.pembina_roles.includes('{{ $role }}')">
                                        {{ $role }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label form-label-required">Keterangan</label>
                            <textarea name="keterangan" rows="3" class="form-input form-textarea" x-model="editRule.keterangan" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Urutan Tampilan</label>
                            <input type="number" name="display_order" class="form-input" x-model="editRule.display_order" min="1">
                        </div>
                    </div>
                    
                    <div class="p-6 border-t border-gray-100 flex justify-end gap-3">
                        <button type="button" @click="showEditModal = false" class="btn btn-secondary">Batal</button>
                        <button type="submit" class="btn btn-primary">Update Aturan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function pembinaanRulesPage() {
    return {
        showAddModal: false,
        showEditModal: false,
        selectionMode: false,
        selected: [],
        selectAll: false,
        editRule: {
            id: null,
            poin_min: 0,
            poin_max: null,
            pembina_roles: [],
            keterangan: '',
            display_order: 1
        },
        
        openEditModal(rule) {
            this.editRule = { ...rule };
            this.showEditModal = true;
        }
    }
}
</script>
@endpush
@endsection
