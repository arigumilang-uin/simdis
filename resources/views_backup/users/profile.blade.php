@extends('layouts.app')

{{-- 2. CONTENT UTAMA --}}
@section('content')

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#0f172a', // Slate 900
                    accent: '#3b82f6',  // Blue 500
                    subtle: '#64748b'   // Slate 500
                },
                fontFamily: {
                    sans: ['Inter', 'sans-serif'],
                }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

@php
    // Logika untuk menentukan apakah pengguna adalah Operator (digunakan untuk Nama Lengkap)
    // Asumsi nama role untuk operator adalah 'Operator'
    $userRoleName = $user->role->nama_role ?? 'User';
    $isOperator = $userRoleName === 'Operator';
@endphp

<div class="page-container p-6 bg-slate-50 min-h-screen font-sans">
    
    <div class="max-w-6xl mx-auto">
        
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Profil Pengguna</h1>
            <p class="text-slate-500 mt-1 font-medium">Kelola data diri dan keamanan akun Anda.</p>
        </div>

        <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-100 flex flex-col md:flex-row">
            
            <div class="w-full md:w-1/3 bg-slate-900 relative overflow-hidden flex flex-col items-center justify-center text-center p-10 text-white min-h-[400px]">
                
                <div class="absolute inset-0 opacity-20 pointer-events-none">
                    <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                        <path d="M0 0 L100 0 L100 100 L0 100 Z" fill="#0f172a"/>
                        <path d="M0 100 C 20 0 50 0 100 100 Z" fill="#1e293b"/>
                        <circle cx="90" cy="10" r="40" fill="#334155" opacity="0.5"/>
                    </svg>
                </div>

                <div class="relative z-10 flex-1 flex flex-col items-center justify-center w-full">
                    <div class="mb-6 relative inline-block">
                        <div class="w-32 h-32 rounded-full p-1 bg-gradient-to-tr from-blue-500 to-cyan-400 shadow-2xl mx-auto flex items-center justify-center">
                            {{-- Avatar menggunakan Username (nama asli user) --}}
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->username) }}&background=0f172a&color=fff&size=256&bold=true" 
                                    class="w-full h-full rounded-full border-4 border-slate-900 object-cover">
                        </div>
                        <div class="absolute bottom-2 right-2 w-6 h-6 bg-emerald-500 border-4 border-slate-900 rounded-full" title="Online"></div>
                    </div>
                    
                    {{-- SIDEBAR: Menampilkan USERNAME (nama asli user) --}}
                    <h2 class="text-2xl font-bold tracking-tight mb-1">{{ $user->username }}</h2>
                    <p class="text-blue-200 text-sm mb-6 font-medium">{{ $user->email }}</p>
                    
                    <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-slate-800 border border-slate-700 shadow-sm mx-auto">
                        <span class="w-2 h-2 rounded-full bg-blue-500 mr-2 animate-pulse"></span>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-300">{{ $userRoleName }}</span>
                    </div>
                </div>

                <div class="relative z-10 mt-auto pt-6 text-slate-500 text-xs">
                    Bergabung sejak {{ $user->created_at->format('M Y') }}
                </div>
            </div>

            <div class="w-full md:w-2/3 p-8 md:p-12 bg-white">
                
                {{-- PERBAIKAN SPACING: mb-8 diubah menjadi mb-5 dan pb-4 menjadi pb-2 --}}
                <div class="mb-5 pb-2 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800">Edit Informasi</h3>
                    <p class="text-sm text-slate-400">Perbarui detail profil Anda di bawah ini.</p>
                </div>

                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- Role Info (read-only) --}}
                        <div class="md:col-span-2">
                            <div class="group">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Peran</label>
                                <div class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-100">
                                    <span class="w-2 h-2 rounded-full bg-blue-500 mr-3"></span>
                                    <span class="font-semibold text-slate-700">{{ $userRoleName }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="group">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Alamat Email</label>
                            <input type="email" name="email" class="form-input-premium" value="{{ old('email', $user->email) }}" required>
                            @error('email') <p class="text-red-500 text-xs mt-1 ml-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- PERBAIKAN 2: Input Username (Editable untuk semua user) --}}
                        <div class="group">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Username</label>
                            <input 
                                type="text" 
                                name="username" 
                                class="form-input-premium" 
                                value="{{ old('username', $user->username) }}" 
                                required
                            >
                            @error('username') <p class="text-red-500 text-xs mt-1 ml-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Nomor WhatsApp</label>
                            {{-- Input Phone tetap readonly jika role Wali Murid --}}
                            <input type="text" name="phone" class="form-input-premium" value="{{ old('phone', $user->phone) }}" placeholder="08..."
                                {{ $userRoleName == 'Wali Murid' ? 'readonly' : '' }}>
                            @error('phone') <p class="text-red-500 text-xs mt-1 ml-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Keamanan Akun</label>
                            <a href="{{ route('profile.change-password.form') }}" class="flex items-center justify-between w-full px-4 py-3 bg-white border-2 border-dashed border-slate-300 rounded-xl text-slate-600 hover:border-amber-400 hover:text-amber-600 hover:bg-amber-50 transition-all duration-200 group no-underline shadow-sm">
                                <div class="flex items-center gap-2">
                                    <div class="p-1 bg-slate-100 rounded-md group-hover:bg-amber-100 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 14l-1 1-1 1H6v2H2v-2v-2.268l2-2a1.414 1.414 0 01.422-.977l5.96-5.96a1.414 1.414 0 01.977-.422l.268.268z" />
                                        </svg>
                                    </div>
                                    <span class="text-sm font-bold">Ganti Password</span>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-0 group-hover:opacity-100 transition-opacity transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>

                    </div>

                    @if($userRoleName == 'Wali Murid')
                        <div class="mt-4 bg-blue-50 border border-blue-100 rounded-lg p-3 flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-xs text-blue-700 leading-relaxed">
                                Nomor telepon Wali Murid disinkronisasi otomatis dari data siswa. Hubungi admin jika ada kesalahan.
                            </p>
                        </div>
                    @endif

                    <div class="mt-10 flex items-center justify-end gap-4 border-t border-slate-100 pt-6">
                        <a href="{{ route('dashboard') }}" class="px-6 py-3 text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors no-underline">
                            Batal
                        </a>
                        <button type="submit" class="px-8 py-3 bg-slate-900 hover:bg-black text-white text-sm font-bold rounded-xl shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5 active:scale-95 flex items-center gap-2">
                            <span>Simpan Perubahan</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>

                </form>
            </div>

        </div>

    </div>
</div>
@endsection

{{-- 3. STYLE CSS (PREMIUM INPUT) --}}
@section('styles')
<style>
    /* Styling dasar form-input-premium */
    .form-input-premium {
        display: block;
        width: 100%;
        padding: 0.875rem 1rem;
        font-size: 0.925rem;
        line-height: 1.25;
        color: #1e293b;
        background-color: #fff;
        background-clip: padding-box;
        border: 2px solid #f1f5f9;
        border-radius: 0.75rem;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Efek focus */
    .form-input-premium:focus {
        border-color: #3b82f6;
        background-color: #fff;
        outline: 0;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    /* Efek hover (kecuali disabled) */
    .form-input-premium:hover:not(:disabled):not([readonly]) {
        border-color: #cbd5e1;
    }

    /* Styling untuk disabled/readonly */
    .form-input-premium:disabled, .form-input-premium[readonly] {
        background-color: #f8fafc;
        color: #94a3b8;
        border-color: #f1f5f9;
        cursor: not-allowed;
    }
</style>
@endsection