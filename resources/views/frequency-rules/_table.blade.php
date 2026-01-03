
    {{-- Bulk Action Toolbar --}}
    <div x-show="selected.length > 0" x-cloak x-transition 
         class="bg-indigo-50 p-3 flex flex-col sm:flex-row justify-between items-center gap-3 mb-4 rounded-xl border border-indigo-100 shadow-sm relative z-10 transition-all duration-300">
        <div class="flex items-center gap-2">
            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-bold" x-text="selected.length"></span>
            <span class="text-sm font-medium text-indigo-900">Data Terpilih</span>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" 
                    @click="if(confirm('Hapus ' + selected.length + ' data terpilih?')) { alert('Fitur bulk delete sedang dalam pengembangan.'); }" 
                    class="btn btn-sm btn-secondary text-red-600 border-red-200 hover:bg-red-50 hover:border-red-300 transition-colors">
                <x-ui.icon name="trash" size="14" />
                <span>Hapus Terpilih</span>
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th>Nama Pelanggaran</th>
                    <th class="w-[40%]">Rules (Frekuensi, Poin & Sanksi)</th>
                    <th class="text-center">Status</th>
                    <th class="w-32 text-center cursor-pointer select-none hover:bg-gray-50 transition-colors group" @click="toggleSelectionMode()" title="Klik untuk memilih data">
                        <div class="flex items-center justify-center">
                            <template x-if="!selectionMode">
                                <div class="flex items-center justify-center gap-2 text-gray-400 group-hover:text-indigo-600 transition-colors p-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider">Pilih</span>
                                    <x-ui.icon name="square" size="16" />
                                </div>
                            </template>
                            <template x-if="selectionMode">
                                <div class="flex items-center justify-center gap-1">
                                    <input type="checkbox" x-model="selectAll" 
                                        @change="selectAll ? selected = ['{{ $jenisPelanggaran->pluck('id')->implode("','") }}'] : selected = []" 
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
                @forelse($jenisPelanggaran ?? [] as $jp)
                    <tr :class="{ 'bg-indigo-50/50': selected.includes('{{ $jp->id }}') }">
                        {{-- Kategori --}}
                        <td>
                            @php
                                $kategoriNama = strtolower($jp->kategoriPelanggaran->nama_kategori ?? '');
                                $badgeClass = 'badge-neutral';
                                if (str_contains($kategoriNama, 'ringan')) $badgeClass = 'badge-info';
                                elseif (str_contains($kategoriNama, 'sedang')) $badgeClass = 'badge-warning';
                                elseif (str_contains($kategoriNama, 'berat')) $badgeClass = 'badge-danger';
                            @endphp
                            <span class="badge {{ $badgeClass }}">
                                {{ $jp->kategoriPelanggaran->nama_kategori ?? '-' }}
                            </span>
                        </td>

                        {{-- Nama --}}
                        <td>
                            <div class="font-medium text-gray-800">{{ $jp->nama_pelanggaran }}</div>
                            <div class="text-xs text-gray-400 font-mono">ID: {{ $jp->id }}</div>
                        </td>

                        {{-- Rules List --}}
                        <td>
                            @if($jp->frequencyRules->count() > 0)
                                <div class="space-y-2">
                                    @foreach($jp->frequencyRules as $rule)
                                    <div class="p-2 rounded-lg border border-gray-100 bg-gray-50 text-xs">
                                        <div class="flex flex-wrap items-center gap-2 mb-1">
                                            <span class="px-2 py-0.5 rounded bg-gray-800 text-white font-bold text-[10px]">
                                                @if($rule->frequency_min == 1 && !$rule->frequency_max) 
                                                    Setiap 
                                                @elseif($rule->frequency_max) 
                                                    {{$rule->frequency_min}}-{{$rule->frequency_max}}x 
                                                @else 
                                                    {{$rule->frequency_min}}+x 
                                                @endif
                                            </span>
                                            <span class="px-2 py-0.5 rounded bg-red-100 text-red-700 font-bold">{{ $rule->poin }} Poin</span>
                                            @if($rule->trigger_surat)
                                                <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-700 font-bold flex items-center gap-1">
                                                    <x-ui.icon name="mail" size="10" />
                                                    SURAT
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-gray-600">
                                            <span class="text-gray-400 font-bold">Sanksi:</span> {{ $rule->sanksi_description }}
                                        </div>
                                        @if($rule->pembina_roles && count($rule->pembina_roles) > 0)
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            @foreach($rule->pembina_roles as $role)
                                                <span class="text-[9px] bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded font-bold">{{ $role }}</span>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-3 rounded-lg border border-dashed border-gray-200 bg-gray-50 text-center">
                                    <span class="text-xs text-gray-400">Default: {{ $jp->poin }} Poin (Setiap Kejadian)</span>
                                </div>
                            @endif
                        </td>

                        {{-- Status Toggle --}}
                        <td class="text-center">
                            @if($jp->frequencyRules->count() > 0)
                                <span class="badge {{ $jp->is_active ? 'badge-success' : 'badge-neutral' }}">
                                    {{ $jp->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            @else
                                <span class="text-xs text-red-400 italic">No Rules</span>
                            @endif
                        </td>

                    {{-- Actions --}}
                    <td class="text-center relative">
                        {{-- Selection Mode --}}
                        <div x-show="selectionMode" style="display: none;">
                            <input type="checkbox" value="{{ $jp->id }}" x-model="selected" class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer">
                        </div>

                        {{-- Normal Mode --}}
                        <div x-show="!selectionMode">
                            {{-- Desktop --}}
                             <div class="hidden md:hidden" style="display: none;">
                                <a href="{{ route('frequency-rules.show', $jp->id) }}" class="btn btn-icon btn-outline" title="Kelola Rules">
                                    <x-ui.icon name="settings" size="16" />
                                </a>
                                <a href="{{ route('jenis-pelanggaran.edit', $jp->id) }}" class="btn btn-icon btn-outline" title="Edit">
                                    <x-ui.icon name="edit" size="16" />
                                </a>
                                <form action="{{ route('jenis-pelanggaran.destroy', $jp->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus jenis pelanggaran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-outline text-red-500 hover:bg-red-50" title="Hapus">
                                        <x-ui.icon name="trash" size="16" />
                                    </button>
                                </form>
                            </div>
                            
                            {{-- Mobile: Dropdown with Teleport --}}
                            <div class="relative inline-block text-left"
                                 x-data="{
                                     open: false,
                                     timer: null,
                                     isLongPress: false,
                                     
                                     startPress() {
                                         this.isLongPress = false;
                                         this.timer = setTimeout(() => {
                                             this.isLongPress = true;
                                             this.toggleSelectionMode();
                                             if (!this.selected.includes('{{ $jp->id }}')) {
                                                 this.selected.push('{{ $jp->id }}');
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
                                             // Position menu to the left of trigger
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
                                         class="w-48 origin-top-right rounded-xl bg-white shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none border border-gray-100"
                                    >
                                        <div class="py-1">
                                            <a href="{{ route('frequency-rules.show', $jp->id) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                                                <x-ui.icon name="settings" size="14" />
                                                Kelola Rules
                                            </a>
                                            <a href="{{ route('jenis-pelanggaran.edit', $jp->id) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-indigo-600 transition-colors">
                                                <x-ui.icon name="edit" size="14" />
                                                Edit
                                            </a>
                                            <div class="border-t border-gray-100 my-1"></div>
                                            <form action="{{ route('jenis-pelanggaran.destroy', $jp->id) }}" method="POST" onsubmit="return confirm('Hapus jenis pelanggaran ini?')">
                                                @csrf
                                                @method('DELETE')
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
                        <td colspan="5">
                            <x-ui.empty-state 
                                icon="file" 
                                title="Belum Ada Data" 
                                description="Tidak ada jenis pelanggaran yang terdaftar." 
                            >
                                <x-slot:action>
                                    <a href="{{ route('jenis-pelanggaran.create') }}" class="btn btn-primary">Tambah Jenis Pelanggaran</a>
                                </x-slot:action>
                            </x-ui.empty-state>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
