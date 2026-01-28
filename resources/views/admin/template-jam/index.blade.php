@extends('layouts.app')

@section('title', 'Template Jam Pelajaran')

@section('page-header')
    <x-page-header 
        title="Template Jam Pelajaran" 
        subtitle="Konfigurasi slot waktu per periode semester dan per hari"
    />
@endsection

@section('content')
<div class="space-y-6" x-data="templateJamManager()">

    {{-- Filter Periode & Hari --}}
    <div class="bg-white md:border md:border-slate-200 md:rounded-xl md:shadow-sm overflow-hidden">
        <div class="px-4 md:px-6 py-4 border-b border-slate-100">
            <form method="GET" class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                {{-- Periode Dropdown --}}
                <div class="flex items-center gap-3">
                    <label class="text-sm font-medium text-slate-600 whitespace-nowrap">Periode:</label>
                    <select name="periode_id" class="form-input text-sm rounded-lg py-2" onchange="this.form.submit()">
                        @foreach($allPeriodes as $periode)
                            <option value="{{ $periode->id }}" {{ $selectedPeriode && $selectedPeriode->id == $periode->id ? 'selected' : '' }}>
                                {{ $periode->display_name }} {{ $periode->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Hari Pills --}}
                <div class="flex flex-wrap items-center gap-2">
                    @foreach($hariList as $h)
                        <a href="{{ route('admin.template-jam.index', ['periode_id' => $selectedPeriode?->id, 'hari' => $h]) }}"
                           class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all border
                                  {{ $selectedHari == $h 
                                     ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' 
                                     : 'bg-white text-slate-600 border-slate-200 hover:border-indigo-300 hover:bg-indigo-50' }}">
                            {{ $h }}
                        </a>
                    @endforeach
                </div>
            </form>
        </div>
    </div>

    @if($selectedPeriode)
        {{-- Empty State: Generate Default --}}
        @if($templateJams->count() == 0)
            <x-ui.empty-state 
                title="Belum Ada Template untuk {{ $selectedHari }}"
                description="Generate template default dengan 15 slot jam pelajaran?"
                icon="clock"
            >
                <x-slot:action>
                    <form action="{{ route('admin.template-jam.generate') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="periode_semester_id" value="{{ $selectedPeriode->id }}">
                        <input type="hidden" name="hari" value="{{ $selectedHari }}">
                        <button type="submit" class="btn btn-primary">
                            <x-ui.icon name="zap" size="16" />
                            <span>Generate Template Default</span>
                        </button>
                    </form>
                </x-slot:action>
            </x-ui.empty-state>

            {{-- Copy From Other Period --}}
            @if($allPeriodes->count() > 1)
                <div class="bg-white md:border md:border-slate-200 md:rounded-xl md:shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h3 class="font-semibold text-slate-800">Salin dari Periode Lain</h3>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('admin.template-jam.copy') }}" method="POST" class="flex flex-col sm:flex-row items-end gap-4">
                            @csrf
                            <input type="hidden" name="to_periode_id" value="{{ $selectedPeriode->id }}">
                            <div class="flex-1 w-full">
                                <label class="block text-sm font-medium text-slate-600 mb-1.5">Periode Sumber</label>
                                <select name="from_periode_id" class="form-input w-full" required>
                                    <option value="">Pilih periode...</option>
                                    @foreach($allPeriodes as $p)
                                        @if($p->id != $selectedPeriode->id)
                                            <option value="{{ $p->id }}">{{ $p->display_name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-secondary whitespace-nowrap">
                                <x-ui.icon name="copy" size="16" />
                                <span>Salin Template</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        @endif

        {{-- Slots Table --}}
        @if($templateJams->count() > 0)
            <div class="bg-white md:border md:border-slate-200 md:rounded-xl md:shadow-sm overflow-hidden"
                 x-data="{ selectionMode: false, selected: [], selectAll: false }"
                 @toggle-selection-mode.window="selectionMode = !selectionMode; if (!selectionMode) { selected = []; selectAll = false; }"
                 @enter-selection.window="selectionMode = true; if (!selected.includes($event.detail.id)) selected.push($event.detail.id)"
                 x-effect="selectAll ? selected = {{ json_encode($templateJams->pluck('id')->map(fn($id) => (string) $id)->toArray()) }} : null">
                
                {{-- Header --}}
                <div class="px-4 md:px-6 py-4 border-b border-slate-100">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h3 class="font-semibold text-slate-800">Template Jam - {{ $selectedHari }}</h3>
                            <p class="text-sm text-slate-500 mt-0.5">{{ $templateJams->where('tipe', 'pelajaran')->count() }} jam pelajaran</p>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            {{-- Bulk Delete Button --}}
                            <template x-if="selectionMode && selected.length > 0">
                                <form action="{{ route('admin.template-jam.bulkDestroy') }}" method="POST" 
                                      onsubmit="return confirm('Hapus ' + document.querySelectorAll('[name=\'ids[]\']:checked').length + ' baris?')">
                                    @csrf
                                    @method('DELETE')
                                    <template x-for="id in selected" :key="id">
                                        <input type="hidden" name="ids[]" :value="id">
                                    </template>
                                    <button type="submit" class="btn btn-sm bg-red-600 text-white hover:bg-red-700">
                                        <x-ui.icon name="trash" size="14" />
                                        <span>Hapus (<span x-text="selected.length"></span>)</span>
                                    </button>
                                </form>
                            </template>

                            {{-- Cancel Selection --}}
                            <template x-if="selectionMode">
                                <button @click="selectionMode = false; selected = []; selectAll = false" class="btn btn-sm btn-secondary">
                                    <x-ui.icon name="x" size="14" />
                                    <span class="hidden md:inline">Batal</span>
                                </button>
                            </template>

                            {{-- Add Row Form --}}
                            <form action="{{ route('admin.template-jam.addRow') }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                <input type="hidden" name="periode_semester_id" value="{{ $selectedPeriode->id }}">
                                <input type="hidden" name="hari" value="{{ $selectedHari }}">
                                <div class="flex items-center bg-white border border-slate-200 rounded-lg overflow-hidden">
                                    <input type="number" name="jumlah" value="1" min="1" max="20" 
                                           class="w-12 h-9 text-center text-sm border-0 focus:ring-0 text-slate-700">
                                    <button type="submit" class="px-3 h-9 bg-indigo-600 text-white hover:bg-indigo-700 flex items-center gap-1.5 text-sm font-medium transition-colors whitespace-nowrap">
                                        <x-ui.icon name="plus" size="14" />
                                        <span>Tambah Baris</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Desktop Table (hidden on mobile) --}}
                <div class="hidden md:block table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <x-table.action-header />
                                <th class="w-16 text-center">No</th>
                                <th class="w-20 text-center">Jam</th>
                                <th class="w-28">Mulai</th>
                                <th class="w-28">Selesai</th>
                                <th>Tipe</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $jamKeCounter = 0;
                                $romanNumerals = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII', 'XIII', 'XIV', 'XV', 'XVI', 'XVII', 'XVIII', 'XIX', 'XX'];
                            @endphp
                            @foreach($templateJams as $index => $slot)
                                @php
                                    $isPelajaran = $slot->tipe === 'pelajaran';
                                    if ($isPelajaran) $jamKeCounter++;
                                    $jamKe = $isPelajaran ? ($romanNumerals[$jamKeCounter - 1] ?? $jamKeCounter) : '-';
                                    $jamMulai = $slot->jam_mulai instanceof \DateTime ? $slot->jam_mulai->format('H:i') : ($slot->jam_mulai ?? '');
                                    $jamSelesai = $slot->jam_selesai instanceof \DateTime ? $slot->jam_selesai->format('H:i') : ($slot->jam_selesai ?? '');
                                    
                                    $rowClass = match($slot->tipe) {
                                        'pelajaran' => '',
                                        'istirahat' => 'bg-amber-50/50',
                                        'ishoma' => 'bg-orange-50/50',
                                        'upacara' => 'bg-blue-50/50',
                                        default => 'bg-slate-50/50',
                                    };
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <x-table.action-column :id="$slot->id">
                                        <form action="{{ route('admin.template-jam.destroy', $slot->id) }}" method="POST" onsubmit="return confirm('Hapus baris ini?')">
                                            @csrf @method('DELETE')
                                            <x-table.action-item icon="trash" type="submit" class="text-red-600 hover:text-red-700 hover:bg-red-50">
                                                Hapus
                                            </x-table.action-item>
                                        </form>
                                    </x-table.action-column>
                                    <td class="text-center font-medium text-slate-500">{{ $slot->urutan }}</td>
                                    <td class="text-center">
                                        @if($isPelajaran)
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 font-bold text-xs">{{ $jamKe }}</span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <input type="text" value="{{ $jamMulai }}" data-field="jam_mulai" data-id="{{ $slot->id }}"
                                               data-prev-row="{{ $index > 0 ? $templateJams[$index - 1]->id : '' }}"
                                               class="block w-full px-2 py-1.5 text-sm border border-slate-200 rounded-lg text-center font-mono focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                               placeholder="HH:MM" maxlength="5" @change="updateTime($event)" @input="formatTimeInput($event)">
                                    </td>
                                    <td>
                                        <input type="text" value="{{ $jamSelesai }}" data-field="jam_selesai" data-id="{{ $slot->id }}"
                                               data-next-row="{{ $index < $templateJams->count() - 1 ? $templateJams[$index + 1]->id : '' }}"
                                               class="block w-full px-2 py-1.5 text-sm border border-slate-200 rounded-lg text-center font-mono focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                               placeholder="HH:MM" maxlength="5" @change="updateTime($event)" @input="formatTimeInput($event)">
                                    </td>
                                    <td x-data="{ isCustom: false }">
                                        <select x-show="!isCustom" data-field="tipe" data-id="{{ $slot->id }}"
                                                class="block w-full px-2 py-1.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                                @change="if ($el.value === 'lainnya') { isCustom = true; $nextTick(() => $refs.customInput.focus()); } else { updateField($event); }">
                                            @foreach($tipeOptions as $val => $label)
                                                <option value="{{ $val }}" {{ $slot->tipe == $val ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" x-ref="customInput" x-show="isCustom" data-field="tipe" data-id="{{ $slot->id }}"
                                               class="block w-full px-2 py-1.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                               placeholder="Nama tipe..." style="display: none;"
                                               @blur="if ($el.value && $el.value.trim() !== '') { updateField($event); } else { isCustom = false; }"
                                               @keydown.enter.prevent="$el.blur()" @keydown.escape="isCustom = false; $el.value = '';">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Cards (hidden on desktop) --}}
                <div class="md:hidden">
                    @php $jamKeCounter = 0; @endphp
                    @foreach($templateJams as $index => $slot)
                        @php
                            $isPelajaran = $slot->tipe === 'pelajaran';
                            if ($isPelajaran) $jamKeCounter++;
                            $jamKe = $isPelajaran ? ($romanNumerals[$jamKeCounter - 1] ?? $jamKeCounter) : null;
                            $jamMulai = $slot->jam_mulai instanceof \DateTime ? $slot->jam_mulai->format('H:i') : ($slot->jam_mulai ?? '');
                            $jamSelesai = $slot->jam_selesai instanceof \DateTime ? $slot->jam_selesai->format('H:i') : ($slot->jam_selesai ?? '');
                            
                            $bgClass = match($slot->tipe) {
                                'istirahat' => 'bg-amber-50 border-amber-100',
                                'ishoma' => 'bg-orange-50 border-orange-100',
                                'upacara' => 'bg-blue-50 border-blue-100',
                                default => 'bg-white border-slate-100',
                            };
                        @endphp
                        <div class="p-4 {{ $bgClass }} border-b">
                            {{-- Header Row --}}
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    {{-- Selection Checkbox --}}
                                    <template x-if="selectionMode">
                                        <input type="checkbox" value="{{ $slot->id }}" x-model="selected" 
                                               class="w-5 h-5 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                                    </template>
                                    
                                    <span class="text-sm font-bold text-slate-500">#{{ $slot->urutan }}</span>
                                    @if($jamKe)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-100 text-indigo-700 font-bold text-sm">{{ $jamKe }}</span>
                                    @endif
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold
                                        {{ $slot->tipe === 'pelajaran' ? 'bg-slate-200 text-slate-700' : '' }}
                                        {{ $slot->tipe === 'istirahat' ? 'bg-amber-200 text-amber-800' : '' }}
                                        {{ $slot->tipe === 'ishoma' ? 'bg-orange-200 text-orange-800' : '' }}
                                        {{ $slot->tipe === 'upacara' ? 'bg-blue-200 text-blue-800' : '' }}">
                                        {{ ucfirst($slot->tipe) }}
                                    </span>
                                    
                                    <template x-if="!selectionMode">
                                        <form action="{{ route('admin.template-jam.destroy', $slot->id) }}" method="POST" 
                                              onsubmit="return confirm('Hapus baris ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg">
                                                <x-ui.icon name="trash" size="16" />
                                            </button>
                                        </form>
                                    </template>
                                </div>
                            </div>

                            {{-- Time Inputs --}}
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <label class="block text-xs text-slate-500 mb-1">Mulai</label>
                                    <input type="text" value="{{ $jamMulai }}" data-field="jam_mulai" data-id="{{ $slot->id }}"
                                           data-prev-row="{{ $index > 0 ? $templateJams[$index - 1]->id : '' }}"
                                           class="w-full px-3 py-2.5 text-base border border-slate-200 rounded-lg text-center font-mono focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                           placeholder="00:00" @change="updateTime($event)" @input="formatTimeInput($event)">
                                </div>
                                <div class="text-slate-300 text-xl font-light pt-5">→</div>
                                <div class="flex-1">
                                    <label class="block text-xs text-slate-500 mb-1">Selesai</label>
                                    <input type="text" value="{{ $jamSelesai }}" data-field="jam_selesai" data-id="{{ $slot->id }}"
                                           data-next-row="{{ $index < $templateJams->count() - 1 ? $templateJams[$index + 1]->id : '' }}"
                                           class="w-full px-3 py-2.5 text-base border border-slate-200 rounded-lg text-center font-mono focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                           placeholder="00:00" @change="updateTime($event)" @input="formatTimeInput($event)">
                                </div>
                            </div>

                            {{-- Type Select --}}
                            <div class="mt-3">
                                <label class="block text-xs text-slate-500 mb-1">Tipe</label>
                                <select data-field="tipe" data-id="{{ $slot->id }}"
                                        class="w-full px-3 py-2.5 text-base border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                        @change="updateField($event)">
                                    @foreach($tipeOptions as $val => $label)
                                        <option value="{{ $val }}" {{ $slot->tipe == $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Legend (desktop only) --}}
                <div class="hidden md:block px-4 md:px-6 py-3 bg-slate-50 border-t border-slate-100">
                    <div class="flex flex-wrap items-center gap-4 text-xs text-slate-600">
                        <span class="font-medium text-slate-700">Keterangan:</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 bg-white border border-slate-200 rounded"></span> Pelajaran</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 bg-amber-50 border border-amber-200 rounded"></span> Istirahat</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 bg-orange-50 border border-orange-200 rounded"></span> Ishoma</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 bg-blue-50 border border-blue-200 rounded"></span> Upacara</span>
                    </div>
                </div>
            </div>
        @endif
    @else
        <x-ui.empty-state
            icon="calendar"
            title="Belum Ada Periode Semester"
            description="Buat periode semester terlebih dahulu."
        >
            <x-slot:action>
                <a href="{{ route('admin.periode-semester.create') }}" class="btn btn-primary">
                    <x-ui.icon name="plus" size="16" />
                    <span>Buat Periode</span>
                </a>
            </x-slot:action>
        </x-ui.empty-state>
    @endif
