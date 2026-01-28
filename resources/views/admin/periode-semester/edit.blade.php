@extends('layouts.app')

@section('title', 'Edit Periode Semester')

@section('page-header')
    <x-page-header 
        title="Edit Periode Semester" 
        subtitle="Perbarui data periode: {{ $periode->nama_periode }}"
        :backUrl="route('admin.periode-semester.index')"
    />
@endsection

@section('content')
<x-forms.card 
    action="{{ route('admin.periode-semester.update', $periode->id) }}" 
    maxWidth="full" 
    layout="sidebar"
    method="PUT"
    x-data="editPeriodeForm()"
    @submit.prevent="submitForm"
>
    {{-- LEFT COLUMN (Main Content) --}}
    <div class="lg:col-span-8 space-y-6">
        
        {{-- SECTION 1: Konfigurasi Periode --}}
        <x-forms.section 
            title="Konfigurasi Periode" 
            variant="card"
            icon="calendar"
        >
            <x-slot name="description">Konfigurasi semester dan tahun ajaran.</x-slot>
            <x-slot name="actions">
                <div x-show="previewNama" x-transition 
                        class="flex items-center gap-1.5 text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100">
                    <span class="text-[11px] font-bold uppercase tracking-wider" x-text="previewNama"></span>
                </div>
            </x-slot>

            <div class="space-y-5">
                {{-- Info Box --}}
                <div class="p-3 bg-emerald-50 border border-emerald-100 rounded-lg text-sm text-emerald-700 flex items-start gap-2">
                    <x-ui.icon name="info" size="16" class="mt-0.5 shrink-0" />
                    <span><strong>Nama Periode:</strong> {{ $periode->nama_periode }} (otomatis dihasilkan)</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Semester --}}
                    <x-forms.select 
                        name="semester" 
                        label="Semester" 
                        required 
                        x-model="form.semester"
                    >
                        <option value="Ganjil" {{ old('semester', $periode->semester->value) === 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                        <option value="Genap" {{ old('semester', $periode->semester->value) === 'Genap' ? 'selected' : '' }}>Genap</option>
                    </x-forms.select>
                    
                    {{-- Tahun Ajaran --}}
                    <x-forms.input 
                        name="tahun_ajaran" 
                        label="Tahun Ajaran" 
                        required 
                        placeholder="2025/2026" 
                        x-model="form.tahunAjaran"
                        :value="$periode->tahun_ajaran"
                        help="Format: YYYY/YYYY"
                    />
                </div>
            </div>
        </x-forms.section>

        {{-- SECTION 2: Rentang Tanggal --}}
        <x-forms.section 
            title="Rentang Tanggal" 
            variant="card"
            icon="clock"
        >
            <x-slot name="description">Perbarui tanggal mulai dan selesai periode.</x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <x-forms.input 
                    name="tanggal_mulai" 
                    label="Tanggal Mulai" 
                    type="date"
                    required 
                    x-model="form.tanggalMulai"
                    :value="$periode->tanggal_mulai->format('Y-m-d')"
                />
                
                <x-forms.input 
                    name="tanggal_selesai" 
                    label="Tanggal Selesai" 
                    type="date"
                    required 
                    x-model="form.tanggalSelesai"
                    :value="$periode->tanggal_selesai->format('Y-m-d')"
                />
            </div>

            {{-- Duration Preview --}}
            <div x-show="durasiHari > 0" x-transition class="mt-4 p-3 bg-slate-50 border border-slate-100 rounded-lg text-sm text-slate-600 flex items-center gap-2">
                <x-ui.icon name="calendar-days" size="16" />
                <span>Durasi: <strong x-text="durasiHari"></strong> hari (<strong x-text="Math.ceil(durasiHari / 7)"></strong> minggu)</span>
            </div>
        </x-forms.section>

        {{-- ACTIONS --}}
        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('admin.periode-semester.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition-all">
                Batal
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold shadow-sm flex items-center gap-2 transition-all active:scale-95 bg-indigo-600 hover:bg-indigo-700"
                :disabled="!isValid"
                :class="{'opacity-50 cursor-not-allowed': !isValid}">
                <x-ui.icon name="check" size="16" />
                Simpan Perubahan
            </button>
        </div>
    </div>

    {{-- RIGHT COLUMN (Sidebar) --}}
    <div class="hidden lg:block lg:col-span-4 pl-2">
        <div class="sticky top-8 space-y-4">
             {{-- Status Card --}}
             <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">
                    Status Periode
                </h4>
                <div class="space-y-3">
                    @if($periode->is_active)
                        <div class="flex items-center gap-2 p-2.5 bg-emerald-50 border border-emerald-100 rounded-lg">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                            <span class="text-sm font-semibold text-emerald-700">Periode Aktif</span>
                        </div>
                    @else
                        <div class="flex items-center gap-2 p-2.5 bg-slate-50 border border-slate-100 rounded-lg">
                            <span class="w-2 h-2 bg-slate-400 rounded-full"></span>
                            <span class="text-sm font-medium text-slate-600">Tidak Aktif</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Metadata Card --}}
             <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">
                    Informasi
                </h4>
                <div class="space-y-4 relative">
                     <div class="absolute left-[7px] top-2 bottom-2 w-[1px] bg-slate-100"></div>
                     
                    <div class="relative pl-6">
                        <div class="absolute left-0 top-0.5 w-4 h-4 rounded-full border border-slate-200 bg-white z-10 flex items-center justify-center">
                             <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div>
                        </div>
                        <h5 class="text-sm font-semibold text-slate-800">Dibuat</h5>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $periode->created_at->format('d M Y, H:i') }}</p>
                    </div>

                    <div class="relative pl-6">
                        <div class="absolute left-0 top-0.5 w-4 h-4 rounded-full border border-slate-200 bg-white z-10 flex items-center justify-center">
                             <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div>
                        </div>
                         <h5 class="text-sm font-semibold text-slate-800">Terakhir Diubah</h5>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $periode->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-amber-50 rounded-xl p-5 border border-amber-100">
                <h4 class="text-xs font-bold text-amber-800 uppercase tracking-widest mb-3 flex items-center gap-2">
                    <x-ui.icon name="alert-triangle" size="14" /> Perhatian
                </h4>
                 <p class="text-xs text-amber-700 leading-relaxed">
                    Mengubah tanggal periode akan mempengaruhi jadwal dan absensi yang sudah terhubung.
                </p>
            </div>
        </div>
    </div>
