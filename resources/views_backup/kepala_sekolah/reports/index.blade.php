@extends('layouts.app')

@section('content')

<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: { colors: { primary: '#4f46e5', slate: { 800: '#1e293b', 900: '#0f172a' } } }
        },
        corePlugins: { preflight: false }
    }
</script>

<div class="page-container p-4">
    
    <!-- Header -->
    <div class="mb-3 border-b border-slate-200 pb-1">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-3">
                    <i class="fas fa-file-excel text-emerald-600"></i>
                    Laporan & Ekspor
                </h1>
                <p class="text-slate-500 text-sm mt-1">Buat dan unduh laporan data pelanggaran, siswa, dan tindakan lanjut.</p>
            </div>
            <a href="{{ route('dashboard.kepsek') }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold py-2 px-4 rounded-xl transition-colors text-sm flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <form action="{{ route('kepala-sekolah.reports.preview') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-lg-8">
                
                <!-- Card 1: Jenis Laporan -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-6 overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                        <h3 class="text-sm font-bold text-slate-700 m-0 uppercase tracking-wide flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-primary"></span>
                            1. Pilih Jenis Laporan
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="form-group mb-0">
                            <label class="form-label-modern">Jenis Laporan <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="report_type" name="report_type" class="form-input-modern w-full appearance-none pr-8" required>
                                    <option value="">-- Pilih Jenis Laporan --</option>
                                    <option value="pelanggaran">📋 Laporan Pelanggaran</option>
                                    <option value="siswa">👤 Laporan Siswa Bermasalah</option>
                                    <option value="tindakan">⚡ Laporan Tindakan Lanjut</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Filter Data -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-6 overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                        <h3 class="text-sm font-bold text-slate-700 m-0 uppercase tracking-wide flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            2. Filter & Periode
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Jurusan -->
                            <div class="form-group mb-0">
                                <label class="form-label-modern">Jurusan <span class="text-slate-400 text-xs font-normal">(Opsional)</span></label>
                                <div class="relative">
                                    <select id="jurusan_id" name="jurusan_id" class="form-input-modern w-full appearance-none pr-8">
                                        <option value="">-- Semua Jurusan --</option>
                                        @foreach($jurusans as $j)
                                            <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Kelas -->
                            <div class="form-group mb-0">
                                <label class="form-label-modern">Kelas <span class="text-slate-400 text-xs font-normal">(Opsional)</span></label>
                                <div class="relative">
                                    <select id="kelas_id" name="kelas_id" class="form-input-modern w-full appearance-none pr-8">
                                        <option value="">-- Semua Kelas --</option>
                                        @foreach($kelas as $k)
                                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Dari Tanggal -->
                            <div class="form-group mb-0">
                                <label class="form-label-modern">Dari Tanggal <span class="text-slate-400 text-xs font-normal">(Opsional)</span></label>
                                <input type="date" id="periode_mulai" name="periode_mulai" class="form-input-modern w-full">
                            </div>

                            <!-- Sampai Tanggal -->
                            <div class="form-group mb-0">
                                <label class="form-label-modern">Sampai Tanggal <span class="text-slate-400 text-xs font-normal">(Opsional)</span></label>
                                <input type="date" id="periode_akhir" name="periode_akhir" class="form-input-modern w-full">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                
                <!-- Info Cards -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl shadow-sm border border-blue-100 mb-4 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start gap-3 mb-4">
                            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-600"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-blue-900 mb-1">Panduan Penggunaan</h4>
                                <p class="text-xs text-blue-700 leading-relaxed">
                                    Pilih jenis laporan dan atur filter sesuai kebutuhan. Klik tombol <strong>Pratinjau</strong> untuk melihat data sebelum mengunduh.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl shadow-sm border border-amber-100 mb-4 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-download text-amber-600"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-amber-900 mb-1">Format Export</h4>
                                <p class="text-xs text-amber-700 leading-relaxed">
                                    Laporan dapat diunduh dalam format <strong>CSV</strong> (Excel) atau <strong>PDF</strong> untuk dokumentasi.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 sticky top-6 z-10">
                    <div class="p-6">
                        <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                            <i class="fas fa-search text-emerald-500"></i> Generate Laporan
                        </h4>
                        <p class="text-xs text-slate-500 mb-6 leading-relaxed">
                            Pastikan filter sudah sesuai sebelum membuat pratinjau laporan.
                        </p>
                        
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-blue-200 transition-all transform active:scale-95 mb-3 flex items-center justify-center gap-2">
                            <i class="fas fa-search"></i> Pratinjau Laporan
                        </button>
                        
                        <button type="reset" class="w-full bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold py-3 px-4 rounded-xl transition-colors text-sm flex items-center justify-center gap-2">
                            <i class="fas fa-redo"></i> Reset Filter
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </form>

</div>

@endsection

@section('styles')
<style>
    /* Styling Manual untuk komponen form yang Modern & Clean */
    
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

    .page-container {
        max-width: 1400px;
        margin: 0 auto;
    }
</style>
@endsection