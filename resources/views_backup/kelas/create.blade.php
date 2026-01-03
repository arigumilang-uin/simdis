@extends('layouts.app')

@section('content')

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: { colors: { primary: '#3b82f6', slate: { 800: '#1e293b', 900: '#0f172a' } } }
        },
        corePlugins: { preflight: false }
    }
</script>

<style>
    .page-wrap { background: #f8fafc; min-height: 100vh; padding: 2rem 1.5rem; font-family: 'Inter', sans-serif; display: flex; justify-content: center; }
    
    /* Input Styling */
    .form-group { margin-bottom: 1.25rem; }
    .form-label { display: block; font-size: 0.875rem; font-weight: 600; color: #475569; margin-bottom: 0.5rem; }
    .form-input { 
        width: 100%; 
        padding: 0.75rem 1rem; 
        border-radius: 0.75rem; 
        border: 1px solid #cbd5e1; 
        background-color: #ffffff; 
        color: #1e293b; 
        transition: all 0.2s; 
        font-size: 0.95rem; 
    }
    .form-input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
    
    /* Custom Select Arrow */
    select.form-input {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.75rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;
    }

    /* Buttons */
    .btn-primary { background: #2563eb; color: white; padding: 0.75rem 1.5rem; border-radius: 0.75rem; font-weight: 600; border: none; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; }
    .btn-primary:hover { background: #1d4ed8; transform: translateY(-1px); shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3); }
    
    .btn-secondary { background: white; border: 1px solid #e2e8f0; color: #64748b; padding: 0.75rem 1.5rem; border-radius: 0.75rem; font-weight: 600; text-decoration: none; display: inline-block; text-align: center; transition: 0.2s; }
    .btn-secondary:hover { background: #f1f5f9; color: #334155; border-color: #cbd5e1; }
</style>

<div class="page-wrap">

    <div class="w-full max-w-3xl">
        
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 m-0">Tambah Kelas Baru</h1>
                <p class="text-slate-500 text-sm mt-1">Buat rombongan belajar baru.</p>
            </div>
            <a href="{{ route('kelas.index') }}" class="text-sm font-medium text-slate-500 hover:text-blue-600 no-underline flex items-center gap-1 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Kembali
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-slate-100 overflow-hidden">
            
            <form action="{{ route('kelas.store') }}" method="POST" class="p-8">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    
                    <div class="form-group">
                        <label class="form-label">Tingkat Kelas <span class="text-rose-500">*</span></label>
                        <select name="tingkat" class="form-input cursor-pointer" required>
                            <option value="">-- Pilih Tingkat --</option>
                            <option value="X" {{ old('tingkat') == 'X' ? 'selected' : '' }}>Kelas X (Sepuluh)</option>
                            <option value="XI" {{ old('tingkat') == 'XI' ? 'selected' : '' }}>Kelas XI (Sebelas)</option>
                            <option value="XII" {{ old('tingkat') == 'XII' ? 'selected' : '' }}>Kelas XII (Dua Belas)</option>
                        </select>
                        <p class="text-xs text-slate-400 mt-1 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="16" y2="12"/><line x1="12" x2="12.01" y1="8" y2="8"/></svg>
                            Nama kelas akan digenerate otomatis.
                        </p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Jurusan / Kompetensi <span class="text-rose-500">*</span></label>
                        <select name="jurusan_id" class="form-input cursor-pointer" required>
                            <option value="">-- Pilih Jurusan --</option>
                            @foreach($jurusanList as $j)
                                <option value="{{ $j->id }}" data-kode="{{ $j->kode_jurusan ?? '' }}">
                                    {{ $j->nama_jurusan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr class="border-slate-100 my-6">

                <div class="form-group">
                    <label class="form-label">Wali Kelas (Opsional)</label>
                    <select name="wali_kelas_user_id" class="form-input cursor-pointer">
                        <option value="">-- Pilih dari Guru yang Ada --</option>
                        @foreach($waliList as $w)
                            <option value="{{ $w->id }}">{{ $w->nama }} ({{ $w->username }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="relative flex items-center">
                            <input type="checkbox" id="create_wali" name="create_wali" value="1" class="peer h-5 w-5 cursor-pointer appearance-none rounded-md border border-slate-300 transition-all checked:border-blue-500 checked:bg-blue-500 hover:border-blue-400">
                            <svg class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-white opacity-0 peer-checked:opacity-100" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <label for="create_wali" class="text-sm font-bold text-slate-700 cursor-pointer select-none">
                            Buat akun Wali Kelas baru secara otomatis
                        </label>
                    </div>
                    <p class="text-xs text-slate-500 mt-1 ml-8">Centang ini jika guru belum terdaftar di sistem. Akun akan dibuatkan oleh sistem.</p>

                    <div id="wali_preview" class="mt-4 hidden animate-fade-in-down">
                        <div class="bg-white border border-indigo-100 rounded-lg p-4 shadow-sm relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-2 opacity-10">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </div>
                            
                            <h5 class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-3">Preview Akun Baru</h5>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 relative z-10">
                                <div>
                                    <span class="text-xs text-slate-400 block mb-1">Username</span>
                                    <div class="flex items-center gap-2 bg-slate-50 p-2 rounded border border-slate-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        <span id="wali_username_preview" class="font-mono font-bold text-slate-700 text-sm">...</span>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-400 block mb-1">Password Awal</span>
                                    <div class="flex items-center gap-2 bg-slate-50 p-2 rounded border border-slate-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                        <span id="wali_password_preview" class="font-mono font-bold text-rose-500 text-sm">...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 flex flex-col-reverse md:flex-row gap-3 justify-end">
                    <a href="{{ route('kelas.index') }}" class="btn-secondary w-full md:w-auto">Batal</a>
                    <button type="submit" class="btn-primary w-full md:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v13a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Simpan Data
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const chk = document.getElementById('create_wali');
    const tingkatEl = document.querySelector('select[name="tingkat"]');
    const jurusanEl = document.querySelector('select[name="jurusan_id"]');
    const previewBox = document.getElementById('wali_preview');
    const userPreview = document.getElementById('wali_username_preview');
    const passPreview = document.getElementById('wali_password_preview');

    function generateKodeFromNama(nama){
        const parts = nama.trim().split(/\s+/).filter(Boolean);
        let letters = '';
        for(let p of parts){ letters += p[0].toUpperCase(); if(letters.length>=3) break; }
        return letters || 'JRS';
    }

    function normalizeUsername(str){
        return str.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
    }

    function randomPassword(len=10){
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        let out = '';
        for(let i=0;i<len;i++) out += chars.charAt(Math.floor(Math.random()*chars.length));
        return out;
    }

    function updatePreview(){
        const tingkat = tingkatEl.value || '';
        const jurusanOpt = jurusanEl.selectedOptions[0];
        // Mengambil data-kode dari option yang dipilih
        const jurusanKode = jurusanOpt ? (jurusanOpt.getAttribute('data-kode') || '') : '';
        
        // Fallback jika tidak ada kode
        let kode = jurusanKode || generateKodeFromNama(jurusanOpt ? jurusanOpt.textContent : '');
        
        // Format: kode_tingkat_wali (cth: rpl_x_wali)
        const username = normalizeUsername(kode + '_' + tingkat + '_wali');
        
        userPreview.textContent = username;
        // Password generate hanya sekali saat preview muncul agar tidak berubah-ubah terus
        if(passPreview.textContent === '...' || passPreview.textContent === '') {
             passPreview.textContent = randomPassword(8);
        }
    }

    chk.addEventListener('change', function(){
        if(chk.checked){ 
            previewBox.classList.remove('hidden'); 
            updatePreview(); 
        } else { 
            previewBox.classList.add('hidden'); 
        }
    });

    tingkatEl.addEventListener('change', function(){ if(chk.checked) updatePreview(); });
    jurusanEl.addEventListener('change', function(){ if(chk.checked) updatePreview(); });
});
</script>
@endpush

@endsection