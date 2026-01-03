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
                    emerald: { 500: '#10b981', 600: '#059669' }
                }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

<div class="page-wrap-custom min-h-screen p-6">
    <div class="max-w-7xl mx-auto">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 pb-1 custom-header-row">
            <div>
                <div class="flex items-center gap-3 text-indigo-600 mb-2">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">Audit Trail</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-800 m-0 tracking-tight flex items-center gap-3">
                    <i class="fas fa-info-circle text-indigo-600"></i> Detail Log Aktivitas
                </h1>
            </div>
            
            <a href="{{ route('audit.activity.index') }}" class="btn-clean-action no-underline">
                <i class="fas fa-arrow-left"></i> Kembali ke Riwayat
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 animate-in fade-in duration-500">
            
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100 flex items-center justify-between">
                        <h5 class="text-xs font-black uppercase tracking-widest text-slate-500 m-0">Informasi Utama</h5>
                        <span class="px-3 py-1 rounded-full bg-indigo-600 text-white text-[10px] font-black uppercase">
                            {{ $log->log_name }}
                        </span>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase mb-2 block tracking-tight">Tanggal & Waktu</label>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 border border-slate-100">
                                        <i class="far fa-clock text-lg"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-700 leading-tight">{{ $log->created_at->format('d M Y') }}</span>
                                        <span class="text-[11px] font-mono text-slate-400">{{ $log->created_at->format('H:i:s') }} WIB</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase mb-2 block tracking-tight">Dilakukan Oleh</label>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-sm border border-indigo-100 shadow-sm">
                                        {{ strtoupper(substr($log->causer->nama ?? 'S', 0, 1)) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-700 leading-tight">{{ $log->causer->nama ?? 'System' }}</span>
                                        <span class="text-[11px] font-mono text-indigo-500">{{ $log->causer->username ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-6 border-slate-100">

                        <div class="mb-6">
                            <label class="text-[10px] font-bold text-slate-400 uppercase mb-2 block tracking-tight">Keterangan Aktivitas</label>
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 italic text-slate-600 text-sm leading-relaxed">
                                "{{ $log->description }}"
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-3 bg-white border border-slate-100 rounded-xl">
                                <label class="text-[9px] font-bold text-slate-400 uppercase block mb-1">Subject Type</label>
                                <code class="text-[10px] text-indigo-600 break-all">{{ $log->subject_type ?? 'N/A' }}</code>
                            </div>
                            <div class="p-3 bg-white border border-slate-100 rounded-xl">
                                <label class="text-[9px] font-bold text-slate-400 uppercase block mb-1">Subject ID</label>
                                <code class="text-[10px] text-indigo-600 break-all">ID: {{ $log->subject_id ?? 'N/A' }}</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden h-full flex flex-col">
                    <div class="px-6 py-4 bg-slate-800 border-b border-slate-700 flex items-center justify-between">
                        <h5 class="text-xs font-black uppercase tracking-widest text-indigo-300 m-0">Metadata Properties</h5>
                        <i class="fas fa-database text-slate-500 text-xs"></i>
                    </div>
                    
                    <div class="p-0 flex-1 relative bg-slate-900">
                        @if(empty($log->properties) || $log->properties == '[]')
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-500 p-8 text-center">
                                <i class="fas fa-code text-3xl mb-3 opacity-20"></i>
                                <p class="text-xs font-bold uppercase tracking-widest">No Modified Data</p>
                            </div>
                        @else
                            <div class="p-6 font-mono text-[12px] leading-relaxed overflow-auto max-h-[500px] scrollbar-custom">
                                <pre class="m-0 text-emerald-400">@php
                                    // Memastikan data adalah array sebelum di encode
                                    $properties = is_string($log->properties) ? json_decode($log->properties, true) : $log->properties;
                                    echo json_encode($properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                                @endphp</pre>
                            </div>
                        @endif
                    </div>
                    
                    <div class="px-6 py-3 bg-slate-800 text-[10px] text-slate-500 border-t border-slate-700 italic">
                        * Data ini menunjukkan atribut yang diubah (Old vs New).
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

@endsection

@section('styles')
<style>
/* --- CORE STYLING (IDENTIK DENGAN MODUL SEBELUMNYA) --- */
.page-wrap-custom { background: #f8fafc; min-height: 100vh; padding: 1.5rem; font-family: 'Inter', sans-serif; }
.custom-header-row { border-bottom: 1px solid #e2e8f0; }

.btn-clean-action {
    padding: 0.65rem 1.2rem; 
    border-radius: 0.75rem;
    background-color: #f1f5f9; 
    color: #475569; 
    font-size: 0.8rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    transition: 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border: 1px solid #e2e8f0;
}
.btn-clean-action:hover {
    background-color: #e2e8f0;
    color: #1e293b;
}

/* Scrollbar Custom untuk Code View */
.scrollbar-custom::-webkit-scrollbar { width: 6px; }
.scrollbar-custom::-webkit-scrollbar-track { background: #0f172a; }
.scrollbar-custom::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
.scrollbar-custom::-webkit-scrollbar-thumb:hover { background: #475569; }

/* Animasi Fade In */
.animate-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endsection