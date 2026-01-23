@extends('layouts.app')

@section('title', 'Kelola Absensi - ' . $jadwal->mataPelajaran->nama_mapel)

@section('page-header')
    @php
        $displayJamMulai = $jadwal->merged_jam_mulai ?? $jadwal->jam_mulai;
        $displayJamSelesai = $jadwal->merged_jam_selesai ?? $jadwal->jam_selesai;
        $jamMulaiFormatted = $displayJamMulai instanceof \DateTime ? $displayJamMulai->format('H:i') : substr($displayJamMulai, 0, 5);
        $jamSelesaiFormatted = $displayJamSelesai instanceof \DateTime ? $displayJamSelesai->format('H:i') : substr($displayJamSelesai, 0, 5);
    @endphp
    <x-page-header 
        :title="$jadwal->mataPelajaran->nama_mapel"
        :subtitle="$jadwal->kelas->nama_kelas . ' • ' . $jadwal->hari->value . ' ' . $jamMulaiFormatted . ' - ' . $jamSelesaiFormatted . ' • ' . $pertemuanList->count() . ' pertemuan'"
    >
        <x-slot:actions>
            <a href="{{ route('absensi.index') }}" class="btn btn-white">
                <x-ui.icon name="arrow-left" size="16" />
                <span>Kembali</span>
            </a>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
