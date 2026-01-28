@extends('layouts.app')

@section('title', 'Tambah Kelas')

@section('page-header')
    <x-page-header 
        title="Tambah Kelas" 
        subtitle="Buat rombongan belajar baru dalam sistem."
        :backUrl="route('kelas.index')"
    />
@endsection

@section('content')
@php
    $konsentrasiApiUrl = route('api.konsentrasi.by-jurusan');
    
    // Build Jurusan Map for Alpine Preview
    $jurusanMap = [];
    foreach($jurusanList ?? [] as $j) {
        $jurusanMap[$j->id] = $j->kode_jurusan ?? strtoupper(substr($j->nama_jurusan, 0, 3));
    }
@endphp

<x-forms.card 
    action="{{ route('kelas.store') }}" 
    maxWidth="full" 
    layout="sidebar"
    x-data="createKelasForm({{ json_encode($jurusanMap) }})"
    @submit.prevent="submitForm"
>
    {{-- LEFT COLUMN (Main Content) --}}
    <div class="lg:col-span-8 space-y-6">
        
        {{-- SECTION 1: Konfigurasi --}}
        <x-forms.section 
            title="Konfigurasi Kelas" 
            variant="card"
            icon="layout"
        >
            <x-slot name="description">Tentukan tingkat, jurusan, dan nomor rombel.</x-slot>
            <x-slot name="actions">
                <div x-show="generatedName" x-transition 
                        class="flex items-center gap-1.5 text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100">
                    <span class="text-[11px] font-bold uppercase tracking-wider" x-text="generatedName"></span>
                </div>
            </x-slot>

            <div class="space-y-6">
                {{-- Tingkat (Custom Radio Cards) --}}
                <div class="form-group">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Tingkat Kelas <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach(['X', 'XI', 'XII'] as $val)
                        <label class="relative">
                            <input type="radio" name="tingkat" value="{{ $val }}" x-model="form.tingkat" class="peer sr-only">
                            <div class="p-3 text-center rounded-xl border-2 cursor-pointer transition-all hover:bg-slate-50 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700 border-slate-200 text-slate-500">
                                <span class="block text-2xl font-bold mb-0.5">{{ $val == 'X' ? '10' : ($val == 'XI' ? '11' : '12') }}</span>
                                <span class="text-[10px] font-bold uppercase tracking-wider">Kelas {{ $val }}</span>
                            </div>
                            <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 text-indigo-500 transition-opacity">
                                <x-ui.icon name="check-circle" size="16" stroke-width="3" />
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Jurusan --}}
                    <div class="md:col-span-2">
                        <x-forms.select 
                            name="jurusan_id" 
                            label="Jurusan / Program Keahlian" 
                            required 
                            x-model="form.jurusanId"
                            @change="loadKonsentrasi()"
                            :options="$jurusanList"
                            optionValue="id"
                            optionLabel="nama_jurusan"
                            placeholder="-- Pilih Jurusan --"
                        />
                    </div>
                    
                    {{-- Konsentrasi --}}
                    <div x-show="form.jurusanId" x-transition>
                        <div :class="{ 'opacity-75 pointer-events-none': loadingKonsentrasi }">
                             <label class="block text-sm font-semibold text-slate-700 mb-1">Konsentrasi Keahlian</label>
                             <select 
                                name="konsentrasi_id" 
                                class="form-select w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                x-model="form.konsentrasiId"
                                :disabled="!form.jurusanId || konsentrasiList.length === 0"
                            >
                                <option value="">-- Tidak Spesifik --</option>
                                <template x-for="k in konsentrasiList" :key="k.id">
                                    <option :value="k.id" x-text="k.nama_konsentrasi + (k.kode_konsentrasi ? ' (' + k.kode_konsentrasi + ')' : '')"></option>
                                </template>
                            </select>
                            <p class="text-[11px] text-slate-500 mt-1.5" x-show="form.jurusanId && konsentrasiList.length === 0 && !loadingKonsentrasi">
                                ℹ️ Tidak ada data konsentrasi khusus.
                            </p>
                            <p class="text-[11px] text-indigo-500 mt-1.5 flex items-center gap-1" x-show="loadingKonsentrasi">
                                <x-ui.icon name="loader" size="12" class="animate-spin" /> Memuat data...
                            </p>
                        </div>
                    </div>

                    {{-- Rombel --}}
                    <div>
                        <x-forms.select 
                            name="rombel" 
                            label="Nomor Rombel" 
                            x-model="form.rombel" 
                            help="Opsional, default: Tanpa Nomor."
                        >
                            <option value="none">Tanpa Nomor</option>
                            @for($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </x-forms.select>
                    </div>
                </div>

                {{-- Preview Box --}}
                <div x-show="generatedName" x-transition 
                     class="p-4 bg-gradient-to-br from-indigo-50 to-white rounded-xl border border-indigo-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0 shadow-sm border border-indigo-200">
                        <span class="text-lg font-bold">Aa</span>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-0.5">Preview Nama Kelas</p>
                        <h3 class="text-xl font-bold text-slate-800 tracking-tight" x-text="generatedName"></h3>
                    </div>
                </div>
            </div>
        </x-forms.section>

        {{-- SECTION 2: Wali Kelas --}}
        <x-forms.section 
            title="Wali Kelas" 
            variant="card"
            icon="user"
        >
            <x-slot name="description">Guru yang bertanggung jawab (opsional).</x-slot>
            
            <x-forms.select 
                name="wali_kelas_user_id" 
                label="Pilih Guru Wali Kelas" 
                :options="$waliList ?? []"
                optionValue="id"
                optionLabel="username"
                placeholder="-- Pilih Guru --"
                help="Dapat diatur belakangan."
            />
        </x-forms.section>

        {{-- ACTIONS --}}
        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('kelas.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition-all">
                Batal
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold shadow-sm flex items-center gap-2 transition-all active:scale-95 bg-indigo-600 hover:bg-indigo-700"
                :disabled="!isValid"
                :class="{'opacity-50 cursor-not-allowed': !isValid}">
                <x-ui.icon name="check" size="16" />
                Simpan Kelas
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
                                :class="form.tingkat ? 'border-emerald-500 text-emerald-500' : 'border-slate-300 text-transparent'">
                                <div class="w-1.5 h-1.5 rounded-full" :class="form.tingkat ? 'bg-emerald-500' : 'bg-slate-200'"></div>
                        </div>
                        <h5 class="text-sm font-semibold text-slate-800">Tingkat Kelas</h5>
                        <p class="text-[11px] text-slate-500 mt-0.5" x-text="form.tingkat ? 'Kelas ' + form.tingkat : 'Wajib dipilih.'"></p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="relative pl-6">
                        <div class="absolute left-0 top-0.5 w-4 h-4 rounded-full border flex items-center justify-center bg-white z-10"
                                :class="form.jurusanId ? 'border-emerald-500 text-emerald-500' : 'border-slate-300 text-transparent'">
                                <div class="w-1.5 h-1.5 rounded-full" :class="form.jurusanId ? 'bg-emerald-500' : 'bg-slate-200'"></div>
                        </div>
                        <h5 class="text-sm font-semibold text-slate-800">Jurusan</h5>
                        <p class="text-[11px] text-slate-500 mt-0.5" x-text="form.jurusanId ? 'Terpilih.' : 'Wajib dipilih.'"></p>
                    </div>
                    
                    {{-- Step 3 --}}
                    <div class="relative pl-6">
                         <div class="absolute left-0 top-0.5 w-4 h-4 rounded-full border flex items-center justify-center bg-white z-10"
                                :class="generatedName ? 'border-indigo-500 text-indigo-500' : 'border-slate-300 text-transparent'">
                                <x-ui.icon name="check" size="10" />
                        </div>
                        <h5 class="text-sm font-semibold text-slate-800">Nama Kelas</h5>
                        <p class="text-[11px] font-mono text-indigo-600 font-bold mt-0.5" x-text="generatedName || '-'"></p>
                    </div>
                </div>
            </div>

            <div class="bg-indigo-50 rounded-xl p-5 border border-indigo-100">
                <h4 class="text-xs font-bold text-indigo-800 uppercase tracking-widest mb-3 flex items-center gap-2">
                    <x-ui.icon name="info" size="14" /> Auto-Generate
                </h4>
                 <p class="text-xs text-indigo-700 leading-relaxed">
                    Sistem otomatis membuat nama kelas (misal: <strong>XI TKJ 1</strong>) berdasarkan konfigurasi. Jika nama tersebut sudah ada, sistem akan otomatis menggunakan nomor urut berikutnya (misal: <strong>XI TKJ 2</strong>).
                </p>
            </div>
        </div>
    </div>
</x-forms.card>

<script>
    function createKelasForm(jurusanMap) {
        return {
            form: {
                tingkat: '{{ old('tingkat') }}',
                jurusanId: '{{ old('jurusan_id') }}',
                konsentrasiId: '{{ old('konsentrasi_id') }}',
                rombel: '{{ old('rombel', 'none') }}',
            },
            
            jurusanMap: jurusanMap,
            konsentrasiList: [],
            loadingKonsentrasi: false,
            
            get isValid() {
                return this.form.tingkat && this.form.jurusanId;
            },
            
            get generatedName() {
                if (!this.form.tingkat || !this.form.jurusanId) return '';
                
                let kode = this.jurusanMap[this.form.jurusanId] || '';
                
                // Cek Konsentrasi
                if (this.form.konsentrasiId && this.konsentrasiList.length > 0) {
                     const k = this.konsentrasiList.find(item => item.id == this.form.konsentrasiId);
                     if (k) {
                          kode = k.kode_konsentrasi || k.nama_konsentrasi.substring(0,3).toUpperCase();
                     }
                }
                
                const rombelSuffix = (this.form.rombel && this.form.rombel !== 'none') ? (' ' + this.form.rombel) : '';
                return `${this.form.tingkat} ${kode}${rombelSuffix}`;
            },

            async loadKonsentrasi() {
                this.form.konsentrasiId = '';
                this.konsentrasiList = [];
                
                if (!this.form.jurusanId) return;
                
                this.loadingKonsentrasi = true;
                try {
                    const response = await fetch('{{ $konsentrasiApiUrl }}?jurusan_id=' + this.form.jurusanId);
                    this.konsentrasiList = await response.json();
                } catch (error) {
                    console.error('Failed to load konsentrasi:', error);
                } finally {
                    this.loadingKonsentrasi = false;
                }
            },
            
            submitForm(e) {
                if (this.isValid) {
                    e.target.submit();
                }
            },
            
            init() {
                if (this.form.jurusanId) this.loadKonsentrasi();
            }
        }
    }
</script>
@endsection
