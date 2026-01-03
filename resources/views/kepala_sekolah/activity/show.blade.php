@extends('layouts.app')

@section('title', 'Detail Log Aktivitas')
@section('subtitle', 'Audit Trail')
@section('page-header', true)

@section('actions')
    <button type="button" onclick="history.back()" class="btn btn-secondary">
        <x-ui.icon name="arrow-left" size="18" />
        <span>Kembali</span>
    </button>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    
    {{-- Left Column: Main Info --}}
    <div class="lg:col-span-7 space-y-6">
        <div class="card">
            <div class="card-header">
                <span class="card-title">Informasi Utama</span>
                <span class="badge badge-primary">{{ $log->log_name ?? 'default' }}</span>
            </div>
            
            <div class="card-body space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Tanggal & Waktu --}}
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase mb-2 block">Tanggal & Waktu</label>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 border border-gray-100">
                                <x-ui.icon name="clock" size="20" />
                            </div>
                            <div>
                                <div class="font-bold text-gray-700">{{ $log->created_at->format('d M Y') }}</div>
                                <div class="text-xs font-mono text-gray-400">{{ $log->created_at->format('H:i:s') }} WIB</div>
                            </div>
                        </div>
                    </div>

                    {{-- Pelaku --}}
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase mb-2 block">Dilakukan Oleh</label>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-sm border border-indigo-100">
                                {{ strtoupper(substr($log->causer->nama ?? 'S', 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-bold text-gray-700">{{ $log->causer->nama ?? 'System' }}</div>
                                <div class="text-xs font-mono text-indigo-500">{{ $log->causer->username ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-100">

                {{-- Keterangan --}}
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase mb-2 block">Keterangan Aktivitas</label>
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 italic text-gray-600 text-sm">
                        "{{ $log->description }}"
                    </div>
                </div>

                {{-- Subject Info --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-3 bg-white border border-gray-100 rounded-xl">
                        <label class="text-[9px] font-bold text-gray-400 uppercase block mb-1">Subject Type</label>
                        <code class="text-[10px] text-indigo-600 break-all">{{ $log->subject_type ?? 'N/A' }}</code>
                    </div>
                    <div class="p-3 bg-white border border-gray-100 rounded-xl">
                        <label class="text-[9px] font-bold text-gray-400 uppercase block mb-1">Subject ID</label>
                        <code class="text-[10px] text-indigo-600 break-all">ID: {{ $log->subject_id ?? 'N/A' }}</code>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Column: Metadata/Properties --}}
    <div class="lg:col-span-5">
        <div class="card h-full flex flex-col overflow-hidden">
            <div class="px-6 py-4 bg-gray-800 border-b border-gray-700 flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wide text-indigo-300">Metadata Properties</span>
                <x-ui.icon name="database" size="16" class="text-gray-500" />
            </div>
            
            <div class="flex-1 relative bg-gray-900">
                @php
                    $properties = $log->properties;
                    if (is_string($properties)) {
                        $properties = json_decode($properties, true);
                    }
                    $hasProperties = !empty($properties) && $properties != '[]';
                @endphp
                
                @if(!$hasProperties)
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-500 p-8 text-center">
                        <x-ui.icon name="code" size="40" strokeWidth="1.5" class="opacity-20 mb-3" />
                        <p class="text-xs font-bold uppercase tracking-wide">No Modified Data</p>
                    </div>
                @else
                    <div class="p-6 font-mono text-xs leading-relaxed overflow-auto max-h-[500px]">
                        <pre class="m-0 text-emerald-400 whitespace-pre-wrap">{{ json_encode($properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif
            </div>
            
            <div class="px-6 py-3 bg-gray-800 text-[10px] text-gray-500 border-t border-gray-700 italic">
                * Data ini menunjukkan atribut yang diubah (Old vs New).
            </div>
        </div>
    </div>

</div>
@endsection