</div>

@push('scripts')
<script>
function templateJamManager() {
    return {
        saving: false,

        async updateField(event) {
            const el = event.target;
            const id = el.dataset.id;
            const field = el.dataset.field;
            let value = el.value;

            if (!value || value.trim() === '') {
                 window.location.reload(); 
                 return;
            }
            
            value = value.trim().toLowerCase();
            await this.saveField(id, field, value);
            
            if (field === 'tipe') {
                window.location.reload();
            }
        },

        async updateTime(event) {
            const el = event.target;
            const id = el.dataset.id;
            const field = el.dataset.field;
            const value = el.value;

            await this.saveField(id, field, value);

            if (field === 'jam_selesai' && el.dataset.nextRow) {
                const nextRowInput = document.querySelector(`input[data-id="${el.dataset.nextRow}"][data-field="jam_mulai"]`);
                if (nextRowInput && nextRowInput.value !== value) {
                    nextRowInput.value = value;
                    await this.saveField(el.dataset.nextRow, 'jam_mulai', value);
                }
            }

            if (field === 'jam_mulai' && el.dataset.prevRow) {
                const prevRowInput = document.querySelector(`input[data-id="${el.dataset.prevRow}"][data-field="jam_selesai"]`);
                if (prevRowInput && prevRowInput.value !== value) {
                    prevRowInput.value = value;
                    await this.saveField(el.dataset.prevRow, 'jam_selesai', value);
                }
            }
        },

        async saveField(id, field, value) {
            this.saving = true;

            try {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('_method', 'PATCH');
                formData.append('field', field);
                formData.append('value', value);

                const response = await fetch(`/admin/template-jam/${id}/update-field`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (!data.success) {
                    alert(data.message || 'Gagal menyimpan');
                }
            } catch (error) {
                console.error('Error:', error);
            } finally {
                this.saving = false;
            }
        },

        formatTimeInput(event) {
            let value = event.target.value.replace(/[^0-9:]/g, '');
            
            if (value.length === 2 && !value.includes(':')) {
                value = value + ':';
            }
            
            if (value.length > 5) {
                value = value.substring(0, 5);
            }
            
            event.target.value = value;
        }
    }
}
</script>
@endpush
@endsection