<style>
    /* Premium Grid Table Styling */
    .grid-table {
        border-collapse: collapse;
        white-space: nowrap;
    }
    .grid-table th,
    .grid-table td {
        border: 1px solid #e5e7eb;
        padding: 10px 12px;
        vertical-align: middle;
    }
    .grid-table thead th {
        position: sticky;
        top: 0;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        z-index: 10;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        color: #475569;
        border-bottom: 2px solid #e2e8f0;
    }
    .grid-table tbody tr {
        transition: background-color 0.15s ease;
    }
    .grid-table tbody tr:hover {
        background-color: #f8fafc;
    }
    .grid-table tbody tr:nth-child(even) {
        background-color: #fafbfc;
    }
    .grid-table tbody tr:nth-child(even):hover {
        background-color: #f1f5f9;
    }
    
    .status-select { 
        appearance: none; padding: 2px 14px 2px 4px; font-size: 10px; width: 32px; text-align: center;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 2px center; background-repeat: no-repeat; background-size: 10px;
    }
    .status-select.hadir { background-color: #d1fae5; color: #065f46; }
    .status-select.sakit { background-color: #fef3c7; color: #92400e; }
    .status-select.izin { background-color: #dbeafe; color: #1e40af; }
    .status-select.alfa { background-color: #fee2e2; color: #991b1b; }
    
    /* Premium Status Buttons */
    .status-btn-group {
        display: inline-flex;
        background: #f8fafc;
        padding: 4px;
        border-radius: 12px;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.06);
    }
    .status-btn { 
        padding: 8px 14px; 
        font-size: 12px; 
        font-weight: 600; 
        border: none;
        background: transparent;
        color: #64748b;
        cursor: pointer; 
        transition: all 0.2s ease;
        border-radius: 8px;
        margin: 0 2px;
    }
    .status-btn:hover:not([class*="active-"]) { 
        background: white; 
        color: #334155;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .status-btn.active-hadir { 
        background: linear-gradient(135deg, #10b981, #059669); 
        color: white; 
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
    }
    .status-btn.active-sakit { 
        background: linear-gradient(135deg, #f59e0b, #d97706); 
        color: white; 
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.4);
    }
    .status-btn.active-izin { 
        background: linear-gradient(135deg, #3b82f6, #2563eb); 
        color: white; 
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
    }
    .status-btn.active-alfa { 
        background: linear-gradient(135deg, #ef4444, #dc2626); 
        color: white; 
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
    }
    
    /* Row hover effect */
    .attendance-row {
        transition: all 0.15s ease;
    }
    .attendance-row:hover {
        background: linear-gradient(90deg, #f8fafc, #ffffff) !important;
    }

    /* Manual Responsive Visibility (Fix for JIT issues) */
    .view-mobile { display: flex; flex-direction: column; height: 75vh; }
    .view-desktop { display: none; }
    
    @media (min-width: 768px) {
        .view-mobile { display: none !important; }
        .view-desktop { display: block !important; }
    }

    /* Strict Alignment for Desktop Grid */
    .desktop-grid-table {
        table-layout: auto !important; /* Fixed causes collapse in flex w-0 parents. Auto is safer. */
        border-collapse: collapse;
        width: 100%;
    }
    .desktop-grid-table tr {
        height: 48px !important;
    }
    .desktop-grid-table thead tr {
        height: 50px !important;
    }
    .desktop-grid-table td, .desktop-grid-table th {
        height: 48px; /* Fallback */
        padding-top: 0 !important;
        padding-bottom: 0 !important;
        vertical-align: middle !important;
        white-space: nowrap;
        overflow: hidden;
        border-bottom: 1px solid #f3f4f6; /* gray-100 */
        border-right: 1px solid #f3f4f6; /* Vertical separator */
    }
    .desktop-grid-table thead th {
        height: 50px !important;
        border-bottom: 1px solid #e5e7eb; /* gray-200 */
        border-right: 1px solid #e5e7eb; /* Vertical separator header */
        background-color: #f8fafc; /* slate-50 */
    }
    
    /* Input/Select strict sizing */
    .desktop-grid-table input, .desktop-grid-table select {
        height: 32px !important;
        min-height: 32px !important;
        margin: 0 !important;
        line-height: normal;
    }
</style>

<div class="space-y-6 overflow-x-hidden" x-data="absensiPage()" x-on:absensi-updated.window="handleExternalUpdate($event.detail)">

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <x-ui.icon name="check-circle" size="16" class="text-emerald-500" />
            {{ session('success') }}
        </div>
    @endif

    {{-- Tabs --}}
    <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm overflow-hidden">
        <div class="flex border-b border-gray-200 bg-gray-50/80">
            <button @click="activeTab = 'single'" 
                    :class="activeTab === 'single' ? 'border-emerald-500 bg-white text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                    class="flex items-center gap-2 px-5 py-4 border-b-2 text-sm font-medium transition-all">
                <x-ui.icon name="edit-3" size="16" />
                <span>Absensi Pertemuan</span>
            </button>
            <button @click="activeTab = 'grid'" 
                    :class="activeTab === 'grid' ? 'border-emerald-500 bg-white text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                    class="flex items-center gap-2 px-5 py-4 border-b-2 text-sm font-medium transition-all">
                <x-ui.icon name="grid" size="16" />
                <span>Lihat Semua (Grid)</span>
            </button>
        </div>

        {{-- Tab 1: Single Pertemuan Attendance --}}
        <div x-show="activeTab === 'single'" x-cloak>
            <div class="p-4 md:p-6">
                {{-- Pertemuan Selector --}}
                <div class="mb-6">
                    <label class="form-label">Pilih Pertemuan</label>
                    <select x-model="selectedPertemuanId" @change="onPertemuanChange()" class="form-input max-w-md">
                        <option value="">-- Pilih Pertemuan --</option>
                        @foreach($pertemuanList->sortByDesc('tanggal') as $pertemuan)
                            @php
                                $hariName = $pertemuan->tanggal->locale('id')->isoFormat('dddd');
                            @endphp
                            <option value="{{ $pertemuan->id }}" {{ $todayPertemuan && $todayPertemuan->id == $pertemuan->id ? 'selected' : '' }}>
                                Pertemuan {{ $pertemuan->pertemuan_ke }} - {{ $hariName }}, {{ $pertemuan->tanggal->format('d M Y') }}
                                @if($todayPertemuan && $todayPertemuan->id == $pertemuan->id) (Hari Ini) @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Legend - Responsive --}}
                <div class="mb-4 p-3 md:p-4 rounded-xl bg-gray-50 border border-gray-100">
                    {{-- Mobile View (Compact) --}}
                    <div class="md:hidden flex items-center justify-between text-xs">
                        <div class="flex gap-3 font-medium text-gray-600">
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-emerald-500"></span>H</span>
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-amber-500"></span>S</span>
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-blue-500"></span>I</span>
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-red-500"></span>A</span>
                        </div>
                        <div class="flex items-center gap-1 text-gray-400 text-[10px]">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            Auto-save
                        </div>
                    </div>

                    {{-- Desktop View (Full) --}}
                    <div class="hidden md:flex flex-wrap items-center gap-5 text-sm">
                        <span class="font-semibold text-gray-600 uppercase text-xs tracking-wide">Status:</span>
                        <div class="flex items-center gap-4">
                            <span class="flex items-center gap-2">
                                <span class="w-4 h-4 rounded-md shadow-sm" style="background: linear-gradient(135deg, #10b981, #059669);"></span>
                                <span class="text-gray-600">H = Hadir</span>
                            </span>
                            <span class="flex items-center gap-2">
                                <span class="w-4 h-4 rounded-md shadow-sm" style="background: linear-gradient(135deg, #f59e0b, #d97706);"></span>
                                <span class="text-gray-600">S = Sakit</span>
                            </span>
                            <span class="flex items-center gap-2">
                                <span class="w-4 h-4 rounded-md shadow-sm" style="background: linear-gradient(135deg, #3b82f6, #2563eb);"></span>
                                <span class="text-gray-600">I = Izin</span>
                            </span>
                            <span class="flex items-center gap-2">
                                <span class="w-4 h-4 rounded-md shadow-sm" style="background: linear-gradient(135deg, #ef4444, #dc2626);"></span>
                                <span class="text-gray-600">A = Alfa</span>
                            </span>
                        </div>
                        <span class="ml-auto text-gray-400 text-xs flex items-center gap-1">
                            <x-ui.icon name="save" size="12" />
                            Auto-save saat diubah
                        </span>
                    </div>
                </div>

                {{-- Attendance Table (Single Pertemuan) --}}
                <div x-show="selectedPertemuanId">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="w-16 text-center">No</th>
                                    <th>Nama Siswa</th>
                                    <th class="w-56 text-center">Status Kehadiran</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($siswaList as $index => $siswa)
                                    <tr class="attendance-row" x-data="{ status: getStatus({{ $siswa->id }}) }">
                                        <td class="text-center text-gray-400 font-medium">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="font-semibold text-gray-900">{{ $siswa->nama_siswa }}</div>
                                            <div class="text-xs text-gray-400">NISN: {{ $siswa->nisn }}</div>
                                        </td>
                                        <td class="text-center">
                                            <div class="status-btn-group">
                                                <button type="button" @click="toggleStatus({{ $siswa->id }}, 'Hadir')" 
                                                        :class="getStatus({{ $siswa->id }}) === 'Hadir' ? 'active-hadir' : ''" class="status-btn">H</button>
                                                <button type="button" @click="toggleStatus({{ $siswa->id }}, 'Sakit')" 
                                                        :class="getStatus({{ $siswa->id }}) === 'Sakit' ? 'active-sakit' : ''" class="status-btn">S</button>
                                                <button type="button" @click="toggleStatus({{ $siswa->id }}, 'Izin')" 
                                                        :class="getStatus({{ $siswa->id }}) === 'Izin' ? 'active-izin' : ''" class="status-btn">I</button>
                                                <button type="button" @click="toggleStatus({{ $siswa->id }}, 'Alfa')" 
                                                        :class="getStatus({{ $siswa->id }}) === 'Alfa' ? 'active-alfa' : ''" class="status-btn">A</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Footer --}}
                    <div class="mt-4 flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            <span class="font-semibold text-gray-700">{{ $siswaList->count() }}</span> siswa
                        </div>
                        <button @click="setAllStatus('Hadir')" class="btn btn-primary">
                            <x-ui.icon name="check" size="16" />
                            <span>Semua Hadir</span>
                        </button>
                    </div>
                </div>

                {{-- Empty State --}}
                <div x-show="!selectedPertemuanId">
                    <x-ui.empty-state 
                        icon="calendar" 
                        title="Pilih Pertemuan" 
                        description="Pilih pertemuan dari dropdown di atas untuk mulai mengabsen."
                    />
                </div>
            </div>
        </div>

        {{-- Tab 2: Full Grid View --}}
        <div x-show="activeTab === 'grid'" x-cloak>
            <div class="p-4 md:p-6">
                {{-- Legend - Responsive --}}
                <div class="mb-4 p-3 md:p-4 rounded-xl bg-gray-50 border border-gray-100">
                    {{-- Mobile View (Compact) --}}
                    <div class="md:hidden flex items-center justify-between text-xs">
                        <div class="flex gap-3 font-medium text-gray-600">
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-emerald-500"></span>H</span>
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-amber-500"></span>S</span>
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-blue-500"></span>I</span>
                            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-red-500"></span>A</span>
                        </div>
                        <div class="flex items-center gap-1 text-gray-400 text-[10px]">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            Auto-save
                        </div>
                    </div>

                    {{-- Desktop View (Full) --}}
                    <div class="hidden md:flex flex-wrap items-center gap-5 text-sm">
                        <span class="font-semibold text-gray-600 uppercase text-xs tracking-wide">Status:</span>
                        <div class="flex items-center gap-4">
                            <span class="flex items-center gap-2">
                                <span class="w-4 h-4 rounded-md shadow-sm" style="background: linear-gradient(135deg, #10b981, #059669);"></span>
                                <span class="text-gray-600">H = Hadir</span>
                            </span>
                            <span class="flex items-center gap-2">
                                <span class="w-4 h-4 rounded-md shadow-sm" style="background: linear-gradient(135deg, #f59e0b, #d97706);"></span>
                                <span class="text-gray-600">S = Sakit</span>
                            </span>
                            <span class="flex items-center gap-2">
                                <span class="w-4 h-4 rounded-md shadow-sm" style="background: linear-gradient(135deg, #3b82f6, #2563eb);"></span>
                                <span class="text-gray-600">I = Izin</span>
                            </span>
                            <span class="flex items-center gap-2">
                                <span class="w-4 h-4 rounded-md shadow-sm" style="background: linear-gradient(135deg, #ef4444, #dc2626);"></span>
                                <span class="text-gray-600">A = Alfa</span>
                            </span>
                        </div>
                        <span class="ml-auto text-gray-400 text-xs flex items-center gap-1">
                            <x-ui.icon name="save" size="12" />
                            Auto-save saat diubah
                        </span>
                    </div>
                </div>

                {{-- Mobile Info --}}
                <div class="md:hidden mb-4 p-3 rounded-xl bg-blue-50 border border-blue-100 text-blue-700 text-sm flex items-center gap-2">
                    <x-ui.icon name="info" size="16" />
                    <span>Geser ke kanan untuk melihat seluruh tabel</span>
                </div>

                {{-- MOBILE VIEW: Single Responsive Table --}}
                <div class="view-mobile md:hidden bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
                    <div class="flex-1 overflow-auto relative custom-scrollbar">
                        <table class="grid-table w-full border-separate border-spacing-0">
                            <thead class="bg-slate-50 sticky top-0 z-40">
                                <tr>
                                    <th class="sticky left-0 z-50 w-12 text-center bg-slate-50 border-b border-r border-gray-200 p-2 text-xs font-semibold text-gray-600">No</th>
                                    <th class="sticky left-[48px] z-50 w-32 md:w-48 text-left bg-slate-50 border-b border-r border-gray-200 p-2 text-xs font-semibold text-gray-600 shadow-md">Nama Siswa</th>
                                    <th class="min-w-[100px] text-center bg-slate-50 border-b border-gray-200 p-2 text-xs font-semibold text-gray-600">NISN</th>
                                    @foreach($pertemuanList as $pertemuan)
                                        <th class="min-w-[50px] text-center bg-slate-50 border-b border-gray-200 p-2 text-xs font-semibold text-gray-600 group">
                                            <div class="font-bold cursor-default" title="{{ $pertemuan->tanggal->format('d M Y') }}">{{ $pertemuan->pertemuan_ke }}</div>
                                            <div class="text-[9px] font-normal text-emerald-600">{{ $pertemuan->tanggal->format('d/m') }}</div>
                                        </th>
                                    @endforeach
                                    <th class="md:sticky md:right-[150px] z-40 bg-slate-50 border-b border-l border-gray-200 min-w-[60px] text-center text-xs font-semibold text-red-600">Jml<br>Absen</th>
                                    <th class="md:sticky md:right-0 z-40 bg-slate-50 border-b border-gray-200 min-w-[150px] text-center text-xs font-semibold text-gray-600">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody id="main-grid-body">
                                @foreach($siswaList as $index => $siswa)
                                    <tr class="hover:bg-slate-50 transition-colors group h-[44px]" data-siswa-id="{{ $siswa->id }}">
                                        <td class="sticky left-0 z-20 bg-white group-hover:bg-slate-50 border-b border-r border-gray-100 text-center text-xs text-gray-500">{{ $index + 1 }}</td>
                                        <td class="sticky left-[48px] z-20 bg-white group-hover:bg-slate-50 border-b border-r border-gray-100 p-2 shadow-md">
                                            <div class="font-medium text-gray-800 text-xs truncate w-28 md:w-44" title="{{ $siswa->nama_siswa }}">{{ $siswa->nama_siswa }}</div>
                                        </td>
                                        <td class="border-b border-gray-100 p-2 text-center text-xs text-gray-500 font-mono bg-white group-hover:bg-slate-50">{{ $siswa->nisn }}</td>
                                        
                                        @foreach($pertemuanList as $pertemuan)
                                            @php
                                                $absensi = $absensiMatrix[$siswa->id][$pertemuan->id] ?? null;
                                                $currentStatus = $absensi?->status?->value ?? '';
                                                $statusClass = strtolower($currentStatus);
                                            @endphp
                                            <td class="text-center border-b border-gray-100 p-1 bg-white group-hover:bg-slate-50">
                                                @if($pertemuan->status === 'kosong')
                                                    <span class="text-gray-300">-</span>
                                                @else
                                                    <select class="status-select {{ $statusClass }} rounded-md border-0 cursor-pointer"
                                                            data-siswa-id="{{ $siswa->id }}"
                                                            data-pertemuan-id="{{ $pertemuan->id }}"
                                                            onchange="updateAbsensiGrid(this)">
                                                        <option value="">-</option>
                                                        <option value="Hadir" {{ $currentStatus === 'Hadir' ? 'selected' : '' }}>H</option>
                                                        <option value="Sakit" {{ $currentStatus === 'Sakit' ? 'selected' : '' }}>S</option>
                                                        <option value="Izin" {{ $currentStatus === 'Izin' ? 'selected' : '' }}>I</option>
                                                        <option value="Alfa" {{ $currentStatus === 'Alfa' ? 'selected' : '' }}>A</option>
                                                    </select>
                                                @endif
                                            </td>
                                        @endforeach

                                        @php
                                            $tidakHadir = 0;
                                            foreach($pertemuanList as $p) {
                                                $abs = $absensiMatrix[$siswa->id][$p->id] ?? null;
                                                if ($abs && $abs->status->value !== 'Hadir') { $tidakHadir++; }
                                            }
                                        @endphp
                                        <td class="js-tidak-hadir-count td-tidak-hadir md:sticky md:right-[150px] z-20 bg-white group-hover:bg-slate-50 border-b border-l border-gray-100 text-center font-bold text-lg {{ $tidakHadir > 0 ? 'text-red-600 bg-red-50' : 'text-gray-300' }}">
                                            {{ $tidakHadir }}
                                        </td>
                                        <td class="md:sticky md:right-0 z-20 bg-white group-hover:bg-slate-50 border-b border-gray-100 p-2">
                                            <input type="text" class="w-full min-w-[120px] px-2 py-1 text-[11px] border border-gray-200 rounded focus:border-emerald-400 outline-none transition-all placeholder-gray-300" placeholder="Tambah keterangan...">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- DESKTOP VIEW: 3-Column Fixed Layout (Auto-Fit Screen) --}}
                <div class="view-desktop hidden md:block bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden w-full">
                    <div class="flex w-full max-h-[70vh]">
                        <!-- Left Fixed: No, Nama, NISN (Fixed Width) -->
                        <div class="w-[230px] flex-shrink-0 overflow-y-auto bg-white border-r border-gray-200" style="scrollbar-width: none;" id="desktop-left-panel">
                            <table class="desktop-grid-table text-xs w-full">
                                <thead class="sticky top-0 z-10">
                                    <tr style="height: 50px;">
                                        <th class="text-center !bg-slate-50 border-b border-gray-200 p-2 font-semibold text-gray-600 w-[50px]">No</th>
                                        <th class="text-left !bg-slate-50 border-b border-gray-200 p-2 font-semibold text-gray-600 w-[120px]">Nama Siswa</th>
                                        <th class="text-center !bg-slate-50 border-b border-gray-200 p-2 font-semibold text-gray-600 w-[60px]">NISN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($siswaList as $index => $siswa)
                                        <tr style="height: 48px;" class="border-b border-gray-100 hover:bg-slate-50 transition-colors" data-siswa-id="{{ $siswa->id }}">
                                            <td class="text-center text-gray-500 font-semibold p-1 align-middle">{{ $index + 1 }}</td>
                                            <td class="p-1 align-middle">
                                                <div class="font-semibold text-gray-800 text-[12px] truncate w-[120px]" title="{{ $siswa->nama_siswa }}">{{ $siswa->nama_siswa }}</div>
                                            </td>
                                            <td class="text-center text-gray-500 font-mono text-[11px] p-1 align-middle">{{ $siswa->nisn }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Middle Scrollable: Pertemuan (Flexible Width) -->
                        <div class="flex-1 w-0 min-w-0 overflow-x-auto overflow-y-auto" id="desktop-pertemuan-scroll" style="-webkit-overflow-scrolling: touch;">
                            <table class="desktop-grid-table text-xs w-full min-w-max">
                                <thead class="sticky top-0 z-10">
                                    <tr style="height: 50px;">
                                        @foreach($pertemuanList as $pertemuan)
                                            <th class="text-center !bg-slate-50 border-b border-gray-200 min-w-[50px] p-1 font-semibold text-gray-600">
                                                <div class="font-bold text-[12px]">{{ $pertemuan->pertemuan_ke }}</div>
                                                <div class="text-[9px] text-emerald-600 font-normal">{{ $pertemuan->tanggal->format('d/m') }}</div>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($siswaList as $index => $siswa)
                                        <tr style="height: 48px;" class="border-b border-gray-100 hover:bg-slate-50 transition-colors" data-siswa-id="{{ $siswa->id }}">
                                            @foreach($pertemuanList as $pertemuan)
                                                @php
                                                    $absensi = $absensiMatrix[$siswa->id][$pertemuan->id] ?? null;
                                                    $currentStatus = $absensi?->status?->value ?? '';
                                                    $statusClass = strtolower($currentStatus);
                                                @endphp
                                                <td class="text-center p-1 align-middle">
                                                    @if($pertemuan->status === 'kosong')
                                                        <span class="text-gray-300">-</span>
                                                    @else
                                                        <select class="status-select {{ $statusClass }} rounded border-gray-200 text-xs py-1 px-1 w-full text-center cursor-pointer focus:ring-1 focus:ring-emerald-500"
                                                                style="height: 32px;"
                                                                data-siswa-id="{{ $siswa->id }}"
                                                                data-pertemuan-id="{{ $pertemuan->id }}"
                                                                onchange="updateAbsensiGrid(this)">
                                                            <option value="">-</option>
                                                            <option value="Hadir" {{ $currentStatus === 'Hadir' ? 'selected' : '' }}>H</option>
                                                            <option value="Sakit" {{ $currentStatus === 'Sakit' ? 'selected' : '' }}>S</option>
                                                            <option value="Izin" {{ $currentStatus === 'Izin' ? 'selected' : '' }}>I</option>
                                                            <option value="Alfa" {{ $currentStatus === 'Alfa' ? 'selected' : '' }}>A</option>
                                                        </select>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Right Fixed: Tidak Hadir, Keterangan (Fixed Width) -->
                        <div class="w-[250px] flex-shrink-0 overflow-y-auto bg-white border-l border-gray-200" style="scrollbar-width: none;" id="desktop-right-panel">
                            <table class="desktop-grid-table text-xs w-full">
                                <thead class="sticky top-0 z-10">
                                    <tr style="height: 50px;">
                                        <th class="text-center !bg-slate-50 border-b border-gray-200 w-[70px] p-1 font-semibold text-gray-600">
                                            <div class="text-[11px] leading-tight">Tidak<br>Hadir</div>
                                        </th>
                                        <th class="text-center !bg-slate-50 border-b border-gray-200 p-2 font-semibold text-gray-600">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($siswaList as $index => $siswa)
                                        @php
                                            $tidakHadir = 0;
                                            foreach($pertemuanList as $p) {
                                                $abs = $absensiMatrix[$siswa->id][$p->id] ?? null;
                                                if ($abs && $abs->status->value !== 'Hadir') { $tidakHadir++; }
                                            }
                                        @endphp
                                        <tr style="height: 48px;" class="border-b border-gray-100 hover:bg-slate-50 transition-colors" data-siswa-id="{{ $siswa->id }}">
                                            <td class="js-tidak-hadir-count text-center font-bold text-lg align-middle {{ $tidakHadir > 0 ? 'text-red-600 bg-red-50' : 'text-gray-300' }}">
                                                {{ $tidakHadir }}
                                            </td>
                                            <td class="text-center p-1 align-middle">
                                                <input type="text" class="w-full px-2 py-1 text-[11px] border border-gray-200 rounded focus:border-emerald-400 outline-none transition-all placeholder-gray-300" 
                                                       style="height: 32px;"
                                                       placeholder="Keterangan...">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Scroll Sync Script (Desktop Only) --}}
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const mid = document.getElementById('desktop-pertemuan-scroll');
                        const left = document.getElementById('desktop-left-panel');
                        const right = document.getElementById('desktop-right-panel');
                        if (mid && left && right) {
                            mid.addEventListener('scroll', () => {
                                left.scrollTop = mid.scrollTop;
                                right.scrollTop = mid.scrollTop;
                            });
                        }
                    });
                </script>



                {{-- Footer --}}
                <div class="mt-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 p-4 rounded-2xl bg-gradient-to-r from-gray-50 to-white border border-gray-100">
                    <div class="text-sm text-gray-500 flex items-center gap-4">
                        <span class="flex items-center gap-1.5">
                            <x-ui.icon name="users" size="14" class="text-gray-400" />
                            <span class="font-semibold text-gray-700">{{ $siswaList->count() }}</span> siswa
                        </span>
                        <span class="text-gray-300">•</span>
                        <span class="flex items-center gap-1.5">
                            <x-ui.icon name="calendar" size="14" class="text-gray-400" />
                            <span class="font-semibold text-gray-700">{{ $pertemuanList->count() }}</span> pertemuan
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const jadwalId = {{ $jadwal->id }};
const todayPertemuanId = {{ $todayPertemuan?->id ?? 'null' }};
const csrfToken = '{{ csrf_token() }}';

