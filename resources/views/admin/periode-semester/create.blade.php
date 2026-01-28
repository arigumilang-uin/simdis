@extends('layouts.app')

@section('title', 'Tambah Periode Semester')

@section('page-header')
    <x-page-header 
        title="Tambah Periode Semester" 
        subtitle="Buat periode semester baru untuk tahun ajaran."
        :backUrl="route('admin.periode-semester.index')"
    />
@endsection

@section('content')
<x-forms.card 
    action="{{ route('admin.periode-semester.store') }}" 
    maxWidth="full" 
    layout="sidebar"
    x-data="createPeriodeForm()"
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
            <x-slot name="description">Tentukan semester dan tahun ajaran.</x-slot>
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
                    <span><strong>Info:</strong> Nama periode akan otomatis dihasilkan dari Semester + Tahun Ajaran.</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Semester --}}
                    <x-forms.select 
                        name="semester" 
                        label="Semester" 
                        required 
                        x-model="form.semester"
                        placeholder="-- Pilih Semester --"
                    >
                        <option value="Ganjil">Ganjil</option>
                        <option value="Genap">Genap</option>
                    </x-forms.select>
                    
                    {{-- Tahun Ajaran --}}
                    <x-forms.input 
                        name="tahun_ajaran" 
                        label="Tahun Ajaran" 
                        required 
                        placeholder="2025/2026" 
                        x-model="form.tahunAjaran"
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
            <x-slot name="description">Tentukan tanggal mulai dan selesai periode.</x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <x-forms.input 
                    name="tanggal_mulai" 
                    label="Tanggal Mulai" 
                    type="date"
                    required 
                    x-model="form.tanggalMulai"
                />
                
                <x-forms.input 
                    name="tanggal_selesai" 
                    label="Tanggal Selesai" 
                    type="date"
                    required 
                    x-model="form.tanggalSelesai"
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
                Simpan Periode
            </button>
        </div>
    </div>

    {{-- RIGHT COLUMN (Sidebar) --}}
    <div class="hidden lg:block lg:col-span-4 pl-2">
        <div class="sticky top-8 space-y-4">
             <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">
                    Kelengkapan
                </h4>
                <div class="space-y-4 relative">
                     <div class="absolute left-[7px] top-2 bottom-2 w-[1px] bg-slate-100"></div>
                     
                    {{-- Step 1 --}}
                    <div class="relative pl-6">
                        <div class="absolute left-0 top-0.5 w-4 h-4 rounded-full border flex items-center justify-center bg-white z-10"
                                :class="form.semester ? 'border-emerald-500' : 'border-slate-300'">
                                <div class="w-1.5 h-1.5 rounded-full" :class="form.semester ? 'bg-emerald-500' : 'bg-slate-200'"></div>
                        </div>
                        <h5 class="text-sm font-semibold text-slate-800">Semester</h5>
                        <p class="text-[11px] text-slate-500 mt-0.5" x-text="form.semester || 'Wajib dipilih'"></p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="relative pl-6">
                        <div class="absolute left-0 top-0.5 w-4 h-4 rounded-full border flex items-center justify-center bg-white z-10"
                                :class="form.tahunAjaran.length > 4 ? 'border-emerald-500' : 'border-slate-300'">
                                <div class="w-1.5 h-1.5 rounded-full" :class="form.tahunAjaran.length > 4 ? 'bg-emerald-500' : 'bg-slate-200'"></div>
                        </div>
                        <h5 class="text-sm font-semibold text-slate-800">Tahun Ajaran</h5>
                        <p class="text-[11px] text-slate-500 mt-0.5" x-text="form.tahunAjaran || 'Wajib diisi'"></p>
                    </div>
                    
                    {{-- Step 3 --}}
                    <div class="relative pl-6">
                        <div class="absolute left-0 top-0.5 w-4 h-4 rounded-full border flex items-center justify-center bg-white z-10"
                                :class="form.tanggalMulai && form.tanggalSelesai ? 'border-emerald-500' : 'border-slate-300'">
                                <div class="w-1.5 h-1.5 rounded-full" :class="form.tanggalMulai && form.tanggalSelesai ? 'bg-emerald-500' : 'bg-slate-200'"></div>
                        </div>
                        <h5 class="text-sm font-semibold text-slate-800">Rentang Tanggal</h5>
                        <p class="text-[11px] text-slate-500 mt-0.5" x-text="form.tanggalMulai && form.tanggalSelesai ? 'Lengkap' : 'Wajib diisi'"></p>
                    </div>
                    
                    {{-- Preview --}}
                    <div class="relative pl-6">
                         <div class="absolute left-0 top-0.5 w-4 h-4 rounded-full border flex items-center justify-center bg-white z-10"
                                :class="previewNama ? 'border-indigo-500 text-indigo-500' : 'border-slate-300'">
                                <x-ui.icon name="check" size="10" />
                        </div>
                        <h5 class="text-sm font-semibold text-slate-800">Nama Periode</h5>
                        <p class="text-[11px] font-mono text-indigo-600 font-bold mt-0.5" x-text="previewNama || '-'"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-forms.card>

<script>
    function createPeriodeForm() {
        return {
            form: {
                semester: '{{ old('semester') }}',
                tahunAjaran: '{{ old('tahun_ajaran') }}',
                tanggalMulai: '{{ old('tanggal_mulai') }}',
                tanggalSelesai: '{{ old('tanggal_selesai') }}',
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
