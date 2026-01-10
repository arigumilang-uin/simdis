@extends('layouts.app')

@section('title', 'Tambah Siswa')

@section('page-header')
    <x-page-header 
        title="Tambah Siswa" 
        subtitle="Daftarkan siswa baru ke dalam database sekolah."
        :backUrl="route('siswa.index')"
    />
@endsection

@section('content')
<div class="form-page-container" x-data="createSiswaForm()">
    <form action="{{ route('siswa.store') }}" method="POST" class="form-layout-wrapper" @submit.prevent="submitForm">
        @csrf
        
        {{-- MAIN CONTENT --}}
        <div class="form-main-card">
            <div class="form-header">
                <h3 class="form-title">Data Diri Siswa</h3>
                <p class="form-subtitle">Masukan informasi identitas siswa dengan benar.</p>
            </div>
            
            <div class="form-body">
                {{-- SECTION 1: Identitas --}}
                {{-- Menggunakan style binding langsung agar pasti berubah warnanya --}}
                <div class="form-section transition-all duration-300 relative pl-6 py-2 mb-8"
                     :class="isValidSection1 ? 'bg-emerald-50/30' : ''">
                    
                    {{-- Dynamic Colored Line --}}
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 rounded-full transition-colors duration-300"
                         :class="isValidSection1 ? 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]' : 'bg-slate-200'">
                    </div>

                    <div class="form-section-title flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                                 :class="isValidSection1 ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-500'">
                                <x-ui.icon name="user" size="18" />
                            </div>
                            <span :class="isValidSection1 ? 'text-emerald-700' : 'text-slate-700'">Identitas Pribadi</span>
                        </div>
                        
                        <div x-show="isValidSection1" x-transition class="flex items-center gap-1 text-emerald-600 text-xs font-bold uppercase tracking-wider bg-emerald-100 px-2.5 py-1 rounded-full border border-emerald-200">
                            <x-ui.icon name="check" size="12" /> Lengkap
                        </div>
                    </div>
                
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        {{-- NISN with Live Validation --}}
                        <div class="form-group">
                            <label class="form-label form-label-required">NISN</label>
                            <div class="relative">
                                <input type="text" name="nisn" x-model="form.nisn" @input="checkNisn()" 
                                    class="form-input w-full pr-10 font-mono tracking-wide" 
                                    :class="{
                                        'border-red-300 focus:border-red-500 focus:ring-red-200': nisnStatus === 'invalid',
                                        'border-emerald-500 focus:border-emerald-500 focus:ring-emerald-200': nisnStatus === 'valid',
                                        'border-gray-300': nisnStatus === 'empty' || nisnStatus === 'checking'
                                    }"
                                    placeholder="10 digit angka" required maxlength="10">
                                
                                {{-- Status Icon --}}
                                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                    <div x-show="nisnStatus === 'checking'" class="animate-spin text-blue-500"><x-ui.icon name="loader" size="18"/></div>
                                    <div x-show="nisnStatus === 'valid'" class="text-emerald-500 drop-shadow-sm"><x-ui.icon name="check-circle" size="18"/></div>
                                    <div x-show="nisnStatus === 'invalid'" class="text-red-500 drop-shadow-sm"><x-ui.icon name="alert-circle" size="18"/></div>
                                </div>
                            </div>
                            
                            {{-- Live Feedback Message --}}
                            <div class="mt-2 text-xs font-medium transition-all min-h-[1.25rem]">
                                <p x-show="nisnStatus === 'empty'" class="text-slate-400">Wajib 10 digit angka.</p>
                                <p x-show="nisnStatus === 'checking'" class="text-blue-500 flex items-center gap-1">
                                    Memeriksa availability...
                                </p>
                                <p x-show="nisnStatus === 'valid'" class="text-emerald-600 flex items-center gap-1">
                                    <x-ui.icon name="check" size="12"/> NISN tersedia.
                                </p>
                                <p x-show="nisnStatus === 'invalid'" class="text-red-600 flex flex-wrap items-center gap-1">
                                    <span x-text="nisnMessage"></span> 
                                    <span x-show="nisnOwner" class="font-bold text-red-800 bg-red-100 px-1.5 py-0.5 rounded text-[10px] uppercase tracking-wide border border-red-200" x-text="'Milik: ' + nisnOwner"></span>
                                </p>
                            </div>
                        </div>

                        {{-- Nama Siswa --}}
                        <div class="form-group">
                            <label class="form-label form-label-required">Nama Lengkap</label>
                            <input type="text" name="nama_siswa" x-model="form.nama" 
                                class="form-input w-full" placeholder="Sesuai ijazah/rapor" required>
                        </div>
                    </div>

                    <div class="form-group mt-2">
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

                {{-- SECTION 2: Wali Murid --}}
                <div class="form-section relative pl-6 py-2 transition-all duration-300"
                    :class="isValidSection2 ? 'bg-blue-50/20' : ''">
                    
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 rounded-full bg-slate-200 transition-colors duration-300"
                        :class="isValidSection2 ? 'bg-blue-400' : 'bg-slate-200'">
                    </div>

                    <div class="form-section-title">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors"
                             :class="isValidSection2 ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-500'">
                            <x-ui.icon name="users" size="18" />
                        </div>
                        <span :class="isValidSection2 ? 'text-blue-700' : 'text-slate-700'">Data Wali Murid</span>
                    </div>
                    
                    <div class="mt-4">
                        <!-- Hidden Input untuk Existing Wali ID (Auto-Linked) -->
                        <input type="hidden" name="wali_murid_user_id" x-model="form.waliId">

                        <div class="mb-4">
                            <x-forms.input 
                                name="nomor_hp_wali_murid" 
                                label="Nomor WhatsApp Wali" 
                                placeholder="08xxxxxxxxxx" 
                                help="Sistem akan otomatis mengecek akun Wali Murid."
                                x-model="form.phone"
                                @input="checkWali()"
                            />
                            
                            <!-- CHECKING STATUS -->
                            <div x-show="waliStatus === 'checking'" class="mt-2 text-sm text-slate-500 flex items-center gap-2">
                                <x-ui.icon name="loader-2" class="animate-spin" size="14"/> Memeriksa ketersediaan...
                            </div>
                            
                            <!-- FOUND: LINK OTOMATIS -->
                            <div x-show="waliStatus === 'found' && waliData" x-transition class="mt-3 bg-emerald-50 border border-emerald-100 rounded-lg p-3">
                                <div class="flex items-start gap-3">
                                    <div class="bg-emerald-100 text-emerald-600 p-2 rounded-full">
                                        <x-ui.icon name="link" size="16"/>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-emerald-800 text-sm">Akun Wali Ditemukan</h4>
                                        <p class="text-xs text-emerald-700 mt-1">
                                            Nomor ini terdaftar atas nama <strong x-text="waliData?.nama"></strong>.
                                        </p>
                                        <p class="text-[10px] text-emerald-600 mt-1 italic font-medium">
                                            Siswa ini akan otomatis dihubungkan ke akun tersebut.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Create New Wali Option (Hanya muncul jika BELUM ada) --}}
                        <div x-show="waliStatus === 'available'" x-transition class="bg-blue-50/50 rounded-xl p-4 border border-blue-100 group hover:border-blue-300 transition-all cursor-pointer"
                             @click="form.createWali = !form.createWali">
                            <div class="flex items-start gap-3">
                                <div class="relative flex items-center mt-1">
                                    <input type="checkbox" name="create_wali" value="1" x-model="form.createWali" 
                                        class="w-5 h-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500 cursor-pointer pointer-events-none">
                                </div>
                                <div class="flex-1">
                                    <span class="block font-semibold text-sm text-gray-900 group-hover:text-blue-700 transition-colors">Buat akun Wali Murid baru</span>
                                    <span class="block text-xs text-slate-500 mt-1">Nomor ini belum terdaftar. Centang untuk membuat akun login.</span>
                                </div>
                            </div>

                            <div x-show="form.createWali" x-transition.origin.top class="mt-4 ml-8">
                                <div class="flex gap-3 bg-white p-3 rounded-lg border border-blue-100 shadow-sm text-xs">
                                    <div class="bg-blue-100 p-2 rounded text-blue-600 h-fit">
                                        <x-ui.icon name="key" size="16" />
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-bold text-blue-800 uppercase tracking-wide mb-2 text-[10px]">Preview Kredensial</h4>
                                        <div class="grid grid-cols-1 gap-1 font-mono text-slate-600">
                                            <div class="flex justify-between border-b border-dashed border-slate-100 pb-1">
                                                <span>Username:</span> 
                                                <span class="font-bold text-slate-800">wali.<span x-text="form.nisn || '...'"></span></span>
                                            </div>
                                            <div class="flex justify-between pt-1">
                                                <span>Password:</span> 
                                                <span class="font-bold text-slate-800">smkn1.walimurid.<span x-text="getShortPhone()"></span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FOOTER ACTIONS --}}
            <div class="form-actions-footer">
                <a href="{{ route('siswa.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary px-6" 
                    :disabled="!isValidSection1 || nisnStatus === 'checking'"
                    :class="{'opacity-50 cursor-not-allowed': !isValidSection1 || nisnStatus === 'checking'}">
                    <x-ui.icon name="check" size="18" class="mr-2" />
                    Simpan Data Siswa
                </button>
            </div>
        </div>
        
        {{-- SIDEBAR TIPS --}}
        <div class="form-sidebar sticky top-4">
            <div class="sidebar-card">
                <div class="sidebar-header bg-gradient-to-r from-slate-50 to-white">
                    <x-ui.icon name="activity" size="16" class="text-amber-500" />
                    <span class="sidebar-title">Status Kelengkapan</span>
                </div>
                <div class="sidebar-body space-y-6">
                    {{-- Progress Indicator --}}
                    <div class="relative pl-4 border-l-2 border-slate-100 space-y-8">
                        
                        {{-- Step 1 --}}
                        <div class="relative transition-all duration-300" :class="isValidSection1 ? 'opacity-100' : 'opacity-100'">
                            <span class="absolute -left-[21px] top-0 w-4 h-4 rounded-full border-2 transition-all duration-300 z-10 box-content bg-white"
                                  :class="isValidSection1 ? 'border-emerald-500 bg-emerald-500' : 'border-slate-300'">
                                <x-ui.icon name="check" size="10" class="text-white absolute top-[3px] left-[3px]" x-show="isValidSection1" />
                            </span>
                            <p class="text-[10px] font-bold uppercase tracking-wider mb-1" 
                               :class="isValidSection1 ? 'text-emerald-600' : 'text-slate-400'">Wajib</p>
                            <p class="text-sm font-semibold text-slate-700">Identitas Siswa</p>
                            
                            {{-- Mini checklist --}}
                            <div class="mt-2 space-y-1">
                                <div class="flex items-center gap-2 text-xs" :class="nisnStatus === 'valid' ? 'text-emerald-600' : 'text-slate-400'">
                                    <div class="w-1.5 h-1.5 rounded-full" :class="nisnStatus === 'valid' ? 'bg-emerald-500' : 'bg-slate-300'"></div>
                                    NISN (10 Digit & Unik)
                                </div>
                                <div class="flex items-center gap-2 text-xs" :class="form.nama.length > 2 ? 'text-emerald-600' : 'text-slate-400'">
                                    <div class="w-1.5 h-1.5 rounded-full" :class="form.nama.length > 2 ? 'bg-emerald-500' : 'bg-slate-300'"></div>
                                    Nama Lengkap
                                </div>
                                <div class="flex items-center gap-2 text-xs" :class="form.kelasId ? 'text-emerald-600' : 'text-slate-400'">
                                    <div class="w-1.5 h-1.5 rounded-full" :class="form.kelasId ? 'bg-emerald-500' : 'bg-slate-300'"></div>
                                    Pilih Kelas
                                </div>
                            </div>
                        </div>
                        
                        {{-- Step 2 --}}
                        <div class="relative">
                            <span class="absolute -left-[21px] top-0 w-4 h-4 rounded-full border-2 border-slate-300 bg-white z-10 box-content"
                                  :class="isValidSection2 ? 'border-blue-400 bg-blue-400' : 'border-slate-300'">
                            </span>
                             <p class="text-[10px] font-bold uppercase tracking-wider mb-1 text-slate-400">Opsional</p>
                            <p class="text-sm font-semibold text-slate-700">Wali Murid</p>
                            
                            {{-- Wali Status Info --}}
                            <div class="mt-1" x-show="form.phone.length > 2" x-transition>
                                <template x-if="waliStatus === 'found'">
                                    <div class="text-[10px] text-emerald-600 flex items-center gap-1 font-medium bg-emerald-50 px-1.5 py-0.5 rounded w-fit border border-emerald-100">
                                        <x-ui.icon name="link" size="10" /> <span x-text="'Wali: ' + (waliData?.nama?.split(' ')[2] || waliData?.nama?.split(' ')[0] || '...')"></span>
                                    </div>
                                </template>
                                <template x-if="form.createWali">
                                    <div class="text-[10px] text-blue-600 flex items-center gap-1 font-medium bg-blue-50 px-1.5 py-0.5 rounded w-fit border border-blue-100">
                                        <x-ui.icon name="plus" size="10" /> Buat Akun Baru
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    
                    <div x-show="isValidSection1" x-transition class="p-3 bg-emerald-50 rounded-lg border border-emerald-100 text-center">
                        <p class="text-xs text-emerald-700 font-medium">Data wajib sudah lengkap! 🎉</p>
                        <p class="text-[10px] text-emerald-600 mt-1">Silakan klik simpan</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Alpine.js Logic Script --}}
