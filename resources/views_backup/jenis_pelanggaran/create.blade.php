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
                    indigo: { 600: '#4f46e5' } // Primary/Save color
                },
                boxShadow: { 'soft': '0 4px 10px rgba(0,0,0,0.05)' }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

<div class="page-wrap bg-gray-50 min-h-screen p-4 sm:p-6">
    
    <div class="max-w-3xl mx-auto">
        
        <div class="flex justify-between items-center mb-3 pb-1 border-b border-gray-200">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Tambah Jenis Pelanggaran Baru</h1>
                <p class="text-sm text-gray-500 mt-1">Buat aturan kedisiplinan baru di sistem.</p>
            </div>
            
            <a href="{{ route('jenis-pelanggaran.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-bold rounded-xl hover:bg-slate-200 transition-all flex items-center gap-2 no-underline">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-indigo-50/50">
                 <h3 class="text-base font-bold text-slate-700 m-0 flex items-center gap-2">
                     Informasi Dasar Pelanggaran
                 </h3>
            </div>
            
            <form action="{{ route('jenis-pelanggaran.store') }}" method="POST">
                @csrf
                
                <div class="p-6 space-y-5 bg-slate-50/50">
                    
                    <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl text-sm shadow-sm">
                        <strong class="block mb-1">Workflow:</strong>
                        <ul class="list-disc list-inside space-y-1 ml-4 text-xs">
                            <li>Isi form ini untuk membuat jenis pelanggaran baru</li>
                            <li>Setelah disimpan, Anda akan diarahkan ke halaman **Kelola Rules**</li>
                            <li>Di halaman Kelola Rules, atur: **Frekuensi, Poin, Sanksi, Trigger Surat, Pembina**</li>
                        </ul>
                    </div>

                    <div>
                        <label for="nama_pelanggaran" class="form-label-custom">Nama Pelanggaran <span class="text-rose-500">*</span></label>
                        <input type="text" id="nama_pelanggaran" name="nama_pelanggaran" 
                               class="form-input-modern w-full @error('nama_pelanggaran') is-invalid-custom @enderror" 
                               placeholder="Contoh: Rambut tidak sesuai (3-2-1, diwarnai, crop)"
                               required value="{{ old('nama_pelanggaran') }}" autofocus>
                        @error('nama_pelanggaran') <p class="text-rose-500 text-xs mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label for="kategori_id" class="form-label-custom">Kategori Pelanggaran <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <select id="kategori_id" name="kategori_id" 
                                    class="form-input-modern w-full appearance-none pr-8 bg-white @error('kategori_id') is-invalid-custom @enderror" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategori as $k)
                                    <option value="{{ $k->id }}" 
                                            {{ old('kategori_id') == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                        @error('kategori_id') <p class="text-rose-500 text-xs mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label for="filter_category" class="form-label-custom">Filter Kategori <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <div class="relative">
                            <select id="filter_category" name="filter_category" 
                                    class="form-input-modern w-full appearance-none pr-8 bg-white @error('filter_category') is-invalid-custom @enderror">
                                <option value="">-- Tidak ada filter --</option>
                                <option value="atribut" {{ old('filter_category') == 'atribut' ? 'selected' : '' }}>Atribut/Seragam</option>
                                <option value="absensi" {{ old('filter_category') == 'absensi' ? 'selected' : '' }}>Absensi/Kehadiran</option>
                                <option value="kerapian" {{ old('filter_category') == 'kerapian' ? 'selected' : '' }}>Kerapian/Kebersihan</option>
                                <option value="ibadah" {{ old('filter_category') == 'ibadah' ? 'selected' : '' }}>Ibadah/Agama</option>
                                <option value="berat" {{ old('filter_category') == 'berat' ? 'selected' : '' }}>Berat/Kejahatan</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                        <small class="text-slate-500 text-xs mt-1 block">Filter untuk memudahkan pencarian saat catat pelanggaran.</small>
                        @error('filter_category') <p class="text-rose-500 text-xs mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="keywords" class="form-label-custom">Alias / Keywords <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <textarea id="keywords" name="keywords" 
                                  class="form-input-modern w-full h-24 @error('keywords') is-invalid-custom @enderror" 
                                  rows="2" 
                                  placeholder="Contoh: Rambut panjang, Rambut gondrong, Rambut dicat">{{ old('keywords') }}</textarea>
                        <small class="text-slate-500 text-xs mt-1 block">
                            Kata kunci alternatif untuk memudahkan pencarian. Pisahkan dengan koma atau enter.
                        </small>
                        @error('keywords') <p class="text-rose-500 text-xs mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>

                </div>

                <div class="px-6 py-4 bg-white border-t border-slate-200 flex justify-end space-x-3">
                    <a href="{{ route('jenis-pelanggaran.index') }}" class="px-5 py-2 text-sm font-bold text-gray-700 hover:text-gray-900 bg-white border border-gray-300 rounded-xl shadow-sm hover:bg-gray-100 transition no-underline">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2 text-white text-sm font-bold rounded-xl bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all transform active:scale-95 flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan & Lanjut ke Kelola Rules
                    </button>
                </div>
            </form>
        </div>
        
    </div>
</div>
@endsection

@section('styles')
<style>
    /* 1. BASE FORM STYLES */
    .page-wrap { font-family: 'Inter', sans-serif; }
    
    .form-label-custom {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: #475569; /* Slate 600 */
        margin-bottom: 0.5rem;
    }
    .form-input-modern {
        display: block;
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
        line-height: 1.25;
        color: #1e293b;
        background-color: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        transition: border-color 0.2s, box-shadow 0.2s;
        /* Ensure consistency for selects/textareas */
        resize: vertical;
    }
    .form-input-modern:focus {
        border-color: #3b82f6;
        outline: 0;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
    
    /* Error State */
    .is-invalid-custom {
        border-color: #f43f5e !important;
    }
</style>
@endsection