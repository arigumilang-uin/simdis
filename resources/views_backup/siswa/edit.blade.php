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
                    indigo: { 600: '#4f46e5' },
                    info: { 500: '#3b82f6' } 
                },
                boxShadow: { 'soft': '0 4px 10px rgba(0,0,0,0.05)' }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

<div class="page-wrap bg-gray-50 min-h-screen p-4 sm:p-6">
    
    <div class="max-w-4xl mx-auto">
        
        @php 
            $isWaliKelas = Auth::user()->hasRole('Wali Kelas');
            $primaryColorClass = $isWaliKelas ? 'bg-info-500 hover:bg-info-600 shadow-info-200' : 'bg-amber-500 hover:bg-amber-600 shadow-amber-200'; 
            $readOnlyAttr = $isWaliKelas ? 'readonly' : '';
            $headerText = $isWaliKelas ? 'Update Kontak Wali Murid' : 'Edit Data Induk Siswa';
        @endphp

        <div class="flex justify-between items-center mb-3 pb-1 border-b border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $headerText }}</h1>
                <p class="text-sm text-gray-500 mt-1">Perbarui detail untuk siswa: <span class="font-semibold text-slate-800">{{ $siswa->nama_siswa }}</span></p>
            </div>
            
            <a href="{{ route('siswa.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-200 transition-all flex items-center gap-2 no-underline">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                 <h3 class="text-base font-bold text-slate-700 m-0">Formulir Perubahan Data</h3>
            </div>
            
            <form action="{{ route('siswa.update', $siswa->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="p-6 space-y-4 bg-slate-50/50">
                    
                    @if($isWaliKelas)
                    <div class="bg-info-50 border border-info-200 text-info-700 px-4 py-3 rounded-xl mb-4 text-sm shadow-sm flex items-start gap-4">
                        <i class="fas fa-info-circle text-info-500 text-xl shrink-0 mt-0.5"></i> 
                        <div>
                            <strong class="block font-semibold">Info Akses:</strong>
                            <p class="text-sm">Sebagai Wali Kelas, Anda **hanya diizinkan** mengubah **Nomor HP Wali Murid**. Untuk perbaikan data inti (Nama/NISN/Kelas), silakan hubungi Operator Sekolah.</p>
                        </div>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                        
                        <div>
                            <label class="form-label-custom">NISN</label>
                            <div class="relative">
                                <input type="text" name="nisn" class="form-input-custom {{ $readOnlyAttr ? 'bg-gray-100 text-gray-600 cursor-not-allowed' : '' }}" 
                                       value="{{ $siswa->nisn }}" {{ $readOnlyAttr }} required>
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"><i class="fas fa-id-card text-sm"></i></div>
                            </div>
                        </div>

                        <div>
                            <label class="form-label-custom">Nama Lengkap</label>
                            <div class="relative">
                                <input type="text" name="nama_siswa" class="form-input-custom {{ $readOnlyAttr ? 'bg-gray-100 text-gray-600 cursor-not-allowed' : '' }}" 
                                       value="{{ $siswa->nama_siswa }}" {{ $readOnlyAttr }} required>
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"><i class="fas fa-user text-sm"></i></div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="form-label-custom">Kelas</label>
                            @if($isWaliKelas)
                                {{-- Wali Kelas: Readonly Input --}}
                                <div class="relative">
                                    <input type="text" 
                                           class="form-input-custom bg-gray-100 text-gray-600 cursor-not-allowed" 
                                           value="{{ $siswa->kelas->nama_kelas }}" 
                                           readonly>
                                    <input type="hidden" name="kelas_id" value="{{ $siswa->kelas_id }}">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                        <i class="fas fa-chalkboard text-sm"></i>
                                    </div>
                                </div>
                            @else
                                {{-- Operator: Select2 Dropdown --}}
                                <div class="relative">
                                    <select 
                                        name="kelas_id" 
                                        class="form-input-custom w-full select2-no-icon" 
                                        required 
                                        data-placeholder="-- Pilih Kelas --"
                                    >
                                        <option value=""></option>
                                        @foreach($kelas as $k)
                                            <option value="{{ $k->id }}" {{ $siswa->kelas_id == $k->id ? 'selected' : '' }}>
                                                {{ $k->nama_kelas }}
                                            </option>
                                        @endforeach
                                    </select>
                                    </div>
                            @endif
                        </div>

                        <div>
                            <label class="form-label-custom text-info-500">Nomor HP Wali Murid (WA)</label>
                            <div class="relative">
                                <input type="text" name="nomor_hp_wali_murid" class="form-input-custom border-info-300 focus:border-info-500" 
                                       value="{{ $siswa->nomor_hp_wali_murid }}" placeholder="Contoh: 081234567890">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 text-emerald-500"><i class="fab fa-whatsapp text-sm"></i></div>
                            </div>
                             <p class="text-xs text-slate-400 mt-1 ml-1">Pastikan nomor aktif (WhatsApp).</p>
                        </div>
                    </div>
                    
                    @if(!$isWaliKelas)
                    <div class="pt-4 mt-6 border-t border-slate-200">
                        <div class="border border-slate-200 bg-white rounded-xl p-4 shadow-sm">
                             <label class="form-label-custom mb-2 flex items-center gap-2 text-indigo-700">
                                 <i class="fas fa-key text-indigo-500"></i> Akun Login Wali Murid
                             </label>

                            <select 
                                name="wali_murid_user_id" 
                                class="form-input-custom w-full select2-no-icon" 
                                data-placeholder="-- Pilih Akun --"
                            >
                                <option value=""></option>
                                @foreach($waliMurid as $wali)
                                    <option value="{{ $wali->id }}" {{ $siswa->wali_murid_user_id == $wali->id ? 'selected' : '' }}>
                                        {{ $wali->nama }} ({{ $wali->username }})
                                    </option>
                                @endforeach
                            </select>
                            
                            <p class="text-xs text-slate-500 mt-2 ml-1">
                                Hubungkan siswa ini dengan akun login aplikasi Wali Murid yang sudah terdaftar (opsional).
                            </p>
                        </div>
                    </div>
                    @endif

                </div>

                <div class="px-6 py-4 bg-white border-t border-slate-200 flex justify-end space-x-3">
                    <a href="{{ route('siswa.index') }}" class="px-5 py-2 text-sm font-bold text-gray-700 hover:text-gray-900 bg-white border border-gray-300 rounded-xl shadow-sm hover:bg-gray-100 transition no-underline">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2 text-white text-sm font-bold rounded-xl {{ $primaryColorClass }} transition-all transform active:scale-95 flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<style>
    /* 1. SELECT2 CUSTOM STYLES */
    .select2-container--default .select2-selection--single {
        padding-top: 0.75rem !important;
        padding-bottom: 0.75rem !important;
        height: auto !important; 
        border-radius: 0.75rem !important; 
        border: 1px solid #e2e8f0 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1 !important; 
        padding-left: 1rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100% !important;
        top: 0 !important;
    }

    /* 2. BASE FORM STYLES */
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
        /* Default padding for inputs/selects that don't need left icon */
        padding-left: 1rem; 
    }
    .form-input-custom:focus {
        border-color: #3b82f6;
        outline: 0;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
    
    /* Ensure icon padding is applied only when needed */
    .form-input-with-icon {
        padding-left: 2.5rem !important;
    }
</style>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            
            // --- INITIALIZATION ---
            $('.select2').select2({
                theme: 'default',
                width: '100%' 
            });
            
            // Apply custom styling to Select2 container
            $('.select2-container--default .select2-selection--single').addClass('form-input-custom');
            
            // --- LOGIC & ICON INJECTION ---
            const isWaliKelas = @json($isWaliKelas);
            
            // 1. Core Data Icons (NISN, Nama, Kelas Readonly)
            const inputIconMap = {
                'input[name="nisn"]': { icon: 'fas fa-id-card', color: 'gray-400' },
                'input[name="nama_siswa"]': { icon: 'fas fa-user', color: 'gray-400' },
                'input[name="nomor_hp_wali_murid"]': { icon: 'fab fa-whatsapp', color: 'emerald-500' },
                // Kelas readonly is complex due to structure, target directly
            };

            $.each(inputIconMap, function(selector, data) {
                const input = $(selector);
                if (input.length) {
                    // Check if input is NOT Select2
                    if (!input.hasClass('select2')) {
                        input.addClass('form-input-with-icon');
                        
                        // Manually prepend the icon container to the parent div
                        const iconHtml = `<div class="absolute inset-y-0 left-0 flex items-center pl-3 text-${data.color}"><i class="${data.icon} text-sm"></i></div>`;
                        input.parent().prepend(iconHtml);

                        // Fix for Kelas Readonly (needs icon padding if present)
                        if (selector.includes('kelas_id') && isWaliKelas) {
                           input.addClass('form-input-with-icon');
                           const kelasIconHtml = `<div class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"><i class="fas fa-chalkboard text-sm"></i></div>`;
                           input.parent().prepend(kelasIconHtml);
                        }
                    }
                }
            });
            
            // 2. Fix Kelas Select2 Alignment for non-Wali Kelas
            if (!isWaliKelas) {
                 const selectKelas = $('select[name="kelas_id"]');
                 selectKelas.on('select2:open', function() {
                    // Remove left padding added by Select2 if it's there
                    $('.select2-container--default .select2-selection--single').removeClass('form-input-with-icon');
                 });
            }

            // 3. Wali Kelas Button Text Change
            if (isWaliKelas) {
                const submitButton = $('button[type="submit"]');
                submitButton.html('<i class="fas fa-save"></i> Update Kontak Wali');
            }
        });
    </script>
    {{-- Memastikan file JS lokal Anda tetap dipanggil --}}
    <script src="{{ asset('js/pages/siswa/edit.js') }}"></script>
@endpush