@extends('layouts.app')

@section('title', 'Template Jam Pelajaran')

@section('page-header')
    <x-page-header 
        title="Template Jam Pelajaran" 
        subtitle="Konfigurasi slot waktu per periode semester dan per hari"
        :total="$templateJams->count()" 
    />
@endsection

@section('content')
<div class="space-y-6" x-data="templateJamManager()">

    {{-- Alert --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter Card --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pilih Periode & Hari</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Periode --}}
                <div class="form-group">
                    <label class="form-label">Periode Semester</label>
                    <select name="periode_id" class="form-input" onchange="this.form.submit()">
                        @foreach($allPeriodes as $periode)
                            <option value="{{ $periode->id }}" {{ $selectedPeriode && $selectedPeriode->id == $periode->id ? 'selected' : '' }}>
                                {{ $periode->display_name }} {{ $periode->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Hari Tabs --}}
                <div class="form-group md:col-span-2">
                    <label class="form-label">Hari</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($hariList as $h)
                            <a href="{{ route('admin.template-jam.index', ['periode_id' => $selectedPeriode?->id, 'hari' => $h]) }}"
                               class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $selectedHari == $h ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                {{ $h }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($selectedPeriode)
        {{-- Generate Default Template --}}
        @if($templateJams->count() == 0)
            <div class="card border-2 border-dashed border-emerald-300 bg-emerald-50">
                <div class="card-body text-center py-8">
                    <div class="text-emerald-600 mb-4">
                        <x-ui.icon name="clock" size="48" class="mx-auto" />
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 mb-2">Belum Ada Template untuk {{ $selectedHari }}</h3>
                    <p class="text-slate-600 mb-4">Generate template default dengan 15 slot jam pelajaran?</p>
                    <form action="{{ route('admin.template-jam.generate') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="periode_semester_id" value="{{ $selectedPeriode->id }}">
                        <input type="hidden" name="hari" value="{{ $selectedHari }}">
                        <button type="submit" class="btn btn-primary">
                            <x-ui.icon name="zap" size="16" />
                            <span>Generate Template Default</span>
                        </button>
                    </form>
                </div>
            </div>
        @endif

        {{-- Slots Table - Interactive --}}
        @if($templateJams->count() > 0)
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h3 class="card-title">Template Jam - {{ $selectedHari }}</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-slate-500">
                            {{ $templateJams->where('tipe', 'pelajaran')->count() }} jam pelajaran
                        </span>
                        <form action="{{ route('admin.template-jam.addRow') }}" method="POST" class="flex items-center gap-2">
                            @csrf
                            <input type="hidden" name="periode_semester_id" value="{{ $selectedPeriode->id }}">
                            <input type="hidden" name="hari" value="{{ $selectedHari }}">
                            
                            <div class="flex items-center bg-white border border-slate-200 rounded-lg overflow-hidden">
                                <input type="number" name="jumlah" value="1" min="1" max="20" 
                                       class="w-16 h-9 text-center text-sm border-0 focus:ring-0 text-slate-700" 
                                       placeholder="Jml" title="Jumlah baris yang ingin ditambahkan">
                                <button type="submit" class="px-3 h-9 bg-emerald-600 text-white hover:bg-emerald-700 flex items-center gap-1 text-sm font-medium transition-colors">
                                    <x-ui.icon name="plus" size="14" />
                                    <span>Tambah</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="overflow-x-auto">
                        <table class="table" id="template-jam-table">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="w-20 text-center">No</th>
                                    <th class="w-24 text-center">Jam ke-</th>
                                    <th class="w-28">Mulai</th>
                                    <th class="w-28">Selesai</th>
                                    <th class="w-36">Tipe</th>
                                    <th class="w-24 text-center">Aksi</th>
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
                                        if ($isPelajaran) {
                                            $jamKeCounter++;
                                        }
                                        $jamKe = $isPelajaran ? ($romanNumerals[$jamKeCounter - 1] ?? $jamKeCounter) : '-';
                                        $jamMulai = $slot->jam_mulai instanceof \DateTime ? $slot->jam_mulai->format('H:i') : ($slot->jam_mulai ?? '');
                                        $jamSelesai = $slot->jam_selesai instanceof \DateTime ? $slot->jam_selesai->format('H:i') : ($slot->jam_selesai ?? '');
                                        
                                        // Row color based on type
                                        $rowClass = match($slot->tipe) {
                                            'pelajaran' => '',
                                            'istirahat' => 'bg-amber-50',
                                            'ishoma' => 'bg-orange-50',
                                            'upacara' => 'bg-blue-50',
                                            default => 'bg-slate-50',
                                        };
                                    @endphp
                                    <tr class="{{ $rowClass }} hover:bg-slate-100 transition-colors" 
                                        data-row-id="{{ $slot->id }}"
                                        data-urutan="{{ $slot->urutan }}">
                                        {{-- No (Urutan) --}}
                                        <td class="text-center font-medium text-slate-500">
                                            {{ $slot->urutan }}
                                        </td>
                                        
                                        {{-- Jam ke- (Roman) --}}
                                        <td class="text-center">
                                            @if($isPelajaran)
                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 font-bold text-sm">
                                                    {{ $jamKe }}
                                                </span>
                                            @else
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </td>
                                        
                                        {{-- Jam Mulai --}}
                                        <td>
                                            <input type="text" 
                                                   value="{{ $jamMulai }}"
                                                   data-field="jam_mulai"
                                                   data-id="{{ $slot->id }}"
                                                   data-prev-row="{{ $index > 0 ? $templateJams[$index - 1]->id : '' }}"
                                                   class="form-input text-sm w-full time-input text-center font-mono"
                                                   placeholder="HH:MM"
                                                   pattern="([01]?[0-9]|2[0-3]):[0-5][0-9]"
                                                   maxlength="5"
                                                   @change="updateTime($event)"
                                                   @input="formatTimeInput($event)">
                                        </td>
                                        
                                        {{-- Jam Selesai --}}
                                        <td>
                                            <input type="text" 
                                                   value="{{ $jamSelesai }}"
                                                   data-field="jam_selesai"
                                                   data-id="{{ $slot->id }}"
                                                   data-next-row="{{ $index < $templateJams->count() - 1 ? $templateJams[$index + 1]->id : '' }}"
                                                   class="form-input text-sm w-full time-input text-center font-mono"
                                                   placeholder="HH:MM"
                                                   pattern="([01]?[0-9]|2[0-3]):[0-5][0-9]"
                                                   maxlength="5"
                                                   @change="updateTime($event)"
                                                   @input="formatTimeInput($event)">
                                        </td>
                                        
                                        {{-- Tipe --}}
                                        <td x-data="{ isCustom: false }">
                                            <select x-show="!isCustom"
                                                    data-field="tipe"
                                                    data-id="{{ $slot->id }}"
                                                    class="form-input text-sm w-full tipe-select"
                                                    @change="
                                                        if ($el.value === 'lainnya') {
                                                            isCustom = true;
                                                            $nextTick(() => $refs.customInput.focus());
                                                        } else {
                                                            updateField($event);
                                                        }
                                                    ">
                                                @foreach($tipeOptions as $val => $label)
                                                    <option value="{{ $val }}" {{ $slot->tipe == $val ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            
                                            <input type="text" 
                                                   x-ref="customInput"
                                                   x-show="isCustom"
                                                   data-field="tipe"
                                                   data-id="{{ $slot->id }}"
                                                   class="form-input text-sm w-full"
                                                   placeholder="Nama tipe..."
                                                   style="display: none;"
                                                   @blur="
                                                        if ($el.value && $el.value.trim() !== '') {
                                                            updateField($event);
                                                        } else {
                                                            isCustom = false;
                                                            // Reset helper logic if needed, but page reload will fix consistent state
                                                            $el.previousElementSibling.value = '{{ $slot->tipe }}';
                                                        }
                                                   "
                                                   @keydown.enter.prevent="$el.blur()"
                                                   @keydown.escape="
                                                        isCustom = false;
                                                        $el.value = '';
                                                        $el.previousElementSibling.value = '{{ $slot->tipe }}';
                                                   "
                                            >
                                        </td>
                                        
                                        {{-- Aksi --}}
                                        <td>
                                            <div class="flex items-center justify-center gap-1">
                                                <form action="{{ route('admin.template-jam.destroy', $slot->id) }}" 
                                                      method="POST" 
                                                      onsubmit="return confirm('Hapus baris ini?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-icon btn-white text-red-600 hover:text-red-700" 
                                                            title="Hapus">
                                                        <x-ui.icon name="trash" size="14" />
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Legend & Tips --}}
            <div class="bg-slate-50 border border-slate-200 rounded-lg p-4">
                <h4 class="font-bold text-slate-700 mb-3">💡 Keterangan & Tips</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="font-medium text-slate-700 mb-2">Warna Baris:</p>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="w-4 h-4 bg-white border border-slate-200 rounded"></span>
                                <span>Pelajaran</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-4 h-4 bg-amber-50 border border-amber-200 rounded"></span>
                                <span>Istirahat</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-4 h-4 bg-orange-50 border border-orange-200 rounded"></span>
                                <span>Ishoma</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-4 h-4 bg-blue-50 border border-blue-200 rounded"></span>
                                <span>Upacara</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <p class="font-medium text-slate-700 mb-2">Tips:</p>
                        <ul class="list-disc list-inside text-slate-600 space-y-1">
                            <li>Kolom "Jam ke-" otomatis hanya untuk tipe Pelajaran</li>
                            <li>Ubah jam langsung di tabel, otomatis tersimpan</li>
                            <li>Jam selesai akan menjadi jam mulai baris berikutnya</li>
                            <li>Ubah tipe untuk mengubah kategori slot waktu</li>
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Copy From Other Period --}}
        @if($allPeriodes->count() > 1 && $templateJams->count() == 0)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Salin dari Periode Lain</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.template-jam.copy') }}" method="POST" class="flex items-end gap-4">
                        @csrf
                        <input type="hidden" name="to_periode_id" value="{{ $selectedPeriode->id }}">
                        <div class="form-group flex-1">
                            <label class="form-label">Periode Sumber</label>
                            <select name="from_periode_id" class="form-input" required>
                                <option value="">Pilih periode...</option>
                                @foreach($allPeriodes as $p)
                                    @if($p->id != $selectedPeriode->id)
                                        <option value="{{ $p->id }}">{{ $p->display_name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-secondary">
                            <x-ui.icon name="copy" size="16" />
                            <span>Salin Template</span>
                        </button>
                    </form>
                </div>
            </div>
        @endif
    @else
        <x-ui.empty-state
            icon="calendar"
            title="Belum Ada Periode Semester"
            description="Buat periode semester terlebih dahulu."
            :actionUrl="route('admin.periode-semester.create')"
            actionLabel="Buat Periode"
        />
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
            let value = el.type === 'checkbox' ? (el.checked ? 1 : 0) : el.value;

            // Simple validation
            if (!value || value.trim() === '') {
                 window.location.reload(); 
                 return;
            }
            
            // Normalize value
            value = value.trim().toLowerCase();

            await this.saveField(id, field, value);
            
            // If tipe changed, reload to recalculate Jam ke- and update dropdown options
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

            // Auto-sync: if jam_selesai changed, update next row's jam_mulai
            if (field === 'jam_selesai' && el.dataset.nextRow) {
                const nextRowInput = document.querySelector(`input[data-id="${el.dataset.nextRow}"][data-field="jam_mulai"]`);
                if (nextRowInput && nextRowInput.value !== value) {
                    nextRowInput.value = value;
                    await this.saveField(el.dataset.nextRow, 'jam_mulai', value);
                }
            }

            // Auto-sync: if jam_mulai changed, update prev row's jam_selesai
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

        // Format time input to HH:MM (24-hour)
        formatTimeInput(event) {
            let value = event.target.value.replace(/[^0-9:]/g, '');
            
            // Auto-add colon after 2 digits
            if (value.length === 2 && !value.includes(':')) {
                value = value + ':';
            }
            
            // Limit to 5 characters (HH:MM)
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
