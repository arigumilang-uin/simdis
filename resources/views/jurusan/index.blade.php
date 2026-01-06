@extends('layouts.app')

@section('title', 'Manajemen Jurusan')

@section('page-header')
    <x-page-header 
        title="Manajemen Jurusan" 
        subtitle="Kelola data jurusan/kompetensi keahlian."
        :total="$jurusanList->count()"
    />
@endsection

@section('content')
<div x-data="{ selectionMode: false, selected: [], selectAll: false }">
    {{-- Action Buttons --}}
    <div class="flex flex-wrap justify-end gap-2 mb-6">
        <button 
            type="button" 
            @click="$dispatch('open-jurusan-form', { title: 'Tambah Jurusan Baru' })"
            class="btn btn-primary"
        >
            <x-ui.icon name="plus" size="18" />
            <span>Tambah Jurusan</span>
        </button>
    </div>
    
    {{-- Bulk Action Toolbar --}}
    <div x-show="selected.length > 0" x-transition x-cloak class="bg-indigo-50 p-3 flex flex-col sm:flex-row justify-between items-center gap-3 mb-4 rounded-xl border border-indigo-100 shadow-sm">
        <div class="flex items-center gap-2">
            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-bold" x-text="selected.length"></span>
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
                                        @change="selectAll ? selected = {{ Js::from($jurusanList->pluck('id')->map(fn($id) => (string)$id)->values()) }} : selected = []"
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
                        <td class="text-center relative">
                            {{-- Normal Mode: Kebab Dropdown --}}
                            <div x-show="!selectionMode" 
                                 x-data="{
                                     open: false,
                                     timer: null,
                                     isLongPress: false,
                                     
                                     startPress() {
                                         this.isLongPress = false;
                                         this.timer = setTimeout(() => {
                                             this.isLongPress = true;
                                             // Trigger Selection Mode (Parent Scope)
                                             this.selectionMode = true;
                                             // Add to selected (Parent Scope)
                                             if (!this.selected.includes('{{ $j->id }}')) {
                                                 this.selected.push('{{ $j->id }}');
                                             }
                                             if (navigator.vibrate) navigator.vibrate(50);
                                         }, 500);
                                     },
                                     
                                     endPress() {
                                         clearTimeout(this.timer);
                                     },

                                     toggle() {
                                         if (this.isLongPress) return;
                                         if (this.open) { this.open = false; return; }
                                         this.open = true;
                                         this.$nextTick(() => {
                                             const trigger = this.$refs.trigger.getBoundingClientRect();
                                             const menu = this.$refs.menu;
                                             
                                             // Calculate Right Alignment
                                             let left = trigger.right - menu.offsetWidth;
                                             let top = trigger.bottom + 2; 
                                             
                                             // Check bottom overflow
                                             if (window.innerHeight - trigger.bottom < menu.offsetHeight + 20) {
                                                 top = trigger.top - menu.offsetHeight - 2;
                                             }
                                             
                                             menu.style.top = `${top}px`;
                                             menu.style.left = `${left}px`;
                                         });
                                     }
                                 }" 
                                 @scroll.window="open = false"
                                 @resize.window="open = false"
                                 class="relative inline-block text-left"
                            >
                                <button 
                                    x-ref="trigger" 
                                    @click="toggle()" 
                                    @mousedown="startPress()"
                                    @touchstart="startPress()"
                                    @mouseup="endPress()"
                                    @mouseleave="endPress()"
                                    @touchend="endPress()"
                                    type="button" 
                                    class="p-1.5 text-gray-400 rounded-lg hover:bg-gray-100 hover:text-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 select-none"
                                >
                                    <x-ui.icon name="more-horizontal" size="18" />
                                </button>
                                
                                <template x-teleport="body">
                                    <div x-show="open" 
                                         x-ref="menu"
                                         @click.outside="open = false"
                                         style="position: fixed; z-index: 9999; display: none;"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         class="w-36 origin-top-right rounded-xl bg-white shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none border border-gray-100"
                                    >
                                        <div class="py-1">
                                            <a href="{{ route('jurusan.show', $j->id) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                                                <x-ui.icon name="info" size="14" />
                                                Detail
                                            </a>
                                            <a href="{{ route('jurusan.edit', $j->id) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                                                <x-ui.icon name="edit" size="14" />
                                                Edit
                                            </a>
                                            <div class="border-t border-gray-100 my-1"></div>
                                            <form action="{{ route('jurusan.destroy', $j->id) }}" method="POST" onsubmit="return confirm('Hapus jurusan ini?')">
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

                            {{-- Selection Mode: Checkbox --}}
                            <div x-show="selectionMode" style="display: none;">
                                <input type="checkbox" value="{{ $j->id }}" x-model="selected" class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer">
                            </div>
                        </td>
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

{{-- Slide-over Form Drawer --}}
<x-ui.slide-over 
    id="jurusan-form" 
    title="Tambah Jurusan" 
    size="lg"
    icon="layers"
>
    <form 
        action="{{ route('jurusan.store') }}" 
        method="POST" 
        class="space-y-5"
        x-data="{ submitting: false, isEdit: false, editId: null }"
        @submit="submitting = true"
        x-on:open-jurusan-form.window="
            isEdit = $event.detail?.id ? true : false;
            editId = $event.detail?.id || null;
            if (isEdit) {
                $el.action = '{{ url('jurusan') }}/' + editId;
            } else {
                $el.action = '{{ route('jurusan.store') }}';
            }
        "
    >
        @csrf
        <input type="hidden" name="_method" x-bind:value="isEdit ? 'PUT' : 'POST'">
        
        <div class="form-section">
            <div class="form-section-title">
                <x-ui.icon name="info" size="14" />
                Informasi Jurusan
            </div>
            
            <x-forms.grid :cols="2">
                <x-forms.input 
                    name="kode_jurusan" 
                    label="Kode Jurusan" 
                    placeholder="Contoh: TKJ" 
                    required 
                    help="Maks. 10 karakter"
                />
                
                <x-forms.select 
                    name="kaprodi_user_id" 
                    label="Kepala Prodi" 
                    :options="$kaprodiList ?? []"
                    optionValue="id"
                    optionLabel="username"
                    placeholder="-- Pilih Kaprodi --"
                />
            </x-forms.grid>
            
            <x-forms.input 
                name="nama_jurusan" 
                label="Nama Jurusan" 
                placeholder="Contoh: Teknik Komputer dan Jaringan" 
                required 
            />
        </div>
    </form>
    
    <x-slot:footer>
        <button type="button" @click="$dispatch('close-jurusan-form')" class="btn btn-secondary">
            Batal
        </button>
        <button 
            type="submit" 
            form="jurusan-form-form"
            class="btn btn-primary"
            onclick="this.closest('.slide-over-panel').querySelector('form').submit()"
        >
            <x-ui.icon name="save" size="18" />
            <span>Simpan Jurusan</span>
        </button>
    </x-slot:footer>
</x-ui.slide-over>
@endsection

