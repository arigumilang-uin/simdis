@extends('layouts.app')

@section('content')

{{-- 1. TAILWIND CONFIG & SETUP --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#0f172a',
                    indigo: { 600: '#4f46e5', 50: '#eef2ff', 100: '#e0e7ff', 700: '#4338ca' },
                }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

<div class="page-wrap-custom min-h-screen p-6">
    <div class="max-w-5xl mx-auto">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 custom-header-row pb-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 m-0 tracking-tight flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-lg shadow-lg shadow-indigo-200">
                        <i class="fas fa-edit"></i>
                    </div>
                    Edit Catatan
                </h1>
                <p class="text-slate-500 text-sm mt-1 ml-14">Perbarui detail laporan pelanggaran anda.</p>
            </div>
            
            <a href="{{ route('my-riwayat.index') }}" class="btn-clean-action no-underline bg-white shadow-sm hover:shadow-md">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-in fade-in duration-500">
            
            {{-- SIDEBAR: INFO SISWA & STATUS --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 relative overflow-hidden">
                    
                    <div class="relative z-10">
                        
                        <span class="text-[10px] font-black uppercase tracking-widest text-indigo-500 block mb-1">Siswa Terkait</span>
                        <h2 class="text-xl font-bold text-slate-800 leading-tight mb-4">{{ $r->siswa?->nama }}</h2>
                        
                        <div class="pt-4 border-t border-slate-100">
                            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Status Bukti</div>
                            @if($r->bukti_foto_path)
                                <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100 flex items-center gap-3 group cursor-pointer transition-all hover:shadow-md" onclick="window.open('{{ route('bukti.show', $r->bukti_foto_path) }}', '_blank')">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-emerald-700">Tersedia</div>
                                        <div class="text-[10px] text-emerald-600">Klik untuk lihat</div>
                                    </div>
                                    <i class="fas fa-external-link-alt ml-auto text-emerald-400 group-hover:text-emerald-600"></i>
                                </div>
                            @else
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-slate-200 text-slate-400 flex items-center justify-center">
                                        <i class="fas fa-minus"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-500">Belum Ada</div>
                                        <div class="text-[10px] text-slate-400">Silahkan unggah foto</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- MAIN CONTENT: FORM EDIT --}}
            <div class="lg:col-span-2">
                <style>
                    /* Local Override for Compact Form */
                    .custom-label { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; display: block; margin-bottom: 0.35rem; }
                    .form-card { background: white; border-radius: 1rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
                </style>

                <form action="{{ route('my-riwayat.update', $r->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    @csrf
                    @method('PUT')
                    
                    <div class="p-6 md:p-8 space-y-5">
                        
                        <!-- Jenis Pelanggaran -->
                        <div>
                            <label for="jenis_pelanggaran_id" class="custom-label">Jenis Pelanggaran</label>
                            <div class="relative">
                                <select name="jenis_pelanggaran_id" id="jenis_pelanggaran_id" class="custom-select-clean w-full pl-10 appearance-none bg-slate-50 border-slate-200 focus:bg-white transition-colors h-11">
                                    @foreach($jenis as $j)
                                        <option value="{{ $j->id }}" {{ $r->jenis_pelanggaran_id == $j->id ? 'selected' : '' }}>
                                            {{ $j->nama_pelanggaran }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute left-3 top-0 bottom-0 flex items-center pointer-events-none text-slate-400">
                                    <i class="fas fa-exclamation-circle text-xs"></i>
                                </div>
                                <div class="absolute right-3 top-0 bottom-0 flex items-center pointer-events-none text-slate-400">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Date & Time Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="tanggal_kejadian" class="custom-label">Tanggal</label>
                                <div class="relative">
                                    <input type="date" name="tanggal_kejadian" id="tanggal_kejadian" 
                                           value="{{ optional($r->tanggal_kejadian)->format('Y-m-d') }}" 
                                           class="custom-input-clean w-full pl-10 bg-slate-50 border-slate-200 focus:bg-white h-11">
                                    <div class="absolute left-3 top-0 bottom-0 flex items-center pointer-events-none text-slate-400">
                                        <i class="far fa-calendar text-xs"></i>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label for="jam_kejadian" class="custom-label">Waktu</label>
                                <div class="relative">
                                    <input type="time" name="jam_kejadian" id="jam_kejadian" 
                                           value="{{ optional($r->tanggal_kejadian)->format('H:i') }}" 
                                           class="custom-input-clean w-full pl-10 bg-slate-50 border-slate-200 focus:bg-white h-11">
                                    <div class="absolute left-3 top-0 bottom-0 flex items-center pointer-events-none text-slate-400">
                                        <i class="far fa-clock text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div>
                            <label for="keterangan" class="custom-label">Keterangan Tambahan</label>
                            <textarea name="keterangan" id="keterangan" rows="4" 
                                      class="custom-input-clean w-full bg-slate-50 border-slate-200 focus:bg-white p-3 leading-relaxed" 
                                      placeholder="Deskripsikan detail kejadian pelanggaran...">{{ old('keterangan', $r->keterangan) }}</textarea>
                        </div>

                        <!-- Upload New Foto -->
                        <div class="pt-2">
                            <label for="bukti_foto" class="custom-label">
                                <i class="fas fa-cloud-upload-alt mr-1"></i> Perbarui Bukti (Opsional)
                            </label>
                            <input type="file" name="bukti_foto" id="bukti_foto" 
                                   class="block w-full text-xs text-slate-500
                                          file:mr-4 file:py-2.5 file:px-4
                                          file:rounded-xl file:border-0
                                          file:text-xs file:font-bold
                                          file:bg-indigo-50 file:text-indigo-700
                                          hover:file:bg-indigo-100
                                          cursor-pointer border border-slate-200 rounded-xl bg-slate-50">
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-4">
                        <span class="text-xs text-slate-400 hidden sm:inline">Pastikan data sudah benar sebelum menyimpan.</span>
                        <div class="flex items-center gap-3 ml-auto">
                            <a href="{{ route('my-riwayat.index') }}" class="btn-filter-secondary no-underline px-5 py-2.5 text-xs">Batal</a>
                            <button type="submit" class="btn-filter-primary px-6 py-2.5 shadow-lg shadow-indigo-200">
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection

@section('styles')
<style>
    /* --- CORE STYLING --- */
    .page-wrap-custom { background: #f8fafc; min-height: 100vh; padding: 1.5rem; font-family: 'Inter', sans-serif; }
    .custom-header-row { border-bottom: 1px solid #e2e8f0; }

    /* Form Controls */
    .custom-input-clean, .custom-select-clean {
        border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.65rem 1rem;
        font-size: 0.85rem; background: white; outline: none; transition: 0.2s;
    }
    .custom-input-clean:focus, .custom-select-clean:focus { 
        border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); 
    }

    .btn-filter-primary {
        background: #4f46e5; color: white; border: none; border-radius: 0.75rem; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; padding: 0.75rem 1.5rem; cursor: pointer; transition: 0.2s;
    }
    .btn-filter-primary:hover { background-color: #4338ca; transform: translateY(-1px); }

    .btn-filter-secondary {
        background: #f1f5f9; color: #64748b; border-radius: 0.75rem; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; padding: 0.75rem 1.5rem; border: none; cursor: pointer; transition: 0.2s;
    }
    .btn-filter-secondary:hover { background-color: #e2e8f0; color: #1e293b; }

    .btn-clean-action {
        padding: 0.65rem 1.2rem; border-radius: 0.75rem; background-color: #f1f5f9; color: #475569; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; transition: 0.2s; display: inline-flex; align-items: center; gap: 0.5rem; border: 1px solid #e2e8f0;
    }
    .btn-clean-action:hover { background-color: #e2e8f0; color: #1e293b; }

    /* Animasi */
    .animate-in { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection