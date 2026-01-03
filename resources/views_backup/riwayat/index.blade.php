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
                    accent: '#3b82f6',
                    success: '#10b981',
                    indigo: { 600: '#4f46e5', 50: '#eef2ff', 100: '#e0e7ff', 700: '#4338ca' }
                }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

<div class="page-wrap-custom min-h-screen p-6">
    <div class="max-w-7xl mx-auto">
        
        {{-- HEADER HALAMAN --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-3 gap-1 pb-2 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 m-0 tracking-tight flex items-center gap-3">
                    <i class="fas fa-history text-indigo-600"></i> Log Riwayat Pelanggaran
                </h1>
                <p class="text-slate-500 text-sm mt-1">Daftar rekaman pelanggaran siswa yang tercatat di sistem.</p>
            </div>
            
            <div class="flex items-center gap-2">
                @php
                    $role = auth()->user()->effectiveRoleName() ?? auth()->user()->role?->nama_role;
                    $backRoute = match($role) {
                        'Wali Kelas' => route('dashboard.walikelas'),
                        'Kaprodi' => route('dashboard.kaprodi'),
                        'Kepala Sekolah' => route('dashboard.kepsek'),
                        default => route('dashboard.admin'),
                    };
                @endphp
                <a href="{{ $backRoute }}" class="btn-filter-secondary py-2 px-4 no-underline flex items-center shadow-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Dashboard
                </a>
                <div class="bg-slate-800 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-lg">
                    Total: {{ $riwayat->total() }} Data
                </div>
            </div>
        </div>

        {{-- FILTER SECTION --}}
        <div>
             @include('components.riwayat.filter-form')
        </div>

        {{-- MAIN DATA TABLE --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse custom-solid-table">
                    <thead>
                        <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4">Waktu</th>
                            <th class="px-6 py-4">Identitas Siswa</th>
                            <th class="px-6 py-4">Detail Pelanggaran</th>
                            <th class="px-6 py-4 text-center">Poin</th>
                            <th class="px-6 py-4">Dicatat Oleh</th>
                            <th class="px-6 py-4 text-center">Bukti</th>
                            @if(auth()->user()->hasRole('Operator Sekolah'))
                            <th class="px-6 py-4 text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($riwayat as $r)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            {{-- 1. WAKTU --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('riwayat.index', array_merge(request()->all(), ['start_date' => $r->tanggal_kejadian->format('Y-m-d'), 'end_date' => $r->tanggal_kejadian->format('Y-m-d')])) }}" 
                                   class="font-bold text-slate-700 hover:text-indigo-600 no-underline block">
                                    {{ $r->tanggal_kejadian->format('d M Y') }}
                                </a>
                                <div class="text-[11px] text-slate-400 flex items-center gap-1 mt-1 font-mono">
                                    <i class="far fa-clock"></i> {{ $r->tanggal_kejadian->format('H:i') }} WIB
                                </div>
                            </td>

                            {{-- 2. SISWA --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @php $initial = strtoupper(substr($r->siswa->nama_siswa, 0, 1)); @endphp
                                    <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ $initial }}
                                    </div>
                                    <div>
                                        <a href="{{ route('siswa.show', $r->siswa->id) }}" class="font-bold text-slate-700 hover:text-indigo-600 no-underline leading-none block mb-1">
                                            {{ $r->siswa->nama_siswa }}
                                        </a>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[10px] font-extrabold uppercase px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">
                                                {{ $r->siswa->kelas->nama_kelas }}
                                            </span>
                                            @php
                                                $pelanggaranService = app(\App\Services\Pelanggaran\PelanggaranService::class);
                                                $totalPoinSiswa = $pelanggaranService->calculateTotalPoin($r->siswa_id);
                                                $colorClass = $totalPoinSiswa >= 100 ? 'text-rose-600 bg-rose-50' : ($totalPoinSiswa >= 50 ? 'text-amber-600 bg-amber-50' : 'text-slate-500 bg-slate-50');
                                            @endphp
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full {{ $colorClass }}">
                                                Akumulasi: {{ $totalPoinSiswa }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- 3. PELANGGARAN --}}
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-700 leading-snug">{{ $r->jenisPelanggaran->nama_pelanggaran }}</div>
                                <div class="text-[10px] font-bold text-indigo-400 uppercase tracking-tighter mt-0.5">
                                    {{ $r->jenisPelanggaran->kategoriPelanggaran->nama_kategori }}
                                </div>
                                @if($r->keterangan)
                                <div class="text-[11px] text-slate-500 italic mt-1 bg-slate-50 p-1.5 rounded border-l-2 border-slate-200">
                                    "{{ Str::limit($r->keterangan, 45) }}"
                                </div>
                                @endif
                            </td>

                            {{-- 4. POIN --}}
                            <td class="px-6 py-4 text-center">
                                @php $poinInfo = \App\Helpers\PoinDisplayHelper::getPoinForRiwayat($r); @endphp
                                @if($poinInfo['matched'] && $poinInfo['poin'] > 0)
                                    <div class="inline-block bg-rose-50 text-rose-600 font-black text-xs px-2.5 py-1 rounded-full border border-rose-100 shadow-sm" title="{{ \App\Helpers\PoinDisplayHelper::getFrequencyText($r) }}">
                                        +{{ $poinInfo['poin'] }}
                                    </div>
                                @else
                                    <div class="inline-block bg-slate-100 text-slate-400 font-bold text-xs px-2.5 py-1 rounded-full" title="{{ \App\Helpers\PoinDisplayHelper::getFrequencyText($r) }}">
                                        +0
                                    </div>
                                @endif
                                @if($poinInfo['frequency'])
                                    <div class="text-[9px] font-bold text-slate-400 mt-1 uppercase tracking-widest">{{ $poinInfo['frequency'] }}× Kejadian</div>
                                @endif
                            </td>

                            {{-- 5. PELAPOR --}}
                            <td class="px-6 py-4">
                                @if($r->guruPencatat)
                                    <a href="{{ route('riwayat.index', ['pencatat_id' => $r->guru_pencatat_user_id]) }}" class="flex items-center gap-2 text-slate-600 hover:text-indigo-600 no-underline group">
                                        <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center border border-slate-200">
                                            <i class="fas fa-user-tie text-[10px]"></i>
                                        </div>
                                        <div>
                                            <div class="text-[11px] font-bold leading-none">{{ $r->guruPencatat->username }}</div>
                                            <div class="text-[9px] uppercase font-bold text-slate-400">Staff</div>
                                        </div>
                                    </a>
                                @else
                                    <span class="text-[10px] text-slate-400 italic flex items-center gap-1"><i class="fas fa-robot"></i> Sistem</span>
                                @endif
                            </td>

                            {{-- 6. BUKTI --}}
                            <td class="px-6 py-4 text-center">
                                @if($r->bukti_foto_path)
                                    <a href="{{ route('bukti.show', ['path' => $r->bukti_foto_path]) }}" target="_blank" class="w-8 h-8 rounded-lg border border-slate-200 bg-white inline-flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:border-indigo-100 hover:shadow-sm transition-all shadow-sm">
                                        <i class="fas fa-image text-xs"></i>
                                    </a>
                                @else
                                    <span class="text-slate-300 text-xs">-</span>
                                @endif
                            </td>

                            {{-- 7. AKSI --}}
                            @if(auth()->user()->hasRole('Operator Sekolah'))
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('my-riwayat.edit', ['riwayat' => $r->id, 'return_url' => url()->full()]) }}" class="btn-action-custom text-amber-500 bg-amber-50 hover:bg-amber-100" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('my-riwayat.destroy', $r->id) }}" method="POST" class="m-0" onsubmit="return confirm('Yakin ingin menghapus riwayat ini? Poin siswa akan direcalculate.');">
                                        @csrf @method('DELETE')
                                        <input type="hidden" name="return_url" value="{{ url()->full() }}">
                                        <button type="submit" class="btn-action-custom text-rose-500 bg-rose-50 hover:bg-rose-100 border-none">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-search fa-3x text-slate-100 mb-4"></i>
                                    <h6 class="text-slate-500 font-bold m-0">Data tidak ditemukan</h6>
                                    <p class="text-slate-400 text-xs mt-1">Coba sesuaikan filter atau kata kunci pencarian Anda.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- FOOTER / PAGINATION --}}
            <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                        Menampilkan <span class="text-slate-700">{{ $riwayat->firstItem() ?? 0 }} - {{ $riwayat->lastItem() ?? 0 }}</span> dari {{ $riwayat->total() }} Data
                    </span>
                    <div class="pagination-custom">
                        {{ $riwayat->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
/* --- CORE STYLING --- */
.page-wrap-custom { background: #f8fafc; font-family: 'Inter', sans-serif; }

/* Filter Container */
#stickyFilter { border-radius: 1rem; }
#stickyFilter.is-sticky {
    position: sticky; top: 1rem; z-index: 50;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(8px);
}

/* Button & Action */
.btn-filter-secondary {
    background: #f1f5f9; color: #475569 !important; border-radius: 0.75rem; font-weight: 700; font-size: 0.75rem; border: none; cursor: pointer;
    transition: all 0.2s;
}
.btn-filter-secondary:hover { background: #e2e8f0; transform: translateY(-1px); }

.btn-action-custom {
    width: 28px; height: 28px; border-radius: 0.5rem; display: inline-flex; align-items: center; justify-content: center;
    font-size: 0.75rem; transition: all 0.2s; cursor: pointer; text-decoration: none !important;
}

/* Pagination Overrides */
.pagination-custom .pagination { margin: 0; gap: 4px; }
.pagination-custom .page-link {
    border-radius: 0.5rem !important; border: 1px solid #e2e8f0 !important; color: #64748b !important;
    padding: 0.4rem 0.8rem; font-size: 0.75rem; font-weight: 700;
}
.pagination-custom .page-item.active .page-link { background: #4f46e5 !important; border-color: #4f46e5 !important; color: white !important; }

/* Custom Scrollbar for overflow-x */
.overflow-x-auto::-webkit-scrollbar { height: 6px; }
.overflow-x-auto::-webkit-scrollbar-track { background: #f1f5f9; }
.overflow-x-auto::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>
@endsection

@push('scripts')
    <script src="{{ asset('js/pages/riwayat/filters.js') }}"></script>
    <script src="{{ asset('js/pages/riwayat/index.js') }}"></script>
    <script>
        // Simple Sticky Observer
        const filter = document.querySelector('#stickyFilter');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 150) {
                filter.classList.add('is-sticky');
            } else {
                filter.classList.remove('is-sticky');
            }
        });
    </script>
@endpush