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
    <div class="max-w-7xl mx-auto">
        
        {{-- Header --}}
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-200">
            <div>
                <div class="text-[10px] font-black uppercase tracking-[0.2em] bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100 text-indigo-600 mb-2 inline-block">Preview Surat</div>
                <h1 class="text-2xl font-bold text-slate-800 m-0 tracking-tight flex items-center gap-3">
                    <i class="fas fa-file-alt text-indigo-600"></i> Preview: {{ $surat->tipe_surat }}
                </h1>
                <p class="text-sm text-slate-500 mt-1 m-0">Nomor: {{ $surat->nomor_surat }}</p>
            </div>
            <a href="javascript:history.back()" class="px-4 py-2 rounded-lg bg-white text-slate-600 text-xs font-bold border border-slate-200 hover:bg-slate-50 no-underline">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            {{-- Main Preview Area --}}
            <div class="lg:col-span-9">
                <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-indigo-100 flex justify-between items-center">
                        <div>
                            <h2 class="text-lg font-black text-indigo-900 m-0">Preview Surat</h2>
                            <p class="text-xs text-indigo-600 mt-1 m-0">Tampilan surat yang akan dicetak</p>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="zoomOut()" class="px-3 py-2 bg-white rounded-lg border border-indigo-200 hover:bg-indigo-50 text-xs font-bold">
                                <i class="fas fa-search-minus"></i>
                            </button>
                            <button onclick="zoomIn()" class="px-3 py-2 bg-white rounded-lg border border-indigo-200 hover:bg-indigo-50 text-xs font-bold">
                                <i class="fas fa-search-plus"></i>
                            </button>
                        </div>
                    </div>
                    
                    {{-- Surat Template Display --}}
                    <div class="p-6 bg-slate-100">
                        <div id="surat-container" class="bg-white shadow-2xl mx-auto" style="width: 21.5cm; min-height: 33cm; transform-origin: top center;">
                            @php
                                // Convert logo to Base64 (SAME AS CETAK)
                                $path = public_path('assets/images/logo_riau.png');
                                $logoBase64 = null;
                                if (file_exists($path)) {
                                    $type = pathinfo($path, PATHINFO_EXTENSION);
                                    $data = file_get_contents($path);
                                    $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                                }
                            @endphp
                            
                            {{-- Template with previewMode to scope CSS --}}
                            @include('pdf.surat-panggilan', [
                                'siswa' => $kasus->siswa,
                                'surat' => $surat,
                                'logoBase64' => $logoBase64,
                                'previewMode' => true,
                            ])
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar Actions --}}
            <div class="lg:col-span-3">
                <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden sticky top-6">
                    <div class="px-6 py-4 bg-slate-900 text-white">
                        <h3 class="text-lg font-black m-0">Aksi</h3>
                        <p class="text-xs text-slate-400 mt-1 m-0">Pilih tindakan</p>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        {{-- Edit Button --}}
                        <a href="{{ route('tindak-lanjut.edit-surat', $kasus->id) }}" 
                           class="flex items-center justify-center gap-2 px-4 py-4 rounded-xl bg-amber-50 text-amber-700 border-2 border-amber-200 hover:bg-amber-100 hover:border-amber-300 transition-all font-bold text-xs uppercase tracking-wider no-underline group w-full">
                            <i class="fas fa-edit group-hover:scale-110 transition-transform"></i>
                            <span>Edit Isi Surat</span>
                        </a>
                        
                        {{-- Cetak Button --}}
                        <a href="{{ route('tindak-lanjut.cetak-surat', $kasus->id) }}" 
                           onclick="return confirm('Download surat untuk {{ $kasus->siswa->nama_siswa }}?')"
                           target="_blank"
                           class="flex items-center justify-center gap-2 px-4 py-4 rounded-xl bg-indigo-600 text-white border-2 border-indigo-700 hover:bg-indigo-700 transition-all font-bold text-xs uppercase tracking-wider no-underline shadow-lg w-full">
                            <i class="fas fa-download"></i>
                            <span>Download PDF</span>
                        </a>

                        {{-- Info --}}
                        <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 mt-6">
                            <div class="flex items-start gap-2">
                                <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                                <div class="text-xs text-blue-700 leading-relaxed">
                                    <strong class="block mb-1">Tips:</strong>
                                    • Gunakan tombol zoom untuk memperbesar/memperkecil<br>
                                    • Klik "Edit" jika ada yang perlu diubah<br>
                                    • Klik "Download PDF" untuk cetak surat
                                </div>
                            </div>
                        </div>

                        {{-- Info Siswa --}}
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 mt-4">
                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-3">Siswa</div>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold flex-shrink-0">
                                    {{ substr($kasus->siswa->nama_siswa, 0, 1) }}
                                </div>
                                <div class="flex-1">
                                    <div class="font-bold text-sm text-slate-800">{{ $kasus->siswa->nama_siswa }}</div>
                                    <div class="text-xs text-slate-500">{{ $kasus->siswa->kelas->nama_kelas ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .page-wrap-custom { font-family: 'Inter', sans-serif; }
    #surat-container {
        transition: transform 0.3s ease;
    }
</style>

<script>
    let currentZoom = 1;
    
    function zoomIn() {
        currentZoom = Math.min(currentZoom + 0.1, 1.5);
        document.getElementById('surat-container').style.transform = `scale(${currentZoom})`;
    }
    
    function zoomOut() {
        currentZoom = Math.max(currentZoom - 0.1, 0.5);
        document.getElementById('surat-container').style.transform = `scale(${currentZoom})`;
    }
</script>

@endsection
