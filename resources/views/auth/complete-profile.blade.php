@extends('layouts.guest')

@section('title', 'Lengkapi Profil')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-slate-50 to-primary-50">
    <div class="w-full max-w-lg">
        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 p-8 border border-gray-100">
            {{-- Header --}}
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-primary-500/30">
                    <x-ui.icon name="user" size="32" class="text-white" />
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Lengkapi Profil Anda</h1>
                <p class="text-gray-500 mt-2">
                    Halo <span class="font-medium text-gray-700">{{ Auth::user()->username }}</span>, 
                    sebelum melanjutkan silakan lengkapi data profil Anda.
                </p>
            </div>
            
            {{-- Progress Indicator --}}
            <div class="flex items-center justify-center gap-2 mb-8">
                <div class="w-10 h-10 rounded-full bg-primary-600 text-white flex items-center justify-center text-sm font-bold">1</div>
                <div class="w-16 h-1 bg-gray-200 rounded-full"></div>
                <div class="w-10 h-10 rounded-full bg-gray-200 text-gray-400 flex items-center justify-center text-sm font-bold">2</div>
            </div>
            
            {{-- Error Alert --}}
            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-xl">
                    <div class="flex gap-3">
                        <x-ui.icon name="x" size="20" class="text-red-500 shrink-0" />
                        <div class="text-sm text-red-600">
                            @foreach($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
            
            {{-- Form --}}
            <form action="{{ route('profile.complete.store') }}" method="POST" class="space-y-5">
                @csrf
                
                {{-- Email --}}
                <div class="form-group">
                    <label for="email" class="form-label form-label-required">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-ui.icon name="mail" size="18" class="text-gray-400" />
                        </div>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="{{ old('email', Auth::user()->email) }}"
                            class="form-input !pl-10 @error('email') error @enderror" 
                            placeholder="contoh@email.com"
                            required
                        >
                    </div>
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- Current Password --}}
                @if($needsPasswordChange ?? true)
                <div class="form-group" x-data="{ show: false }">
                    <label for="current_password" class="form-label form-label-required">Password Lama</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-ui.icon name="unlock" size="18" class="text-gray-400" />
                        </div>
                        <input 
                            :type="show ? 'text' : 'password'" 
                            id="current_password" 
                            name="current_password" 
                            class="form-input !pl-10 !pr-10 @error('current_password') error @enderror" 
                            placeholder="Masukkan password saat ini"
                            required
                        >
                        <button 
                            type="button" 
                            @click="show = !show"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                        >
                            <x-ui.icon name="eye" size="18" x-show="!show" />
                            <x-ui.icon name="eye-off" size="18" x-show="show" />
                        </button>
                    </div>
                    @error('current_password')
                        <p class="form-error">{{ $message }}</p>
                    @else
                        <p class="form-help">Masukkan password yang Anda gunakan untuk login (password default dari operator).</p>
                    @enderror
                </div>
                @endif
                
                {{-- New Password --}}
                <div class="form-group" x-data="{ show: false }">
                    <label for="password" class="form-label form-label-required">Password Baru</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-ui.icon name="lock" size="18" class="text-gray-400" />
                        </div>
                        <input 
                            :type="show ? 'text' : 'password'" 
                            id="password" 
                            name="password" 
                            class="form-input !pl-10 !pr-10 @error('password') error @enderror" 
                            placeholder="Minimal 8 karakter"
                            required
                        >
                        <button 
                            type="button" 
                            @click="show = !show"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                        >
                            <x-ui.icon name="eye" size="18" x-show="!show" />
                            <x-ui.icon name="eye-off" size="18" x-show="show" />
                        </button>
                    </div>
                    @error('password')
                        <p class="form-error">{{ $message }}</p>
                    @else
                        <p class="form-help">Gunakan kombinasi huruf, angka, dan simbol untuk keamanan lebih baik.</p>
                    @enderror
                </div>
                
                {{-- Confirm Password --}}
                <div class="form-group" x-data="{ show: false }">
                    <label for="password_confirmation" class="form-label form-label-required">Konfirmasi Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-ui.icon name="shield" size="18" class="text-gray-400" />
                        </div>
                        <input 
                            :type="show ? 'text' : 'password'" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            class="form-input !pl-10 !pr-10" 
                            placeholder="Ulangi password baru"
                            required
                        >
                        <button 
                            type="button" 
                            @click="show = !show"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                        >
                            <x-ui.icon name="eye" size="18" x-show="!show" />
                            <x-ui.icon name="eye-off" size="18" x-show="show" />
                        </button>
                    </div>
                </div>
                
                {{-- Actions --}}
                <div class="flex flex-col gap-3 pt-4">
                    <button type="submit" class="btn btn-primary w-full !py-3 text-base">
                        <x-ui.icon name="check" size="18" />
                        <span>Simpan & Lanjutkan</span>
                    </button>
                    
                    <a href="{{ route('profile.complete.skip') }}" class="btn btn-secondary w-full !py-3 text-base justify-center">
                        <span>Lewati untuk sekarang</span>
                    </a>
                </div>
            </form>
        </div>
        
        {{-- Footer --}}
        <div class="text-center mt-6 text-sm text-gray-400">
            <p>Anda login sebagai <span class="font-medium text-gray-600">{{ Auth::user()->role?->nama_role ?? 'User' }}</span></p>
        </div>
    </div>
</div>
@endsection
