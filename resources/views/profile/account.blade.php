@extends('layouts.app')

@section('title', 'Profil Saya')

@section('page-header')
    <x-page-header 
        title="Profil Saya" 
        subtitle="Kelola informasi akun dan keamanan Anda."
    />
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Profile Card --}}
    <div class="lg:col-span-1">
        <div class="card">
            <div class="card-body text-center">
                {{-- Avatar --}}
                <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white text-3xl font-bold shadow-lg shadow-blue-500/30">
                    {{ strtoupper(substr(Auth::user()->username ?? 'U', 0, 1)) }}
                </div>
                
                <h3 class="text-xl font-bold text-gray-800 mt-4">{{ Auth::user()->username }}</h3>
                <p class="text-gray-500">{{ Auth::user()->email ?? 'Email belum diatur' }}</p>
                
                <div class="mt-4">
                    <span class="badge badge-primary text-sm">
                        {{ Auth::user()->effectiveRoleName() ?? Auth::user()->role?->nama_role ?? 'User' }}
                    </span>
                </div>
                
                {{-- Stats --}}
                <div class="grid grid-cols-2 gap-4 mt-6 pt-6 border-t border-gray-100">
                    <div>
                        <p class="text-2xl font-bold text-gray-800">{{ Auth::user()->created_at?->diffInDays(now()) ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Hari Bergabung</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800">{{ Auth::user()->last_login_at?->diffForHumans() ?? '-' }}</p>
                        <p class="text-xs text-gray-500">Login Terakhir</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Edit Forms --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Profile Information --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title flex items-center gap-2">
                    <x-ui.icon name="user" size="18" class="text-gray-400" />
                    Informasi Profil
                </h3>
            </div>
            <div class="card-body">
                <form action="{{ route('account.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Username (readonly) --}}
                        <div class="form-group">
                            <label class="form-label">Username</label>
                            <input type="text" value="{{ Auth::user()->username }}" class="form-input bg-gray-50" readonly disabled>
                            <p class="form-help">Username tidak dapat diubah.</p>
                        </div>
                        
                        {{-- Email --}}
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="{{ old('email', Auth::user()->email) }}"
                                class="form-input @error('email') error @enderror" 
                                placeholder="contoh@email.com"
                            >
                            @error('email')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    {{-- NIP/NUPTK (for teachers) --}}
                    @if(Auth::user()->hasAnyRole(['Guru', 'Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Kepala Sekolah']))
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="nip" class="form-label">NIP</label>
                            <input 
                                type="text" 
                                id="nip" 
                                name="nip" 
                                value="{{ old('nip', Auth::user()->nip) }}"
                                class="form-input @error('nip') error @enderror" 
                                placeholder="Nomor Induk Pegawai"
                            >
                            @error('nip')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="nuptk" class="form-label">NUPTK</label>
                            <input 
                                type="text" 
                                id="nuptk" 
                                name="nuptk" 
                                value="{{ old('nuptk', Auth::user()->nuptk) }}"
                                class="form-input @error('nuptk') error @enderror" 
                                placeholder="Nomor Unik Pendidik dan Tenaga Kependidikan"
                            >
                            @error('nuptk')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    @endif
                    
                    <div class="flex justify-end pt-4">
                        <button type="submit" class="btn btn-primary">
                            <x-ui.icon name="save" size="18" />
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        {{-- Change Password --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title flex items-center gap-2">
                    <x-ui.icon name="lock" size="18" class="text-gray-400" />
                    Ubah Password
                </h3>
            </div>
            <div class="card-body">
                <form action="{{ route('account.password.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('POST')
                    
                    {{-- Current Password --}}
                    <div class="form-group" x-data="{ show: false }">
                        <label for="current_password" class="form-label form-label-required">Password Saat Ini</label>
                        <div class="relative">
                            <input 
                                :type="show ? 'text' : 'password'" 
                                id="current_password" 
                                name="current_password" 
                                class="form-input !pr-10 @error('current_password') error @enderror" 
                                placeholder="Masukkan password saat ini"
                                required
                            >
                            <button 
                                type="button" 
                                @click="show = !show"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                            >
                                <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg x-show="show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>
                                </svg>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- New Password --}}
                        <div class="form-group" x-data="{ show: false }">
                            <label for="password" class="form-label form-label-required">Password Baru</label>
                            <div class="relative">
                                <input 
                                    :type="show ? 'text' : 'password'" 
                                    id="password" 
                                    name="password" 
                                    class="form-input !pr-10 @error('password') error @enderror" 
                                    placeholder="Minimal 8 karakter"
                                    required
                                >
                                <button 
                                    type="button" 
                                    @click="show = !show"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                >
                                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    <svg x-show="show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        {{-- Confirm Password --}}
                        <div class="form-group" x-data="{ show: false }">
                            <label for="password_confirmation" class="form-label form-label-required">Konfirmasi Password</label>
                            <div class="relative">
                                <input 
                                    :type="show ? 'text' : 'password'" 
                                    id="password_confirmation" 
                                    name="password_confirmation" 
                                    class="form-input !pr-10" 
                                    placeholder="Ulangi password baru"
                                    required
                                >
                                <button 
                                    type="button" 
                                    @click="show = !show"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                >
                                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    <svg x-show="show" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end pt-4">
                        <button type="submit" class="btn btn-primary">
                            <x-ui.icon name="shield-check" size="18" />
                            <span>Ubah Password</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
