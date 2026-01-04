{{-- Dispatch total count to header --}}
<div x-data x-init="$dispatch('update-total-data', { total: {{ $siswa->total() }} })"></div>

    {{-- Data Table --}}
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th class="w-12">No</th>
                    <th>NISN</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Kontak Wali</th>
                    <th class="w-20 text-center cursor-pointer select-none hover:bg-gray-100 transition-colors group" @click="toggleSelectionMode()" title="Klik untuk memilih data">
                        <div class="flex items-center justify-center">
                            <template x-if="!selectionMode">
                                <div class="flex items-center justify-center gap-2 text-gray-400 group-hover:text-indigo-600 transition-colors p-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider">Pilih</span>
                                    <x-ui.icon name="check-square" size="16" />
                                </div>
                            </template>
                            <template x-if="selectionMode">
                                <div class="flex items-center justify-center gap-1">
                                    <input type="checkbox" x-model="selectAll" @change="toggleSelectAll()" @click.stop class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer" title="Pilih Semua">
                                    <button type="button" @click.stop="selectionMode = false" class="p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition-colors" title="Batalkan Pilih">
                                        <x-ui.icon name="x" size="14" />
                                    </button>
                                </div>
                            </template>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($siswa as $index => $s)
                    <tr :class="{ 'bg-indigo-50/40': selected.includes('{{ $s->id }}') }">
                        <td>
                            <a href="{{ route('siswa.show', $s->id) }}" class="text-gray-500 hover:text-primary-600 transition-colors">
                                {{ $siswa->firstItem() + $index }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('siswa.show', $s->id) }}" class="inline-block font-mono text-xs bg-gray-100 px-2 py-1 rounded-md hover:bg-primary-100 hover:text-primary-700 transition-colors">
                                {{ $s->nisn }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('siswa.show', $s->id) }}" class="font-medium text-gray-800 hover:text-primary-600 transition-colors">
                                {{ $s->nama_siswa }}
                            </a>
                        </td>
                        <td>
                            @if($s->kelas)
                                <a href="{{ route('kelas.show', $s->kelas->id) }}" class="badge badge-primary hover:bg-primary-200 transition-colors">
                                    {{ $s->kelas->nama_kelas }}
                                </a>
                            @else
                                <span class="badge badge-neutral">-</span>
                            @endif
                        </td>
                        <td>
                            @if($s->nomor_hp_wali_murid)
                                <a href="https://wa.me/62{{ ltrim($s->nomor_hp_wali_murid, '0') }}" target="_blank" class="text-emerald-600 hover:text-emerald-700 font-medium inline-flex items-center gap-1">
                                    <x-ui.icon name="brand-whatsapp" size="14" class="fill-current" strokeWidth="0" />
                                    {{ $s->nomor_hp_wali_murid }}
                                </a>
                            @else
                                <span class="text-gray-400 text-sm">-</span>
                            @endif
                        </td>
                        <td class="text-center relative">
                            {{-- Selection Mode: Checkbox --}}
                            <template x-if="selectionMode">
                                <input type="checkbox" value="{{ $s->id }}" x-model="selected" class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer">
                            </template>

                            {{-- Normal Mode: Kebab Dropdown --}}
                            <template x-if="!selectionMode">
                                <div 
                                     x-data="{ 
                                         open: false,
                                         pressTimer: null,
                                         isLongPress: false,
                                         startPress() {
                                             this.isLongPress = false;
                                             this.pressTimer = setTimeout(() => {
                                                 this.isLongPress = true;
                                                 $dispatch('enter-selection', { id: '{{ $s->id }}' });
                                             }, 500);
                                         },
                                         endPress() {
                                             clearTimeout(this.pressTimer);
                                         },
                                         handleClick() {
                                             if (!this.isLongPress) {
                                                 this.open = !this.open;
                                             }
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
                                        class="p-1.5 text-gray-400 rounded-lg hover:bg-gray-100 hover:text-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 select-none"
                                    >
                                        <x-ui.icon name="more-horizontal" size="18" />
                                    </button>
                                    
                                    <template x-teleport="body">
                                        <div x-show="open" 
                                             @click.outside="open = false"
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="transform opacity-0 scale-95"
                                             x-transition:enter-end="transform opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="transform opacity-100 scale-100"
                                             x-transition:leave-end="transform opacity-0 scale-95"
                                             class="w-36 origin-top-right rounded-xl bg-white shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none border border-gray-100"
                                             :style="open ? (() => {
                                                 const rect = $refs.trigger.getBoundingClientRect();
                                                 const top = rect.bottom + 4;
                                                 const left = rect.right - 144;
                                                 return `position: fixed; z-index: 9999; top: ${top}px; left: ${left}px;`;
                                             })() : 'display: none;'"
                                        >
                                        <div class="py-1">
                                            <a href="{{ route('siswa.show', $s->id) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                                                <x-ui.icon name="eye" size="14" />
                                                Detail
                                            </a>
                                            @can('update', $s)
                                            <a href="{{ route('siswa.edit', $s->id) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                                                <x-ui.icon name="edit" size="14" />
                                                Edit
                                            </a>
                                            @endcan
                                            @can('delete', $s)
                                            <div class="border-t border-gray-100 my-1"></div>
                                            <button type="button" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors"
                                                    @click="open = false; $dispatch('open-delete-modal', { id: {{ $s->id }}, nama: '{{ $s->nama_siswa }}', nisn: '{{ $s->nisn }}' })">
                                                <x-ui.icon name="trash" size="14" />
                                                Hapus
                                            </button>
                                            @endcan
                                        </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <x-ui.empty-state 
                                icon="search" 
                                title="Data Tidak Ditemukan" 
                                description="Tidak ada data siswa yang sesuai dengan filter Anda." 
                            >
                                <x-slot:action>
                                    @can('create', App\Models\Siswa::class)
                                        <a href="{{ route('siswa.create') }}" class="btn btn-primary">
                                            <x-ui.icon name="plus" size="18" />
                                            <span>Tambah Siswa Baru</span>
                                        </a>
                                    @endcan
                                </x-slot:action>
                            </x-ui.empty-state>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Pagination --}}
    @if($siswa->hasPages())
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-4">
            <p class="text-sm text-gray-500">
                Menampilkan {{ $siswa->firstItem() }} sampai {{ $siswa->lastItem() }} dari {{ $siswa->total() }} data
            </p>
            <div class="pagination">
                {{-- Previous --}}
                @if($siswa->onFirstPage())
                    <span class="pagination-btn" disabled>
                        <x-ui.icon name="chevron-left" size="16" />
                    </span>
                @else
                    {{-- Use data-href for AJAX handling --}}
                    <a href="{{ $siswa->previousPageUrl() }}" class="pagination-btn" data-page="prev">
                        <x-ui.icon name="chevron-left" size="16" />
                    </a>
                @endif
                
                {{-- Next --}}
                @if($siswa->hasMorePages())
                    {{-- Use data-href for AJAX handling --}}
                    <a href="{{ $siswa->nextPageUrl() }}" class="pagination-btn" data-page="next">
                        <x-ui.icon name="chevron-right" size="16" />
                    </a>
                @else
                    <span class="pagination-btn" disabled>
                        <x-ui.icon name="chevron-right" size="16" />
                    </span>
                @endif
            </div>
        </div>
    @endif
