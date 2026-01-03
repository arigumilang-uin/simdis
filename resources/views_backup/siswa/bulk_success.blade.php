@extends('layouts.app')

@section('title', 'Proses Bulk Create Berhasil')

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
                    emerald: { 500: '#10b981', 600: '#059669', 700: '#047857' },
                    rose: { 500: '#f43f5e' }, 
                    blue: { 50: '#eff6ff', 800: '#1e40af' },
                    indigo: { 600: '#4f46e5' }
                },
                boxShadow: { 'soft': '0 4px 10px rgba(0,0,0,0.05)' }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

<div class="page-wrap bg-gray-50 min-h-screen p-4 sm:p-6">
    
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-3xl shadow-xl-soft border border-slate-100 mt-5 mb-8">
            <div class="p-8 sm:p-10 text-center">
                
                {{-- Success Icon --}}
                <div class="mb-6">
                    <i class="fas fa-check-circle text-emerald-500" style="font-size: 4rem;"></i>
                </div>
                
                <h2 class="text-3xl text-emerald-700 font-bold mb-3 tracking-tight">Proses Bulk Create Berhasil!</h2>
                
                {{-- Summary Ringkas --}}
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-xl mb-6 mx-auto max-w-md">
                    <p class="font-bold mb-2">Ringkasan:</p>
                    <ul class="text-left text-sm space-y-1">
                        <li>**{{ $totalCreated ?? 0 }}** siswa baru telah ditambahkan ke sistem.</li>
                        @if(($totalWaliCreated ?? 0) > 0)
                            <li>**{{ $totalWaliCreated }}** akun Wali Murid otomatis telah dibuat.</li>
                        @else
                            <li>Tidak ada akun Wali Murid yang dibuat (opsi tidak dicentang).</li>
                        @endif
                    </ul>
                </div>
                
                {{-- Catatan Penting --}}
                <div class="bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded-xl mb-8">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle text-blue-500 mt-1 shrink-0"></i>
                        <ul class="mb-0 text-left text-sm space-y-1">
                            <li>File Excel kredensial (jika dibuat) otomatis diunduh ke device Anda. **Format file:** NISN | Username | Password.</li>
                            <li>Sarankan kepada Wali Murid untuk mengubah password setelah login pertama.</li>
                            <li>Simpan file Excel kredensial di tempat yang aman.</li>
                        </ul>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="{{ route('siswa.index') }}" class="px-6 py-3 bg-indigo-600 text-white text-base font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all no-underline flex items-center justify-center gap-2">
                        <i class="fas fa-list"></i> Kembali ke Daftar Siswa
                    </a>
                    <a href="{{ route('siswa.bulk-create') }}" class="px-6 py-3 bg-white border border-indigo-300 text-indigo-600 text-base font-bold rounded-xl hover:bg-indigo-50 transition-all no-underline flex items-center justify-center gap-2">
                        <i class="fas fa-plus"></i> Tambah Batch Lagi
                    </a>
                </div>
            </div>
        </div>

        {{-- DETAIL KREDENSIAL WALI MURID (Jika ada yang dibuat) --}}
        @if(($totalWaliCreated ?? 0) > 0)
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden mt-6">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h5 class="text-base font-bold text-slate-700 flex items-center gap-2"><i class="fas fa-file-excel text-emerald-600"></i> Detail Kredensial Wali Murid</h5>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left table-auto">
                    <thead class="bg-gray-100 text-slate-600 text-xs uppercase font-bold tracking-wider">
                        <tr>
                            <th class="px-6 py-3">No</th>
                            <th class="px-6 py-3">NISN</th>
                            <th class="px-6 py-3">Username</th>
                            <th class="px-6 py-3">Password (Sampel)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($waliCreated as $idx => $wali)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-3 text-sm text-slate-600">{{ $idx + 1 }}</td>
                                <td class="px-6 py-3 text-sm font-mono text-slate-800">{{ $wali['nisn'] }}</td>
                                <td class="px-6 py-3 text-sm font-mono text-slate-800">{{ $wali['username'] }}</td>
                                <td class="px-6 py-3 text-sm font-mono text-slate-500">
                                    <span class="text-slate-400 italic">{{ substr($wali['password'], 0, 3) }}****</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-slate-400 text-center py-5">Tidak ada kredensial wali yang dibuat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Auto-trigger download jika file tersedia di session
        @if($autoDownloadFile ?? false)
        setTimeout(function () {
            const link = document.createElement('a');
            link.href = '{{ $autoDownloadFile }}';
            link.download = true;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }, 500);
        @endif
    });
</script>
@endpush

@section('styles')
<style>
    .page-wrap { font-family: 'Inter', sans-serif; }
</style>
@endsection