// Store siswa IDs for tracking
const siswaIds = @json($siswaList->pluck('id')->toArray());

// Track absensi status for each siswa/pertemuan
const absensiState = {};
@foreach($siswaList as $siswa)
    absensiState[{{ $siswa->id }}] = {};
    @foreach($pertemuanList as $pertemuan)
        @php
            $absensi = $absensiMatrix[$siswa->id][$pertemuan->id] ?? null;
            $status = $absensi?->status?->value ?? '';
        @endphp
        absensiState[{{ $siswa->id }}][{{ $pertemuan->id }}] = '{{ $status }}';
    @endforeach
@endforeach

const initialAbsensi = @json(collect($absensiMatrix)->map(function($pertemuanData) {
    return collect($pertemuanData)->map(function($item) {
        return $item ? $item->status->value : null;
    });
}));

// Toast notification function
function showToast(message, type = 'success') {
    // Remove existing toast
    const existingToast = document.getElementById('save-toast');
    if (existingToast) existingToast.remove();
    
    const toast = document.createElement('div');
    toast.id = 'save-toast';
    toast.className = `fixed bottom-4 right-4 z-50 px-4 py-3 rounded-xl shadow-lg flex items-center gap-2 text-sm font-medium transition-all transform translate-y-0 opacity-100`;
    
    if (type === 'success') {
        toast.classList.add('bg-emerald-500', 'text-white');
        toast.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> ${message}`;
    } else {
        toast.classList.add('bg-red-500', 'text-white');
        toast.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> ${message}`;
    }
    
    document.body.appendChild(toast);
    
    // Auto remove after 2 seconds
    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => toast.remove(), 300);
    }, 2000);
}

