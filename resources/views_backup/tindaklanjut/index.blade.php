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
                    accent: '#3b82f6',
                    success: '#10b981',
                    info: '#3b82f6',
                    warning: '#f59e0b',
                    danger: '#f43f5e',
                }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

@php
    use App\Enums\StatusTindakLanjut;
    
    // Get current filter
    $currentStatus = request('status');
    $isArsip = in_array($currentStatus, ['Selesai', 'Ditolak']);
    
    // Build base query sesuai role
    $baseQuery = \App\Models\TindakLanjut::query();
    
    $user = auth()->user();
    if ($role === 'Wali Kelas') {
        $kelasBinaan = $user->kelasDiampu;
        if ($kelasBinaan) {
            $baseQuery->whereHas('siswa', fn($q) => $q->where('kelas_id', $kelasBinaan->id));
        }
    } elseif ($role === 'Kaprodi') {
        $jurusanBinaan = $user->jurusanDiampu;
        if ($jurusanBinaan) {
            $baseQuery->whereHas('siswa.kelas', fn($q) => $q->where('jurusan_id', $jurusanBinaan->id));
        }
    }
    
    // Statistics sesuai role
    $stats = [
        'total' => $tindakLanjut->total(),
        'aktif' => (clone $baseQuery)->whereIn('status', StatusTindakLanjut::activeStatuses())->count(),
        'selesai' => (clone $baseQuery)->where('status', StatusTindakLanjut::SELESAI)->count(),
        'ditolak' => (clone $baseQuery)->where('status', StatusTindakLanjut::DITOLAK)->count(),
    ];
    
    // Status badge colors
    $statusColors = [
        'Baru' => 'bg-blue-100 text-blue-700 border-blue-200',
        'Menunggu Persetujuan' => 'bg-amber-100 text-amber-700 border-amber-200',
        'Disetujui' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        'Ditolak' => 'bg-rose-100 text-rose-700 border-rose-200',
        'Ditangani' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
        'Selesai' => 'bg-slate-100 text-slate-700 border-slate-200',
    ];
    
    // Check if user has full access
    $hasFullAccess = in_array($role, ['Waka Kesiswaan', 'Kepala Sekolah', 'Operator Sekolah', 'Developer']);
@endphp

<div class="page-wrap-custom min-h-screen p-6">
    <div class="max-w-7xl mx-auto">
        
        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-2 pb-2 border-b border-slate-200/60">
    <div class="flex flex-col gap-1">
        {{-- Badge Row: Dibuat lebih kecil --}}
        <div class="flex items-center gap-1.5">
            <span class="text-[9px] font-black uppercase tracking-wider bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded border border-indigo-100/50">
                Manajemen Kasus
            </span>
            @if(!$hasFullAccess)
                <span class="text-[9px] font-black uppercase tracking-wider bg-amber-50 text-amber-700 px-1.5 py-0.5 rounded border border-amber-200/50 flex items-center gap-1">
                    <i class="fas fa-filter text-[8px]"></i>
                    {{ $role === 'Wali Kelas' ? ($user->kelasDiampu->nama_kelas ?? 'Kelas') : ($user->jurusanDiampu->nama_jurusan ?? 'Jurusan') }}
                </span>
            @endif
        </div>

        {{-- Title Row: Icon dan Judul dalam satu baris compact --}}
        <div class="flex items-baseline gap-2">
            <h1 class="text-lg font-extrabold text-slate-800 m-0 tracking-tight flex items-center gap-2">
                <i class="fas fa-folder-open text-indigo-500 text-base"></i> Daftar Tindak Lanjut
            </h1>
            <p class="text-slate-400 text-[11px] font-medium hidden md:block">
                @if($role === 'Wali Kelas')
                    — Kasus kelas binaan
                @elseif($role === 'Kaprodi')
                    — Kasus jurusan binaan
                @else
                    — Kelola arsip & pelanggaran
                @endif
            </p>
        </div>
        
        {{-- Mobile-only description (Optional) --}}
        <p class="text-slate-400 text-[10px] md:hidden m-0">
             Monitoring laporan pelanggaran siswa.
        </p>
    </div>
