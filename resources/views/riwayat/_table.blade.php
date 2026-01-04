{{-- Riwayat Table Partial --}}
<div x-data x-init="$dispatch('update-total-data', { total: {{ $riwayat->total() }} })"></div>

{{-- Bulk Action Toolbar --}}
<div x-show="selected.length > 0" x-cloak x-transition 
     class="bg-indigo-50 p-3 flex flex-col sm:flex-row justify-between items-center gap-3 mb-4 rounded-xl border border-indigo-100 shadow-sm relative z-10">
    <div class="flex items-center gap-2">
        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-bold" x-text="selected.length"></span>
        <span class="text-sm font-medium text-indigo-900">Data Terpilih</span>
    </div>
    <div class="flex flex-wrap gap-2">
        <button type="button" 
                @click="if(confirm('Apakah Anda yakin ingin menghapus ' + selected.length + ' data pelanggaran terpilih? Tindakan ini tidak dapat dibatalkan.')) { alert('Fitur bulk delete sedang dalam pengembangan.'); }" 
                class="btn btn-sm btn-secondary text-red-600 border-red-200 hover:bg-red-50 hover:border-red-300 transition-colors">
            <x-ui.icon name="trash" size="14" />
            <span>Hapus Terpilih</span>
        </button>
    </div>
</div>