<script>
    function createSiswaForm() {
        return {
            form: {
                nisn: '{{ old('nisn') }}',
                nama: '{{ old('nama_siswa') }}',
                kelasId: '{{ old('kelas_id') }}',
                phone: '{{ old('nomor_hp_wali_murid') }}',
                createWali: false,
                waliId: null, // Hidden input for linking existing wali
            },
            
            // Stats
            nisnStatus: 'empty', // empty, checking, valid, invalid
            nisnMessage: '',
            nisnOwner: '',
            debounceTimer: null,
            
            // Wali Stats
            waliStatus: 'empty', // empty, checking, found, available, invalid
            waliData: null,
            waliTimer: null,
            
            // Computed Validation
            get isValidSection1() {
                return this.nisnStatus === 'valid' && 
                       this.form.nama.length > 2 && 
                       this.form.kelasId !== '';
            },
            
            get isValidSection2() {
                // Section 2 considered "filled/active" if phone or createWali is set
                return this.form.phone.length > 5 || this.form.createWali;
            },
            
            // Helpers
            getShortPhone() {
                return this.form.phone ? this.form.phone.replace(/\D/g,'') : '...';
            },

            // Action: Check NISN
            checkNisn() {
                const nisn = this.form.nisn;
                
                // RESET: Hapus nama pemilik sebelumnya setiap kali input berubah
                // Ini mencegah nama siswa lain muncul saat validasi format (misal digit kurang)
                this.nisnOwner = '';
                
                // Reset if empty
                if (!nisn) {
                    this.nisnStatus = 'empty';
                    return;
                }
                
                // Allow only numbers
                if (/[^0-9]/.test(nisn)) {
                    this.nisnStatus = 'invalid';
                    this.nisnMessage = 'Hanya boleh angka.';
                    return;
                }
                
                // Strict 10 digit check for local validation
                if (nisn.length !== 10) {
                    this.nisnStatus = 'invalid';
                    this.nisnMessage = 'Wajib 10 digit. Saat ini: ' + nisn.length;
                    return;
                }
                
                this.nisnStatus = 'checking';
                
                // Debounce Request
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(async () => {
                    try {
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
                        console.error('Error checking NISN:', error);
                        this.nisnStatus = 'invalid';
                        this.nisnMessage = 'Gagal terhubung ke server.';
                    }
                }, 500); // Wait 500ms after typing stops
            },

            // Action: Check Wali HP
            checkWali() {
                // Sanitize input: only numbers
                this.form.phone = this.form.phone.replace(/[^0-9]/g, '');
                
                const hp = this.form.phone;
                
                // Reset states
                this.waliStatus = hp && hp.length > 5 ? 'checking' : 'empty';
                this.waliData = null;
                this.form.waliId = null;
                this.form.createWali = false; // Reset create option

                if (!hp || hp.length < 5) return;
                
                // Debounce
                clearTimeout(this.waliTimer);
                this.waliTimer = setTimeout(async () => {
                    try {
                        const response = await fetch(`{{ route('siswa.check-wali-hp') }}?phone=${hp}`);
                        const data = await response.json();
                        
                        if (data.status === 'found') {
                            this.waliStatus = 'found';
                            this.waliData = data.wali;
                            this.form.waliId = data.wali.id; // Auto link
                            this.form.createWali = false;
                        } else if (data.status === 'available') {
                            this.waliStatus = 'available';
                            this.form.waliId = null;
                            // Optional: auto-check createWali if desired, but user said "opsi ada"
                            this.form.createWali = true; 
                        } else {
                            this.waliStatus = 'invalid';
                        }
                    } catch (error) {
                        console.error('Error checking Wali HP:', error);
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
