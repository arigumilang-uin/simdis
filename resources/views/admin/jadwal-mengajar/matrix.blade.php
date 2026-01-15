@extends('layouts.app')

@section('title', 'Matrix Jadwal Mengajar')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header 
        title="Input Matrix Jadwal" 
        subtitle="Input jadwal mengajar dalam format matrix untuk satu kelas dan satu hari"
    />

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
            <h3 class="card-title">Pilih Periode, Kelas & Hari</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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

                {{-- Kelas --}}
                <div class="form-group">
                    <label class="form-label">Kelas</label>
                    <select name="kelas_id" class="form-input" onchange="this.form.submit()">
                        <option value="">Pilih Kelas...</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id }}" {{ $selectedKelas && $selectedKelas->id == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }} ({{ $k->jurusan?->kode_jurusan ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Hari --}}
                <div class="form-group">
                    <label class="form-label">Hari</label>
                    <select name="hari" class="form-input" onchange="this.form.submit()">
                        @foreach($hariList as $h)
                            <option value="{{ $h }}" {{ $selectedHari == $h ? 'selected' : '' }}>{{ $h }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Info --}}
                <div class="form-group">
                    <label class="form-label">Info</label>
                    <div class="text-sm text-slate-600 bg-slate-50 rounded-lg p-3">
                        @if($selectedKelas && $selectedKurikulum)
                            <div>Tingkat: <strong>{{ $selectedKelas->tingkat }}</strong></div>
                            <div>Kurikulum: <strong>{{ $selectedKurikulum->kode }}</strong></div>
                        @elseif($selectedKelas)
                            <div class="text-amber-600">⚠ Kurikulum belum dikonfigurasi untuk tingkat {{ $selectedKelas->tingkat }}</div>
                        @else
                            <div class="text-slate-400">Pilih kelas terlebih dahulu</div>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Matrix Table --}}
    @if($selectedKelas && $selectedPeriode && $templateJams->count() > 0)
        @if($mapelList->count() > 0)
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h3 class="card-title">
                        Jadwal {{ $selectedKelas->nama_kelas }} - {{ $selectedHari }}
                    </h3>
                    <span class="text-sm text-slate-500">
                        {{ $templateJams->where('tipe', 'pelajaran')->count() }} slot pelajaran
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="w-16 text-center">Jam</th>
                                    <th class="w-32">Waktu</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Guru Pengajar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($templateJams as $slot)
                                    @php
                                        $jadwal = $existingJadwal[$slot->id] ?? null;
                                        $isBreak = $slot->tipe !== 'pelajaran';
                                    @endphp
                                    
                                    @if($isBreak)
                                        {{-- Break row --}}
                                        <tr class="bg-amber-50">
                                            <td class="text-center font-medium text-amber-600">{{ $slot->urutan }}</td>
                                            <td class="font-mono text-sm text-amber-600">{{ $slot->waktu }}</td>
                                            <td colspan="2" class="text-center text-amber-600 font-medium">
                                                🍴 {{ $slot->label }}
                                            </td>
                                        </tr>
                                    @else
                                        {{-- Pelajaran row with inline dropdowns --}}
                                        <tr class="hover:bg-slate-50" 
                                            x-data="jadwalRow({{ $slot->id }}, {{ $jadwal?->mata_pelajaran_id ?? 'null' }}, {{ $jadwal?->user_id ?? 'null' }})"
                                        >
                                            <td class="text-center">
                                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 font-bold text-sm">
                                                    {{ $slot->urutan }}
                                                </span>
                                            </td>
                                            <td class="font-mono text-sm">
                                                {{ $slot->waktu }}
                                            </td>
                                            <td>
                                                <select x-model="mapelId" 
                                                        @change="onMapelChange()" 
                                                        :disabled="saving"
                                                        class="form-input text-sm w-full border-slate-200 focus:border-emerald-500 focus:ring-emerald-500"
                                                        :class="{ 'opacity-50': saving }">
                                                    <option value="">-- Pilih Mapel --</option>
                                                    @foreach($mapelList as $mp)
                                                        <option value="{{ $mp->id }}">{{ $mp->nama_mapel }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select x-model="guruId" 
                                                        @change="save()" 
                                                        :disabled="saving || loadingGuru"
                                                        class="form-input text-sm w-full border-slate-200 focus:border-emerald-500 focus:ring-emerald-500"
                                                        :class="{ 'opacity-50': saving || loadingGuru }">
                                                    <option value="">-- Pilih Guru --</option>
                                                    <template x-for="g in guruOptions" :key="g.id">
                                                        <option :value="g.id" x-text="g.nama + (g.is_primary ? ' ⭐' : '')"></option>
                                                    </template>
                                                    <template x-if="guruOptions.length === 0 && !loadingGuru && mapelId">
                                                        <option disabled>Tidak ada guru untuk mapel ini</option>
                                                    </template>
                                                </select>
                                                <div x-show="loadingGuru" class="text-xs text-slate-400 mt-1">Loading guru...</div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Info --}}
            <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 text-sm text-slate-600">
                <strong>💡 Tips:</strong> 
                <ul class="list-disc list-inside mt-1 space-y-1">
                    <li>Pilih mata pelajaran terlebih dahulu, maka guru pengampu akan muncul di dropdown.</li>
                    <li>Guru dengan tanda ⭐ adalah guru utama untuk mapel tersebut.</li>
                    <li>Data akan otomatis tersimpan setelah memilih guru.</li>
                </ul>
            </div>
        @else
            <x-ui.empty-state
                icon="alert-triangle"
                title="Kurikulum Belum Dikonfigurasi"
                description="Mata pelajaran tidak dapat ditampilkan karena kurikulum untuk tingkat {{ $selectedKelas->tingkat }} belum dikonfigurasi pada periode ini."
                :actionUrl="route('admin.periode-semester.tingkatKurikulum', $selectedPeriode->id)"
                actionLabel="Konfigurasi Kurikulum"
            />
        @endif
    @elseif($selectedKelas && $selectedPeriode && $templateJams->count() == 0)
        <x-ui.empty-state
            icon="clock"
            title="Belum Ada Template Jam"
            description="Template jam untuk hari {{ $selectedHari }} belum dikonfigurasi. Silakan buat template jam terlebih dahulu."
            :actionUrl="route('admin.template-jam.index', ['periode_id' => $selectedPeriode->id, 'hari' => $selectedHari])"
            actionLabel="Buat Template Jam"
        />
    @elseif(!$selectedKelas)
        <x-ui.empty-state
            icon="info"
            title="Pilih Kelas"
            description="Silakan pilih kelas terlebih dahulu untuk menampilkan matrix jadwal."
        />
    @endif

    {{-- Back Button --}}
    <div class="flex justify-start">
        <a href="{{ route('admin.jadwal-mengajar.index', ['periode_id' => $selectedPeriode?->id]) }}" class="btn btn-secondary">
            <x-ui.icon name="arrow-left" size="16" />
            <span>Kembali ke Daftar</span>
        </a>
    </div>
