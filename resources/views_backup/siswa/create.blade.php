@extends('layouts.app')

@section('content')

{{-- 1. TAILWIND CONFIG & SETUP --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#4f46e5',
                    slate: { 800: '#1e293b', 900: '#0f172a' }
                }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

<div class="page-container p-4">
    
    {{-- Quick Actions Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-3 pb-1 border-b border-gray-200">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Tambah Siswa Baru</h1>
            <p class="text-sm text-gray-500 mt-1">Pastikan NISN valid dan belum terdaftar sebelumnya.</p>
        </div>
        
        <div class="flex flex-wrap gap-2 mt-3 sm:mt-0">
            <a href="{{ route('siswa.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-200 transition-all flex items-center gap-2 no-underline">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('siswa.bulk-create') }}" class="px-4 py-2 bg-indigo-700 text-white text-sm font-bold rounded-xl hover:bg-blue-600 shadow-lg shadow-blue-200 transition-all flex items-center gap-2 no-underline">
                <i class="fas fa-copy"></i> Tambah Banyak
            </a>
        </div>
    </div>
    
    <form action="{{ route('siswa.store') }}" method="POST">
        @csrf
        
        <div class="row">
            <div class="col-lg-8">
                
                {{-- Alert Error --}}
                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded-r">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700 font-bold">Gagal menyimpan data:</p>
                                <ul class="list-disc list-inside text-xs text-red-600 mt-1">
                                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Card 1: Data Siswa --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-6 overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                        <h3 class="text-sm font-bold text-slate-700 m-0 uppercase tracking-wide flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-primary"></span>
                            1. Data Siswa
                        </h3>
                    </div>
                    <div class="p-6">
                        
                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 mb-5 text-xs text-blue-700">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>Catatan:</strong> Pastikan NISN valid dan belum terdaftar sebelumnya.
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            {{-- NISN --}}
                            <div class="form-group mb-0">
                                <label class="form-label-modern">NISN <span class="text-red-500">*</span></label>
                                <input type="text" name="nisn" class="form-input-modern w-full @error('nisn') border-red-500 @enderror" 
                                       placeholder="Nomor Induk Siswa Nasional" value="{{ old('nisn') }}" required
                                       pattern="[0-9]{8,}" title="NISN harus numeric minimal 8 digit">
                                @error('nisn') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Nama Lengkap --}}
                            <div class="form-group mb-0">
                                <label class="form-label-modern">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" name="nama_siswa" class="form-input-modern w-full @error('nama_siswa') border-red-500 @enderror" 
                                       placeholder="Sesuai Ijazah/Rapor" value="{{ old('nama_siswa') }}" required>
                                @error('nama_siswa') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            
                            {{-- Kelas --}}
                            <div class="form-group mb-0">
                                <label class="form-label-modern">Kelas <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select name="kelas_id" class="form-input-modern w-full appearance-none pr-8 @error('kelas_id') border-red-500 @enderror" required>
                                        <option value="">-- Pilih Kelas --</option>
                                        @foreach($kelas as $k)
                                            <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                                {{ $k->nama_kelas }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                                @error('kelas_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Nomor HP Wali Murid --}}
                            <div class="form-group mb-0">
                                <label class="form-label-modern text-emerald-600">
                                    <i class="fab fa-whatsapp mr-1"></i> Nomor HP Wali Murid (WA)
                                </label>
                                <input type="text" name="nomor_hp_wali_murid" class="form-input-modern w-full border-emerald-300 focus:border-emerald-500 @error('nomor_hp_wali_murid') border-red-500 @enderror" 
                                       placeholder="Contoh: 081234567890" value="{{ old('nomor_hp_wali_murid') }}">
                                <p class="text-xs text-slate-400 mt-1">Wajib diisi untuk fitur notifikasi otomatis.</p>
                                @error('nomor_hp_wali_murid') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Akun Wali Murid --}}
                <div class="bg-white rounded-2xl shadow-sm border border-blue-200 mb-6 overflow-hidden">
                    <div class="bg-blue-50 px-6 py-4 border-b border-blue-100">
                        <h3 class="text-sm font-bold text-blue-800 m-0 uppercase tracking-wide flex items-center gap-2">
                            <i class="fas fa-user-friends text-blue-600"></i>
                            2. Akun Login Wali Murid (Opsional)
                        </h3>
                    </div>
                    <div class="p-6">
                        
                        <div class="form-group mb-4">
                            <label class="form-label-modern text-blue-700">Pilih Akun Wali Murid yang Sudah Ada</label>
                            <select name="wali_murid_user_id" class="form-input-modern w-full border-blue-300 focus:border-blue-500 select2 @error('wali_murid_user_id') border-red-500 @enderror">
                                <option value="">-- Cari Nama Wali Murid --</option>
                                @foreach($waliMurid as $wali)
                                    <option value="{{ $wali->id }}" {{ old('wali_murid_user_id') == $wali->id ? 'selected' : '' }}>
                                        {{ $wali->nama }} ({{ $wali->username }})
                                    </option>
                                @endforeach
                            </select>
                            @error('wali_murid_user_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            
                            <p class="text-xs text-slate-500 mt-2">
                                Pilih akun jika Wali Murid sudah terdaftar. Jika tidak, centang opsi di bawah ini.
                            </p>
                        </div>

                        <div class="border-t border-slate-100 pt-4">
    <div class="bg-gradient-to-r from-slate-50 to-blue-50 p-4 rounded-xl border border-slate-200">
        <div class="flex items-start">
            <div class="flex items-center h-6">
                <input type="checkbox" class="w-5 h-5 text-indigo-600 bg-white border-slate-300 rounded focus:ring-indigo-500 focus:ring-2 cursor-pointer transition" 
                       id="create_wali" name="create_wali" value="1" {{ old('create_wali') ? 'checked' : '' }}>
            </div>
            <label class="ml-3 cursor-pointer" for="create_wali">
                <span class="text-sm font-bold text-slate-800 block">Buat akun Wali Murid otomatis</span>
                <span class="text-xs text-slate-500 mt-0.5 block">Sistem akan membuat username dan password baru secara otomatis</span>
            </label>
        </div>
        
        <div id="wali-preview" class="mt-4 p-4 border-2 border-indigo-300 rounded-xl bg-white shadow-sm" style="display:none;">
            <div class="flex items-center gap-2 mb-3 pb-2 border-b border-indigo-100">
                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-eye text-indigo-600"></i>
                </div>
                <strong class="text-sm text-indigo-900 font-bold">Preview Akun Baru</strong>
            </div>
            <div class="space-y-3">
                <div class="flex items-center">
                    <div class="w-24 flex items-center gap-2 text-xs font-bold text-slate-600">
                        <i class="fas fa-user text-indigo-500"></i> Username
                    </div>
                    <span id="wali-preview-username" class="font-mono font-bold text-indigo-700 bg-indigo-50 px-4 py-2 rounded-lg border border-indigo-200"></span>
                </div>
                <div class="flex items-center">
                    <div class="w-24 flex items-center gap-2 text-xs font-bold text-slate-600">
                        <i class="fas fa-lock text-amber-500"></i> Password
                    </div>
                    <span class="text-xs text-amber-600 bg-amber-50 px-4 py-2 rounded-lg border border-amber-200 italic">
                        <i class="fas fa-info-circle mr-1"></i>Akan ditampilkan setelah disimpan
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
                    </div>
                </div>

            </div>

            {{-- Sidebar Kanan --}}
            <div class="col-lg-4">
                
                {{-- Card Action --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-4 sticky top-6 z-10">
                    <div class="p-6">
                        <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                            <i class="fas fa-check-circle text-emerald-500"></i> Simpan Data
                        </h4>
                        <p class="text-xs text-slate-500 mb-6 leading-relaxed">
                            Pastikan semua data sudah terisi dengan benar sebelum menyimpan.
                        </p>
                        
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-indigo-200 transition-all transform active:scale-95 mb-3 flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i> Simpan Data Siswa
                        </button>
                        
                        <a href="{{ route('siswa.index') }}" class="w-full block text-center bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold py-3 px-4 rounded-xl transition-colors text-sm">
                            Batal
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            // --- LOGIC PREVIEW WALI ---
            const chk = document.getElementById('create_wali');
            const nisnInput = document.querySelector('input[name="nisn"]');
            const previewBox = document.getElementById('wali-preview');
            const userPreview = document.getElementById('wali-preview-username');

            if (chk) {
                function normalizeWaliUsername(nisn){
                    return 'wali.' + nisn.replace(/[^0-9]+/g, '');
                }

                function updatePreview(){
                    const nisn = nisnInput.value || '';
                    const username = normalizeWaliUsername(nisn);
                    userPreview.textContent = username;
                }

                function togglePreview() {
                    if(chk.checked){ 
                        previewBox.style.display = 'block'; 
                        updatePreview(); 
                    } else {
                        previewBox.style.display = 'none';
                    }
                }

                chk.addEventListener('change', togglePreview);
                nisnInput.addEventListener('input', function(){ if(chk.checked) updatePreview(); });
                
                togglePreview(); 
            }
            
            // --- SELECT2 INITIALIZATION ---
            $('.select2').select2({
                theme: 'default',
                width: '100%',
                placeholder: "-- Cari Nama Wali Murid --",
                allowClear: true
            });
        });
    </script>
    <script src="{{ asset('js/pages/siswa/create.js') }}"></script>
@endpush

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<style>
    /* Modern Form Styles - Consistent with users/edit.blade.php */
    
    .form-label-modern {
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 0.5rem;
        letter-spacing: 0.025em;
    }

    .form-input-modern {
        display: block;
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        line-height: 1.25;
        color: #1e293b;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-input-modern:focus {
        border-color: #6366f1;
        outline: 0;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .form-checkbox-modern {
        width: 1.25rem;
        height: 1.25rem;
        border-radius: 0.375rem;
        border: 1px solid #94a3b8;
        background-color: white;
        transition: all 0.2s;
        cursor: pointer;
    }
    
    .form-checkbox-modern:checked {
        background-color: #4f46e5;
        border-color: #4f46e5;
    }

    /* SELECT2 Custom Styles */
    .select2-container--default .select2-selection--single {
        padding: 0.75rem 1rem !important;
        height: auto !important; 
        border-radius: 0.75rem !important; 
        border: 1px solid #e2e8f0 !important;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .select2-container--default .select2-selection--single:focus-within {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1) !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.25 !important; 
        padding-left: 0 !important;
        color: #1e293b;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100% !important;
        top: 0 !important;
        right: 0.75rem !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #94a3b8;
    }
</style>
@endsection