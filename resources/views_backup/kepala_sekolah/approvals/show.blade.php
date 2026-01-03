@extends('layouts.app')

@section('content')

{{-- TAILWIND SETUP --}}
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

<div class="page-container p-4" style="max-width: 1400px; margin: 0 auto; font-family: 'Inter', sans-serif;">
    
    <div class="mb-4 border-b border-slate-200 pb-2">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-3 m-0">
                    <i class="fas fa-file-signature text-indigo-600"></i>
                    Tinjauan Kasus
                </h1>
                <p class="text-slate-500 text-sm mt-1 mb-0">Evaluasi detail kejadian dan berikan keputusan validasi secara detail.</p>
            </div>
            <a href="{{ route('tindak-lanjut.pending-approval') }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold py-2 px-4 rounded-xl transition-colors text-sm flex items-center gap-2 no-underline shadow-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        {{-- KOLOM KIRI --}}
        <div class="col-lg-8">
            
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-6 overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                    <h3 class="text-sm font-bold text-slate-700 m-0 uppercase tracking-wide flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-primary"></span>
                        1. Informasi Siswa & Pelanggaran
                    </h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-5 pb-6 border-b border-slate-100 mb-6">
                        <div class="w-16 h-16 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-2xl font-black shadow-lg">
                            {{ substr($kasus->siswa->nama_siswa, 0, 1) }}
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-800 mb-1 m-0">{{ $kasus->siswa->nama_siswa }}</h2>
                            <div class="flex gap-2 mt-1">
                                <span class="bg-blue-50 text-blue-600 border border-blue-100 px-2 py-0.5 rounded text-[11px] font-bold uppercase">
                                    {{ $kasus->siswa->kelas->nama_kelas }}
                                </span>
                                <span class="bg-slate-100 text-slate-600 border border-slate-200 px-2 py-0.5 rounded text-[11px] font-bold">
                                    NISN: {{ $kasus->siswa->nisn }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="form-label-modern">Kronologi / Pemicu</label>
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 italic text-slate-600 text-sm leading-relaxed border-l-4 border-l-indigo-400">
                            "{{ $kasus->pemicu }}"
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 rounded-xl bg-rose-50 border border-rose-100">
                            <span class="form-label-modern !text-rose-500 !mb-1">Rekomendasi Sanksi</span>
                            <span class="text-sm font-bold text-rose-700 block">{{ $kasus->sanksi_deskripsi ?? 'N/A' }}</span>
                        </div>
                        <div class="p-4 rounded-xl bg-indigo-50 border border-indigo-100">
                            <span class="form-label-modern !text-indigo-500 !mb-1">Dilaporkan Oleh</span>
                            <span class="text-sm font-bold text-indigo-700 block">{{ $kasus->user->nama ?? 'Sistem' }}</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            @if($kasus->suratPanggilan)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-6 overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                    <h3 class="text-sm font-bold text-slate-700 m-0 uppercase tracking-wide flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        2. Administrasi Surat Panggilan
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="form-label-modern">Nomor Surat</label>
                            <span class="font-mono text-xs font-bold text-slate-700 bg-slate-100 px-3 py-2 rounded-lg border border-slate-200 block w-fit">
                                {{ $kasus->suratPanggilan->nomor_surat }}
                            </span>
                        </div>
                        <div>
                            <label class="form-label-modern">Jadwal Pertemuan</label>
                            <div class="flex items-center gap-3 text-sm font-bold text-slate-600">
                                <span><i class="far fa-calendar-alt text-blue-500 mr-1"></i> {{ \Carbon\Carbon::parse($kasus->suratPanggilan->tanggal_pertemuan)->format('d M Y') }}</span>
                                <span class="text-slate-300">|</span>
                                <span><i class="far fa-clock text-blue-500 mr-1"></i> {{ $kasus->suratPanggilan->waktu_pertemuan }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('tindak-lanjut.preview-surat', $kasus->id) }}" class="flex-1 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold py-2.5 px-4 rounded-xl transition-all text-xs flex items-center justify-center gap-2 no-underline">
                            <i class="fas fa-eye"></i> Preview Surat
                        </a>
                        <a href="{{ route('tindak-lanjut.cetak-surat', $kasus->id) }}" target="_blank" class="flex-1 bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 px-4 rounded-xl transition-all text-xs flex items-center justify-center gap-2 no-underline">
                            <i class="fas fa-print text-blue-400"></i> Cetak Dokumen
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- KOLOM KANAN (SIDEBAR) --}}
        <div class="col-lg-4">
            
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl shadow-sm border border-blue-100 mb-4 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-600"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-blue-900 mb-1 m-0">Panduan Validasi</h4>
                            <p class="text-xs text-blue-700 leading-relaxed m-0">
                                Pilih <strong>Setuju</strong> untuk memvalidasi poin pelanggaran, atau <strong>Tolak</strong> jika data tidak sesuai.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 sticky top-6 z-10 overflow-hidden">
                <div class="p-6">
                    <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide mb-4 flex items-center gap-2 m-0">
                        <i class="fas fa-gavel text-emerald-500"></i> Generate Keputusan
                    </h4>
                    
                    <form id="approvalForm" method="POST" class="space-y-5">
                        @csrf
                        
                        <div>
                            <label class="form-label-modern">Status Validasi <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="action_type" value="approve" checked onclick="updateAction('approve')" class="peer sr-only">
                                    <div class="p-3 text-center rounded-xl border border-slate-200 bg-slate-50 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 transition-all shadow-sm">
                                        <i class="fas fa-check-circle text-slate-300 peer-checked:text-emerald-600 block mb-1"></i>
                                        <span class="block text-[10px] font-bold uppercase text-slate-500 peer-checked:text-emerald-700">Setuju</span>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="action_type" value="reject" onclick="updateAction('reject')" class="peer sr-only">
                                    <div class="p-3 text-center rounded-xl border border-slate-200 bg-slate-50 peer-checked:border-rose-500 peer-checked:bg-rose-50 transition-all shadow-sm">
                                        <i class="fas fa-times-circle text-slate-300 peer-checked:text-rose-600 block mb-1"></i>
                                        <span class="block text-[10px] font-bold uppercase text-slate-500 peer-checked:text-rose-700">Tolak</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label for="reason" class="form-label-modern">Catatan / Alasan</label>
                            <textarea name="reason" id="reason" rows="4" 
                                class="form-input-modern w-full"
                                placeholder="Opsional: Tambahkan alasan khusus..."></textarea>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-blue-200 transition-all transform active:scale-95 flex items-center justify-center gap-2 border-none cursor-pointer">
                                <i class="fas fa-paper-plane"></i> Kirim Keputusan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @else
            {{-- Info jika status bukan Menunggu Persetujuan --}}
            <div class="bg-slate-50 rounded-2xl border border-slate-200 p-6 text-center">
                <i class="fas fa-info-circle text-3xl text-slate-300 mb-3"></i>
                <p class="text-sm text-slate-500 font-medium">
                    @if($kasus->status->value === 'Disetujui')
                        Kasus ini telah <span class="font-bold text-emerald-600">disetujui</span>.
                    @elseif($kasus->status->value === 'Ditolak')
                        Kasus ini telah <span class="font-bold text-rose-600">ditolak</span>.
                    @else
                        Status kasus: <span class="font-bold">{{ $kasus->status->value }}</span>
                    @endif
                </p>
            </div>
            @endif

        </div>
    </div>
