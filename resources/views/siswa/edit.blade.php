@extends('layouts.app')

@section('title', 'Edit Siswa')

@section('page-header')
    <x-page-header 
        title="Edit Siswa" 
        subtitle="Perbarui informasi data siswa."
        :backUrl="route('siswa.index')"
    />
@endsection

@section('content')
<x-forms.card 
    action="{{ route('siswa.update', $siswa->id) }}" 
    maxWidth="full" 
    layout="sidebar"
    method="PUT" {{-- Method PUT for Update --}}
    x-data="editSiswaForm()"
    @submit.prevent="submitForm"
>
    {{-- LEFT COLUMN (Main Content) --}}
    <div class="lg:col-span-8 space-y-6">
        
        {{-- SECTION 1: Identitas --}}
        <x-forms.section 
            title="Identitas Pribadi" 
            variant="card"
            icon="user"
        >
            <x-slot name="description">Informasi dasar siswa.</x-slot>
            <x-slot name="actions">
                <div x-show="isValidSection1" x-transition 
                        class="flex items-center gap-1.5 text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-100">
                    <x-ui.icon name="check" size="14" stroke-width="3" />
                    <span class="text-[11px] font-bold uppercase tracking-wider">Lengkap</span>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- NISN Wrapper --}}
                <div>
                    <x-forms.input 
                        name="nisn" 
                        label="NISN" 
                        required 
                        placeholder="10 digit angka" 
                        maxlength="10"
                        x-model="form.nisn" 
                        @input="checkNisn()"
                        help=""
                    />

                    {{-- Feedback Message --}}
                    <div class="mt-1 ml-1 text-xs font-medium transition-all min-h-[1.25rem]">
                        <p x-show="nisnStatus === 'empty'" class="text-slate-400">Wajib 10 digit angka unik.</p>
                        <p x-show="nisnStatus === 'checking'" class="text-blue-500 flex items-center gap-1">
                            <x-ui.icon name="loader" size="12" class="animate-spin" /> Memeriksa...
                        </p>
                        <p x-show="nisnStatus === 'valid'" class="text-emerald-600 flex items-center gap-1">
                            <x-ui.icon name="check" size="12"/> Tersedia.
                        </p>
                        {{-- Handle current NISN as valid too --}}
                        <p x-show="nisnStatus === 'current'" class="text-indigo-600 flex items-center gap-1">
                             <x-ui.icon name="check-circle" size="12"/> NISN Saat Ini (Valid).
                        </p>
                        <p x-show="nisnStatus === 'invalid'" class="text-red-600 flex flex-wrap items-center gap-1">
                            <span x-text="nisnMessage"></span> 
                            <span x-show="nisnOwner" class="font-bold text-red-800 bg-red-100 px-1 rounded text-[10px]" x-text="'Milik: ' + nisnOwner"></span>
                        </p>
                    </div>
                </div>
                
                <x-forms.input 
                    name="nama_siswa" 
                    label="Nama Lengkap" 
                    required 
                    placeholder="Sesuai ijazah/rapor" 
                    x-model="form.nama"
                />

                <div class="col-span-1 md:col-span-2">
                    <x-forms.select 
                        name="kelas_id" 
                        label="Kelas Saat Ini" 
                        required
                        x-model="form.kelasId"
                        :options="$kelas"
                        optionValue="id"
                        optionLabel="nama_kelas"
                        placeholder="-- Pilih Kelas --"
                    />
                </div>
            </div>
        </x-forms.section>

        {{-- SECTION 2: Wali Murid --}}
        <x-forms.section 
            title="Data Wali Murid" 
            variant="card"
            icon="users"
        >
             <x-slot name="description">Hubungkan dengan akun wali murid.</x-slot>

            <div class="space-y-4">
                <input type="hidden" name="wali_murid_user_id" x-model="form.waliId">
                
                <x-forms.input 
                    name="nomor_hp_wali_murid" 
                    label="Nomor WhatsApp Wali" 
                    placeholder="08xxxxxxxxxx" 
                    x-model="form.phone"
                    @input="checkWali()"
                />
                
                {{-- Statuses --}}
                <div x-show="waliStatus === 'checking'" class="text-xs text-indigo-500 flex items-center gap-2">
                    <x-ui.icon name="loader-2" class="animate-spin" size="14"/> Memeriksa...
                </div>
                
                <div x-show="waliStatus === 'found' && waliData" x-transition 
                        class="p-4 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-emerald-600 shrink-0 border border-emerald-100 shadow-sm">
                        <x-ui.icon name="link" size="14" />
                    </div>
                    <div>
                        <p class="text-xs text-emerald-900 font-medium">Akun Ditemukan: <span class="font-bold" x-text="waliData?.nama"></span></p>
                        <p class="text-[10px] text-emerald-600">Terhubung.</p>
                    </div>
                </div>

                 {{-- Current Connected Wali Info (Initial State) --}}
                 <div x-show="waliStatus === 'initial_linked' && initialWaliName" class="p-4 rounded-lg bg-blue-50 border border-blue-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-blue-600 shrink-0 border border-blue-100 shadow-sm">
                        <x-ui.icon name="link" size="14" />
                    </div>
                    <div>
                        <p class="text-xs text-blue-900 font-medium">Terhubung dengan: <span class="font-bold" x-text="initialWaliName"></span></p>
                        <p class="text-[10px] text-blue-600">Akun Wali saat ini.</p>
                    </div>
                 </div>

                <div x-show="waliStatus === 'available'" x-transition 
                        class="p-4 rounded-lg border border-dashed border-slate-300 bg-slate-50/50 hover:bg-white hover:border-indigo-300 transition-all cursor-pointer"
                        @click="form.createWali = !form.createWali">
                    <div class="flex gap-3">
                        <div class="pt-0.5">
                            <input type="checkbox" name="create_wali" value="1" x-model="form.createWali"
                                class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 pointer-events-none">
                        </div>
                        <div class="flex-1">
                            <span class="block text-sm font-semibold text-slate-700">Buat Akun Baru</span>
                            <p class="text-xs text-slate-500 mt-0.5">Centang untuk membuat akun login wali murid.</p>
                            <div x-show="form.createWali" x-transition class="mt-2 text-xs bg-white p-2 rounded border border-slate-100 text-slate-600 font-mono">
                                <div>User: wali.<span x-text="form.nisn || '...'"></span></div>
                                <div>Pass: <span x-text="getShortPhone()"></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-forms.section>

        {{-- ACTIONS --}}
        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('siswa.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition-all">
                Batal
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold shadow-sm flex items-center gap-2 transition-all active:scale-95 bg-indigo-600 hover:bg-indigo-700" 
                :disabled="!isValidSection1 || nisnStatus === 'checking'"
                :class="{'opacity-50 cursor-not-allowed': !isValidSection1 || nisnStatus === 'checking'}">
                <x-ui.icon name="check" size="16" />
                Simpan Perubahan
            </button>
        </div>
    </div>

    {{-- RIGHT COLUMN (Sidebar) --}}
    <div class="hidden lg:block lg:col-span-4 pl-2">
        <div class="sticky top-8 space-y-4">
             <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">
                    Status
                </h4>
                <div class="space-y-4 relative">
                     <div class="absolute left-[7px] top-2 bottom-2 w-[1px] bg-slate-100"></div>
                     
                    {{-- Step 1 --}}
                    <div class="relative pl-6">
                        <div class="absolute left-0 top-0.5 w-4 h-4 rounded-full border flex items-center justify-center bg-white z-10"
                                :class="isValidSection1 ? 'border-emerald-500 text-emerald-500' : 'border-slate-300 text-transparent'">
                                <div class="w-1.5 h-1.5 rounded-full" :class="isValidSection1 ? 'bg-emerald-500' : 'bg-slate-200'"></div>
                        </div>
                        <h5 class="text-sm font-semibold text-slate-800">Identitas Siswa</h5>
                        <div class="mt-1 space-y-1">
                            <div class="flex items-center gap-2 text-[11px]" :class="(nisnStatus === 'valid' || nisnStatus === 'current') ? 'text-emerald-600' : 'text-slate-400'">
                                <x-ui.icon name="check" size="10" /> <span>NISN Valid</span>
                            </div>
                            <div class="flex items-center gap-2 text-[11px]" :class="form.nama.length > 2 ? 'text-emerald-600' : 'text-slate-400'">
                                    <x-ui.icon name="check" size="10" /> <span>Nama Lengkap</span>
                            </div>
                        </div>
                    </div>
                
                    {{-- Step 2 --}}
                    <div class="relative pl-6">
                        <div class="absolute left-0 top-0.5 w-4 h-4 rounded-full border border-slate-300 bg-white z-10 flex items-center justify-center">
                            <div class="w-1.5 h-1.5 rounded-full" :class="isValidSection2 ? 'bg-indigo-500' : 'bg-white'"></div>
                        </div>
                        <h5 class="text-sm font-semibold text-slate-800">Wali Murid</h5>
                        <p class="text-[11px] text-slate-400 mt-0.5">Linked/Update.</p>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                <h5 class="font-bold text-slate-700 text-xs mb-1 flex items-center gap-1.5">
                    <x-ui.icon name="info" size="12" /> Info Edit
                </h5>
                <p class="text-[11px] text-slate-500 leading-relaxed">
                    Perubahan NISN akan divalidasi ulang. Jika Wali Murid diubah, sistem akan mencari akun baru berdasarkan nomor HP.
                </p>
            </div>
        </div>
    </div>
</x-forms.card>

{{-- Alpine.js Logic Script --}}
<script>
    function editSiswaForm() {
        return {
            form: {
                nisn: '{{ old('nisn', $siswa->nisn) }}',
                nama: '{{ old('nama_siswa', $siswa->nama_siswa) }}',
                kelasId: '{{ old('kelas_id', $siswa->kelas_id) }}',
                phone: '{{ old('nomor_hp_wali_murid', $siswa->nomor_hp_wali_murid) }}',
                createWali: false,
                waliId: '{{ old('wali_murid_user_id', $siswa->wali_murid_user_id) }}',
            },
            
            // Initial Data
            initialNisn: '{{ $siswa->nisn }}',
            initialWaliName: '{{ $siswa->waliMurid ? $siswa->waliMurid->nama : "" }}',

            // Stats
            nisnStatus: 'current', // empty, checking, valid, invalid, current
            nisnMessage: '',
            nisnOwner: '',
            debounceTimer: null,
            
            // Wali Stats
            waliStatus: '{{ $siswa->wali_murid_user_id ? "initial_linked" : "empty" }}', 
            waliData: null,
            waliTimer: null,
            
            // Computed Validation
            get isValidSection1() {
                return (this.nisnStatus === 'valid' || this.nisnStatus === 'current') && 
                       this.form.nama.length > 2 && 
                       this.form.kelasId !== '';
            },
            
            get isValidSection2() {
                 return this.form.phone.length > 5 || this.form.createWali;
            },
            
            // Helpers
            getShortPhone() {
                return this.form.phone ? this.form.phone.replace(/\D/g,'') : '...';
            },

            // Action: Check NISN
            checkNisn() {
                const nisn = this.form.nisn;
                this.nisnOwner = '';
                
                 if (!nisn) {
                    this.nisnStatus = 'empty';
                    return;
                }
                
                // If matches initial, it's valid (current)
                if (nisn === this.initialNisn) {
                    this.nisnStatus = 'current';
                    return;
                }

                if (/[^0-9]/.test(nisn)) {
                    this.nisnStatus = 'invalid';
                    this.nisnMessage = 'Hanya boleh angka.';
                    return;
                }
                
                if (nisn.length !== 10) {
                    this.nisnStatus = 'invalid';
                    this.nisnMessage = 'Wajib 10 digit. Saat ini: ' + nisn.length;
                    return;
                }
                
                this.nisnStatus = 'checking';
                
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(async () => {
                    try {
                        // We check availability generally. If taken, it's invalid (unless it's self, which is handled by initialNisn check above)
                        const response = await fetch(`{{ route('siswa.check-nisn') }}?nisn=${nisn}`);
                        const data = await response.json();
                        
                        if (data.available) {
                            this.nisnStatus = 'valid';
                            this.nisnMessage = data.message;
                        } else {
                            this.nisnStatus = 'invalid';
                            this.nisnMessage = data.message;
                            this.nisnOwner = data.owner || '';
                        }
                    } catch (error) {
                        this.nisnStatus = 'invalid';
                        this.nisnMessage = 'Gagal terhubung ke server.';
                    }
                }, 500); 
            },

            // Action: Check Wali HP
            checkWali() {
                this.form.phone = this.form.phone.replace(/[^0-9]/g, '');
                const hp = this.form.phone;
                
                this.waliStatus = hp && hp.length > 5 ? 'checking' : 'empty';
                this.waliData = null;
                // Don't reset waliId immediately if it was initial, wait for result or change
                // But if typing new number, reset ID.
                this.form.createWali = false; 

                if (!hp || hp.length < 5) return;
                
                clearTimeout(this.waliTimer);
                this.waliTimer = setTimeout(async () => {
                    try {
                        const response = await fetch(`{{ route('siswa.check-wali-hp') }}?phone=${hp}`);
                        const data = await response.json();
                        
                        if (data.status === 'found') {
                            this.waliStatus = 'found';
                            this.waliData = data.wali;
                            this.form.waliId = data.wali.id; 
                            this.form.createWali = false;
                        } else if (data.status === 'available') {
                            this.waliStatus = 'available';
                            this.form.waliId = null; 
                            this.form.createWali = true; 
                        } else {
                            this.waliStatus = 'invalid';
                        }
                    } catch (error) {
                        this.waliStatus = 'invalid';
                    }
                }, 500); 
            },
            
            submitForm(e) {
                if (this.isValidSection1) {
                    e.target.submit();
                }
            }
        }
    }
</script>
@endsection
