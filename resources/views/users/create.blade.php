@extends('layouts.app')

@section('title', 'Tambah User')

@section('page-header')
    <x-page-header 
        title="Tambah User" 
        subtitle="Buat akun pengguna baru."
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
    title="Form Tambah User"
    :action="route('users.store')"
    method="POST"
    maxWidth="3xl"
    x-data="{ 
        roleId: '{{ old('role_id', '') }}',
        roleMap: {{ json_encode($roleMap) }},
        
        getRoleName() {
            return this.roleMap[this.roleId] || '';
        },
        needsNipNuptk() {
            const roles = ['guru', 'waka kesiswaan', 'waka sarana', 'operator sekolah', 'wali kelas', 'kaprodi', 'kepala sekolah'];
            const name = this.getRoleName();
            return roles.some(r => name.includes(r));
        },
        isWaliKelas() {
            return this.getRoleName().includes('wali kelas');
        },
        isKaprodi() {
            return this.getRoleName().includes('kaprodi');
        },
        isWaliMurid() {
            return this.getRoleName().includes('wali murid');
        },
        isDeveloper() {
            return this.getRoleName().includes('developer');
        }
    }"
>
    {{-- Basic Information --}}
    <x-forms.section title="Informasi Dasar" icon="user" variant="default">
        <x-forms.grid :cols="2">
            <x-forms.input 
                name="username" 
                label="Username" 
                required 
                help="Username untuk login ke sistem" 
            />
            
            <x-forms.input 
                type="email" 
                name="email" 
                label="Email" 
                help="Email untuk notifikasi (opsional)"
            />
        </x-forms.grid>
        
        <x-forms.grid :cols="2">
            <x-forms.password 
                name="password" 
                label="Password" 
                required 
                autocomplete="new-password"
            />
            
            <x-forms.password 
                name="password_confirmation" 
                label="Konfirmasi Password" 
                required 
                autocomplete="new-password"
            />
        </x-forms.grid>
    </x-forms.section>
    
    {{-- Role Selection --}}
    <x-forms.section title="Hak Akses" icon="shield" variant="info">
        <x-forms.select 
            name="role_id" 
            label="Role Pengguna" 
            required 
            x-model="roleId"
            :options="$roles"
            optionValue="id"
            optionLabel="nama_role"
            :value="old('role_id')"
            placeholder="Pilih Role"
            help="Role menentukan hak akses dan fitur yang tersedia."
        />
    </x-forms.section>
    
    {{-- NIP & NUPTK (untuk Guru, Waka, Kepala Sekolah, etc) --}}
    <div x-show="needsNipNuptk() || isDeveloper()" x-transition x-cloak>
        <x-forms.section title="Data Kepegawaian" icon="credit-card" variant="warning">
            <x-forms.grid :cols="2">
                <x-forms.input 
                    name="nip" 
                    label="NIP" 
                    placeholder="18 digit NIP" 
                />
                
                <x-forms.input 
                    name="nuptk" 
                    label="NUPTK" 
                    placeholder="16 digit NUPTK" 
                />
            </x-forms.grid>
        </x-forms.section>
    </div>
    
    {{-- Kelas (untuk Wali Kelas) --}}
    <div x-show="isWaliKelas() || isDeveloper()" x-transition x-cloak>
        <x-forms.section title="Kelas yang Diampu" icon="book-open" variant="success">
            <x-forms.select 
                name="kelas_id" 
                label="Kelas" 
                :options="$kelas"
                optionValue="id"
                optionLabel="nama_kelas"
                :value="old('kelas_id')"
                placeholder="-- Pilih Kelas --"
                help="Pilih kelas yang akan menjadi tanggung jawab wali kelas ini."
            />
        </x-forms.section>
    </div>
    
    {{-- Jurusan (untuk Kaprodi) --}}
    <div x-show="isKaprodi() || isDeveloper()" x-transition x-cloak>
        <x-forms.section title="Jurusan yang Diampu" icon="layers" variant="primary">
            <x-forms.select 
                name="jurusan_id" 
                label="Jurusan"
                :options="$jurusan"
                optionValue="id"
                optionLabel="nama_jurusan"
                :value="old('jurusan_id')"
                placeholder="-- Pilih Jurusan --"
                help="Pilih jurusan yang akan menjadi tanggung jawab Kaprodi ini."
            />
        </x-forms.section>
    </div>
    
    {{-- Siswa/Anak (untuk Wali Murid) --}}
    <div x-show="isWaliMurid() || isDeveloper()" x-transition x-cloak>
        <x-forms.section title="Siswa/Anak yang Diasuh" icon="users" variant="danger">
            <p class="text-sm text-gray-500 mb-3">Pilih siswa yang menjadi anak dari wali murid ini.</p>
            <div class="max-h-48 overflow-y-auto border border-rose-200 rounded-lg p-3 bg-white space-y-2">
                @forelse($siswa ?? [] as $s)
                    <label class="flex items-center gap-3 p-2 hover:bg-rose-50 rounded-lg cursor-pointer">
                        <input type="checkbox" name="siswa_ids[]" value="{{ $s->id }}" 
                               {{ in_array($s->id, old('siswa_ids', [])) ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-800">{{ $s->nama_siswa }}</p>
                            <p class="text-sm text-gray-500">{{ $s->nisn }} • {{ $s->kelas->nama_kelas ?? '-' }}</p>
                        </div>
                    </label>
                @empty
                    <p class="text-gray-400 text-sm text-center py-4">Tidak ada data siswa</p>
                @endforelse
            </div>
            @error('siswa_ids')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </x-forms.section>
    </div>
    
    {{-- Phone & Status --}}
    <x-forms.grid :cols="2">
        <x-forms.input 
            name="phone" 
            label="No. Telepon" 
            placeholder="08xxxxxxxxxx" 
        />
        
        <div class="form-group flex items-end">
            <x-forms.checkbox 
                name="is_active"
                label="Aktifkan akun langsung"
                :checked="true"
            />
        </div>
    </x-forms.grid>
    
    <x-forms.actions 
        submitLabel="Simpan User"
        :cancelUrl="route('users.index')"
    />
</x-forms.card>
@endsection