</div>
{{-- STATISTICS CARDS SLIM VERSION --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
    {{-- Total --}}
    <a href="{{ route('tindak-lanjut.index') }}" class="group flex items-center gap-3 bg-white p-3 rounded-xl border border-slate-200 shadow-sm hover:border-slate-300 transition-all no-underline">
        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center text-xs group-hover:bg-slate-200 transition-colors">
            <i class="fas fa-folder"></i>
        </div>
        <div class="overflow-hidden">
            <h3 class="text-base font-black text-slate-700 m-0 leading-tight">
                {{ $stats['aktif'] + $stats['selesai'] + $stats['ditolak'] }}
            </h3>
            <p class="text-[9px] text-slate-400 uppercase font-bold tracking-tight m-0 truncate">Total Kasus</p>
        </div>
    </a>

    {{-- Aktif --}}
    <a href="{{ route('tindak-lanjut.index', ['active_only' => 1]) }}" class="group flex items-center gap-3 bg-white p-3 rounded-xl border {{ !$isArsip && !$currentStatus ? 'border-indigo-400 ring-2 ring-indigo-50' : 'border-indigo-100' }} shadow-sm hover:border-indigo-300 transition-all no-underline">
        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs group-hover:bg-indigo-100 transition-colors">
            <i class="fas fa-clock"></i>
        </div>
        <div class="overflow-hidden">
            <h3 class="text-base font-black text-indigo-600 m-0 leading-tight">{{ $stats['aktif'] }}</h3>
            <p class="text-[9px] text-slate-400 uppercase font-bold tracking-tight m-0 truncate">Dalam Proses</p>
        </div>
    </a>

    {{-- Selesai --}}
    <a href="{{ route('tindak-lanjut.index', ['status' => 'Selesai']) }}" class="group flex items-center gap-3 bg-white p-3 rounded-xl border {{ $currentStatus === 'Selesai' ? 'border-emerald-400 ring-2 ring-emerald-50' : 'border-emerald-100' }} shadow-sm hover:border-emerald-300 transition-all no-underline">
        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs group-hover:bg-emerald-100 transition-colors">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="overflow-hidden">
            <h3 class="text-base font-black text-emerald-600 m-0 leading-tight">{{ $stats['selesai'] }}</h3>
            <p class="text-[9px] text-slate-400 uppercase font-bold tracking-tight m-0 truncate">Tuntas</p>
        </div>
    </a>

    {{-- Ditolak --}}
    <a href="{{ route('tindak-lanjut.index', ['status' => 'Ditolak']) }}" class="group flex items-center gap-3 bg-white p-3 rounded-xl border {{ $currentStatus === 'Ditolak' ? 'border-rose-400 ring-2 ring-rose-50' : 'border-rose-100' }} shadow-sm hover:border-rose-300 transition-all no-underline">
        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-xs group-hover:bg-rose-100 transition-colors">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="overflow-hidden">
            <h3 class="text-base font-black text-rose-600 m-0 leading-tight">{{ $stats['ditolak'] }}</h3>
            <p class="text-[9px] text-slate-400 uppercase font-bold tracking-tight m-0 truncate">Ditolak</p>
        </div>
    </a>
</div>

        {{-- JAVASCRIPT UNTUK TOGGLE --}}
<script>
    function toggleFilterKasus() {
        const content = document.getElementById('filterContentKasus');
        content.classList.toggle('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (window.innerWidth < 768) {
            const urlParams = new URLSearchParams(window.location.search);
            const isFiltered = urlParams.has('status') || urlParams.has('kelas_id') || urlParams.has('jurusan_id');
            
            if (!isFiltered) {
                const content = document.getElementById('filterContentKasus');
                if (content) content.classList.add('hidden');
            }
        }
    });
</script>

{{-- KARTU FILTER UTAMA --}}
<div id="kasusFilterCard" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6 top-4 z-10">
    
    {{-- Header Kartu (Toggle-able) --}}
    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center cursor-pointer transition-colors hover:bg-slate-50 group" onclick="toggleFilterKasus()">
        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider m-0 flex items-center gap-2 group-hover:text-indigo-600 transition-colors">
            <span class="p-1.5 bg-indigo-50 border border-indigo-100 rounded-lg text-indigo-600 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
            </span>
            Filter & Pencarian Kasus
        </h3>
    </div>

    {{-- Konten Filter --}}
    <div id="filterContentKasus" class="transition-all duration-300 ease-in-out p-6">
        <form id="filterFormKasus" action="{{ route('tindak-lanjut.index') }}" method="GET" class="w-full">
            
            <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-12 gap-4 items-end">
                
                {{-- 1. Status (3/12 kolom) --}}
                <div class="lg:col-span-3">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Status Kasus</label>
                    <div class="relative">
                        <select name="status" class="w-full appearance-none bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 pr-8 shadow-sm transition-all hover:bg-white cursor-pointer" onchange="this.form.submit()">
                            <option value="">- Semua Status -</option>
                            @foreach(StatusTindakLanjut::cases() as $status)
                                <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                {{-- 2. Kelas (Hanya tampil jika bukan Wali Kelas) (3/12 kolom) --}}
                @if($hasFullAccess || $role === 'Kaprodi')
                <div class="lg:col-span-3">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Kelas</label>
                    <div class="relative">
                        <select name="kelas_id" class="w-full appearance-none bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 pr-8 shadow-sm transition-all hover:bg-white cursor-pointer" onchange="this.form.submit()">
                            <option value="">- Semua Kelas -</option>
                            @php
                                $kelasList = \App\Models\Kelas::orderBy('nama_kelas');
                                if ($role === 'Kaprodi' && $user->jurusanDiampu) {
                                    $kelasList = $kelasList->where('jurusan_id', $user->jurusanDiampu->id);
                                }
                                $kelasList = $kelasList->get();
                            @endphp
                            @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>{{ $kelas->nama_kelas }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>
                @endif

                {{-- 3. Jurusan (Hanya Admin/BK/Full Access) (3/12 kolom) --}}
                @if($hasFullAccess)
                <div class="lg:col-span-3">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Jurusan</label>
                    <div class="relative">
                        <select name="jurusan_id" class="w-full appearance-none bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 pr-8 shadow-sm transition-all hover:bg-white cursor-pointer" onchange="this.form.submit()">
                            <option value="">- Semua Jurusan -</option>
                            @foreach(\App\Models\Jurusan::orderBy('nama_jurusan')->get() as $jurusan)
                                <option value="{{ $jurusan->id }}" {{ request('jurusan_id') == $jurusan->id ? 'selected' : '' }}>{{ $jurusan->nama_jurusan }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>
                @endif

                {{-- 4. Per Page (1/12 atau 2/12 kolom) --}}
                <div class="lg:col-span-1">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Hal</label>
                    <select name="perPage" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl p-2.5 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer" onchange="this.form.submit()">
                        <option value="10" {{ request('perPage') == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ request('perPage', 20) == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </div>

                {{-- 5. Reset Button (Sisa kolom) --}}
                <div class="lg:col-span-2">
                    @if(request()->has('status') || request()->has('kelas_id') || request()->has('jurusan_id'))
                        <a href="{{ route('tindak-lanjut.index') }}" class="w-full inline-flex justify-center items-center py-2.5 text-sm font-bold border border-rose-100 bg-rose-50 text-rose-600 rounded-xl shadow-sm hover:bg-rose-100 transition-colors h-[42px] no-underline">
                            <i class="fas fa-sync-alt mr-2 text-xs"></i> Reset
                        </a>
                    @else
                        <button type="submit" class="w-full py-2.5 bg-indigo-600 text-white rounded-xl font-bold text-xs uppercase tracking-wider hover:bg-indigo-700 transition-all shadow-sm h-[42px]">
                            <i class="fas fa-filter mr-1"></i> Terapkan
                        </button>
                    @endif
                </div>
            
            </div>
        </form>
    </div>
</div>

        {{-- DATA TABLE CARD --}}
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    {{-- Header Tabel: Dibuat lebih slim --}}
    <div class="px-5 py-3 border-b border-slate-100 bg-white flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
        <h5 class="text-[11px] font-black text-slate-700 m-0 uppercase tracking-widest flex items-center gap-2">
            @if($currentStatus)
                Log Kasus: <span class="text-indigo-600">{{ $currentStatus }}</span>
            @elseif(request('active_only'))
                Antrean Kasus Aktif
            @else
                Main Transaction Log
            @endif
        </h5>
        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 bg-slate-50 px-2 py-1 rounded-lg border border-slate-100">
            Record: <span class="text-indigo-600">{{ $tindakLanjut->firstItem() ?? 0 }}-{{ $tindakLanjut->lastItem() ?? 0 }}</span> / {{ $tindakLanjut->total() }}
        </span>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse table-fixed min-w-[800px]">
            <thead>
                <tr class="text-[9px] font-black text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                    <th class="px-5 py-3 w-56">Informasi Siswa</th>
                    <th class="px-5 py-3 w-32">Unit Kelas</th>
                    <th class="px-5 py-3 w-64">Sanksi</th>
                    <th class="px-5 py-3 w-36 text-center">Status</th>
                    <th class="px-5 py-3 w-32 text-right">Timestamp</th>
                    <th class="px-5 py-3 w-28 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($tindakLanjut as $item)
                <tr class="hover:bg-slate-50/50 transition-all group">
                    {{-- Siswa --}}
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-indigo-500 text-white flex items-center justify-center font-bold text-[10px] flex-shrink-0">
                                {{ strtoupper(substr($item->siswa->nama_siswa ?? 'X', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('siswa.show', $item->siswa_id) }}" class="text-[12px] font-bold text-slate-700 hover:text-indigo-600 no-underline block leading-tight truncate">
                                    {{ $item->siswa->nama_siswa ?? '-' }}
                                </a>
                                <span class="text-[9px] font-mono text-slate-400 block mt-0.5">{{ $item->siswa->nisn ?? '-' }}</span>
                            </div>
                        </div>
                    </td>

                    {{-- Kelas --}}
                    <td class="px-5 py-3">
                        <div class="text-[11px] font-bold text-slate-700 truncate">{{ $item->siswa->kelas->nama_kelas ?? '-' }}</div>
                        <div class="text-[9px] text-slate-400 uppercase truncate">{{ $item->siswa->kelas->jurusan->kode_jurusan ?? '-' }}</div>
                    </td>

                    {{-- Sanksi --}}
                    <td class="px-5 py-3">
                        <p class="text-[10px] text-slate-500 leading-snug m-0 line-clamp-2 italic italic">
                            "{{ $item->sanksi_deskripsi ?? 'Belum ditentukan' }}"
                        </p>
                    </td>

                    {{-- Status --}}
                    <td class="px-5 py-3 text-center">
                        <span class="inline-block px-2 py-1 rounded-md text-[8px] font-black uppercase tracking-wider border {{ $statusColors[$item->status->value] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ $item->status->label() }}
                        </span>
                    </td>

                    {{-- Tanggal --}}
                    <td class="px-5 py-3 text-right">
                        <div class="text-[10px] font-bold text-slate-700">{{ $item->tanggal_tindak_lanjut ? \Carbon\Carbon::parse($item->tanggal_tindak_lanjut)->format('d/m/y') : '-' }}</div>
                        <div class="text-[9px] text-slate-400 font-medium tracking-tighter">{{ $item->created_at->format('H:i') }} WIB</div>
                    </td>

                    {{-- Aksi --}}
<td class="px-6 py-3 text-center">
    <div class="flex items-center justify-center gap-2">
        {{-- Tombol View Detail --}}
        <a href="{{ route('tindak-lanjut.show', $item->id) }}" 
           class="group/btn w-9 h-9 rounded-xl bg-slate-50 text-slate-400 hover:bg-indigo-600 hover:text-white transition-all duration-300 flex items-center justify-center no-underline shadow-sm hover:shadow-indigo-200 hover:shadow-lg active:scale-90" 
           title="Lihat Detail Kasus">
            <i class="fas fa-eye text-xs group-hover/btn:scale-110 transition-transform"></i>
        </a>

        {{-- Tombol Edit/Manage (Hanya Muncul jika Aktif) --}}
        @if($item->status->isActive())
            <a href="{{ route('tindak-lanjut.edit', $item->id) }}" 
               class="group/btn w-9 h-9 rounded-xl bg-slate-50 text-slate-400 hover:bg-amber-500 hover:text-white transition-all duration-300 flex items-center justify-center no-underline shadow-sm hover:shadow-amber-200 hover:shadow-lg active:scale-90" 
               title="Kelola Tindak Lanjut">
                <i class="fas fa-pen-nib text-xs group-hover/btn:rotate-12 transition-transform"></i>
            </a>
        @else
            {{-- Placeholder atau Tombol Terkunci (Opsional) --}}
            <div class="w-9 h-9 rounded-xl bg-slate-50 text-slate-200 flex items-center justify-center cursor-not-allowed" title="Kasus Selesai/Terkunci">
                <i class="fas fa-lock text-xs"></i>
            </div>
        @endif
    </div>
</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-16">
                        <div class="flex flex-col items-center opacity-30">
                            <i class="fas fa-database text-3xl mb-2"></i>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">No Data Logs</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    @if($tindakLanjut->hasPages())
    <div class="px-5 py-3 border-t border-slate-100 bg-white flex justify-center">
        {{ $tindakLanjut->appends(request()->query())->links() }}
    </div>
    @endif
</div>
        {{-- INFO SECTION --}}
        <div class="mt-8 p-6 bg-indigo-50/50 rounded-2xl border border-indigo-100">
            <h6 class="text-sm font-bold text-indigo-800 mb-3 flex items-center gap-2">
                <i class="fas fa-info-circle"></i> Informasi Status Kasus
            </h6>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-indigo-700/80">
                <div>
                    <span class="font-bold">Aktif:</span>
                    <ul class="pl-4 mt-1 space-y-1 mb-0">
                        <li><span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 text-[9px] font-bold">Baru</span> - Kasus baru dibuat</li>
                        <li><span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 text-[9px] font-bold">Menunggu Persetujuan</span> - Menunggu Kepsek</li>
                        <li><span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 text-[9px] font-bold">Disetujui</span> - Siap ditangani</li>
                        <li><span class="px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700 text-[9px] font-bold">Ditangani</span> - Sedang diproses</li>
                    </ul>
                </div>
                <div>
                    <span class="font-bold">Arsip:</span>
                    <ul class="pl-4 mt-1 space-y-1 mb-0">
                        <li><span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 text-[9px] font-bold">Selesai</span> - Kasus tuntas</li>
                        <li><span class="px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 text-[9px] font-bold">Ditolak</span> - Tidak disetujui Kepsek</li>
                    </ul>
                </div>
                <div>
                    <span class="font-bold">Tips:</span>
                    <ul class="pl-4 mt-1 space-y-1 mb-0">
                        <li>Klik card statistik untuk filter cepat</li>
                        <li>Kasus selesai/ditolak tetap tersimpan sebagai arsip</li>
                        <li>Klik nama siswa untuk melihat profil lengkap</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .page-wrap-custom { background: #f8fafc; font-family: 'Inter', sans-serif; }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endsection
