@extends('layouts.app')

@section('title', 'Profil Pengguna')
@section('subtitle', 'Kelola data diri dan keamanan akun Anda.')
@section('page-header', true)

@section('content')
@php
    $userRoleName = $user->role->nama_role ?? 'User';
    $isOperator = $userRoleName === 'Operator Sekolah';
@endphp

<div class="max-w-5xl">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Left Sidebar: Profile Card --}}
        <div class="lg:col-span-1">
            <div class="card bg-gradient-to-br from-gray-800 to-gray-900 text-white">
                <div class="card-body flex flex-col items-center text-center py-10">
                    {{-- Avatar --}}
                    <div class="mb-6 relative">
                        <div class="w-28 h-28 rounded-full p-1 bg-gradient-to-tr from-blue-500 to-cyan-400 shadow-2xl">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->username) }}&background=1f2937&color=fff&size=256&bold=true" 
                                 class="w-full h-full rounded-full border-4 border-gray-800 object-cover">
                        </div>
                        <div class="absolute bottom-1 right-1 w-5 h-5 bg-emerald-500 border-4 border-gray-800 rounded-full" title="Online"></div>
                    </div>
                    
                    {{-- Username --}}
                    <h2 class="text-xl font-bold mb-1">{{ $user->username }}</h2>
                    <p class="text-blue-200 text-sm mb-4">{{ $user->email }}</p>
                    
                    {{-- Role Badge --}}
                    <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-gray-700 border border-gray-600">
                        <span class="w-2 h-2 rounded-full bg-blue-500 mr-2 animate-pulse"></span>
                        <span class="text-xs font-bold uppercase tracking-wide text-gray-300">{{ $userRoleName }}</span>
                    </div>
                    
                    {{-- Join Date --}}
                    <div class="mt-auto pt-6 text-gray-400 text-xs">
                        Bergabung sejak {{ $user->created_at->format('M Y') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Edit Form --}}
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Edit Informasi</span>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            {{-- Role (Read-only) --}}
                            <div class="md:col-span-2">
                                <div class="form-group">
                                    <label class="form-label">Peran</label>
                                    <div class="inline-flex items-center px-4 py-2 bg-indigo-50 rounded-xl border border-indigo-100">
                                        <span class="w-2 h-2 rounded-full bg-indigo-500 mr-3"></span>
                                        <span class="font-semibold text-gray-700">{{ $userRoleName }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Email --}}
                            <div class="form-group">
                                <label for="email" class="form-label form-label-required">Alamat Email</label>
                                <input type="email" id="email" name="email" class="form-input @error('email') error @enderror" 
                                       value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Username --}}
                            <div class="form-group">
                                <label for="username" class="form-label form-label-required">Username</label>
                                <input type="text" id="username" name="username" class="form-input @error('username') error @enderror" 
                                       value="{{ old('username', $user->username) }}" required>
                                @error('username')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Phone --}}
                            <div class="form-group">
                                <label for="phone" class="form-label">Nomor WhatsApp</label>
                                <input type="text" id="phone" name="phone" class="form-input @error('phone') error @enderror" 
                                       value="{{ old('phone', $user->phone) }}" placeholder="08..."
                                       {{ $userRoleName == 'Wali Murid' ? 'readonly' : '' }}>
                                @error('phone')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Change Password Link --}}
                            <div class="form-group">
                                <label class="form-label">Keamanan Akun</label>
                                <a href="{{ route('profile.change-password.form') }}" 
                                   class="flex items-center justify-between w-full px-4 py-3 bg-white border-2 border-dashed border-gray-200 rounded-xl text-gray-600 hover:border-amber-400 hover:text-amber-600 hover:bg-amber-50 transition-all group">
                                    <div class="flex items-center gap-2">
                                        <div class="p-1 bg-gray-100 rounded-md group-hover:bg-amber-100 transition-colors">
                                                <x-ui.icon name="lock" size="16" />
                                        </div>
                                        <span class="text-sm font-bold">Ganti Password</span>
                                    </div>
                                    <x-ui.icon name="chevron-right" size="16" class="opacity-0 group-hover:opacity-100 transition-opacity" />
                                </a>
                            </div>

                        </div>

                        {{-- Wali Murid Info --}}
                        @if($userRoleName == 'Wali Murid')
                        <div class="p-4 bg-blue-50 rounded-xl border border-blue-100">
                            <div class="flex items-start gap-3">
                                <x-ui.icon name="info" size="18" class="text-blue-500 shrink-0 mt-0.5" />
                                <p class="text-xs text-blue-700">
                                    Nomor telepon Wali Murid disinkronisasi otomatis dari data siswa. Hubungi admin jika ada kesalahan.
                                </p>
                            </div>
                        </div>
                        @endif

                        {{-- Actions --}}
                        <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-100">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <x-ui.icon name="save" size="18" />
                                <span>Simpan Perubahan</span>
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
