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
                    indigo: { 600: '#4f46e5', 50: '#eef2ff', 100: '#e0e7ff', 700: '#4338ca' },
                    emerald: { 50: '#ecfdf5', 100: '#d1fae5', 600: '#059669' },
                    amber: { 50: '#fffbeb', 100: '#fef3c7', 600: '#d97706' },
                    blue: { 50: '#eff6ff', 100: '#dbeafe', 600: '#2563eb' }
                }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

<div class="page-wrap-custom min-h-screen p-6">
    <div class="max-w-7xl mx-auto">
        
        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 pb-4 border-b border-slate-200">
            <div>
                <div class="flex items-center gap-2 text-indigo-600 mb-1">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">Pembinaan Internal</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-800 m-0 tracking-tight flex items-center gap-3">
                    <i class="fas fa-user-check text-indigo-600"></i> Siswa Perlu Pembinaan
                </h1>
                <p class="text-slate-500 text-sm mt-1">Monitoring dan tracking status pembinaan internal siswa.</p>
            </div>
            
            <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-lg bg-white text-slate-600 text-xs font-bold border border-slate-200 hover:bg-slate-50 no-underline">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>

        {{-- STATISTICS CARDS --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Total</span>
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <h3 class="text-2xl font-black text-slate-700">{{ $stats['total'] }}</h3>
                <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Siswa</p>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-amber-200 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-black text-amber-500 uppercase tracking-wider">Perlu Pembinaan</span>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <h3 class="text-2xl font-black text-amber-600">{{ $stats['perlu_pembinaan'] }}</h3>
                <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Menunggu</p>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-blue-200 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-black text-blue-500 uppercase tracking-wider">Sedang Dibina</span>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="fas fa-user-cog"></i>
                    </div>
                </div>
                <h3 class="text-2xl font-black text-blue-600">{{ $stats['sedang_dibina'] }}</h3>
                <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Proses</p>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-emerald-200 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-black text-emerald-500 uppercase tracking-wider">Selesai</span>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <h3 class="text-2xl font-black text-emerald-600">{{ $stats['selesai'] }}</h3>
                <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Tuntas</p>
            </div>
        </div>

        {{-- FILTER --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <h5 class="text-sm font-bold text-slate-700 m-0 flex items-center gap-2">
                    <i class="fas fa-filter text-slate-400"></i> Filter & Export
                </h5>
                <a href="{{ route('pembinaan.export-csv', request()->query()) }}" class="text-emerald-600 font-bold text-xs hover:text-emerald-700 flex items-center gap-1 no-underline">
                    <i class="fas fa-file-csv"></i> Export CSV
                </a>
            </div>
            <div class="p-6">
                <form method="GET" action="{{ route('pembinaan.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Status</label>
                        <select name="status" class="w-full p-2.5 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none">
                            <option value="">Semua Status</option>
                            <option value="Perlu Pembinaan" {{ $statusFilter == 'Perlu Pembinaan' ? 'selected' : '' }}>🟡 Perlu Pembinaan</option>
                            <option value="Sedang Dibina" {{ $statusFilter == 'Sedang Dibina' ? 'selected' : '' }}>🔵 Sedang Dibina</option>
                            <option value="Selesai" {{ $statusFilter == 'Selesai' ? 'selected' : '' }}>🟢 Selesai</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Range Poin</label>
                        <select name="rule_id" class="w-full p-2.5 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none">
                            <option value="">Semua Range</option>
                            @foreach($rules as $rule)
                                <option value="{{ $rule->id }}" {{ $ruleId == $rule->id ? 'selected' : '' }}>{{ $rule->getRangeText() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Kelas</label>
                        <select name="kelas_id" class="w-full p-2.5 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none">
                            <option value="">Semua Kelas</option>
                            @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->id }}" {{ $kelasId == $kelas->id ? 'selected' : '' }}>{{ $kelas->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Jurusan</label>
                        <select name="jurusan_id" class="w-full p-2.5 rounded-xl border border-slate-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none">
                            <option value="">Semua Jurusan</option>
                            @foreach($jurusanList as $jurusan)
                                <option value="{{ $jurusan->id }}" {{ $jurusanId == $jurusan->id ? 'selected' : '' }}>{{ $jurusan->nama_jurusan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 px-4 py-2.5 bg-indigo-600 text-white rounded-xl font-bold text-xs uppercase tracking-wider hover:bg-indigo-700 transition-all">
                            Filter
                        </button>
                        <a href="{{ route('pembinaan.index') }}" class="px-4 py-2.5 bg-slate-100 text-slate-600 rounded-xl font-bold text-xs uppercase tracking-wider hover:bg-slate-200 transition-all no-underline">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <h5 class="text-sm font-bold text-slate-700 m-0">Daftar Pembinaan</h5>
                <span class="text-xs text-slate-500 bg-white px-3 py-1 rounded-full border border-slate-200">
                    Total: <b class="text-indigo-600">{{ $pembinaanList->count() }}</b>
                </span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50">
                            <th class="px-6 py-4">Siswa</th>
                            <th class="px-6 py-4">Kelas</th>
                            <th class="px-6 py-4 text-center">Poin</th>
                            <th class="px-6 py-4">Keterangan</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4">Dibina Oleh</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($pembinaanList as $item)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 text-white flex items-center justify-center font-black text-sm">
                                        {{ strtoupper(substr($item->siswa->nama_siswa, 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('siswa.show', $item->siswa->id) }}" class="text-sm font-bold text-slate-700 hover:text-indigo-600 no-underline">
                                            {{ $item->siswa->nama_siswa }}
                                        </a>
                                        <span class="block text-[10px] font-mono text-slate-400">{{ $item->siswa->nisn }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-bold text-slate-700">{{ $item->siswa->kelas->nama_kelas ?? '-' }}</span>
                                <span class="block text-[10px] text-slate-400">{{ $item->siswa->kelas->jurusan->nama_jurusan ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php 
                                    $p = $item->total_poin_saat_trigger;
                                    $badgeColor = $p > 300 ? 'bg-rose-100 text-rose-600 border-rose-200' : 
                                                 ($p > 100 ? 'bg-amber-100 text-amber-600 border-amber-200' : 
                                                  'bg-indigo-100 text-indigo-600 border-indigo-200');
                                @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $badgeColor }}">
                                    {{ $p }} Poin
                                </span>
                            </td>
                            <td class="px-6 py-4 max-w-xs">
                                <p class="text-[11px] text-slate-600 leading-relaxed italic m-0 line-clamp-2">"{{ $item->keterangan_pembinaan }}"</p>
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">{{ $item->range_text }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider border {{ $item->status->badgeClasses() }}">
                                    {{ $item->status->value }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($item->dibinaOleh)
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-[10px]">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div>
                                            <span class="text-xs font-bold text-slate-700 block">{{ $item->dibinaOleh->nama ?? $item->dibinaOleh->username }}</span>
                                            <span class="text-[9px] text-slate-400">{{ $item->dibina_at?->format('d M Y') }}</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if($item->status->value === 'Perlu Pembinaan')
                                        <form action="{{ route('pembinaan.mulai', $item->id) }}" method="POST" onsubmit="return confirm('Mulai pembinaan untuk {{ $item->siswa->nama_siswa }}?')">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="px-3 py-2 rounded-lg bg-indigo-600 text-white text-[10px] font-bold uppercase tracking-wider hover:bg-indigo-700 transition-all">
                                                <i class="fas fa-play-circle mr-1"></i> Mulai
                                            </button>
                                        </form>
                                    @elseif($item->status->value === 'Sedang Dibina')
                                        <button type="button" onclick="openSelesaikanModal({{ $item->id }}, '{{ $item->siswa->nama_siswa }}')" class="px-3 py-2 rounded-lg bg-emerald-600 text-white text-[10px] font-bold uppercase tracking-wider hover:bg-emerald-700 transition-all">
                                            <i class="fas fa-check-circle mr-1"></i> Selesai
                                        </button>
                                    @else
                                        <span class="text-xs text-emerald-600 font-bold">
                                            <i class="fas fa-check-double"></i> Tuntas
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-16">
                                <div class="flex flex-col items-center opacity-60">
                                    <i class="fas fa-user-shield text-4xl text-slate-300 mb-3"></i>
                                    <p class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Data Tidak Ditemukan</p>
                                    <p class="text-xs text-slate-400">Belum ada siswa yang perlu pembinaan atau semua sudah selesai.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- INFO SECTION --}}
        <div class="mt-8 p-6 bg-indigo-50/50 rounded-2xl border border-indigo-100">
            <h6 class="text-sm font-bold text-indigo-800 mb-3 flex items-center gap-2">
                <i class="fas fa-info-circle"></i> Informasi Penting
            </h6>
            <ul class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 mb-0 pl-4 text-xs text-indigo-700/80">
                <li class="leading-relaxed"><strong>Perlu Pembinaan</strong> = Siswa yang mencapai threshold poin dan belum ditangani.</li>
                <li class="leading-relaxed"><strong>Sedang Dibina</strong> = Proses pembinaan sedang berlangsung oleh pembina.</li>
                <li class="leading-relaxed"><strong>Selesai</strong> = Pembinaan telah selesai dengan hasil yang tercatat.</li>
                <li class="leading-relaxed">Klik nama siswa untuk melihat <strong>riwayat lengkap</strong> pelanggaran.</li>
            </ul>
        </div>
    </div>
</div>

{{-- MODAL SELESAIKAN PEMBINAAN --}}
<div id="modalSelesaikan" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50" style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="px-6 py-4 bg-emerald-50 border-b border-emerald-100">
            <h3 class="text-lg font-bold text-emerald-800 m-0 flex items-center gap-2">
                <i class="fas fa-check-circle"></i> Selesaikan Pembinaan
            </h3>
        </div>
        <form id="formSelesaikan" method="POST">
            @csrf
            @method('PUT')
            <div class="p-6 space-y-4">
                <p class="text-sm text-slate-600">
                    Selesaikan pembinaan untuk: <strong id="namaSiswaModal" class="text-slate-800"></strong>
                </p>
                <div>
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 block">Hasil Pembinaan</label>
                    <textarea name="hasil_pembinaan" rows="4" class="w-full p-3 rounded-xl border border-slate-200 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none" placeholder="Tuliskan hasil/catatan pembinaan..."></textarea>
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" onclick="closeSelesaikanModal()" class="px-4 py-2 bg-slate-200 text-slate-600 rounded-xl font-bold text-xs uppercase tracking-wider hover:bg-slate-300 transition-all">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-xl font-bold text-xs uppercase tracking-wider hover:bg-emerald-700 transition-all">
                    <i class="fas fa-check mr-1"></i> Selesaikan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openSelesaikanModal(id, nama) {
        document.getElementById('namaSiswaModal').textContent = nama;
        document.getElementById('formSelesaikan').action = '/pembinaan/' + id + '/selesaikan';
        document.getElementById('modalSelesaikan').style.display = 'flex';
    }
    
    function closeSelesaikanModal() {
        document.getElementById('modalSelesaikan').style.display = 'none';
    }

    // Close modal when clicking outside
    document.getElementById('modalSelesaikan').addEventListener('click', function(e) {
        if (e.target === this) {
            closeSelesaikanModal();
        }
    });
</script>

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
