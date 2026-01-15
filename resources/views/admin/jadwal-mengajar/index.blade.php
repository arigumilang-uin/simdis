@extends('layouts.app')

@section('title', 'Jadwal Mengajar')

@section('page-header')
    <x-page-header 
        title="Jadwal Mengajar" 
        subtitle="Kelola jadwal mengajar per periode semester"
        :total="$jadwals->total()"
    />
@endsection

@section('content')
<div class="space-y-6">

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

    {{-- Action Button --}}
    <div class="flex justify-between items-center">
        <div class="flex items-center gap-2">
            {{-- PDF Export --}}
            <a href="{{ route('admin.jadwal-mengajar.pdf.preview') }}" 
               target="_blank"
               class="btn btn-secondary">
                <x-ui.icon name="eye" size="18" />
                <span>Preview PDF</span>
            </a>
            <a href="{{ route('admin.jadwal-mengajar.pdf.download') }}" 
               class="btn btn-secondary">
                <x-ui.icon name="download" size="18" />
                <span>Download PDF</span>
            </a>
        </div>
        <a href="{{ route('admin.jadwal-mengajar.matrix') }}{{ $selectedPeriode ? '?periode_id=' . $selectedPeriode->id : '' }}" class="btn btn-primary">
            <x-ui.icon name="grid" size="18" />
            <span>Input Matrix</span>
        </a>
    </div>

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

    {{-- Filter Card --}}
    <div class="card" x-data="{ expanded: {{ request('kelas_id') || request('user_id') || request('hari') ? 'true' : 'false' }} }">
        <div class="card-header cursor-pointer" @click="expanded = !expanded">
            <div class="flex items-center gap-2">
                <x-ui.icon name="filter" class="text-gray-400" size="18" />
                <span class="card-title">Filter Data</span>
            </div>
            <x-ui.icon name="chevron-down" size="20" class="text-gray-400 transition-transform" ::class="{ 'rotate-180': expanded }" />
        </div>
        
        <div x-show="expanded" x-collapse.duration.300ms x-cloak>
            <div class="card-body border-t border-gray-100">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <input type="hidden" name="periode_id" value="{{ $selectedPeriode?->id }}">

                    {{-- Kelas --}}
                    <div class="form-group">
                        <label class="form-label">Kelas</label>
                        <select name="kelas_id" class="form-input">
                            <option value="">Semua Kelas</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Guru --}}
                    <div class="form-group">
                        <label class="form-label">Guru</label>
                        <select name="user_id" class="form-input">
                            <option value="">Semua Guru</option>
                            @foreach($guru as $g)
                                <option value="{{ $g->id }}" {{ $guruId == $g->id ? 'selected' : '' }}>
                                    {{ $g->username }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Hari --}}
                    <div class="form-group">
                        <label class="form-label">Hari</label>
                        <select name="hari" class="form-input">
                            <option value="">Semua Hari</option>
                            @foreach($hariList as $h)
                                <option value="{{ $h }}" {{ $hari == $h ? 'selected' : '' }}>{{ $h }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-end gap-2">
                        @if(request('kelas_id') || request('user_id') || request('hari'))
                            <a href="{{ route('admin.jadwal-mengajar.index', ['periode_id' => $selectedPeriode?->id]) }}" class="btn btn-secondary">
                                <x-ui.icon name="refresh-cw" size="14" />
                                <span>Reset</span>
                            </a>
                        @endif
                        <button type="submit" class="btn btn-primary">
                            <x-ui.icon name="search" size="14" />
                            <span>Cari</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Table --}}
    @if($jadwals->count() > 0)
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-24">Hari</th>
                        <th class="w-40">Waktu</th>
                        <th>Kelas</th>
                        <th>Mata Pelajaran</th>
                        <th>Guru</th>

                        <th class="w-24 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jadwals as $jadwal)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td>
                                <span class="badge badge-slate">{{ $jadwal->templateJam?->hari?->value ?? '-' }}</span>
                            </td>
                            <td class="font-mono text-sm">
                                {{ $jadwal->templateJam ? $jadwal->templateJam->waktu : '-' }}
                            </td>
                            <td>
                                <div class="font-medium">{{ $jadwal->kelas?->nama_kelas }}</div>
                                <div class="text-xs text-slate-500">{{ $jadwal->kelas?->jurusan?->nama_jurusan }}</div>
                            </td>
                            <td>
                                <div class="font-medium">{{ $jadwal->mataPelajaran?->nama_mapel }}</div>
                                @if($jadwal->mataPelajaran?->kode_mapel)
                                    <div class="text-xs text-slate-500">{{ $jadwal->mataPelajaran->kode_mapel }}</div>
                                @endif
                            </td>
                            <td>{{ $jadwal->guru?->username ?? '-' }}</td>

                            <td>
                                <div class="flex items-center justify-center">
                                    <form action="{{ route('admin.jadwal-mengajar.destroy', $jadwal->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Hapus jadwal ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-white text-red-600 hover:text-red-700" title="Hapus">
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

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $jadwals->withQueryString()->links() }}
        </div>
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
@endsection
