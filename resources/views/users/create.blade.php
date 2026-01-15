@extends('layouts.app')

@section('title', 'Tambah User')

@section('page-header')
    <x-page-header 
        title="Tambah User Baru" 
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

<div class="form-page-container" 
    x-data="userForm({
        roleId: '{{ old('role_id', '') }}',
        roleMap: {{ Js::from($roleMap) }}
    })"
>
    <form action="{{ route('users.store') }}" method="POST" class="form-layout-wrapper">
        @csrf
        
        {{-- MAIN CONTENT (Left) --}}
        <div class="form-main-card">
            <div class="form-header">
                <h3 class="form-title">Informasi Akun</h3>
                <p class="form-subtitle">Lengkapi kredensial dan hak akses pengguna.</p>
            </div>
            
            <div class="form-body">
                {{-- SECTION 1: Login Details --}}
                <div class="form-section active">
                    <div class="form-section-title">
                        <x-ui.icon name="lock" size="18" class="text-primary-600" />
                        <span>Kredensial Login</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-forms.input 
                            name="username" 
                            label="Nama Lengkap" 
                            placeholder="Ari Gumilang, S.T., M.Kom"
                            required 
                            help="Nama asli user (bisa dengan gelar). Digunakan untuk login & ditampilkan di sistem." 
                        />
                        
                        <x-forms.input 
                            type="email" 
                            name="email" 
                            label="Email Address" 
                            placeholder="nama@sekolah.sch.id"
                            help="Opsional, untuk reset password."
                        />
                    </div>
                
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-4">
                        <x-forms.password 
                            name="password" 
                            label="Password" 
                            required 
                            autocomplete="new-password"
                        />
                        
                        <x-forms.password 
                            name="password_confirmation" 
                            label="Ulangi Password" 
                            required 
                            autocomplete="new-password"
                        />
                        
                        <div class="md:col-span-2">
                             <div class="flex items-center gap-2 mt-1">
                                <div class="h-1 flex-1 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-green-500 w-0 transition-all duration-300" id="password-strength"></div>
                                </div>
                                <span class="text-xs text-gray-400">Minimal 8 karakter</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECTION 2: Role & Access --}}
                <div class="form-section">
                    <div class="form-section-title">
                        <x-ui.icon name="shield" size="18" class="text-primary-600" />
                        <span>Hak Akses & Detail Profil</span>
                    </div>

                    <div class="form-group">
                        <x-forms.select 
                            name="role_id" 
                            label="Role Pengguna" 
                            required 
                            x-model="roleId"
                            :options="$roles"
                            optionValue="id"
                            optionLabel="nama_role"
                            :value="old('role_id')"
                            placeholder="-- Pilih Jenis Akun --"
                        />
                    </div>

                    {{-- NIP/NI PPPK/NUPTK (Guru/Staff) --}}
                    <div x-show="needsNipNuptk()" x-transition class="bg-amber-50 rounded-lg p-5 border border-amber-100 mb-4">
                        <h4 class="text-sm font-semibold text-amber-800 mb-3 flex items-center gap-2">
                            <x-ui.icon name="credit-card" size="14" /> Identitas Kepegawaian
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-forms.input name="nip" label="NIP" placeholder="Nomor Induk Pegawai" help="Untuk PNS" />
                            <x-forms.input name="ni_pppk" label="NI PPPK" placeholder="Nomor Induk PPPK" help="Untuk PPPK" />
                            <x-forms.input name="nuptk" label="NUPTK" placeholder="Nomor Unik Pendidik" help="Untuk Non-ASN" />
                        </div>
                    </div>

                    {{-- Kelas (Wali Kelas) --}}
                    <div x-show="isWaliKelas()" x-transition class="bg-emerald-50 rounded-lg p-5 border border-emerald-100 mb-4">
                        <h4 class="text-sm font-semibold text-emerald-800 mb-3 flex items-center gap-2">
                            <x-ui.icon name="book-open" size="14" /> Binaan Kelas
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
                    
                    {{-- Kaprodi --}}
                    <div x-show="isKaprodi()" x-transition class="bg-indigo-50 rounded-lg p-5 border border-indigo-100 mb-4">
                        <h4 class="text-sm font-semibold text-indigo-800 mb-3 flex items-center gap-2">
                            <x-ui.icon name="layers" size="14" /> Jurusan Binaan
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

                    {{-- Wali Murid --}}
                    <div x-show="isWaliMurid()" x-transition class="bg-rose-50 rounded-lg p-5 border border-rose-100 mb-4">
                        <h4 class="text-sm font-semibold text-rose-800 mb-3 flex items-center gap-2">
                            <x-ui.icon name="users" size="14" /> Hubungkan Siswa
                        </h4>
                        <div class="max-h-48 overflow-y-auto bg-white border border-rose-200 rounded-md p-2">
                            @forelse($siswa ?? [] as $s)
                                <label class="flex items-center gap-3 p-2 hover:bg-rose-50 rounded cursor-pointer">
                                    <input type="checkbox" name="siswa_ids[]" value="{{ $s->id }}" class="rounded text-rose-500 focus:ring-rose-500">
                                    <span class="text-sm text-gray-700">{{ $s->nama_siswa }} ({{ $s->kelas->nama_kelas ?? '-' }})</span>
                                </label>
                            @empty
                                <p class="text-center text-sm text-gray-400 py-2">Tidak ada data siswa</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Phone & Status --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-end mt-4">
                        <div class="form-group mb-0">
                            <x-forms.input name="phone" label="Nomor Telepon" placeholder="Opsional" />
                        </div>
                        <div class="form-group mb-2">
                            <x-forms.checkbox 
                                name="is_active" 
                                label="Aktifkan Akun Langsung" 
                                description="User dapat login segera setelah dibuat." 
                                checked 
                            />
                        </div>
                    </div>
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="form-actions-footer">
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary px-6">Simpan User Baru</button>
            </div>
        </div>
        
        {{-- SIDEBAR LINK (Right) --}}
        <div class="form-sidebar">
            <div class="sidebar-card">
                <div class="sidebar-header">
                    <x-ui.icon name="shield-check" size="16" class="text-primary-500" />
                    <span class="sidebar-title">Panduan Role</span>
                </div>
                <div class="sidebar-body">
                    <ul class="space-y-3 text-sm text-slate-600">
                        <li class="flex gap-2">
                            <span class="font-semibold text-slate-800 min-w-[80px]">Guru:</span>
                            <span>Akses mencatat pelanggaran dan input nilai.</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="font-semibold text-slate-800 min-w-[80px]">Wali Kelas:</span>
                            <span>Akses kelola siswa binaan dan rekap laporan.</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="font-semibold text-slate-800 min-w-[80px]">Kaprodi:</span>
                            <span>Monitoring satu jurusan penuh.</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="font-semibold text-slate-800 min-w-[80px]">BP/BK:</span>
                            <span>Akses tindak lanjut dan konseling siswa.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
