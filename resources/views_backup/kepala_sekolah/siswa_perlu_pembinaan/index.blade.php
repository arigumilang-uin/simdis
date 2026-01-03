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
                    info: '#3b82f6',
                    warning: '#f59e0b',
                    danger: '#f43f5e',
                    orange: '#f97316',
                    indigo: { 600: '#4f46e5', 50: '#eef2ff', 100: '#e0e7ff', 700: '#4338ca' },
                    blue: { 50: '#eff6ff', 100: '#dbeafe', 600: '#2563eb' }
                },
                boxShadow: { 'soft': '0 4px 10px rgba(0,0,0,0.05)' }
            }
        },
        corePlugins: { preflight: false }
    }
</script>

<div class="page-wrap-custom min-h-screen p-6">
    <div class="max-w-7xl mx-auto">
        
        <div class="flex justify-between items-center mb-3 pb-1 border-b border-gray-200 custom-header-row">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 m-0 tracking-tight flex items-center gap-3">
                    <i class="fas fa-user-check text-indigo-600"></i> Siswa Perlu Pembinaan
                </h1>
                <p class="text-slate-500 text-sm mt-1">Monitoring akumulasi poin untuk pembinaan internal.</p>
            </div>
            
            <a href="{{ route('dashboard.kepsek') }}" class="btn-clean-action">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8 custom-card-grid">
            
            {{-- Total Siswa --}}
            <div class="dashboard-card group bg-white border-info-light">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-card-label text-info">Total Siswa</span>
                    <div class="icon-circle bg-blue-50 text-info group-hover:bg-info group-hover:text-white">
                        <i class="fas fa-users w-5 h-5"></i>
                    </div>
                </div>
                <h3 class="text-card-value text-slate-700">{{ $stats['total_siswa'] }}</h3>
                <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Perlu Tinjauan</p>
                <div class="bottom-bar bg-info"></div>
            </div>

            @php $colors = ['success', 'warning', 'orange', 'danger']; @endphp
            @foreach($stats['by_range'] as $index => $stat)
                @if($stat['count'] > 0)
                @php $color = $colors[$index] ?? 'indigo'; @endphp
                <div class="dashboard-card group bg-white border-{{$color}}-light">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-card-label text-{{$color}}">{{ $stat['rule']->getRangeText() }}</span>
                        <div class="icon-circle bg-{{$color}}-light text-{{$color}} group-hover:bg-{{$color}} group-hover:text-white">
                            <i class="fas fa-exclamation-triangle w-5 h-5"></i>
                        </div>
                    </div>
                    <h3 class="text-card-value text-slate-700">{{ $stat['count'] }}</h3>
                    <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Siswa</p>
                    <div class="bottom-bar bg-{{$color}}"></div>
                </div>
                @endif
            @endforeach
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <h5 class="text-sm font-bold text-slate-700 m-0 flex items-center gap-2">
                    <i class="fas fa-filter text-slate-400"></i> Filter Data
                </h5>
                <a href="{{ route('kepala-sekolah.siswa-perlu-pembinaan.export-csv', request()->query()) }}" class="text-emerald-600 font-bold text-xs hover:text-emerald-700 flex items-center gap-1 no-underline">
                    <i class="fas fa-file-csv text-sm"></i> Export CSV
                </a>
            </div>
            <div class="p-6">
                <form method="GET" action="{{ route('kepala-sekolah.siswa-perlu-pembinaan.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Range Poin</label>
                        <select name="rule_id" class="custom-select-clean w-full">
                            <option value="">Semua Range</option>
                            @foreach($rules as $rule)
                                <option value="{{ $rule->id }}" {{ $ruleId == $rule->id ? 'selected' : '' }}>{{ $rule->getRangeText() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Kelas</label>
                        <select name="kelas_id" class="custom-select-clean w-full">
                            <option value="">Semua Kelas</option>
                            @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->id }}" {{ $kelasId == $kelas->id ? 'selected' : '' }}>{{ $kelas->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase mb-1 block">Jurusan</label>
                        <select name="jurusan_id" class="custom-select-clean w-full">
                            <option value="">Semua Jurusan</option>
                            @foreach($jurusanList as $jurusan)
                                <option value="{{ $jurusan->id }}" {{ $jurusanId == $jurusan->id ? 'selected' : '' }}>{{ $jurusan->nama_jurusan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="btn-filter-primary flex-1">Filter</button>
                        <a href="{{ route('kepala-sekolah.siswa-perlu-pembinaan.index') }}" class="btn-filter-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="flex justify-between items-end px-2 mb-3 mt-8">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Daftar Siswa Perlu Pembinaan</span>
             <span class="text-xs text-slate-500 bg-white px-3 py-1 rounded-full border border-slate-200 shadow-sm">
                Total: <b class="text-indigo-600">{{ $siswaList->count() }}</b>
            </span>
        </div>

        <div class="overflow-x-auto pb-6">
            <table class="float-table custom-table-header text-left">
                <thead>
                    <tr class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-3 pl-8">Siswa</th>
                        <th class="px-6 py-3">Rombel / Jurusan</th>
                        <th class="px-6 py-3 text-center">Total Poin</th>
                        <th class="px-6 py-3">Rekomendasi</th>
                        <th class="px-6 py-3 pr-8">Pembina</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
    @forelse($siswaList as $item)
    <tr class="float-row group custom-float-row">
        {{-- Kolom Siswa (Tanpa Inisial) --}}
        <td class="px-6 py-4 pl-8">
    <div class="flex flex-col min-w-0">
        {{-- Ganti nama_lengkap menjadi nama_siswa --}}
        <a href="{{ route('siswa.show', $item['siswa']->id) }}" 
           class="text-sm font-bold text-slate-700 hover:text-indigo-600 no-underline transition-colors truncate">
            {{ $item['siswa']->nama_siswa }}
        </a>
        <div class="flex items-center gap-2 mt-1">
            {{-- Ganti nis menjadi nisn --}}
            <span class="text-[10px] font-mono text-slate-400 bg-slate-50 px-1.5 py-0.5 rounded border border-slate-100">
                NISN: {{ $item['siswa']->nisn }}
            </span>
        </div>
    </div>
</td>

        <td class="px-6 py-4">
            <div class="flex flex-col">
                <span class="text-xs font-bold text-slate-700">{{ $item['siswa']->kelas->nama_kelas ?? '-' }}</span>
                <span class="text-[10px] text-slate-400 uppercase tracking-wide">{{ $item['siswa']->kelas->jurusan->nama_jurusan ?? '-' }}</span>
            </div>
        </td>

        <td class="px-6 py-4 text-center">
            @php 
                $p = $item['total_poin'];
                $badgeColor = $p > 300 ? 'bg-rose-100 text-rose-600 border-rose-200' : 
                             ($p > 100 ? 'bg-amber-100 text-amber-600 border-amber-200' : 
                              'bg-indigo-100 text-indigo-600 border-indigo-200');
            @endphp
            <span class="custom-badge-base border {{$badgeColor}}">
                {{ $p }} Poin
            </span>
        </td>

        <td class="px-6 py-4">
            <div class="flex flex-col gap-1 max-w-xs">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">{{ $item['rekomendasi']['range_text'] }}</span>
                <p class="text-[11px] text-slate-600 leading-relaxed italic m-0">"{{ $item['rekomendasi']['keterangan'] }}"</p>
            </div>
        </td>

        <td class="px-6 py-4 pr-8">
            <div class="flex flex-wrap gap-1">
                @foreach($item['rekomendasi']['pembina_roles'] as $role)
                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-[9px] font-bold border border-slate-200 uppercase tracking-tighter">
                        {{ $role }}
                    </span>
                @endforeach
            </div>
        </td>
    </tr>
    @empty
    {{-- State Kosong Tetap Sama --}}
    <tr>
        <td colspan="5" class="text-center py-12 text-slate-400 bg-white rounded-2xl border border-slate-100">
            <div class="flex flex-col items-center opacity-60">
                <i class="fas fa-user-shield text-3xl mb-2 text-slate-300"></i>
                <p class="text-sm font-bold uppercase tracking-widest">Data Tidak Ditemukan</p>
            </div>
        </td>
    </tr>
    @endforelse
</tbody>
            </table>
        </div>

        <div class="mt-8 p-6 bg-indigo-50/50 rounded-2xl border border-indigo-100">
            <h6 class="text-sm font-bold text-indigo-800 mb-3 flex items-center gap-2">
                <i class="fas fa-info-circle"></i> Catatan Penting
            </h6>
            <ul class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 mb-0 pl-4 text-xs text-indigo-700/80">
                <li class="leading-relaxed"><strong>Pembinaan Internal</strong> adalah rekomendasi konseling, TIDAK trigger surat pemanggilan otomatis.</li>
                <li class="leading-relaxed"><strong>Surat Pemanggilan</strong> hanya trigger dari sanksi "Panggilan orang tua" di aturan frekuensi.</li>
                <li class="leading-relaxed">Data digunakan untuk <strong>monitoring proaktif</strong> sebelum mencapai threshold berat.</li>
                <li class="leading-relaxed">Klik nama siswa untuk melihat <strong>riwayat lengkap</strong> pelanggaran.</li>
            </ul>
        </div>
        
    </div>
</div>
@endsection

@section('styles')
<style>
/* --- CORE STYLING --- */
.page-wrap-custom { 
    background: #f8fafc; 
    min-height: 100vh; 
    padding: 1.5rem; 
    font-family: 'Inter', sans-serif; 
}
.custom-header-row { border-bottom: 1px solid #e2e8f0; }

/* Dashboard Cards (Ref: Dashboard v3) */
.custom-card-grid { display: flex; flex-wrap: wrap; align-items: stretch; gap: 1rem; }
.custom-card-grid > div { flex: 1 1 18%; min-width: 180px; }

.dashboard-card {
    border-radius: 1rem;
    padding: 1.25rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
    position: relative;
    overflow: hidden;
    transition: transform 0.3s, box-shadow 0.3s;
    display: flex; flex-direction: column; height: 100%;
}
.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}
.text-card-label { font-size: 0.6rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; }
.text-card-value { font-size: 1.5rem; font-weight: 800; margin-top: 0.25rem; }
.dashboard-card p { margin-top: auto; padding-top: 0.5rem; }

.icon-circle {
    padding: 0.5rem; border-radius: 0.75rem; transition: 0.3s; flex-shrink: 0;
}
.bottom-bar {
    position: absolute; bottom: 0; left: 0; width: 100%; height: 4px;
    transform: scaleX(0); transition: transform 0.3s; transform-origin: left;
}
.dashboard-card:hover .bottom-bar { transform: scaleX(1); }

/* Form Controls */
.custom-select-clean {
    border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.5rem;
    font-size: 0.8rem; background: white; outline: none; transition: 0.2s;
}
.custom-select-clean:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
.btn-filter-primary {
    background: #4f46e5; color: white; border: none; padding: 0.5rem 1rem;
    border-radius: 0.75rem; font-weight: 700; font-size: 0.8rem; transition: 0.2s;
}
.btn-filter-primary:hover { background: #4338ca; transform: translateY(-1px); }
.btn-filter-secondary {
    background: #f1f5f9; color: #475569; padding: 0.5rem 1rem; border-radius: 0.75rem;
    font-weight: 700; font-size: 0.8rem; text-decoration: none !important;
}

/* Floating Table */
.float-table { border-collapse: separate; border-spacing: 0 10px; width: 100%; }
.custom-table-header th { padding: 0.75rem 0; color: #94a3b8; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; }
.float-row { background: white; transition: 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
.custom-float-row td:first-child { border-radius: 12px 0 0 12px; border-left: 1px solid #f1f5f9; }
.custom-float-row td:last-child { border-radius: 0 12px 12px 0; border-right: 1px solid #f1f5f9; }
.custom-float-row:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); z-index: 10; position: relative; }

.custom-badge-base { display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 800; }
.btn-clean-action { padding: 0.5rem 1rem; border-radius: 0.75rem; background: #f1f5f9; color: #475569; font-size: 0.75rem; font-weight: 700; text-decoration: none !important; }

/* Helpers */
.bg-info-light { background: #e0f2fe; }
.bg-success-light { background: #dcfce7; }
.bg-warning-light { background: #fef3c7; }
.bg-orange-light { background: #ffedd5; }
.bg-danger-light { background: #fee2e2; }
</style>
@endsection