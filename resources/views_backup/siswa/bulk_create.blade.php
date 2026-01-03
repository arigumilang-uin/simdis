@extends('layouts.app')

@section('content')

{{-- 1. TAILWIND CONFIG --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#2563eb', // Blue 600
                    indigo: { 50: '#eef2ff', 100: '#e0e7ff', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca' },
                    slate: { 800: '#1e293b' }
                },
                boxShadow: { 'soft': '0 4px 10px rgba(0,0,0,0.05)' }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

<div class="page-container p-4 md:p-6 bg-slate-50 min-h-screen font-['Inter']">
    <div class="max-w-7xl mx-auto">
        
        {{-- HEADER SECTION --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 border-b border-slate-200 pb-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 m-0 flex items-center gap-3">
                    <span class="p-2 bg-blue-50 text-blue-600 rounded-xl shadow-sm border border-blue-100">
                        <i class="fas fa-users-cog text-lg"></i>
                    </span>
                    Tambah Banyak Siswa Sekaligus
                </h1>
                <p class="text-slate-500 text-sm mt-1">Impor data siswa secara massal ke dalam satu kelas tujuan.</p>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('siswa.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-bold no-underline hover:bg-slate-50 transition-all shadow-sm">
                    <i class="fas fa-arrow-left text-xs"></i> Kembali
                </a>
                <a href="{{ route('siswa.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-bold no-underline hover:bg-blue-700 transition-all shadow-md shadow-blue-100">
                    <i class="fas fa-user-plus text-xs"></i> Tambah Satuan
                </a>
            </div>
        </div>

        <form action="{{ route('siswa.bulk-store') }}" method="POST" enctype="multipart/form-data" id="bulkCreateForm">
            @csrf

            {{-- ERROR ALERTS --}}
            @if(session('error') || session('bulk_errors'))
                <div class="mb-6 space-y-3">
                    @if(session('error'))
                    <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-xl shadow-sm">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-exclamation-circle text-rose-500"></i>
                            <p class="text-sm text-rose-700 font-bold m-0">{{ session('error') }}</p>
                        </div>
                    </div>
                    @endif

                    @if(session('bulk_errors'))
                    <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-xl shadow-sm">
                        <p class="text-sm text-amber-800 font-bold mb-2 flex items-center gap-2">
                            <i class="fas fa-database"></i> Baris bermasalah terdeteksi:
                        </p>
                        <ul class="list-disc list-inside text-xs text-amber-700 space-y-1">
                            @foreach(session('bulk_errors') as $be)
                                <li>{{ $be }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                {{-- KOLOM KIRI: KONFIGURASI DATA --}}
                <div class="lg:col-span-8 space-y-6">
                    
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="bg-slate-50/80 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                            <h3 class="text-sm font-black text-slate-700 m-0 uppercase tracking-widest flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                1. Konfigurasi Kelas
                            </h3>
                        </div>
                        <div class="p-6">
                            <label class="form-label-modern">Kelas Tujuan <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <select name="kelas_id" class="form-input-modern w-full appearance-none pr-10 cursor-pointer" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach(App\Models\Kelas::orderBy('nama_kelas')->get() as $k)
                                        <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-400">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="bg-slate-50/80 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                            <h3 class="text-sm font-black text-slate-700 m-0 uppercase tracking-widest flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                2. Input Data Manual
                            </h3>
                            <button type="button" id="addRowBtn" class="px-3 py-1 bg-blue-50 text-blue-600 border border-blue-100 rounded-lg text-[10px] font-black uppercase hover:bg-blue-600 hover:text-white transition-all">
                                <i class="fas fa-plus mr-1"></i> Tambah Baris
                            </button>
                        </div>
                        <div class="p-6">
                            <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-3 mb-6 flex items-start gap-3">
                                <i class="fas fa-lightbulb text-blue-500 mt-0.5"></i>
                                <p class="text-[11px] text-blue-700 leading-relaxed m-0 font-medium">
                                    <strong>Tips Pro:</strong> Anda bisa langsung melakukan <strong>Copy-Paste</strong> data dari Excel atau Google Sheets ke baris NISN pertama. Sistem akan otomatis membagi kolom.
                                </p>
                            </div>

                            {{-- CARD CONTAINER --}}
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse custom-solid-table" id="bulkTable">
            <thead>
                <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50 border-b border-slate-100">
                    <th class="px-6 py-4" style="width: 25%;">NISN</th>
                    <th class="px-6 py-4" style="width: 40%;">Nama Lengkap</th>
                    <th class="px-6 py-4" style="width: 25%;">No. HP Wali</th>
                    <th class="px-6 py-4 text-center" style="width: 10%;">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @php $initial = old('bulk_rows') ?? 5; @endphp
                @for($i = 0; $i < $initial; $i++)
                <tr class="hover:bg-slate-50/50 transition-colors group">
                    {{-- 1. NISN --}}
                    <td class="px-4 py-3">
                        <input type="text" 
                               name="nisn[]" 
                               class="form-input-clean bulk-nisn" 
                               placeholder="1234567890"
                               style="width: 100%;">
                    </td>

                    {{-- 2. NAMA LENGKAP --}}
                    <td class="px-4 py-3">
                        <input type="text" 
                               name="nama[]" 
                               class="form-input-clean bulk-nama" 
                               placeholder="Masukkan nama lengkap siswa"
                               style="width: 100%;">
                    </td>

                    {{-- 3. NO HP WALI --}}
                    <td class="px-4 py-3">
                        <input type="text" 
                               name="hp_wali[]" 
                               class="form-input-clean bulk-hp" 
                               placeholder="08123456789"
                               style="width: 100%;">
                    </td>

                    {{-- 4. AKSI (HAPUS BARIS) --}}
                    <td class="px-6 py-3 text-center">
                        <button type="button" 
                                class="remove-row w-8 h-8 inline-flex items-center justify-center rounded-lg text-slate-300 hover:text-rose-500 hover:bg-rose-50 transition-all border-none bg-transparent cursor-pointer">
                            <i class="fas fa-minus-circle text-lg"></i>
                        </button>
                    </td>
                </tr>
                @endfor
            </tbody>
        </table>
    </div>
</div>


                            <textarea name="bulk_data" id="bulk_data" class="hidden">{{ old('bulk_data') }}</textarea>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="bg-blue-50/50 px-6 py-4 border-b border-blue-100 flex items-center justify-between">
                            <h3 class="text-[10px] font-black text-blue-700 m-0 uppercase tracking-widest flex items-center gap-2 italic">
                                <i class="fas fa-file-excel"></i>
                                Alternatif: Impor File Spreadsheet
                            </h3>
                        </div>
                        <div class="p-6">
                            <input type="file" name="bulk_file" accept=".csv,.xlsx" 
                                   class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:uppercase file:bg-blue-600 file:text-white hover:file:bg-blue-700 file:cursor-pointer transition-all">
                            <p class="text-[10px] text-slate-400 mt-3 font-medium">
                                *Mendukung .CSV atau .XLSX. Format kolom harus: <strong>NISN, Nama Lengkap, Nomor HP Wali</strong>.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- KOLOM KANAN: SIDEBAR AKSI --}}
                <div class="lg:col-span-4 space-y-6">
                    
                    {{-- Card: Opsi Wali --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-start gap-4">
                                <div class="flex items-center h-6">
                                    <input id="create_wali_all" name="create_wali_all" type="checkbox" value="1" 
                                           class="w-5 h-5 text-blue-600 border-slate-300 rounded focus:ring-blue-500 cursor-pointer">
                                </div>
                                <label for="create_wali_all" class="cursor-pointer">
                                    <span class="block text-sm font-black text-slate-800 leading-tight">Buat Akun Wali</span>
                                    <span class="text-[11px] text-slate-500 mt-1 block leading-relaxed">Otomatis membuat akses akun wali untuk semua siswa baru.</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Action Button --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden sticky top-6">
                        <div class="p-6 bg-slate-900 text-white">
                            <h3 class="text-xs font-black uppercase tracking-widest m-0 flex items-center gap-2 italic">
                                <i class="fas fa-rocket text-blue-400"></i> Finalisasi
                            </h3>
                        </div>
                        <div class="p-6">
                            <p class="text-xs text-slate-500 mb-6 leading-relaxed">
                                Pastikan kelas tujuan dan format data di tabel sudah sesuai sebelum memproses pendaftaran massal.
                            </p>
                            
                            <button type="submit" id="bulkSubmitBtn" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-black text-[11px] uppercase tracking-widest shadow-lg shadow-blue-100 transition-all active:scale-95 flex items-center justify-center gap-2">
                                <i class="fas fa-cloud-upload-alt"></i> Proses Data Masal
                            </button>
                            
                            <a href="{{ route('siswa.index') }}" class="w-full mt-3 inline-flex justify-center items-center py-3 px-4 bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest no-underline transition-all">
                                Batalkan Prosedur
                            </a>
                        </div>
                    </div>
                    
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('styles')
<style>
    .form-label-modern {
        display: block;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 0.5rem;
        letter-spacing: 0.05em;
    }

    .form-input-modern {
        display: block;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        color: #1e293b;
        background-color: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        transition: all 0.2s;
    }

    .form-input-modern:focus {
        border-color: #2563eb;
        outline: 0;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }

    .form-input-table {
        width: 100%;
        background-color: transparent;
        border: 1px solid transparent;
        border-radius: 0.5rem;
        padding: 0.5rem;
        font-size: 0.875rem;
        color: #1e293b;
        transition: all 0.2s;
    }

    .form-input-table:focus {
        background-color: #fff;
        border-color: #2563eb;
        outline: 0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .page-container { max-width: 1400px; margin: 0 auto; }
</style>

{{-- CSS UNTUK INPUT FIELD AGAR BORDERLESS --}}
<style>
    .form-input-clean {
        background: transparent;
        border: 1px solid transparent;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 0.875rem; /* text-sm */
        color: #334155; /* slate-700 */
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .form-input-clean:hover {
        background-color: #f8fafc; /* slate-50 */
    }

    .form-input-clean:focus {
        outline: none;
        background-color: #ffffff;
        border-color: #e2e8f0; /* slate-200 */
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }

    .form-input-clean::placeholder {
        color: #cbd5e1; /* slate-300 */
        font-weight: 400;
    }
</style>
@endsection

@push('scripts')
    <script src="{{ asset('js/pages/siswa/bulk_create.js') }}"></script>
@endpush