</div>

@push('scripts')
<script>
// Cache guru per mapel
const guruCache = {};

function jadwalRow(templateJamId, initialMapelId, initialGuruId) {
    return {
        templateJamId: templateJamId,
        mapelId: initialMapelId || '',
        guruId: initialGuruId || '',
        guruOptions: [],
        saving: false,
        loadingGuru: false,

        async init() {
            // Load guru if mapel already selected
            if (this.mapelId) {
                await this.loadGuruForMapel(this.mapelId);
            }
        },

        async onMapelChange() {
            this.guruId = ''; // Reset guru when mapel changes
            if (this.mapelId) {
                await this.loadGuruForMapel(this.mapelId);
            } else {
                this.guruOptions = [];
            }
        },

        async loadGuruForMapel(mapelId) {
            // Check cache first
            if (guruCache[mapelId]) {
                this.guruOptions = guruCache[mapelId];
                return;
            }

            this.loadingGuru = true;
            try {
                const response = await fetch(`/admin/mata-pelajaran/${mapelId}/guru`);
                const data = await response.json();
                this.guruOptions = data;
                guruCache[mapelId] = data; // Cache it
            } catch (error) {
                console.error('Error loading guru:', error);
                this.guruOptions = [];
            } finally {
                this.loadingGuru = false;
            }
        },

        async save() {
            // Only save if both are selected or both are empty
            if ((this.mapelId && !this.guruId) || (!this.mapelId && this.guruId)) {
                return; // Wait for both to be selected
            }

            this.saving = true;

            try {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('periode_semester_id', '{{ $selectedPeriode?->id }}');
                formData.append('template_jam_id', this.templateJamId);
                formData.append('kelas_id', '{{ $selectedKelas?->id }}');
                formData.append('mata_pelajaran_id', this.mapelId);
                formData.append('user_id', this.guruId);

                const response = await fetch('{{ route("admin.jadwal-mengajar.updateCell") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                const data = await response.json();

                if (!data.success) {
                    alert(data.message || 'Terjadi kesalahan');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menyimpan');
            } finally {
                this.saving = false;
            }
        }
    }
}
</script>
@endpush
@endsection
