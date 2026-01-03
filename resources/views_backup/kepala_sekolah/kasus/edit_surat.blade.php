@extends('layouts.app')

@section('content')

{{-- Tailwind Config --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#0f172a',
                    indigo: { 600: '#4f46e5', 50: '#eef2ff', 100: '#e0e7ff', 700: '#4338ca' },
                    emerald: { 50: '#ecfdf5', 100: '#d1fae5', 600: '#059669', 700: '#047857' }
                }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

<div class="page-wrap-custom min-h-screen p-6 bg-slate-50">
    <div class="max-w-4xl mx-auto">
        
        {{-- Header --}}
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-200">
            <div>
                <div class="text-[10px] font-black uppercase tracking-[0.2em] bg-amber-50 px-2 py-0.5 rounded border border-amber-100 text-amber-600 mb-2 inline-block">Edit Surat</div>
                <h1 class="text-2xl font-bold text-slate-800 m-0 tracking-tight flex items-center gap-3">
                    <i class="fas fa-edit text-amber-600"></i> Edit Isi Surat Panggilan
                </h1>
            </div>
            <a href="javascript:history.back()" class="px-4 py-2 rounded-lg bg-white text-slate-600 text-xs font-bold border border-slate-200 hover:bg-slate-50 no-underline">
                <i class="fas fa-arrow-left mr-1"></i> Batal
            </a>
        </div>

        {{-- Info Alert --}}
        <div class="mb-6 p-4 rounded-xl bg-blue-50 border border-blue-200">
            <div class="flex items-start gap-3">
                <i class="fas fa-info-circle text-blue-600 mt-1"></i>
                <div class="flex-1">
                    <div class="text-sm font-bold text-blue-900 mb-1">Perhatian</div>
                    <div class="text-xs text-blue-700 leading-relaxed">
                        Anda dapat mengedit isi surat jika sistem menghasilkan data yang kurang akurat. Pastikan semua informasi sudah benar sebelum mencetak.
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Card --}}
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
            <div class="px-8 py-6 bg-gradient-to-r from-amber-50 to-orange-50 border-b border-amber-100">
                <h2 class="text-lg font-black text-amber-900 m-0">Form Edit Surat</h2>
                <p class="text-xs text-amber-600 mt-1 m-0">Edit field yang perlu diubah</p>
            </div>
            
            <form action="{{ route('tindak-lanjut.update-surat', $kasus->id) }}" method="POST" class="p-8 space-y-6">
                @csrf
                @method('PUT')

                {{-- Siswa Info (Read-only) --}}
                <div class="p-5 rounded-xl bg-slate-50 border border-slate-200">
                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-3">Siswa Bersangkutan</label>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-lg font-black">
                            {{ substr($kasus->siswa->nama_siswa, 0, 1) }}
                        </div>
                        <div>
                            <div class="font-bold text-slate-800">{{ $kasus->siswa->nama_siswa }}</div>
                            <div class="text-xs text-slate-500">{{ $kasus->siswa->kelas->nama_kelas ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Nomor Surat --}}
                <div>
                    <label for="nomor_surat" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">
                        Nomor Surat <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="nomor_surat" 
                           id="nomor_surat" 
                           value="{{ old('nomor_surat', $surat->nomor_surat) }}"
                           class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none text-sm font-mono transition-all"
                           placeholder="Contoh: 259/421.5-SMKN 1 LD/2025"
                           required>
                    @error('nomor_surat')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Lampiran & Hal --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="lampiran" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">
                            Lampiran
                        </label>
                        <input type="text" 
                               name="lampiran" 
                               id="lampiran" 
                               value="{{ old('lampiran', $surat->lampiran ?? '-') }}"
                               class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none text-sm transition-all"
                               placeholder="Contoh: - (atau 1 Lembar)">
                        @error('lampiran')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="hal" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">
                            Hal <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="hal" 
                               id="hal" 
                               value="{{ old('hal', $surat->hal ?? 'Panggilan Orang Tua / Wali Murid') }}"
                               class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none text-sm transition-all"
                               placeholder="Contoh: Panggilan Orang Tua / Wali Murid"
                               required>
                        @error('hal')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Tanggal & Waktu Pertemuan --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="tanggal_pertemuan" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">
                            Tanggal Pertemuan <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               name="tanggal_pertemuan" 
                               id="tanggal_pertemuan" 
                               value="{{ old('tanggal_pertemuan', \Carbon\Carbon::parse($surat->tanggal_pertemuan)->format('Y-m-d')) }}"
                               class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none text-sm transition-all"
                               required>
                        @error('tanggal_pertemuan')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="waktu_pertemuan" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">
                            Waktu Pertemuan <span class="text-red-500">*</span>
                        </label>
                        <input type="time" 
                               name="waktu_pertemuan" 
                               id="waktu_pertemuan" 
                               value="{{ old('waktu_pertemuan', $surat->waktu_pertemuan) }}"
                               class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none text-sm transition-all"
                               required>
                        @error('waktu_pertemuan')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Tempat Pertemuan --}}
                <div>
                    <label for="tempat_pertemuan" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">
                        Tempat Pertemuan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="tempat_pertemuan" 
                           id="tempat_pertemuan" 
                           value="{{ old('tempat_pertemuan', $surat->tempat_pertemuan ?? 'Ruang BK SMK Negeri 1 Lubuk Dalam') }}"
                           class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none text-sm transition-all"
                           placeholder="Contoh: Ruang BK SMK Negeri 1 Lubuk Dalam"
                           required>
                    @error('tempat_pertemuan')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Keperluan --}}
                <div>
                    <label for="keperluan" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">
                        Keperluan / Pemicu <span class="text-red-500">*</span>
                    </label>
                    <textarea name="keperluan" 
                              id="keperluan" 
                              rows="4"
                              class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none text-sm transition-all resize-none"
                              placeholder="Jelaskan keperluan pemanggilan..."
                              required>{{ old('keperluan', $surat->keperluan) }}</textarea>
                    @error('keperluan')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-slate-400 mt-2">
                        <i class="fas fa-lightbulb mr-1"></i>
                        Contoh: Terlambat 3x dalam 1 minggu
                    </p>
                </div>

                {{-- Actions --}}
                <div class="flex gap-4 pt-4 border-t border-slate-200">
                    <button type="submit" 
                            class="flex-1 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-sm uppercase tracking-wider transition-all shadow-lg transform hover:-translate-y-0.5">
                        <i class="fas fa-save mr-2"></i>
                        Simpan Perubahan
                    </button>
                    <a href="javascript:history.back()" 
                       class="px-8 py-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold text-sm uppercase tracking-wider transition-all no-underline">
                        Batal
                    </a>
                </div>
            </form>
        </div>

    </div>
</div>

<style>
    .page-wrap-custom { font-family: 'Inter', sans-serif; }
</style>

@endsection
