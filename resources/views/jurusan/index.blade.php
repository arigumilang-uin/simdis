@extends('layouts.app')

@section('title', 'Manajemen Jurusan')

@section('page-header')
    <x-page-header 
        title="Manajemen Jurusan" 
        subtitle="Kelola data jurusan/kompetensi keahlian."
    >
        <x-slot:actions>
            <button 
                type="button" 
                @click="$dispatch('open-jurusan-form', { title: 'Tambah Jurusan Baru' })"
                class="btn btn-primary"
            >
                <x-ui.icon name="plus" size="18" />
                <span>Tambah Jurusan</span>
            </button>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
<div x-data='{ 
    selectionMode: false, 
    selected: [], 
    selectAll: false,
    pageIds: {{ json_encode($jurusanList->pluck('id')->map(fn($id) => (string) $id)) }},
    init() {
         this.$watch("selectAll", val => {
             this.selected = val ? [...this.pageIds] : [];
         });
         this.$watch("selected", val => {
             if (val.length === 0) this.selectionMode = false;
             if (this.pageIds.length > 0 && val.length !== this.pageIds.length) this.selectAll = false;
             else if (this.pageIds.length > 0 && val.length === this.pageIds.length) this.selectAll = true;
         });
    }
}'
@toggle-selection-mode.window="selectionMode = $event.detail !== undefined ? $event.detail : !selectionMode">
    
    <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm overflow-hidden mb-8 border-b border-gray-200 md:border-b-0">
        {{-- Unified Toolbar --}}
        <div class="px-4 md:px-6 py-5 border-b border-gray-100 bg-white">
            <x-ui.action-bar :total="$jurusanList->count()" totalLabel="Jurusan" class="!gap-4" />
            
            {{-- Bulk Action Toolbar --}}
            <div x-show="selected.length > 0" x-transition x-cloak class="mt-3 bg-indigo-50 p-2 flex flex-col sm:flex-row justify-between items-center gap-3 rounded-lg border border-indigo-100">
                <div class="flex items-center gap-2 px-1">
                    <span class="flex items-center justify-center w-5 h-5 rounded-full bg-indigo-600 text-white text-[10px] font-bold" x-text="selected.length"></span>
                    <span class="text-sm font-medium text-indigo-900">Jurusan Terpilih</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="if(confirm('Hapus ' + selected.length + ' jurusan terpilih?')) { alert('Fitur bulk delete sedang dalam pengembangan.'); }" class="btn btn-sm btn-white text-red-600 border-red-200 hover:bg-red-50">
                        <x-ui.icon name="trash" size="14" />
                        Hapus Massal
                    </button>
                    <button type="button" @click="selected = []; selectionMode = false;" class="btn btn-sm btn-white">
                        Batal
                    </button>
                </div>
            </div>
        </div>

        <div class="table-container min-h-[300px]">
        <table class="table">
            <thead>
                <tr>
                    <th class="w-12">No</th>
                    <th class="">Kode</th>
                    <th>Nama Jurusan</th>
                    <th class="">Kaprodi</th>
                    <th class="text-center">Konsentrasi</th>
                    <th class="text-center">Jumlah Kelas</th>
<x-table.action-header />
                </tr>
            </thead>
            <tbody>
                @forelse($jurusanList ?? [] as $index => $j)
                    <tr :class="{ 'bg-indigo-50/40': selected.includes('{{ $j->id }}') }">
                        <td class="text-gray-500">{{ $loop->iteration }}</td>
                        <td class=""><span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded-md">{{ $j->kode_jurusan ?? '-' }}</span></td>
                        <td class="font-medium text-gray-800">{{ $j->nama_jurusan }}</td>
                        <td class="text-gray-500">{{ $j->kaprodi->username ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('konsentrasi.index', ['jurusan_id' => $j->id]) }}" class="badge badge-info hover:bg-blue-200 transition">
                                {{ $j->konsentrasi_count ?? $j->konsentrasi->count() }}
                            </a>
                        </td>
                        <td class="text-center"><span class="badge badge-primary">{{ $j->kelas_count ?? $j->kelas->count() }}</span></td>
                        <x-table.action-column :id="$j->id">
                            <x-table.action-item icon="info" href="{{ route('jurusan.show', $j->id) }}">
                                Detail
                            </x-table.action-item>
                            
                            <x-table.action-item 
                                type="button"
                                icon="edit"
                                @click="open = false; $dispatch('open-jurusan-form', { 
                                    title: 'Edit Jurusan',
                                    editMode: true,
                                    id: {{ $j->id }},
                                    kode_jurusan: '{{ $j->kode_jurusan ?? '' }}',
                                    nama_jurusan: '{{ addslashes($j->nama_jurusan) }}',
                                    kaprodi_user_id: {{ $j->kaprodi_user_id ?? 'null' }}
                                })"
                            >
                                Edit
                            </x-table.action-item>
                            
                            <x-table.action-separator />
                            <form action="{{ route('jurusan.destroy', $j->id) }}" method="POST" onsubmit="return confirm('Hapus jurusan ini?')">
                                @csrf
                                @method('DELETE')
                                <x-table.action-item 
                                    type="submit" 
                                    icon="trash" 
                                    class="text-red-600 hover:bg-red-50 hover:text-red-700 w-full text-left"
                                >
                                    Hapus
                                </x-table.action-item>
                            </form>
                        </x-table.action-column>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-ui.empty-state 
                                title="Tidak Ada Data" 
                                description="Belum ada jurusan yang terdaftar." 
                                icon="hexagon"
                            >
                                <x-slot:action>
                                    <button 
                                        type="button" 
                                        @click="$dispatch('open-jurusan-form', { title: 'Tambah Jurusan Baru' })"
                                        class="btn btn-primary"
                                    >
                                        <x-ui.icon name="plus" size="18" />
                                        <span>Tambah Jurusan</span>
                                    </button>
                                </x-slot:action>
                            </x-ui.empty-state>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </div>
</div>

{{-- Slide-over Form Drawer --}}
<x-ui.slide-over 
    id="jurusan-form" 
    title="Tambah Jurusan" 
    size="lg"
    icon="layers"
>
    <form 
        id="jurusan-form-element"
        action="{{ route('jurusan.store') }}" 
        method="POST" 
        class="space-y-5"
        x-data="{
            editMode: false,
            editId: null,
            formData: {
                kode_jurusan: '',
                nama_jurusan: '',
                kaprodi_user_id: ''
            },
            resetForm() {
                this.editMode = false;
                this.editId = null;
                this.formData = {
                    kode_jurusan: '',
                    nama_jurusan: '',
                    kaprodi_user_id: ''
                };
            }
        }"
        x-on:open-jurusan-form.window="
            if ($event.detail?.editMode) {
                editMode = true;
                editId = $event.detail.id;
                formData.kode_jurusan = $event.detail.kode_jurusan || '';
                formData.nama_jurusan = $event.detail.nama_jurusan || '';
                formData.kaprodi_user_id = $event.detail.kaprodi_user_id || '';
            } else {
                resetForm();
            }
        "
        x-on:slide-over-closed.window="resetForm()"
        :action="editMode ? '{{ url('jurusan') }}/' + editId : '{{ route('jurusan.store') }}'"
    >
        @csrf
        <input type="hidden" name="_method" x-bind:value="editMode ? 'PUT' : 'POST'">
        
        <div class="form-section">
            <div class="form-section-title">
                <x-ui.icon name="info" size="14" />
                Informasi Jurusan
            </div>
            
            <x-forms.grid :cols="2">
                <div class="form-group">
                    <label for="kode_jurusan" class="form-label form-label-required">Kode Jurusan</label>
                    <input 
                        type="text" 
                        name="kode_jurusan" 
                        id="kode_jurusan" 
                        class="form-input"
                        placeholder="Contoh: TKJ"
                        maxlength="10"
                        required
                        x-model="formData.kode_jurusan"
                    >
                    <p class="form-help">Maks. 10 karakter</p>
                </div>
                
                <div class="form-group">
                    <label for="kaprodi_user_id" class="form-label">Kepala Prodi</label>
                    <select 
                        name="kaprodi_user_id" 
                        id="kaprodi_user_id" 
                        class="form-input form-select"
                        x-model="formData.kaprodi_user_id"
                    >
                        <option value="">-- Pilih Kaprodi --</option>
                        @foreach($kaprodiList ?? [] as $k)
                            <option value="{{ $k->id }}">{{ $k->username }}</option>
                        @endforeach
                    </select>
                </div>
            </x-forms.grid>
            
            <div class="form-group">
                <label for="nama_jurusan" class="form-label form-label-required">Nama Jurusan</label>
                <input 
                    type="text" 
                    name="nama_jurusan" 
                    id="nama_jurusan" 
                    class="form-input"
                    placeholder="Contoh: Teknik Komputer dan Jaringan"
                    required
                    x-model="formData.nama_jurusan"
                >
            </div>
        </div>
    </form>
    
    <x-slot:footer>
        <button type="button" @click="$dispatch('close-jurusan-form')" class="btn btn-secondary">
            Batal
        </button>
        <button 
            type="submit" 
            class="btn btn-primary"
            onclick="document.getElementById('jurusan-form-element').submit()"
        >
            <x-ui.icon name="save" size="18" />
            <span>Simpan Jurusan</span>
        </button>
    </x-slot:footer>
</x-ui.slide-over>
@endsection