</x-forms.card>

<script>
    function editPeriodeForm() {
        return {
            form: {
                semester: '{{ old('semester', $periode->semester->value) }}',
                tahunAjaran: '{{ old('tahun_ajaran', $periode->tahun_ajaran) }}',
                tanggalMulai: '{{ old('tanggal_mulai', $periode->tanggal_mulai->format('Y-m-d')) }}',
                tanggalSelesai: '{{ old('tanggal_selesai', $periode->tanggal_selesai->format('Y-m-d')) }}',
            },
            
            get isValid() {
                return this.form.semester && 
                       this.form.tahunAjaran.length > 4 && 
                       this.form.tanggalMulai && 
                       this.form.tanggalSelesai;
            },
            
            get previewNama() {
                if (!this.form.semester || !this.form.tahunAjaran) return '';
                return `Semester ${this.form.semester} ${this.form.tahunAjaran}`;
            },
            
            get durasiHari() {
                if (!this.form.tanggalMulai || !this.form.tanggalSelesai) return 0;
                const start = new Date(this.form.tanggalMulai);
                const end = new Date(this.form.tanggalSelesai);
                const diffTime = Math.abs(end - start);
                return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            },
            
            submitForm(e) {
                if (this.isValid) {
                    e.target.submit();
                }
            }
        }
    }
</script>
@endsection
