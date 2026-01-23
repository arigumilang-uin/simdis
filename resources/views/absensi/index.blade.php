@extends('layouts.app')

@section('title', 'Jadwal Mengajar Saya')

@section('page-header')
    <x-page-header 
        title="Jadwal Mengajar Saya" 
        subtitle="Semester {{ $currentSemester }} - {{ $currentTahunAjaran }}"
    />
@endsection

@section('content')
<div class="space-y-6" x-data="{ activeHari: '{{ $hariIni ?? array_key_first($jadwalByHari->toArray()) }}' }">

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($jadwalByHari->isEmpty())
        <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm p-12">
            <x-ui.empty-state 
                icon="calendar-x" 
                title="Tidak Ada Jadwal" 
                description="Anda belum memiliki jadwal mengajar yang terdaftar untuk periode ini." 
            />
        </div>
    @else
        {{-- Hari Tabs --}}
        <div class="bg-white md:border md:border-gray-200 md:rounded-xl md:shadow-sm overflow-hidden">
            <div class="flex border-b border-gray-200 bg-gray-50/80 overflow-x-auto">
                @foreach($jadwalByHari as $hari => $jadwalList)
                    @php $isToday = $hari === $hariIni; @endphp
                    <button 
                        type="button"
                        @click="activeHari = '{{ $hari }}'"
                        class="flex-shrink-0 px-5 py-4 text-center border-b-2 transition-all focus:outline-none"
                        :class="activeHari === '{{ $hari }}' 
                            ? 'border-emerald-500 bg-white' 
                            : 'border-transparent hover:bg-gray-100'"
                    >
                        <div class="text-left">
                            <div class="text-sm font-semibold" :class="activeHari === '{{ $hari }}' ? 'text-gray-900' : 'text-gray-600'">{{ $hari }}</div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-500">{{ $jadwalList->count() }} jadwal</span>
                                @if($isToday)
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-600 uppercase">Hari Ini</span>
                                @endif
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Content per Hari --}}
        @foreach($jadwalByHari as $hari => $jadwalList)
            <div x-show="activeHari === '{{ $hari }}'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                
                {{-- Jadwal Cards Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    @foreach($jadwalList as $jadwal)
                        <div class="group relative bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:shadow-gray-200/50 hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                            
                            {{-- Accent Line --}}

                            
                            {{-- Card Content --}}
                            <div class="p-5 pt-6">
                                
                                {{-- Header: Waktu + Badge --}}
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center shadow-lg shadow-emerald-200">
                                            <x-ui.icon name="clock" size="18" class="text-white" />
                                        </div>
                                        <div>
                                            @if($jadwal->time_display ?? false)
                                                <div class="text-sm font-semibold text-gray-900">
                                                    {{ $jadwal->time_display }}
                                                </div>
                                            @else
                                                @php
                                                    $jamMulai = $jadwal->templateJam?->jam_mulai;
                                                    $jamSelesai = $jadwal->templateJam?->jam_selesai;
                                                    $jamMulai = $jamMulai instanceof \DateTime ? $jamMulai->format('H:i') : substr($jamMulai ?? '', 0, 5);
                                                    $jamSelesai = $jamSelesai instanceof \DateTime ? $jamSelesai->format('H:i') : substr($jamSelesai ?? '', 0, 5);
                                                @endphp
                                                <div class="text-sm font-semibold text-gray-900">
                                                    {{ $jamMulai }} - {{ $jamSelesai }}
                                                </div>
                                            @endif
                                            <div class="text-xs text-gray-400">Waktu mengajar</div>
                                        </div>
                                    </div>
                                    @if(($jadwal->session_count ?? 1) > 1)
                                        <span class="text-[10px] font-semibold px-2.5 py-1 rounded-lg bg-amber-50 text-amber-600 border border-amber-100">
                                            {{ $jadwal->session_count }} sesi
                                        </span>
                                    @endif
                                </div>

                                {{-- Mata Pelajaran --}}
                                <div class="mb-4">
                                    <h4 class="font-bold text-gray-900 text-lg leading-snug">
                                        {{ $jadwal->mataPelajaran->nama_mapel }}
                                    </h4>
                                </div>

                                {{-- Kelas & Jurusan --}}
                                <div class="flex items-center gap-3 mb-5">
                                    <div class="flex items-center gap-2 px-3 py-2 bg-gradient-to-r from-gray-50 to-gray-100/50 rounded-xl border border-gray-100">
                                        <div class="w-7 h-7 rounded-lg bg-white shadow-sm flex items-center justify-center">
                                            <x-ui.icon name="users" size="14" class="text-gray-500" />
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-800">{{ $jadwal->kelas->nama_kelas }}</div>
                                            <div class="text-[11px] text-gray-400">{{ $jadwal->kelas->jurusan->nama_jurusan ?? '-' }}</div>
                                        </div>
                                    </div>
                                    @if(isset($jadwal->totalPertemuan))
                                        <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                            <x-ui.icon name="calendar" size="12" class="text-gray-400" />
                                            <span>{{ $jadwal->totalPertemuan }} pertemuan</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Action Button --}}
                                <a href="{{ route('absensi.grid', $jadwal->id) }}" 
                                   class="flex items-center justify-center w-full py-2.5 px-4 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow-lg hover:shadow-emerald-200 transition-all duration-200">
                                    Kelola Absensi
                                </a>

                            </div>
                        </div>
                    @endforeach
                </div>

                @if($jadwalList->isEmpty())
                    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
                        <x-ui.icon name="calendar" size="48" class="text-gray-300 mx-auto mb-4" />
                        <p class="text-gray-500">Tidak ada jadwal untuk hari {{ $hari }}</p>
                    </div>
                @endif
            </div>
        @endforeach
    @endif

</div>
@endsection