// Update Grid select dropdown for a specific siswa and pertemuan (targets ALL styling instances - mobile & desktop)
function updateGridSelect(siswaId, pertemuanId, status) {
    const selects = document.querySelectorAll(`select[data-siswa-id="${siswaId}"][data-pertemuan-id="${pertemuanId}"]`);
    selects.forEach(select => {
        // Update selected value directly
        select.value = status;
        
        // Manual fallback ensuring option is selected
        for (let i = 0; i < select.options.length; i++) {
            if (select.options[i].value === status) {
                select.selectedIndex = i;
                break;
            }
        }
        
        // Update styling class
        select.className = 'status-select rounded-md border-0 cursor-pointer ' + status.toLowerCase();
    });
}

// Update "Tidak Hadir" count for a specific siswa
function updateTidakHadirCount(siswaId) {
    let tidakHadir = 0;
    
    // Count non-Hadir statuses for this siswa
    Object.keys(absensiState[siswaId] || {}).forEach(pertemuanId => {
        const status = absensiState[siswaId][pertemuanId];
        if (status && status !== '' && status !== 'Hadir') {
            tidakHadir++;
        }
    });

    // Update ALL matching rows (Mobile & Desktop)
    // We use data-siswa-id attribute on TR elements in both layouts
    const rows = document.querySelectorAll(`tr[data-siswa-id="${siswaId}"]`);
    rows.forEach(row => {
        const tdTidakHadir = row.querySelector('.js-tidak-hadir-count');
        if (tdTidakHadir) {
            tdTidakHadir.textContent = tidakHadir;
            tdTidakHadir.classList.remove('text-gray-300', 'text-red-600', 'bg-red-50');
            if (tidakHadir > 0) {
                tdTidakHadir.classList.add('text-red-600', 'bg-red-50');
            } else {
                tdTidakHadir.classList.add('text-gray-300');
            }
        }
    });
}

