@extends('layouts.app')

@section('content')

{{-- 1. TAILWIND CONFIG & SETUP --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    // Konfigurasi warna dasar agar seragam
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#0f172a', // Slate 900
                    accent: '#3b82f6',  // Blue 500
                    rose: { 500: '#f43f5e' }, 
                    amber: { 500: '#f59e0b' },
                    indigo: { 600: '#4f46e5' }
                },
                boxShadow: { 'soft': '0 4px 10px rgba(0,0,0,0.05)' }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

<div class="page-wrap bg-gray-50 min-h-screen p-3 sm:p-6">
    
    <div class="max-w-4xl mx-auto">
        
        <div class="flex justify-between items-center mb-3 pb-1 border-b border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Tambah Jurusan Baru</h1>
                <p class="text-sm text-gray-500 mt-1">Isi detail program studi dan tetapkan akun Kaprodi.</p>
            </div>
        </div>

        <form action="{{ route('jurusan.store') }}" method="POST">
            @csrf
            
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                     <h3 class="text-base font-bold text-slate-700 m-0 flex items-center gap-2">
                         <i class="fas fa-layer-group text-indigo-600"></i> Detail Program Studi
                     </h3>
                </div>
                
                <div class="p-6 space-y-6">
                    
                    {{-- Nama Jurusan --}}
                    <div>
                        <label class="form-label-custom">Nama Jurusan <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_jurusan" class="form-input-custom @error('nama_jurusan') is-invalid-custom @enderror" value="{{ old('nama_jurusan') }}" required>
                        @error('nama_jurusan') <p class="text-rose-500 text-xs mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Kode Jurusan --}}
                    <div>
                        <label class="form-label-custom">Kode Jurusan (Otomatis jika dikosongkan)</label>
                        <input type="text" name="kode_jurusan" class="form-input-custom @error('kode_jurusan') is-invalid-custom @enderror" value="{{ old('kode_jurusan') }}" placeholder="Contoh: APHP">
                        @error('kode_jurusan') <p class="text-rose-500 text-xs mt-1 ml-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-slate-400 mt-1">Kode ini akan digunakan untuk membuat username Kaprodi.</p>
                    </div>
                    
                    {{-- OPSI BUAT KAPRODI OTOMATIS --}}
                    <div class="border border-indigo-200 bg-indigo-50 rounded-xl p-4">
                        <div class="flex items-start">
                            <input type="checkbox" class="form-checkbox-custom mt-1" id="create_kaprodi" name="create_kaprodi" value="1" {{ old('create_kaprodi') ? 'checked' : '' }}>
                            <label class="form-label-custom text-indigo-700 ml-3 cursor-pointer" for="create_kaprodi">
                                Buat akun Kaprodi otomatis untuk jurusan ini
                            </label>
                        </div>
                        
                        <div id="kaprodi_preview" class="p-3 mt-3 border border-indigo-300 rounded-lg bg-white" style="display:none;">
                            <strong class="text-sm text-indigo-800">Preview Akun Baru:</strong>
                            <div class="grid grid-cols-2 gap-4 mt-2 text-sm">
                                <div><span class="text-slate-500">Username:</span> <span id="kaprodi_username_preview" class="font-bold text-slate-800"></span></div>
                                <div><span class="text-slate-500">Password (Sampel):</span> <span id="kaprodi_password_preview" class="font-bold text-slate-800"></span></div>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-2">Password akan di-generate ulang saat disimpan.</p>
                        </div>
                    </div>
                    
                </div>
                
                {{-- FOOTER ACTION --}}
                <div class="px-6 py-4 bg-gray-50 border-t border-slate-200 flex justify-end space-x-3">
                    <a href="{{ route('jurusan.index') }}" class="px-5 py-2 text-sm font-bold text-gray-700 hover:text-gray-900 bg-white border border-gray-300 rounded-xl shadow-sm hover:bg-gray-100 transition no-underline">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all transform active:scale-95 flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Jurusan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const chk = document.getElementById('create_kaprodi');
    const namaInput = document.querySelector('input[name="nama_jurusan"]');
    const kodeInput = document.querySelector('input[name="kode_jurusan"]');
    const previewBox = document.getElementById('kaprodi_preview');
    const userPreview = document.getElementById('kaprodi_username_preview');
    const passPreview = document.getElementById('kaprodi_password_preview');
    
    // Fungsi untuk memastikan preview selalu ter-generate saat DOM dimuat
    let isPasswordGenerated = false;

    function generateKodeFromNama(nama){
        const parts = nama.trim().split(/\s+/).filter(Boolean);
        let letters = '';
        for(let p of parts){ letters += p[0].toUpperCase(); if(letters.length>=3) break; }
        return letters || 'JRS';
    }

    function normalizeKaprodiUsernameFromKode(kode){
        // Memastikan kode Jurusan tidak terlalu panjang
        if (kode.length > 5) kode = kode.substring(0, 5); 
        return 'kaprodi.' + kode.toLowerCase().replace(/[^a-z0-9]+/g, '');
    }

    function randomPassword(len=8){
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        let out = '';
        for(let i=0;i<len;i++) out += chars.charAt(Math.floor(Math.random()*chars.length));
        return out;
    }

    function updatePreview(){
        const nama = namaInput.value || '';
        let kode = kodeInput.value || '';
        
        if (!kode) {
            // Gunakan kode sementara dari nama jika field kode kosong
            kode = generateKodeFromNama(nama);
        }
        
        const username = normalizeKaprodiUsernameFromKode(kode);
        
        // Hanya generate password baru jika belum pernah digenerate (untuk menjaga konsistensi visual)
        if (!isPasswordGenerated) {
            passPreview.textContent = randomPassword(8);
            isPasswordGenerated = true;
        }
        
        userPreview.textContent = username;
    }

    // --- EVENT LISTENERS ---
    
    // 1. Toggle Checkbox
    chk.addEventListener('change', function(){
        if(chk.checked){ 
            previewBox.style.display = 'block'; 
            updatePreview(); 
        }
        else {
            previewBox.style.display = 'none';
        }
    });

    // 2. Update Preview on Input
    namaInput.addEventListener('input', function(){ if(chk.checked) updatePreview(); });
    kodeInput.addEventListener('input', function(){ if(chk.checked) updatePreview(); });
    
    // 3. Initial Check (Jika form di-load dengan old('create_kaprodi') true)
    if (chk.checked) {
        previewBox.style.display = 'block';
        updatePreview();
    }
});
</script>
@endpush

@section('styles')
<style>
    .page-wrap { font-family: 'Inter', sans-serif; }
    
    .form-label-custom {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: #475569; /* Slate 600 */
        margin-bottom: 0.5rem;
    }
    .form-input-custom {
        display: block;
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
        color: #1e293b;
        background-color: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-input-custom:focus {
        border-color: #3b82f6;
        outline: 0;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
    .is-invalid-custom {
        border-color: #f43f5e; /* Rose 500 */
    }

    .form-checkbox-custom {
        /* Custom styling for checkbox */
        width: 1.25rem;
        height: 1.25rem;
        border-radius: 0.25rem;
        border: 1px solid #94a3b8;
        background-color: white;
        transition: all 0.2s;
        cursor: pointer;
    }
    .form-checkbox-custom:checked {
        background-color: #4f46e5;
        border-color: #4f46e5;
    }
</style>
@endsection