{{-- Data Table --}}
<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Siswa</th>
                <th class="">Kelas</th>
                <th>Pelanggaran</th>
                <th class="text-center">Poin</th>
                <th class="">Dicatat Oleh</th>
                <th class="text-center">Bukti</th>
                <th class="w-24 text-center cursor-pointer select-none hover:bg-gray-100 transition-colors group" @click="toggleSelectionMode()" title="Klik untuk memilih data">
                    <div class="flex items-center justify-center">
                        <template x-if="!selectionMode">
                            <div class="flex items-center justify-center gap-2 text-gray-400 group-hover:text-indigo-600 transition-colors p-1">
                                <span class="text-[10px] font-bold uppercase tracking-wider">Pilih</span>
                                <x-ui.icon name="check-square" size="16" />
                            </div>
                        </template>
                        <template x-if="selectionMode">
                            <div class="flex items-center justify-center gap-1">
                                <input type="checkbox" id="riwayat_select_all" name="select_all" x-model="selectAll" 
                                    @change="selectAll ? selected = ['{{ $riwayat->pluck('id')->implode("','") }}'] : selected = []" 
                                    @click.stop class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer" title="Pilih Semua">
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
            @forelse($riwayat as $r)
                <tr :class="{ 'bg-indigo-50/40': selected.includes('{{ $r->id }}') }">
                    {{-- Waktu (Tanggal + Jam) --}}
                    <td class="whitespace-nowrap">
                        <div class="font-medium text-gray-800">{{ $r->tanggal_kejadian->format('d M Y') }}</div>
                        <div class="text-xs text-gray-400">
                            <x-ui.icon name="clock" size="12" class="inline mr-1" />
                            {{ $r->tanggal_kejadian->format('H:i') }} WIB
                        </div>
                    </td>
                    
                    {{-- Siswa --}}
                    <td>
                        <a href="{{ route('siswa.show', $r->siswa->id ?? 0) }}" class="font-medium text-gray-800 hover:text-blue-600">
                            {{ $r->siswa->nama_siswa ?? '-' }}
                        </a>
                        <div class="text-xs text-gray-400 font-mono">{{ $r->siswa->nisn ?? '-' }}</div>
                    </td>
                    
                    {{-- Kelas --}}
                    <td class="">
                        <span class="badge badge-primary">{{ $r->siswa->kelas->nama_kelas ?? '-' }}</span>
                    </td>
                    
                    {{-- Pelanggaran --}}
                    <td class="max-w-xs">
                        <p class="font-medium text-gray-800">{{ $r->jenisPelanggaran->nama_pelanggaran ?? '-' }}</p>
                        <p class="text-xs text-gray-400">{{ $r->jenisPelanggaran->kategoriPelanggaran->nama_kategori ?? '' }}</p>
                        @if($r->keterangan)
                            <p class="text-sm text-gray-500 truncate mt-1 italic">"{{ Str::limit($r->keterangan, 40) }}"</p>
                        @endif
                    </td>
                    
                    {{-- Poin --}}
                    <td class="text-center">
                        @php
                            $poinInfo = \App\Helpers\PoinDisplayHelper::getPoinForRiwayat($r);
                        @endphp
                        @if($poinInfo['matched'] && $poinInfo['poin'] > 0)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700" 
                                  title="{{ \App\Helpers\PoinDisplayHelper::getFrequencyText($r) }}">
                                +{{ $poinInfo['poin'] }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-400">
                                +0
                            </span>
                        @endif
                        @if(!empty($poinInfo['frequency']))
                            <div class="text-[10px] text-gray-400 mt-1">{{ $poinInfo['frequency'] }}× Kejadian</div>
                        @endif
                    </td>
                    
                    {{-- Dicatat Oleh --}}
                    <td class="text-sm">
                        @if($r->guruPencatat)
                            <div class="font-medium text-gray-700">{{ $r->guruPencatat->username }}</div>
                            <div class="text-[10px] text-gray-400">{{ $r->guruPencatat->nama ?? '-' }}</div>
                        @else
                            <span class="text-gray-400 italic text-xs">Sistem</span>
                        @endif
                    </td>
                    
                    {{-- Bukti Foto --}}
                    <td class="text-center">
                        @if($r->bukti_foto_path)
                            <a href="{{ asset('storage/' . $r->bukti_foto_path) }}" target="_blank" 
                               class="btn btn-icon btn-outline" title="Lihat Bukti">
                                <x-ui.icon name="image" size="16" />
                            </a>
                        @else
                            <span class="text-gray-300">-</span>
                        @endif
                    </td>
                    
                    {{-- Aksi --}}
                    <td class="text-center relative">
                        {{-- Selection Mode --}}
                        <div x-show="selectionMode" style="display: none;">
                            <input type="checkbox" id="riwayat_select_{{ $r->id }}" name="selected_ids[]" value="{{ $r->id }}" x-model="selected" class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer">
                        </div>

                        {{-- Normal Mode --}}
                        <div x-show="!selectionMode">
                            {{-- Desktop Buttons --}}
                            <div class="hidden md:flex justify-center gap-1">
                                <a href="{{ route('riwayat.edit', $r->id) }}" class="btn btn-icon btn-outline" title="Edit">
                                    <x-ui.icon name="edit" size="16" />
                                </a>
                                <form action="{{ route('riwayat.destroy', $r->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus riwayat pelanggaran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-outline text-red-500 hover:bg-red-50 hover:border-red-200" title="Hapus">
                                        <x-ui.icon name="trash" size="16" />
                                    </button>
                                </form>
                            </div>
                            
                            {{-- Mobile Kebab Dropdown --}}
                            <div class="md:hidden relative inline-block text-left"
                                 x-data="{
                                     open: false,
                                     timer: null,
                                     isLongPress: false,
                                     
                                     startPress() {
                                         this.isLongPress = false;
                                         this.timer = setTimeout(() => {
                                             this.isLongPress = true;
                                             this.toggleSelectionMode();
                                             if (!this.selected.includes('{{ $r->id }}')) {
                                                 this.selected.push('{{ $r->id }}');
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
                                             let left = trigger.right - menu.offsetWidth;
                                             let top = trigger.bottom + 2; 
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
                            >
                                <button 
                                    x-ref="trigger" 
                                    @click="toggle()" 
                                    @mousedown="startPress()"
                                    @touchstart="startPress()"
                                    @mouseup="endPress()"
                                    @mouseleave="endPress()"
                                    @touchend="endPress()"
                                    class="p-1.5 text-gray-400 rounded-lg hover:bg-gray-100 hover:text-gray-600 transition-colors"
                                >
                                    <x-ui.icon name="more-horizontal" size="18" />
                                </button>

                                <template x-teleport="body">
                                    <div x-show="open" 
                                         x-ref="menu"
                                         @click.outside="open = false"
                                         style="position: fixed; z-index: 9999; display: none;"
                                         x-transition:enter="transition ease-out duration-100"
                                         class="w-36 origin-top-right rounded-xl bg-white shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none border border-gray-100"
                                    >
                                        <div class="py-1">
                                            <a href="{{ route('riwayat.edit', $r->id) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                                                <x-ui.icon name="edit" size="14" />
                                                Edit
                                            </a>
                                            <div class="border-t border-gray-100 my-1"></div>
                                            <form action="{{ route('riwayat.destroy', $r->id) }}" method="POST" onsubmit="return confirm('Hapus riwayat pelanggaran ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors">
                                                    <x-ui.icon name="trash" size="14" />
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">
                        <x-ui.empty-state 
                            icon="rotate-ccw" 
                            title="Tidak Ada Data" 
                            description="Belum ada riwayat pelanggaran yang dicatat." 
                        >
                            <x-slot:action>
                                <a href="{{ route('riwayat.create') }}" class="btn btn-primary">Catat Pelanggaran</a>
                            </x-slot:action>
                        </x-ui.empty-state>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($riwayat->hasPages())
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-4">
        <p class="text-sm text-gray-500">
            Menampilkan {{ $riwayat->firstItem() }} - {{ $riwayat->lastItem() }} dari {{ $riwayat->total() }}
        </p>
        {{ $riwayat->links() }}
    </div>
@endif
