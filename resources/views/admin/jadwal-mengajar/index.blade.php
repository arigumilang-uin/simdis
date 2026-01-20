@extends('layouts.app')

@section('title', 'Jadwal Mengajar')

@section('page-header')
    <x-page-header 
        title="Jadwal Mengajar" 
        subtitle="Kelola jadwal mengajar per periode semester"
    >
        <x-slot:actions>
            <a href="{{ route('admin.jadwal-mengajar.pdf.preview') }}" target="_blank" class="btn btn-secondary">
                <x-ui.icon name="eye" size="18" />
                <span class="hidden md:inline">Preview PDF</span>
            </a>
            <a href="{{ route('admin.jadwal-mengajar.pdf.download') }}" class="btn btn-secondary">
                <x-ui.icon name="download" size="18" />
                <span class="hidden md:inline">Download</span>
            </a>
            <a href="{{ route('admin.jadwal-mengajar.matrix') }}{{ $selectedPeriode ? '?periode_id=' . $selectedPeriode->id : '' }}" class="btn btn-primary">
                <x-ui.icon name="grid" size="18" />
                <span>Input Matrix</span>
            </a>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
<div class="space-y-4">

    {{-- Alert --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    {{-- Period Tabs --}}
    @if($allPeriodes->count() > 0)
        <div class="flex flex-wrap gap-2">
            @foreach($allPeriodes as $periode)
                <a href="{{ route('admin.jadwal-mengajar.index', ['periode_id' => $periode->id]) }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $selectedPeriode && $selectedPeriode->id == $periode->id ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ $periode->display_name }}
                    @if($periode->is_active)
                        <span class="ml-1 text-xs opacity-75">(Aktif)</span>
                    @endif
                </a>
            @endforeach
        </div>
    @endif

    <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm overflow-hidden mb-8 border-b border-gray-200 md:border-b-0">
        {{-- Toolbar --}}
        <div class="px-4 md:px-6 py-5 border-b border-gray-100 bg-white">
            <x-ui.action-bar :total="$jadwals->total()" totalLabel="Jadwal" class="!gap-4">
                <x-slot:filters>
                    <form method="GET" id="filter-form-jadwal" class="space-y-4">
                        <input type="hidden" name="periode_id" value="{{ $selectedPeriode?->id }}">
                        
                        {{-- Kelas --}}
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-gray-500 uppercase">Kelas</label>
                            <select name="kelas_id" class="form-select w-full text-sm rounded-lg" onchange="this.form.submit()">
                                <option value="">Semua Kelas</option>
                                @foreach($kelas as $k)
                                    <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Guru --}}
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-gray-500 uppercase">Guru</label>
                            <select name="user_id" class="form-select w-full text-sm rounded-lg" onchange="this.form.submit()">
                                <option value="">Semua Guru</option>
                                @foreach($guru as $g)
                                    <option value="{{ $g->id }}" {{ $guruId == $g->id ? 'selected' : '' }}>{{ $g->username }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Hari --}}
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-gray-500 uppercase">Hari</label>
                            <select name="hari" class="form-select w-full text-sm rounded-lg" onchange="this.form.submit()">
                                <option value="">Semua Hari</option>
                                @foreach($hariList as $h)
                                    <option value="{{ $h }}" {{ $hari == $h ? 'selected' : '' }}>{{ $h }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </x-slot:filters>
                @if(request('kelas_id') || request('user_id') || request('hari'))
                <x-slot:reset>
                    <a href="{{ route('admin.jadwal-mengajar.index', ['periode_id' => $selectedPeriode?->id]) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Reset</a>
                </x-slot:reset>
                @endif
            </x-ui.action-bar>
        </div>

        {{-- Table --}}
        @if($jadwals->count() > 0)
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Hari</th>
                            <th>Jam</th>
                            <th>Kelas</th>
                            <th>Mata Pelajaran</th>
                            <th>Guru</th>
                            <th class="w-28 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jadwals as $jadwal)
                            <tr>
                                <td>
                                    <span class="font-medium">{{ $jadwal->templateJam?->hari }}</span>
                                </td>
                                <td>
                                    <span class="text-sm">Jam ke-{{ $jadwal->templateJam?->urutan ?? '-' }}</span>
                                    <span class="block text-xs text-slate-400">
                                        {{ $jadwal->templateJam?->waktu_mulai }} - {{ $jadwal->templateJam?->waktu_selesai }}
                                    </span>
                                </td>
                                <td>
                                    <span class="font-medium">{{ $jadwal->kelas?->nama_kelas }}</span>
                                </td>
                                <td>
                                    <span class="font-medium">{{ $jadwal->mataPelajaran?->nama }}</span>
                                    <span class="block text-xs text-slate-400">{{ $jadwal->mataPelajaran?->kode }}</span>
                                </td>
                                <td>
                                    <span class="text-sm">{{ $jadwal->guru?->username ?? '-' }}</span>
                                </td>
                                <td>
                                    <div class="flex items-center justify-center gap-1">
                                        <form action="{{ route('admin.jadwal-mengajar.destroy', $jadwal->id) }}" method="POST" onsubmit="return confirm('Hapus jadwal ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-white text-rose-600 hover:bg-rose-50" title="Hapus">
                                                <x-ui.icon name="trash" size="14" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            @if($jadwals->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex flex-col items-center justify-between gap-4 md:flex-row">
                <p class="text-sm text-gray-500 text-center md:text-left">
                    Menampilkan <span class="font-semibold text-gray-900">{{ $jadwals->firstItem() }}</span> 
                    sampai <span class="font-semibold text-gray-900">{{ $jadwals->lastItem() }}</span> 
                    dari <span class="font-semibold text-gray-900">{{ $jadwals->total() }}</span> data
                </p>
                <div class="flex items-center gap-2">
                    @if($jadwals->onFirstPage())
                        <button type="button" class="btn btn-sm btn-secondary text-gray-400 cursor-not-allowed bg-white/50" disabled>
                            <x-ui.icon name="chevron-left" size="16" />
                            <span>Sebelumnya</span>
                        </button>
                    @else
                        <a href="{{ $jadwals->withQueryString()->previousPageUrl() }}" class="btn btn-sm btn-secondary hover:text-indigo-600 hover:border-indigo-200 bg-white">
                            <x-ui.icon name="chevron-left" size="16" />
                            <span>Sebelumnya</span>
                        </a>
                    @endif
                    @if($jadwals->hasMorePages())
                        <a href="{{ $jadwals->withQueryString()->nextPageUrl() }}" class="btn btn-sm btn-secondary hover:text-indigo-600 hover:border-indigo-200 bg-white">
                            <span>Selanjutnya</span>
                            <x-ui.icon name="chevron-right" size="16" />
                        </a>
                    @else
                        <button type="button" class="btn btn-sm btn-secondary text-gray-400 cursor-not-allowed bg-white/50" disabled>
                            <span>Selanjutnya</span>
                            <x-ui.icon name="chevron-right" size="16" />
                        </button>
                    @endif
                </div>
            </div>
            @endif
        @else
            <x-ui.empty-state
                icon="calendar"
                title="Belum Ada Jadwal"
                description="Gunakan fitur Input Matrix untuk menambahkan jadwal."
                :actionUrl="route('admin.jadwal-mengajar.matrix', ['periode_id' => $selectedPeriode?->id])"
                actionLabel="Input Matrix"
            />
        @endif
    </div>
</div>
@endsection
