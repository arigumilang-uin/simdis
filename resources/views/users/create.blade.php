@extends('layouts.app')

@section('title', 'Tambah Pengguna')

@section('page-header')
    <x-page-header 
        title="Tambah Pengguna Baru" 
        subtitle="Buat akun untuk Guru, Staff, atau Administrator."
        :backUrl="route('users.index')"
    />
@endsection

@section('content')
@php
    $roleMap = [];
    foreach($roles ?? [] as $role) {
        $roleMap[$role->id] = strtolower($role->nama_role);
    }
@endphp

<x-forms.card 
    action="{{ route('users.store') }}" 
    maxWidth="full" 
    layout="sidebar"
    x-data="createUserForm({{ Js::from($roleMap) }})"
    @submit.prevent="submitForm"
>
    {{-- LEFT COLUMN (Main Content) --}}
    <div class="lg:col-span-8 space-y-6">

        {{-- SECTION 1: Kredensial Login --}}
        <x-forms.section 
            title="Kredensial Login" 
            variant="card"
            icon="lock"
        >
            <x-slot name="description">Informasi login wajib diisi.</x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <x-forms.input 
                    name="username" 
                    label="Nama Lengkap" 
                    placeholder="Ari Gumilang, S.T., M.Kom"
                    required 
                    x-model="form.username"
                    help="Nama asli pengguna beserta gelar." 
                />
                
                <x-forms.input 
                    type="email" 
                    name="email" 
                    label="Email Address" 
                    placeholder="nama@sekolah.sch.id"
                    x-model="form.email"
                    help="Opsional, untuk reset password."
                />
                
                {{-- Password Fields --}}
                <x-forms.password 
                    name="password" 
                    label="Password" 
                    required 
                    autocomplete="new-password"
                    x-model="form.password"
                />
                
                <div>
                   <x-forms.password 
                        name="password_confirmation" 
                        label="Ulangi Password" 
                        required 
                        autocomplete="new-password"
                        x-model="form.passwordConfirm"
                        @input="checkPasswordMatch()"
                    />
                    {{-- Match Indicator --}}
                    <div x-show="form.passwordConfirm.length > 0" class="mt-1 text-xs font-medium transition-all"
                         :class="passwordMatch ? 'text-emerald-600' : 'text-red-500'">
                        <span x-text="passwordMatch ? 'Password cocok.' : 'Password tidak cocok.'"></span>
                    </div>
                </div>
            </div>
        </x-forms.section>
        
        {{-- SECTION 2: Hak Akses & Role --}}
        <x-forms.section 
            title="Hak Akses & Detail Profil" 
            variant="card"
            icon="shield"
        >
            <x-slot name="description">Tentukan role dan data spesifik pengguna.</x-slot>

            <div class="space-y-5">
                <x-forms.select 
                    name="role_id" 
                    label="Role Pengguna" 
                    required 
                    x-model="roleId"
                    :options="$roles"
                    optionValue="id"
                    optionLabel="nama_role"
                    placeholder="-- Pilih Jenis Akun --"
                />

                {{-- CONDITIONAL: Data Kepegawaian (Guru & Struktural) --}}
                <div x-show="needsNipNuptk()" x-transition class="pt-2 border-t border-slate-100 mt-2">
                    <h4 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <span>Identitas Kepegawaian</span>
                        <div class="h-px flex-1 bg-slate-100"></div>
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        {{-- NIP --}}
                        <div>
                            <x-forms.input 
                                name="nip" 
                                label="NIP" 
                                placeholder="Nomor Induk Pegawai" 
                                help="Isi jika PNS (18 digit)" 
                                x-model="form.nip"
                                @input="checkIdentity('nip')"
                                maxlength="18"
                            />
                            <div class="mt-1 ml-1 text-xs font-medium min-h-[1.25rem]">
                                <p x-show="status.nip === 'checking'" class="text-blue-500 flex items-center gap-1"><x-ui.icon name="loader" size="12" class="animate-spin" /> Cek...</p>
                                <p x-show="status.nip === 'valid'" class="text-emerald-600 flex items-center gap-1"><x-ui.icon name="check" size="12"/> Tersedia</p>
                                <p x-show="status.nip === 'invalid'" class="text-red-600 flex items-center gap-1"><span x-text="messages.nip"></span></p>
                            </div>
                        </div>

                        {{-- NI PPPK --}}
                        <div>
                            <x-forms.input 
                                name="ni_pppk" 
                                label="NI PPPK" 
                                placeholder="Nomor Induk PPPK" 
                                help="Isi jika PPPK (18 digit)" 
                                x-model="form.ni_pppk"
                                @input="checkIdentity('ni_pppk')"
                                maxlength="18"
                            />
                             <div class="mt-1 ml-1 text-xs font-medium min-h-[1.25rem]">
                                <p x-show="status.ni_pppk === 'checking'" class="text-blue-500 flex items-center gap-1"><x-ui.icon name="loader" size="12" class="animate-spin" /> Cek...</p>
                                <p x-show="status.ni_pppk === 'valid'" class="text-emerald-600 flex items-center gap-1"><x-ui.icon name="check" size="12"/> Tersedia</p>
                                <p x-show="status.ni_pppk === 'invalid'" class="text-red-600 flex items-center gap-1"><span x-text="messages.ni_pppk"></span></p>
                            </div>
                        </div>

                        {{-- NUPTK --}}
                        <div>
                            <x-forms.input 
                                name="nuptk" 
                                label="NUPTK" 
                                placeholder="Nomor Unik Pendidik" 
                                help="Isi jika Non-ASN (16 digit)" 
                                x-model="form.nuptk"
                                @input="checkIdentity('nuptk')"
                                maxlength="16"
                            />
                             <div class="mt-1 ml-1 text-xs font-medium min-h-[1.25rem]">
                                <p x-show="status.nuptk === 'checking'" class="text-blue-500 flex items-center gap-1"><x-ui.icon name="loader" size="12" class="animate-spin" /> Cek...</p>
                                <p x-show="status.nuptk === 'valid'" class="text-emerald-600 flex items-center gap-1"><x-ui.icon name="check" size="12"/> Tersedia</p>
                                <p x-show="status.nuptk === 'invalid'" class="text-red-600 flex items-center gap-1"><span x-text="messages.nuptk"></span></p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CONDITIONAL: Wali Kelas --}}
                <div x-show="isWaliKelas()" x-transition class="pt-2 border-t border-slate-100 mt-2">
                    <h4 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <span>Tugas Tambahan: Wali Kelas</span>
                        <div class="h-px flex-1 bg-slate-100"></div>
                    </h4>
                    <x-forms.select 
                        name="kelas_id" 
                        label="Wali Kelas Untuk" 
                        :options="$kelas"
                        optionValue="id"
                        optionLabel="nama_kelas"
                        placeholder="-- Pilih Kelas --"
                    />
                </div>
                
                {{-- CONDITIONAL: Kaprodi --}}
                <div x-show="isKaprodi()" x-transition class="pt-2 border-t border-slate-100 mt-2">
                    <h4 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <span>Tugas Tambahan: Kaprodi</span>
                        <div class="h-px flex-1 bg-slate-100"></div>
                    </h4>
                    <x-forms.select 
                        name="jurusan_id" 
                        label="Ketua Program Studi" 
                        :options="$jurusan"
                        optionValue="id"
                        optionLabel="nama_jurusan"
                        placeholder="-- Pilih Jurusan --"
                    />
                </div>

                {{-- CONDITIONAL: Wali Murid --}}
                <div x-show="isWaliMurid()" x-transition class="pt-2 border-t border-slate-100 mt-2">
                     <h4 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <span>Hubungkan Siswa</span>
                        <div class="h-px flex-1 bg-slate-100"></div>
                    </h4>
                    <div class="max-h-56 overflow-y-auto border border-slate-200 rounded-lg p-1 bg-slate-50 shadow-inner">
                        @forelse($siswa ?? [] as $s)
                            <label class="flex items-center gap-3 p-2.5 hover:bg-white rounded-md cursor-pointer transition-all border-b border-transparent hover:border-slate-100 hover:shadow-sm">
                                <input type="checkbox" name="siswa_ids[]" value="{{ $s->id }}" class="rounded text-indigo-600 focus:ring-indigo-500 w-4 h-4 bg-white">
                                <span class="text-sm text-slate-700 font-medium">{{ $s->nama_siswa }} <span class="text-xs text-slate-400 font-normal">({{ $s->kelas->nama_kelas ?? '-' }})</span></span>
                            </label>
                        @empty
                            <p class="text-center text-sm text-slate-400 py-4">Tidak ada data siswa</p>
                        @endforelse
                    </div>
                </div>

                {{-- Common Additional Info --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2 border-t border-slate-100 mt-2">
                    <x-forms.input name="phone" label="Nomor Telepon" placeholder="Contoh: 08123456789" />
                    
                    <div class="flex items-center h-full pt-6">
                         <label class="flex items-center gap-3 cursor-pointer select-none group">
                            <input type="checkbox" name="is_active" value="1" checked class="w-5 h-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 transition-all">
                            <div>
                                <span class="block text-sm font-bold text-slate-700 group-hover:text-indigo-700 transition-colors">Aktifkan Akun</span>
                                <span class="text-xs text-slate-500">Pengguna bisa langsung login.</span>
                            </div>
                         </label>
                    </div>
                </div>
            </div>
        </x-forms.section>

        {{-- ACTIONS --}}
        <div class="flex justify-end gap-3 pt-2">
             <a href="{{ route('users.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition-all">
                Batal
            </a>
            <button type="submit" class="px-5 py-2.5 rounded-lg text-white text-sm font-semibold shadow-sm flex items-center gap-2 transition-all active:scale-95 bg-indigo-600 hover:bg-indigo-700">
                <x-ui.icon name="check" size="16" />
                Simpan Pengguna
            </button>
        </div>
    </div>

    {{-- RIGHT COLUMN (Sidebar) --}}
    <div class="hidden lg:block lg:col-span-4 pl-2">
        <div class="sticky top-8 space-y-4">
             <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">
                    Panduan Role
                </h4>
                <ul class="space-y-4 text-sm">
                    <li class="flex gap-3 items-start">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                            <x-ui.icon name="user" size="16" />
                        </div>
                        <div>
                            <strong class="block text-slate-800">Guru & Staff</strong>
                            <p class="text-xs text-slate-500 leading-snug mt-0.5">Wajib memiliki NIP/NI PPPK/NUPTK.</p>
                        </div>
                    </li>
                    <li class="flex gap-3 items-start">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                            <x-ui.icon name="shield" size="16" />
                        </div>
                        <div>
                            <strong class="block text-slate-800">Struktural</strong>
                            <p class="text-xs text-slate-500 leading-snug mt-0.5">Kepala Sekolah, Waka, & Operator juga mengisi data kepegawaian.</p>
                        </div>
                    </li>
                     <li class="flex gap-3 items-start">
                        <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                            <x-ui.icon name="users" size="16" />
                        </div>
                        <div>
                            <strong class="block text-slate-800">Wali Murid</strong>
                            <p class="text-xs text-slate-500 leading-snug mt-0.5">Akun untuk orang tua siswa.</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</x-forms.card>

<script>
    function createUserForm(roleMap) {
        return {
            form: {
                username: '{{ old('username') }}',
                email: '{{ old('email') }}',
                password: '',
                passwordConfirm: '',
                nip: '{{ old('nip') }}',
                ni_pppk: '{{ old('ni_pppk') }}',
                nuptk: '{{ old('nuptk') }}'
            },
            status: { nip: '', ni_pppk: '', nuptk: '' },
            messages: { nip: '', ni_pppk: '', nuptk: '' },
            timers: { nip: null, ni_pppk: null, nuptk: null },

            roleId: '{{ old('role_id') }}',
            roleMap: roleMap,
            
            passwordMatch: false,
            
            checkPasswordMatch() {
                this.passwordMatch = this.form.password === this.form.passwordConfirm;
            },
            
            checkIdentity(field) {
                const value = this.form[field];
                this.messages[field] = '';
                
                // Allow empty for optional logic (backend validation handles strictness if required)
                if (!value) {
                    this.status[field] = '';
                    return;
                }

                // Numeric only check
                if (/[^0-9]/.test(value)) {
                    this.status[field] = 'invalid';
                    this.messages[field] = 'Hanya angka.';
                    return;
                }

                // Length Check
                const requiredLen = field === 'nuptk' ? 16 : 18;
                if (value.length !== requiredLen) {
                    this.status[field] = 'invalid';
                    this.messages[field] = `Wajib ${requiredLen} digit.`;
                    return;
                }

                this.status[field] = 'checking';
                
                clearTimeout(this.timers[field]);
                this.timers[field] = setTimeout(async () => {
                    try {
                        const response = await fetch(`{{ route('users.check-identity') }}?type=${field}&value=${value}`);
                        const data = await response.json();
                        
                        if (data.available) {
                            this.status[field] = 'valid';
                        } else {
                            this.status[field] = 'invalid';
                            this.messages[field] = `Milik: ${data.owner}`;
                        }
                    } catch (error) {
                         this.status[field] = 'invalid';
                         this.messages[field] = 'Gagal cek data.';
                    }
                }, 500);
            },

            submitForm(e) {
                e.target.submit();
            },
            
            getRoleName() {
                if (!this.roleId) return '';
                return this.roleMap[this.roleId] || '';
            },

            needsNipNuptk() {
                const r = this.getRoleName();
                // Expanded logic for all staff/structural roles
                const staffRoles = [
                    'guru', 
                    'pengajar', 
                    'staff', 
                    'wali kelas', 
                    'kaprodi', 
                    'kepala sekolah', 
                    'operator', 
                    'waka', 
                    'kurikulum', 
                    'kesiswaan', 
                    'sarana'
                ];
                return staffRoles.some(role => r.includes(role));
            },
            
            isWaliKelas() {
                return this.getRoleName().includes('wali kelas');
            },
            
            isKaprodi() {
                return this.getRoleName().includes('kaprodi') || this.getRoleName().includes('jurusan');
            },
            
            isWaliMurid() {
                return this.getRoleName().includes('wali murid') || this.getRoleName().includes('orang tua');
            }
        }
    }
</script>
@endsection
