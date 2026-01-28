{{-- Dispatch total count to header --}}
<div x-data x-init="$dispatch('update-total-data', { total: {{ $jenisPelanggaran->count() }} })"></div>

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
                    <x-table.action-header />
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
                        <x-table.action-column :id="$jp->id">
                            <x-table.action-item 
                                icon="settings" 
                                href="{{ route('frequency-rules.show', $jp->id) }}"
                            >
                                Kelola Rules
                            </x-table.action-item>
                            
                            <x-table.action-item 
                                icon="edit" 
                                href="{{ route('jenis-pelanggaran.edit', $jp->id) }}"
                            >
                                Edit
                            </x-table.action-item>
                            
                            <x-table.action-separator />
                            
                            <x-table.action-item 
                                icon="trash" 
                                class="text-red-600 hover:bg-red-50 hover:text-red-700"
                                @click="open = false; if(confirm('Hapus jenis pelanggaran ini?')) { document.getElementById('delete-form-{{ $jp->id }}').submit(); }"
                            >
                                Hapus
                            </x-table.action-item>
                        </x-table.action-column>
                    </tr>
                    
                    {{-- Hidden Delete Form --}}
                    <form id="delete-form-{{ $jp->id }}" action="{{ route('jenis-pelanggaran.destroy', $jp->id) }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
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