function absensiPage() {
    return {
        activeTab: 'single',
        selectedPertemuanId: todayPertemuanId ? String(todayPertemuanId) : '',
        absensiData: initialAbsensi,
        
        onPertemuanChange() {},
        
        getStatus(siswaId) {
            if (!this.selectedPertemuanId) return '';
            const pertemuanData = this.absensiData[siswaId];
            if (!pertemuanData) return '';
            return pertemuanData[this.selectedPertemuanId] || '';
        },
        
        setStatus(siswaId, status) {
            if (!this.selectedPertemuanId) return;
            if (!this.absensiData[siswaId]) this.absensiData[siswaId] = {};
            this.absensiData[siswaId][this.selectedPertemuanId] = status;
            
            // Update absensiState for real-time count
            if (!absensiState[siswaId]) absensiState[siswaId] = {};
            absensiState[siswaId][this.selectedPertemuanId] = status;
            updateTidakHadirCount(siswaId);
            
            // Sync with Grid tab
            updateGridSelect(siswaId, this.selectedPertemuanId, status);
            
            this.saveAbsensi(siswaId, this.selectedPertemuanId, status);
        },
        
        toggleStatus(siswaId, status) {
            if (!this.selectedPertemuanId) return;
            const currentStatus = this.getStatus(siswaId);
            if (currentStatus === status) {
                if (!this.absensiData[siswaId]) this.absensiData[siswaId] = {};
                this.absensiData[siswaId][this.selectedPertemuanId] = '';
                
                // Update absensiState
                if (!absensiState[siswaId]) absensiState[siswaId] = {};
                absensiState[siswaId][this.selectedPertemuanId] = '';
                updateTidakHadirCount(siswaId);
                
                // Sync with Grid tab
                updateGridSelect(siswaId, this.selectedPertemuanId, '');
                
                this.saveAbsensi(siswaId, this.selectedPertemuanId, '');
            } else {
                this.setStatus(siswaId, status);
            }
        },
        
        setAllStatus(status) {
            if (!this.selectedPertemuanId) return;
            @foreach($siswaList as $siswa)
                this.setStatus({{ $siswa->id }}, status);
            @endforeach
            showToast('Semua siswa ditandai ' + status);
        },
        
        // Handle update from outside (Grid view)
        handleExternalUpdate(detail) {
            const { siswaId, pertemuanId, status } = detail;
            // Update local Alpine data to trigger reactivity in Single tab
            if (!this.absensiData[siswaId]) this.absensiData[siswaId] = {};
            this.absensiData[siswaId][pertemuanId] = status;
            
            // Also update absensiState global if needed (already updated in updateAbsensiGrid usually)
            // But we ensure consistency
            if (!absensiState[siswaId]) absensiState[siswaId] = {};
            absensiState[siswaId][pertemuanId] = status;
        },

        saveAbsensi(siswaId, pertemuanId, status) {
            fetch('{{ route("absensi.updateSingle") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ siswa_id: siswaId, pertemuan_id: pertemuanId, status: status })
            })
            .then(response => response.json())
            .then(data => { 
                if (data.success) {
                    showToast('Tersimpan');
                } else {
                    showToast('Gagal menyimpan', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan', 'error');
            });
        }
    };
}

function updateAbsensiGrid(selectElement) {
    const siswaId = selectElement.dataset.siswaId;
    const pertemuanId = selectElement.dataset.pertemuanId;
    const status = selectElement.value;
    
    selectElement.className = 'status-select rounded-md border-0 cursor-pointer ' + status.toLowerCase();
    
    // Update absensiState and the "Tidak Hadir" count
    if (!absensiState[siswaId]) absensiState[siswaId] = {};
    absensiState[siswaId][pertemuanId] = status;
    updateTidakHadirCount(siswaId);
    
    // Dispatch event for Alpine component to pick up changes
    window.dispatchEvent(new CustomEvent('absensi-updated', { 
        detail: { siswaId, pertemuanId, status } 
    }));
    
    fetch('{{ route("absensi.updateSingle") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ siswa_id: siswaId, pertemuan_id: pertemuanId, status: status })
    })
    .then(response => response.json())
    .then(data => { 
        if (data.success) {
            showToast('Tersimpan');
        } else {
            showToast('Gagal menyimpan', 'error');
        }
    })
    .catch(error => {
        showToast('Terjadi kesalahan', 'error');
    });
}

function setAllTodayGrid(status) {
    if (!todayPertemuanId) { alert('Tidak ada pertemuan hari ini.'); return; }
    
    document.querySelectorAll(`select[data-pertemuan-id="${todayPertemuanId}"]`).forEach(select => {
        const siswaId = select.dataset.siswaId;
        select.value = status;
        select.className = 'status-select rounded-md border-0 cursor-pointer ' + status.toLowerCase();
        
        // Update absensiState
        if (!absensiState[siswaId]) absensiState[siswaId] = {};
        absensiState[siswaId][todayPertemuanId] = status;
        updateTidakHadirCount(siswaId);
    });
    
    fetch('{{ route("absensi.batchUpdate") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ pertemuan_id: todayPertemuanId, status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Semua siswa ditandai ' + status);
        } else {
            showToast('Gagal menyimpan', 'error');
        }
    });
}
</script>
@endpush
@endsection