</div>

<script>
    function updateAction(action) {
        const form = document.getElementById('approvalForm');
        const id = "{{ $kasus->id }}";
        
        if (action === 'approve') {
            form.action = "{{ url('tindak-lanjut') }}/" + id + "/approve";
        } else {
            form.action = "{{ url('tindak-lanjut') }}/" + id + "/reject";
        }
    }
    window.onload = function() { updateAction('approve'); };
</script>

@endsection

@section('styles')
<style>
    /* Paksa style agar tidak ditimpa CSS framework lain */
    .form-label-modern {
        display: block !important;
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        color: #64748b !important;
        margin-bottom: 0.5rem !important;
        letter-spacing: 0.025em !important;
    }

    .form-input-modern {
        display: block !important;
        width: 100% !important;
        padding: 0.75rem 1rem !important;
        font-size: 0.875rem !important;
        line-height: 1.25 !important;
        color: #1e293b !important;
        background-color: #fff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 0.75rem !important;
        box-sizing: border-box !important;
    }

    .form-input-modern:focus {
        border-color: #6366f1 !important;
        outline: 0 !important;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1) !important;
    }
    
    /* Perbaikan Bootstrap conflict */
    .row { display: flex; flex-wrap: wrap; margin-right: -15px; margin-left: -15px; }
    .col-lg-8 { flex: 0 0 66.666667%; max-width: 66.666667%; padding: 0 15px; }
    .col-lg-4 { flex: 0 0 33.333333%; max-width: 33.333333%; padding: 0 15px; }
    
    @media (max-width: 991px) {
        .col-lg-8, .col-lg-4 { flex: 0 0 100%; max-width: 100%; }
    }
</style>
@endsection