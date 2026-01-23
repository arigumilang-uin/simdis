@extends('layouts.app')

@section('title', 'Rekap Absensi')

@section('page-header')
    <x-page-header 
        title="Rekap Absensi" 
        subtitle="Monitoring kehadiran siswa"
        backUrl="{{ route('absensi.index') }}"
    />
@endsection

@section('content')
<div class="space-y-6">

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <x-ui.icon name="alert-circle" size="16" class="text-red-500" />
            {{ session('error') }}
        </div>
    @endif

    {{-- Info Konteks User --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <x-ui.icon name="info" size="20" class="text-blue-500 flex-shrink-0 mt-0.5" />
            <div class="text-sm text-blue-800">
                <div class="font-semibold mb-1">{{ $userRole }}</div>
                @if($userRole === 'Wali Kelas')
                    <p class="text-blue-700">Anda dapat melihat rekap absensi siswa di kelas {{ $kelasList->first()?->nama_kelas }} yang Anda ampu.</p>
                @elseif($userRole === 'Kaprodi')
                    <p class="text-blue-700">Anda dapat melihat rekap absensi siswa di jurusan dan konsentrasi yang Anda kelola.</p>
                @else
                    <p class="text-blue-700">Anda dapat melihat rekap absensi dari semua kelas di sekolah.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm p-6">
        <form action="{{ route('absensi.report') }}" method="GET" class="space-y-4" x-data="{ 
            kelasId: '{{ $selectedKelasId }}',
            startDate: '{{ $startDate }}',
            endDate: '{{ $endDate }}',
            activePeriod: null,
            setSemesterPeriod() {
                this.startDate = '{{ $semesterDates['start'] ?? '' }}';
                this.endDate = '{{ $semesterDates['end'] ?? '' }}';
                this.activePeriod = 'semester';
            },
            setThisMonth() {
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const lastDay = new Date(year, now.getMonth() + 1, 0).getDate();
                this.startDate = `${year}-${month}-01`;
                this.endDate = `${year}-${month}-${String(lastDay).padStart(2, '0')}`;
                this.activePeriod = 'month';
            }
        }">
            
            {{-- For Wali Kelas: Show info box instead of dropdown --}}
            @if($userRole === 'Wali Kelas')
                <div>
                    <label class="form-label">Kelas Anda</label>
                    <div class="px-4 py-3 rounded-lg border border-gray-200 bg-gray-50">
                        <div class="font-semibold text-gray-900">
                            {{ $kelasList->first()?->nama_kelas }} - {{ $kelasList->first()?->jurusan->nama_jurusan }}
                        </div>
                    </div>
                    <input type="hidden" name="kelas_id" value="{{ $kelasList->first()?->id }}">
                </div>
            @else
                <div>
                    <label class="form-label">
                        @if($userRole === 'Kaprodi')
                            Pilih Kelas (Jurusan Anda)
                        @else
                            Pilih Kelas
                        @endif
                    </label>
                    <select name="kelas_id" class="form-input" x-model="kelasId" required>
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}">
                                {{ $kelas->nama_kelas }} - {{ $kelas->jurusan->nama_jurusan ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Quick Period Shortcuts --}}
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="setSemesterPeriod()" 
                    :class="activePeriod === 'semester' ? 'bg-emerald-500 text-white border-emerald-600' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border-gray-200'" 
                    class="text-xs px-3 py-1.5 rounded-lg border transition-all flex items-center gap-1.5">
                    <x-ui.icon name="calendar" size="12" />
                    <span>Semester {{ $currentSemester?->value ?? 'Saat Ini' }}</span>
                    <x-ui.icon name="check" size="12" x-show="activePeriod === 'semester'" />
                </button>
                <button type="button" @click="setThisMonth()" 
                    :class="activePeriod === 'month' ? 'bg-emerald-500 text-white border-emerald-600' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border-gray-200'" 
                    class="text-xs px-3 py-1.5 rounded-lg border transition-all flex items-center gap-1.5">
                    <x-ui.icon name="calendar" size="12" />
                    <span>Bulan Ini</span>
                    <x-ui.icon name="check" size="12" x-show="activePeriod === 'month'" />
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="start_date" x-model="startDate" @input="activePeriod = null" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="end_date" x-model="endDate" @input="activePeriod = null" class="form-input" required>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary">
                    <x-ui.icon name="search" size="16" />
                    <span>Tampilkan Rekap</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Results --}}
    @if($rekap)
        {{-- Summary Statistics --}}
        @php
            $totalSiswa = $rekap->count();
            $avgKehadiran = $rekap->avg(function($item) {
                return $item['total_hari'] > 0 ? ($item['hadir'] / $item['total_hari']) * 100 : 0;
            });
            $siswaHadirBaik = $rekap->filter(function($item) {
                $persen = $item['total_hari'] > 0 ? ($item['hadir'] / $item['total_hari']) * 100 : 0;
                return $persen >= 90;
            })->count();
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm p-5">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <x-ui.icon name="users" size="24" class="text-blue-600" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Siswa</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalSiswa }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm p-5">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                        <x-ui.icon name="trending-up" size="24" class="text-emerald-600" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Rata-rata Kehadiran</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($avgKehadiran, 1) }}%</p>
                    </div>
                </div>
            </div>

            <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm p-5">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                        <x-ui.icon name="award" size="24" class="text-amber-600" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Kehadiran ≥ 90%</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $siswaHadirBaik }} <span class="text-sm font-normal text-gray-500">siswa</span></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Data Table --}}
        <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900">{{ $kelasList->firstWhere('id', $selectedKelasId)?->nama_kelas }}</h3>
                <p class="text-sm text-gray-500 mt-1">
                    {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-16 text-center">#</th>
                            <th class="text-left">Nama Siswa</th>
                            <th class="w-20 text-center">Total</th>
                            <th class="w-20 text-center">Hadir</th>
                            <th class="w-20 text-center">Sakit</th>
                            <th class="w-20 text-center">Izin</th>
                            <th class="w-20 text-center">Alfa</th>
                            <th class="w-32 text-center">% Hadir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rekap as $index => $item)
                            @php
                                $persenHadir = $item['total_hari'] > 0 
                                    ? round(($item['hadir'] / $item['total_hari']) * 100, 1) 
                                    : 0;
                            @endphp
                            <tr>
                                <td class="text-center text-gray-400 font-medium">{{ $index + 1 }}</td>
                                <td>
                                    <div class="font-semibold text-gray-900">{{ $item['siswa']->nama_siswa }}</div>
                                    <div class="text-xs text-gray-400">NISN: {{ $item['siswa']->nisn }}</div>
                                </td>
                                <td class="text-center font-semibold text-gray-700">{{ $item['total_hari'] }}</td>
                                <td class="text-center">
                                    <span class="inline-flex items-center justify-center min-w-[48px] px-3 py-1 rounded-lg bg-emerald-100 text-emerald-700 font-semibold text-sm">
                                        {{ $item['hadir'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="inline-flex items-center justify-center min-w-[48px] px-3 py-1 rounded-lg bg-amber-100 text-amber-700 font-semibold text-sm">
                                        {{ $item['sakit'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="inline-flex items-center justify-center min-w-[48px] px-3 py-1 rounded-lg bg-blue-100 text-blue-700 font-semibold text-sm">
                                        {{ $item['izin'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="inline-flex items-center justify-center min-w-[48px] px-3 py-1 rounded-lg bg-red-100 text-red-700 font-semibold text-sm">
                                        {{ $item['alfa'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="flex flex-col items-center gap-1.5">
                                        <span class="inline-flex items-center justify-center min-w-[64px] px-3 py-1 rounded-lg font-semibold text-sm
                                            {{ $persenHadir >= 90 ? 'bg-emerald-100 text-emerald-700' : ($persenHadir >= 75 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                            {{ $persenHadir }}%
                                        </span>
                                        <div class="w-full bg-gray-200 rounded-full h-1.5 max-w-[80px]">
                                            <div class="h-1.5 rounded-full transition-all
                                                {{ $persenHadir >= 90 ? 'bg-emerald-500' : ($persenHadir >= 75 ? 'bg-amber-500' : 'bg-red-500') }}" 
                                                style="width: {{ $persenHadir }}%">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm p-12">
            <x-ui.empty-state 
                icon="filter" 
                title="Pilih Kelas dan Periode" 
                description="Pilih kelas dan rentang tanggal untuk melihat rekap absensi siswa."
            />
        </div>
    @endif
</div>
@endsection
