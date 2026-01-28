@extends('layouts.app')

@section('title', 'Edit Kurikulum')

@section('page-header')
    <x-page-header 
        title="Edit Kurikulum" 
        subtitle="Perbarui data kurikulum: {{ $kurikulum->nama }}"
        :backUrl="route('admin.kurikulum.index')"
    />
@endsection

@section('content')
<x-forms.card 
    action="{{ route('admin.kurikulum.update', $kurikulum->id) }}" 
    maxWidth="full" 
    layout="sidebar"
    method="PUT"
    x-data="editKurikulumForm()"
    @submit.prevent="submitForm"
>
    {{-- LEFT COLUMN (Main Content) --}}
    <div class="lg:col-span-8 space-y-6">
        
        {{-- SECTION 1: Identitas Kurikulum --}}
        <x-forms.section 
            title="Identitas Kurikulum" 
            variant="card"
            icon="book-open"
        >
            <x-slot name="description">Informasi dasar kurikulum.</x-slot>
            <x-slot name="actions">
                <div x-show="isValid" x-transition 
                        class="flex items-center gap-1.5 text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-100">
                    <x-ui.icon name="check" size="14" stroke-width="3" />
                    <span class="text-[11px] font-bold uppercase tracking-wider">Lengkap</span>
                </div>
            </x-slot>

            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-forms.input 
                        name="kode" 
                        label="Kode Kurikulum" 
                        required 
                        placeholder="Contoh: K13, MERDEKA" 
                        x-model="form.kode"
                        :value="$kurikulum->kode"
                        help="Kode singkat dan unik."
                    />
                    
                    <x-forms.input 
                        name="tahun_berlaku" 
                        label="Tahun Berlaku" 
                        type="number"
                        placeholder="Contoh: 2022" 
                        x-model="form.tahunBerlaku"
                        :value="$kurikulum->tahun_berlaku"
                        min="2000"
                        max="2100"
                        help="Opsional."
                    />
                </div>
                
                <x-forms.input 
                    name="nama" 
                    label="Nama Kurikulum" 
                    required 
                    placeholder="Contoh: Kurikulum Merdeka" 
                    x-model="form.nama"
                    :value="$kurikulum->nama"
                />
                
                <x-forms.textarea 
                    name="deskripsi" 
                    label="Deskripsi" 
                    placeholder="Deskripsi singkat tentang kurikulum ini (opsional)"
                    rows="3"
                    :value="$kurikulum->deskripsi"
                />
            </div>
        </x-forms.section>

        {{-- SECTION 2: Status --}}
        <x-forms.section 
            title="Status" 
            variant="card"
            icon="toggle-left"
        >
            <x-slot name="description">Atur status aktif kurikulum.</x-slot>

            <x-forms.checkbox 
                name="is_active" 
                label="Kurikulum Aktif"
                :checked="old('is_active', $kurikulum->is_active)"
                help="Kurikulum aktif akan tersedia untuk dipilih di modul lain."
            />
        </x-forms.section>

        {{-- ACTIONS --}}
        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('admin.kurikulum.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition-all">
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
                    Informasi
                </h4>
                <div class="space-y-4 relative">
                     <div class="absolute left-[7px] top-2 bottom-2 w-[1px] bg-slate-100"></div>
                     
                    <div class="relative pl-6">
                        <div class="absolute left-0 top-0.5 w-4 h-4 rounded-full border border-slate-200 bg-white z-10 flex items-center justify-center">
                             <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div>
                        </div>
                        <h5 class="text-sm font-semibold text-slate-800">Dibuat</h5>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $kurikulum->created_at->format('d M Y') }}</p>
                    </div>

                    <div class="relative pl-6">
                        <div class="absolute left-0 top-0.5 w-4 h-4 rounded-full border border-slate-200 bg-white z-10 flex items-center justify-center">
                             <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div>
                        </div>
                         <h5 class="text-sm font-semibold text-slate-800">Terakhir Diubah</h5>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $kurikulum->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-indigo-50 rounded-xl p-5 border border-indigo-100">
                <h4 class="text-xs font-bold text-indigo-800 uppercase tracking-widest mb-3 flex items-center gap-2">
                    <x-ui.icon name="info" size="14" /> Info Edit
                </h4>
                 <p class="text-xs text-indigo-700 leading-relaxed">
                    Mengubah kurikulum tidak akan mempengaruhi mata pelajaran atau jadwal yang sudah terhubung.
                </p>
            </div>
        </div>
    </div>
</x-forms.card>

<script>
    function editKurikulumForm() {
        return {
            form: {
                kode: '{{ old('kode', $kurikulum->kode) }}',
                nama: '{{ old('nama', $kurikulum->nama) }}',
                tahunBerlaku: '{{ old('tahun_berlaku', $kurikulum->tahun_berlaku) }}',
            },
            
            get isValid() {
                return this.form.kode.length > 0 && this.form.nama.length > 2;
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
